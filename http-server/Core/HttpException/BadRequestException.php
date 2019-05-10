<?php

namespace Core\HttpException;

use Core\HttpException;

class BadRequestException extends HttpException
{
	const	DEFAULT_MESSAGE	= "Bad Request";
	const	STATUS_CODE		= 400;
}