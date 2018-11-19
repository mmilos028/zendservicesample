<?php
class ApcoErrorHelper {
	/**
	 *
	 * Every Error is sent through email to list of emails in configuration file
	 * @param string $exception_text
	 * @param string $recipient
	 */
	public static function sendMail($exception_text, $recipient = null){
		try{
			$config = Zend_Registry::get('config');
			if($config->sendErrorsOnMail == "true" && strlen($exception_text)>0){
				$from = $config->mailSendErrorFrom;
				if(!isset($recipients))
					$recipients = $config->mailSendErrorTo;
				else{
					$recipients = $config->mailSendErrorTo;
					//here to read recipients group and load that one from configuration
				}
				$smtpServer = $config->smtpServer;
				$tr = null;
				$tr = new Zend_Mail_Transport_Smtp($smtpServer);
				$mail = null;
				$mail = new Zend_Mail('UTF-8');
				$mail->clearFrom();
				$recipients_arr = explode(",", $recipients);
				$mail->addTo($recipients_arr, $config->mailToTitle);
				$exception_text = "<html><body>" . $exception_text . "</body></html>";
				$mail->setBodyHtml($exception_text);
				$mail->setFrom($from, $config->mailFromTitle);
				$mail->setSubject($config->mailSubjectTitle);
				Zend_Mail::setDefaultTransport($tr);
				$mail->send();
			}
		}catch(Zend_Exception $ex){
		}
	}

	/**
	 *
	 * Write error to log for merchant (APCO) integration
	 * @param string $message
	 */
	public static function apcoIntegrationErrorLog($message){
		try{
			$config = Zend_Registry::get('config');
			if($config->writeApcoIntegrationErrorLogFile == "true"){
				$error_logger = Zend_Registry::get("apco_integration_error_logger");
				$error_logger->log($message, Zend_Log::ERR);
			}
		}catch(Zend_Exception $ex){
		}
	}

	/**
	 *
	 * Write error to log and send mail for merchant (APCO) payments ...
	 * @param string $mail_message
	 * @param string $log_message
	 * @param string $recipients
	 */
	public static function apcoIntegrationError($mail_message, $log_message, $recipients = null){
		try{
			if(strlen($mail_message) > 0){
                self::sendMail($mail_message, $recipients);
			}
			if(strlen($log_message) > 0){
                self::apcoIntegrationErrorLog($log_message);
			}
		}catch(Zend_Exception $ex){
		}
	}

	/**
	 *
	 * Write access to log for merchant (APCO) integration
	 * @param string $message
	 */
	public static function apcoIntegrationAccessLog($message){
		try{
			$config = Zend_Registry::get('config');
			if($config->writeApcoIntegrationAccessLogFile == "true"){
				$access_logger = Zend_Registry::get("apco_integration_access_logger");
				$access_logger->log($message, Zend_Log::INFO);
			}
		}catch(Zend_Exception $ex){
		}
	}

	/**
	 *
	 * Write log and send mail for merchant access (APCO) to web service
	 * @param string $mail_message
	 * @param string $log_message
	 * @param string $recipients
	 */
	public static function apcoIntegrationAccess($mail_message, $log_message, $recipients = null){
		try{
			if(strlen($mail_message) >0){
				self::sendMail($mail_message, $recipients);
			}
			if(strlen($log_message) > 0){
				self::apcoIntegrationAccessLog($log_message);
			}
		}catch(Zend_Exception $ex){
		}
	}

    /**
	 *
	 * Write declined transactions to log for merchant (APCO) integration
	 * @param string $message
	 */
	public static function apcoIntegrationDeclinedTransactionsLog($message){
		try{
			$config = Zend_Registry::get('config');
			if($config->writeApcoIntegrationDeclinedTransactionsLogFile == "true"){
				$access_logger = Zend_Registry::get("apco_integration_declined_transactions_logger");
				$access_logger->log($message, Zend_Log::INFO);
			}
		}catch(Zend_Exception $ex){
		}
	}

	/**
	 *
	 * Write log and send mail for merchant declined transactions (APCO) to web service
	 * @param string $mail_message
	 * @param string $log_message
	 * @param string $recipients
	 */
	public static function apcoIntegrationDeclinedTransactions($mail_message, $log_message, $recipients = null){
		try{
			if(strlen($mail_message) >0){
				self::sendMail($mail_message, $recipients);
			}
			if(strlen($log_message) > 0){
				self::apcoIntegrationDeclinedTransactionsLog($log_message);
			}
		}catch(Zend_Exception $ex){
		}
	}
}
