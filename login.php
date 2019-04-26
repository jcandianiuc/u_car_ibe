<?php
function conectar(){
	//Variables para la base de datos
	$host = "localhost";
	$username = "";
	$password = "";
	$dbname= "";
	//Conectamos vía mysqli
	$conn = new mysqli($host, $username, $password, $dbname);
	//Verificamos si tenemos conexión
	if ($conn->connect_errno) {
		die();
	}
	else{
		return $conn;//Regresamos la variable de conexión
	}
}

$conn= conectar();

$usuario= $_POST['usuario'];
$contraseña= $_POST['contraseña'];

//$query= "INSERT INTO localizacion (latitud, longitud) VALUES('1', '1');";

//$query= "UPDATE localizacion SET latitud='$latitud', longitud='$longitud' WHERE id_localizacion='1';";

//$query= "SELECT * FROM Usuarios WHERE correo='a' AND pass ='a';";
//$query= "SELECT * FROM Usuarios WHERE correo='$correo';";
$query= "SELECT * FROM Usuarios WHERE usuario='$usuario';";
$r= $conn->query($query);

if ($r->num_rows === 0){
	echo "No";
	//echo $r;
}
else{
	$datos = $r->fetch_assoc();
    if (password_verify($contraseña, $datos['pass'])) {
		echo "Si";
	}
	else{
		echo "No";
	}
	
}

$conn->close();

?>
