<?php

namespace Core;

use Exception;

class Model
{
	const	FILLABLE_FIELD_NAMES	= array();

	public function __construct(array $filling=array())
	{
		$this->fill(array_merge($filling,static::fieldDefaults()));
	}

	static public function fieldDefaults():array{ return array(); }

	static public function normalization($value)
	{
		if ($value instanceof static)
			return $value;
		else if (is_object($value))
			return new static((array)$value);
		else if (is_array($value))
			return new static($value);
		else
			throw new Exception("cannot-normalize");
	}

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
			return $this->$setterName($fieldValue);
		else
			throw new Exception("no-setter");
	}

	public function fill(array $filling):void
	{
		$fillableFieldNames	= array_merge(array_keys(get_object_vars($this)),static::FILLABLE_FIELD_NAMES);
		foreach ($fillableFieldNames as $fieldName)
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