<?php
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
class SubjectTypesModel{
	public function __construct(){
	}
	//returns subject type, from managment types returns name-type of subject
    /**
     * @param $subject_name
     * @return mixed
     * @throws Zend_Exception
     */
	public function getSubjectType($subject_name){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		try{
			$stmt = $dbAdapter->prepare('BEGIN :p := DYNVAR.VAL(:var_in); END;');
			$stmt->bindParam(':var_in', $subject_name);
			$value_out = "";
			$stmt->bindParam(':p', $value_out, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return $value_out;
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			throw new Zend_Exception($message);
		}
	}
}