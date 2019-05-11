<?php

namespace Core\HttpException;

use Core\HttpException;

class MethodNotAllowedException extends HttpException
{
	const	DEFAULT_MESSAGE	= "Method Not Allowed";
	const	DEFAULT_SLUG	= "method-not-allowed";
	const	STATUS_CODE		= 405;
}