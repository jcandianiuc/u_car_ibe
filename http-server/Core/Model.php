<?php

namespace Core;

use Exception;

class Model
{
	public function __construct(array $filling=array())
	{
		$this->fill(array_merge($filling,static::fieldDefaults()));
	}

	static public function fieldDefaults():array{ return array(); }

	public function __get($fieldName)
	{
		$fieldNameTitleCase	= ucfirst($fieldName);
		$getterName				= "get{$fieldNameTitleCase}";
		if (method_exists($this,$getterName))
			return $this->$getterName();
		else
			throw new Exception("no-getter");
	}

	public function __set($fieldName,$fieldValue)
	{
		$fieldNameTitleCase	= ucfirst($fieldName);
		$setterName			= "set{$fieldNameTitleCase}";
		if (method_exists($this,$setterName))
			return $this->$setterName($value);
		else
			throw new Exception("no-setter");
	}

	public function fill(array $filling):void
	{
		foreach (array_keys(get_object_vars($this)) as $fieldName)
			if (array_key_exists($fieldName,$filling))
				try {
					$this->__set($fieldName,$filling[$fieldName]);
				} catch (Exception $e) {
					if ($e->getMessage()=="no-setter")
						$this->$fieldName	= $filling[$fieldName];
					else
						throw $e;
				}
	}
}