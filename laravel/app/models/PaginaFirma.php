<?php

class PaginaFirma extends Base
{
    public $table = "paginafirma";

    public static function PaginasRegistradas($limit,$offset)
    {
        $datos= PaginaFirma::where('estado','=','1')
                ->offset($offset)
                ->limit($limit)
                ->orderBy('id','DESC')
                ->get();
        return $datos;
    }

    public static function PaginasPendientesD()
    {
        $datos=PaginaFirma::where('estado','=','2')->get();
        return $datos;
    }

    public static function PaginasPendientes($r)
    {
        $datos=PaginaFirma::where('estado','=','2')->get();

        $r['rst']='1';
        $r['msj']='Se listaron <b>'.count($datos).'</b> página(s)';
        $r['pentot']=$datos;
        return $r;
    }
    public static function PaginasPendientesDosCientos($r)
    {
        //$set=DB::select('SET GLOBAL group_concat_max_len = 2048');
        $sql="  SELECT min(id) inicio,if(id%200=0,id-id%200,id-id%200+200) fin, 
                max(id) maximo, GROUP_CONCAT( IF(estado=2,id,NULL) ) vacios
                FROM paginafirma
                GROUP BY fin";
        $datos=DB::select($sql);

        $r['pen200']=$datos;
        return $r;
    }

    public static function LimpiarNuevamente()
    {
        $sql="SELECT Eliminados();";

        $dd=DB::select($sql);
    }
}
