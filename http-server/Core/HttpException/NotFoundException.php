<?php

namespace Core\HttpException;

use Core\HttpException;

class NotFoundException extends HttpException
{
	const	DEFAULT_MESSAGE	= "Not Found";
	const	DEFAULT_SLUG	= "not-found";
	const	STATUS_CODE		= 404;
}