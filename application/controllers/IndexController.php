<?php

class IndexController extends Zend_Controller_Action{
	public function init(){
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout->disableLayout();
	}
	public function indexAction(){
		header('Location: http://www.google.com/');
	}
}