<?php
ini_set('error_reporting', E_ALL);
ini_set('display_error', 1);
ini_set('display_startup_errors', 1);

require_once HELPERS_DIR . DS . 'WebSiteEmailHelper.php';
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
class MailTestController extends Zend_Controller_Action
{
    //constants - XLNTCASINO
	/*
    private $recipients_arr = "activestudio.milos.m@gmail.com, milos.m@activestudio.rs";
    private $player_smtp_server = "192.168.5.27";
    private $player_mail_address = "activestudio.milos.m@gmail.com, milos.m@activestudio.rs";
    private $mail_address_from = "email@xlntcasino.com";
	private $player_name = "XLNT test1";
    private $player_username = "XLNTtest1";
	private $casino_name = "XLNTcasino.com";
    private $payment_method = "VISA";
    private $player_transaction_limit = "500.00";
	*/

	//constants - MULTIWIN24.com
	/*
	private $recipients_arr = "activestudio.milos.m@gmail.com, milos.m@activestudio.rs";
    private $player_smtp_server = "192.168.5.27";
    private $player_mail_address = "activestudio.milos.m@gmail.com";
    private $mail_address_from = "noreply@multiwin24.com";
	private $player_name = "DevPlay Milos";
    private $player_username = "DevPlayMilos";
    private $casino_name = "MultiWin24.com";
    private $payment_method = "VISA";
    private $player_transaction_limit = "500.00";
*/

//constants CASINO 400

	private $recipients_arr = "activestudio.milos.m@gmail.com";
	private $player_smtp_server = "46.240.136.21";
	private $player_mail_address = "activestudio.milos.m@gmail.com";
	//private $player_mail_address = "milos.m@activestudio.rs";
	private $mail_address_from = "service@activestudio.rs";
	private $player_name = "Casino400 milosm17";
	private $player_username = "milosm17";
	private $casino_name = "Casino400.com";
	private $payment_method = "VISA";
	private $player_transaction_limit = "500.00";



    private $fee_amount = "5.00";
    private $currency = "EUR";
    private $transaction_amount = "250.00";
    private $transaction_fee = "5.00";
    private $transaction_id = "123456789";
    private $next_fee_date = "01-Feb-2017";
    private $reactivate_before_fee_date = "20-Jan-2017";
    private $inactive_time = "180";

    //private $language_settings = 'sv_SE';
    private $language_settings = 'en_GB';
    //private $language_settings = 'cs_CZ';
    //private $language_settings = 'de_DE';
    //private $language_settings = 'rs_RS';

    private $sleep_seconds = 1;

    public function init(){
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout->disableLayout();
	}

    public function indexAction()
    {
		//$siteLink = "https://www.xlntcasino.com";
		$siteLink = "http://www.casino400.com/multiwin24";
		//$siteLink = "#";
        //$siteLink = "https://www.multiwin24.com";
        $siteImagesLocation = "{$siteLink}/images/email/";
        $playerActivationLink = "{$siteLink}/activation.php";
        $supportLink = "{$siteLink}/support.php";
        $termsLink = "{$siteLink}/terms.php";
        $contactLink = "{$siteLink}contact.php";
        $forgotPasswordLink = "{$siteLink}/reset_password.php";
        $playerUnlockLink = "{$siteLink}/unlock_account.php";
        $privacyPolicyLink = "{$siteLink}/privacy.php";

        $helperWebSiteEmail = new WebSiteEmailHelper();


        //1. mail - New player registration E-mail - after successful registration - with confirmation link
        $result = $helperWebSiteEmail->getActivationEmailToPlayerContent($this->player_name,
	      $this->player_username, $siteImagesLocation, $this->casino_name, $siteLink,
	      $playerActivationLink, $supportLink, $termsLink, $contactLink, $this->language_settings);
        $time_sending = date('d-M-Y H:i:s', time());
        $mail_title = "1 - " . $result['mail_title'] . " " . $time_sending;
        $mail_message = $result['mail_message'];
        $this->sendMailToPlayer($this->mail_address_from, $this->player_mail_address, $mail_title, $mail_message, $mail_title, $mail_title);
        sleep($this->sleep_seconds);
		echo "<br /><br />{$mail_title}";

		//1. mail - New player registration E-mail - after successful registration - with confirmation link
        $result = $helperWebSiteEmail->getActivationEmailToPlayerContentNoMailBody($this->player_name,
	      $this->player_username, $siteImagesLocation, $this->casino_name, $siteLink,
	      $playerActivationLink, $supportLink, $termsLink, $contactLink, $this->language_settings);
        $time_sending = date('d-M-Y H:i:s', time());
        $mail_title = "1 - " . $result['mail_title'] . " " . $time_sending;
        $mail_message = $result['mail_message'];
        $this->sendMailToPlayer($this->mail_address_from, $this->player_mail_address, $mail_title, $mail_message, $mail_title, $mail_title);
        sleep($this->sleep_seconds);
		echo "<br /><br />{$mail_title}";

        //2. mail - Account activation success - following on email verification - informative  mail for new user
        $result = $helperWebSiteEmail->getActivatedPlayerEmailToPlayerContent($this->player_username, $this->player_mail_address,
	      $siteImagesLocation, $this->casino_name, $siteLink, $contactLink, $supportLink, $termsLink, $this->language_settings);
        $time_sending = date('d-M-Y H:i:s', time());
        $mail_title = "2 - " . $result['mail_title'] . " " . $time_sending;
        $mail_message = $result['mail_message'];
        $this->sendMailToPlayer($this->mail_address_from, $this->player_mail_address, $mail_title, $mail_message, $mail_title, $mail_title);
        sleep($this->sleep_seconds);
		echo "<br /><br />{$mail_title}";

        //3. mail - Reminder inactivity  350 days - announcement of "administrative fee" - if no login within 10 days
        $result = $helperWebSiteEmail->getBeforeChargeFeeFromPlayerContent($this->player_username, $siteImagesLocation, $this->casino_name, $siteLink, $supportLink, $termsLink, $contactLink,
	      $this->fee_amount, $this->currency, $this->next_fee_date, $this->reactivate_before_fee_date, $this->inactive_time, $this->language_settings);
        $time_sending = date('d-M-Y H:i:s', time());
        $mail_title = "3 - " . $result['mail_title'] . " " . $time_sending;
        $mail_message = $result['mail_message'];
        $this->sendMailToPlayer($this->mail_address_from, $this->player_mail_address, $mail_title, $mail_message, $mail_title, $mail_title);
        sleep($this->sleep_seconds);
		echo "<br /><br />{$mail_title}";

        //4. mail - Information about fee charged after - 360 days of inactivity
        $result = $helperWebSiteEmail->getChargeFeeFromPlayerContent($this->player_username, $siteImagesLocation, $this->casino_name, $siteLink, $supportLink, $termsLink, $contactLink,
	      $this->fee_amount, $this->currency, $this->next_fee_date, $this->reactivate_before_fee_date, $this->inactive_time, $this->language_settings);
        $time_sending = date('d-M-Y H:i:s', time());
        $mail_title = "4 - " . $result['mail_title'] . " " . $time_sending;
        $mail_message = $result['mail_message'];
        $this->sendMailToPlayer($this->mail_address_from, $this->player_mail_address, $mail_title, $mail_message, $mail_title, $mail_title);
        sleep($this->sleep_seconds);
		echo "<br /><br />{$mail_title}";

        //5. mail - Lost password recovery email - link opens new password section
        $result = $helperWebSiteEmail->getPasswordEmailToPlayerContent($this->player_username, $siteImagesLocation, $this->casino_name, $siteLink, $forgotPasswordLink,
	      $contactLink, $supportLink, $termsLink, $this->language_settings);
        $time_sending = date('d-M-Y H:i:s', time());
        $mail_title = "5 - " . $result['mail_title'] . " " . $time_sending;
        $mail_message = $result['mail_message'];
        $this->sendMailToPlayer($this->mail_address_from, $this->player_mail_address, $mail_title, $mail_message, $mail_title, $mail_title);
        sleep($this->sleep_seconds);
		echo "<br /><br />{$mail_title}";

         //6. mail - Lost username recovery email - Email contains username
        $result = $helperWebSiteEmail->getUsernameEmailToPlayerContent($this->player_name, $this->player_username,
	      $siteImagesLocation, $this->casino_name, $siteLink, $contactLink,
	      $supportLink, $termsLink, $this->language_settings);
        $time_sending = date('d-M-Y H:i:s', time());
        $mail_title = "6 - " . $result['mail_title'] . " " . $time_sending;
        $mail_message = $result['mail_message'];
        $this->sendMailToPlayer($this->mail_address_from, $this->player_mail_address, $mail_title, $mail_message, $mail_title, $mail_title);
        sleep($this->sleep_seconds);
		echo "<br /><br />{$mail_title}";

		 //test mail
        $result = $helperWebSiteEmail->getActivationEmailToPlayerContent($this->player_name,
	      $this->player_username, $siteImagesLocation, $this->casino_name, $siteLink,
	      $playerActivationLink, $supportLink, $termsLink, $contactLink, $this->language_settings);
        $time_sending = date('d-M-Y H:i:s', time());
        $mail_title = "This is test mail subject";
        $mail_message = "Test mail subject {$time_sending}";
        $this->sendMailToPlayer($this->mail_address_from, $this->player_mail_address, $mail_title, $mail_message, $mail_title, $mail_title);
        sleep($this->sleep_seconds);
		echo "<br /><br />{$mail_title}";

        //7. mail - Unlock account - after (n) incorrect password inputs - link for unlocking with a click
        $result = $helperWebSiteEmail->getUnlockPlayerEmailToPlayerContent($this->player_username, $siteImagesLocation, $this->casino_name, $siteLink,
	      $playerUnlockLink, $supportLink, $termsLink, $contactLink, $this->language_settings);
        $time_sending = date('d-M-Y H:i:s', time());
        $mail_title = "7 - " . $result['mail_title'] . " " . $time_sending;
        $mail_message = $result['mail_message'];
        $this->sendMailToPlayer($this->mail_address_from, $this->player_mail_address, $mail_title, $mail_message, $mail_title, $mail_title);
        sleep($this->sleep_seconds);
		echo "<br /><br />{$mail_title}";

        //8. mail  - Declined APCO deposit - for whatever reason
        $result = $helperWebSiteEmail->getPurchaseFailedContent($this->transaction_amount, $this->transaction_fee, $this->currency, $this->transaction_id, $this->payment_method, $this->player_username,
	      $siteImagesLocation, $this->casino_name, $siteLink, $contactLink, $supportLink, $termsLink, $this->language_settings);
        $time_sending = date('d-M-Y H:i:s', time());
        $mail_title = "8 - " . $result['mail_title'] . " " . $time_sending;
        $mail_message = $result['mail_message'];
        $this->sendMailToPlayer($this->mail_address_from, $this->player_mail_address, $mail_title, $mail_message, $mail_title, $mail_title);
        sleep($this->sleep_seconds);
		echo "<br /><br />{$mail_title}";

        //9. mail - Payout request  - confirmation with TransactionID - after player ask payout and money is deducted
        $result = $helperWebSiteEmail->getPayoutSuccessContent($this->player_username, $this->transaction_id, $this->currency, $this->transaction_amount, $this->transaction_fee, $this->payment_method,
	      $siteImagesLocation, $this->casino_name, $siteLink, $contactLink, $supportLink, $termsLink, $this->language_settings);
        $time_sending = date('d-M-Y H:i:s', time());
        $mail_title = "9 - " . $result['mail_title'] . " " . $time_sending;
        $mail_message = $result['mail_message'];
        $this->sendMailToPlayer($this->mail_address_from, $this->player_mail_address, $mail_title, $mail_message, $mail_title, $mail_title);
        sleep($this->sleep_seconds);
		echo "<br /><br />{$mail_title}";

        //10. mail - DEPOSIT - KYC request E-mail - automated - if 1st payout - if deposit with CC by new player more than 500 EUR
         $result = $helperWebSiteEmail->getDepositLimitPurchaseFailedContent($this->player_username, $this->transaction_amount, $this->currency, $this->player_transaction_limit,
	       $siteImagesLocation, $this->casino_name, $siteLink, $contactLink, $supportLink, $termsLink, $privacyPolicyLink, $this->language_settings);
         $time_sending = date('d-M-Y H:i:s', time());
         $mail_title = "10 - " . $result['mail_title'] . " " . $time_sending;
         $mail_message = $result['mail_message'];
         $this->sendMailToPlayer($this->mail_address_from, $this->player_mail_address, $mail_title, $mail_message, $mail_title, $mail_title);
        sleep($this->sleep_seconds);
		echo "<br /><br />{$mail_title}";

         //10. mail - PAYOUT - KYC request E-mail - automated - if 1st payout - if deposit with CC by new player more than 500 EUR
         $result = $helperWebSiteEmail->getPayoutLimitPayoutFailedContent($this->player_username, $this->transaction_amount, $this->currency, $this->player_transaction_limit,
	       $siteImagesLocation, $this->casino_name, $siteLink, $contactLink, $supportLink, $termsLink, $privacyPolicyLink, $this->language_settings);
         $time_sending = date('d-M-Y H:i:s', time());
         $mail_title = "10 - " . $result['mail_title'] . " " . $time_sending;
         $mail_message = $result['mail_message'];
         $this->sendMailToPlayer($this->mail_address_from, $this->player_mail_address, $mail_title, $mail_message, $mail_title, $mail_title);
        sleep($this->sleep_seconds);
		echo "<br /><br />{$mail_title}";

        //11. mail - Payout confirmation - support approved (no cancel withdraw button) - confirmed in APCO BO -APCO confirmed to our BO processing of payout
        $result = $helperWebSiteEmail->getPayoutConfirmationFromApcoBackofficeContent($this->player_username, $this->transaction_id, $this->transaction_amount, $this->transaction_fee, $this->currency,
	      $siteImagesLocation, $this->casino_name, $siteLink, $contactLink, $supportLink, $termsLink, $this->language_settings);
        $time_sending = date('d-M-Y H:i:s', time());
        $mail_title = "11 - " . $result['mail_title'] . " " . $time_sending;
        $mail_message = $result['mail_message'];
        $this->sendMailToPlayer($this->mail_address_from, $this->player_mail_address, $mail_title, $mail_message, $mail_title, $mail_title);
        sleep($this->sleep_seconds);
		echo "<br /><br />{$mail_title}";

        //12. mail - Payout decline - support approved - confirmed in APCO BO - APCO-declined to our BO processing of payout- contact support
        $result = $helperWebSiteEmail->getPayoutDeclinedFromApcoBackofficeContent($this->player_username, $this->transaction_id, $this->transaction_amount, $this->transaction_fee, $this->currency,
	      $siteImagesLocation, $this->casino_name, $siteLink, $contactLink, $supportLink, $termsLink, $this->language_settings);
        $time_sending = date('d-M-Y H:i:s', time());
        $mail_title = "12 - " . $result['mail_title'] . " " . $time_sending;
        $mail_message = $result['mail_message'];
        $this->sendMailToPlayer($this->mail_address_from, $this->player_mail_address, $mail_title, $mail_message, $mail_title, $mail_title);
        sleep($this->sleep_seconds);
		echo "<br /><br />{$mail_title}";

        //13. mail - Payout - declined by our support - fee return & payout return to credits - contact support
        $result = $helperWebSiteEmail->getPayoutRequestCanceledBySupportContent($this->player_username, $this->transaction_id, $this->transaction_amount, $this->currency,
	      $siteImagesLocation, $this->casino_name, $siteLink, $contactLink, $supportLink, $termsLink, $this->language_settings);
        $time_sending = date('d-M-Y H:i:s', time());
        $mail_title = "13 - " . $result['mail_title'] . " " . $time_sending;
        $mail_message = $result['mail_message'];
        $this->sendMailToPlayer($this->mail_address_from, $this->player_mail_address, $mail_title, $mail_message, $mail_title, $mail_title);
        sleep($this->sleep_seconds);
		echo "<br /><br />{$mail_title}";

        //14. mail - Pending Payout - cancelled by player
        $result = $helperWebSiteEmail->getPlayerCanceledHisPayoutContent($this->player_username, $this->transaction_id,
	      $siteImagesLocation, $this->casino_name, $siteLink, $contactLink, $supportLink, $termsLink, $this->language_settings);
        $time_sending = date('d-M-Y H:i:s', time());
        $mail_title = "14 - " . $result['mail_title'] . " " . $time_sending;
        $mail_message = $result['mail_message'];
        $this->sendMailToPlayer($this->mail_address_from, $this->player_mail_address, $mail_title, $mail_message, $mail_title, $mail_title);
        sleep($this->sleep_seconds);
		echo "<br /><br />{$mail_title}";

        //15. mail - KYC - renewal needed - automated - if support at payout see KYC older than 180 days & KYC status manually set back to Email-verified
        $result = $helperWebSiteEmail->getPlayerChangesVerificationStatusContent($this->player_username, $this->transaction_id, $this->transaction_amount, $this->currency,
	      $siteImagesLocation, $this->casino_name, $siteLink, $contactLink, $supportLink, $termsLink, $privacyPolicyLink, $this->language_settings);
        $time_sending = date('d-M-Y H:i:s', time());
        $mail_title = "15 - " . $result['mail_title'] . " " . $time_sending;
        $mail_message = $result['mail_message'];
        $this->sendMailToPlayer($this->mail_address_from, $this->player_mail_address, $mail_title, $mail_message, $mail_title, $mail_title);
        sleep($this->sleep_seconds);

        //16. mail - Reminder inactivity 890 days - announcement of "account closing" - if no login within 10 days - request to payout remaining funds
        $result = $helperWebSiteEmail->getPlayerBeforeAccountClosingContent($this->player_username, $siteImagesLocation, $this->casino_name, $siteLink, $supportLink, $termsLink, $contactLink,
	      $this->fee_amount, $this->currency, $this->next_fee_date, $this->reactivate_before_fee_date, $this->inactive_time, $this->language_settings);
        $time_sending = date('d-M-Y H:i:s', time());
        $mail_title = "16 - " . $result['mail_title'] . " " . $time_sending;
        $mail_message = $result['mail_message'];
        $this->sendMailToPlayer($this->mail_address_from, $this->player_mail_address, $mail_title, $mail_message, $mail_title, $mail_title);
        sleep($this->sleep_seconds);
		echo "<br /><br />{$mail_title}";

        //17. mail -  Account closure - after 900 days of inactivity
        $result = $helperWebSiteEmail->getPlayerForAccountClosingContent($this->player_username, $siteImagesLocation, $this->casino_name, $siteLink, $supportLink, $termsLink, $contactLink,
	      $this->fee_amount, $this->currency, $this->next_fee_date, $this->reactivate_before_fee_date, $this->inactive_time, $this->language_settings);
        $time_sending = date('d-M-Y H:i:s', time());
        $mail_title = "17 - " . $result['mail_title'] . " " . $time_sending;
        $mail_message = $result['mail_message'];
        $this->sendMailToPlayer($this->mail_address_from, $this->player_mail_address, $mail_title, $mail_message, $mail_title, $mail_title);
        sleep($this->sleep_seconds);
		echo "<br /><br />{$mail_title}";

        exit("<br /><br />MAILS SENT >> END");

    }

    private function sendMailToPlayer($playerMailSendFrom, $playerMailAddress,
	$playerMailToTitle, $playerMailContent, $playerMailFromTitle,
	$playerMailSubjectTitle){
		try{
            $playerMailFromTitle = "";
            $playerSmtpConfig = array(
                "port"=>25
            );
			$tr = new Zend_Mail_Transport_Smtp($this->player_smtp_server, $playerSmtpConfig);
			$mail = new Zend_Mail('UTF-8');
			$recipients_arr = explode(',', $this->recipients_arr);
			$mail->addTo($recipients_arr, $playerMailToTitle);
			$mail->setBodyHtml($playerMailContent);
			$mail->setFrom($playerMailSendFrom, $playerMailFromTitle);
			$mail->setSubject($playerMailSubjectTitle);
			Zend_Mail::setDefaultTransport($tr);
			$mail->send();
		}catch(Zend_Exception $ex){
			require_once HELPERS_DIR . DS . 'ErrorHelper.php';
			$errorHelper = new ErrorHelper();
			$errorHelper->sendMail(CursorToArrayHelper::getExceptionTraceAsString($ex));
			$errorHelper->siteErrorLog(CursorToArrayHelper::getExceptionTraceAsString($ex));
		}
	}
}
