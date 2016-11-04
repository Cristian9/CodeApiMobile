<?php

namespace Routes\Middleware;

class CsrfMiddleware extends Middleware {

	public function __invoke($request, $response, $next) {


		$name = $this->container->csrf->getTokenNameKey();
		$value = $this->container->csrf->getTokenName();


		$keyname = $this->container->csrf->getTokenValueKey();
		$keyvalue = $this->container->csrf->getTokenValue();

		$_SESSION['name'] = $name;
		$_SESSION['value'] = $value;

		$_SESSION['csrf_name'] = $keyname;
		$_SESSION['csrf_value'] = $keyvalue;


		$response = $next($request, $response);


		return $response;

	}

}