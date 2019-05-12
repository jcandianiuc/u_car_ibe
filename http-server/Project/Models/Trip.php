<?php

namespace Project\Models;

use DateTime;
use Core\Database;
use Core\DbModel;

class Trip extends DbModel
{
	const	TABLE	= "trip";

	const	FILLABLE_FIELD_NAMES	= ["id","markers"];
	const	REQUIRED_FIELD_NAMES	= ["datetime","markers","user_id",];
	
	const	DATETIME_FORMAT	= "Y-m-d H:i:s";
	const	ROLES			= ["driver","passenger"];

	const	MSG_ERR_DATE_EXPIRED			= 
				"Viajes al pasado prohibidos.";
	const	MSG_ERR_FIELD_REQUIRED			= 
				"Este campo es requisito.";
	const	MSG_ERR_INVALID_DATE_FORMAT		= 
				"El formato de fecha debe ser 'Y-m-d H:i:s'.";
	const	MSG_ERR_INVALID_MARKER			= 
				"Un marcador es inválido.";
	const	MSG_ERR_INVALID_ROLE			= 
				"Rol únicamente puede ser \"driver\" o \"passenger\".";
	const	MSG_ERR_PASSENGER_MARKERS_COUNT	=
				"Un pasajero sólo puede especificar un marcador.";

	private		$markers	= array();
	private		$id;
	protected	$role;
	public		$datetime;
	public		$to_uni;
	public		$user_id;

	private function updateMarkers()
	{
		foreach ($this->markers as $marker)
			$marker->trip_id	= $this->id;
	}

	public function getId():int{ return $this->id; }
	public function setId(int $id)
	{
		$this->id	= $id;
		$this->updateMarkers();
	}

	public function fetchMarkers():array
	{
		$this->setMarkers(Marker::queryAllOfTrip($this));
		return $this->getMarkers();
	}
	public function getMarkers():array{ return $this->markers; }
	public function setMarkers(array $markers=array())
	{
		$this->markers	= array_map(["Project\Models\Marker","normalization"],$markers);
		$this->updateMarkers();
	}

	public function getRole(){ return array_key_exists($this->role,self::ROLES)?self::ROLES[$this->role]:"?"; }
	public function setRole($role)
	{
		if (is_numeric($role))
			$this->role	= $role;
		else if (is_string($role)) {
			$roleCode	= array_search($role,self::ROLES);
			$this->role	= $roleCode===false?-1:$roleCode;
		} else
			throw new Exception("wrong-type");
	}

	public function fetchUser():User{ return empty($this->user_id)?null:User::queryWithId($this->user_id); }
	public function setUser(User $user)
	{
		if (empty($user->id))
			throw new Exception("no-user-id");
		$this->user_id	= $user->id;
	}


	public function circleDistance($lat1, $lon1, $lat2, $lon2) {
	  $rad = M_PI / 180;
	  return acos(sin($lat2*$rad) * sin($lat1*$rad) + cos($lat2*$rad) * cos($lat1*$rad) * cos($lon2*$rad - $lon1*$rad)) * 6371 * 1000;// Metros
	}


	//Funcion para comparar una coordenada con el conjunto de coordenadas de una ruta

	public function testMatch($marcador, $ruta , $distancemin){
	  foreach($ruta as $coord){
	    $dist= $this->circleDistance($marcador->latitude, $marcador->longitude, $coord->latitude, $coord->longitude);
	    if ($dist <= $distancemin) {
	      return TRUE;
	    }
	  }
	  return FALSE;
	}


	public function findProposal()
	{
		if ($this->role == "driver") {
			#Consulta obtener la ruta del conductor $this->id
			// $routedriver	= Marker::queryAllMatchingParamsInnerJoin([
			// 'marker.trip_id'		=> $this->id,
			// 'trip.role'		=> 0,
			// ], "trip");
			$trip_id = $this->id;
			$role= 0;
			$sql	= "SELECT * FROM `marker` INNER JOIN `trip` ON `marker`.`trip_id`=:trip_id AND `trip`.`role`=:role AND `trip`.`id`=:trip_id";
			$routedriver	= array_map(["Project\Models\Marker","normalization"],Database::instance()->query($sql,array(':trip_id' => $this->id, ':role' => $role)));


			print_r ($routedriver);
			print_r ("=========");
			if (empty($routedriver))
				throw new BadRequestException("wrong-credentials",self::MSG_ERR_INVALID_MARKER);
			else {
				#Consulta para obtener marcadores de pasajeros
				// $markerpassenger	= Marker::queryAllMatchingParamsInnerJoin([
				// 'trip.role'		=> 1,
				// ], "trip");
				// $trip_id = $this->id;
				// $role= 1;
				// $datetime1= $this->datetime;
				$sql	= <<<ENDOFQUERY
					SELECT	`proposal`.`id`					AS `trip_id`,
							`marker`.`latitude`				AS `latitude`,
							`marker`.`longitude`			AS `longitude`,
							`proposed`.`driver_status`,
							`proposed`.`passenger_status`

						FROM	`trip`				AS `proposal`
							LEFT JOIN	`match`		AS `proposed`
								ON	(`proposal`.`role`=0
										AND `proposal`.`id`=`proposed`.`driver_trip_id`
										AND `proposed`.`passenger_trip_id`=:trip_id
									)
									OR	(`proposal`.`id`=`proposed`.`passenger_trip_id`
											AND `proposed`.`driver_trip_id`=:trip_id)

							JOIN		`marker`
								ON	`proposal`.`id`=`marker`.`trip_id`

						WHERE	`proposal`.`role`!=:role
								AND	`proposal`.`to_uni`=:to_uni
								AND (`proposed`.`driver_trip_id` IS NULL
										OR	(`proposed`.`driver_status`!=2 AND `proposed`.`passenger_status`!=2)
								)
								AND ABS(TIMESTAMPDIFF(MINUTE,`proposal`.`datetime`,:datetime))<=30
ENDOFQUERY;
				//"SELECT * FROM `marker` INNER JOIN `trip` ON `trip`.`role`=:role AND `trip`.`datetime`=:datetime1 AND `marker`.`trip_id` =  `trip`.`id` AND `trip`.`to_uni`=:to_uni";
				$markerpassenger	= array_map(
					["Project\Models\Marker","normalization"],
					Database::instance()->query($sql,array(
						':trip_id'	=> $this->id,
						':datetime'	=> $this->datetime,
						':role'		=> $this->role,
						':to_uni'	=> $this->to_uni,
				)));

				print_r ($markerpassenger);
				if (empty($markerpassenger))
					throw new BadRequestException("wrong-credentials",self::MSG_ERR_INVALID_MARKER);
				else {
					foreach($markerpassenger as $marker){
						echo $this->testMatch($marker, $routedriver ,200)?"yes":"no";

					}


				}
			}
		}
		else{
			#Consulta para obtener marcador del pasajero $this->id


			#Consulta obtener las rutas de los conductores 


		}

		return null;
	}

	public function saveWithMarkers()
	{
		$pdo	= Database::instance()->getPdo();
		$pdo->beginTransaction();

		try {
			$this->save();
			foreach($this->markers as $marker)
				$marker->save();
			$pdo->commit(); // Guardar todo
		} catch (Exception $e) {
			$pdo->rollBack(); // Deshacer cambios
			throw $e;
		}
	}

	public function validation()
	{
		$errors	= array();

		foreach (self::REQUIRED_FIELD_NAMES as $fieldName)
			if (empty($this->$fieldName))
				$errors[]	= ['code'=>"required",'message'=>self::MSG_ERR_FIELD_REQUIRED,'data'=>$fieldName];

		if (!isset($this->to_uni))
			$errors[]	= ['code'=>"required",'message'=>self::MSG_ERR_FIELD_REQUIRED,'data'=>"to_uni"];

		if (!empty($this->datetime)) {
			$datetime	= DateTime::createFromFormat(self::DATETIME_FORMAT,$this->datetime);
			if (empty($datetime))
				$errors[]	= ['code'=>"invalid-format",'message'=>self::MSG_ERR_INVALID_DATE_FORMAT,'data'=>"datetime"];
			else if (!$this->exists()&&$datetime->diff(new DateTime)->s<0)
				$errors[]	= ['code'=>"date-expired",'message'=>self::MSG_ERR_DATE_EXPIRED,'data'=>"datetime"];
		}

		if (!isset($this->role))
			$errors[]	= ['code'=>"required",'message'=>self::MSG_ERR_FIELD_REQUIRED,'data'=>"role"];
		else if ($this->role<0||$this->role>=count(self::ROLES))
			$errors[]	= ['code'=>"invalid",'message'=>self::MSG_ERR_INVALID_ROLE,'data'=>"role"];

		if (!empty($this->role)&&!empty($this->markers))
			if ($this->role=="passenger"&&count($this->markers)>1)
				$errors[]	= ['code'=>"too-many-markers",'message'=>self::MSG_ERR_PASSENGER_MARKERS_COUNT,'data'=>"markers"];

		foreach ($this->markers as $index=>$marker) {
			$markerErrors	= $marker->validation();
			if (!empty($markerErrors))
				$errors[]	= ['code'=>"invalid-marker",'message'=>str_replace("[index]",$index,self::MSG_ERR_INVALID_MARKER),'data'=>['marker'=>$marker,'errors'=>$markerErrors]];
		}

		return $errors;
	}
}