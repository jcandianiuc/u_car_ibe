<?php

namespace Core\HttpException;

use Core\HttpException;

class UnauthorizedException extends HttpException
{
	const	DEFAULT_MESSAGE	= "Unauthorized";
	const	DEFAULT_SLUG	= "unauthorized";
	const	STATUS_CODE		= 401;
}