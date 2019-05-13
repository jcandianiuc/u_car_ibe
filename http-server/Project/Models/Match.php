<?php

namespace Project\Models;

use Core\DbModel;
use Core\Database;

class Match extends DbModel
{
	const	TABLE	= "match";

	const	REQUIRED_FIELD_NAMES	= ["driver_trip_id","passenger_trip_id",];
	const	STATUS_FIELD_NAMES		= ["driver_status","passenger_status",];

	const	STATUSES	= ["pending","accepted","rejected"];

	const	MSG_ERR_FIELD_REQUIRED	= "Este campo es requisito.";
	const	MSG_ERR_INVALID_STATUS	= "Valor inválido";

	protected	$driver_trip_id;
	protected	$passenger_trip_id;
	protected	$driver_status;
	protected	$passenger_status;

	static public function handleCreation(DbModel $match)
	{
		// no se necesita hacer nada ya que los ids se especificaron manualmente
	}

	static public function existanceTest(DbModel $match)
	{
		return ($match instanceof Match)&&!empty(Match::queryAllMatchingParams([
			"driver_trip_id"	=> $match->driver_trip_id,
			"passenger_trip_id"	=> $match->passenger_trip_id,
		]));
	}

	public function getDriver_status()
	{
		return array_key_exists($this->driver_status,self::STATUSES)?self::STATUSES[$this->driver_status]:"?";
	}
	public function setDriver_status($status)
	{
		if (is_numeric($status))
			$this->driver_status	= $status;
		else if (is_string($status)) {
			$statusCode				= array_search($status,self::STATUSES);
			$this->driver_status	= $statusCode===false?-1:$statusCode;
		} else
			throw new Exception("wrong-type");
	}

	public function getPassenger_status()
	{
		return array_key_exists($this->passenger_status,self::STATUSES)?self::STATUSES[$this->passenger_status]:"?";
	}
	public function setPassenger_status($status)
	{
		if (is_numeric($status))
			$this->passenger_status	= $status;
		else if (is_string($status)) {
			$statusCode				= array_search($status,self::STATUSES);
			$this->passenger_status	= $statusCode===false?-1:$statusCode;
		} else
			throw new Exception("wrong-type");
	}

	public function validation()
	{
		$errors	= array();

		foreach (self::REQUIRED_FIELD_NAMES as $fieldName)
			if (empty($this->$fieldName))
				$errors[]	= ['code'=>"required",'message'=>self::MSG_ERR_FIELD_REQUIRED,'data'=>$fieldName];

		foreach (self::STATUS_FIELD_NAMES as $fieldName)
			if (!isset($this->$fieldName))
				$errors[]	= ['code'=>"required",'message'=>self::MSG_ERR_FIELD_REQUIRED,'data'=>$fieldName];
			else if ($this->$fieldName<0||count(self::STATUSES))
				$errors[]	= ['code'=>"invalid-status",'message'=>self::MSG_ERR_INVALID_STATUS,'data'=>$fieldName];

		return $errors;
	}

	public function save()
	{
		parent::save();
	}
}