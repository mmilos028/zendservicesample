<?php

namespace outcomebet\casino\api\client;
use outcomebet\casino\api\client\transport\CurlTransport;

/**
 * Class Client
 * @package outcomebet\casino\api\client
 */
class Client
{
	/** @var Transport */
	private $transport;

	/**
	 * @param array $connectionData
	 * @param Transport $transport
	 */
	public function __construct($connectionData, $transport = null)
	{
		$this->transport = $transport;
		$this->getTransport()->setConnectionData($connectionData);
	}

	public function listGames()
	{
		$result = $this->runAction('Game.list', array());
		return $result['games'];
	}

	public function getPlayerInfo($player)
	{
		$result = $this->runAction('Player.get', array('player' => $player));
		return $result['player'];
	}

	public function setPlayerInfo($playerInfo)
	{
		$result = $this->runAction('Player.set', array('player' => $playerInfo));
		return $result['player'];
	}

	public function setBankGroup($bankGroup)
	{
		$result = $this->runAction('BankGroup.set', array('bank_group' => $bankGroup));
		return $result['bank_group'];
	}

	public function runGame($game, $player, $params = array())
	{
		return $this->runAction('Game.run', array(
				'game' => $game,
				'player' => $player,
			) + $params);
	}

	public function getSessionInfo($sessionId)
	{
		$result = $this->runAction('Session.get', array('session' => $sessionId));
		return $result['session'];
	}

	public function listSessions($timeStart, $timeEnd, $params = array())
	{
		$result = $this->runAction('Session.list', array(
				'time_start' => $timeStart,
				'time_end' => $timeEnd,
			) + $params);
		return $result['sessions'];
	}

	public function listPlayerSpins($timeStart, $timeEnd, $player)
	{
		$result = $this->runAction('Spin.list', array(
			'time_start' => $timeStart,
			'time_end' => $timeEnd,
			'player' => $player,
		));
		return $result['spins'];
	}

	public function closeSession($sessionId)
	{
		$result = $this->runAction('Session.close', array('session' => $sessionId));
		return $result['session'];
	}

	public function listTransactions($timeStart, $timeEnd, $params)
	{
		$result = $this->runAction('Transaction.list', array(
			'time_start' => $timeStart,
			'time_end' => $timeEnd,
		) + $params);
		return $result['transactions'];
	}

	public function playerReport($timeStart, $timeEnd, $params = array())
	{
		$result = $this->runAction('Report.Player.get', array(
				'time_start' => $timeStart,
				'time_end' => $timeEnd,
			) + $params
		);
		return $result['report'];
	}

	public function gameReport($timeStart, $timeEnd, $params = array())
	{
		$result = $this->runAction('Report.Game.get', array(
				'time_start' => $timeStart,
				'time_end' => $timeEnd,
			) + $params
		);
		return $result['report'];
	}

	public function getBalance($player)
	{
		$result = $this->runAction('Balance.get', array('player' => $player));
		return $result['balance'];
	}

	public function changeBalance($player, $amount)
	{
		return $this->runAction('Balance.change', array('player' => $player, 'amount' => $amount));
	}

	public function withdrawBalance($player)
	{
		return $this->runAction('Balance.withdraw', array('player' => $player));
	}

	public function apiVersion()
	{
		return $this->runAction('Api.version', array());
	}

	protected function getTransport()
	{
		if($this->transport === null)
			$this->transport = new CurlTransport();

		return $this->transport;
	}

	protected function runAction($action, $arguments)
	{
		$requestData = json_encode(array(
			'action' => $action,
			'arguments' => $arguments,
		));

		$responseData = $this->getTransport()->sendRequest($requestData);

		$response = json_decode($responseData, true);

		if($response['success'])
			return $response['result'];
		else
			throw new Exception($response['message']);
	}
}
