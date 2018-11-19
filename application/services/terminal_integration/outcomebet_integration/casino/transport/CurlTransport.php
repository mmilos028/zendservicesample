<?php

namespace outcomebet\casino\api\client\transport;

use outcomebet\casino\api\client\Transport;

class CurlTransport extends Transport
{
	private $curlHandle;

	public function sendRequest($data)
	{
		$handle = $this->getCurlHandle();

		curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
		$response = curl_exec($handle);

		if ($response === false) {
			$error = curl_error($handle);
			throw new \Exception($error);
		}

		return $response;
	}

	protected function getCurlHandle()
	{
		if ($this->curlHandle === null)
			$this->initCurlHandle();
		return $this->curlHandle;
	}

	protected function initCurlHandle()
	{
		$this->curlHandle = curl_init();
		curl_setopt_array($this->curlHandle, array(
			CURLOPT_POST => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_URL => $this->url,
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json',
			),
			CURLOPT_SSLCERT => $this->sslKeyPath,
		));
	}

	protected function resetConnection()
	{
		if($this->curlHandle !== null)
		{
			curl_close($this->curlHandle);
			$this->curlHandle = null;
		}
	}
}