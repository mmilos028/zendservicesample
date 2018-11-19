<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'WebSiteEmailHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';

class TestController extends Zend_Controller_Action{
	
	public function init(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
	}
	
	public function indexAction(){
        /*
		$start_time = microtime(true);
		$numcalls = 1000;
		require_once MODELS_DIR . DS . 'SessionModel.php';
		$modelSession = new SessionModel();
		for($i = 1; $i <= $numcalls; $i++){
			$modelSession->testPackages($i);
		}
		$end_time = microtime(true);
		var_dump("Zavrsio $numcalls poziva za " . ($end_time - $start_time) . " sekundi");
        */

    	$errorHelper = new ErrorHelper();

        $detected_ip_address = IPHelper::getRealIPAddress();
        $message = "TestController::indexAction() ";

        $message .= print_r($_REQUEST, true);

        $errorHelper->merchantError($message, $message);

        /*$message .= "<br /> Detected IP address = {$detected_ip_address} <br />";
        $message .= "<br /> GET VARIABLES <br />";
        foreach($_GET as $key => $value){
            $value = urldecode($value);
            $message .= "<br /> {$key} = {$value} <br />";
        }

        $message .= "<br /> POST VARIABLES <br />";
        foreach($_POST as $key => $value){
            $value = urldecode($value);
            $message .= "<br /> {$key} = {$value} <br />";
        }

        $message .= "<br /> REQUEST VARIABLES <br />";
        foreach($_REQUEST as $key => $value){
            $value = urldecode($value);
            $message .= "<br /> {$key} = {$value} <br />";
        }

        $errorHelper->merchantError($message, $message);

        print_r($message);*/
	}

    public function successAction(){
        $errorHelper = new ErrorHelper();

        $detected_ip_address = IPHelper::getRealIPAddress();
        $message = "TestController::successAction() ";

        $message .= print_r($_REQUEST, true);

        $errorHelper->merchantError($message, $message);
    }

    public function failureAction(){
        $errorHelper = new ErrorHelper();

        $detected_ip_address = IPHelper::getRealIPAddress();
        $message = "TestController::failureAction() ";

        $message .= print_r($_REQUEST, true);

        $errorHelper->merchantError($message, $message);
    }

    public function notificationAction(){
        $errorHelper = new ErrorHelper();

        $detected_ip_address = IPHelper::getRealIPAddress();
        $message = "TestController::notificationAction() ";

        $message .= print_r($_REQUEST, true);

        $errorHelper->merchantError($message, $message);
    }


	
}