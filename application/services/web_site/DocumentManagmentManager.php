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
class DocumentManagmentManager {	
	
	/**
	 * 
	 * tests if access is through whitelisted ip address range ...
	 */
	private function isSecureConnection(){
		$config = Zend_Registry::get('config');
		//will check site ip address caller if true
		if($config->checkSiteIpAddress == "true"){
			$ip_addresses = explode(' ', $config->siteIpAddress);
			$host_ip_address = IPHelper::getRealIPAddress();
			$status = in_array($host_ip_address, $ip_addresses);
			if(!$status){
				$errorHelper = new ErrorHelper();
				$mail_message = $log_message = 'DocumentManagment on web site: Host with blacklisted ip address ' . $host_ip_address . ' is trying to connect to web site web service.';
				$errorHelper->siteError($mail_message, $log_message);
			}
			return $status;
		} else {
            return true;
        }
	}
	/**
	 * 
	 * save player's document information into database ...
	 * @param int $site_session_id
	 * @param string $document_site
	 * @param string $document_location
	 * @param string $document_file_name
     * @return mixed
	 */
	public function uploadDocument($site_session_id, $document_site, $document_location, 
	$document_file_name){
		if(!$this->isSecureConnection()){
			return NON_SSL_CONNECTION;
		}
		if(!isset($site_session_id) || !isset($document_site) || !isset($document_location) 
		&& !isset($document_file_name)){
			return NOK_INVALID_DATA;
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
				return OK;
			}else{
				return $result['message'];
			}
		}catch(Zend_Exception $ex){
			$errorHelper = new ErrorHelper();
			$mail_message = 'Error uploading player document: <br /> ' . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$log_message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($mail_message, $log_message);
			return NOK_EXCEPTION;
		}	
	}
	
	/**
	*
	* List players documents ...
	* @param int $site_session_id
	* @param int $player_id
	* @return mixed
	*/
	public function listPlayersDocuments($site_session_id, $player_id){		
		if(!$this->isSecureConnection()){
			return NON_SSL_CONNECTION;
		}
		if(!isset($site_session_id) && !isset($player_id)){
			return NOK_INVALID_DATA;
		}
		$site_session_id = strip_tags($site_session_id);
		$player_id = strip_tags($player_id);
		require_once MODELS_DIR . DS . 'DocumentManagmentModel.php';
		$modelDocumentManagment = new DocumentManagmentModel();
		$result = $modelDocumentManagment->listPlayerDocuments($site_session_id, $player_id);
		if($result['status'] == NOK){
			//NOK_EXCEPTION | NOK_INVALID_SESSION_TYPE
			return $result['message'];
		}
		$arrData = CursorToArrayHelper::cursorToArray($result["list_documents"]);
		$resArray = array();
		$no_items = count($arrData);
		for($i=0;$i<$no_items; $i++){
			if($arrData[$i]['document_site'] == "dummy") {
                continue;
            }
			$resArray[] = array(
				"user_document_id"=>$arrData[$i]['user_document_id'], 
				"document_location"=>$arrData[$i]['document_location'],
				"document_file_name"=>$arrData[$i]['document_file_name'],
				"document_upload_time"=>$arrData[$i]['document_upload_time'], 
				"document_verified"=>$arrData[$i]['document_verified'], 
				"document_verified_by"=>$arrData[$i]['document_verified_by'],
				"document_verification_time"=>$arrData[$i]['document_verification_time'],
				"player_id"=>$arrData[$i]['player_id'],
				"player_name"=>$arrData[$i]['player_name'],
				"verified_by"=>$arrData[$i]['verified_by']
			);
		}
		return $resArray;
	}
}