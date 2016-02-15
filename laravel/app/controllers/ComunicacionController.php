<?php

class ComunicacionController extends \BaseController
{

    private $userID;
    private $userNivelId;

    /**
     * Constructor de la clase
     *
     */
    public function __construct()
    {
        $this->beforeFilter('auth'); // bloqueo de acceso
        $this->userID = Auth::user()->id;
        $this->userNivelId = Auth::user()->nivel_id;
    }

    public function debug($data , $kill = true) {
        var_dump($data);
        if ($kill)
            die();
    }

    public function getAuth($user_id) {
        return Response::json(Auth::user());
    }

    public function getCargos($cargo_id) {

        $cargo = Cargo::find(Auth::user()->nivel_id);
        // agregando padre

        $nivelId = Auth::user()->nivel_id;
        $seguirAlguien= $nivelId-1;
        // 10 liebre
        // 11 administrador
        if($nivelId==10 or $nivelId==11){
            $seguirAlguien=0;
        }
        $cargoS= Cargo::find($seguirAlguien);
        if( count($cargoS)<=0 ){
            $cargoS= new stdClass();
            $cargoS->nombre='';
        }

        $cargo->padre = $cargoS;


        return Response::json($cargo);

    }

    public function errorMessage($message) {

        header('HTTP/1.1 401 Internal Server Booboo');
        header('Content-Type: application/json; charset=UTF-8');
        die(json_encode(array('message' => $message, 'code' => 'error')));
    }

    /**
     * cargar modulos, mantenimiento
     * GET /perfil/test
     *
     * @return Response
     */
    public function getTest()
    {
//        $usuario = Persona::find(Auth::user()->id);
        return Response::json($this->userID);
    }

    public function postComunicacion() {
        $data = Input::all();

        if (!empty($data['envioEnMasa'])) {
            // mensaje enviado por libre para muchos grupos
            $idMensaje = DB::table("mensajes")->insertGetId(array(
                'activista_id' => $this->userID,
                'asunto' => array_key_exists('asunto', $data) ? $data['asunto']: "",
                'mensaje' => array_key_exists('mensaje', $data) ? $data['mensaje']: "",
                'estado' => 1,
                'activo' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'reponsed_at' => date('Y-m-d H:i:s'),
                'archivo_id' => array_key_exists('archivo_id', $data) ? $data['archivo_id']: "",
            ));

            // registra la respuesta automaticamente
            $id = DB::table("respuestas")->insertGetId(array(
                'mensaje_id' => $idMensaje,
                'respondido_por' => $this->userID,
                'respondido_at' => date('Y-m-d H:i:s'),
                'respuesta' => $data['respuesta'],
                'cargo_id' => $data['acceso'],
                'estado' => 1,
                'created_at'=> date('Y-m-d H:i:s'),
            ));

            // @todo : agregar el guardar accesos cuando se haga para paginas , grupo de personas , etc

            $results = array(
                "code"=>"ok",
                "message"=>"Mensaje Enviado"
            );



        } elseif (!empty($data['editar'])) {
            DB::table("mensajes")
                ->where('id', $data['id'])
                ->update(array(
                    'estado' => 1,
                    'archivo_id' => array_key_exists('archivo_id', $data) ? $data['archivo_id']: "",
                    'reponsed_at' => date('Y-m-d H:i:s'),
                ));

            $id = DB::table("respuestas")->insertGetId(array(
                'mensaje_id' => $data['id'],
                'respondido_por' => $this->userID,
                'respondido_at' => date('Y-m-d H:i:s'),
                'respuesta' => $data['respuesta'],
                'cargo_id' => $data['acceso'],
                'estado' => 1,
                'created_at'=>date('Y-m-d H:i:s'),
            ));

            $results = array(
                "code"=>"ok",
                "message"=>"Datos correctamente guardados"
            );

        } else {
            $id = DB::table("mensajes")->insertGetId(array(
                'activista_id' => $this->userID,
                'asunto' => array_key_exists('asunto', $data) ? $data['asunto']: "",
                'mensaje' => array_key_exists('mensaje', $data) ? $data['mensaje']: "",
                'estado' => 0,
                'activo' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'archivo_id' => array_key_exists('archivo_id', $data) ? $data['archivo_id']: "",
            ));

            if ($id) {
                // @todo manejar errores
                $results = array(
                    "code"=>"ok",
                    "message"=>"Datos correctamente guardados"
                );
            }
        }

        return Response::json($results);
    }

    public function getComunicacion($mensaje_id = 0) {
        $data = Input::all();
        $where = ' where  1 = 1 ';

        if (!empty($data['soy']) && $data['soy'] != 'liebre') {
            $where .= " activista_id = '.$this->userID.' ";
        }

        if (isset($data['estado'])) {
            $where .= " and estado = ".$data['estado'] . ' ' ;
        }

        if (!empty($mensaje_id)) {
            $where .= " and id = ".$mensaje_id . ' ' ;
        }

        if (!empty($data['soloEnviados'])) {
            $where .= " and activista_id = " . $this->userID . ' ' ;
        }

        $sql = "select *  , (select CONCAT(a.nombres,' ', a.paterno) from activistas a  where mensajes.activista_id = a.id) activista_nombre " .            ' from mensajes '
            .$where. ' order by id desc ';
        $rows = DB::select($sql);

        if (!empty($mensaje_id)) {
            return Response::json($rows[0]);

        } else
            return Response::json($rows);

    }

    public function getTipoacceso($tipo_acceso_id = 0) {

        $sql = 'SELECT 0 id,"Todos" nombre
                UNION
                SELECT id,nombre from cargos where estado = 1';

        return Response::json(DB::select($sql));
    }

    public function postTipoacceso($tipo_acceso_id = 0) {

        $sql = 'SELECT 0 id,"Todos" nombre
                UNION
                SELECT id,nombre from cargos where estado = 1';

        return Response::json(DB::select($sql));
    }

    // bandeja id es em id del mensae de la bandeja
    // la bandeja devuelve todos los mensajes que pertenecen al usuario activo
    public function getBandeja($bandeja_id = 0) {
        $unico = $bandeja_id > 0;
        $where = '';

        if ($unico) {
            $where = ' AND m.id = '.$bandeja_id;
        }

        $sql = "
            SELECT m.*, r.respuesta, r.tipo_acceso_id, r.respondido_at,r.url
            FROM mensajes m
            INNER join respuestas r on r.mensaje_id = m.id
            WHERE m.estado = 1
            AND (activista_id = " .  $this->userID  . 
            " OR cargo_id IS NULL OR cargo_id = ".$this->userNivelId." )".
            $where.
            " ORDER BY r.respondido_at DESC";
        if ($unico)
            return Response::json(DB::select($sql)[0]);
        else
            return Response::json(DB::select($sql));

    }

    public function postComunicacionfile() {
        if (Input::hasFile('file')) {

            $filename = Input::file('file')->getClientOriginalName();
            $extension = Input::file('file')->getClientOriginalExtension();

            try {
                $ubicacion = 'img/upload';
                $fileName  = str_random(11) . '_' . $filename;
                $file = Input::file('file')->move($ubicacion, $fileName);


                $id = DB::table("archivos")->insertGetId(array(
                    'name' => $filename,
                    'ubicacion' => $ubicacion . '/' . $fileName ,
                    'type' => $extension
                ));


            } catch(Exception $e) {


            }

            return Response::json($id);
        }
    }

    public function getArchivos ($archivo_id = 0) {
        $unico = $archivo_id > 0 ;
        $sql = 'select * from archivos ';
        if ($unico) {
            $sql .= ' where id = '.$archivo_id;
        }


        if ($unico)
            return Response::json(DB::select($sql)[0]);
        else
            return Response::json(DB::select($sql));

    }




}
