<?php

namespace Core;

class DbModel extends Model
{
	//const	TABLE needs definition

	static public function handleCreation(DbModel $record,array $result)
	{
		if (array_key_exists("id",$result))
			$record->id	= $result["id"];
	}

	static public function instantiate(array $filling=array())
	{
		return new static($filling);
	}

	static public function executeCreate(DbModel $record)
	{
		$table			= static::TABLE;
		$values			= get_object_vars($record);
		$columns_sql	= implode(",",array_map(function($fieldName)
		{
			return "`${fieldName}`";
		},$values));
		$values_sql		= implode(",",array_map(function($fieldName)
		{
			return ":${fieldName}";
		},$values));
		$sql			= "INSERT INTO ${table}(${columns_sql}) VALUES (${values_sql})";

		$result	= Database::instance()->execute($sql,$values);
		self::handleCreation($record,$result);
	}

	static public function executeUpdate(DbModel $record):bool
	{
		$table		= static::TABLE;
		$values		= get_object_vars($record);
		$set_sql	= implode(",",array_map(function($fieldName)
		{
			return "`${fieldName}`=:${fieldName}";
		},array_keys($values)));
		$where_sql	= static::identityWhereSQL($record);
		$sql		= "UPDATE ${table} SET ${set_sql} WHERE ${where_sql}";

		$result	= Database::instance()->execute($sql,$values);
		return $result["rows"]>0;
	}

	static public function identityWhereSQL(DbModel $record):string
	{
		return "`id`=:id";
	}

	static public function queryAllMatchingParams(array $params)
	{
		$table	= static::TABLE;
		$sql	= "SELECT * FROM `${table}`";

		$where_sql	= implode(" AND ",array_map(function($fieldName)
		{
			return "`${fieldName}`=:${fieldName}";
		},array_keys($params)));
		if (!empty($where_sql))
			$sql	.= " WHERE ${where_sql}";
		return array_map([get_called_class(),"instantiate"],Database::instance()->query($sql,$params));
	}

	static public function existanceTest(DbModel $record):bool
	{
		return !empty($record->id);
	}

	public function save()
	{
		if (static::existanceTest($this))
			static::executeUpdate($record);
		else
			static::executeCreate($record);
	}
}