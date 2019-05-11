<?php

namespace Project;

use Core\Controller;
use Core\Request;
use Core\Response;
use Core\HttpException\BadRequestException;
use Core\HttpException\MethodNotAllowedException;
use Core\HttpException\UnauthorizedException;
use Project\Models\User;
use Project\Models\Trip;

class TripController extends Controller
{
	const	MSG_ERR_WRONG_AUTH	= "La información de autenticación es incorrecta.";

	static private $user;

	/*	Esta función precede a todas las peticiones que se manejan con este controller,
		y verifica que la petición esté autenticada.

		Igualmente se asigna el usuario al controller.
	*/
	static public function handleRequest(Request $request)
	{
		$auth_header	= empty($request->headers['Authorization'])?null:$request->headers['Authorization'];
		$auth_token		= empty($auth_header)?null:substr($auth_header,6);
		$user			= self::$user = empty($auth_token)?null:User::queryWithToken($auth_token);
		
		if (empty($user))
			throw new UnauthorizedException("unauthorized",self::MSG_ERR_WRONG_AUTH);
		else
			return parent::handleRequest($request);
	}

	static public function handleStart(Request $request)
	{
		if ($request->method!="POST")
			throw new MethodNotAllowedException();

		$trip		= new Trip((array)$request->data);
		$trip->user	= self::$user;
		
		$errors			= $trip->validation();
		if (!empty($errors))
			throw new BadRequestException("invalid-form","La información proporcionada para el viaje es inválida.",$errors);
		else {
			$trip->saveWithMarkers();
			$proposal	= $trip->findProposal();
			return new Response([
				'trip_id'	=> $trip->id,
				'proposal'	=> $proposal,
			]);
		}
	}
}