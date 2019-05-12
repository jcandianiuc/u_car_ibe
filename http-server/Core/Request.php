<?php

namespace Core;

class Request extends Http
{
	private	$data;
	private	$method;
	private	$path;

	public function __construct(string $path)
	{
		parent::__construct(file_get_contents("php://input"));
		$this->method	= $_SERVER['REQUEST_METHOD'];
		$this->path		= $path;
	}

	private function dataResolution()
	{
		if ($this->method=="GET")
			return $_GET;
		else {
			$headers		= $this->getHeaders();
			$content_type	= array_key_exists("Content-Type",$headers)?$headers['Content-Type']:null;
			if ($content_type=="application/json")
				return json_decode($this->getBody());
			else
				return $_POST;
		}
	}

	public function getCookies():array
	{
		if (empty($this->galletas))
			$this->galletas	= $_COOKIE;
		return parent::getCookies();
	}

	public function getData()
	{
		if (empty($this->data))
			$this->data	= $this->dataResolution();
		return $this->data;
	}

	public function getHeaders():array
	{
		if (empty($this->headers))
			$this->headers	= apache_request_headers();
		return parent::getHeaders();
	}

	public function getMethod():string{ return $this->method; }

	public function getPath():string{ return $this->path; }

	public function getPathParts():array{ return explode("/",$this->getPath()); }
}