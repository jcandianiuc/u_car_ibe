<?php

namespace Core;

class DbModel extends Model
{
	//const	TABLE needs definition

	static protected function handleCreation(self $record,array $result)
	{
		if (array_key_exists("id",$result))
			$record->id	= $result["id"];
	}

	static public function executeCreate(self $record)
	{
		$table			= static::TABLE;
		$values			= get_object_vars($record);
		$columns_sql	= implode(",",array_map(function($fieldName)
		{
			return "`${fieldName}`";
		},array_keys($values)));
		$values_sql		= implode(",",array_map(function($fieldName)
		{
			return ":${fieldName}";
		},array_keys($values)));
		$sql			= "INSERT INTO ${table}(${columns_sql}) VALUES (${values_sql})";

		$result	= Database::instance()->execute($sql,$values);
		static::handleCreation($record,$result);
	}

	static public function executeUpdate(self $record):bool
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

	static public function identityWhereSQL(self $record):string
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
		return array_map([get_called_class(),"normalization"],Database::instance()->query($sql,$params));
	}

	static public function queryAllMatchingParamsInnerJoin(array $params, array $tableinner)
	{
		$table	= static::TABLE;
		$sql	= "SELECT * FROM `${table}` INNER JOIN `${tableinner}`";

		$where_sql	= implode(" AND ",array_map(function($fieldName)
		{
			return "`${fieldName}`=:${fieldName}";
		},array_keys($params)));
		if (!empty($where_sql))
			$sql	.= " ON ${where_sql}";
		
		return array_map([get_called_class(),"normalize"],Database::instance()->query($sql,$params));
	}

	static public function queryWithId(int $id)
	{
		$table	= static::TABLE;
		$sql	= "SELECT * FROM `${table}` WHERE `id`=:id LIMIT 1";

		$results	= array_map([get_called_class(),"normalization"],Database::instance()->query($sql,compact("id")));
		return empty($results)?null:current($results);
	}

	static public function existanceTest(self $record):bool
	{
		return !empty($record->id);
	}

	public function exists(){ return static::existanceTest($this); }

	public function save()
	{
		if (static::existanceTest($this))
			static::executeUpdate($this);
		else
			static::executeCreate($this);
	}
}