<?php
include_once "conect.php";

global $conexionMF;
$rawdata=array();

$json_file_mf="C:/Users/administrator.HABERSDOM/Desktop/demo/Friedman/json_error_mf_diario.json";
$json_file_rb="C:/Users/administrator.HABERSDOM/Desktop/demo/Friedman/json_error_ro_diario.json";
$json_file_hl="C:/Users/administrator.HABERSDOM/Desktop/demo/Friedman/json_error_hl_diario.json";
$json_file_bg="C:/Users/administrator.HABERSDOM/Desktop/demo/Friedman/json_error_bm_diario.json";

//C:/Users/administrator.HABERSDOM/Desktop/demo/Friedman/

function execonsultasqlsrv($query,$conect,$res){
    $i=0;
    if(!$result =sqlsrv_query($conect, $query)) {
        die();
        //echo "Error al registrarse";

    }


//Resultado de consulta
    while($row = sqlsrv_fetch_array($result))

    {





        $res[$i]=$row;

        $i++;







    }




    return $res;
}
function errores($content,$data_sql){
    $error=array();
    for($i=0;$i<count($content);$i++ ){

  //      for ($j=0;$j<count($data_sql);$j++  ){


            foreach ($content[$i] as $key => $value ){


                //if($key=='consulta' and $value == $data_sql[$j]['conver'] ){
                // $fec =  $data_sql[$j]['fecha'];


                $error[$i]['fecha']=$value;






                //}



            }





        foreach ($content[$i]['error'] as $key1   ){
            $error[$i][]=$key1;

        }




      //  }

    }


    return $error;

}
function tabla($error){
    $tabla="";
    $tabla.='<table class="table table-hover"><thead class="thead-dark" ><tr> <th>Fecha</th><th>Error</th></tr></thead><tbody> ';
    for ($i=0;$i<count($error);$i++){






        for ($j=1;$j<count($error[0]);$j++){

            $tabla.="<tr> <td>" .$error[$i]['fecha'] ."</td>";
            $tabla.= "<td>" .$error[$i][$j-1][0]."<td/><tr/>";
        }





    }
    $tabla.="</tbody><table/>";

    return $tabla;

}

$archivo_mf= file_get_contents($json_file_mf);
$content_mf = json_decode($archivo_mf, true);

$archivo_rb= file_get_contents($json_file_rb);
$content_rb = json_decode($archivo_rb, true);

$archivo_hl= file_get_contents($json_file_hl);
$content_hl = json_decode($archivo_hl, true);

$archivo_bg= file_get_contents($json_file_bg);
$content_bg = json_decode($archivo_bg, true);


$sql_query=utf8_decode("
SET NOCOUNT ON
Declare @FECI date
DECLARE @TABL table (fecha date, conver varchar(20))

 

set @FECI='20130101'

 

while @FECI <= convert(date,getdate(),103)
begin
insert into @TABL values (@FECI, convert(varchar,YEAR(@FECI))+'|'+convert(varchar,DATEPART(WEEK,@FECI))+'|'+convert(varchar,DATEPART(WEEKDAY,@FECI)) )
set @FECI=dateadd(dd,1,@FECI)
end

 

SET NOCOUNT OFF
select convert(varchar,fecha)fecha,conver from @TABL


");




$rawdata=execonsultasqlsrv($sql_query,$conexionMF,$rawdata);




$error_mf=errores($content_mf,$rawdata);
$tabla_mf=tabla($error_mf);

$error_rb=errores($content_rb,$rawdata);
$tabla_rb=tabla($error_rb);

$error_hl=errores($content_hl,$rawdata);
$tabla_hl=tabla($error_hl);

$error_bg=errores($content_bg,$rawdata);
$tabla_bg=tabla($error_bg);



$estructura=array( "mf"=> $tabla_mf,
    "rb"=> $tabla_rb,
    "hl"=> $tabla_hl,
    "bg"=> $tabla_bg);


$estructura=json_encode($estructura);

echo $estructura;