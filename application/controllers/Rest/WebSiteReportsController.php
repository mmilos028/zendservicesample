<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'CurrencyListHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'WebSiteEmailHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
require_once HELPERS_DIR . DS . 'NumberHelper.php';
require_once HELPERS_DIR . DS . 'StringHelper.php';

/**
 * 
 * Web site web service REPORTS ...
 *
 */

class Rest_WebSiteReportsManager extends Zend_Controller_Action {

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
				"\n\n /onlinecasinoservice/rest/web-site-reports";
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
	 * 
	 * List limits ...
	 * @return mixed
	 */
	public function listLimitsAction(){
        $session_id = strip_tags($this->getRequest()->getParam('session_id', null));
        require_once "services" . DS . "WebSiteReportsManager.php";
        WebSiteReportsManager::listLimits($session_id);
	}
	
	
	/**
	 * 
	 * List limits with fixed response ...
	 * @return mixed
	 */
	public function listLimitsNewAction(){
        $session_id = strip_tags($this->getRequest()->getParam('session_id', null));
        require_once "services" . DS . "WebSiteReportsManager.php";
        WebSiteReportsManager::listLimitsNew($session_id);
	}
	
	/**
	 * 
	 * List player history method ...
	 * @return mixed
	 */
	public function listPlayerHistoryAction(){
        $session_id = strip_tags($this->getRequest()->getParam('session_id', null));
        $start_date = strip_tags($this->getRequest()->getParam('start_date', null));
        $end_date = strip_tags($this->getRequest()->getParam('end_date', null));
        $page_number = strip_tags($this->getRequest()->getParam('page_number', 1));
        $per_page = strip_tags($this->getRequest()->getParam('per_page', 50));
        $column = strip_tags($this->getRequest()->getParam('column', 1));
        $order = strip_tags($this->getRequest()->getParam('order', 'asc'));
        require_once "services" . DS . "WebSiteReportsManager.php";
        WebSiteReportsManager::listPlayerHistory($session_id, $start_date, $end_date, $page_number, $per_page, $column, $order);
	}
	
	/**
	*
	* List player history details method ...
	* @return mixed
	*/
	public function listPlayerHistoryDetailsAction(){
        $session_id = strip_tags($this->getRequest()->getParam('session_id', null));
        $page_number = strip_tags($this->getRequest()->getParam('page_number', 1));
        $per_page = strip_tags($this->getRequest()->getParam('per_page', 50));
        $column = strip_tags($this->getRequest()->getParam('column', 1));
        $order = strip_tags($this->getRequest()->getParam('order', 'asc'));
        require_once "services" . DS . "WebSiteReportsManager.php";
        WebSiteReportsManager::listPlayerHistoryDetails($session_id, $page_number, $per_page, $column, $order);
	}
	
	/**
	*
	*/
	public function listPlayerHistorySubdetailsAction(){
        $session_id = strip_tags($this->getRequest()->getParam('session_id', null));
        $page_number = strip_tags($this->getRequest()->getParam('page_number', 1));
        $per_page = strip_tags($this->getRequest()->getParam('per_page', 50));
        $column = strip_tags($this->getRequest()->getParam('column', 1));
        $order = strip_tags($this->getRequest()->getParam('order', 'asc'));
        require_once "services" . DS . "WebSiteReportsManager.php";
        WebSiteReportsManager::listPlayerHistorySubdetails($session_id, $page_number, $per_page, $column, $order);
	}
	
	/**
	*
	*/
	public function listCreditTransfersAction(){
        $session_id = strip_tags($this->getRequest()->getParam('session_id', null));
        $start_date = strip_tags($this->getRequest()->getParam('start_date', null));
        $end_date = strip_tags($this->getRequest()->getParam('end_date', null));
        $page_number = strip_tags($this->getRequest()->getParam('page_number', 1));
        $per_page = strip_tags($this->getRequest()->getParam('per_page', 50));
        $column = strip_tags($this->getRequest()->getParam('column', 1));
        $order = strip_tags($this->getRequest()->getParam('order', 'asc'));
        require_once "services" . DS . "WebSiteReportsManager.php";
        WebSiteReportsManager::listCreditTransfers($session_id, $start_date, $end_date, $page_number, $per_page, $column, $order);
	}

    /**
	*
	*/
	public function listAvailableBonusCampaignsAction(){
        $affiliate_username = strip_tags($this->getRequest()->getParam('affiliate_username', null));
        $player_id = strip_tags($this->getRequest()->getParam('player_id'));
        require_once "services" . DS . "WebSiteReportsManager.php";
        WebSiteReportsManager::listAvailableBonusCampaigns($affiliate_username, $player_id);
	}


    /**
	*
	*/
	public function listPlayerAvailableBonusCampaignsAction(){
        $player_id = strip_tags($this->getRequest()->getParam('player_id'));
        require_once "services" . DS . "WebSiteReportsManager.php";
        WebSiteReportsManager::listPlayerAvailableBonusCampaigns($player_id);
	}

    /**
	*
	*/
	public function listPlayerActiveBonusesAndPromotionsAction(){
		$player_id = strip_tags($this->getRequest()->getParam('player_id'));
        require_once "services" . DS . "WebSiteReportsManager.php";
        WebSiteReportsManager::listPlayerActiveBonusesAndPromotions($player_id);
	}

    /**
     *
     */
    public function listTopWonJackpotsAction(){
        $affiliate_name = strip_tags($this->getRequest()->getParam('affiliate_name'));
        require_once "services" . DS . "WebSiteReportsManager.php";
        WebSiteReportsManager::listPlayerActiveBonusesAndPromotions($affiliate_name);
    }

    /**
     *
     */
    public function listCurrentJackpotLevelsAction(){
        $affiliate_name = strip_tags($this->getRequest()->getParam('affiliate_name'));
        $currency = strip_tags($this->getRequest()->getParam('currency'));
        require_once "services" . DS . "WebSiteReportsManager.php";
        WebSiteReportsManager::listCurrentJackpotLevels($affiliate_name, $currency);
    }

    /**
     *
     */
    public function listHighWinsAction(){
        $affiliate_name = strip_tags($this->getRequest()->getParam('affiliate_name'));
        require_once "services" . DS . "WebSiteReportsManager.php";
        WebSiteReportsManager::listHighWins($affiliate_name);
    }
}