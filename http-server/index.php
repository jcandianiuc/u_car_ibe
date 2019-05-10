<?php

/*
	Script de entrada hacia la API.
	El core se encarga de inicializar el sistema,
	a partir de la configuración en config.php

	Los endpoints se definen en routes.php
*/

require "core.php";

use Core\uCARibe;
uCARibe::app(include "config.php")->run();