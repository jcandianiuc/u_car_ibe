<?php

namespace Core;

class Response extends Http
{
	private	$status;

	public function __construct($body="")
	{
		$this->headers	= array();
		$this->galletas	= array();
		if (is_array($body))
			$this->setJson($body);
		else
			$this->setBody($body);
	}

	static public function headersArrayValidation(array $headers)
	{
		$has_numeric_keys		= count(array_filter(array_map("is_numeric",array_keys($headers))))>0;
		$all_values_are_strings	= count(array_filter(array_map("is_string",$headers)))==count($headers);
		return !$has_numeric_keys&&$all_values_are_strings;
	}

	public function setHeader(string $headerName,string $headerValue)
	{
		$this->headers[$headerName]	= $headerValue;
	}

	public function getHeaders():array{ return $this->headers; }
	public function setHeaders(array $headers)
	{
		if (self::headersArrayValidation($headers))
			$this->headers	= $headers;
		else
			throw new Exception("invalid-headers");
	}

	public function getJson()
	{
		if ($this->getHeaders()['Content-Type']=="application/json")
			return json_decode($this->getBody());
		else
			return null;
	}
	public function setJson($json)
	{
		$this->setHeader('Content-Type',"application/json");
		$this->setBody(json_encode($json));
	}

	public function getStatus(){ return $this->status; }
	public function setStatus(int $status){ $this->status=$status; }

	public function send()
	{
		http_response_code($this->getStatus());
		foreach ($this->getHeaders() as $headerName=>$headerValue)
			header("{$headerName}: {$headerValue}");
		foreach ($this->getCookies() as $galletaName=>$galletaValue)
			$_COOKIE[$galletaName]	= $galletaValue;
		echo $this->getBody();
	}
}