<?php

interface ExternalIntegration{
	/**
	 * 
	 * this service is checking player credits from external integration
	 * 
	 * @param string $affiliate_username
	 * @param string $affiliate_password
	 * @param string $player_id
	 * @param string $ws_url
	 * @return mixed
	 */
	public function getPlayerInfo($affiliate_username, $affiliate_password, $player_id, $ws_url);
	
	/**
	 * this service is making external player transactions for external integration on internet
	 * 
	 * @param string $affiliate_username
	 * @param string $affiliate_password
	 * @param string $player_id
	 * @param string $amount
	 * @param string $transaction_type
     * @param string $received_date
     * @param string $reservation_id
     * @param string $game_transaction_id
     * @param string $game_move_id
	 * @param string $game_id
	 * @param string $game_name
	 * @param string $ws_url
	 * @return mixed
	 */
	public function postTransaction($affiliate_username, $affiliate_password, $player_id, $amount, $transaction_type, $received_date, $reservation_id, $game_transaction_id, $game_move_id, $game_id, $game_name, $ws_url);

    /**
	 * this service is notifying client in closing player session from games for external integration on internet
	 *
	 * @param string $affiliate_username
	 * @param string $affiliate_password
	 * @param string $player_id
	 * @param string $ws_url
	 * @return mixed
	 */
    public function closePlayerSession($affiliate_username, $affiliate_password, $player_id, $ws_url);

    /**
	 * this service is checking external player transactions for external integration on internet
	 *
	 * @param string $affiliate_username
	 * @param string $affiliate_password
	 * @param string $player_id
	 * @param string $amount
	 * @param string $transaction_type
     * @param string $received_date
     * @param string $reservation_id
     * @param string $game_transaction_id
     * @param string $game_move_id
	 * @param string $game_id
	 * @param string $game_name
	 * @param string $ws_url
	 * @return mixed
	 */
	public function checkTransaction($affiliate_username, $affiliate_password, $player_id, $amount, $transaction_type, $received_date, $reservation_id, $game_transaction_id, $game_move_id, $game_id, $game_name, $ws_url);
}