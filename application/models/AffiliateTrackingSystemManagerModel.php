<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';

class AffiliateTrackingSystemManagerModel
{
	public function __construct(){
	}

    /**
     * @param $start_date
     * @param $end_date
     * @param $affiliate_id
     * @return mixed
     * @throws Zend_Exception
     */
	public function getAffiliates($start_date, $end_date, $affiliate_id){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_ats_auth');
		$config = Zend_Registry::get("config");
		$dbAdapter->beginTransaction();
		try{		
			$stmt = $dbAdapter->prepare('CALL ATS_INTEGRATION.GET_AFFILIATE_INFO(:P_REQUEST, :P_LIST_AFF_INFO)');
			$stmt->bindParam(':P_REQUEST', $affiliate_id);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':P_LIST_AFF_INFO', $cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$dbAdapter->closeConnection();
			$arr_data = CursorToArrayHelper::cursorToArray($cursor);
			$arr_neto = $this->getNeto($start_date, $end_date);			
			$result = array();
			foreach($arr_data as $data){
				$neto_box = null;
				$currency_neto_box = '';
				$active = ($data['banned'] == 'N') ? 'Y' : 'N';
				foreach($arr_neto as $data_neto){
					if($data_neto['aff_id'] == $data['id']){
						$neto_box[] = array('neto'=>$data_neto['neto'],'currency'=>$data_neto['currency'],'day'=>$data_neto['day']);
						//$currency_neto_box = $data_neto['currency'];
						//break;
					}
				}	
				$result[] = array(
                    'start_date'=>$start_date,
                    'from_date'=>$end_date,
                    'for_affiliate_id'=>$affiliate_id,
                    'id'=>$data['id'],
                    'name'=>$data['name'],
                    'parent'=>$data['subject_id_for'],
                    'direct_players'=>$data['direct_players'],
					'all_players'=>$data['all_players'],
                    'active'=>$active,
                    'subject_level'=>$data['subject_level'],
					'e_mail'=>$data['e_mail'],
                    'first_name'=>$data['first_name'],
                    'last_name'=>$data['last_name'],
					'phone'=>$data['phone'],
                    'address'=>$data['address'],
                    'zip_code'=>$data['zip_code'],
					'city'=>$data['city'],
                    'currency'=>$data['currency'],
                    'neto'=>$neto_box
				);				
			}
			//$result = array_merge($result, $arr_neto);
			//return $arr_neto;
			// return $this->encrypt(json_encode($result));
			return json_encode($result);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = "ATS_INTEGRATION.GET_AFFILIATE_INFO(:P_REQUEST = {$affiliate_id}, :P_LIST_AFF_INFO) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return 0;
		}	
	}

    /**
     * @param $start_date
     * @param $end_date
     * @return mixed
     * @throws Zend_Exception
     */
	public function getNeto($start_date, $end_date){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_ats_auth');
		$config = Zend_Registry::get("config");
		$dbAdapter->beginTransaction();
		try{
			//$start_date = '01-May-14';
			//$end_date = '01-Sep-14';
			$stmt = $dbAdapter->prepare('CALL ATS_INTEGRATION.AFFILIATE_CASH_INFO(:p_start_date_in, :p_end_date_in, :p_list_aff_info)');
			$stmt->bindParam(":p_start_date_in",$start_date);
			$stmt->bindParam(":p_end_date_in",$end_date);
			$cursor = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(":p_list_aff_info",$cursor);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursor->execute();
			$cursor->free();
			$arr_neto = CursorToArrayHelper::cursorToArray($cursor);
			return $arr_neto;
		}
		catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = "ATS_INTEGRATION.AFFILIATE_CASH_INFO(:p_start_date_in = {$start_date}, :p_end_date_in = {$end_date}, :p_list_aff_info) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return null;
		}
	}

    /**
     * @param $string
     * @return string
     */
	public function encrypt($string){
		$enc_key = "1d109fdebae5e3a0cd8b547e763051cb";
		$enc_key = pack('H*', $enc_key);
		$size = mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CBC);
		$iv = mcrypt_create_iv($size, MCRYPT_DEV_URANDOM);
		$cipherstring = mcrypt_encrypt(MCRYPT_CAST_256, $enc_key, $string, MCRYPT_MODE_CBC, $iv);
		$cipherstring = $iv . '__$#Av_' . $cipherstring;
		$cipherstring_base64 = base64_encode($cipherstring);
		return $cipherstring_base64;
	}

    /**
     * @param $username
     * @param $password
     * @return mixed
     * @throws Zend_Exception
     */
	public function checkAffiliateExists($username, $password){
        /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_ats_auth');
		$config = Zend_Registry::get("config");
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL ATS_INTEGRATION.CHECK_AFF_EXISTS(:p_aff_username, :p_aff_pass, :p_exists)');
			$stmt->bindParam(":p_aff_username",$username);
			$stmt->bindParam(":p_aff_pass",$password);
			$p_exists = "";
			$stmt->bindParam(':p_exists', $p_exists, SQLT_CHR, 255);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			return $p_exists;
		}
		catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = "ATS_INTEGRATION.CHECK_AFF_EXISTS(:p_aff_username = {$username}, :p_aff_pass = {$password}, :p_exists) <br /> Exception Error: <br />" . CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			return 0;
		}
	}
}