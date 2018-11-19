<?php
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
class WebSiteBonusModel{
	public function __construct(){
	}

    /**
     * @param $pc_session_id
     * @param $promo_code
     * @return array
     * @throws Zend_Exception
     */
    public function getPromotionCode($pc_session_id, $promo_code){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL BONUS_SYSTEM.get_promotion(:p_session_id_in, :p_promo_code_in, :p_status_out)');
			$stmt->bindParam(':p_session_id_in', $pc_session_id);
            $stmt->bindParam(':p_promo_code_in', $promo_code);
			$status_out = "";
			$stmt->bindParam(':p_status_out', $status_out, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "pc_session_id"=>$pc_session_id, "status_out"=>$status_out);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);			
            $code = $ex->getCode();
            if($code == 20400){
                return array("status" => NOK, "message"=> "NOK_PROMOTION_USED_BY_PLAYER");
            }
            else if($code == 20401)
            {
                return array("status" => NOK, "message"=> "NOK_PROMOTION_PLAYER_HAS_ACTIVE_BONUS");
            }
            else {
				$errorHelper->siteError($message, $message);
                return array("status" => NOK, "message" => NOK_EXCEPTION);
            }
		}
    }

	//cancel bonus transactions for player from web site
    /**
     * @param $pc_session_id
     * @return array
     * @throws Zend_Exception
     */
	public function cancelBonusTransactions($pc_session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL BONUS_SYSTEM.BONUS_CANCEL_TRANSACTIONS(:p_session_id_in, :error_message)');
			$stmt->bindParam(':p_session_id_in', $pc_session_id);
			$error_message = "";
			//possible errors
			//'Player does not have active bonus campaign !!!'
			$stmt->bindParam(':error_message', $error_message, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "pc_session_id"=>$pc_session_id, "message"=>$error_message); 
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}
	
	/*
	CHECK_BONUS_CODE ( p_session_id_in       IN  CSESSIONS.ID%TYPE,  --prima id-a subjekta, da li postoji u CSession
	 p_bonus_campaign_code_in    IN  BONUS_CAMPAIGN.CAMPAIGN_CODE%TYPE, --prima code campanje
	 p_status_exists_out OUT VARCHAR2 --kao izlazni rezultat vraca Y ili N
	  )
	*/
    /**
     * @param $pc_session_id
     * @param $bonus_campaign_code
     * @param $deposit_amount
     * @return array
     * @throws Zend_Exception
     */
	public function checkBonusCode($pc_session_id, $bonus_campaign_code, $deposit_amount){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			//$stmt = $dbAdapter->prepare('CALL BONUS_SYSTEM.CHECK_BONUS_CODE(:p_session_id_in, :p_bonus_campaign_code_in, :p_deposit_amount_in, :p_status_exists_out)');
			$stmt = $dbAdapter->prepare('CALL BONUS_SYSTEM.CHECK_IF_ELIGIBLE_FOR_BONUS(:p_session_id_in, :p_bonus_code_in, :p_amount_in, :p_bonus_already_exists_out, :error_message)');
			$stmt->bindParam(':p_session_id_in', $pc_session_id);
			$stmt->bindParam(':p_bonus_code_in', $bonus_campaign_code);
			$stmt->bindParam(':p_amount_in', $deposit_amount);
			$status_exists = "";
			$stmt->bindParam(':p_bonus_already_exists_out', $status_exists, SQLT_CHR, 255);
			$error_message = "";
			$stmt->bindParam(':error_message', $error_message, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			//DEBUG THIS PART OF CODE - CREATE PLAYER VIA WEB SERVICE		
			/*
			$errorHelper = new ErrorHelper();
			$errorHelper->sendMail("BONUS_SYSTEM.CHECK_IF_ELIGIBLE_FOR_BONUS( p_session_id_in = {$pc_session_id}, p_bonus_code_in = {$bonus_campaign_code}, p_amount_in = {$deposit_amount}, p_bonus_already_exists_out = {$status_exists} error_message = {$error_message})");
			*/
            if(strlen($error_message) == 0){
                return array("status"=>OK, "pc_session_id"=>$pc_session_id, "bonus_campaign_code"=>$bonus_campaign_code, "deposit_amount"=>$deposit_amount, "bonus_code_status"=>$status_exists);
            }else{
                return array("status"=>OK, "error_message"=>$error_message,
                    "pc_session_id"=>$pc_session_id, "bonus_campaign_code"=>$bonus_campaign_code, "deposit_amount"=>$deposit_amount, "bonus_code_status"=>$status_exists
                );
            }
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}
	
	/*
	CHECK_BONUS_CODE ( p_session_id_in       IN  CSESSIONS.ID%TYPE,  --prima id-a subjekta, da li postoji u CSession
	 p_bonus_campaign_code_in    IN  BONUS_CAMPAIGN.CAMPAIGN_CODE%TYPE, --prima code campanje
	 p_status_exists_out OUT VARCHAR2 --kao izlazni rezultat vraca Y ili N
	  )
	*/
    /**
     * @param $pc_session_id
     * @param $bonus_campaign_code
     * @param $deposit_amount
     * @return array
     * @throws Zend_Exception
     */
	public function checkBonusCodeStatus($pc_session_id, $bonus_campaign_code, $deposit_amount){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL BONUS_SYSTEM.CHECK_BONUS_CODE(:p_session_id_in, :p_bonus_campaign_code_in, :p_deposit_amount_in, :p_status_exists_out)');
			$stmt->bindParam(':p_session_id_in', $pc_session_id);
			$stmt->bindParam(':p_bonus_campaign_code_in', $bonus_campaign_code);
			$stmt->bindParam(':p_deposit_amount_in', $deposit_amount);
			$status_exists = "";
			$stmt->bindParam(':p_status_exists_out', $status_exists, SQLT_CHR, 255);			
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			//DEBUG THIS PART OF CODE - CREATE PLAYER VIA WEB SERVICE		
			/*
			$errorHelper = new ErrorHelper();
			$errorHelper->sendMail("BONUS_SYSTEM.CHECK_BONUS_CODE( p_session_id_in = {$pc_session_id}, p_bonus_campaign_code_in = {$bonus_campaign_code}, p_deposit_amount_in = {$deposit_amount}, p_status_exists_out = {$status_exists})");
			*/
			return array("status"=>OK, "pc_session_id"=>$pc_session_id, "bonus_campaign_code"=>$bonus_campaign_code, "deposit_amount"=>$deposit_amount, "bonus_code_exists_status"=>$status_exists); 
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}
	
	/*
	PROCEDURE LIST_PLAYERS_BONUS_HISTORY ( p_session_id_in       IN  csessions.id%type, --input parameter session_id
    p_player_bonus_hist_out OUT SYS_REFCURSOR --output iz procedure lista bonusa za playera
    ); 
	Procedura vraca listu bonusa sa sledecim podacima tj kolonama:
	BONUS_START_DATE, CAMPAIGN_NAME, NVL(BONUS_RESTRICTED_INITIAL,0) as BONUS_AMOUNT, BONUS_BALANCE_STATUS
	Sortirana je po datumu.
	Ukoliko za igraca ne postoji istorija vraca propisanu poruku sa error codom
	(-20500,'There is no bonus in history!!!');
	//list player history report for site
	*/
    /**
     * @param $session_id
     * @return array
     * @throws Zend_Exception
     */
	public function listPlayersBonusHistory($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL BONUS_SYSTEM.LIST_PLAYERS_BONUS_HISTORY(:p_session_id_in, :p_player_bonus_hist_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_player_bonus_hist_out', $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();			
			return array("status"=>OK, "report"=>$cursor);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($message, $message);
			if($ex->getCode() == 20500){
				return array("status"=>OK, "data"=>array());
			}else{
				return array("status"=>NOK, "error_code"=>$ex->getCode(), "message"=>NOK_EXCEPTION, "details"=>$message);
			}
		}
	}

    /**
     * @param $affiliate_username
     * @param $player_id
     * @return array
     * @throws Zend_Exception
     */
    public function listBonusCampaignsAvailable($affiliate_username, $player_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL casino.bonus_system_rep.bonus_campaigns_available(:p_white_label_name, :p_player_id, :p_list_available_bonuses)');
			$stmt->bindParam(':p_white_label_name', $affiliate_username);
            $stmt->bindParam(':p_player_id', $player_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_list_available_bonuses', $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
            /*
            $errorHelper = new ErrorHelper();
            $cursor_content = print_r($cursor, true);
            $message = "casino.bonus_system_rep.bonus_campaigns_available(:p_white_label_name = {$affiliate_username}, :p_player_id = {$player_id}, :p_list_available_bonuses = {$cursor_content})";
			$errorHelper->serviceError($message, $message);
            */
			return array("status"=>OK, "cursor"=>$cursor);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($message, $message);
			return array("status"=>NOK, "cursor"=>array());
		}
    }

    /**
     * @param $player_id
     * @return array
     * @throws Zend_Exception
     */
    public function listPlayerBonusCampaignsAvailable($player_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL casino.bonus_system_rep.player_bonus_details(:p_player_id, :p_bonus_details_out)');
            $stmt->bindParam(':p_player_id', $player_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_bonus_details_out', $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "cursor"=>$cursor);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($message, $message);
			return array("status"=>NOK, "cursor"=>array());
		}
    }

    /**
     * @param $player_id
     * @return array
     * @throws Zend_Exception
     */
    public function listPlayerActiveBonusesAndPromotions($player_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL casino.bonus_system_rep.player_active_bonus_promotions(:p_player_id, :cur_bonus_promotions_out)');
            $stmt->bindParam(':p_player_id', $player_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':cur_bonus_promotions_out', $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "cursor"=>$cursor);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($message, $message);
			return array("status"=>NOK, "cursor"=>array());
		}
    }
	
    /**
	 * Check if bonus is available for country
     * @param $ip_address
     * @param $affiliate_name
     * @return array
     * @throws Zend_Exception
     */
    public function checkBonusAvailableForCountry($ip_address, $affiliate_name){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL CS_USER_UTILITY.check_bonus_avail_for_country(:p_ip_address, :p_aff_name, :p_country_is_prohibited)');
			$stmt->bindParam(':p_ip_address', $ip_address);
            $stmt->bindParam(':p_aff_name', $affiliate_name);
			$country_is_prohibited = "";
			$stmt->bindParam(':p_country_is_prohibited', $country_is_prohibited, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=> OK, "country_is_prohibited"=>$country_is_prohibited);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($message, $message);
            $code = $ex->getCode();            
            return array("status" => NOK, "message" => NOK_EXCEPTION);
            
		}
    }
}