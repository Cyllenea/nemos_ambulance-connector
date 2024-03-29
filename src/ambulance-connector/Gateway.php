<?php declare(strict_types=1);

namespace cyllenea\AmbulanceConnector;

use Nette\Caching\IStorage;
use cyllenea\AmbulanceConnector\Exception\ConfigurationException;
use cyllenea\AmbulanceConnector\Exception\ResponseException;

class Gateway implements IGateway
{
	/** @var string */
	private $_tempDir = NULL;

	/** @var string */
	private $_token = "";

	/** @var string */
	private $_location = "http://www.nemosnet.cz/webobj-ws/";

	/** @var string */
	private $_uri = "http://www.nemosnet.cz/webobj-ws/";

	/** @var \SoapClient */
	private $_soapObject;

	private $_response;


	public function __construct($tempDir = NULL, $token = NULL, $location = NULL, $uri = NULL)
	{
		if ($tempDir) {
			$this->_tempDir = $tempDir;
		}

		if ($token) {
			$this->_token = $token;
		}

		if ($location) {
			$this->_location = $location;
		}

		if ($token) {
			$this->_uri = $uri;
		}
	}

	protected function _createSoapObject()
	{
		if ($this->_location && $this->_uri) {
			$this->_soapObject = new \SoapClient(NULL, ['location' => $this->_location, 'uri' => $this->_uri]);
		} else {
			throw new ConfigurationException("Location or Uri are missing.");
		}
	}

	public function call($function)
	{

		// Dynamicke parametry, odstraneni volane funkce u NEMOS
		$args = func_get_args();
		if ($args[0] === $function) {
			unset($args[0]);
		}

		// Pridani tokenu do prvniho parametru funkce pro SOAP
		$args[-1] = $this->_token;
		ksort($args);

		try {
			$this->_createSoapObject();
			/** @var Response _response */
			if ($this->_response = $this->_soapObject->__soapCall($function, $args)) {
				$arrayResponse = (array)$this->_response;
				unset($arrayResponse["status"]);
				unset($arrayResponse["zprava"]);

				$response = new Response((int)$this->_response->status, $this->_response->zprava, $arrayResponse);
				if ($response->getStatus() !== 0) {
					throw new ResponseException($response->getMessage());
				} else {
					return $response;
				}
			} else {
				throw new ResponseException("The response is missing. Is server active?");
			}
		} catch (\SoapFault $e) {
			return FALSE;
		}
	}

	public function setToken(string $token)
	{
		$this->_token = $token;

	}

	public function setLocation(string $location)
	{
		$this->_location = $location;
	}

	public function setUri(string $uri)
	{
		$this->_uri = $uri;
	}

	public function setStorage(IStorage $storage)
	{
		$this->storage = $storage;
	}

}
