<?php
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';

class LuckyGameIntegrationModel
{
    public function __construct()
    {
    }

    /**
     * returns token for lucky game integration
     * @param $session_id
     * @param $game_id
     * @return mixed
     * @throws Zend_Exception
     */
    public function getLuckyIntegrationToken($session_id, $game_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL LUCKY_INTEGRATION.GET_TOKEN(:p_session_id, :p_game_id, :p_game_session_id, :p_path)');
            $stmt->bindParam(':p_session_id', $session_id);
            $stmt->bindParam(':p_game_id', $game_id);
            $game_session_id_out = "";
            $stmt->bindParam(':p_game_session_id', $game_session_id_out, SQLT_CHR, 255);
            $path = "";
            $stmt->bindParam(':p_path', $path, SQLT_CHR, 255);
            $stmt->execute();
            $dbAdapter->commit();
            $dbAdapter->closeConnection();
            return array(
                "status" => OK,
                "game_session_id_out"=>$game_session_id_out,
                "path" => $path
            );
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $errorHelper = new ErrorHelper();
            $message = "LuckyGameIntegrationModel::getLuckyIntegrationToken(session_id = {$session_id}, game_id = {$game_id}) 
            <br /> LUCKY_INTEGRATION.GET_TOKEN(:p_session_id = {$session_id}, :p_game_id = {$game_id}, :p_game_session_id, :p_path) 
            <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);
            return array("status" => NOK, "message" => NOK_EXCEPTION);
        }
    }

    /**
     * @param $session_id
     * @return array
     * @throws Zend_Exception
     */
    public function closeLuckyGameIntegrationSession($session_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
        $dbAdapter = Zend_Registry::get('db_auth');
        $dbAdapter->beginTransaction();
        try{
            $stmt = $dbAdapter->prepare('CALL LUCKY_INTEGRATION.CLOSE_GAME_SESSION(:p_session_id, :p_status_out)');
            $stmt->bindParam(':p_session_id', $session_id);
            $status_out = "";
            $stmt->bindParam(':p_status_out', $status_out, SQLT_CHR, 255);
            $stmt->execute();
            $dbAdapter->commit();
            $dbAdapter->closeConnection();
            return array(
                "status" => OK,
                "status_out" => $status_out,
            );
        }catch(Zend_Exception $ex){
            $dbAdapter->rollBack();
            $dbAdapter->closeConnection();
            $errorHelper = new ErrorHelper();
            $message = "LuckyGameIntegrationModel::closeLuckyGameIntegrationSession(session_id = {$session_id}) 
            <br /> LUCKY_INTEGRATION.CLOSE_GAME_SESSION(:p_session_id = {$session_id}, :p_status_out) 
            <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
            $errorHelper->serviceError($message, $message);
            return array("status" => NOK, "message" => NOK_EXCEPTION);
        }
    }
}