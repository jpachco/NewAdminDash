<?php
require_once __DIR__ . '/../../../config/config.php';

// Cambiamos la conexión a 'HL'
$ds = Database::getConnection('HL');
$resultadoHL = [];
$diferencia = [];

$cons_mon = "select proceso, fecha from Hist_procesos 
             where convert(date, Fecha, 103) = convert(date, GETDATE(), 103) 
             order by fecha";

$stmt = $ds->query($cons_mon);
$rows = $stmt->fetchAll(PDO::FETCH_NUM);

$filasHL = count($rows);

if ($filasHL == 0) {
    $estructuraHL = ""; 
} else {

    for ($i = 0; $i < $filasHL; $i++) {
        $proceso = $rows[$i][0];
        $fechaActual = date_create($rows[$i][1]);

        // 1. Lógica para $resultadoHL (Cambiado a HL)
        $resultadoHL[$i][0] = $proceso;
        $resultadoHL[$i][1] = date_format($fechaActual, 'j');
        $resultadoHL[$i][2] = intval(date_format($fechaActual, 'n')) - 1; // Mes base 0 para JS
        $resultadoHL[$i][3] = date_format($fechaActual, 'Y');
        $resultadoHL[$i][4] = date_format($fechaActual, 'G');
        $resultadoHL[$i][5] = intval(date_format($fechaActual, 'i'));
        $resultadoHL[$i][6] = intval(date_format($fechaActual, 's'));

        // 2. Lógica para $diferencia
        $diferencia[$i][0] = $proceso;
        $diferencia[$i][2] = date_format($fechaActual, 'Y/m/d H:i:s');

        if ($i == 0) {
            $diferencia[$i][1] = date_format(date_create('00:00:00'), 'Y/m/d H:i:s');
        } else {
            $diferencia[$i][1] = date_format(date_create($rows[$i - 1][1]), 'Y/m/d H:i:s');
        }
    }

    // Generación de estructura JSON/Gantt para HL
    $estructuraHL = "{";
    $estructuraHL .= "name:'High Life',id:'hl',parent:'plan',";
    $estructuraHL .= "start:Date.UTC(" . $resultadoHL[0][3] . "," . $resultadoHL[0][2] . "," . $resultadoHL[0][1] . ",0,0,0),";
    $estructuraHL .= "end:Date.UTC(" . $resultadoHL[0][3] . "," . $resultadoHL[0][2] . "," . $resultadoHL[0][1] . "," . $resultadoHL[$filasHL - 1][4] . "," . $resultadoHL[$filasHL - 1][5] . "," . $resultadoHL[$filasHL - 1][6] . ")";
    $estructuraHL .= "},";

    for ($i = 0; $i < count($resultadoHL); $i++) {
        $estructuraHL .= "{";
        $estructuraHL .= "name:'" . $resultadoHL[$i][0] . "',";
        $estructuraHL .= "id:'" . $resultadoHL[$i][0] . "hl',";
        $estructuraHL .= "parent:'hl',";

        if ($i > 0) {
            $estructuraHL .= "dependency:'" . $resultadoHL[$i - 1][0] . "hl',";
            $inicioH = $resultadoHL[$i - 1][4];
            $inicioM = $resultadoHL[$i - 1][5];
            $inicioS = $resultadoHL[$i - 1][6];
        } else {
            $inicioH = 0; $inicioM = 0; $inicioS = 0;
        }

        $estructuraHL .= "start:Date.UTC(" . $resultadoHL[$i][3] . "," . $resultadoHL[$i][2] . "," . $resultadoHL[$i][1] . "," . $inicioH . "," . $inicioM . "," . $inicioS . "),";
        $estructuraHL .= "end:Date.UTC(" . $resultadoHL[$i][3] . "," . $resultadoHL[$i][2] . "," . $resultadoHL[$i][1] . "," . $resultadoHL[$i][4] . "," . $resultadoHL[$i][5] . "," . $resultadoHL[$i][6] . ")";
        $estructuraHL .= "},";
    }

    $estructuraHL = rtrim($estructuraHL, ",");
}
?>