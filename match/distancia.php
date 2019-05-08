<?php

include_once('funciones_distancia.php');

//Prueba obtener distancia

// echo distance(32.9697, -96.80322, 29.46786, -98.53506) . " Metros<br>";
// echo haversineGreatCircleDistance(32.9697, -96.80322, 29.46786, -98.53506). " Metros Haversine<br>";
// echo vincentyGreatCircleDistance(32.9697, -96.80322, 29.46786, -98.53506). " Metros Vincenty<br>";
// echo codexworldGetDistanceOpt(32.9697, -96.80322, 29.46786, -98.53506). " Metros Worldgetdistance<br>";
// echo circle_distance(32.9697, -96.80322, 29.46786, -98.53506). " Metros Circle_distance<br>";


//Prueba Funcion para comparar una coordenada con el conjunto de coordenadas de una ruta

$ruta = array(
    array(
         "latitud" => "32.9697",
         "longitud" => "-96.80451",
    ),
    array(
         "latitud" => "32.9697",
         "longitud" => "-96.80251",
    ),
);


$marcador = array(
   "latitud" => "32.9687",
   "longitud" => "-96.80222",
);

//La funcion se manda el marcador y la ruta, ademas se le da la distancia minima en metros que debe estar alejada el marcador de la ruta para que sea buena en metros
echo ruta_marcador($marcador, $ruta, 250);



?>