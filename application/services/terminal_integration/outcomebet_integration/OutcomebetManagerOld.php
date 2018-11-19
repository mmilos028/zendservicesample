<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
require_once SERVICES_DIR . DS . 'terminal_integration' . DS . 'outcomebet_integration' . DS . "casino" . DS . 'Client.php';
require_once SERVICES_DIR . DS . 'terminal_integration' . DS . 'outcomebet_integration' . DS . "casino" . DS . 'Transport.php';
require_once SERVICES_DIR . DS . 'terminal_integration' . DS . 'outcomebet_integration' . DS . "casino" . DS . 'Exception.php';
require_once SERVICES_DIR . DS . 'terminal_integration' . DS . 'outcomebet_integration' . DS . "casino" . DS . 'transport' . DS . 'CurlTransport.php';

/**
 *
 * web service for outcomebet integration
 *
 */
class OutcomebetManager {

    private $client = null;
    private $game_id = 1725;
    private $withdraw_transaction = "MANAGMENT_TYPES.CREDITS_TO_OUTCOME";
    private $deposit_transaction = "MANAGMENT_TYPES.CREDITS_FROM_OUTCOME";

    public function __construct(){
        $client = new \outcomebet\casino\api\client\Client(
            array(
                "sslKeyPath" => SERVICES_DIR . DS . "outcomebet_integration" . DS . "casino" . DS . "apikey.pem",
                "url" => "https://api.skygamming.com/"
            )
        );
        $this->client = $client;
    }

    /**
     * @return mixed
     */
    public function listGames(){
        return $this->client->listGames();
    }

    /**
     * @param $game_id
     * @param $player_id
     */
    public function runGame($game_id, $player_id){
        return $this->client->runGame($game_id, $player_id);
    }

    /**
     * @param $player_id
     * @return mixed
     */
    public function getPlayerInformation($player_id){
        $player = array(
            "player_id"=>$player_id
        );
        return $this->client->getPlayerInfo($player);
    }

    /**
     * @param $player_id
     * @param null $player_username
     * @param null $affiliate_id
     * @return mixed
     */
    public function setPlayerInformation($player_id, $player_username = null, $affiliate_id = null){
        $playerInfo = array(
            "player_id"=>$player_id,
            "nick"=>$player_username,
            "bank_group"=>$affiliate_id
        );
        return $this->client->setPlayerInfo($playerInfo);
    }

    /**
     * @param $session_id
     * @return mixed
     */
    public function closeSession($session_id){
        return $this->client->closeSession($session_id);
    }

    /**
     * @param $session_id
     * @return mixed
     */
    public function getSessionInfo($session_id){
        return $this->client->getSessionInfo($session_id);
    }

    /**
     * @param $pc_session_id
     * @param $player_id
     * @param $amount
     * @return mixed
     */
    public function changeBalance($pc_session_id, $player_id, $amount)
	{
        $player = array(
            "player_id"=>$player_id
        );
        if($amount > 0){
            //do deposit
            $modelSubjectTypes = new SubjectTypesModel();
            $transaction_type_name = $modelSubjectTypes->getSubjectType($this->deposit_transaction);
            $result = OutcomebetModel::changePlayerBalance($player_id, $pc_session_id, abs($amount), $this->game_id, $transaction_type_name);
        }else{
            //do withdraw
            $modelSubjectTypes = new SubjectTypesModel();
            $transaction_type_name = $modelSubjectTypes->getSubjectType($this->withdraw_transaction);
            $result = OutcomebetModel::changePlayerBalance($player_id, $pc_session_id, abs($amount), $this->game_id, $transaction_type_name);
        }
        if($result['status'] == OK) {
            return $this->client->changeBalance($player, $amount);
        }else{
            $mail_message = "OutcomebetModel::postTransaction - OUTCOMEBET.post_transaction_ws(:p_player_id_in = {$player_id}, :p_session_id_in = {$pc_session_id}, :p_amount_in = {$amount}, :p_live_casino_game_id = {$this->game_id}, :p_transaction_type_name_in = {$transaction_type_name}, :p_transaction_id_out)";
			$log_message = "OutcomebetModel::postTransaction - OUTCOMEBET.post_transaction_ws(:p_player_id_in = {$player_id}, :p_session_id_in = {$pc_session_id}, :p_amount_in = {$amount}, :p_live_casino_game_id = {$this->game_id}, :p_transaction_type_name_in = {$transaction_type_name}, :p_transaction_id_out)";
            $errorHelper = new ErrorHelper();
			$errorHelper->serviceError($mail_message, $log_message);
            return "";
        }
	}

    /**
     * @param $pc_session_id
     * @param $player_id
     * @return mixed
     */
	public function withdrawBalance($pc_session_id, $player_id)
	{
        $player = array(
            "player_id"=>$player_id
        );
        //do withdraw balance all
        $playerDetails = OutcomebetModel::getPlayerDetails($player_id);
        $modelSubjectTypes = new SubjectTypesModel();
        $transaction_type_name = $modelSubjectTypes->getSubjectType($this->withdraw_transaction);
        $amount = NumberHelper::convert_double($playerDetails['cursor']['credits']);
        if($playerDetails['status'] == OK){
            $result = OutcomebetModel::changePlayerBalance($player_id, $pc_session_id, abs($amount), $this->game_id, $transaction_type_name);
            if($result['status'] == OK){
                //send withdraw
                return $this->client->withdrawBalance($player);
            }else{
                $mail_message = "OutcomebetModel::postTransaction - OUTCOMEBET.post_transaction_ws(:p_player_id_in = {$player_id}, :p_session_id_in = {$pc_session_id}, :p_amount_in = {$amount}, :p_live_casino_game_id = {$this->game_id}, :p_transaction_type_name_in = {$transaction_type_name}, :p_transaction_id_out)";
                $log_message = "OutcomebetModel::postTransaction - OUTCOMEBET.post_transaction_ws(:p_player_id_in = {$player_id}, :p_session_id_in = {$pc_session_id}, :p_amount_in = {$amount}, :p_live_casino_game_id = {$this->game_id}, :p_transaction_type_name_in = {$transaction_type_name}, :p_transaction_id_out)";
                $errorHelper = new ErrorHelper();
                $errorHelper->serviceError($mail_message, $log_message);
                return "";
            }
        }else{
            $mail_message = "OutcomebetModel::postTransaction - OUTCOMEBET.post_transaction_ws(:p_player_id_in = {$player_id}, :p_session_id_in = {$pc_session_id}, :p_amount_in = {$amount}, :p_live_casino_game_id = {$this->game_id}, :p_transaction_type_name_in = {$transaction_type_name}, :p_transaction_id_out)";
			$log_message = "OutcomebetModel::postTransaction - OUTCOMEBET.post_transaction_ws(:p_player_id_in = {$player_id}, :p_session_id_in = {$pc_session_id}, :p_amount_in = {$amount}, :p_live_casino_game_id = {$this->game_id}, :p_transaction_type_name_in = {$transaction_type_name}, :p_transaction_id_out)";
            $errorHelper = new ErrorHelper();
			$errorHelper->serviceError($mail_message, $log_message);
            return "";
        }

	}

    /**
     * @param $player_id
     * @return mixed
     */
    public function getBalance($player_id){
        return $this->client->getBalance($player_id);
    }

    /**
     * @param $affiliate_id
     * @param $affiliate_username
     * @param $default_value
     * @param $currency
     * @return mixed
     */
    public function setBankGroup($affiliate_id, $affiliate_username, $default_value, $currency)
	{
        $bankGroup = array(
            "id"=>$affiliate_id,
            "name"=>$affiliate_username,
            "default_value"=>$default_value,
            "currency"=>$currency,
            "template"=>null
        );
		return $this->client->setBankGroup($bankGroup);
	}

}