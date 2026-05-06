<?php //Usuario
//contraseña
//base
//servidor



$sql_usuario= 'magil';
$sql_pass = 'Marcoo0';
$sql_servidor = '190.27.1.13\BI';
$sql_basedatosMF = 'dbdistribucion';
global $conexionMF;
$sql_infoMF = array('Database'=>$sql_basedatosMF, 'UID'=>$sql_usuario, 'PWD'=>$sql_pass);
$conexionMF =sqlsrv_connect($sql_servidor, $sql_infoMF) or die("no se establecio conexion");


$sql_basedatosRB = 'dbRoberts';
global $conexionRB;
$sql_infoRB = array('Database'=>$sql_basedatosRB, 'UID'=>$sql_usuario, 'PWD'=>$sql_pass);
$conexionRB =sqlsrv_connect($sql_servidor, $sql_infoRB) or die("no se establecio conexion");

$sql_basedatosHL = 'dbhighlife';
global $conexionHL;
$sql_infoHL = array('Database'=>$sql_basedatosHL, 'UID'=>$sql_usuario, 'PWD'=>$sql_pass);
$conexionHL =sqlsrv_connect($sql_servidor, $sql_infoHL) or die("no se establecio conexion");

$sql_basedatosLB = 'dbLamberti';
global $conexionLB;
$sql_infoLB = array('Database'=>$sql_basedatosLB, 'UID'=>$sql_usuario, 'PWD'=>$sql_pass);
$conexionLB =sqlsrv_connect($sql_servidor, $sql_infoLB) or die("no se establecio conexion");

$sql_basedatosMSDB = 'msdb';
global $conexionMSDB;
$sql_infoMSDB = array('Database'=>$sql_basedatosMSDB, 'UID'=>$sql_usuario, 'PWD'=>$sql_pass);
$conexionMSDB =sqlsrv_connect($sql_servidor, $sql_infoMSDB) or die("no se establecio conexion");


/*

$mysql_servername = "localhost";
$mysql_username = "root";
$mysql_database = "bi";
$mysql_password = "Marcooo0";
//  Create a new connection to the MySQL database using PDO
global $conn;
$conn = new mysqli($mysql_servername, $mysql_username, $mysql_password,$mysql_database);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
*/



 ?>
