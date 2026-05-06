<?php
require_once __DIR__ . '/../../../config/config.php';

// Cambiamos la conexión a 'RB'
$ds = Database::getConnection('RB');
$resultadoRB = [];
$diferencia = [];

$cons_mon = "select proceso, fecha from Hist_procesos 
             where convert(date, Fecha, 103) = convert(date, GETDATE(), 103) 
             order by fecha";

$stmt = $ds->query($cons_mon);
$rows = $stmt->fetchAll(PDO::FETCH_NUM);

$filasRB = count($rows);

if ($filasRB == 0) {
    // Si prefieres que no muera el script completo, puedes dejarlo como string vacío
    $estructuraRB = ""; 
} else {

    for ($i = 0; $i < $filasRB; $i++) {
        $proceso = $rows[$i][0];
        $fechaActual = date_create($rows[$i][1]);

        // 1. Lógica para $resultadoRB (Cambiado de MF a RB)
        $resultadoRB[$i][0] = $proceso;
        $resultadoRB[$i][1] = date_format($fechaActual, 'j');
        $resultadoRB[$i][2] = intval(date_format($fechaActual, 'n')) - 1;
        $resultadoRB[$i][3] = date_format($fechaActual, 'Y');
        $resultadoRB[$i][4] = date_format($fechaActual, 'G');
        $resultadoRB[$i][5] = intval(date_format($fechaActual, 'i'));
        $resultadoRB[$i][6] = intval(date_format($fechaActual, 's'));

        // 2. Lógica para $diferencia
        $diferencia[$i][0] = $proceso;
        $diferencia[$i][2] = date_format($fechaActual, 'Y/m/d H:i:s');

        if ($i == 0) {
            $diferencia[$i][1] = date_format(date_create('00:00:00'), 'Y/m/d H:i:s');
        } else {
            $diferencia[$i][1] = date_format(date_create($rows[$i - 1][1]), 'Y/m/d H:i:s');
        }
    }

    // Generación de estructura JSON/Gantt para RB
    $estructuraRB = "{";
    $estructuraRB .= "name:'Roberts',id:'rb',parent:'plan',";
    $estructuraRB .= "start:Date.UTC(" . $resultadoRB[0][3] . "," . $resultadoRB[0][2] . "," . $resultadoRB[0][1] . ",0,0,0),";
    $estructuraRB .= "end:Date.UTC(" . $resultadoRB[0][3] . "," . $resultadoRB[0][2] . "," . $resultadoRB[0][1] . "," . $resultadoRB[$filasRB - 1][4] . "," . $resultadoRB[$filasRB - 1][5] . "," . $resultadoRB[$filasRB - 1][6] . ")";
    $estructuraRB .= "},";

    for ($i = 0; $i < count($resultadoRB); $i++) {
        $estructuraRB .= "{";
        $estructuraRB .= "name:'" . $resultadoRB[$i][0] . "',";
        $estructuraRB .= "id:'" . $resultadoRB[$i][0] . "rb',";
        $estructuraRB .= "parent:'rb',";

        if ($i > 0) {
            $estructuraRB .= "dependency:'" . $resultadoRB[$i - 1][0] . "rb',";
            $inicioH = $resultadoRB[$i - 1][4];
            $inicioM = $resultadoRB[$i - 1][5];
            $inicioS = $resultadoRB[$i - 1][6];
        } else {
            $inicioH = 0; $inicioM = 0; $inicioS = 0;
        }

        $estructuraRB .= "start:Date.UTC(" . $resultadoRB[$i][3] . "," . $resultadoRB[$i][2] . "," . $resultadoRB[$i][1] . "," . $inicioH . "," . $inicioM . "," . $inicioS . "),";
        $estructuraRB .= "end:Date.UTC(" . $resultadoRB[$i][3] . "," . $resultadoRB[$i][2] . "," . $resultadoRB[$i][1] . "," . $resultadoRB[$i][4] . "," . $resultadoRB[$i][5] . "," . $resultadoRB[$i][6] . ")";
        $estructuraRB .= "},";
    }

    $estructuraRB = rtrim($estructuraRB, ",");
}
?>