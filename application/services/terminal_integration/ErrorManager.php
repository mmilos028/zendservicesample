<?php
/**
 * 
 * Class to resolve errors in application ...
 *
 */
class ErrorManager {
	/**
	 * 
	 * Every Error is sent through email to list of emails in configuration file
	 * @param mixed $config
	 * @param string $exception_text
	 */
	public function sendMail($config, $exception_text){
		try{
			if($config->sendErrorsOnMail == "true"){
				$from = $config->mailSendErrorFrom;
				$recipients = $config->mailSendErrorTo;
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
		}catch(Zend_Exception $ex){}
	}
}