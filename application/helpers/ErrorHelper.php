<?php
class ErrorHelper {
	/**
	 *
	 * Every Error is sent through email to list of emails in configuration file
	 * @param string $exception_text
	 * @param string $recipients
	 */
	public function sendMail($exception_text, $recipient = null){
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
	 * Write error to log for GGL (LDC) integration
	 * @param string $message
	 */
	public function ldcIntegrationErrorLog($message){
		try{
			$config = Zend_Registry::get('config');
			if($config->writeLdcIntegrationErrorLogFile == "true"){
				$error_logger = Zend_Registry::get("ldc_integration_error_logger");
				$error_logger->log($message, Zend_Log::ERR);
			}
		}catch(Zend_Exception $ex){
		}
	}

	/**
	 *
	 * Wrap method to write to log file and send mail for LDC integration
	 * @param string $mail_message
	 * @param string $log_message
	 * @param string $recipients
	 */
	public function ldcIntegrationError($mail_message, $log_message, $recipients = null){
		try{
		    if(strlen($log_message) > 0){
				$this->ldcIntegrationErrorLog($log_message);
			}
			if(strlen($mail_message) > 0){
				$this->sendMail($mail_message, $recipients);
			}
		}catch(Zend_Exception $ex){
		}
	}

	/**
	 *
	 * Write access log to log for GGL (LDC) integration
	 * @param string $message
	 */
	public function ldcIntegrationAccessLog($message){
		try{
			$config = Zend_Registry::get('config');
			if($config->writeLdcIntegrationAccessLogFile == "true"){
				$access_logger = Zend_Registry::get("ldc_integration_access_logger");
				$access_logger->log($message, Zend_Log::INFO);
			}
		}catch(Zend_Exception $ex){
		}
	}

	/**
	 *
	 * Send mail and write to log file for GGL (LDC) integration access
	 * @param string $mail_message
	 * @param string $log_message
	 * @param string $recipients
	 */
	public function ldcIntegrationAccess($mail_message, $log_message, $recipients = null){
		try{
		    if(strlen($log_message) > 0){
				$this->ldcIntegrationAccessLog($log_message);
			}
			if(strlen($mail_message) > 0){
				$this->sendMail($mail_message, $recipients);
			}
		}catch(Zend_Exception $ex){
		}
	}

	/**
	 *
	 * Write error to log for merchant (APCO) integration
	 * @param string $message
	 */
	public function merchantErrorLog($message){
		try{
			$config = Zend_Registry::get('config');
			if($config->writeMerchantErrorLogFile == "true"){
				$error_logger = Zend_Registry::get("merchant_error_logger");
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
	public function merchantError($mail_message, $log_message, $recipients = null){
		try{
		    if(strlen($log_message) > 0){
				$this->merchantErrorLog($log_message);
			}
			if(strlen($mail_message) > 0){
				$this->sendMail($mail_message, $recipients);
			}
		}catch(Zend_Exception $ex){
		}
	}

	/**
	 *
	 * Write access to log for merchant (APCO) integration
	 * @param string $message
	 */
	public function merchantAccessLog($message){
		try{
			$config = Zend_Registry::get('config');
			if($config->writeMerchantAccessLogFile == "true"){
				$access_logger = Zend_Registry::get("merchant_access_logger");
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
	public function merchantAccess($mail_message, $log_message, $recipients = null){
		try{
		    if(strlen($log_message) > 0){
				$this->merchantAccessLog($log_message);
			}
			if(strlen($mail_message) >0){
				$this->sendMail($mail_message, $recipients);
			}
		}catch(Zend_Exception $ex){
		}
	}

	/**
	 *
	 * Write error to log for service (onlinecasinoservice) for game client use
	 * @param string $message
	 */
	public function serviceErrorLog($message){
		try{
			$config = Zend_Registry::get('config');
			if($config->writeErrorLogFile == "true"){
				$access_logger = Zend_Registry::get("error_logger");
				$access_logger->log($message, Zend_Log::ERR);
			}
		}catch(Zend_Exception $ex){
		}
	}

	/**
	 *
	 * Write error to log file and send mail to recipients list here ...
	 * @param string $mail_message
	 * @param string $log_message
	 * @param string $recipients
	 */
	public function serviceError($mail_message, $log_message, $recipients = null){
		try{
		    if(strlen($log_message) > 0){
				$this->serviceErrorLog($log_message);
			}
			if(strlen($mail_message) > 0){
				$this->sendMail($mail_message, $recipients);
			}
		}catch(Zend_Exception $ex){
		}
	}

	/**
	 *
	 * Write access log to log file for service (onlinecasinoservice) for game client use
	 * @param string $message
	 */
	public function serviceAccessLog($message){
		try{
			$config = Zend_Registry::get('config');
			if($config->writeAccessLogFile == "true"){
				$access_logger = Zend_Registry::get("access_logger");
				$access_logger->log($message, Zend_Log::INFO);
			}
		}catch(Zend_Exception $ex){
		}
	}

	/**
	 *
	 * Write access to log file and send mail to recipients list
	 * @param string $mail_message
	 * @param string $log_message
	 * @param string $recipients
	 */
	public function serviceAccess($mail_message, $log_message, $recipients = null){
		try{
		    if(strlen($log_message) > 0){
				$this->serviceAccessLog($log_message);
			}
			if(strlen($mail_message) > 0){
				$this->sendMail($mail_message, $recipients);
			}
		}catch(Zend_Exception $ex){
		}
	}

	/**
	 *
	 * Write web site errors to log for web site use
	 * @param string $message
	 */
	public function siteErrorLog($message){
		try{
			$config = Zend_Registry::get('config');
			if($config->writeSiteErrorLogFile == "true"){
				$access_logger = Zend_Registry::get("site_error_logger");
				$access_logger->log($message, Zend_Log::ERR);
			}
		}catch(Zend_Exception $ex){
		}
	}

	/**
	 *
	 * Write error to log file and send mail to recipients list for web site service ...
	 * @param string $mail_message
	 * @param string $log_message
	 * @param string $recipients
	 */
	public function siteError($mail_message, $log_message, $recipients = null){
		try{
		    if(strlen($log_message) > 0){
				$this->serviceErrorLog($log_message);
			}
			if(strlen($mail_message) > 0){
				$this->sendMail($mail_message, $recipients);
			}
		}catch(Zend_Exception $ex){
		}
	}

	/**
	 *
	 * Write access log to log file for web site usage
	 * @param string $message
	 */
	public function siteAccessLog($message){
		try{
			$config = Zend_Registry::get('config');
			if($config->writeSiteAccessLogFile == "true"){
				$access_logger = Zend_Registry::get("site_access_logger");
				$access_logger->log($message, Zend_Log::INFO);
			}
		}catch(Zend_Exception $ex){
		}
	}

	/**
	 *
	 * Write access to log file and send mail to recipients list for web site usage
	 * @param string $mail_message
	 * @param string $log_message
	 * @param string $recipients
	 */
	public function siteAccess($mail_message, $log_message, $recipients = null){
		try{
		    if(strlen($mail_message) > 0){
				$this->serviceAccessLog($log_message);
			}
			if(strlen($mail_message) > 0){
				$this->sendMail($mail_message, $recipients);
			}
		}catch(Zend_Exception $ex){
		}
	}

	/**
	*
	* Write cbcx integration errors to log for cbcx
	* @param string $message
	*/
	public function cbcxErrorLog($message){
		try{
			$config = Zend_Registry::get('config');
			if($config->writeCbcxErrorLogFile == "true"){
				$cbcx_logger = Zend_Registry::get("cbcx_error_logger");
				$cbcx_logger->log($message, Zend_Log::ERR);
			}
		}catch(Zend_Exception $ex){
		}
	}

	/**
	*
	* Write error to log file and send mail to recipients list for cbcx casino service ...
	* @param string $mail_message
	* @param string $log_message
	* @param string $recipients
	*/
	public function cbcxError($mail_message, $log_message, $recipients = null){
		try{
		    if(strlen($log_message) > 0){
				$this->cbcxErrorLog($log_message);
			}
			if(strlen($mail_message) > 0){
				$this->sendMail($mail_message, $recipients);
			}
		}catch(Zend_Exception $ex){
		}
	}

	/**
	 *
	 * Write access log to log file for cbcx integration usage
	 * @param string $message
	 */
	public function cbcxAccessLog($message){
		try{
			$config = Zend_Registry::get('config');
			if($config->writeCbcxAccessLogFile == "true"){
				$cbcx_logger = Zend_Registry::get("cbcx_access_logger");
				$cbcx_logger->log($message, Zend_Log::INFO);
			}
		}catch(Zend_Exception $ex){
		}
	}

	/**
	 *
	 * Write access to log file and send mail to recipients list for cbcx integration usage
	 * @param string $mail_message
	 * @param string $log_message
	 * @param string $recipients
	 */
	public function cbcxAccess($mail_message, $log_message, $recipients = null){
		try{
		    if(strlen($mail_message) > 0){
				$this->cbcxAccessLog($log_message);
			}
			if(strlen($mail_message) > 0){
				$this->sendMail($mail_message, $recipients);
			}
		}catch(Zend_Exception $ex){
		}
	}


    /**
	*	EXTERNAL INTEGRATION INTERFACE
	*/
	/**
	 *
	 * Write error to log for external integration (integration app)
	 * @param string $message
	 */
	public function externalIntegrationErrorLog($message){
		try{
			$config = Zend_Registry::get('config');
			if($config->writeExternalIntegrationAccessLogFile == "true"){
				$external_integration_access_logger = Zend_Registry::get("external_integration_error_logger");
				$external_integration_access_logger->log($message, Zend_Log::ERR);
			}
		}catch(Zend_Exception $ex){
		}
	}

	/**
	 *
	 * Write error to log file and send mail to recipients list here ...
	 * @param string $mail_message
	 * @param string $log_message
	 * @param string $recipients
	 */
	public function externalIntegrationError($mail_message, $log_message, $recipients = null){
		try{
		    if(strlen($log_message) > 0){
				$this->externalIntegrationErrorLog($log_message);
			}
			if(strlen($mail_message) > 0){
				$this->sendMail($mail_message, $recipients);
			}
		}catch(Zend_Exception $ex){
		}
	}

	/**
	 *
	 * Write access log to log file for external integration (integration app)
	 * @param string $message
	 */
	public function externalIntegrationAccessLog($message){
		try{
			$config = Zend_Registry::get('config');
			if($config->writeExternalIntegrationAccessLogFile == "true"){
				$external_integration_access_logger = Zend_Registry::get("external_integration_access_logger");
				$external_integration_access_logger->log($message, Zend_Log::INFO);
			}
		}catch(Zend_Exception $ex){
		}
	}

	/**
	 *
	 * Write access to log file and send mail to recipients list
	 * @param string $mail_message
	 * @param string $log_message
	 * @param string $recipients
	 */
	public function externalIntegrationAccess($mail_message, $log_message, $recipients = null){
		try{
		    if(strlen($log_message) > 0){
				$this->externalIntegrationAccessLog($log_message);
			}
			if(strlen($mail_message) > 0){
				$this->sendMail($mail_message, $recipients);
			}
		}catch(Zend_Exception $ex){
		}
	}

    /////SPORT BETTING LOGGER ////////////
    /**
     *
     * Write error to log for Sport Betting integration
     * @param string $message
     */
    public function sportBettingIntegrationErrorLog($message){
        try{
            $config = Zend_Registry::get('config');
            if($config->writeSportBettingIntegrationErrorLogFile == "true"){
                $error_logger = Zend_Registry::get("sport_betting_integration_error_logger");
                $error_logger->log($message, Zend_Log::ERR);
            }
        }catch(Zend_Exception $ex){
        }
    }

    /**
     *
     * Wrap method to write to log file and send mail for Sport Betting integration
     * @param string $mail_message
     * @param string $log_message
     * @param string $recipients
     */
    public function sportBettingIntegrationError($mail_message, $log_message, $recipients = null){
        try{
            if(strlen($log_message) > 0){
                $this->sportBettingIntegrationErrorLog($log_message);
            }
            if(strlen($mail_message) > 0){
                $this->sendMail($mail_message, $recipients);
            }
        }catch(Zend_Exception $ex){
        }
    }

    /**
     *
     * Write access log to log for Sport Betting integration
     * @param string $message
     */
    public function sportBettingIntegrationAccessLog($message){
        try{
            $config = Zend_Registry::get('config');
            if($config->writeSportBettingIntegrationAccessLogFile == "true"){
                $access_logger = Zend_Registry::get("sport_betting_integration_access_logger");
                $access_logger->log($message, Zend_Log::INFO);
            }
        }catch(Zend_Exception $ex){
        }
    }

    /**
     *
     * Send mail and write to log file for Sport Betting integration access
     * @param string $mail_message
     * @param string $log_message
     * @param string $recipients
     */
    public function sportBettingIntegrationAccess($mail_message, $log_message, $recipients = null){
        try{
            if(strlen($log_message) > 0){
                $this->sportBettingIntegrationAccessLog($log_message);
            }
            if(strlen($mail_message) > 0){
                $this->sendMail($mail_message, $recipients);
            }
        }catch(Zend_Exception $ex){
        }
    }

    ///VIVO GAMING INTEGRATION

    ///PAYSECURE

    /**
	 *
	 * Write error to log for vivo gaming integration
	 * @param string $message
	 */
	public function vivoGamingIntegrationErrorLog($message){
		try{
			$config = Zend_Registry::get('config');
			if($config->writeVivoGamingIntegrationErrorLogFile == "true"){
				$error_logger = Zend_Registry::get("vivo_gaming_integration_error_logger");
				$error_logger->log($message, Zend_Log::ERR);
			}
		}catch(Zend_Exception $ex){
		}
	}

	/**
	 *
	 * Wrap method to write to log file and send mail for Vivo Gaming integration
	 * @param string $mail_message
	 * @param string $log_message
	 * @param string $recipients
	 */
	public function vivoGamingIntegrationError($mail_message, $log_message, $recipients = null){
		try{
		    if(strlen($log_message) > 0){
				$this->vivoGamingIntegrationErrorLog($log_message);
			}
			if(strlen($mail_message) > 0){
				$this->sendMail($mail_message, $recipients);
			}
		}catch(Zend_Exception $ex){
		}
	}

	/**
	 *
	 * Write access log to log for Vivo Gaming integration
	 * @param string $message
	 */
	public function vivoGamingIntegrationAccessLog($message){
		try{
			$config = Zend_Registry::get('config');
			if($config->writeVivoGamingIntegrationAccessLogFile == "true"){
				$access_logger = Zend_Registry::get("vivo_gaming_integration_access_logger");
				$access_logger->log($message, Zend_Log::INFO);
			}
		}catch(Zend_Exception $ex){
		}
	}

	/**
	 *
	 * Send mail and write to log file for Vivo Gaming integration access
	 * @param string $mail_message
	 * @param string $log_message
	 * @param string $recipients
	 */
	public function vivoGamingIntegrationAccess($mail_message, $log_message, $recipients = null){
		try{
		    if(strlen($log_message) > 0){
				$this->vivoGamingIntegrationAccessLog($log_message);
			}
			if(strlen($mail_message) > 0){
				$this->sendMail($mail_message, $recipients);
			}
		}catch(Zend_Exception $ex){
		}
	}
}
