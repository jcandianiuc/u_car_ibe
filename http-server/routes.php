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
	[
		'handles'	=> function(Request $request)
		{
			return in_array(
				$request->getPath(),
				[
					"trip/start",
					"trip/proposal",
					"trip/accept",
					"trip/reject",
				]
			);
		},
		'handle'	=> function(Request $request)
		{
			return Project\TripController::handleRequest($request);
		},
	]
];