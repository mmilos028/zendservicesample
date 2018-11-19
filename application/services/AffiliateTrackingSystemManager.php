<?php
class AffiliateTrackingSystemManager
{
	/**
	* Returns list of affiliates: ID, name, parent affiliate
	* @param string $username
	* @param string $password
	* @param string $start_date
	* @param string $end_date
    * @param string $option
	* @return mixed
	*/
	public function getAffiliates($username, $password, $start_date, $end_date, $option='ALL'){
		$salt_user = md5('s@d_mu_nije_nista');
		$salt_pass = md5('ubio_sI_ga_Ko_zec@');
		$user = 'ats' . $salt_user . 'service';
		$pass = 'ats' . $salt_pass . 'protection';
        try {
            if ($user == $username && $pass == $password) {
                require_once MODELS_DIR . DS . "AffiliateTrackingSystemManagerModel.php";
                $model = new AffiliateTrackingSystemManagerModel();
                $result = $model->getAffiliates($start_date, $end_date, $option);
                return $result;
            } else {
                return "Autorizacija nije uspela";
            }
        }catch(Zend_Exception $ex){
            return "Autorizacija nije uspela " . $ex->getMessage();
        }
	}	
	/**
	* Returns affiliate exists
	*
	* @param string $username
	* @param string $password
	* @return string
	*/
	public function checkAffiliateExists($username,$password){
		/*		
		$enc_key = "1d109fdebae5e3a0cd8b547e763051cb";
		$enc_key = pack('H*', $enc_key);
		$cipherstring = base64_decode($username);
		$cipherarray = explode('__$#Av_', $cipherstring);
		$iv_dec = $cipherarray[0];
		$cipherstring = $cipherarray[1];
		$username = trim(mcrypt_decrypt(MCRYPT_CAST_256, $enc_key, $cipherstring, MCRYPT_MODE_CBC, $iv_dec));
		$cipherstring = base64_decode($password);
		$cipherarray = explode('__$#Av_', $cipherstring);
		$iv_dec = $cipherarray[0];
		$cipherstring = $cipherarray[1];
		$password = trim(mcrypt_decrypt(MCRYPT_CAST_256, $enc_key, $cipherstring, MCRYPT_MODE_CBC, $iv_dec)); */
		$password = md5(md5($password));
		require_once MODELS_DIR . DS . "AffiliateTrackingSystemManagerModel.php";
		$model = new AffiliateTrackingSystemManagerModel();
		$result = $model->checkAffiliateExists($username,$password);
		return $result;
	}
}