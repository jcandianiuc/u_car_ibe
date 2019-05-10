<?php

namespace Core\HttpException;

use Core\HttpException;

class NotFoundException extends HttpException
{
	const	DEFAULT_MESSAGE	= "Not Found";
	const	STATUS_CODE		= 404;
}