<?php
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
class DocumentManagmentModel{
	public function __construct(){
	}

	/** Procedure to upload players documents and write it to database ... */
    /**
     * @param $site_session_id
     * @param $document_site
     * @param $document_location
     * @param $document_file_name
     * @return array
     * @throws Zend_Exception
     */
	public function storePlayerUploadDoc($site_session_id, $document_site, $document_location, $document_file_name){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL CS_USER_UTILITY.store_player_upload_doc(:p_session_id_in, :p_document_site_in, :p_document_location_in, :p_document_file_name_in)');
			$stmt->bindParam(':p_session_id_in', $site_session_id);
			$stmt->bindParam(':p_document_site_in', $document_site);
			$stmt->bindParam(':p_document_location_in', $document_location);
			$stmt->bindParam(':p_document_file_name_in', $document_file_name);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$code = $ex->getCode();
			if($code != "20717" && $code != "20718"){
				$errorHelper = new ErrorHelper();
				$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
				$errorHelper->siteError($message, $message);
				return array("status"=>NOK, "message"=>NOK_EXCEPTION);
			}
			if($code == "20717"){
				//sends email when user tries to upload document with wrong session type
				//raise_application_error(-20717, 'The session id you provided is not of site session type or not a valid session id!')
				$errorHelper = new ErrorHelper();
				$mail_message = "User has tried to upload document with wrong session type! <br /> Site / BO session id: {$site_session_id} <br /> Document site: {$document_site} <br /> Document location: {$document_location} <br /> Document file name: {$document_file_name} <br /> Exception message: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
				$log_message = "User has tried to upload document with wrong session type! Site / BO session id: {$site_session_id} Document site: {$document_site} Document location: {$document_location} Document file name: {$document_file_name} Exception message: " . CursorToArrayHelper::getExceptionTraceAsString($ex); 
				$errorHelper->siteError($mail_message, $log_message);
				return array("status"=>NOK, "message"=>NOK_INVALID_SESSION_TYPE);
			}
			if($code == "20718"){
				//raise_application_error(-20718, 'Such file name already used!');
				//sends email when user tries to upload document with existing file name
				$errorHelper = new ErrorHelper();
				$mail_message = "User has tried to upload document with existing file name! <br /> Site session id: {$site_session_id} <br /> Document site: {$document_site} <br /> Document location: {$document_location} <br /> Document file name: {$document_file_name} <br /> Exception message: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
				$log_message = "User has tried to upload document with existing file name! Site / BO session id: {$site_session_id} Document site: {$document_site} Document location: {$document_location} Document file name: {$document_file_name}";
				$errorHelper->siteError($mail_message, $log_message);
				return array("status"=>NOK, "message"=>NOK_EXISTING_FILE_NAME);
			}
            return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

    /**
     * @param $site_session_id
     * @param $player_id
     * @return array
     */
	public function listPlayerDocuments($site_session_id, $player_id){
        //site_session_id ili bo_session_id
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL CS_USER_UTILITY.list_player_documents(:p_session_id_in, :p_player_id_in, :p_user_docs_out)');
			$stmt->bindParam(':p_session_id_in', $site_session_id);
			$stmt->bindParam(':p_player_id_in', $player_id);			
			$cursorListDocuments = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':p_user_docs_out', $cursorListDocuments);			
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursorListDocuments->execute();
			$dbAdapter->closeConnection();
			return array("status"=>OK, "list_documents"=>$cursorListDocuments);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$code = $ex->getCode();
			if($code != "20719"){
				$errorHelper = new ErrorHelper();
				$message = CursorToArrayHelper::getExceptionTraceAsString($ex);				
				$errorHelper->siteError($message, $message);
				return array("status"=>NOK, "message"=>NOK_EXCEPTION);
			}
			if($code == "20719"){
				//raise_application_error(-20719, 'The session id you provided is not of back office/site session type or not a valid session id!');
				//sends email when user tries to login with wrong username password
				$errorHelper = new ErrorHelper();
				$mail_message = "User has tried to list player documents with wrong session type! <br /> Site / BO session id: {$site_session_id} <br /> Player id: {$player_id} <br /> Exception message: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
				$log_message = "User has tried to list player documents with wrong session type! Site / BO session id: {$site_session_id} Player id: {$player_id} Exception message: " . CursorToArrayHelper::getExceptionTraceAsString($ex);
				$errorHelper->siteError($mail_message, $log_message);
				return array("status"=>NOK, "message"=>NOK_INVALID_SESSION_TYPE);
			}
            return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}

    /**
     * @param $bo_session_id
     * @param $player_id
     * @param $player_verification_status
     * @return array
     * @throws Zend_Exception
     */
	public function setPlayerVerificationStatus($bo_session_id, $player_id, $player_verification_status){
		//backoffice or site session id
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL CS_USER_UTILITY.set_player_verif_status_extern(:p_session_id_in, :p_player_id_in, :p_player_verif_status_in)');
			$stmt->bindParam(':p_session_id_in', $bo_session_id);
			$stmt->bindParam(':p_player_id_in', $player_id);
			$stmt->bindParam(':p_player_verif_status_in', $player_verification_status);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("status"=>OK);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$code = $ex->getCode();
			if($code != "20716"){
				$errorHelper = new ErrorHelper();
				$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
				$errorHelper->siteError($message, $message);
				return array("status"=>NOK, "message"=>NOK_EXCEPTION);
			}
			if($code == "20716"){
				//raise_application_error(-20716, 'The session id you provided is not of back office/site session type or not a valid session id!');
				$errorHelper = new ErrorHelper();
				$mail_message = "User has tried to verify player status with wrong session type! <br />Site / BO session id: {$bo_session_id} <br />Player id: {$player_id} <br />Verify status: {$player_verification_status} <br />Exception error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
				$log_message = "User has tried to verify player status with wrong session type! Site / BO session id: {$bo_session_id} Player id: {$player_id} Verify status: {$player_verification_status}";
				$errorHelper->siteError($mail_message, $log_message);
				return array("status"=>NOK, "message"=>NOK_INVALID_SESSION_TYPE);
			}
            return array("status"=>NOK, "message"=>NOK_EXCEPTION);
		}
	}
}