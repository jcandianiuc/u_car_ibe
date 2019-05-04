<?php

namespace Core;

use Exception;

abstract class HttpException extends Exception
{
	const STATUS_CODE	= 500;

	public function __construct(string $message="")
	{
		parent::__construct(empty($message)?static::DEFAULT_MESSAGE:$message);
	}

	public function send()
	{
		$response			= new Response($this->getMessage());
		$response->status	= static::STATUS_CODE;
		$response->send();
	}
}