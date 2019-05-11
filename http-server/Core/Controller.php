<?php

namespace Core;

use Core\Request;

class Controller
{
	static public function handleRequest(Request $request)
	{
		$path	= $request->getPathParts();
		$target	= end($path);
		$suffix	= ucfirst($target);
		$method	= "handle${suffix}";

		if (method_exists(get_called_class(),$method))
			return call_user_func(array(get_called_class(),$method),$request);
		else
			throw new Exception("no-matching-method");
	}
}