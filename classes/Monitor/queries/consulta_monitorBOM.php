<?php
require_once __DIR__ . '/../../../config/config.php';

// Cambiamos la conexión a 'BG'
$ds = Database::getConnection('BG');
$resultadoLB = [];
$diferencia = [];

$cons_mon = "select proceso, fecha from Hist_procesos 
             where convert(date, Fecha, 103) = convert(date, GETDATE(), 103) 
             order by fecha";

$stmt = $ds->query($cons_mon);
$rows = $stmt->fetchAll(PDO::FETCH_NUM);

$filasLB = count($rows);

if ($filasLB == 0) {
    $estructuraLB = ""; 
} else {

    for ($i = 0; $i < $filasLB; $i++) {
        $proceso = $rows[$i][0];
        $fechaActual = date_create($rows[$i][1]);

        // 1. Lógica para $resultadoLB
        $resultadoLB[$i][0] = $proceso;
        $resultadoLB[$i][1] = date_format($fechaActual, 'j');
        $resultadoLB[$i][2] = intval(date_format($fechaActual, 'n')) - 1; // Mes base 0 para JS
        $resultadoLB[$i][3] = date_format($fechaActual, 'Y');
        $resultadoLB[$i][4] = date_format($fechaActual, 'G');
        $resultadoLB[$i][5] = intval(date_format($fechaActual, 'i'));
        $resultadoLB[$i][6] = intval(date_format($fechaActual, 's'));

        // 2. Lógica para $diferencia
        $diferencia[$i][0] = $proceso;
        $diferencia[$i][2] = date_format($fechaActual, 'Y/m/d H:i:s');

        if ($i == 0) {
            $diferencia[$i][1] = date_format(date_create('00:00:00'), 'Y/m/d H:i:s');
        } else {
            $diferencia[$i][1] = date_format(date_create($rows[$i - 1][1]), 'Y/m/d H:i:s');
        }
    }

    // Generación de estructura JSON/Gantt para LB
    $estructuraLB = "{";
    $estructuraLB .= "name:'Boggi Milano',id:'lb',parent:'plan',";
    $estructuraLB .= "start:Date.UTC(" . $resultadoLB[0][3] . "," . $resultadoLB[0][2] . "," . $resultadoLB[0][1] . ",0,0,0),";
    $estructuraLB .= "end:Date.UTC(" . $resultadoLB[0][3] . "," . $resultadoLB[0][2] . "," . $resultadoLB[0][1] . "," . $resultadoLB[$filasLB - 1][4] . "," . $resultadoLB[$filasLB - 1][5] . "," . $resultadoLB[$filasLB - 1][6] . ")";
    $estructuraLB .= "},";

    for ($i = 0; $i < count($resultadoLB); $i++) {
        $estructuraLB .= "{";
        $estructuraLB .= "name:'" . $resultadoLB[$i][0] . "',";
        $estructuraLB .= "id:'" . $resultadoLB[$i][0] . "lb',";
        $estructuraLB .= "parent:'lb',";

        if ($i > 0) {
            $estructuraLB .= "dependency:'" . $resultadoLB[$i - 1][0] . "lb',";
            $inicioH = $resultadoLB[$i - 1][4];
            $inicioM = $resultadoLB[$i - 1][5];
            $inicioS = $resultadoLB[$i - 1][6];
        } else {
            $inicioH = 0; $inicioM = 0; $inicioS = 0;
        }

        $estructuraLB .= "start:Date.UTC(" . $resultadoLB[$i][3] . "," . $resultadoLB[$i][2] . "," . $resultadoLB[$i][1] . "," . $inicioH . "," . $inicioM . "," . $inicioS . "),";
        $estructuraLB .= "end:Date.UTC(" . $resultadoLB[$i][3] . "," . $resultadoLB[$i][2] . "," . $resultadoLB[$i][1] . "," . $resultadoLB[$i][4] . "," . $resultadoLB[$i][5] . "," . $resultadoLB[$i][6] . ")";
        $estructuraLB .= "},";
    }

    $estructuraLB = rtrim($estructuraLB, ",");
}
?>