<?php

namespace Core;

use Exception;

abstract class HttpException extends Exception
{
	const STATUS_CODE	= 500;

	public	$code;
	public	$data;

	public function __construct(string $code,string $message="",$data=null)
	{
		parent::__construct(empty($message)?static::DEFAULT_MESSAGE:$message);
		$this->code	= $code;
		$this->data	= $data;
	}

	public function send()
	{
		$response			= new Response([
			"code"		=> $this->code,
			"message"	=> $this->getMessage(),
			"data"		=> $this->data,
		);
		$response->status	= static::STATUS_CODE;
		$response->send();
	}
}