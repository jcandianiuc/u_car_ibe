<?php

use Core\Request;
use Core\Response;

return [
	[
		'handles'	=> function(Request $request)
		{
			// Esta será la acción que se ejecute cuando
			// la URI apunte a "/login"
			return $request->getPath()=="login"; // se omite el primer slash
		},
		'handle'	=> function(Request $request)
		{
			require "login.php";
		},
	],
	[
		'handles'	=> function(Request $request)
		{
			// Igualmente se puede obtener el path por piezas,
			// por ejemplo:
			//	"/register" => ["register"]
			$pathParts	= $request->getPathParts();
			return $pathParts[0]=="register";
		},
		'handle'	=> function(Request $request)
		{
			require "registro.php";
		},
	],
	[
		'handles'	=> function(Request $request)
		{
			// El del 'handles' es asegurar que una petición
			// esté adecuadamente preparada para ser manejada.

			// Por ejemplo, esta petición sólo la manejaremos si es "POST"
			if ($request->method=="POST")
				return true;
			else
				return false;
		},
		'handle'	=> function(Request $request)
		{
			// Y en el handler, operar sobre la petición.
			// Esto primoridalente se delegará a otros scripts, similar a los ejemplos anteriores.

			if (empty($request->data->hello))
				throw new Core\HttpException\BadRequestException;

			// Las respuestas ya están preparadas para manejar texto y JSON
			return new Response([
				'success'	=> true,
				'data'		=> "Hello, World!",
				'path'		=> $request->getPath(),
			]);
		},
	],
];