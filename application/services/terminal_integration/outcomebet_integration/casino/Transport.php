<?php

namespace outcomebet\casino\api\client;

abstract class Transport
{
	protected $url;
	protected $sslKeyPath;

	public function setConnectionData($connectionData)
	{
		$this->url = $connectionData['url'];
		$this->sslKeyPath = $connectionData['sslKeyPath'];
		$this->resetConnection();
	}

	protected function resetConnection()
	{}

	abstract public function sendRequest($data);
}