<?php

namespace Project\Models;

use Core\DbModel;

class Marker extends DbModel
{
	const	TABLE	= "marker";

	const	REQUIRED_FIELD_NAMES	= ["latitude","longitude"];

	const	MSG_ERR_FIELD_REQUIRED	= "Este campo es requisito.";

	protected	$id;
	public		$trip_id;
	public		$latitude;
	public		$longitude;

	static public function queryAllOfTrip(Trip $trip)
	{
		return self::queryAllMatchingParams(['trip_id'=>$trip->id]);
	}

	public function validation()
	{
		$errors	= array();

		foreach (self::REQUIRED_FIELD_NAMES as $fieldName)
			if (empty($this->$fieldName))
				$errors[]	= ['code'=>"required",'message'=>self::MSG_ERR_FIELD_REQUIRED,'data'=>$fieldName];

		return $errors;
	}
}