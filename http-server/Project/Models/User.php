<?php

namespace Project\Models;

use Core\DbModel;
use Core\Database;

class User extends DbModel
{
	const	TABLE	= "user";

	const	REQUIRED_FIELD_NAMES	= ["id","name","password","phone",];
	const	ID_LENGTH				= 9;

	const	MSG_ERR_FIELD_REQUIRED	= "Este campo es requisito.";
	const	MSG_ERR_INVALID_ID		= "La matrícula es inválida.";

	protected	$token;
	public		$id;
	public		$name;
	public		$password;
	public		$phone;
	public		$verified	= false;

	static protected function handleCreation(DbModel $record,array $result)
	{
		// no se necesita hacer nada ya que el id se especifica desde un principio
	}

	static public function passwordHashing(string $raw_password)
	{
		return md5($raw_password);
	}

	static public function existanceTest(DbModel $record):bool
	{
		return !empty($record->id)&&!empty(User::queryAllMatchingParams(['id'=>$record->id]));
	}

	static public function tokenHashing(User $user)
	{
		return md5("{$user->id}:{$user->password}");
	}

	static public function queryWithToken(string $token)
	{
		$table	= static::TABLE;
		$sql	= "SELECT * FROM `${table}` WHERE `token`=:token LIMIT 1";

		$results	= array_map([get_called_class(),"normalization"],Database::instance()->query($sql,compact("token")));
		return empty($results)?null:current($results);
	}

	public function getToken(){ return $this->token; }

	public function save()
	{
		if (empty($this->token))
			$this->token	= self::tokenHashing($this);
		parent::save();
	}

	public function validation()
	{
		$errors	= array();

		foreach (self::REQUIRED_FIELD_NAMES as $fieldName)
			if (empty($this->$fieldName))
				$errors[]	= ['code'=>"required",'message'=>User::MSG_ERR_FIELD_REQUIRED,'data'=>$fieldName];

		if (!empty($this->id)&&strlen((string)$this->id)!=self::ID_LENGTH)
			$errors[]	= ['code'=>"invalid",'message'=>User::MSG_ERR_INVALID_ID,'data'=>"id"];

		return $errors;
	}
}