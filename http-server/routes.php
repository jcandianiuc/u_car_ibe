<?php

use Core\Request;
use Core\Response;

return [
	[
		'handles'	=> function(Request $request)
		{
			return in_array($request->getPath(),["login","registration","verification"]);
		},
		'handle'	=> function(Request $request)
		{
			return Project\UserController::handleRequest($request);
		},
	],
];