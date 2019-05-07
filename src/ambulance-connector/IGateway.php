<?php declare(strict_types=1);

namespace cyllenea\AmbulanceConnector;

interface IGateway {

	/** @param string $token */
	public function setToken(string $token);

	/** @param string $location */
	public function setLocation(string $location);

	/** @param string $uri */
	public function setUri(string $uri);

}
