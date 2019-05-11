<?php

namespace Project;

use Core\Controller;
use Core\Request;
use Core\Response;
use Core\HttpException\BadRequestException;
use Core\HttpException\MethodNotAllowedException;
use Core\HttpException\UnauthorizedException;
use Project\Models\User;

class UserController extends Controller
{
	const	MSG_ERR_NOT_VERIFIED		= "Para iniciar sesión, es necesario verificar el correo electrónico institucional.";
	const	MSG_ERR_WRONG_CREDENTIALS	= "La cuenta no existe, o la contraseña es incorrecta.";

	static public function handleLogin(Request $request)
	{
		if ($request->method!="POST")
			throw new MethodNotAllowedException();

		if (!isset($request->data->id,$request->data->password))
			throw new BadRequestException("invalid-form");

		$users	= User::queryAllMatchingParams([
			'id'		=> $request->data->id,
			'password'	=> User::passwordHashing($request->data->password),
		]);

		if (empty($users))
			throw new BadRequestException("wrong-credentials",self::MSG_ERR_WRONG_CREDENTIALS);
		else {
			$user	= $users[0];
			if (!$user->verified)
				throw new UnauthorizedException("pending-email-verification",self::MSG_ERR_NOT_VERIFIED);

			$response	= new Response();
			$response->setJson($user->token);
			return $response;
		}
	}
}