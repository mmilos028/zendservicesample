<?php
class ZendAmfServiceBrowser{
	static public $ZEND_AMF_SERVER;
	private $_zendAmfServer;
	public function __construct(){
	}
	public function getServices(){
		$this->_zendAmfServer = ZendAmfServiceBrowser::$ZEND_AMF_SERVER;
		$methods = $this->_zendAmfServer->getFunctions();
		$methodsTable = '<methods>';
		foreach ($methods as $method => $value){
			$functionReflection = $methods[ $method ];
			$parameters = $functionReflection->getParameters();
			$methodsTable = $methodsTable . "<method name='$method'>";
			foreach ($parameters as $param)$methodsTable = $methodsTable . "<param name='$param->name'/>";
			$methodsTable = $methodsTable . "</method>";
		}
		$methodsTable = $methodsTable . '</methods>';
		unset( $methods );
		return $methodsTable;
	}
}