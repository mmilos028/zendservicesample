<?php
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
class WebSiteModel{
	public function __construct(){
	}

		//returns status if country is not allowed per ip address and affiliate_id
		/**
		 * @param string $affiliate_id
		 * @param string $ip_address
		 * @return array
		 * @throws Zend_Exception
		 */
		public function checkIfCountryIsProhibited($affiliate_id, $ip_address){
            /* @var $dbAdapter Zend_Db_Adapter_Oracle */
            $dbAdapter = Zend_Registry::get('db_auth');
            $dbAdapter->beginTransaction();
            //DEBUG HERE
            /*
            $errorHelper = new ErrorHelper();
            $message = "cs_user_utility.check_if_country_is_prohibited(:p_ip_address = {$ip_address}, :p_aff_id = {$affiliate_id}, :p_country_is_prohibited =)";
            $errorHelper->siteAccess($message, $message);
            */
            try{
                $stmt = $dbAdapter->prepare('CALL cs_user_utility.check_if_country_is_prohibited(:p_ip_address, :p_aff_id, :p_country_is_prohibited)');
                $stmt->bindParam(':p_ip_address', $ip_address);
                $stmt->bindParam(':p_aff_id', $affiliate_id);
                $country_is_prohibited = "";
                $stmt->bindParam(':p_country_is_prohibited', $country_is_prohibited, SQLT_CHR, 255);
                $stmt->execute(null, false);
                $dbAdapter->commit();
                $dbAdapter->closeConnection();
                return array("status"=>OK, "country_is_prohibited"=>$country_is_prohibited);
            }catch(Zend_Exception $ex){
                $dbAdapter->rollBack();
                $dbAdapter->closeConnection();
                $errorHelper = new ErrorHelper();
                $message = "cs_user_utility.check_if_country_is_prohibited(:p_ip_address = {$ip_address}, :p_aff_id = {$affiliate_id}, :p_country_is_prohibited=) <br /> Exception error: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
                $errorHelper->siteError($message, $message);
                return array("status"=>NOK, "message"=>NOK_EXCEPTION);
            }
		}

    //returns game limits in player login in web site
    /**
     * @param null $player_id
     * @param null $affiliate_name
     * @param string $ip_address
     * @return array
     * @throws Zend_Exception
     */
    public function checkPlayerCountryLimits($player_id = null, $affiliate_name = null, $ip_address = ""){
        $config = Zend_Registry::get('config');
	    if($config->measureSpeedPerformance == "true") {
            $before_time = microtime(true);
        }
	    if($config->db->enable_cache == "true" && $player_id == null){
	        $cacheObj = Zend_Registry::get('db_cache');
			$cache_key_name = "MANAGMENT_CORE__CHECK_PLAYER_COUNTRY_LIMITS_p_player_id_{$player_id}_p_aff_name_{$affiliate_name}_p_ip_address_{$ip_address}";
			$cache_key_name = str_replace(array("."), "_", $cache_key_name);
		    $result = unserialize($cacheObj->load($cache_key_name) );
		    if(!isset($result) || $result == null || !$result) {
                /* @var $dbAdapter Zend_Db_Adapter_Oracle */
            $dbAdapter = Zend_Registry::get('db_auth');
            $dbAdapter->beginTransaction();
            //DEBUG HERE
            /*
            $errorHelper = new ErrorHelper();
            $message = "MANAGMENT_CORE.CHECK_PLAYER_COUNTRY_LIMITS(:p_player_id = {$player_id}, :p_aff_name = {$affiliate_name}, :p_ip_address = {$ip_address},
                :p_reg_limit =, :p_game_limit =, :p_bonus_limit =, :p_deposit_limit =, :p_status =)";
            $errorHelper->siteAccess($message, $message);
            */
            try {
                $stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.CHECK_PLAYER_COUNTRY_LIMITS(:p_player_id, :p_aff_name, :p_ip_address,
                :p_reg_limit, :p_game_limit, :p_bonus_limit, :p_deposit_limit, :p_aff_id, :p_country_code, :p_status)');
                    $stmt->bindParam(':p_player_id', $player_id);
                    $stmt->bindParam(':p_aff_name', $affiliate_name);
                    $stmt->bindParam(':p_ip_address', $ip_address);
                    $reg_limit = "";
                    $stmt->bindParam(':p_reg_limit', $reg_limit, SQLT_CHR, 255);
                    $game_limit = "";
                    $stmt->bindParam(':p_game_limit', $game_limit, SQLT_CHR, 255);
                    $bonus_limit = "";
                    $stmt->bindParam(':p_bonus_limit', $bonus_limit, SQLT_CHR, 255);
                    $deposit_limit_cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
                    $stmt->bindCursor(":p_deposit_limit", $deposit_limit_cursor);
                    $aff_id = "";
                    $stmt->bindParam(':p_aff_id', $aff_id, SQLT_CHR, 255);
                    $country_code = "";
                    $stmt->bindParam(':p_country_code', $country_code, SQLT_CHR, 255);
                    $status_out = "";
                    $stmt->bindParam(':p_status', $status_out, SQLT_CHR, 255);
                    $stmt->execute(null, false);
                    $dbAdapter->commit();
                    $dbAdapter->closeConnection();

                    $result = array("status" => OK, "reg_limit" => $reg_limit, "game_limit" => $game_limit, "bonus_limit" => $bonus_limit,
                        "status_out" => $status_out, "deposit_limit_cursor" => $deposit_limit_cursor, "affiliate_id" => $aff_id, "country_code" => $country_code);

                    $cache_key_name = "MANAGMENT_CORE__CHECK_PLAYER_COUNTRY_LIMITS_p_player_id_{$player_id}_p_aff_name_{$affiliate_name}_p_ip_address_{$ip_address}";
					$cache_key_name = str_replace(array("."), "_", $cache_key_name);
                    $cacheObj->save(serialize($result), $cache_key_name);

                    if($config->measureSpeedPerformance == "true") {
                        $after_time = microtime(true);
                        $difference_time = number_format(($after_time-$before_time), 4);
                        $errorHelper = new ErrorHelper();
                        $measure_time_message = "MANAGMENT_CORE.CHECK_PLAYER_COUNTRY_LIMITS(:p_player_id = {$player_id}, :p_aff_name = {$affiliate_name}, :p_ip_address = {$ip_address},
                        :p_reg_limit =, :p_game_limit =, :p_bonus_limit =, :p_deposit_limit =, :p_status =)";
                        $measure_time_message .= "<br /> REQUIRED_TIME = {$difference_time}";
                        $errorHelper->siteAccessLog($measure_time_message);
                    }

                    return $result;
                } catch (Zend_Exception $ex) {
                    $dbAdapter->rollBack();
                    $dbAdapter->closeConnection();
                    $errorHelper = new ErrorHelper();
                    $message = "MANAGMENT_CORE.CHECK_PLAYER_COUNTRY_LIMITS(:p_player_id = {$player_id}, :p_aff_name = {$affiliate_name}, :p_ip_address = {$ip_address},
                    :p_reg_limit =, :p_game_limit =, :p_bonus_limit =, :p_deposit_limit =, :p_status =) <br /> Exception error: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
                    $errorHelper->siteError($message, $message);
                    return array("status" => NOK, "message" => NOK_EXCEPTION);
                }
            }else{
		        return $result;
		    }

        }else {
            /* @var $dbAdapter Zend_Db_Adapter_Oracle */
            $dbAdapter = Zend_Registry::get('db_auth');
            $dbAdapter->beginTransaction();
            //DEBUG HERE
            /*
            $errorHelper = new ErrorHelper();
            $message = "MANAGMENT_CORE.CHECK_PLAYER_COUNTRY_LIMITS(:p_player_id = {$player_id}, :p_aff_name = {$affiliate_name}, :p_ip_address = {$ip_address},
                :p_reg_limit =, :p_game_limit =, :p_bonus_limit =, :p_deposit_limit =, :p_status =)";
            $errorHelper->siteAccess($message, $message);
            */
            try {
                $stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.CHECK_PLAYER_COUNTRY_LIMITS(:p_player_id, :p_aff_name, :p_ip_address,
			:p_reg_limit, :p_game_limit, :p_bonus_limit, :p_deposit_limit, :p_aff_id, :p_country_code, :p_status)');
                $stmt->bindParam(':p_player_id', $player_id);
                $stmt->bindParam(':p_aff_name', $affiliate_name);
                $stmt->bindParam(':p_ip_address', $ip_address);
                $reg_limit = "";
                $stmt->bindParam(':p_reg_limit', $reg_limit, SQLT_CHR, 255);
                $game_limit = "";
                $stmt->bindParam(':p_game_limit', $game_limit, SQLT_CHR, 255);
                $bonus_limit = "";
                $stmt->bindParam(':p_bonus_limit', $bonus_limit, SQLT_CHR, 255);
                $deposit_limit_cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
                $stmt->bindCursor(":p_deposit_limit", $deposit_limit_cursor);
                $aff_id = "";
                $stmt->bindParam(':p_aff_id', $aff_id, SQLT_CHR, 255);
                $country_code = "";
                $stmt->bindParam(':p_country_code', $country_code, SQLT_CHR, 255);
                $status_out = "";
                $stmt->bindParam(':p_status', $status_out, SQLT_CHR, 255);
                $stmt->execute(null, false);
                $dbAdapter->commit();
                $dbAdapter->closeConnection();

                if($config->measureSpeedPerformance == "true") {
                    $after_time = microtime(true);
                    $difference_time = number_format(($after_time-$before_time), 4);
                    $errorHelper = new ErrorHelper();
                    $measure_time_message = "MANAGMENT_CORE.CHECK_PLAYER_COUNTRY_LIMITS(:p_player_id = {$player_id}, :p_aff_name = {$affiliate_name}, :p_ip_address = {$ip_address},
                    :p_reg_limit =, :p_game_limit =, :p_bonus_limit =, :p_deposit_limit =, :p_status =)";
                    $measure_time_message .= "<br /> REQUIRED_TIME = {$difference_time}";
                    $errorHelper->siteAccessLog($measure_time_message);
                }

                return array("status" => OK, "reg_limit" => $reg_limit, "game_limit" => $game_limit, "bonus_limit" => $bonus_limit,
                    "status_out" => $status_out, "deposit_limit_cursor" => $deposit_limit_cursor, "affiliate_id" => $aff_id, "country_code" => $country_code);
            } catch (Zend_Exception $ex) {
                $dbAdapter->rollBack();
                $dbAdapter->closeConnection();
                $errorHelper = new ErrorHelper();
                $message = "MANAGMENT_CORE.CHECK_PLAYER_COUNTRY_LIMITS(:p_player_id = {$player_id}, :p_aff_name = {$affiliate_name}, :p_ip_address = {$ip_address},
			    :p_reg_limit =, :p_game_limit =, :p_bonus_limit =, :p_deposit_limit =, :p_status =) <br /> Exception error: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
                $errorHelper->siteError($message, $message);
                return array("status" => NOK, "message" => NOK_EXCEPTION);
            }
        }
    }

    //player change terms for CBC system
    /**
     * @param $session_id
     * @return array
     * @throws Zend_Exception
     */
    public function changeTermsForCBC($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.CHANGE_TERMS_FOR_CBC(:p_session_id_in, :p_status_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
            $cbc_status = "";
            $stmt->bindParam(':p_status_out', $cbc_status, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "message"=>$cbc_status);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
    }

    //player change terms for GGL system
    /**
     * @param $session_id
     * @return array
     * @throws Zend_Exception
     */
    public function changeTermsForGGL($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.CHANGE_TERMS_FOR_GGL(:p_session_id_in, :p_status_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
            $ggl_status = "";
            $stmt->bindParam(':p_status_out', $ggl_status, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "message"=>$ggl_status);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
    }

    //check terms and conditions for web site player
    /**
     * @param $session_id
     * @return array
     * @throws Zend_Exception
     */
    public function checkTermsAndConditions($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.CHECK_TERMS_AND_CONDITIONS(:p_session_site_id_in, :p_cbc_status_out, :p_ggl_status_out)');
			$stmt->bindParam(':p_session_site_id_in', $session_id);
            $cbc_status = "";
			$stmt->bindParam(':p_cbc_status_out', $cbc_status, SQLT_CHR, 255);
            $ggl_status = "";
            $stmt->bindParam(':p_ggl_status_out', $ggl_status, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "cbc_status"=>$cbc_status, "ggl_status"=>$ggl_status);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
    }

    /**
     * @param $site_session_id
     * @param $pc_session_id
     * @return array
     * @throws Zend_Exception
     */
	public function setTimeModified($site_session_id, $pc_session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL SITE_LOGIN.M$SET_TIME_MODIFIED(:p_session_site_id_in, :p_session_pc_id_in)');
			$stmt->bindParam(':p_session_site_id_in', $site_session_id);
			$stmt->bindParam(':p_session_pc_id_in', $pc_session_id);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "pc_session_id"=>$pc_session_id, "site_session_id"=>$site_session_id);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

    /**
     * @param $affiliate_id
     * @param $tid_code
     * @return array
     * @throws Zend_Exception
     */
	public function currencyListNewPlayer($affiliate_id, $tid_code){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL WEB_REPORTS.CURRENCY_LIST_NEW_PLAYER(:p_aff_id_IN, :p_tid_in, :p_currency_list_out)');
			$stmt->bindParam(':p_aff_id_IN', $affiliate_id);
            $stmt->bindParam(':p_tid_in', $tid_code);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":p_currency_list_out", $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
            $cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "result"=>$cursor);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($message, $message);
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

	//reset wrong logins to player in gc application
    /**
     * @param $player_id
     * @return array
     * @throws Zend_Exception
     */
	public function resetWrongLoginsLeft($player_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		/*
		//DEBUG
		$errorHelper = new ErrorHelper();
		$errorHelper->sendMail(CursorToArrayHelper::getExceptionTraceAsString($ex));
		$errorHelper->siteErrorLog("called from web service for web site CS_USER_UTILITY.reset_wrong_logins_left(" . $player_id . ")");
		*/
		try{
			$stmt = $dbAdapter->prepare('CALL CS_USER_UTILITY.reset_wrong_logins_left(:p_subject_id_in)');
			$stmt->bindParam(':p_subject_id_in', $player_id);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$errorHelper->sendMail(CursorToArrayHelper::getExceptionTraceAsString($ex));
			$errorHelper->siteErrorLog(CursorToArrayHelper::getExceptionTraceAsString($ex));
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

    /**
     * @param $username
     * @param $password
     * @return array
     * @throws Zend_Exception
     */
	public function banPlayer($username, $password){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGE_SUBJECTS.ban_player(:p_username_in, :p_password_in)');
			$stmt->bindParam(':p_username_in', $username);
			$stmt->bindParam(":p_password_in", $password);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "result"=>YES);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$errorHelper->sendMail(CursorToArrayHelper::getExceptionTraceAsString($ex));
			$errorHelper->siteErrorLog(CursorToArrayHelper::getExceptionTraceAsString($ex));
			$code = $ex->getCode();
			if($code == 20121) //RAISE_APPLICATION_ERROR (-20121, 'Wrong username or password !!!!');
				return array("status"=>NOK, "message"=>NOK_NO_PLAYER);
			else
				return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

	//validate site session for player
    /**
     * @param $site_session_id
     * @param $pc_session_id
     * @return array
     * @throws Zend_Exception
     */
	public function validateSiteSession($site_session_id, $pc_session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$yes_no = NO;
        $remaining_seconds = 0;
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL SITE_LOGIN.VALIDATE_SITE_SESSION(:p_session_site_id_in, :p_session_pc_id_in, :p_yes_no_out, :p_remaining_seconds_out)');
			$stmt->bindParam(':p_session_site_id_in', $site_session_id);
			$stmt->bindParam(':p_session_pc_id_in', $pc_session_id);
			$stmt->bindParam(":p_yes_no_out", $yes_no, SQLT_CHR, 5);
            $stmt->bindParam(":p_remaining_seconds_out", $remaining_seconds, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "yes_no_status"=>$yes_no, "remaining_seconds"=>$remaining_seconds);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$errorHelper->sendMail(CursorToArrayHelper::getExceptionTraceAsString($ex));
			$errorHelper->siteErrorLog(CursorToArrayHelper::getExceptionTraceAsString($ex));
			$yes_no = NO;
			return array("status"=>OK, "yes_no_status"=>$yes_no);
		}
	}

	//opens anonymous session for web site users
    /**
     * @param $ip_address
     * @return mixed
     * @throws Zend_Exception
     */
	public function openAnonymousSession($ip_address){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL SITE_LOGIN.M$OPEN_ANONYMOUS_SESSION(:p_ip_address_in, :p_session_id_out)');
			$stmt->bindParam(':p_ip_address_in', $ip_address);
			$session_out = "0";
			$stmt->bindParam(':p_session_id_out', $session_out, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return $session_out;
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$errorHelper->sendMail(CursorToArrayHelper::getExceptionTraceAsString($ex));
			$errorHelper->siteErrorLog(CursorToArrayHelper::getExceptionTraceAsString($ex));
			throw new Zend_Exception(CursorToArrayHelper::getExceptionTraceAsString($ex));
		}
	}

	//manage limits for player on web site - new procedure in new package
    /**
     * @param $session_id
     * @param $limit_types_name
     * @param $limit_in
     * @param $start_date
     * @param $end_date
     * @param $duration
     * @return mixed
     * @throws Zend_Exception
     */
	public function manageLimitsWDelay($session_id, $limit_types_name, $limit_in, $start_date, $end_date, $duration){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL CS_USER_UTILITY.MANAGE_LIMITS_W_DELAY(:p_session_id_in, :p_limit_types_name_in, :p_limit_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_duration_in)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_limit_types_name_in', $limit_types_name);
			$stmt->bindParam(':p_limit_in', $limit_in);
			$stmt->bindParam(':p_start_date_in', $start_date);
			$stmt->bindParam(':p_end_date_in', $end_date);
			$stmt->bindParam(':p_duration_in', $duration);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return true;
		}catch(Zend_Exception $ex){
			//can return exception ORA-20708: Provided limit type does not exist!
			/*Ukoliko upisete limit koji
			* nije definisan u bazi (ili napravite gresku pri nazivu limita) dobicete ORA-20708*/
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$errorHelper->sendMail(CursorToArrayHelper::getExceptionTraceAsString($ex));
			$errorHelper->siteErrorLog(CursorToArrayHelper::getExceptionTraceAsString($ex));
			throw new Zend_Exception(CursorToArrayHelper::getExceptionTraceAsString($ex));
		}
	}

	//logout from web site using pc session
    /**
     * @param $pc_session_id
     * @return mixed
     * @throws Zend_Exception
     */
	public function siteLogoutPC($pc_session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL SITE_LOGIN.LOGOUT_PC_SESSION(:p_session_id_in)');
			$stmt->bindParam(':p_session_id_in', $pc_session_id);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return true;
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			throw new Zend_Exception($ex);
		}
	}

    /**
     * @param $site_session_id
     * @param $pc_session_id
     * @return bool
     * @throws Zend_Exception
     */
	public function siteLogout($site_session_id, $pc_session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL SITE_LOGIN.LOGOUT_SITE_SESSION_W_PCS(:p_site_session_id_in, :p_pc_session_id_in)');
			$stmt->bindParam(':p_site_session_id_in', $site_session_id);
			$stmt->bindParam(':p_pc_session_id_in', $pc_session_id);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return true;
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$errorHelper->sendMail(CursorToArrayHelper::getExceptionTraceAsString($ex));
			$errorHelper->siteErrorLog(CursorToArrayHelper::getExceptionTraceAsString($ex));
			return false;
		}
	}

	//site login using pc session
    /**
     * @param $username
     * @param $password
     * @param $mac_address
     * @param $version
     * @param $ip_address
     * @param $country
     * @param $city
     * @return mixed
     * @throws Zend_Exception
     */
	public function siteLoginPC($username, $password, $mac_address, $version, $ip_address, $country, $city){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL SITE_LOGIN.SITE_LOGIN_PCS(:p_user_name_in, :p_password_in, :p_mac_address_in, :p_version_in, :p_ip_address_in, :p_country_in, :p_city_in, :P_LIST_OF_GAMES_OUT, :p_session_id_out, :p_credits_out, :p_currency_out, :p_player_id_out)');
			$stmt->bindParam(':p_user_name_in', $username);
			$stmt->bindParam(':p_password_in', $password);
			$stmt->bindParam(':p_mac_address_in', $mac_address);
			$stmt->bindParam(':p_version_in', $version);
			$stmt->bindParam(':p_ip_address_in', $ip_address);
			$stmt->bindParam(':p_country_in', $country);
			$stmt->bindParam(':p_city_in', $city);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":P_LIST_OF_GAMES_OUT", $cursor);
			$session_id_out = "-1";
			$stmt->bindParam(':p_session_id_out', $session_id_out, SQLT_CHR, 255);
			$credits_out = "0";
			$stmt->bindParam(':p_credits_out', $credits_out, SQLT_CHR, 255);
			$currency_out = "";
			$stmt->bindParam(':p_currency_out', $currency_out, SQLT_CHR, 255);
			$player_id_out = "";
			$stmt->bindParam(':p_player_id_out', $player_id_out, SQLT_CHR, 255);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return array("session_id"=>$session_id_out, "credits"=>$credits_out,
			"currency"=>$currency_out, "player_id"=>$player_id_out, "list_of_games"=>$cursor);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$code = $ex->getCode();
			/*if received exception is unknown*/
			if($code != "20121" && $code != "20122" && $code != "1422" && $code != "20500" && $code != "20707"){
				$errorHelper = new ErrorHelper();
				$errorHelper->sendMail(CursorToArrayHelper::getExceptionTraceAsString($ex));
				$errorHelper->siteErrorLog(CursorToArrayHelper::getExceptionTraceAsString($ex));
				return INTERNAL_ERROR;
			}
			/*if received exception is wrong username or password type*/
			if($code == "20121"){
				//sends email when user tries to login with wrong username password
				$errorHelper = new ErrorHelper();
				//$errorHelper->sendMail($config, "User has tried to login with wrong username or password! <br /> Username: " . $username . "<br /> Mac Address: " . $mac_address .  "<br /> IP Address:" . $ip_address . "<br /> Country:" . $country . "<br /> City: " . $city . " <br /> Exception error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex));
				$errorHelper->siteErrorLog("User has tried to login with wrong username or password! Username: " . $username . " Mac Address: " . $mac_address .  " IP Address:" . $ip_address . " Country:" . $country . " City: " . $city);
				return WRONG_USERNAME_PASSWORD;
			}
			/*if received exception is wrong physical address type*/
			if($code == "20122"){
				//sends email when user tries to login with wrong physical address type
				$errorHelper = new ErrorHelper();
				$errorHelper->sendMail("User has tried to login with wrong physical address! <br /> Username:" . $username .  "<br/> Mac Address: " . $mac_address . "<br /> IP Address:" . $ip_address . "<br /> Country: " . $country . "<br /> City:" . $city . " <br /> Exception error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex));
				$errorHelper->siteErrorLog("User has tried to login with wrong physical address! Username:" . $username .  " Mac Address: " . $mac_address . " IP Address:" . $ip_address . " Country: " . $country . " City:" . $city);
				return WRONG_PHYSICAL_ADDRESS;
			}
			if($code == "20500"){
				//sends email when user tries to login with activated banned limit by himself on web site
				$errorHelper = new ErrorHelper();
				$errorHelper->sendMail("User has tried to login with his own active banned limit! <br /> Username:" . $username .  "<br/> Mac Address: " . $mac_address . "<br /> IP Address:" . $ip_address . "<br /> Country: " . $country . "<br /> City:" . $city . " <br /> Exception error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex));
				$errorHelper->siteErrorLog("User has tried to login with his own active banned limit! Username:" . $username .  " Mac Address: " . $mac_address . " IP Address:" . $ip_address . " Country: " . $country . " City:" . $city);
				return PLAYER_BANNED_LIMIT;
			}
			if($code == "20707"){
				//sends email when user tries to login with wrong username or password more than allowed number of times
				$errorHelper = new ErrorHelper();
				$errorHelper->sendMail("User has tried to login with wrong username or password more than allowed number of times! <br /> Username:" . $username .  "<br/> Mac Address: " . $mac_address . "<br /> IP Address:" . $ip_address . "<br /> Country: " . $country . "<br /> City:" . $city . " <br /> Exception error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex));
				$errorHelper->siteErrorLog("User has tried to login with wrong username or password more than allowed number of times! Username:" . $username .  " Mac Address: " . $mac_address . " IP Address:" . $ip_address . " Country: " . $country . " City:" . $city);
				return LOGIN_TOO_MANY_TIMES;
			}
			return INTERNAL_ERROR;
		}
	}

	//returns player validation hash for his registration through web site
    /**
     * @param $player_id
     * @return mixed
     * @throws Zend_Exception
     */
	public function getPlayerValidationHash($player_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGE_SUBJECTS.GET_PLAYER_VALIDATION_HASH(:p_player_id_in, :p_validation_hash_out)');
			$stmt->bindParam(':p_player_id_in', $player_id);
			$validation_hash = '';
			$stmt->bindParam(':p_validation_hash_out', $validation_hash, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return $validation_hash;
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$errorHelper->sendMail(CursorToArrayHelper::getExceptionTraceAsString($ex));
			$errorHelper->siteErrorLog(CursorToArrayHelper::getExceptionTraceAsString($ex));
			return null;
		}
	}

	//returns player id from site's session id
    /**
     * @param $site_session_id
     * @return mixed
     * @throws Zend_Exception
     */
	public function sessionIdToPlayerId($site_session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL SITE_LOGIN.M$SESSION_ID_TO_PLAYER_ID(:p_session_id_in, :p_player_id_out, :p_player_name_out, :p_player_password_out, :p_player_pass_name_out)');
			$stmt->bindParam(':p_session_id_in', $site_session_id);
			$player_id = "0";
			$stmt->bindParam(':p_player_id_out', $player_id, SQLT_CHR, 255);
			$player_name = '';
			$stmt->bindParam(':p_player_name_out', $player_name, SQLT_CHR, 255);
			$player_password = '';
			$stmt->bindParam(':p_player_password_out', $player_password, SQLT_CHR, 255);
			$player_pass_name = '';
			$stmt->bindParam(':p_player_pass_name_out', $player_pass_name, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("player_id"=>$player_id, "player_name"=>$player_name, "player_pass_name"=>$player_pass_name, "player_password"=>$player_password);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
            $message = "WebSite Model - sessionIdToPlayerId method exception: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($message, $message);
			return false;
		}
	}

	//returns limits setup for player on web site
    /**
     * @param $session_id
     * @return mixed
     * @throws Zend_Exception
     */
	public function listLimits($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			//$stmt = $dbAdapter->prepare('CALL SITE_LOGIN.M$LIST_LIMITS(:p_session_id_in, :p_list_limits_out)');
			$stmt = $dbAdapter->prepare('CALL CS_USER_UTILITY.LIST_LIMITS(:p_session_id_in, :p_list_limits_out)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":p_list_limits_out", $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			return $cursor;
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$errorHelper->sendMail("WebSite Model - listLimits method <br /> CS_USER_UTILITY.LIST_LIMITS(p_session_id_in = {$session_id}, ) exception: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex));
			$errorHelper->siteErrorLog("WebSite Model - listLimits method <br /> CS_USER_UTILITY.LIST_LIMITS(p_session_id_in = {$session_id}, ) exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex));
			throw new Zend_Exception($ex);
		}
	}

	//manage limits for player on web site - new procedure in new package
    /**
     * @param $session_id
     * @param $limit_types_name
     * @param $limit_in
     * @param $start_date
     * @param $end_date
     * @param $duration
     * @return mixed
     * @throws Zend_Exception
     */
	public function manageLimits($session_id, $limit_types_name, $limit_in, $start_date, $end_date, $duration){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL CS_USER_UTILITY.M$MANAGE_LIMITS(:p_session_id_in, :p_limit_types_name_in, :p_limit_in,' . "to_date(:p_start_date_in, 'DD-Mon-YYYY')," . "to_date(:p_end_date_in, 'DD-Mon-YYYY')" . ', :p_duration_in)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_limit_types_name_in', $limit_types_name);
			$stmt->bindParam(':p_limit_in', $limit_in);
			$stmt->bindParam(':p_start_date_in', $start_date);
			$stmt->bindParam(':p_end_date_in', $end_date);
			$stmt->bindParam(':p_duration_in', $duration);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return true;
		}catch(Zend_Exception $ex){
			//can return exception ORA-20708: Provided limit type does not exist!
			/*Ukoliko upisete limit koji
			* nije definisan u bazi (ili napravite gresku pri nazivu limita) dobicete ORA-20708*/
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$errorHelper->sendMail("WebSite Model - manageLimits method exception: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex));
			$errorHelper->siteErrorLog("WebSite Model - manageLimits method exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex));
			throw new Zend_Exception(CursorToArrayHelper::getExceptionTraceAsString($ex));
		}
	}

	//site login through web site
    /**
     * @param $site_name
     * @param $username
     * @param $password
     * @param $mac_address
     * @param $version
     * @param $ip_address
     * @param $country
     * @param $city
     * @param $device_aff_id
     * @param $gp_mac_address
     * @return array
     * @throws Zend_Exception
     */
	public function siteLogin($site_name, $username, $password, $mac_address, $version, $ip_address,
	$country, $city, $device_aff_id, $gp_mac_address){
        //ini_set('display_errors', 0);
        //ini_set('error_reporting', 0);
        //ini_set('')
		//DEBUG THIS CODE
		/*
		$errorHelper = new ErrorHelper();
		$errorHelper->sendMail("CALL SITE_LOGIN.SITE_LOGIN_MALTA(:p_site_name_in = $site_name, :p_username_in = $username, :p_password_in = $password, :p_session_id_out,
			:p_mac_address_in = $mac_address, :p_version_in = $version, :p_ip_address_in = $ip_address, :p_country_in = $country, :p_city_in = $city, :p_device_aff_id_in = $device_aff_id, :p_gp_mac_address_in = $gp_mac_address,
			:p_list_of_games_out, :p_pc_session_id_out, :p_credits_out, :p_currency_out, :p_player_id_out, :p_device_out, :p_aff_id_out,
			:p_player_verif_status_out, :p_error_message_out)");
		*/
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
        $session_id_out = "-1";
        $pc_session_id = "";
        $credits = "";
        $currency = "";
        $player_id = "";
        $device = "";
        $aff_id_out  = "";
        $player_verif_status_out = "";
        $p_error_message_out = "";

        $config = Zend_Registry::get('config');
	    if($config->measureSpeedPerformance == "true") {
            $before_time = microtime(true);
        }


		try{
			//$stmt = $dbAdapter->prepare('CALL SITE_LOGIN.M$SITE_LOGIN(:p_site_name_in, :p_username_in, :p_password_in, :p_session_id_out,
			$stmt = $dbAdapter->prepare('CALL SITE_LOGIN.SITE_LOGIN_MALTA(:p_site_name_in, :p_username_in, :p_password_in, :p_session_id_out, :p_mac_address_in, :p_version_in, :p_ip_address_in, :p_country_in, :p_city_in, :p_device_aff_id_in, :p_gp_mac_address_in, :p_list_of_games_out, :p_pc_session_id_out, :p_credits_out, :p_currency_out, :p_player_id_out, :p_device_out, :p_aff_id_out, :p_player_verif_status_out, :p_error_message_out)');
			$stmt->bindParam(':p_site_name_in', $site_name);
			$stmt->bindParam(':p_username_in', $username);
			$stmt->bindParam(':p_password_in', $password);
			$stmt->bindParam(':p_session_id_out', $session_id_out, SQLT_CHR, 255);
			$stmt->bindParam(':p_mac_address_in', $mac_address);
			$stmt->bindParam(':p_version_in', $version);
			$stmt->bindParam(':p_ip_address_in', $ip_address);
			$stmt->bindParam(':p_country_in', $country);
			$stmt->bindParam(':p_city_in', $city);
			$stmt->bindParam(':p_device_aff_id_in', $device_aff_id);
			$stmt->bindParam(':p_gp_mac_address_in', $gp_mac_address);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":p_list_of_games_out", $cursor);
			$stmt->bindParam(':p_pc_session_id_out', $pc_session_id, SQLT_CHR, 255);
			$stmt->bindParam(':p_credits_out', $credits, SQLT_CHR, 255);
			$stmt->bindParam(':p_currency_out', $currency, SQLT_CHR, 255);
			$stmt->bindParam(':p_player_id_out', $player_id, SQLT_CHR, 255);
			$stmt->bindParam(':p_device_out', $device, SQLT_CHR, 255);
			$stmt->bindParam(':p_aff_id_out', $aff_id_out, SQLT_CHR, 255);
			$stmt->bindParam(':p_player_verif_status_out', $player_verif_status_out, SQLT_CHR, 255);
			$stmt->bindParam(':p_error_message_out', $p_error_message_out, SQLT_CHR, 255);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
            //DEBUG THIS CODE
            /*
            $errorHelper = new ErrorHelper();
            $errorHelper->sendMail("CALL SITE_LOGIN.SITE_LOGIN_MALTA(:p_site_name_in = $site_name, :p_username_in = $username, :p_password_in = $password, :p_session_id_out,
              :p_mac_address_in = $mac_address, :p_version_in = $version, :p_ip_address_in = $ip_address, :p_country_in = $country, :p_city_in = $city, :p_device_aff_id_in = $device_aff_id, :p_gp_mac_address_in = $gp_mac_address,
              :p_list_of_games_out, :p_pc_session_id_out = {$pc_session_id}, :p_credits_out = {$credits}, :p_currency_out = {$currency}, :p_player_id_out = {$player_id}, :p_device_out = {$device},
               :p_aff_id_out = {$aff_id_out}, :p_player_verif_status_out = {$player_verif_status_out}, :p_error_message_out = {$p_error_message_out})");
            */

            if($config->measureSpeedPerformance == "true") {
                $after_time = microtime(true);
                $difference_time = number_format(($after_time-$before_time), 4);
                $errorHelper = new ErrorHelper();
                $measure_time_message = "SITE_LOGIN.SITE_LOGIN_MALTA(:p_site_name_in = $site_name, :p_username_in = $username, :p_password_in = $password, :p_session_id_out,
                  :p_mac_address_in = $mac_address, :p_version_in = $version, :p_ip_address_in = $ip_address, :p_country_in = $country, :p_city_in = $city, :p_device_aff_id_in = $device_aff_id, :p_gp_mac_address_in = $gp_mac_address,
                  :p_list_of_games_out, :p_pc_session_id_out = {$pc_session_id}, :p_credits_out = {$credits}, :p_currency_out = {$currency}, :p_player_id_out = {$player_id}, :p_device_out = {$device},
                  :p_aff_id_out = {$aff_id_out}, :p_player_verif_status_out = {$player_verif_status_out}, :p_error_message_out = {$p_error_message_out})";
                $measure_time_message .= "<br /> REQUIRED_TIME = {$difference_time}";
                $errorHelper->siteAccessLog($measure_time_message);
            }

			if($p_error_message_out == ""){
				return array("status"=>OK, "site_session_id"=>(int)$session_id_out, "list_games"=>$cursor, "pc_session_id"=>(int)$pc_session_id,
				"credits"=>$credits, "currency"=>$currency, "player_id"=>(int)$player_id, "device"=>$device, "aff_id_out"=>(int)$aff_id_out,
				"player_verif_status"=>$player_verif_status_out);
			}
			//if player's account is locked he will be notified with email to unlock his account
			else if($p_error_message_out == 'ERROR.ORA-20707.Too many unsuccessful login tries!'){
				return array("status"=>NOK, "player_id"=>(int)$player_id, "message"=>LOGIN_TOO_MANY_TIMES);
			}else{
                $errorHelper = new ErrorHelper();
                $errorHelper->sendMail("WebSite Model - siteLogin method exception: <br />");
                $errorHelper->siteErrorLog("WebSite Model - siteLogin method exception: ");
                return array("status"=>NOK, "message"=>NOK_EXCEPTION);
            }
	    }
		catch(Zend_Db_Cursor_Exception $ex1){
    	    $errorHelper = new ErrorHelper();
			//$errorHelper->sendMail("WebSite Model - siteLogin method Zend_Db_Cursor_Exception exception: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex1));
			$errorHelper->siteErrorLog("WebSite Model - siteLogin method Zend_Db_Cursor_Exception exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex1));
            /*$cursor = array();
				return array("status"=>OK, "site_session_id"=>(int)$session_id_out, "list_games"=>$cursor, "pc_session_id"=>(int)$pc_session_id,
				"credits"=>$credits, "currency"=>$currency, "player_id"=>(int)$player_id, "device"=>$device, "aff_id_out"=>(int)$aff_id_out,
				"player_verif_status"=>$player_verif_status_out);*/
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
        }
        catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$code = $ex->getCode();
			//if received exception is wrong username or password type with more than allowed number of attempts
			if($code != "20121" && $code != "20122" && $code != "1422" && $code != "20500" && $code != "20707" && $code != "20399"){
				$errorHelper = new ErrorHelper();
				//$errorHelper->sendMail("WebSite Model - siteLogin method Zend_Exception exception: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex));
				$errorHelper->siteErrorLog("WebSite Model - siteLogin method Zend_Exception exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex));
				return array("status"=>NOK, "message"=>NOK_EXCEPTION);
			}
			//if received exception is wrong username or password type
			if($code == "20121"){
				//sends email when user tries to login with wrong username password
				$errorHelper = new ErrorHelper();
				//$errorHelper->sendMail("User has tried to login with wrong username or password on web site! <br /> Site domain: " . $site_name . "<br /> Username: " . $username . "<br /> Mac Address: " . $mac_address .  "<br /> IP Address:" . $ip_address . "<br /> Country:" . $country . "<br /> City: " . $city);
				$errorHelper->siteErrorLog("User has tried to login with wrong username or password! Site domain: " . $site_name . " Username: " . $username . " Mac Address: " . $mac_address .  " IP Address:" . $ip_address . " Country:" . $country . " City: " . $city);
				return array("status"=>NOK, "message"=>WRONG_USERNAME_PASSWORD);
			}
			//if received exception is wrong physical address type
			if($code == "20122"){
				//sends email when user tries to login with wrong physical address type
				$errorHelper = new ErrorHelper();
				//$errorHelper->sendMail("User has tried to login with wrong physical address on web site! <br /> Site domain: " . $site_name . "<br /> Username:" . $username .  "<br/> Mac Address: " . $mac_address . "<br /> IP Address:" . $ip_address . "<br /> Country: " . $country . "<br /> City:" . $city . " <br /> Exception message: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex));
				$errorHelper->siteErrorLog("User has tried to login with wrong physical address! Site domain: " . $site_name . " Username:" . $username .  " Mac Address: " . $mac_address . " IP Address:" . $ip_address . " Country: " . $country . " City:" . $city);
				return array("status"=>NOK, "message"=>WRONG_PHYSICAL_ADDRESS);
			}
			if($code == "20500"){
				//sends email when user tries to login with active banned limit on web site
				$errorHelper = new ErrorHelper();
				//$errorHelper->sendMail("User has tried to login with his own active banned limit on web site! <br /> Site domain: " . $site_name . "<br /> Username:" . $username .  "<br/> Mac Address: " . $mac_address . "<br /> IP Address:" . $ip_address . "<br /> Country: " . $country . "<br /> City:" . $city . " <br /> Exception message: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex));
				$errorHelper->siteErrorLog("User has tried to login with his own active banned limit! Site domain: " . $site_name . " Username:" . $username .  " Mac Address: " . $mac_address . " IP Address:" . $ip_address . " Country: " . $country . " City:" . $city);
				return array("status"=>NOK, "message"=>PLAYER_BANNED_LIMIT);
			}
			if($code == "20399"){
				//sends email when user tries to login with active banned limit on web site
				$errorHelper = new ErrorHelper();
				//$errorHelper->sendMail("User has tried to login with his prohibited country on web site! <br /> Site domain: " . $site_name . "<br /> Username:" . $username .  "<br/> Mac Address: " . $mac_address . "<br /> IP Address:" . $ip_address . "<br /> Country: " . $country . "<br /> City:" . $city . " <br /> Exception message: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex));
				$errorHelper->siteErrorLog("User has tried to login with his prohibited country on web site! Site domain: " . $site_name . " Username:" . $username .  " Mac Address: " . $mac_address . " IP Address:" . $ip_address . " Country: " . $country . " City:" . $city);
				return array("status"=>NOK, "message"=>PLAYER_COUNTRY_PROHIBITED);
			}
			/*if($code == "20707"){
				//sends email when user tries to login more than allowed number of times on web site
				$errorHelper = new ErrorHelper();
				$errorHelper->sendMail("User has tried to login with wrong username or password more than allowed number of times on web site! <br /> Site domain: " . $site_name . "<br /> Username:" . $username .  "<br/> Mac Address: " . $mac_address . "<br /> IP Address:" . $ip_address . "<br /> Country: " . $country . "<br /> City:" . $city . " <br /> Exception message: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex));
				$errorHelper->siteErrorLog("User has tried to login with wrong username or password more than allowed number of times! Site domain: " . $site_name . " Username:" . $username .  " Mac Address: " . $mac_address . " IP Address:" . $ip_address . " Country: " . $country . " City:" . $city);
				return array("status"=>NOK, "message"=>LOGIN_TOO_MANY_TIMES);
			}*/
			$errorHelper = new ErrorHelper();
			$errorHelper->sendMail("WebSite Model - siteLogin method Zend_Exception exception: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex));
			$errorHelper->siteErrorLog("WebSite Model - siteLogin method Zend_Exception exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex));
			return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

	//reset player's password
    /**
     * @param $session_id
     * @param $subject_id
     * @param $password_new
     * @return mixed
     * @throws Zend_Exception
     */
	public function resetPassword($session_id, $subject_id, $password_new){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.M$RESET_PASSWORD(:p_session_id_in, :p_subject_id_in, :p_password_new_in)');
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_subject_id_in', $subject_id);
			$stmt->bindParam(':p_password_new_in', $password_new);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return 1;
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$errorHelper->sendMail("WebSite Model - resetPassword method exception: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex));
			$errorHelper->siteErrorLog("WebSite Model - resetPassword method exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex));
			throw new Zend_Exception(CursorToArrayHelper::getExceptionTraceAsString($ex));
		}
	}

	//list countries on web site forms
    /**
     * @return mixed
     * @throws Zend_Exception
     */
	public function listCountries(){
	    $config = Zend_Registry::get('config');

        if($config->measureSpeedPerformance == "true") {
            $before_time = microtime(true);
        }

	    if($config->db->enable_cache == "true"){
	        $cacheObj = Zend_Registry::get('db_cache');
			$cache_key_name = "WEB_REPORTS__M_LIST_COUNTRIES";
			$cache_key_name = str_replace(array("."), "_", $cache_key_name);
		    $result = unserialize($cacheObj->load($cache_key_name) );
		    if(!isset($result) || $result == null || !$result) {
		        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
                $dbAdapter = Zend_Registry::get('db_auth');
                $dbAdapter->beginTransaction();
                try {
                    $stmt = $dbAdapter->prepare('CALL WEB_REPORTS.M$LIST_COUNTRIES(:p_countries_list_out)');
                    $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
                    $stmt->bindCursor(":p_countries_list_out", $cursor);
                    $stmt->execute(null, false);
                    $dbAdapter->commit();
                    $cursor->execute();
                    $cursor->free();
                    $dbAdapter->closeConnection();
                    $result = $cursor;

                    $cache_key_name = "WEB_REPORTS__M_LIST_COUNTRIES";
					$cache_key_name = str_replace(array("."), "_", $cache_key_name);
                    $cacheObj->save(serialize($result), $cache_key_name);

                    if($config->measureSpeedPerformance == "true") {
                        $after_time = microtime(true);
                        $difference_time = number_format(($after_time-$before_time), 4);
                        $errorHelper = new ErrorHelper();
                        $measure_time_message = "WEB_REPORTS.M\$LIST_COUNTRIES(:p_countries_list_out)";
                        $measure_time_message .= "<br /> REQUIRED_TIME = {$difference_time}";
                        $errorHelper->siteAccessLog($measure_time_message);
                    }

                    return $result;
                } catch (Zend_Exception $ex) {
                    $dbAdapter->rollBack();
                    $dbAdapter->closeConnection();
                    $errorHelper = new ErrorHelper();
                    $errorHelper->sendMail("WebSite Model - listCountries method exception: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex));
                    $errorHelper->siteErrorLog("WebSite Model - listCountries method exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex));
                    throw new Zend_Exception(CursorToArrayHelper::getExceptionTraceAsString($ex));
                }
            }else{
		        return $result;
            }
        }else {
            /* @var $dbAdapter Zend_Db_Adapter_Oracle */
            $dbAdapter = Zend_Registry::get('db_auth');
            $dbAdapter->beginTransaction();
            try {
                $stmt = $dbAdapter->prepare('CALL WEB_REPORTS.M$LIST_COUNTRIES(:p_countries_list_out)');
                $cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
                $stmt->bindCursor(":p_countries_list_out", $cursor);
                $stmt->execute(null, false);
                $dbAdapter->commit();
                $cursor->execute();
                $cursor->free();
                $dbAdapter->closeConnection();

                if($config->measureSpeedPerformance == "true") {
                    $after_time = microtime(true);
                    $difference_time = number_format(($after_time-$before_time), 4);
                    $errorHelper = new ErrorHelper();
                    $measure_time_message = "WEB_REPORTS.M\$LIST_COUNTRIES(:p_countries_list_out)";
                    $measure_time_message .= "<br /> REQUIRED_TIME = {$difference_time}";
                    $errorHelper->siteAccessLog($measure_time_message);
                }

                return $cursor;
            } catch (Zend_Exception $ex) {
                $dbAdapter->rollBack();
                $dbAdapter->closeConnection();
                $errorHelper = new ErrorHelper();
                $errorHelper->sendMail("WebSite Model - listCountries method exception: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex));
                $errorHelper->siteErrorLog("WebSite Model - listCountries method exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex));
                throw new Zend_Exception(CursorToArrayHelper::getExceptionTraceAsString($ex));
            }
        }
	}

    /**
     * @param $affiliate_id
     * @return array
     */
    public function listRegistrationAllowedCountries($affiliate_id){
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL MANAGMENT_CORE.LIST_REGISTRATION_LIMIT(:p_aff_id, :p_inherited_from, :p_list_reg_limit_e, :p_list_reg_limit_d)');
            $cursorEnabled = new Zend_Db_Cursor_Oracle($dbAdapter);
            $cursorDisabled = new Zend_Db_Cursor_Oracle($dbAdapter);
            $stmt->bindParam(':p_aff_id', $affiliate_id);
            $inheritedFrom = '';
            $stmt->bindParam(':p_inherited_from', $inheritedFrom, SQLT_CHR, 255);
            $stmt->bindCursor(':p_list_reg_limit_e', $cursorEnabled);
            $stmt->bindCursor(':p_list_reg_limit_d', $cursorDisabled);
            $stmt->execute(null, false);
            $dbAdapter->commit();
            $cursorEnabled->execute();
            $cursorDisabled->execute();
            $cursorEnabled->free();
            $cursorDisabled->free();
            $dbAdapter->closeConnection();
            return array('status'=>OK, 'inherited' => $inheritedFrom, 'cursorE' => $cursorEnabled, 'cursorD' => $cursorDisabled);
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $errorHelper = new ErrorHelper();
            $message = CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->siteError($message, $message);
            return array("status"=>NOK, "message"=>$message);
        }
    }

	//get username on player details to web site
    /**
     * @param $name
     * @param $familyname
     * @param $birthday
     * @param $email
     * @return array
     * @throws Zend_Exception
     */
	public function ForgotUsername($name, $familyname, $birthday, $email){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL CS_USER_UTILITY.forgot_username(:p_first_name_in, :p_last_name_in, :p_birthday_in, :p_email_in, :p_username_out, :p_status_out, :p_player_id_out)');
			$stmt->bindParam(':p_first_name_in', $name);
			$stmt->bindParam(':p_last_name_in', $familyname);
			$stmt->bindParam(':p_birthday_in', $birthday);
			$stmt->bindParam(':p_email_in', $email);
			$username = "";
			$stmt->bindParam(':p_username_out', $username, SQLT_CHR, 255);
			$status = NOK;
			$stmt->bindParam(':p_status_out', $status, SQLT_CHR, 255);
			$player_id = "";
			$stmt->bindParam(':p_player_id_out', $player_id, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			if ($status == OK){
				return array("status"=>OK, "username"=>$username, "player_id"=>$player_id);
			}
			return array("status"=>NOK);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$mail_message = "WebSite Model - ForgotUsername method exception: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = "WebSite Model - ForgotUsername method exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($mail_message, $log_message);
			return array("status"=>NOK_EXCEPTION);
		}
	}

	//get link to reset password on web site
    /**
     * @param $username
     * @param $answer
     * @return array
     * @throws Zend_Exception
     */
	public function ForgotPasswordWithSecurityAnswer($username, $answer){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL CS_USER_UTILITY.forgot_username_w_sec_answer(:p_username_in, :p_sec_answer_in, :p_temporary_code_out, :p_email_out, :p_status_out, :p_player_id_out)');
			$stmt->bindParam(':p_username_in', $username);
			$stmt->bindParam(':p_sec_answer_in', $answer);
			$id = "";
			$stmt->bindParam(':p_temporary_code_out', $id, SQLT_CHR, 255);
			$email = "";
			$stmt->bindParam(':p_email_out', $email, SQLT_CHR, 255);
			$status = "";
			$stmt->bindParam(':p_status_out', $status, SQLT_CHR, 255);
			$player_id = "";
			$stmt->bindParam(':p_player_id_out', $player_id, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			//DEBUG HERE
			/*
			$errorHelper = new ErrorHelper();
			$message = "CS_USER_UTILITY.forgot_username_w_sec_answer(:p_username_in = {$username}, :p_sec_answer_in = {$answer}, :p_temporary_code_out = {$id}, :p_email_out = {$email}, :p_status_out = {$status}, :p_player_id_out = {$player_id})";
			$errorHelper->siteError($message);
			*/
			if ($status == OK){
				return array("status"=>OK, "id" => $id, "email" => $email, "player_id"=>$player_id);
			}
			return array("status"=>NOK);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
            $code = $ex->getCode();
			$errorHelper = new ErrorHelper();
            $message = "WebSite Model - ForgotPasswordWithSecurityAnswer method exception: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($message, $message);
            //raise_application_error(-20301, 'Security answer is invalid.');
            //raise_application_error(-20302, 'Subject not found.');
            if($code == "20301"){
                return array("status" => NOK, "code" => $code, "message" => NOK_SECURITY_ANWSER_INVALID);
            }else if($code == "20302") {
                return array("status" => NOK, "code" => $code, "message" => NOK_NO_SUBJECT_FOUND);
            }else{
                return array("status" => NOK, "code" => $code, "message" => NOK_EXCEPTION);
            }
		}
	}

	//get link to reset password on web site on players personal details
    /**
     * @param $username
     * @param $name
     * @param $familyname
     * @param $birthday
     * @param $email
     * @return array
     * @throws Zend_Exception
     */
	public function ForgotPasswordWithPersonalData($username, $name, $familyname, $birthday, $email){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL CS_USER_UTILITY.forgot_username_w_pers_data(:p_username_in, :p_first_name_in, :p_last_name_in, :p_birthday_in, :p_email_in, :p_temporary_code_out, :p_status_out, :p_player_id_out)');
			$stmt->bindParam(':p_username_in', $username);
			$stmt->bindParam(':p_first_name_in', $name);
			$stmt->bindParam(':p_last_name_in', $familyname);
			$stmt->bindParam(':p_birthday_in', $birthday);
			$stmt->bindParam(':p_email_in', $email);
			$id = "";
			$stmt->bindParam(':p_temporary_code_out', $id, SQLT_CHR, 255);
			$status = "";
			$stmt->bindParam(':p_status_out', $status, SQLT_CHR, 255);
			$player_id = "";
			$stmt->bindParam(':p_player_id_out', $player_id, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			//DEBUG THIS CODE
			/*
			$errorHelper = new ErrorHelper();
			$message = "CS_USER_UTILITY.forgot_username_w_pers_data(:p_username_in = {$username}, :p_first_name_in = {$name}, :p_last_name_in = {$familyname}, :p_birthday_in = {$birthday}, :p_email_in = {$email}, :p_temporary_code_out = {$id}, :p_status_out = {$status}, :p_player_id_out = {$player_id})";
			$errorHelper->siteError($message);
			*/
			if ($status == OK){
				return array("status"=>OK, "id"=>$id, "status_out"=>$status, "player_id"=>$player_id);
			}
			return array("status"=>NOK);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$errorHelper->sendMail("WebSite Model - ForgotPasswordWithPersonalData method exception: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex));
			$errorHelper->siteErrorLog("WebSite Model - ForgotPasswordWithPersonalData method exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex));
			return array("status"=>NOK);
		}
	}

	//check for id given to player on username
    /**
     * @param $username
     * @param $id
     * @return mixed
     * @throws Zend_Exception
     */
	public function CheckTemporaryID($username, $id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL CS_USER_UTILITY.validate_temp_code_forgot(:p_username_in, :p_id_in, :p_status_out)');
			$stmt->bindParam(':p_username_in', $username);
			$stmt->bindParam(':p_id_in', $id);
			$status = "";
			$stmt->bindParam(':p_status_out', $status, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return $status;
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$errorHelper->sendMail("WebSite Model - CheckTemporaryID method exception: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex));
			$errorHelper->siteErrorLog("WebSite Model - CheckTemporaryID method exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex));
			throw new Zend_Exception(CursorToArrayHelper::getExceptionTraceAsString($ex));
		}
	}

	//change password to player through web site
    /**
     * @param $username
     * @param $id
     * @param $password
     * @param $question
     * @param $answer
     * @return mixed
     * @throws Zend_Exception
     */
	public function ChangePassword($username, $id, $password, $question, $answer){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL CS_USER_UTILITY.change_pass_secret_q_a(:p_username_in, :p_id_in, :p_password_in, :p_question_in, :p_answer_in ,:p_status_out)');
			$stmt->bindParam(':p_username_in', $username);
			$stmt->bindParam(':p_id_in', $id);
			$stmt->bindParam(':p_password_in', $password);
			$stmt->bindParam(':p_question_in', $question);
			$stmt->bindParam(':p_answer_in', $answer);
			$status = "";
			$stmt->bindParam(':p_status_out', $status, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return $status;
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$errorHelper->sendMail("WebSite Model - ChangePassword method exception: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex));
			$errorHelper->siteErrorLog("WebSite Model - ChangePassword method exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex));
			throw new Zend_Exception(CursorToArrayHelper::getExceptionTraceAsString($ex));
		}
	}

	//set security question from web site to database
    /**
     * @param $username
     * @param $password
     * @param $question
     * @param $answer
     * @return mixed
     * @throws Zend_Exception
     */
	public function SetSecurityQuestionAnswer($username, $password, $question, $answer){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL CS_USER_UTILITY.set_secret_q_a_extern(:p_username_in, :p_password_in, :p_question_in, :p_answer_in ,:p_status_out)');
			$stmt->bindParam(':p_username_in', $username);
			$stmt->bindParam(':p_password_in', $password);
			$stmt->bindParam(':p_question_in', $question);
			$stmt->bindParam(':p_answer_in', $answer);
			$status = "";
			$stmt->bindParam(':p_status_out', $status, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return $status;
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$errorHelper->sendMail("WebSite Model - SetSecurityQuestionAnswer method exception: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex));
			$errorHelper->siteErrorLog("WebSite Model - SetSecurityQuestionAnswer method exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex));
			throw new Zend_Exception(CursorToArrayHelper::getExceptionTraceAsString($ex));
		}
	}

	//get security question to web site from database
    /**
     * @param $username
     * @return mixed
     * @throws Zend_Exception
     */
	public function GetSecurityQuestionAnswer($username){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try {
			$stmt = $dbAdapter->prepare('CALL CS_USER_UTILITY.get_secret_q_a_extern(:p_username_in, :p_question_out, :p_answer_out, :p_status_out)');
			$stmt->bindParam(':p_username_in', $username);
			$status = "";
			$question = "";
			$answer = "";
			$stmt->bindParam(':p_question_out', $question, SQLT_CHR, 255);
			$stmt->bindParam(':p_answer_out', $answer, SQLT_CHR, 255);
			$stmt->bindParam(':p_status_out', $status, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			//DEBUG THIS CODE
			/*
			$errorHelper = new ErrorHelper();
			$message = "CS_USER_UTILITY.get_secret_q_a_extern(:p_username_in = {$username}, :p_question_out = {$question}, :p_answer_out = {$answer}, :p_status_out = {$status})";
			$errorHelper->siteError($message);
			*/
			return array("question"=>$question , "answer"=>$answer);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$errorHelper->sendMail("WebSite Model - GetSecurityQuestionAnswer method exception: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex));
			$errorHelper->siteErrorLog("WebSite Model - GetSecurityQuestionAnswer method exception: " . CursorToArrayHelper::getExceptionTraceAsString($ex));
			throw new Zend_Exception(CursorToArrayHelper::getExceptionTraceAsString($ex));
		}
	}
}
