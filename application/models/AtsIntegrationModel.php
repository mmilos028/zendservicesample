<?php
require_once MODELS_DIR . DS . 'SubjectTypesModel.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
class AtsIntegrationModel {
    //error constants
    public function __construct(){
    }
    //enable terminal with pin code
    /**
     * @param $username
     * @return string
     * @throws Zend_Exception
     */
   public function checkUsername($username){
       /* @var $dbAdapter Zend_Db_Adapter_Oracle */
       $dbAdapter = Zend_Registry::get('db_ats_auth');
       $dbAdapter->beginTransaction();
       try{
           $stmt = $dbAdapter->prepare('CALL ATS_INTEGRATION.CHECK_USERNAME(:p_username, :p_result)');
           $stmt->bindParam(':p_username', $username);
           $status_out = '';
           $stmt->bindParam(':p_result', $status_out, SQLT_CHR, 255);
           $stmt->execute();
           $dbAdapter->commit();
           $dbAdapter->closeConnection();
           return $status_out;
       }catch(Zend_Exception $ex){
           $dbAdapter->rollBack();
           $dbAdapter->closeConnection();
           $code = $ex->getCode();
           if($code == "20911"){
               $errorHelper = new ErrorHelper();
               $message = ":p_username_in = {$username}, :p_result =" . CursorToArrayHelper::getExceptionTraceAsString($ex);
               $errorHelper->serviceError($message, $message);
               return $ex->getMessage();
           }
           else{
               $errorHelper = new ErrorHelper();
               $message = ":p_username_in = {$username}, :p_result =" . CursorToArrayHelper::getExceptionTraceAsString($ex);
               $errorHelper->serviceError($message, $message);
               return $ex->getMessage();
           }
       }
   }

    /**
     * @param $username
     * @param $password
     * @param $parent_id
     * @param $parent_name
     * @param $country
     * @return string
     * @throws Zend_Exception
     */
    public function createPartner($username, $password, $parent_id, $parent_name, $country){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_ats_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL ATS_INTEGRATION.ADD_SUBJECT_FROM_ATS(:p_username, :p_password, :p_ats_subject_id, :p_parent_name, :p_country_name_in, :p_result)');
            $stmt->bindParam(':p_username', $username);
            $stmt->bindParam(':p_password', $password);
            $stmt->bindParam(':p_ats_subject_id', $parent_id);
            $stmt->bindParam(':p_parent_name', $parent_name);
            $stmt->bindParam(':p_country_name_in', $country);
            $status_out = '';
            $stmt->bindParam(':p_result', $status_out, SQLT_CHR, 255);
            $stmt->execute();
            $dbAdapter->commit();
            $dbAdapter->closeConnection();
            return $status_out;
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $code = $ex->getCode();
            if($code == "20911"){
                $errorHelper = new ErrorHelper();
                $message = ":p_username_in = {$username}, :p_result =" . CursorToArrayHelper::getExceptionTraceAsString($ex);
                $errorHelper->serviceError($message, $message);
                return $ex->getMessage();
            }
            else{
                $errorHelper = new ErrorHelper();
                $message = ":p_username_in = {$username}, :p_result =" . CursorToArrayHelper::getExceptionTraceAsString($ex);
                $errorHelper->serviceError($message, $message);
                return $ex->getMessage();
            }
        }
    }
}