<?php

namespace Project\Models;

use Core\Database;

class User
{
	static public function hashPassword(string $raw_password)
	{
		return md5($raw_password);
	}

	static public function fetchWithIdAndPassword(string $id,string $raw_password)
	{
		$password	= self::hashPassword($raw_password);
		$sql		= "SELECT * FROM user WHERE id=:id AND password=:password";

		$results	= Database::instance()->query($sql,compact("id","password"));
		return empty($results)?null:new self($results[0]);
	}
}