<?php

namespace Project;

use Core\Controller;
use Core\Request;
use Core\Response;
use Core\HttpException\BadRequestException;
use Core\HttpException\MethodNotAllowedException;
use Core\HttpException\NotFoundException;
use Core\HttpException\UnauthorizedException;
use Project\Models\User;
use Project\Models\Trip;
use Project\Models\Proposal;

class TripController extends Controller
{
	const	MSG_ERR_INVALID_FORM			= "Hay errores en la petición.";
	const	MSG_ERR_PREVIOUSLY_REJECTED		= "La propuesta especificada fué rechazada por algún participante.";
	const	MSG_ERR_TRIP_PROPOSAL_NOT_FOUND	= "La propuesta o viaje especificado no existe.";
	const	MSG_ERR_WRONG_AUTH				= "La información de autenticación es incorrecta.";
	const	MSG_ERR_WRONG_CTYPE				= "El contenido de la petición debe ser tipo 'application/json'.";

	static private $user;

	static public function jsonRequestTest(Request $request)
	{
		return $request->method!="POST"||(!empty($request->headers["Content-Type"])&&$request->headers["Content-Type"]=="application/json");
	}

	/*	Esta función precede a todas las peticiones que se manejan con este controller,
		y verifica que la petición esté autenticada.

		Igualmente se asigna el usuario al controller.
	*/
	static public function handleRequest(Request $request)
	{
		if (!self::jsonRequestTest($request))
			throw new BadRequestException("invalid-content-type",self::MSG_ERR_WRONG_CTYPE);

		$auth_header	= empty($request->headers['Authorization'])?null:$request->headers['Authorization'];
		$auth_token		= empty($auth_header)?null:substr($auth_header,6);
		$user			= self::$user = empty($auth_token)?null:User::queryWithToken($auth_token);
		
		if (empty($user))
			throw new UnauthorizedException("unauthorized",self::MSG_ERR_WRONG_AUTH);
		else
			return parent::handleRequest($request);
	}

	static public function handleAccept(Request $request)
	{
		if ($request->method!="POST")
			throw new MethodNotAllowedException();

		$errors	= array()
		if (empty($request->data->trip_id))
			$errors[]	= ['code'=>"required",'message'=>self::MSG_ERR_FIELD_REQUIRED,'data'=>"trip_id"];
		if (empty($request->data->proposal_id))
			$errors[]	= ['code'=>"required",'message'=>self::MSG_ERR_FIELD_REQUIRED,'data'=>"proposal_id"];

		if (!empty($errors))
			throw new BadRequestException("invalid-form",self::MSG_ERR_INVALID_FORM,$errors);

		$proposal	= Proposal::queryWithIds([
			'proposal_id'	=> $request->data->proposal_id,
			'trip_id'		=> $request->data->trip_id,
			'user_id'		=> self::$user->id,
		]);

		if (empty($proposal))
			return new NotFoundException("not-found",self::MSG_ERR_TRIP_PROPOSAL_NOT_FOUND);
		else if ($proposal->getStatus()=="rejected")
			return new ConflictException("previously-rejected",self::MSG_ERR_PREVIOUSLY_REJECTED,$proposal->serialize());
		else {
			$proposal->own_status	= 1;
			$proposal->save();
			return new Response($proposal->serialize());
		}
	}

	static public function handleProposal(Request $request)
	{
		if ($request->method!="GET")
			throw new MethodNotAllowedException();

		if (empty($request->data->trip_id))
			throw new BadRequestException("no-query");

		if (empty($request->data->proposal_id))
			$proposal	= Proposal::queryActualWithTripIdAndUserId($request->data->trip_id,self::$user->id);
		else
			$proposal	= Proposal::queryWithIds([
				'proposal_id'	=> $request->data->proposal_id,
				'trip_id'		=> $request->data->trip_id,
				'user_id'		=> self::$user->id,
			]);

		if (empty($proposal))
			return new NotFoundException("not-found",self::MSG_ERR_TRIP_PROPOSAL_NOT_FOUND);
		else
			return new Response($proposal->serialize());
	}

	static public function handleReject(Request $request)
	{
		if ($request->method!="POST")
			throw new MethodNotAllowedException();

		$errors	= array()
		if (empty($request->data->trip_id))
			$errors[]	= ['code'=>"required","message"=>self::MSG_ERR_FIELD_REQUIRED,"data"=>"trip_id"];
		if (empty($request->data->proposal_id))
			$errors[]	= ['code'=>"required","message"=>self::MSG_ERR_FIELD_REQUIRED,"data"=>"proposal_id"];

		if (!empty($errors))
			throw new BadRequestException("invalid-form",self::MSG_ERR_INVALID_FORM,$errors);

		$proposal	= Proposal::queryWithIds([
			'proposal_id'	=> $request->data->proposal_id,
			'trip_id'		=> $request->data->trip_id,
			'user_id'		=> self::$user->id,
		]);

		if (empty($proposal))
			return new NotFoundException("not-found",self::MSG_ERR_TRIP_PROPOSAL_NOT_FOUND);
		else if ($proposal->getStatus()=="rejected")
			return new ConflictException("previously-rejected",self::MSG_ERR_PREVIOUSLY_REJECTED,$proposal->serialize());
		else {
			$proposal->own_status	= 2;
			$proposal->save();

			$trip	= Trip::queryWithId($proposal->trip_id);
			$trip->findProposal();

			$new_proposal	= Proposal::queryActualWithTripIdAndUserId($trip->id,self::$user->id);
			$response_dict	= empty($new_proposal)?null:$new_proposal->serialize();

			return new Response($response_dict);
		}
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
			$trip->findProposal();

			$proposal		= Proposal::queryActualWithTripIdAndUserId($trip->id,self::$user->id);
			$proposal_dict	= empty($proposal)?null:$proposal->serialize();

			return new Response([
				'trip_id'	=> $trip->id,
				'proposal'	=> $proposal_dict,
			]);
		}
	}
}