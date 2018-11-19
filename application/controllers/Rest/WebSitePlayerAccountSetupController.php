<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';

/**
 *
 * Web site web service main calls ...
 *
 */

class Rest_WebSitePlayerAccountSetupController extends Zend_Controller_Action {


    public function init(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		//set output header Content-Type to application/json
		header('Content-Type: application/json');
	}

	public function preDispatch(){
        header("Access-Control-Allow-Origin: *");
		if($_SERVER['REQUEST_METHOD'] != "POST") {
			$config = Zend_Registry::get('config');
			if($config->onlinecasinoserviceWSDLMode == "false" || $config->onlinecasinoserviceWSDLMode == false){ // not in wsdl mode
				$response = array(
					"status"=>NOK,
					"message"=>NOK_POST_METHOD_MESSAGE
				);
				exit(Zend_Json::encode($response));
			}else{
				$message =
				"\n\n /onlinecasinoservice/rest/web-site-player-account-setup ";
				exit($message);
			}
		}
	}

	public function indexAction(){
		$config = Zend_Registry::get('config');
		if($config->onlinecasinoserviceWSDLMode == "false" || $config->onlinecasinoserviceWSDLMode == false){ // not in wsdl mode
			header('Location: http://www.google.com/');
		}
	}

	/**
	 * @return mixed
	 */
	public function changeTermsAndConditionsForCbcAction(){
        $session_id = strip_tags($this->getRequest()->getParam('session_id', null));

        require_once "services" . DS . "WebSitePlayerAccountSetupManager.php";
		WebSitePlayerAccountSetupManager::ChangeTermsAndConditionsForCBC($session_id);
	}

    /**
	 * @return mixed
	 */
	public function changeTermsAndConditionsForGglAction(){
        $session_id = strip_tags($this->getRequest()->getParam('session_id', null));

        require_once "services" . DS . "WebSitePlayerAccountSetupManager.php";
		WebSitePlayerAccountSetupManager::ChangeTermsAndConditionsForGGL($session_id);
	}

    /**
	 * @return mixed
	 */
	public function checkTermsAndConditionsAction(){
        $session_id = strip_tags($this->getRequest()->getParam('session_id', null));

        require_once "services" . DS . "WebSitePlayerAccountSetupManager.php";
		WebSitePlayerAccountSetupManager::CheckTermsAndConditions($session_id);
	}

    /**
	 * @return mixed
	 */
	public function checkTemporaryIdAction(){
        $username = strip_tags($this->getRequest()->getParam('username', null));
        $id = strip_tags($this->getRequest()->getParam('id', null));

        require_once "services" . DS . "WebSitePlayerAccountSetupManager.php";
		WebSitePlayerAccountSetupManager::CheckTemporaryID($username, $id);
	}

    /**
	 * @return mixed
	 */
	public function changePasswordAction(){
        $username = strip_tags($this->getRequest()->getParam('username', null));
        $id = strip_tags($this->getRequest()->getParam('id', null));
        $password = strip_tags($this->getRequest()->getParam('password', null));
        $question = strip_tags($this->getRequest()->getParam('question', null));
        $answer = strip_tags($this->getRequest()->getParam('answer', null));

        require_once "services" . DS . "WebSitePlayerAccountSetupManager.php";
		WebSitePlayerAccountSetupManager::ChangePassword($username, $id, $password, $question, $answer);
	}

    /**
	 * @return mixed
	 */
	public function setSecurityQuestionAnwserAction(){
        $username = strip_tags($this->getRequest()->getParam('username', null));
        $password = strip_tags($this->getRequest()->getParam('password', null));
        $question = strip_tags($this->getRequest()->getParam('question', null));
        $answer = strip_tags($this->getRequest()->getParam('answer', null));

        require_once "services" . DS . "WebSitePlayerAccountSetupManager.php";
		WebSitePlayerAccountSetupManager::SetSecurityQuestionAnswer($username, $password, $question, $answer);
	}

    /**
	 * @return mixed
	 */
	public function getSecurityQuestionAction(){
        $username = strip_tags($this->getRequest()->getParam('username', null));

        require_once "services" . DS . "WebSitePlayerAccountSetupManager.php";
		WebSitePlayerAccountSetupManager::GetSecurityQuestion($username);
	}

    /**
	 * @return mixed
	 */
	public function responsibleGamingSetupAction(){
        $session_id = strip_tags($this->getRequest()->getParam('session_id', null));

        $monthly_deposit_limit = strip_tags($this->getRequest()->getParam('monthly_deposit_limit', null));
        $monthly_deposit_limit_start_date = strip_tags($this->getRequest()->getParam('monthly_deposit_limit_start_date', null));
        $monthly_deposit_limit_end_date = strip_tags($this->getRequest()->getParam('monthly_deposit_limit_end_date', null));

        $weekly_deposit_limit = strip_tags($this->getRequest()->getParam('weekly_deposit_limit', null));
        $weekly_deposit_start_date = strip_tags($this->getRequest()->getParam('weekly_deposit_start_date', null));
        $weekly_deposit_end_date = strip_tags($this->getRequest()->getParam('weekly_deposit_end_date', null));

        $daily_deposit_limit = strip_tags($this->getRequest()->getParam('daily_deposit_limit', null));
        $daily_deposit_start_date = strip_tags($this->getRequest()->getParam('daily_deposit_start_date', null));
        $daily_deposit_end_date = strip_tags($this->getRequest()->getParam('daily_deposit_end_date', null));

        $monthly_max_loss_limit = strip_tags($this->getRequest()->getParam('monthly_max_loss_limit', null));
        $monthly_max_loss_start_date = strip_tags($this->getRequest()->getParam('monthly_max_loss_start_date', null));
        $monthly_max_loss_end_date = strip_tags($this->getRequest()->getParam('monthly_max_loss_end_date', null));
        $weekly_max_loss_limit = strip_tags($this->getRequest()->getParam('weekly_max_loss_limit', null));

        $weekly_max_loss_start_date = strip_tags($this->getRequest()->getParam('weekly_max_loss_start_date', null));
        $weekly_max_loss_end_date = strip_tags($this->getRequest()->getParam('weekly_max_loss_end_date', null));
        $daily_max_loss_limit = strip_tags($this->getRequest()->getParam('daily_max_loss_limit', null));
        $daily_max_loss_start_date = strip_tags($this->getRequest()->getParam('daily_max_loss_start_date', null));
        $daily_max_loss_end_date = strip_tags($this->getRequest()->getParam('daily_max_loss_end_date', null));

        $max_stake_start_date = strip_tags($this->getRequest()->getParam('max_stake_start_date', null));
        $max_stake_end_date = strip_tags($this->getRequest()->getParam('max_stake_end_date', null));
        $max_stake = strip_tags($this->getRequest()->getParam('max_stake', null));
        $time_limit_minutes = strip_tags($this->getRequest()->getParam('time_limit_minutes', null));

        $banned_start_date = strip_tags($this->getRequest()->getParam('banned_start_date', null));
        $banned_end_date = strip_tags($this->getRequest()->getParam('banned_end_date', null));
        $banned_status = strip_tags($this->getRequest()->getParam('banned_status', null));

        require_once "services" . DS . "WebSitePlayerAccountSetupManager.php";
		WebSitePlayerAccountSetupManager::responsibleGamingSetup($session_id,
		$monthly_deposit_limit, $monthly_deposit_limit_start_date, $monthly_deposit_limit_end_date,
		$weekly_deposit_limit, $weekly_deposit_start_date, $weekly_deposit_end_date,
		$daily_deposit_limit, $daily_deposit_start_date, $daily_deposit_end_date,
		$monthly_max_loss_limit, $monthly_max_loss_start_date, $monthly_max_loss_end_date,
		$weekly_max_loss_limit, $weekly_max_loss_start_date, $weekly_max_loss_end_date,
		$daily_max_loss_limit, $daily_max_loss_start_date, $daily_max_loss_end_date,
		$max_stake_start_date, $max_stake_end_date, $max_stake, $time_limit_minutes,
		$banned_start_date, $banned_end_date, $banned_status);
	}

    /**
	 * @return mixed
	 */
	public function responsibleGamingSetupDelayAction(){
        $session_id = strip_tags($this->getRequest()->getParam('session_id', null));

        $monthly_deposit_limit = strip_tags($this->getRequest()->getParam('monthly_deposit_limit', null));
        $monthly_deposit_limit_start_date = strip_tags($this->getRequest()->getParam('monthly_deposit_limit_start_date', null));
        $monthly_deposit_limit_end_date = strip_tags($this->getRequest()->getParam('monthly_deposit_limit_end_date', null));

        $weekly_deposit_limit = strip_tags($this->getRequest()->getParam('weekly_deposit_limit', null));
        $weekly_deposit_start_date = strip_tags($this->getRequest()->getParam('weekly_deposit_start_date', null));
        $weekly_deposit_end_date = strip_tags($this->getRequest()->getParam('weekly_deposit_end_date', null));

        $daily_deposit_limit = strip_tags($this->getRequest()->getParam('daily_deposit_limit', null));
        $daily_deposit_start_date = strip_tags($this->getRequest()->getParam('daily_deposit_start_date', null));
        $daily_deposit_end_date = strip_tags($this->getRequest()->getParam('daily_deposit_end_date', null));

        $monthly_max_loss_limit = strip_tags($this->getRequest()->getParam('monthly_max_loss_limit', null));
        $monthly_max_loss_start_date = strip_tags($this->getRequest()->getParam('monthly_max_loss_start_date', null));
        $monthly_max_loss_end_date = strip_tags($this->getRequest()->getParam('monthly_max_loss_end_date', null));

        $weekly_max_loss_limit = strip_tags($this->getRequest()->getParam('weekly_max_loss_limit', null));
        $weekly_max_loss_start_date = strip_tags($this->getRequest()->getParam('weekly_max_loss_start_date', null));
        $weekly_max_loss_end_date = strip_tags($this->getRequest()->getParam('weekly_max_loss_end_date', null));

        $daily_max_loss_limit = strip_tags($this->getRequest()->getParam('daily_max_loss_limit', null));
        $daily_max_loss_start_date = strip_tags($this->getRequest()->getParam('daily_max_loss_start_date', null));
        $daily_max_loss_end_date = strip_tags($this->getRequest()->getParam('daily_max_loss_end_date', null));

        $max_stake_start_date = strip_tags($this->getRequest()->getParam('max_stake_start_date', null));
        $max_stake_end_date = strip_tags($this->getRequest()->getParam('max_stake_end_date', null));
        $max_stake = strip_tags($this->getRequest()->getParam('max_stake', null));
        $time_limit_minutes = strip_tags($this->getRequest()->getParam('time_limit_minutes', null));

        $banned_start_date = strip_tags($this->getRequest()->getParam('banned_start_date', null));
        $banned_end_date = strip_tags($this->getRequest()->getParam('banned_end_date', null));
        $banned_status = strip_tags($this->getRequest()->getParam('banned_status', null));

        require_once "services" . DS . "WebSitePlayerAccountSetupManager.php";
		WebSitePlayerAccountSetupManager::responsibleGamingSetupDelay($session_id,
		$monthly_deposit_limit, $monthly_deposit_limit_start_date, $monthly_deposit_limit_end_date,
		$weekly_deposit_limit, $weekly_deposit_start_date, $weekly_deposit_end_date,
		$daily_deposit_limit, $daily_deposit_start_date, $daily_deposit_end_date,
		$monthly_max_loss_limit, $monthly_max_loss_start_date, $monthly_max_loss_end_date,
		$weekly_max_loss_limit, $weekly_max_loss_start_date, $weekly_max_loss_end_date,
		$daily_max_loss_limit, $daily_max_loss_start_date, $daily_max_loss_end_date,
		$max_stake_start_date, $max_stake_end_date, $max_stake, $time_limit_minutes,
		$banned_start_date, $banned_end_date, $banned_status);
	}

    /**
	 * @return mixed
	 */
	public function banPlayerAction(){
        $username = strip_tags($this->getRequest()->getParam('username', null));
        $password = strip_tags($this->getRequest()->getParam('password', null));

        require_once "services" . DS . "WebSitePlayerAccountSetupManager.php";
		WebSitePlayerAccountSetupManager::banPlayer($username, $password);
	}

    /**
	 * @return mixed
	 */
	public function unlockAccountAction(){
        $player_id = strip_tags($this->getRequest()->getParam('player_id', null));

        require_once "services" . DS . "WebSitePlayerAccountSetupManager.php";
		WebSitePlayerAccountSetupManager::unlockAccount($player_id);
	}

}