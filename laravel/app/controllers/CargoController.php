<?php

class CargoController extends \BaseController
{
    public function postNivel()
    {
        //si la peticion es ajax
        if ( Request::ajax() ) {
            
            if (Input::has('estado')) {
                $estado = Input::get('estado');
                $cargos = DB::table('cargos')
                        ->select(
                            'nombre','id'
                        )
                        ->where('estado', '=', 1)
                        ->where('id','<',10)
                        ->orderBy('nombre')
                        ->get();
            } 
            
            return Response::json(array('rst'=>1,'datos'=>$cargos));
        }
    }
    /**
     * cargar modulos, mantenimiento
     * POST /cargo/cargar
     *
     * @return Response
     */
    public function postCargar()
    {
        //si la peticion es ajax
        if ( Request::ajax() ) {
            $cargos = Cargo::get(Input::all());
            return Response::json(array('rst'=>1,'datos'=>$cargos));
        }
    }
    /**
     * Store a newly created resource in storage.
     * POST /cargo/listar
     *
     * @return Response
     */
    public function postListar()
    {
        //si la peticion es ajax
        if ( Request::ajax() ) {
            
            if (Input::get('usuario_id')) {
                $usuarioId = Input::get('usuario_id');
                $cargos = DB::table('submodulo_usuario as su')
                        ->rightJoin(
                            'submodulos as s', function($join) use ($usuarioId)
                            {
                            $join->on('su.submodulo_id', '=', 's.id')
                            ->on('su.usuario_id', '=', DB::raw($usuarioId));
                            }
                        )
                        ->rightJoin(
                            'modulos as m', 
                            's.modulo_id', '=', 'm.id'
                        )
                        ->select(
                            'm.nombre',
                            DB::raw('MAX(su.estado) as estado')
                        )
                        ->where('m.estado', '=', 1)
                        ->groupBy('m.nombre')
                        ->orderBy('m.nombre')
                        ->get();
            } elseif( Input::has('id') ) {
                $cargos = DB::table('cargos')
                            ->select('id', 'nombre')
                            ->where('estado', '=', '1')
                            ->where('id', '=', Input::get('id') )
                            ->orderBy('nombre')
                            ->get();
            }
            else {
                $cargos = DB::table('cargos')
                            ->select('id', 'nombre')
                            ->where('estado', '=', '1')
                            ->orderBy('nombre')
                            ->get();
            }
            
            return Response::json(array('rst'=>1,'datos'=>$cargos));
        }
    }
    /**
     * Store a newly created resource in storage.
     * POST /cargo/cargaropciones
     *
     * @return Response
     */
    public function postCargaropciones()
    {
        $cargoId = Input::get('cargo_id');
        $cargo = new Cargo;
        $opciones = $cargo->getOpciones($cargoId);
        return Response::json(array('rst'=>1,'datos'=>$opciones));
    }
    /**
     * Store a newly created resource in storage.
     * POST /cargo/crear
     *
     * @return Response
     */
    public function postCrear()
    {
        //si la peticion es ajax
        if ( Request::ajax() ) {
            $regex='regex:/^([a-zA-Z .,ñÑÁÉÍÓÚáéíóú]{2,60})$/i';
            $required='required';
            $reglas = array(
                'nombre' => $required.'|unique:cargos',
                //'path' =>$regex.'|unique:modulos,path,',
            );

            $mensaje= array(
                'required'    => ':attribute Es requerido',
                'regex'        => ':attribute Solo debe ser Texto',
            );

            $validator = Validator::make(Input::all(), $reglas, $mensaje);

            if ( $validator->fails() ) {
                return Response::json(
                    array(
                    'rst'=>2,
                    'msj'=>$validator->messages(),
                    )
                );
            }

            $cargo = new Cargo;
            $cargo->nombre = Input::get('nombre');
            $cargo->estado = Input::get('estado');
            $cargo->usuario_created_at = Auth::user()->id;
            $cargo->save();

            $cargoId=$cargo->id;
            $opciones = Input::get('opciones');
            $estado = Input::get('estado');

            if ($opciones) {
                for ($i=0; $i<count($opciones); $i++) {
                    $datos=explode("_",$opciones[$i]);
                    $opcionId = $datos[1];
                    $estado = $datos[0];

                    if($estado==1){
                        $cargoOpcion= new CargoOpcion;
                        $cargoOpcion->cargo_id = $cargoId;
                        $cargoOpcion->opcion_id = $opcionId;
                        $cargoOpcion->usuario_created_at= Auth::user()->id;
                        $cargoOpcion->save();
                    }
                }
            }

            return Response::json(
                array(
                'rst'=>1,
                'msj'=>'Registro realizado correctamente',
                )
            );
        }
    }

    /**
     * Update the specified resource in storage.
     * POST /cargo/editar
     *
     * @return Response
     */
    public function postEditar()
    {
        if ( Request::ajax() ) {
            $regex='regex:/^([a-zA-Z .,ñÑÁÉÍÓÚáéíóú]{2,60})$/i';
            $required='required';
            $reglas = array(
                'nombre' => $required.'|unique:cargos,nombre,'.Input::get('id'),
            );

            $mensaje= array(
                'required'    => ':attribute Es requerido',
                'regex'        => ':attribute Solo debe ser Texto',
            );

            $validator = Validator::make(Input::all(), $reglas, $mensaje);

            if ( $validator->fails() ) {
                return Response::json(
                    array(
                    'rst'=>2,
                    'msj'=>$validator->messages(),
                    )
                );
            }
            $cargoId = Input::get('id');

            $cargos = Cargo::find($cargoId);
            $cargos->nombre = Input::get('nombre');
            $cargos->estado = Input::get('estado');
            $cargos->usuario_updated_at = Auth::user()->id;
            $cargos->save();
            
            $opciones = Input::get('opciones');
            $estado = Input::get('estado');

            DB::table('cargo_opcion')
                    ->where('cargo_id', $cargoId)
                    ->update(
                        array(
                            'estado' => 0,
                            'usuario_updated_at' => Auth::user()->id
                            )
                        );

            if ($estado == 0 ) {
                return Response::json(
                    array(
                    'rst'=>1,
                    'msj'=>'Registro actualizado correctamente',
                    )
                );
            }

            if ($opciones) {
                for ($i=0; $i<count($opciones); $i++) {
                    $datos=explode("_",$opciones[$i]);
                    $opcionId = $datos[1];
                    $estado = $datos[0];

                    $cargoOpciones = DB::table('cargo_opcion')
                                        ->where('cargo_id', '=', $cargoId)
                                        ->where('opcion_id', '=', $opcionId)
                                        ->first();
                    if (is_null($cargoOpciones) AND $estado==1) {
                        $cargoOpcion= new CargoOpcion;
                        $cargoOpcion->cargo_id = $cargoId;
                        $cargoOpcion->opcion_id = $opcionId;
                        $cargoOpcion->usuario_created_at= Auth::user()->id;
                        $cargoOpcion->save();
                    } elseif ( $estado==1 ) {
                        //update a la tabla cargo_opcion
                        DB::table('cargo_opcion')
                            ->where('cargo_id', '=', $cargoId)
                            ->where('opcion_id', '=', $opcionId)
                            ->update(array(
                                'estado' => 1,
                                'usuario_updated_at' => Auth::user()->id
                                )
                            );
                    }
                }
            }
            return Response::json(
                array(
                'rst'=>1,
                'msj'=>'Registro actualizado correctamente',
                )
            );
        }
    }

    /**
     * Changed the specified resource from storage.
     * POST /cargo/cambiarestado
     *
     * @return Response
     */
    public function postCambiarestado()
    {

        if ( Request::ajax() ) {
            $estado = Input::get('estado');
            $cargoId = Input::get('id');
            $cargo = Cargo::find($cargoId);
            $cargo->usuario_updated_at = Auth::user()->id;
            $cargo->estado = Input::get('estado');
            $cargo->save();

            //estado 0, en la tabla cargo_opcion, para este cargo
            /*if ($estado == 0) {
                DB::table('cargo_opcion')
                    ->where('cargo_id', $cargoId)
                    ->update(
                        array(
                            'estado' => $estado,
                            'usuario_updated_at' => Auth::user()->id
                        ));
            }*/

            return Response::json(
                array(
                'rst'=>1,
                'msj'=>'Registro actualizado correctamente',
                )
            );    

        }
    }

}
