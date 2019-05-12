<?php

namespace Core\HttpException;

use Core\HttpException;

class ServerErrorException extends HttpException
{
	const	DEFAULT_MESSAGE	= "Server Error";
	const	DEFAULT_SLUG	= "error";
	const	STATUS_CODE		= 500;
}