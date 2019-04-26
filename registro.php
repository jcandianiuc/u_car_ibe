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
		//echo "popo";
		return $conn;//Regresamos la variable de conexión
	}
}

$conn= conectar();

$correo= $_POST['correo'];
$usuario= $_POST['usuario'];
$contraseña= $_POST['contraseña'];
$contraseña= password_hash($contraseña, PASSWORD_DEFAULT);

//$query= "INSERT INTO localizacion (latitud, longitud) VALUES('1', '1');";

//$query= "UPDATE localizacion SET latitud='$latitud', longitud='$longitud' WHERE id_localizacion='1';";

$query= "SELECT * FROM Usuarios WHERE correo='$correo';"; //OR usuario= '$usuario';";
$query2= "SELECT * FROM Usuarios WHERE usuario='$usuario';";
//echo $query;
$r= $conn->query($query);
$r2= $conn->query($query2);

if (($r->num_rows === 0) and ($r2->num_rows === 0)){

	$query= "INSERT INTO Usuarios(correo, usuario, pass) VALUES('$correo','$usuario','$contraseña');";
	if($r= $conn->query($query) === TRUE){
		
		$query= "INSERT INTO Coordenadas(usuario, latitud, longitud) VALUES('$usuario', '0', '0');";
		if($r= $conn->query($query) === TRUE){
			
			echo "Si";
			$conn->close();
		}
		else{
			
			$conn->close();
		}
	}
	else{
	    
	    echo "No";
	}

}
else{
	echo "No";
	$conn->close();
}



?>