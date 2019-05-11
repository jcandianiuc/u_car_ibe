<?php

namespace Core;

use Exception;

class Http
{
	const	STATUS_OK			= 200;
	const	STATUS_BAD_REQUEST	= 400;
	const	STATUS_FORBIDDEN	= 401;
	const	STATUS_NOT_FOUND	= 404;
	const	STATUS_SERVER_ERROR	= 500;

	protected	$body;
	protected	$galletas;
	protected	$headers;

	public function __construct(string $body="")
	{
		$this->setBody($body);
		$this->headers	= array();
		$this->galletas	= array();
	}

	// Esta función permite utilizar los getters (getVar()) como variables aunque sean privadas,
	// por ejemplo: $body = $http->body;
	public function __get($propertyName)
	{
		$propertyNameTitleCase	= ucfirst($propertyName);
		$getterName				= "get{$propertyNameTitleCase}";
		if (method_exists($this,$getterName))
			return $this->$getterName();
		else
			throw new Exception("no-such-property");
	}

	// Esta función permite utilizar los setters (setVar()) como variables aunque sean privadas,
	// por ejemplo: $http->body = "body";
	public function __set($propertyName,$value)
	{
		$propertyNameTitleCase	= ucfirst($propertyName);
		$setterName				= "set{$propertyNameTitleCase}";
		if (method_exists($this,$setterName))
			return $this->$setterName($value);
		else
			throw new Exception("no-such-property");
	}

	public function __toString(){ return $this->getBody(); }
	
	public function getBody():string{ return $this->body; }
	public function setBody(string $body){ $this->body=$body; }

	public function getCookies():array{ return $this->galletas; }
	
	public function getHeaders():array{ return $this->headers; }
}