<?php

class Firma extends Base
{
    public $table = "firmas";

    public static function ValidaFicha($ficha)
    {
        $sql="  SELECT COUNT(id) cant 
                FROM firmas
                WHERE ficha=".$ficha;
        $r=DB::select($sql);

        return $r[0]->cant;
    }

    public static function ValidaFirma($dni)
    {
        $sql="  SELECT GROUP_CONCAT(id) ids 
                FROM firmas
                WHERE dni='".$dni."'";
        $r=DB::select($sql);

        return $r;
    }

    public static function ValidaPagina($id)
    {
        $pag=PaginaFirma::find($id);
        $r=array();
        $r['rst']=1;
        if( count($pag)>0 ){
            if( $pag->estado==2 ){
                $r['msj']='Página Libre';
            }
            else{
                $r['msj']='Página existente';
                $r['rst']=2;
            }
        }
        else{
            $r['msj']='Página no existe';
            $r['rst']=2;
        }

        return $r;
    }

    public static function CargarFichaPagina($array)
    {
        $sql="  SELECT f.fila,f.ficha,f.dni,f.paterno,f.materno,f.nombre,
                CONCAT(a.paterno,' ',a.materno,', ',a.nombres) recolector,
                f.conteo,f.tconteo,f.pagina_firma_id pagina,f.valida,
                IF( f.estado_firma<>'',
                    MostrarExistentes(f.estado_firma),''
                ) rst,f.rpaterno,f.rmaterno,f.rdni,f.rnombres,f.id
                FROM firmas f
                INNER JOIN escalafon_fichas ef ON ef.desdeh=f.pagina_firma_id
                INNER JOIN escalafon e ON e.id=ef.escalafon_id
                INNER JOIN activistas a ON a.id=e.activista_id
                WHERE f.estado=1
             ".$array['w'];
        $r=DB::select($sql);

        return $r;
    }

    public static function ListarFirmas($array)
    {
        $sql="SELECT id,fila,dni,paterno,materno,nombre,conteo,estado_firma
              FROM firmas
              WHERE estado=1
              AND valida=0";
        $sql.=$array['w'];
        $r=DB::select($sql);

        return $r;
    }

    public static function ListarFirmas2($array)
    {
        $sql="SELECT id,fila,dni,paterno,materno,nombre,conteo,estado_firma,conteo2,estado_firma2
              FROM firmas
              WHERE estado=1
              AND conteo2=0";
        $sql.=$array['w'];
        $r=DB::select($sql);

        return $r;
    }

    public static function ValidarReniec($array)
    {
        $sql="SELECT dni,paterno,materno,nombres
              FROM reniec
              WHERE estado=1";
        $sql.=$array["w"];
        $r=DB::select($sql);

        return $r;
    }

    public static function ConsolidadoFirmas($array)
    {
        $sql="  SELECT date(f.created_at) fecha,COUNT(DISTINCT(f.ficha)) fichas,COUNT(IF(f.conteo=3,f.id,NULL)) blancos,
                COUNT(IF(f.conteo=2 AND f.tconteo=4,f.id,NULL)) duplicado,COUNT(IF(f.conteo=2 AND f.tconteo!=4,f.id,NULL)) no_valido,
                COUNT(IF(f.conteo=2 OR f.conteo=3,f.id,NULL)) total_no_valido,10*COUNT(DISTINCT(f.ficha))-COUNT(IF( (f.conteo=2 AND f.tconteo!=4) OR f.conteo=3,f.id,NULL)) pago,
                COUNT(IF(f.conteo=1 AND f.tconteo=0,f.id,NULL)) valido,COUNT(IF(f.conteo=4,f.id,NULL)) subsanado,
                CONCAT(a.paterno,' ',a.materno,', ',a.nombres) operador,a.id,gp.nombre equipo
                FROM firmas f
                INNER JOIN escalafon_fichas ef ON ef.desdeh=f.pagina_firma_id
                INNER JOIN escalafon e ON e.id=ef.escalafon_id
                INNER JOIN grupos_personas gp ON gp.id=e.grupo_persona_id
                INNER JOIN activistas a ON a.id=e.activista_id
                INNER JOIN activistas a2 ON a2.id=f.usuario_created_at
                WHERE f.estado=1";
        $sql.= $array['w'];
        $sql.= "GROUP BY gp.id,a.id,DATE(f.created_at)
                ORDER BY a.paterno,a.materno,a.nombres,f.created_at";
        $r=DB::select($sql);

        return $r;
    }

    public static function DetalladoFirmas($array)
    {
        $sql="  SELECT date(f.created_at) fecha,f.ficha,COUNT(IF(f.conteo=3,f.id,NULL)) blancos,
                COUNT(IF(f.conteo=2 AND f.tconteo=4,f.id,NULL)) duplicado,COUNT(IF(f.conteo=2 AND f.tconteo!=4,f.id,NULL)) no_valido,
                COUNT(IF(f.conteo=2 OR f.conteo=3,f.id,NULL)) total,10-COUNT(IF( (f.conteo=2 AND f.tconteo!=4) OR f.conteo=3,f.id,NULL)) pago,
                COUNT(IF(f.conteo=1 AND f.tconteo=0,f.id,NULL)) valido,COUNT(IF(f.conteo=4,f.id,NULL)) subsanado,
                CONCAT(a.paterno,' ',a.materno,', ',a.nombres) operador,a.id,gp.nombre equipo
                FROM firmas f
                INNER JOIN escalafon_fichas ef ON ef.desdeh=f.pagina_firma_id
                INNER JOIN escalafon e ON e.id=ef.escalafon_id
                INNER JOIN grupos_personas gp ON gp.id=e.grupo_persona_id
                INNER JOIN activistas a ON a.id=e.activista_id
                INNER JOIN activistas a2 ON a2.id=f.usuario_created_at
                WHERE f.estado=1";
        $sql.= $array['w'];
        $sql.= "GROUP BY gp.id,a.id,DATE(f.created_at),f.ficha
                ORDER BY a.paterno,a.materno,a.nombres,f.created_at";
        $r=DB::select($sql);

        return $r;
    }

    public static function RegistrosFirmas($array)
    {
        $sql="  SELECT a2.id,CONCAT(a2.paterno,' ',a2.materno,', ',a2.nombres) digitador,DATE(f.created_at) fecha,
                COUNT(f.id) cant,COUNT(DISTINCT(f.pagina_firma_id)) paginas,COUNT(DISTINCT(f.ficha)) fichas,gp.nombre equipo,
                COUNT(DISTINCT(IF(f.conteo!=3,f.id,NULL))) firmas
                FROM firmas f
                INNER JOIN escalafon_fichas ef ON ef.desdeh=f.pagina_firma_id AND ef.estado=1
                INNER JOIN escalafon e ON e.id=ef.escalafon_id AND e.estado=1
                INNER JOIN grupos_personas gp ON gp.id=e.grupo_persona_id AND gp.estado=1
                INNER JOIN activistas a ON a.id=e.activista_id
                INNER JOIN activistas a2 ON a2.id=f.usuario_created_at
                WHERE f.estado=1";
        $sql.= $array['w'];
        $sql.= "GROUP BY a2.id,fecha,gp.id
                ORDER BY a.paterno,a.materno,a.nombres,fecha";
        $r=DB::select($sql);

        return $r;
    }

    public static function RegistrosFirmasG($array)
    {
        $fecha="";
        if($array['visualiza']==1){
            $fecha="DATE(f.created_at) fecha, ";
        }
        $sql="  SELECT ".$fecha."
                COUNT(f.id) cant,COUNT(DISTINCT(f.pagina_firma_id)) paginas,COUNT(DISTINCT(f.ficha)) fichas,gp.nombre equipo,
                COUNT(DISTINCT(IF(f.conteo!=3,f.id,NULL))) firmas
                FROM firmas f
                INNER JOIN escalafon_fichas ef ON ef.desdeh=f.pagina_firma_id AND ef.estado=1
                INNER JOIN escalafon e ON e.id=ef.escalafon_id AND e.estado=1
                INNER JOIN grupos_personas gp ON gp.id=e.grupo_persona_id AND gp.estado=1
                INNER JOIN activistas a ON a.id=e.activista_id
                INNER JOIN activistas a2 ON a2.id=f.usuario_created_at
                WHERE f.estado=1 ";
        $sql.= $array['w'];
                if($array['visualiza']==1){
        $sql.=" GROUP BY fecha,gp.id ";
        $sql.=" ORDER BY equipo,fecha";
                }
                else{
        $sql.=" GROUP BY gp.id ";
        $sql.=" ORDER BY equipo";
                }
        
        $r=DB::select($sql);

        return $r;
    }

    public static function DuplicadoFirmas($array)
    {
        $sql="  SELECT f.dni,CONCAT(f.paterno,' ',f.materno,', ',f.nombre) adherente,
                CONCAT(a.paterno,' ',a.materno,', ',a.nombres) operador,DATE(f.created_at) fecha,
                f.ficha,f.pagina_firma_id pagina, f.fila,gp.nombre equipo
                FROM firmas f
                INNER JOIN escalafon_fichas ef ON ef.desdeh=f.pagina_firma_id
                INNER JOIN escalafon e ON e.id=ef.escalafon_id
                INNER JOIN grupos_personas gp ON gp.id=e.grupo_persona_id
                INNER JOIN activistas a ON a.id=e.activista_id
                INNER JOIN activistas a2 ON a2.id=f.usuario_created_at
                WHERE f.estado=1
                AND f.conteo=2 AND f.tconteo=4 ";
        $sql.= $array['w'];
        $sql.= " ORDER BY f.dni,a.paterno,a.materno,a.nombres";
        $r=DB::select($sql);

        return $r;
    }

    public static function ValidaDNI($dni)
    {
        $sql="  SELECT dni,paterno,materno,nombres
                FROM reniec
                WHERE dni='$dni'";

        $sql2=" SELECT dni
                FROM firmas
                WHERE dni='$dni'";

        $sql3="  SELECT dni
                FROM reservas
                where dni='$dni'";

        $r=DB::select($sql);
        $r2=DB::select($sql2);
        $r3=DB::select($sql3);

        $mensaje='';
        if( count($r)>0 ){
            if( count($r2)>0 ){
                $mensaje='DNI no permitido; Ya Existe en las firmas^^0';
            }
            elseif( count($r3)>0 ){
                $mensaje='DNI no permitido; se encuentra reservado^^0';
            }
            else{
                $mensaje='DNI permitido.||MATERNO:  '.$r[0]->materno.'^^1';
            }
        }
        else{
            $mensaje='DNI No exite en BD de Reniec^^1';
        }

        return $mensaje;
    }

    public static function ReservaDNI($dni)
    {
        $reserva= new Reserva;
        $reserva->dni=$dni;
        //$reserva->usuario_created_at=Auth::user()->id;
        $reserva->save();

        $mensaje='El DNI '.$dni.' ha sido reservado.';

        return $mensaje;
    }
}
