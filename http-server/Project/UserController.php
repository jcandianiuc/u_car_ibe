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
	const	MSG_ERR_INVALID_REGISTRATION	= "Los datos de registro son inválidos.";
	const	MSG_ERR_NOT_VERIFIED			= "Para iniciar sesión, es necesario verificar el correo electrónico institucional.";
	const	MSG_ERR_REGISTRED_USER			= "La matrícula especificada ya ha sido registrada.";
	const	MSG_ERR_WRONG_CREDENTIALS		= "La cuenta no existe, o la contraseña es incorrecta.";

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

	static public function handleRegistration(Request $request)
	{
		if ($request->method!="POST")
			throw new MethodNotAllowedException();

		$users = User::queryAllMatchingParams([ # Buscar si hay algun registro con el mismo id
			'id'		=> $request->data->id,
		]);

		if (!empty($users)) # Si no esta vacio, quiere decir que hay un usuario ya registrado con ese id
			throw new BadRequestException("already-registered",self::MSG_ERR_REGISTRED_USER); # Se manda un mensaje de error
		else { # Si esta vacio, se puede realizar el registro

			$user = new User(); # Creamos un nuevo usuario

			# Guardamos los datos del nuevo usuario
			$user->id = $request->data->id;
			$user->password = User::passwordHashing($request->data->password);
			$user->name = $request->data->name;
			$user->phone = $request->data->phone;

			$errors = $user->validation(); # Verificar que no haya errores

			if(empty($errors)){ # Si no hay errores
				# Insertar los datos en la BD
				$user->save();

				$response	= new Response();
				return $response; # No necesitamos regresar nada
			}
			else{
				throw new BadRequestException("invalid-form",self::MSG_ERR_INVALID_REGISTRATION,$errors); # Se manda un mensaje de error
			} 
		}
	}
}