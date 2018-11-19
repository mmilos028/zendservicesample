<?php
/**
 * Enter description here ...
 * Catches and deals with exceptions and errors from Zend framework
 */
require_once HELPERS_DIR . DS . 'ErrorHelper.php';

class ErrorController extends Zend_Controller_Action{
	public function init(){
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout->disableLayout();
	}
	/* errorAction method for exception errors */
	public function errorAction(){
		$errors = $this->_getParam('error_handler');
		$this->view->title = "Errors page";
		switch ($errors->type) {
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
				$this->view->message = $errors->exception;
				$this->view->exception = $errors->exception;
				break;
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
				$this->view->message = $errors->exception;
				$this->view->exception = $errors->exception;
				break;
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
				$this->getResponse()->setHttpResponseCode(404);
				$this->view->message = $errors->exception;
				$this->view->exception = $errors->exception;
				break;
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_OTHER:
				$this->view->message = $errors->exception;
				$this->view->exception = $errors->exception;
				break;
			default:
				$this->view->message =$errors->exception;
				$this->view->exception = $errors->exception;
				break;
		}
        $message = $errors->exception;
        $helperError = new ErrorHelper();
        $helperError->serviceError($message, $message);
		if ($this->getInvokeArg('displayExceptions') == true)
			$this->view->exception = $errors->exception;
		$this->view->request = $errors->request;
		header('Location: http://www.google.com/');
	}
}

