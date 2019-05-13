<?php

namespace Core;

use Core\HttpException;
use Core\HttpException\NotFoundException;
use Core\HttpException\ServerErrorException;

use Exception;

class uCARibe
{
	static private $app;

	public	$config;
	public	$request;

	public static function app(array $config=array())
	{
		if (empty(self::$app))
			self::$app	= new self($config);
		return self::$app;
	}

	public function __construct(array $config)
	{
		$this->config	= $config;
		$this->autoloaderRegistration();
		$this->request	= new Request($this->pathResolution());

		if (!empty($config['request-log']))
			$this->logRequest();
	}

	public function autoloaderRegistration()
	{
		$base_dir	= empty($this->config['base-dir'])?"":$this->config['base-dir'];
		spl_autoload_register(function($className) use ($base_dir)
		{
			$parts	= explode("\\",$className);
			$path	= sprintf("%s%s%s.php",$base_dir,DIRECTORY_SEPARATOR,implode(DIRECTORY_SEPARATOR,$parts));

			if (is_file($path))
				include $path;
		});
	}

	public function logRequest()
	{
		$file	= fopen($this->config['request-log'],"a");
		fwrite($file,time().PHP_EOL);
		fwrite($file,print_r($this->request->headers,true).PHP_EOL);
		fwrite($file,$this->request->body.PHP_EOL);
		fwrite($file,"-------------------------".PHP_EOL);
		fclose($file);
	}

	public function pathResolution():string
	{
		$base_uri		= empty($this->config['base-uri'])?"/":$this->config['base-uri'];
		$complete_uri	= $_SERVER['REQUEST_URI'];
		return substr($complete_uri,strlen($this->config['base-uri']));
	}

	public function run()
	{
		try {
			$handled	= false;
			$request	= $this->request;
			$routes		= require("routes.php");
			do {
				$route	= current($routes);
				if ($route['handles']($request)) {
					$handled	= true;
					$response	= $route['handle']($request);
					if ($response instanceof Response)
						$response->send();
					else if (isset($response))
						(new Response($response))->send();
				}
			} while (!$handled&&next($routes));

			if (!$handled)
				throw new NotFoundException();
		} catch (HttpException $e) {
			$e->send();
		} catch (Exception $e) {
			error_log($e->getMessage());
			(new ServerErrorException("error",$e->getMessage(),$e->getTrace()))->send();
		}
	}
}