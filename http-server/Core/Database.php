<?php

namespace Core;

use PDO;
use Exception;

class Database
{
	static private $instance;

	protected	$pdo;

	public function __construct(string $driver,array $params,string $username,string $password)
	{
		$this->pdo	= new PDO(self::dsnForDriverWithParams($driver,$params),$username,$password);
		$this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
	}

	static public function dsnForDriverWithParams(string $driver,array $params=array()):string
	{
		$paramsString	= implode(";",array_map(function(string $key,string $value)
		{
			return "${key}=${value}}";
		},array_keys($params),array_values($params)));
		return empty($paramsString)?$driver:"${driver}:${paramsString}";
	}

	final static public function instance():Database
	{
		$proyectConfig	= uCARibe::app()->config;
		if (empty(self::$instance)) {
			if (!isset(
				$proyectConfig["db-driver"],
				$proyectConfig["db-host"],
				$proyectConfig["db-name"],
				$proyectConfig["db-user"],
				$proyectConfig["db-pass"]
			))
				throw new Exception("incomplete-default-db-config");
			self::$instance	= new self($proyectConfig["db-driver"],[
				'dbname'	=> $proyectConfig["db-name"],
				'host'		=> $proyectConfig["db-host"],
			],$proyectConfig["db-user"],$proyectConfig["db-pass"]);
		}
		return self::$instance;
	}

	public function getPDO():PDO{ return $this->pdo; }

	public function execute(string $sql,array $params=array()):array
	{
		$query	= $this->pdo->prepare($sql);
		$result	= $query->execute($params);

		$rows	= $query->rowCount($params);
		$id		= $this->pdo->lastInsertId();

		if ($result)
			return compact("rows","id");
		else {
			$error	= $query->errorInfo();
			throw new Exception("${error[2]}");
		}
	}

	public function query(string $sql,array $params=array()):array
	{
		$query	= $this->pdo->prepare($sql);
		$result	= $query->execute($params);
		
		if ($result)
			return $query->fetchAll();
		else {
			$error	= $query->errorInfo();
			throw new Exception("${error[2]}");
		}
	}
}