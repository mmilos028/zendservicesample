<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';

/**
 * 
 * Manages documents for web site ...
 *
 */
class WebSiteDocumentManagementManager {
	

	/**
	 * 
	 * save player's document information into database ...
	 * @param int $site_session_id
	 * @param string $document_site
	 * @param string $document_location
	 * @param string $document_file_name
     * @return mixed
	 */
	public static function uploadDocument($site_session_id, $document_site, $document_location,
	$document_file_name){
		if(!isset($site_session_id) || !isset($document_site) || !isset($document_location) 
		&& !isset($document_file_name)){
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
            exit($json_message);
		}		
		try{
			$site_session_id = strip_tags($site_session_id);
			$document_location = strip_tags($document_location);
			$document_file_name = strip_tags($document_file_name);
			require_once MODELS_DIR . DS . 'DocumentManagmentModel.php';
			$modelDocumentManagmentModel = new DocumentManagmentModel();
			$result = $modelDocumentManagmentModel->storePlayerUploadDoc($site_session_id, 
			$document_site, $document_location, $document_file_name);
			if($result['status'] == OK){
				$json_message = Zend_Json::encode(array("status"=>OK, "message"=>OK));
                exit($json_message);
			}else{
                $json_message = Zend_Json::encode(array("status"=>OK, "message"=>$result['message']));
                exit($json_message);
			}
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = 'Error uploading player document: <br /> ' . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($mail_message, $log_message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
            exit($json_message);
		}	
	}
	
	/**
	*
	* List players documents ...
	* @param int $site_session_id
	* @param int $player_id
	* @return mixed
	*/
	public static function listPlayersDocuments($site_session_id, $player_id){
		if(!isset($site_session_id) && !isset($player_id)){
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_INVALID_DATA));
            exit($json_message);
		}
		$site_session_id = strip_tags($site_session_id);
		$player_id = strip_tags($player_id);
        try {
            require_once MODELS_DIR . DS . 'DocumentManagmentModel.php';
            $modelDocumentManagment = new DocumentManagmentModel();
            $result = $modelDocumentManagment->listPlayerDocuments($site_session_id, $player_id);
            if ($result['status'] == NOK) {
                //NOK_EXCEPTION | NOK_INVALID_SESSION_TYPE
                $json_message = Zend_Json::encode(array("status" => NOK, "message" => $result['message']));
                exit($json_message);
            }
            $arrData = CursorToArrayHelper::cursorToArray($result["list_documents"]);
            $resArray = array();
            $no_items = count($arrData);
            for ($i = 0; $i < $no_items; $i++) {
                if ($arrData[$i]['document_site'] == "dummy") {
                    continue;
                }
                $resArray[] = array(
                    "user_document_id" => $arrData[$i]['user_document_id'],
                    "document_location" => $arrData[$i]['document_location'],
                    "document_file_name" => $arrData[$i]['document_file_name'],
                    "document_upload_time" => $arrData[$i]['document_upload_time'],
                    "document_verified" => $arrData[$i]['document_verified'],
                    "document_verified_by" => $arrData[$i]['document_verified_by'],
                    "document_verification_time" => $arrData[$i]['document_verification_time'],
                    "player_id" => $arrData[$i]['player_id'],
                    "player_name" => $arrData[$i]['player_name'],
                    "verified_by" => $arrData[$i]['verified_by']
                );
            }
            $json_message = Zend_Json::encode(array("status"=>OK, "report"=>$resArray));
            exit($json_message);
        }catch(Zend_Exception $ex){
            $errorHelper = new ErrorHelper();
			$mail_message = 'Error list player documents: <br /> ' . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($mail_message, $log_message);
			$json_message = Zend_Json::encode(array("status"=>NOK, "message"=>NOK_EXCEPTION));
            exit($json_message);
        }
	}
}