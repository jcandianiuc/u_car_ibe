<?php

namespace Project\Models;

use DateTime;
use Core\Database;
use Core\DbModel;

class Trip extends DbModel
{
	const	TABLE	= "trip";

	const	FILLABLE_FIELD_NAMES	= ["id","markers"];
	const	REQUIRED_FIELD_NAMES	= ["datetime","markers","role","user_id",];
	
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

	private	$markers	= array();
	private	$id;
	public	$datetime;
	public	$role;
	public	$to_uni;
	public	$user_id;

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

	public function fetchUser():User{ return empty($this->user_id)?null:User::queryWithId($this->user_id); }
	public function setUser(User $user)
	{
		if (empty($user->id))
			throw new Exception("no-user-id");
		$this->user_id	= $user->id;
	}

	public function findProposal()
	{
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

		if (!empty($this->role)&&!in_array($this->role,self::ROLES))
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