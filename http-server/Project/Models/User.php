<?php

namespace Project\Models;

use Core\DbModel;

class User extends DbModel
{
	const	TABLE	= "user";

	protected	$password;
	protected	$token;
	public		$id;
	public		$name;
	public		$phone;
	public		$verified	= false;

	static public function passwordHashing(string $raw_password)
	{
		return md5($raw_password);
	}

	static public function tokenHashing(User $user)
	{
		return md5("${$user->id}:${$user->password}");
	}

	public function getToken(){ return $this->token; }

	public function save()
	{
		if (empty($this->token))
			$this->token	= self::tokenHashing($this);
		parent::save();
	}
}