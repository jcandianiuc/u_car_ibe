<?php

namespace Project\Models;

use Core\Database;
use Core\Model;

class Proposal extends Model
{
	private		$trip_id;
	private		$trip_role;
	private		$user_id;
	protected	$id;
	protected	$contact		= null;
	protected	$markers		= null;
	protected	$other_status	= 0;
	protected	$own_status		= 0;

	public function __construct(int $user_id,int $trip_id,array $filling=array())
	{
		$this->user_id	= $user_id;
		$this->trip_id	= $trip_id;
		parent::__construct($filling);
	}

	static private function handleResultSet(array $resultset,int $user_id,int $trip_id)
	{
		if (empty($resultset))
			return null;
		else {
			$data		= $resultset[0];
			$markers	= array_map(["Project\Models\Marker","normalization"],json_decode($data['markers']));
			return new Proposal($user_id,$trip_id,array_merge($data,compact("markers")));
		}
	}

	static public function queryWithIds(array $params)
	{
		if (!isset($params['trip_id'],$params['user_id'],$params['proposal_id']))
			throw new Exception("missing-parameters");
		$sql	= <<<ENDOFQUERY
			SELECT  `trip`.`role`															AS	`trip_role`,
					IF(`trip`.`role`=0,`match`.`driver_status`,`match`.`passenger_status`)	AS  `own_status`,
					IF(`trip`.`role`=0,`match`.`passenger_status`,`match`.`driver_status`)	AS  `other_status`,
					`contact`.`name`														AS  `contact_name`,
					CONCAT(`contact`.`id`,"@ucaribe.edu.mx")								AS  `contact_email`,
					`contact`.`phone`														AS  `contact_phone`,
					CONCAT("[",GROUP_CONCAT(
						CONCAT("[",`marker`.`latitude`,",",`marker`.`longitude`,"]")
					),"]")																	AS  `markers`
				
				FROM		`trip`
					JOIN	`match`
						ON	(`trip`.`role`=0 AND `trip`.`id`=`match`.`driver_trip_id`)
							OR	(`trip`.`id`=`match`.`passenger_trip_id`)
					JOIN	`trip`		AS `proposal`
						ON	(`trip`.`role`=0 AND `match`.`passenger_trip_id`=`proposal`.`id`)
							OR	(`trip`.`role`=1 AND `match`.`driver_trip_id`=`proposal`.`id`)
					LEFT JOIN `marker`
						ON	(`match`.`driver_status`!=2 AND `match`.`passenger_status`!=2 AND `proposal`.`id`=`marker`.`trip_id`)
					LEFT JOIN `user`	AS `contact`
						ON	(`match`.`driver_status`=1 AND `match`.`passenger_status`=1 AND `proposal`.`user_id`=`contact`.`id`)

				WHERE	`trip`.`id`=:trip_id
						AND `trip`.`user_id`=:user_id
						AND ((`trip`.`role`=0 AND `match`.`passenger_trip_id`=:proposal_id)
							OR `match`.`driver_trip_id`=:proposal_id
						)

				GROUP BY `match`.`driver_trip_id`,`match`.`passenger_trip_id`
ENDOFQUERY;
		
		return self::handleResultSet(
			Database::instance()->query($sql,$params),
			$params['user_id'],
			$params['trip_id']
		);
	}

	static public function queryActualWithTripIdAndUserId(int $trip_id,int $user_id)
	{
		$sql	= <<<ENDOFQUERY
			SELECT  `trip`.`role`															AS	`trip_role`,
					IF(`trip`.`role`=0,`match`.`driver_status`,`match`.`passenger_status`)	AS  `own_status`,
					IF(`trip`.`role`=0,`match`.`passenger_status`,`match`.`driver_status`)	AS  `other_status`,
					`contact`.`name`														AS  `contact_name`,
					CONCAT(`contact`.`id`,"@ucaribe.edu.mx")								AS  `contact_email`,
					`contact`.`phone`														AS  `contact_phone`,
					CONCAT("[",GROUP_CONCAT(
						CONCAT("[",`marker`.`latitude`,",",`marker`.`longitude`,"]")
					),"]")																	AS  `markers`
				
				FROM		`trip`
					JOIN	`match`
						ON	(`trip`.`role`=0 AND `trip`.`id`=`match`.`driver_trip_id`)
							OR	(`trip`.`id`=`match`.`passenger_trip_id`)
					JOIN	`trip`		AS `proposal`
						ON	(`trip`.`role`=0 AND `match`.`passenger_trip_id`=`proposal`.`id`)
							OR	(`trip`.`role`=1 AND `match`.`driver_trip_id`=`proposal`.`id`)
					LEFT JOIN `marker`
						ON	(`proposal`.`id`=`marker`.`trip_id`)
					LEFT JOIN `user`	AS `contact`
						ON	(`match`.`driver_status`=1 AND `match`.`passenger_status`=1 AND `proposal`.`user_id`=`contact`.`id`)

				WHERE	`trip`.`id`=:trip_id
						AND `trip`.`user_id`=:user_id
						AND (`match`.`driver_status`!=2 AND `match`.`passenger_status`!=2)

				GROUP BY `match`.`driver_trip_id`,`match`.`passenger_trip_id`
ENDOFQUERY;

		return self::handleResultSet(
			Database::instance()->query($sql,compact("trip_id","user_id")),
			$user_id,
			$trip_id
		);
	}

	public function getMatch()
	{
		if (!isset($this->trip_role))
			throw new Exception("indeterminate-role");
		$match	= new Match([
			'driver_trip_id'	=> $this->trip_role==0?$this->trip_id:$this->id,
			'passenger_trip_id'	=> $this->trip_role==0?$this->id:$this->trip_id,
			'driver_status'		=> $this->trip_role==0?$this->own_status:$this->other_status,
			'passenger_status'	=> $this->trip_role==0?$this->other_status:$this->own_status,
		]);
		return $match;
	}

	public function getStatus()
	{
		if ($this->own_status==2||$this->other_status==2)
			return "rejected";
		else if ($this->own_status==0)
			return "pending";
		else if ($this->own_status==1&&$this->other_status==0)
			return "accepted-pending";
		else
			return "accepted";
	}

	public function getTrip_id(){ return $this->trip_id; }
	public function getUser_id(){ return $this->user_id; }

	public function fill(array $filling):void
	{
		$contact_keys	= array_filter(array_keys($filling),function($key)
		{
			return strpos($key,"contact_")===0;
		});
		if (!empty($contact_keys)) {
			if (empty($this->contact))
				$this->contact	= array();
			foreach ($contact_keys as $original_key) {
				$contact_key					= substr($original_key,8);
				$this->contact[$contact_key]	= $filling[$original_key];
			}
		}

		if (isset($filling['trip_role']))
			$this->trip_role	= $filling['trip_role'];

		parent::fill($filling);
	}

	public function save()
	{
		$this->getMatch()->save();
	}

	public function serialize():array
	{
		return [
			'id'		=> $this->id,
			'status'	=> $this->getStatus(),
			'markers'	=> $this->markers,
			'contact'	=> $this->contact,
		];
	}
}