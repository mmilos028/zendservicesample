<?php
require_once HELPERS_DIR . DS . 'CurrencyListHelper.php';
require_once HELPERS_DIR . DS . 'NumberHelper.php';
//generate content and send mails to players
class WebSiteEmailHelper{

	public static $header_img = "header.jpg";
	public static $footer_img = "footer.jpg";
	public static $bg_img = "bg.jpg";

    public static $show_player_mail_header = true;
    public static $show_player_mail_footer = false;

    public static $show_administrator_mail_header = true;
    public static $show_administrator_mail_footer = false;

	public function getMailBody($mail_language, $mail_message, $site_images_location, $mail_title,
    $background_color = "#FFFFFF", $text_color = "#000000", $small_text_color = "#000000", $footer_background = "#FFFFFF",
	$text_font_size = "15", $small_text_font_size = "8",
	$hyperlink_text_color = "#FFFFFF", $hyperlink_font_size = "15",
	$hyperlink_small_text_color = "#FFFFFF", $hyperlink_small_font_size = "8"){

        switch($mail_language){
            case 'de_DE':
            case 'cs_CZ':
            case 'sv_SE':
            case 'rs_RS':
            case 'en_GB':
            default:
               $header_img = self::$header_img;
        }

        switch($mail_language){
            case 'de_DE':
            case 'cs_CZ':
            case 'sv_SE':
            case 'rs_RS':
            case 'en_GB':
            default:
               $footer_img = self::$footer_img;
        }

        $header_img = $site_images_location . $header_img;
        $footer_img = $site_images_location . $footer_img;
				$cid_header_img = "cid:" . md5($header_img);
				$cid_footer_img = "cid:" . md5($footer_img);
				$background_color = "#FFFFFF";
				$text_color = "#000000";
				$small_text_color = "#000000";
				$footer_background = "#FFFFFF";
				$text_font_size = "15";
				$small_text_font_size = "8";
				$hyperlink_text_color = "#000000";
				$hyperlink_font_size = "15";
				$hyperlink_small_text_color = "#000000";
				$hyperlink_small_font_size = "8";

        $mail_inline_styles = "
            <style type=\"text/css\">
                body{
                    margin: 0;
                    background-color: #FFFFFF;
                    color: {$text_color};
                    text-align: left;
                }
                .emailContent {
                    text-align: left;
                    margin-top: 15px;
                    margin-bottom: 15px;
                    margin-left: 30px;
                    margin-right: 15px;
                    font-weight: normal;
                    display: inline-block;
                    min-height: 300px;
                    color: {$text_color};
                }
                p{
                    font-size: {$text_font_size}px;
                    text-align: left;
                    font-family: Calibri, Arial, Helvetica, sans-serif;
                    font-weight: normal;
                    display: block;
                    color: {$text_color};
                    margin: 0;
                    line-height: 15px;
                }
                p.boldLetters{
                    font-size: {$text_font_size}px;
                    text-align: left;
                    font-family: Calibri, Arial, Helvetica, sans-serif;
                    font-weight: bold;
                    display: block;
                    color: {$text_color};
                    margin: 0;
                    line-height: 15px;
                }
                a{
                    font-size: {$hyperlink_font_size}px;
                    text-align: left;
                    font-family: Calibri, Arial, Helvetica, sans-serif;
                    font-weight: normal;
                    color: {$hyperlink_text_color};
                }
                a.smallLetters{
                    font-size: {$hyperlink_small_font_size}px;
                    text-align: left;
                    font-family: Calibri, Arial, Helvetica, sans-serif;
                    font-weight: normal;
                    color: {$hyperlink_small_text_color};
                    line-height: 15px;
                    display: inline-block;
                }
                a.underlineLetters{
                    font-size: {$hyperlink_font_size}px;
                    text-align: left;
                    font-family: Calibri, Arial, Helvetica, sans-serif;
                    font-weight: normal;
                    color: {$hyperlink_text_color};
                }
                .smallLetters{
                    font-size: {$small_text_font_size}px;
                    text-align: left;
                    font-family: Calibri, Arial, Helvetica, sans-serif;
                    font-weight: normal;
                    padding-top: 0;
                    padding-bottom: 0;
                    margin: 0;
                    color: {$small_text_color};
                    line-height: 8px;
                    display: inline-block;
                }
                .boldLetters{
                    font-size: {$text_font_size}px;
                    text-align: left;
                    font-family: Calibri, Arial, Helvetica, sans-serif;
                    font-weight: bold;
                    display: inline-block;
                    color: {$text_color};
                    line-height: 15px;
                }
                .smallLetters p{
                    font-size: {$small_text_font_size}px;
                    text-align: left;
                    font-family: Calibri, Arial, Helvetica, sans-serif;
                    font-weight: normal;
                    color: {$text_color};
                    margin: 0;
                    display: inline-block;
                }
                .header{
                    background-image: url({$header_img});
                    background-repeat: no-repeat;
                    background-color: {$footer_background};
                    -webkit-background-size: 100% auto;
                    -moz-background-size: 100% auto;
                    -o-background-size: 100% auto;
                    background-size: 100% auto;
                    text-align: left;
                    height: 130px;
                    min-width: 986px;
                    width: 100%;
                }
                .footer{
                    background-image: url({$footer_img});
                    background-repeat: no-repeat;
                    background-color: {$footer_background};
                    -webkit-background-size: cover;
                    -moz-background-size: cover;
                    -o-background-size: cover;
                    background-size: cover;
                    text-align: left;
                    height: 70px;
                    min-width: 986px;
                    width: 100%;
                }
                br{
                    content: \" \";
                    line-height: 0.1;
                    margin: 0;
                    border: 0;
                    height: 0;
                    font-size:2px;
                }
                .break{
                    display: block;
                    margin: 20px 0 20px 0;
                    font-size: 15px;
                    line-height: 10px;
                }
                .table-content{
                    width: 986px;
                    text-align: left;
                    margin-left:auto;
                    margin-right:auto;
                }
                img {
                    border: 0;
                }
                </style>
        ";

        if (self::$show_player_mail_header) {
            $mail_header_body = "
            <tr>
                <td class=\"header\">
                    <img width=\"986\" height=\"131\" src=\"{$header_img}\" alt=\"\" />
                </td>
            </tr>
        ";
        } else {
            $mail_header_body = "";
        }

        if (self::$show_player_mail_footer) {
            $mail_footer_body = "
            <tr>
                <td class=\"footer\">
                    <img width=\"986\" height=\"70\" src=\"{$footer_img}\" alt=\"\" />
                </td>
            </tr>
        ";
        } else {
            $mail_footer_body = "";
        }

		$mail_message =
            "<!DOCTYPE html>
                <html>
                    <head>
                        <meta charset=\"utf-8\" />
                        <title> {$mail_title} </title>
                        {$mail_inline_styles}
                    </head>
                    <body>
                        <table class=\"table-content\">
                            {$mail_header_body}
                            <tr>
                                <td style=\"background: {$background_color}\">
                                    <div class=\"emailContent\">
                                        <br /><br />
                                        {$mail_message}
                                        <br /><br />
                                    </div>
                                </td>
                            </tr>
                            {$mail_footer_body}
                        </table>
                    </body>
                </html>";
		return $mail_message;
	}

	public function getAdministratorMailBody($mail_content, $mail_title, $date_processed){
		$background_color = "#FFFFFF";
		$text_color = "#000000";
		$small_text_color = "#000000";
		$footer_background = "#FFFFFF";
		$text_font_size = "15";
		$small_text_font_size = "8";
		$hyperlink_text_color = "#000000";
		$hyperlink_font_size = "15";
		$hyperlink_small_text_color = "#000000";
		$hyperlink_small_font_size = "8";

        $mail_inline_styles = "
            <style type=\"text/css\">
                body{
                    margin: 0;
                    background-color: #FFFFFF;
                    color: {$text_color};
                    text-align: left;
                }
                .emailContent {
                    text-align: left;
                    margin-top: 15px;
                    margin-bottom: 15px;
                    margin-left: 30px;
                    margin-right: 15px;
                    font-weight: normal;
                    display: inline-block;
                    height: 300px;
                    color: {$text_color};
                }
                p{
                    font-size: {$text_font_size}px;
                    text-align: left;
                    font-family: Calibri, Arial, Helvetica, sans-serif;
                    font-weight: normal;
                    display: block;
                    color: {$text_color};
                    margin: 1px;
                    line-height: 20px;
                }
                a{
                    font-size: {$hyperlink_font_size}px;
                    text-align: left;
                    font-family: Calibri, Arial, Helvetica, sans-serif;
                    font-weight: normal;
                    color: {$hyperlink_text_color};
                }
                a.smallLetters{
                    font-size: {$hyperlink_small_font_size}px;
                    text-align: left;
                    font-family: Calibri, Arial, Helvetica, sans-serif;
                    font-weight: normal;
                    color: {$hyperlink_small_text_color};
                    line-height: 20px;
                    display: inline-block;
                }
                .smallLetters{
                    font-size: {$small_text_font_size}px;
                    text-align: left;
                    font-family: Calibri, Arial, Helvetica, sans-serif;
                    font-weight: normal;
                    padding-top: 0;
                    padding-bottom: 0;
                    margin-bottom: 15px;
                    color: {$small_text_color};
                    line-height: 20px;
                    display: inline-block;
                }
                .smallLetters p{
                    font-size: {$small_text_font_size}px;
                    text-align: left;
                    font-family: Calibri, Arial, Helvetica, sans-serif;
                    font-weight: normal;
                    color: {$text_color};
                    margin: 0;
                    display: inline-block;
                }
                .header{
                    background-repeat: no-repeat;
                    background-color: {$footer_background};
                    -webkit-background-size: cover;
                    -moz-background-size: cover;
                    -o-background-size: cover;
                    background-size: cover;
                    text-align: left;
                    height: 0;
                    width: 986px;
                }
                .footer{
                    background-repeat: no-repeat;
                    background-color: {$footer_background};
                    -webkit-background-size: cover;
                    -moz-background-size: cover;
                    -o-background-size: cover;
                    background-size: cover;
                    text-align: left;
                    height: 0;
                    width: 986px;
                }
                br {
                    line-height: 0;
                    margin: 0;
                    border: 0;
                    height: 5px;
                }
                .table-content{
                    width: 100%;
                    margin-left:auto;
                    margin-right:auto;
                }
                img {
                    border: 0;
                }
            </style>
        ";

        $mail_header_body = "
            <tr>
                <td class=\"header\">
                </td>
            </tr>
        ";

        $mail_footer_body = "
            <tr>
                <td class=\"footer\">
                </td>
            </tr>
        ";

		$mail_message =
            "<!DOCTYPE html>
                <html>
                    <head>
                        <meta charset=\"utf-8\" />
                        <title> {$mail_title} </title>
                        {$mail_inline_styles}
                    </head>
                    <body>
                        <table class=\"table-content\">
                            {$mail_header_body}
                            <tr>
                                <td style=\"background: {$background_color}\">
                                    <div class=\"emailContent\">
                                        <br /><br />
                                        {$mail_content}
                                        <br /><br />
                                    </div>
                                </td>
                            </tr>
                            {$mail_footer_body}
                        </table>
                    </body>
                </html>
			";
		return $mail_message;
	}

    //get mail generated content for activation to player
    //1. New player registration E-mail - after successful registration - with confirmation link
	public static function getActivationEmailToPlayerContentNoMailBody($player_name,
	$player_username, $site_images_location, $casino_name, $site_link,
	$player_activation_link, $support_link, $terms_link, $contact_link, $language_settings = 'en_GB',
	$background_color = "#FFFFFF", $text_color = "#000000", $small_text_color = "#000000", $footer_background = "#FFFFFF",
	$text_font_size = "15", $small_text_font_size = "8",
	$hyperlink_text_color = "#FFFFFF", $hyperlink_font_size = "15",
	$hyperlink_small_text_color = "#FFFFFF", $hyperlink_small_font_size = "8"){
        switch ($language_settings){
            case 'de_DE':
                $mail_title = "Willkommen {$player_username} - Ihre {$casino_name} Casino Registrierung";
				$mail_message =
                    "<p> Liebe(r) <span class=\"boldLetters\">{$player_username}</span>, </p>
                        <p> Wir danken für Ihre Registrierung im <span class=\"boldLetters\">{$casino_name}</span> Casino! Ihr Benutzerkonto wurde erfolgreich erstellt.</p>
                        <p class=\"smallLetters\"> &nbsp; </p>
                        <p> Ihr Benutzername ist: <span class=\"boldLetters\">{$player_username}</span> </p>
                        <p> <span class=\"boldLetters\">Bitte klicken Sie auf den untenstehenden Link, um Ihre E-Mail Adresse zu bestätigen: </span> </p>
                        <p> <a class=\"underlineLetters boldLetters\" href=\"{$player_activation_link}\">{$player_activation_link}</a> </p>
                        <p class=\"smallLetters\"> &nbsp; </p>
                        <p> Die Konto-Aktivierung ist notwendig, damit Sie unsere komplettes Angebot nutzen können. </p>
                        <p> Sollten Sie weitere Fragen haben oder sonstige Unterstützung benötigen, bitten wir Sie, sich an unser <a href=\"{$support_link}\" class=\"underlineLetters\">Kundendienst-Service-Team</a> zu wenden. </p>
                        <p> Unsere <a href=\"{$terms_link}\" class=\"underlineLetters\">\"Allgemeinen Geschäftsbedingungen\"</a> finden Sie auf unserer Webseite</p>
                        <p class=\"smallLetters\"> &nbsp; </p>
                        <p> WIR WÜNSCHEN VIEL GLÜCK UND GUTE UNTERHALTUNG! </p>
                        <p> Mit freundlichen Grüßen, </p>
                        <p> Ihr <span class=\"boldLetters\">{$casino_name}</span> Team</p>";
                break;
            case 'cs_CZ':
                $mail_title = "Vítejte {$player_username} -Vaše  {$casino_name} Kasíno registrace";
				$mail_message =
                    "<p> Vážený/á <span class=\"boldLetters\">{$player_username}</span>, </p>
                        <p> Děkujeme za registraci v <span class=\"boldLetters\">{$casino_name}</span> Váš uživatelský účet byl úspěšně vytvořen.</p>
                        <p class=\"smallLetters\"> &nbsp; </p>
                        <p> Vaše uživatelské jméno je: <span class=\"boldLetters\">{$player_username}</span> </p>
                        <p> <span class=\"boldLetters\">Prosím klikněte na dole zobrazený odkaz pro verifikaci Vaší emailové adresy:</span> </p>
                        <p> <a class=\"underlineLetters boldLetters\" href=\"{$player_activation_link}\">{$player_activation_link}</a> </p>
                        <p class=\"smallLetters\"> &nbsp; </p>
                        <p> Aktivace ú`ctu je nezbytná , aby jste mohli využívat celou naší nabídku. </p>
                        <p> Máte-li jakékoliv další dotazy nebo potřebujete jakoukoliv další pomoc, prosím, kontaktujte naši <a href=\"{$support_link}\" class=\"underlineLetters\">Zákaznickou podporu-služby zákazníkům</a>. </p>
                        <p> Naše <a href=\"{$terms_link}\" class=\"underlineLetters\">\"Všeobecné podmínky\"</a> naleznete na našich webových stránkách</p>
                        <p class=\"smallLetters\"> &nbsp; </p>
                        <p> PŘEJEME HODNĚ ŠTĚSTÍ A PŘÍJEMNOU ZÁBAVU ! </p>
                        <p> S přátelským pozdravem, </p>
                        <p> Váš <span class=\"boldLetters\">{$casino_name}</span> tým. </p>";
                break;
            case 'sv_SE':
                $mail_title = "Välkommen {$player_username} - Din {$casino_name} Casino registrering";
                $mail_message =
                    "<p> Kära <span class=\"boldLetters\">{$player_username}</span>, </p>
                        <p class=\"smallLetters\"> &nbsp; </p>
                        <p> Tack för din registrering på <span class=\"boldLetters\">{$casino_name}</span> Casino! Ditt konto har skapats.</p>
                        <p class=\"smallLetters\"> &nbsp; </p>
                        <p> Ditt användarnamn är: <span class=\"boldLetters\">{$player_username}</span> </p>
                        <p> <span class=\"boldLetters\">Klicka för att länken nedan för att bekräfta din e-post:</span> </p>
                        <p> <a class=\"underlineLetters boldLetters\" href=\"{$player_activation_link}\">{$player_activation_link}</a> </p>
                        <p class=\"smallLetters\"> &nbsp; </p>
                        <p> Aktivering är nödvändigt att använda hela skalan av tjänster. </p>
                        <p> Om det finns några frågor eller om du behöver ytterligare hjälp, tveka inte att kontakta vår kundtjänst. </p>
                        <p> För våra Regler & Villkor titta under \"Regler & Villkor\" på sidan. </p>
                        <p class=\"smallLetters\"> &nbsp; </p>
                        <p> Lycka Till! </p>
                        <p> Vänligen, </p>
                        <p> <span class=\"boldLetters\">{$casino_name}</span> supporten </p>";
                break;
            case 'rs_RS':
                $mail_title = "Dobrodošli {$player_username} - Vaša {$casino_name} kazino registracija";
                $mail_message =
                    "<p> Poštovani/a <span class=\"boldLetters\">{$player_username}</span>, </p>
                        <p> Hvala Vam na Vašoj registraciji kod <span class=\"boldLetters\">{$casino_name}</span> kazina! Vaš nalog je uspešno kreiran.</p>
                        <p class=\"smallLetters\"> &nbsp; </p>
                        <p> Vaše korisničko ime je: <span class=\"boldLetters\">{$player_username}</span> </p>
                        <p> <span class=\"boldLetters\">Molimo Vas da kliknete na link ispod da verfikujete Vašu email adresu:</span> </p>
                        <p> <a class=\"underlineLetters boldLetters\" href=\"{$player_activation_link}\">{$player_activation_link}</a> </p>
                        <p class=\"smallLetters\"> &nbsp; </p>
                        <p> Aktivacija je neophodna kako bi ste uživali u punoj ponudi naših servisa. </p>
                        <p> Ukoliko imate bilo kakva pitanja ili Vam je potrebna dalja pomoć, nemojte se ustručavati da kontaktirate naš <a href=\"{$support_link}\" class=\"underlineLetters\">tim podrške</a>. </p>
                        <p> Za naše Uslove korišćenja molimo Vas da posetite naš sajt i kliknete na link <a href=\"{$terms_link}\" class=\"underlineLetters\">\"Uslovi korišćenja\"</a></p>
                        <p class=\"smallLetters\"> &nbsp; </p>
                        <p> SREĆNO! </p>
                        <p> Iskreno Vaš, </p>
                        <p> <span class=\"boldLetters\">{$casino_name}</span> tim podrške </p>";
                break;
            case 'en_GB':
            default:
                $mail_title = "Welcome {$player_username} - Your {$casino_name} Casino registration";
                $mail_message =
                    "<p> Dear <span class=\"boldLetters\">{$player_username}</span>, </p>
                        <p> Thank you for your registration at <span class=\"boldLetters\">{$casino_name}</span> Casino! Your account has been successfully created.</p>
                        <p class=\"smallLetters\"> &nbsp; </p>
                        <p> Your username is: <span class=\"boldLetters\">{$player_username}</span> </p>
                        <p> <span class=\"boldLetters\">Please click to link below to validate your email:</span> </p>
                        <p> <a class=\"underlineLetters boldLetters\" href=\"{$player_activation_link}\">{$player_activation_link}</a> </p>
                        <p class=\"smallLetters\"> &nbsp; </p>
                        <p> Activation is necessary to use the full range of services.</p>
                        <p> If there are any questions or you need further assistance, don't hesitate to contact our <a href=\"{$support_link}\" class=\"underlineLetters\">customer support team</a>. </p>
                        <p> For our terms & conditions please visit our web site <a href=\"{$terms_link}\" class=\"underlineLetters\">\"Terms and Conditions\"</a></p>
                        <p class=\"smallLetters\"> &nbsp; </p>
                        <p> GOOD LUCK! </p>
                        <p> Yours sincerly, </p>
                        <p> The <span class=\"boldLetters\">{$casino_name}</span> support team</p>";
        }
		/*$message = self::getMailBody($language_settings, $mail_message, $site_images_location, $mail_title, $background_color, $text_color, $small_text_color, $footer_background,
				$text_font_size, $small_text_font_size,
				$hyperlink_text_color, $hyperlink_font_size,
				$hyperlink_small_text_color, $hyperlink_small_font_size);*/

		return array("mail_title"=>$mail_title, "mail_message"=>$mail_message);
	}

    //get mail generated content for activation to player
    //1. New player registration E-mail - after successful registration - with confirmation link
	public static function getActivationEmailToPlayerContent($player_name,
	$player_username, $site_images_location, $casino_name, $site_link,
	$player_activation_link, $support_link, $terms_link, $contact_link, $language_settings = 'en_GB',
	$background_color = "#FFFFFF", $text_color = "#000000", $small_text_color = "#000000", $footer_background = "#FFFFFF",
	$text_font_size = "15", $small_text_font_size = "8",
	$hyperlink_text_color = "#FFFFFF", $hyperlink_font_size = "15",
	$hyperlink_small_text_color = "#FFFFFF", $hyperlink_small_font_size = "8"){
        switch ($language_settings){
            case 'de_DE':
                $mail_title = "Willkommen {$player_username} - Ihre {$casino_name} Casino Registrierung";
				$mail_message =
                    "<p> Liebe(r) <span class=\"boldLetters\">{$player_username}</span>, </p>
                        <p> Wir danken für Ihre Registrierung im <span class=\"boldLetters\">{$casino_name}</span> Casino! Ihr Benutzerkonto wurde erfolgreich erstellt.</p>
                        <p class=\"smallLetters\"> &nbsp; </p>
                        <p> Ihr Benutzername ist: <span class=\"boldLetters\">{$player_username}</span> </p>
                        <p> <span class=\"boldLetters\">Bitte klicken Sie auf den untenstehenden Link, um Ihre E-Mail Adresse zu bestätigen: </span> </p>
                        <p> <a class=\"underlineLetters boldLetters\" href=\"{$player_activation_link}\">{$player_activation_link}</a> </p>
                        <p class=\"smallLetters\"> &nbsp; </p>
                        <p> Die Konto-Aktivierung ist notwendig, damit Sie unsere komplettes Angebot nutzen können. </p>
                        <p> Sollten Sie weitere Fragen haben oder sonstige Unterstützung benötigen, bitten wir Sie, sich an unser <a href=\"{$support_link}\" class=\"underlineLetters\">Kundendienst-Service-Team</a> zu wenden. </p>
                        <p> Unsere <a href=\"{$terms_link}\" class=\"underlineLetters\">\"Allgemeinen Geschäftsbedingungen\"</a> finden Sie auf unserer Webseite</p>
                        <p class=\"smallLetters\"> &nbsp; </p>
                        <p> WIR WÜNSCHEN VIEL GLÜCK UND GUTE UNTERHALTUNG! </p>
                        <p> Mit freundlichen Grüßen, </p>
                        <p> Ihr <span class=\"boldLetters\">{$casino_name}</span> Team</p>";
                break;
            case 'cs_CZ':
                $mail_title = "Vítejte {$player_username} -Vaše  {$casino_name} Kasíno registrace";
				$mail_message =
                    "<p> Vážený/á <span class=\"boldLetters\">{$player_username}</span>, </p>
                        <p> Děkujeme za registraci v <span class=\"boldLetters\">{$casino_name}</span> Váš uživatelský účet byl úspěšně vytvořen.</p>
                        <p class=\"smallLetters\"> &nbsp; </p>
                        <p> Vaše uživatelské jméno je: <span class=\"boldLetters\">{$player_username}</span> </p>
                        <p> <span class=\"boldLetters\">Prosím klikněte na dole zobrazený odkaz pro verifikaci Vaší emailové adresy:</span> </p>
                        <p> <a class=\"underlineLetters boldLetters\" href=\"{$player_activation_link}\">{$player_activation_link}</a> </p>
                        <p class=\"smallLetters\"> &nbsp; </p>
                        <p> Aktivace ú`ctu je nezbytná , aby jste mohli využívat celou naší nabídku. </p>
                        <p> Máte-li jakékoliv další dotazy nebo potřebujete jakoukoliv další pomoc, prosím, kontaktujte naši <a href=\"{$support_link}\" class=\"underlineLetters\">Zákaznickou podporu-služby zákazníkům</a>. </p>
                        <p> Naše <a href=\"{$terms_link}\" class=\"underlineLetters\">\"Všeobecné podmínky\"</a> naleznete na našich webových stránkách</p>
                        <p class=\"smallLetters\"> &nbsp; </p>
                        <p> PŘEJEME HODNĚ ŠTĚSTÍ A PŘÍJEMNOU ZÁBAVU ! </p>
                        <p> S přátelským pozdravem, </p>
                        <p> Váš <span class=\"boldLetters\">{$casino_name}</span> tým. </p>";
                break;
            case 'sv_SE':
                $mail_title = "Välkommen {$player_username} - Din {$casino_name} Casino registrering";
                $mail_message =
                    "<p> Kära <span class=\"boldLetters\">{$player_username}</span>, </p>
                        <p class=\"smallLetters\"> &nbsp; </p>
                        <p> Tack för din registrering på <span class=\"boldLetters\">{$casino_name}</span> Casino! Ditt konto har skapats.</p>
                        <p class=\"smallLetters\"> &nbsp; </p>
                        <p> Ditt användarnamn är: <span class=\"boldLetters\">{$player_username}</span> </p>
                        <p> <span class=\"boldLetters\">Klicka för att länken nedan för att bekräfta din e-post:</span> </p>
                        <p> <a class=\"underlineLetters boldLetters\" href=\"{$player_activation_link}\">{$player_activation_link}</a> </p>
                        <p class=\"smallLetters\"> &nbsp; </p>
                        <p> Aktivering är nödvändigt att använda hela skalan av tjänster. </p>
                        <p> Om det finns några frågor eller om du behöver ytterligare hjälp, tveka inte att kontakta vår kundtjänst. </p>
                        <p> För våra Regler & Villkor titta under \"Regler & Villkor\" på sidan. </p>
                        <p class=\"smallLetters\"> &nbsp; </p>
                        <p> Lycka Till! </p>
                        <p> Vänligen, </p>
                        <p> <span class=\"boldLetters\">{$casino_name}</span> supporten </p>";
                break;
            case 'rs_RS':
                $mail_title = "Dobrodošli {$player_username} - Vaša {$casino_name} kazino registracija";
                $mail_message =
                    "<p> Poštovani/a <span class=\"boldLetters\">{$player_username}</span>, </p>
                        <p> Hvala Vam na Vašoj registraciji kod <span class=\"boldLetters\">{$casino_name}</span> kazina! Vaš nalog je uspešno kreiran.</p>
                        <p class=\"smallLetters\"> &nbsp; </p>
                        <p> Vaše korisničko ime je: <span class=\"boldLetters\">{$player_username}</span> </p>
                        <p> <span class=\"boldLetters\">Molimo Vas da kliknete na link ispod da verfikujete Vašu email adresu:</span> </p>
                        <p> <a class=\"underlineLetters boldLetters\" href=\"{$player_activation_link}\">{$player_activation_link}</a> </p>
                        <p class=\"smallLetters\"> &nbsp; </p>
                        <p> Aktivacija je neophodna kako bi ste uživali u punoj ponudi naših servisa. </p>
                        <p> Ukoliko imate bilo kakva pitanja ili Vam je potrebna dalja pomoć, nemojte se ustručavati da kontaktirate naš <a href=\"{$support_link}\" class=\"underlineLetters\">tim podrške</a>. </p>
                        <p> Za naše Uslove korišćenja molimo Vas da posetite naš sajt i kliknete na link <a href=\"{$terms_link}\" class=\"underlineLetters\">\"Uslovi korišćenja\"</a></p>
                        <p class=\"smallLetters\"> &nbsp; </p>
                        <p> SREĆNO! </p>
                        <p> Iskreno Vaš, </p>
                        <p> <span class=\"boldLetters\">{$casino_name}</span> tim podrške </p>";
                break;
            case 'en_GB':
            default:
                $mail_title = "Welcome {$player_username} - Your {$casino_name} Casino registration";
                $mail_message =
                    "<p> Dear <span class=\"boldLetters\">{$player_username}</span>, </p>
                        <p> Thank you for your registration at <span class=\"boldLetters\">{$casino_name}</span> Casino! Your account has been successfully created.</p>
                        <p class=\"smallLetters\"> &nbsp; </p>
                        <p> Your username is: <span class=\"boldLetters\">{$player_username}</span> </p>
                        <p> <span class=\"boldLetters\">Please click to link below to validate your email:</span> </p>
                        <p> <a class=\"underlineLetters boldLetters\" href=\"{$player_activation_link}\">{$player_activation_link}</a> </p>
                        <p class=\"smallLetters\"> &nbsp; </p>
                        <p> Activation is necessary to use the full range of services.</p>
                        <p> If there are any questions or you need further assistance, don't hesitate to contact our <a href=\"{$support_link}\" class=\"underlineLetters\">customer support team</a>. </p>
                        <p> For our terms & conditions please visit our web site <a href=\"{$terms_link}\" class=\"underlineLetters\">\"Terms and Conditions\"</a></p>
                        <p class=\"smallLetters\"> &nbsp; </p>
                        <p> GOOD LUCK! </p>
                        <p> Yours sincerly, </p>
                        <p> The <span class=\"boldLetters\">{$casino_name}</span> support team</p>";
        }
		$message = self::getMailBody($language_settings, $mail_message, $site_images_location, $mail_title, $background_color, $text_color, $small_text_color, $footer_background,
				$text_font_size, $small_text_font_size,
				$hyperlink_text_color, $hyperlink_font_size,
				$hyperlink_small_text_color, $hyperlink_small_font_size);
		return array("mail_title"=>$mail_title, "mail_message"=>$message);
	}

    //generate mail to player that his account is activated after his registration on web site
    //2. Account activation success - following on email verification - informative  mail for new user
	public static function getActivatedPlayerEmailToPlayerContent($player_username, $playerEmail,
	$site_images_location, $casino_name, $site_link, $contact_link, $support_link, $terms_link, $language_settings = 'en_GB',
	$background_color = "#FFFFFF", $text_color = "#000000", $small_text_color = "#000000", $footer_background = "#FFFFFF",
	$text_font_size = "15", $small_text_font_size = "8",
	$hyperlink_text_color = "#FFFFFF", $hyperlink_font_size = "15",
	$hyperlink_small_text_color = "#FFFFFF", $hyperlink_small_font_size = "8"){
        switch ($language_settings){
            case 'de_DE':
                $mail_title = "{$casino_name} - Benutzerkonto Aktivierung: {$player_username}";
                $mail_message =
                    "<p> Liebe(r) <span class=\"boldLetters\">{$player_username}</span>, </p>
                    <p> Ihr Benutzerkonto wurde erfolgreich aktiviert. </p>
                    <p> Die mit diesem Konto verbundene E-Mail Adresse lautet: <a class=\"boldLetters\" href=\"mailto:{$playerEmail}\">{$playerEmail}</a>. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Viel Vergnügen bei der Nutzung unser Angebote. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Sollten Sie weitere Fragen haben oder sonstige Unterstützung benötigen, bitten wir Sie, sich an </p>
                    <p> unser <a href=\"{$support_link}\" class=\"underlineLetters\">Kundendienst-Service-Team</a> zu wenden. </p>
                    <p> Unsere <a href=\"{$terms_link}\" class=\"underlineLetters\">\"Allgemeinen Geschäftsbedingung\"</a> finden Sie auf unserer Webseite. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> WIR WÜNSCHEN VIEL GLÜCK UND GUTE UNTERHALTUNG! </p>
                    <p> Mit freundlichen Grüßen, </p>
                    <p> Ihr <span class=\"boldLetters\">{$casino_name}</span> Team</p>";
                break;
            case 'cs_CZ':
                $mail_title = "{$casino_name} - Aktivace uživatelského účtu: {$player_username}";
                $mail_message =
                    "<p> Vážený/á <span class=\"boldLetters\">{$player_username}</span>, </p>
                    <p> Váš uživatelský účet byl úspěšně aktivován. </p>
                    <p> Emailová adresa spojena s tímto účtem je: <a class=\"boldLetters\" href=\"mailto:{$playerEmail}\">{$playerEmail}</a>. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Příjemnou zábavu při uživání naši nabídky. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Máte-li jakékoliv další dotazy nebo potřebujete jakoukoliv další pomoc, prosím, kontaktujte naši </p>
                    <p> <a href=\"{$support_link}\" class=\"underlineLetters\">Zákaznickou podporu-služby zákazníkům</a>. </p>
                    <p> Naše <a href=\"{$terms_link}\" class=\"underlineLetters\">\"Všeobecné podmínky\"</a> naleznete na našich webových stránkách. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> PŘEJEME HODNĚ ŠTĚSTÍ A PŘÍJEMNOU ZÁBAVU! </p>
                    <p> S přátelským pozdravem, </p>
                    <p> Váš <span class=\"boldLetters\">{$casino_name}</span> tým. </p>";
                break;
            case 'sv_SE':
                $mail_title = "{$casino_name} – Kontoaktivering: {$player_username}";
                $mail_message =
                    "<p> Kära <span class=\"boldLetters\">{$player_username}</span>, </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Ditt konto har aktiverats. </p>
                    <p> E-postadressen som är kopplat till ditt spelarkonto är: <a class=\"boldLetters\" href=\"mailto:{$playerEmail}\">{$playerEmail}</a>. </p>
                    <p> Njut av komplett utbud av våra tjänster nu. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Om du har några frågor eller behöver ytterligare hjälp, tveka inte att kontakta vår kundtjänst. </p>
                    <p> För våra Regler och Villkor vänligen läs, <a href=\"{$terms_link}\" class=\"underlineLetters\">\"Regler och Villkor\"</a>. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Lycka Till! </p>
                    <p> Vänligen, </p>
                    <p> <span class=\"boldLetters\">{$casino_name}</span> supporten </p>";
                break;
            case 'rs_RS':
                $mail_title = "{$casino_name} - Aktivacija korisničkog naloga: {$player_username}";
                $mail_message =
                    "<p> Poštovani/a <span class=\"boldLetters\">{$player_username}</span>, </p>
                    <p> Vaš nalog je uspešno aktiviran. </p>
                    <p> Email adresa povezana sa Vašim korisničkim nalogom: <a class=\"boldLetters\" href=\"mailto:{$playerEmail}\">{$playerEmail}</a>. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Uživajte u punoj ponudi naših servisa. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Ukoliko imate bilo kakva pitanja ili Vam je potrebna dalja pomoć, nemojte se ustručavati da kontaktirate naš </p>
                    <p> <a href=\"{$support_link}\" class=\"underlineLetters\">tim korisničke podrške</a>. </p>
                    <p> Za naše Uslove korišćenja molimo Vas posetite naš web sajt i kliknite na link na <a href=\"{$terms_link}\" class=\"underlineLetters\">\"Uslovi koriščenja\"</a>. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> SREĆNO! </p>
                    <p> Iskreno Vaš, </p>
                    <p> <span class=\"boldLetters\">{$casino_name}</span> tim podrške</p>";
                break;
            case 'en_GB':
            default:
                $mail_title = "{$casino_name} - User account activation: {$player_username}";
                $mail_message =
                    "<p> Dear <span class=\"boldLetters\">{$player_username}</span>, </p>
                    <p> Your account has been successfully activated. </p>
                    <p> The email address connected to your user account is: <a class=\"boldLetters\" href=\"mailto:{$playerEmail}\">{$playerEmail}</a>. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Enjoy the full range of our services now. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Should you have any questions or you need further assistance, don't hesitate to contact our </p>
                    <p> <a href=\"{$support_link}\" class=\"underlineLetters\">customer support team</a>. </p>
                    <p> For our terms and conditions please visit on our website <a href=\"{$terms_link}\" class=\"underlineLetters\">\"Terms and Conditions\"</a>. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> GOOD LUCK! </p>
                    <p> Yours sincerly, </p>
                    <p> The <span class=\"boldLetters\">{$casino_name}</span> support team</p>";
        }
		$message = self::getMailBody($language_settings, $mail_message, $site_images_location, $mail_title, $background_color, $text_color, $small_text_color, $footer_background,
				$text_font_size, $small_text_font_size, $hyperlink_text_color, $hyperlink_font_size, $hyperlink_small_text_color, $hyperlink_small_font_size
        );
		return array("mail_title"=>$mail_title, "mail_message"=>$message);
	}

    //email content to charge fee per month from player if not active for 180 (inactive_time) days
    //3. Reminder inactivity  350 days - announcement of "administrative fee" - if no login within 10 days
	public static function getBeforeChargeFeeFromPlayerContent($player_username, $site_images_location, $casino_name, $site_link, $support_link, $terms_link, $contact_link,
	$fee, $currency, $next_fee_date, $reactivate_before_fee_date, $inactive_time, $language_settings = 'en_GB',
	$background_color = "#FFFFFF", $text_color = "#000000", $small_text_color = "#000000", $footer_background = "#FFFFFF",
	$text_font_size = "15", $small_text_font_size = "8",
	$hyperlink_text_color = "#FFFFFF", $hyperlink_font_size = "15",
	$hyperlink_small_text_color = "#FFFFFF", $hyperlink_small_font_size = "8"){
        $inactive_time_minus_10_days = $inactive_time - 10;
        $fee = NumberHelper::format_double($fee);
        switch ($language_settings){
            case 'de_DE':
                $mail_title = "{$casino_name} - Keine Kontoaktivitäten - Erinnerung";
                $mail_message =
				"<p> Liebe(r) <span class=\"boldLetters\">{$player_username}</span>, </p>
                <p class=\"smallLetters\"> &nbsp; </p>
				<p> Sie waren nunmehr seit <span class=\"boldLetters\">{$inactive_time_minus_10_days} Taggen</span> nicht mehr in Ihrem Benutzerkonto eingeloggt. </p>
				<p class=\"smallLetters\"> &nbsp; </p>
				<p> Entsprechend unserer <a href=\"{$terms_link}\" class=\"underlineLetters\">\"Allgemeinen Geschäftsbedingungen\"</a> sind wir berechtigt, nach {$inactive_time} Tagen Inaktivität eine <span class=\"boldLetters\">{$fee} {$currency} Verwaltungsgebühr</span> einzuheben. </p>
				<p> Diese Verwaltungsgebühr kommt selbstverständlich nicht zum Tragen, wenn Sie sich vor dem </p>
				<p> <span class=\"boldLetters\">{$reactivate_before_fee_date}</span> in Ihr Benutzerkonto einloggen. </p>
				<p> Bitte beachten Sie: Bevor eine Verwaltungsgebühr aufgrund von Inaktivität verrechnet wird, werden </p>
				<p> eventuell aufrechte Promotions und Promotion-Kredite von Ihrem Konto abgebucht. </p>
				<p class=\"smallLetters\"> &nbsp; </p>
				<p> Bitte melden Sie sich in Ihrem Konto an, um unsere tollen Spiele und eventuell verfügbare Promotions anzusehen. </p>
				<p> Sollten Sie weitere Fragen haben, bitten wir Sie, sich an unser <a class=\"underlineLetters\" href=\"{$support_link}\">Kundendienst-Service-Team</a> zu wenden. </p
				<p class=\"smallLetters\"> &nbsp; </p>
				<p> Mit freundlichen Grüßen, </p>
				<p> Ihr <span class=\"boldLetters\">{$casino_name}</span> Team</p>";
                break;
            case 'cs_CZ':
                $mail_title = "{$casino_name} - nečinný  účet— upozornění";
                $mail_message =
				"<p> Vážený/á <span class=\"boldLetters\">{$player_username}</span>, </p>
                <p class=\"smallLetters\"> &nbsp; </p>
				<p> Posledních <span class=\"boldLetters\">{$inactive_time_minus_10_days} dní</span> jste nebyl/a na Vašem uživatelském účtu. </p>
				<p class=\"smallLetters\"> &nbsp; </p>
				<p> V návaznosti na naše <a href=\"{$terms_link}\" class=\"underlineLetters\">\"Všeobecné podmínky\"</a> jsme opravněnni {$inactive_time} dnech nečinnosti </p>
				<p> Vašeho uživatelského účtu účtovat <span class=\"boldLetters\">{$fee} {$currency} administrative fee</span> Správní poplatky. </p>
				<p> Tento správní poplatek samozřejmě nebude účtován pokud se do <span class=\"boldLetters\">{$reactivate_before_fee_date}</span> pŕihlásíte na Váš účet. </p>
				<p> Upozornění: Dříve než bude účtován správní poplatek v důsledku nečinnosti, </p>
				<p> budou případně odečteny stávající akce a  akční kredity z vašeho účtu. </p>
				<p class=\"smallLetters\"> &nbsp; </p>
				<p> Prosím, přihlaste se do svého uživatelského účtu pro zobrazení naších skvělých </p>
				<p> her a všech nových  dostupných  akcí. </p>
				<p> Máte-li jakékoliv další dotazy nebo potřebujete jakoukoliv další pomoc, prosím, kontaktujte naši <a class=\"underlineLetters\" href=\"{$support_link}\">Zákaznickou podporu-služby zákazníkům</a>. </p>
				<p class=\"smallLetters\"> &nbsp; </p>
				<p> S přátelským pozdravem, </p>
				<p> Váš <span class=\"boldLetters\">{$casino_name}</span> tým. </p>";
                break;
            case 'sv_SE':
                $mail_title = "{$casino_name} – KontoInaktivitet - Påminnelse";
                $mail_message =
				"<p> Kära <span class=\"boldLetters\">{$player_username}</span>, </p>
                <p class=\"smallLetters\"> &nbsp; </p>
				<p> Du har inte loggat in på ditt konto på <span class=\"boldLetters\">{$inactive_time_minus_10_days} dagar</span>. </p>
				<p class=\"smallLetters\"> &nbsp; </p>
				<p> Enligt våra <a href=\"{$terms_link}\" class=\"underlineLetters\">\"Regler och Villkor\"</a> hat vi rätt att ta ut en månatlig </p>
				<p> <span class=\"boldLetters\">{$fee} {$currency} administrationsavgift</span> på ditt konto efter {$inactive_time} dagars inaktivitet. </p>
				<p> Denna avgift kommer inte att dras från ditt konto, om du loggar in på ditt konto innan: </p>
				<p> <span class=\"boldLetters\">{$reactivate_before_fee_date}</span> </p>
				<p> Observera att innan en inaktivitetsavgift dras, kommer vi avbryta återstående kampanjer och </p>
				<p> erbjudanden. </p>
				<p class=\"smallLetters\"> &nbsp; </p>
				<p> Vänligen logga in och se våra fantastiska spel och tillgängliga erbjudanden. </p>
				<p> Om du har några frågor, vänligen kontakta vår kundtjänst för att få hjälp. </p>
				<p class=\"smallLetters\"> &nbsp; </p>
				<p> Vänligen, </p>
				<p> <span class=\"boldLetters\">{$casino_name}</span> supporten </p>";
                break;
            case 'rs_RS':
                $mail_title = "{$casino_name} - neaktivnost naloga - Podsetnik";
                $mail_message =
				"<p> Poštovani/a <span class=\"boldLetters\">{$player_username}</span>, </p>
                <p class=\"smallLetters\"> &nbsp; </p>
				<p> Niste bili logovani u Vaš korisnički nalog <span class=\"boldLetters\">{$inactive_time_minus_10_days} dana</span>. </p>
				<p class=\"smallLetters\"> &nbsp; </p>
				<p> Prema našim <a href=\"{$terms_link}\" class=\"underlineLetters\">\"Uslovima korišćenja\"</a> biće Vam naplaćeno <span class=\"boldLetters\">{$fee} {$currency} administrativne takse</span> </p>
				<p> na Vašem korisničkom nalogu posle {$inactive_time} dana neaktivnosti. Ova takse neće biti oduzeta sa stanja kredita Vašeg naloga, </p>
				<p> ukoliko se ulogujete na Vaš korisnički nalog pre: <span class=\"boldLetters\">{$reactivate_before_fee_date}</span> </p>
				<p> Takođe da Vas podsetimo, da ćemo pre izvršavanja ove akcije usled neaktivnosti naloga, takođe Vam otkazati sve preostale promocije i </p>
				<p> promotivne kredite. </p>
				<p class=\"smallLetters\"> &nbsp; </p>
				<p> Molimo Vas da se ulogujete i pogledate našu sjajnu ponudu igara i dostupnih promocija. </p>
				<p> Ukoliko imate bilo kakve nedoumice, molimo Vas da kontaktirate naš <a class=\"underlineLetters\" href=\"{$support_link}\">tim korisničke podrške</a> za pomoć. </p>
				<p class=\"smallLetters\"> &nbsp; </p>
				<p> Iskreno Vaš, </p>
				<p> <span class=\"boldLetters\">{$casino_name}</span> tim podrške </p>";
                break;
            case 'en_GB':
            default:
                $mail_title = "{$casino_name} - account inactivity - Reminder";
                $mail_message =
				"<p> Dear <span class=\"boldLetters\">{$player_username}</span>, </p>
                <p class=\"smallLetters\"> &nbsp; </p>
				<p> You have not been logged into your account for <span class=\"boldLetters\">{$inactive_time_minus_10_days} days</span>. </p>
				<p class=\"smallLetters\"> &nbsp; </p>
				<p> According to our <a href=\"{$terms_link}\" class=\"underlineLetters\">\"Terms and Conditions\"</a> we are entitled to charge a monthly <span class=\"boldLetters\">{$fee} {$currency} administrative fee</span> </p>
				<p> to your account after {$inactive_time} days of inactivity. This fee will not be deducted from your account balance, </p>
				<p> when you login to your account before: <span class=\"boldLetters\">{$reactivate_before_fee_date}</span> </p>
				<p> Please note, that before an inactivity fee is deducted, we will cancel remaining promotions and </p>
				<p> promotion credits. </p>
				<p class=\"smallLetters\"> &nbsp; </p>
				<p> Please login and see our great games and available promotions. </p>
				<p> If you have any concerns, please contact our <a class=\"underlineLetters\" href=\"{$support_link}\">customer support team</a> for assistance. </p>
				<p class=\"smallLetters\"> &nbsp; </p>
				<p> Yours sincerly, </p>
				<p> The <span class=\"boldLetters\">{$casino_name}</span> support team</p>";

        }

		$message = self::getMailBody($language_settings, $mail_message, $site_images_location, $mail_title, $background_color, $text_color, $small_text_color, $footer_background,
				$text_font_size, $small_text_font_size,
				$hyperlink_text_color, $hyperlink_font_size,
				$hyperlink_small_text_color, $hyperlink_small_font_size);
		return array("mail_title"=>$mail_title, "mail_message"=>$message);
	}

    //email content to charge fee per month from player if not active for 180 (inactive_time) days
    //4. Information about fee charged after - 360 days of inactivity
	public static function getChargeFeeFromPlayerContent($player_username, $site_images_location, $casino_name, $site_link, $support_link, $terms_link, $contact_link,
	$fee, $currency, $next_fee_date, $reactivate_before_fee_date, $inactive_time, $language_settings = 'en_GB',
	$background_color = "#FFFFFF", $text_color = "#000000", $small_text_color = "#000000", $footer_background = "#FFFFFF",
	$text_font_size = "15", $small_text_font_size = "8",
	$hyperlink_text_color = "#FFFFFF", $hyperlink_font_size = "15",
	$hyperlink_small_text_color = "#FFFFFF", $hyperlink_small_font_size = "8"){
        $fee = NumberHelper::format_double($fee);
        switch ($language_settings){
            case 'de_DE':
                $mail_title = "{$casino_name} - Keine Kontoaktivitäten - Erinnerung";
                $mail_message =
				"<p> Liebe(r) <span class=\"boldLetters\">{$player_username}</span>, </p>
                <p class=\"smallLetters\"> &nbsp; </p>
				<p> Sie waren nunmehr seit <span class=\"boldLetters\">{$inactive_time} Tagen</span> nicht mehr in Ihrem Benutzerkonto eingeloggt. </p>
				<p> Eine <span class=\"boldLetters\">{$fee} {$currency} Verwaltungsgebühr</span> wurde von Ihrem Konto eingehoben. </p>
				<p> Die nächste monatliche Verwaltungsgebühr wird am <span class=\"boldLetters\">{$next_fee_date}</span> verrechnet. </p>
				<p class=\"smallLetters\"> &nbsp; </p>
				<p> Wir deaktivieren die Verrechnung von Verwaltungsgebühren sofort, wenn Sie Ihr Benutzerkonto reaktivieren. </p>
				<p> Loggen Sie dazu einfach in Ihr Konto ein - und sehen Sie unsere fantastischen Spiele und eventuell neu verfügbare Promotions. </p>
				<p> Sollten Sie weitere Fragen haben, bitten wir Sie, sich an unser <a class=\"underlineLetters\" href=\"{$support_link}\">Kundendienst-Service-Team</a> zu wenden. </p>
				<p> Unsere <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Allgemeinen Geschäftsbedingungen\"</a> finden Sie auf unserer Webseite. </p>
				<p class=\"smallLetters\"> &nbsp; </p>
				<p> Mit freundlichen Grüßen, </p>
				<p> Ihr <span class=\"boldLetters\">{$casino_name}</span> Team</p>";
                break;
            case 'cs_CZ':
                $mail_title = "{$casino_name} - nečinný  účet - upozornění";
                $mail_message =
				"<p> Vážený/á <span class=\"boldLetters\">{$player_username}</span>, </p>
                <p class=\"smallLetters\"> &nbsp; </p>
				<p> Posledních <span class=\"boldLetters\">{$inactive_time} dni</span> jste neaktivovali Váš uživatelský účet. </p>
				<p> Byl Vám odečten správní poplatek <span class=\"boldLetters\">{$fee} {$currency}</span> z Vašeho uživatelského účtu. </p>
				<p> Další měsíční správní poplatek je splatný <span class=\"boldLetters\">{$next_fee_date}</span>. </p>
				<p class=\"smallLetters\"> &nbsp; </p>
				<p> Vyúčtování správních poplaků deaktivujeme okamžitě, když reaktivujete Váš uživatelský účet. </p>
				<p> Jednoduše se přihlašte na Váš uživatelský účet a nahlédněte </p>
				<p> do našich úžasných her a eventuelně na naše nové dostupné akce. </p>
				<p> Máte-li jakékoli další dotazy nebo potřebujete jakoukoliv další pomoc, prosím, kontaktujte naši <a class=\"underlineLetters\" href=\"{$support_link}\">\"Zákaznickou podporu-služby zákazníkům\"</a>. </p>
				<p> Naše <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Všeobecné podmínky\"</a> naleznete na našich webových stránkách. </p>
				<p class=\"smallLetters\"> &nbsp; </p>
				<p> S přátelským pozdravem, </p>
				<p> Váš <span class=\"boldLetters\">{$casino_name}</span> tým. </p>";
                break;
            case 'sv_SE':
                $mail_title = "Beträffande: {$casino_name} - Ert konto: Administrationsavgift på grund av Inaktivitet";
                $mail_message =
				"<p> Kära <span class=\"boldLetters\">{$player_username}</span>, </p>
                <p class=\"smallLetters\"> &nbsp; </p>
				<p> Du har inte varit inloggad på ditt konto på <span class=\"boldLetters\">{$inactive_time} dagar</span>. </p>
				<p> En <span class=\"boldLetters\">{$fee} {$currency} administrationsavgift har belastat ditt inaktiva konto. </span> </p>
				<p> Nästa <span class=\"boldLetters\">administrationsavgift</span> kommer att dras från ditt konto den: <span class=\"boldLetters\">{$next_fee_date}</span>. </p>
				<p class=\"smallLetters\"> &nbsp; </p>
				<p> Vi kommer att avvaktivera månatliga inaktiveringsavgiften omedelbart när du återaktiverar kontot. </p>
				<p> Vänligen, logga in på ditt konto och se våra fantastiska spel och tillgängliga erbjudanden. </p>
				<p class=\"smallLetters\"> &nbsp; </p>
				<p> Om du har några frågor, vänligen kontakta vår kundtjänst för att få hjälp. </p>
				<p> För våra Regler och Villkor vänligen läs, <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Regler och Villkor\"</a>. </p>
				<p class=\"smallLetters\"> &nbsp; </p>
				<p> Vänligen, </p>
				<p> <span class=\"boldLetters\">{$casino_name}</span> supporten</p>";
                break;
            case 'rs_RS':
                $mail_title = "{$casino_name} - naplata administrativne takse neaktivnog naloga";
                $mail_message =
				"<p> Poštovani/a <span class=\"boldLetters\">{$player_username}</span>, </p>
                <p class=\"smallLetters\"> &nbsp; </p>
				<p> Niste se logovani u Vaš korisnički nalog <span class=\"boldLetters\">{$inactive_time} dana</span>. </p>
				<p> Administrativna taksa u iznosu od <span class=\"boldLetters\">{$fee} {$currency} </span> će biti naplaćena od Vašeg neaktivnog naloga. </p>
				<p> Sledeća mesečna naplata će biti izvršena sa stanja na Vašem nalogu: <span class=\"boldLetters\">{$next_fee_date}</span>. </p>
				<p class=\"smallLetters\"> &nbsp; </p>
				<p> Mi ćemo deaktivirati mesečnu naplatu administrativne takse usled neaktivnosti, čim reaktivirate Vaš korisnički nalog. </p>
				<p> Molimo Vas, ulogujte se na Vaš nalog i pogledajte fantastičnu ponudu naših igara i dostupnih promocija. </p>
				<p> Ukoliko imate nedoumica, molimo Vas da kontaktirate naš <a class=\"underlineLetters\" href=\"{$support_link}\">tim podrške</a> za pomoć. </p>
				<p> Za naše Uslove korišćenja molimo Vas da posetite naš web sajt i kliknete na link <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Uslovi korišćenja\"</a>. </p>
				<p class=\"smallLetters\"> &nbsp; </p>
				<p> Iskreno Vaš, </p>
				<p> <span class=\"boldLetters\">{$casino_name}</span> tim podrške</p>";
                break;
            case 'en_GB':
            default:
                $mail_title = "{$casino_name} - account inactivity fee";
                $mail_message =
				"<p> Dear <span class=\"boldLetters\">{$player_username}</span>, </p>
                <p class=\"smallLetters\"> &nbsp; </p>
				<p> You have not been logged into your account for <span class=\"boldLetters\">{$inactive_time} days</span>. </p>
				<p> A <span class=\"boldLetters\">{$fee} {$currency} administrative fee</span> has been charged to your inactive account. </p>
				<p> The next monthly charge will be deducted from your account balance on: <span class=\"boldLetters\">{$next_fee_date}</span>. </p>
				<p class=\"smallLetters\"> &nbsp; </p>
				<p> We will deactivate monthly inactivity fee immediately, when you reactivate your account. </p>
				<p> Please, simply login to your account and see our fantastic games and available promotions. </p>
				<p> If you have any concerns, please contact our <a class=\"underlineLetters\" href=\"{$support_link}\">customer support team</a> for assistance. </p>
				<p> For our terms & conditions please visit our  website <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Terms and Conditions\"</a>. </p>
				<p class=\"smallLetters\"> &nbsp; </p>
				<p> Yours sincerly, </p>
				<p> The <span class=\"boldLetters\">{$casino_name}</span> support team</p>";
        }

		$message = self::getMailBody($language_settings, $mail_message, $site_images_location, $mail_title, $background_color, $text_color, $small_text_color, $footer_background,
				$text_font_size, $small_text_font_size,
				$hyperlink_text_color, $hyperlink_font_size,
				$hyperlink_small_text_color, $hyperlink_small_font_size);
		return array("mail_title"=>$mail_title, "mail_message"=>$message);
	}

    //5. Lost password recovery email - link opens new password section
	//generate mail to player with link to reset his lost password
	public static function getPasswordEmailToPlayerContent($player_username, $site_images_location, $casino_name, $site_link, $forgot_password_link,
	$contact_link, $support_link, $terms_link, $language_settings = 'en_GB',
	$background_color = "#FFFFFF", $text_color = "#000000", $small_text_color = "#000000", $footer_background = "#FFFFFF",
	$text_font_size = "15", $small_text_font_size = "8",
	$hyperlink_text_color = "#FFFFFF", $hyperlink_font_size = "15",
	$hyperlink_small_text_color = "#FFFFFF", $hyperlink_small_font_size = "8"){
        switch ($language_settings){
            case 'de_DE':
               $mail_title = "Bitte ändern Sie Ihr Passwort für {$site_link}";
               $mail_message =
                    "<p> Liebe(r) <span class=\"boldLetters\">{$player_username}</span>, </p>
                    <p> Sie haben eine Anfrage zur Änderung des Passwortes für Ihr <span class=\"boldLetters\">{$site_link}</span> Benutzerkonto gestellt. </p>
                    <p> Bitte klicken Sie auf den untenstehenden Link und wählen Sie ein neues Passwort entsprechend den Anweisungen: </p>
                    <p> <a class=\"underlineLetters boldLetters\" href=\"{$forgot_password_link}\">{$forgot_password_link}</a> </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Sollten Sie weitere Fragen haben oder sonstige Unterstützung benötigen, bitten wir Sie, sich an </p>
                    <p> unser <a class=\"underlineLetters\" href=\"{$support_link}\">Kundendienst-Service-Team</a> zu wenden. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Unsere <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Allgemeinen Geschäftsbedingungen\"</a> finden Sie auf unserer Webseite. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> WIR WÜNSCHEN VIEL GLÜCK UND GUTE UNTERHALTUNG! </p>
                    <p> Mit freundlichen Grüßen, </p>
                    <p> Ihr <span class=\"boldLetters\">{$casino_name}</span> Team</p>";
                break;
            case 'cs_CZ':
               $mail_title = "Prosím změňte si heslo pro {$site_link}";
               $mail_message =
                    "<p> Vážený/á <span class=\"boldLetters\">{$player_username}</span>, </p>
                    <p> Vytvořili jste žádost o změnu hesla pro <span class=\"boldLetters\">{$site_link}</span> uživatelský účet </p>
                    <p> Prosím klikněte na dole se zobrazený odkaz(Link)a zadejte nové heslo dle instrukcí: </p>
                    <p> <a class=\"underlineLetters boldLetters\" href=\"{$forgot_password_link}\">{$forgot_password_link}</a> </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Máte-li jakékoliv další dotazy nebo potřebujete jakoukoliv další pomoc, prosím, kontaktujte naši  </p>
                    <p> <a class=\"underlineLetters\" href=\"{$support_link}\">\"Zákaznickou podporu-služby zákazníkům\"</a>. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Naše <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Všeobecné podmínky\"</a> naleznete na našich webových stránkách. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> PŘEJEME HODNĚ ŠTĚSTÍ A PŘÍJEMNOU ZÁBAVU! </p>
                    <p> S přátelským pozdravem, </p>
                    <p> Váš <span class=\"boldLetters\">{$casino_name}</span> tým. </p>";
                break;
            case 'sv_SE':
               $mail_title = "Vänligen ändra ditt lösenord för {$site_link}";
               $mail_message =
                    "<p> Kära <span class=\"boldLetters\">{$player_username}</span>, </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Du gjorde en begäran om att ändra ditt lösenord för {$site_link} konto. </p>
                    <p> Följ länken nedan och välj ett nytt lösenord enligt instruktionerna: </p>
                    <p> <a class=\"underlineLetters boldLetters\" href=\"{$forgot_password_link}\">{$forgot_password_link}</a> </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Om det finns några frågor eller behöver ytterligare hjälp, tveka inte att kontakta vår kundtjänst. </p>
                    <p> För våra Regler och Villkor vänligen läs, <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Terms and Conditions\"</a>. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Lycka Till! </p>
                    <p> Vänligen, </p>
                    <p> <span class=\"boldLetters\">{$casino_name}</span> supporten</p>";
                break;
            case 'rs_RS':
               $mail_title = "Molimo Vas promenite Vašu lozinku za {$site_link}";
               $mail_message =
                    "<p> Poštovani/a <span class=\"boldLetters\">{$player_username}</span>, </p>
                    <p> Postavili ste zahtev za izmenu Vaše lozinke na web sajtu {$site_link} </p>
                    <p> Molimo Va da pratite link ispod i postavite novu lozinku u skladu sa instrukcijama: </p>
                    <p> <a class=\"underlineLetters boldLetters\" href=\"{$forgot_password_link}\">{$forgot_password_link}</a> </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Ukoliko imate bilo kakva pitanja ili Vam je potrebna dalja pomoć, nemojte očekivati da kontaktirate naš: </p>
                    <p> <a class=\"underlineLetters\" href=\"{$support_link}\">korisnički tim podrške</a>. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Za naše Uslove korišćenja molimo Vas da posetite web sajt i kliknete na link <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Uslovi korišćenja\"</a>. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> SREĆNO! </p>
                    <p> Iskreno Vaš, </p>
                    <p> <span class=\"boldLetters\">{$casino_name}</span> tim podrške</p>";
               break;
            case 'en_GB':
            default:
               $mail_title = "Please change your password for {$site_link}";
               $mail_message =
                    "<p> Dear <span class=\"boldLetters\">{$player_username}</span>, </p>
                    <p> You made a request to change your password for your {$site_link} </p>
                    <p> Please follow the link below and select a new password according to the instructions: </p>
                    <p> <a class=\"underlineLetters boldLetters\" href=\"{$forgot_password_link}\">{$forgot_password_link}</a> </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> If there are any questions or you need further assistance, don't hesitate to contact our: </p>
                    <p> <a class=\"underlineLetters\" href=\"{$support_link}\">customer support team</a>. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> For our terms and conditions please visit on our website <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Terms and Conditions\"</a>. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> GOOD LUCK! </p>
                    <p> Your sincerly, </p>
                    <p> The <span class=\"boldLetters\">{$casino_name}</span> support team</p>";
        }
		$message = self::getMailBody($language_settings, $mail_message, $site_images_location, $mail_title, $background_color, $text_color, $small_text_color, $footer_background,
				$text_font_size, $small_text_font_size,
				$hyperlink_text_color, $hyperlink_font_size,
				$hyperlink_small_text_color, $hyperlink_small_font_size);
		return array("mail_title"=>$mail_title, "mail_message"=>$message);
	}

    //will not be used - generate mail to player with his username
    //6. Lost username recovery email - Email contains username
	public static function getUsernameEmailToPlayerContent($player_name, $player_username,
	$site_images_location, $casino_name, $site_link, $contact_link,
	$support_link, $terms_link, $language_settings = 'en_GB',
	$background_color = "#FFFFFF", $text_color = "#000000", $small_text_color = "#000000", $footer_background = "#FFFFFF",
	$text_font_size = "15", $small_text_font_size = "8",
	$hyperlink_text_color = "#FFFFFF", $hyperlink_font_size = "15",
	$hyperlink_small_text_color = "#FFFFFF", $hyperlink_small_font_size = "8")
    {
        switch ($language_settings){
            case 'de_DE':
                $mail_title = "Ihr Benutzername für {$site_link}";
                $mail_message =
                    "<p> Liebe(r) <span class=\"boldLetters\">{$player_name}</span>, </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Der Benutzername für Ihr <span class=\"boldLetters\">{$site_link}</span> Konto ist: <span class=\"boldLetters\">{$player_username}</span> </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Sollten Sie weitere Fragen haben oder sonstige Unterstützung benötigen, bitten wir Sie, sich an unser <a class=\"underlineLetters\" href=\"{$support_link}\">Kundendienst-Service-Team</a> zu wenden.</p>
                    <p> Unsere <a href=\"{$terms_link}\" class=\"underlineLetters\">\"Allgemeinen Geschäftsbedingungen\"</a> finden Sie auf unserer Webseite. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> WIR WÜNSCHEN VIEL GLÜCK UND GUTE UNTERHALTUNG!! </p>
                    <p> Mit freundlichen Grüßen, </p>
                    <p> Ihr <span class=\"boldLetters\">{$casino_name}</span> Team </p>
                   ";
                break;
            case 'cs_CZ':
                $mail_title = "Vaše uživatelské jméno pro {$site_link}";
                $mail_message =
                    "<p> Vážený/á <span class=\"boldLetters\">{$player_name}</span>, </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Uživatelské jméno pro <span class=\"boldLetters\">{$site_link}</span> Účet je: <span class=\"boldLetters\">{$player_username}</span> </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Máte-li jakékoliv další dotazy nebo potřebujete jakoukoliv další pomoc, prosím, kontaktujte naši <a class=\"underlineLetters\" href=\"{$support_link}\">\"Zákaznickou podporu-služby zákazníkům\"</a>.</p>
                    <p> Naše <a href=\"{$terms_link}\" class=\"underlineLetters\">\"Všeobecné podmínky\"</a> naleznete na našich webových stránkách. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> PŘEJEME HODNĚ ŠTĚSTÍ A PŘÍJEMNOU ZÁBAVU! </p>
                    <p> S přátelským pozdravem, </p>
                    <p> Váš <span class=\"boldLetters\">{$casino_name}</span> tým. </p>
                   ";
                break;
            case 'sv_SE':
                $mail_title = "Ditt lösenord för {$site_link}";
                $mail_message =
                    "<p> Kära <span class=\"boldLetters\">{$player_name}</span>, </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Användarnamnet för ditt <span class=\"boldLetters\">{$site_link}</span> konto är: <span class=\"boldLetters\">{$player_username}</span> </p>
                    <p> Om det finns några frågor eller behöver ytterligare hjälp, tveka inte att kontakta vår kundtjänst. </p>
                    <p> För våra Regler och Villkor vänligen läs, <a href=\"{$terms_link}\" class=\"underlineLetters\">\"Regler och Villkor\"</a> </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Lycka Till! </p>
                    <p> Vänligen, </p>
                    <p> <span class=\"boldLetters\">{$casino_name}</span> supporten </p>
                   ";
                break;
            case 'rs_RS':
                $mail_title = "Vaše korisničko ime na {$site_link}";
                $mail_message =
                    "<p> Poštovani/a <span class=\"boldLetters\">{$player_name}</span>, </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Korisničko ime za Vaš <span class=\"boldLetters\">{$site_link}</span> nalog je: <span class=\"boldLetters\">{$player_username}</span> </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Ukoliko imate neka pitanja ili Vam je potrebna dalja pomoć, nemojte se ustručavati da kontaktirate naš <a class=\"underlineLetters\" href=\"{$support_link}\">korisnički tim podrške</a>.</p>
                    <p> Za naše Uslove korišćenja molimo Vas posetite na našem web sajtu <a href=\"{$terms_link}\" class=\"underlineLetters\">\"Uslove korišćenja\"</a> </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> SREĆNO! </p>
                    <p> Iskreno Vaš, </p>
                    <p> <span class=\"boldLetters\">{$casino_name}</span> tim podrške </p>
                   ";
                break;
            case 'en_GB':
            default:
                $mail_title = "Your username for {$site_link}";
                $mail_message =
                    "<p> Dear <span class=\"boldLetters\">{$player_name}</span>, </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> The username for your <span class=\"boldLetters\">{$site_link}</span> account is: <span class=\"boldLetters\">{$player_username}</span> </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> If there are any questions or you need further assistance, don't hesitate to contact our <a class=\"underlineLetters\" href=\"{$support_link}\">customer support team</a>.</p>
                    <p> For our terms and conditions please visit on our website <a href=\"{$terms_link}\" class=\"underlineLetters\">\"Terms and Conditions\"</a> </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> GOOD LUCK! </p>
                    <p> Yours sincerly, </p>
                    <p> The <span class=\"boldLetters\">{$casino_name}</span> support team </p>
                   ";
        }
		$message = self::getMailBody($language_settings, $mail_message, $site_images_location, $mail_title, $background_color, $text_color, $small_text_color, $footer_background,
				$text_font_size, $small_text_font_size,
				$hyperlink_text_color, $hyperlink_font_size,
				$hyperlink_small_text_color, $hyperlink_small_font_size);
		return array("mail_title"=>$mail_title, "mail_message"=>$message);
	}

	//get mail generated content for player unlocking
    //7. Unlock account - after (n) incorrect password inputs - link for unlocking with a click
	public static function getUnlockPlayerEmailToPlayerContent($player_username, $site_images_location, $casino_name, $site_link,
	$playerUnlockLink, $support_link, $terms_link, $contact_link, $language_settings = 'en_GB',
	$background_color = "#FFFFFF", $text_color = "#000000", $small_text_color = "#000000", $footer_background = "#FFFFFF",
	$text_font_size = "15", $small_text_font_size = "8",
	$hyperlink_text_color = "#FFFFFF", $hyperlink_font_size = "15",
	$hyperlink_small_text_color = "#FFFFFF", $hyperlink_small_font_size = "8"){
        switch ($language_settings){
            case 'de_DE':
                $mail_title = "{$casino_name} - fehlgeschlagene Loginversuche zu Ihrem Benutzerkonto";
                $mail_message =
				"<p> Liebe(r) <span class=\"boldLetters\">{$player_username}</span>, </p>
                <p class=\"smallLetters\"> &nbsp; </p>
				<p> Unser System hat 10 ungültige Loginversuche zu Ihrem Benutzerkonto registriert. <p>
				<p> Ihr Konto wurde gesperrt, um unauthorisierten Zugriff zu verhindern. Bitte klicken Sie auf den untenstehenden Link, um Ihr Konto wieder freizuschalten: </p>
				<p> <a class=\"underlineLetters boldLetters\" href=\"{$playerUnlockLink}\">{$playerUnlockLink}</a> </p>
				<p class=\"smallLetters\"> &nbsp; </p>
				<p> Sollten Sie weitere Fragen haben oder sonstige Unterstützung benötigen, bitten wir Sie, sich an unser <a class=\"underlineLetters\" href=\"{$support_link}\">Kundendienst-Service-Team</a> zu wenden. </p>
                <p> Unsere <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Allgemeinen Geschäftsbedingungen\"</a> finden Sie auf unserer Webseite. </p>
                <p class=\"smallLetters\"> &nbsp; </p>
				<p> WIR WÜNSCHEN VIEL GLÜCK UND GUTE UNTERHALTUNG!! </p>
				<p> Mit freundlichen Grüßen, </p>
                <p> Ihr <span class=\"boldLetters\">{$casino_name}</span> Team</p>";
                break;
            case 'cs_CZ':
                $mail_title = "{$casino_name} –selhání při pŕihlášování k uživatelskému účtu";
                $mail_message =
				"<p> Vážený/á <span class=\"boldLetters\">{$player_username}</span>, </p>
                <p class=\"smallLetters\"> &nbsp; </p>
				<p> Náš system nabízí 10 pokusů k přihlášení k Vašemu uživatelskému účtu. <p>
				<p> Váš účet byl zabanován k zabránění zneužití. Prosím klikněte na dole zobrazený odkaz k odbanování Vaśeho uživatelského účtu: </p>
				<p> <a class=\"underlineLetters boldLetters\" href=\"{$playerUnlockLink}\">{$playerUnlockLink}</a> </p>
				<p class=\"smallLetters\"> &nbsp; </p>
				<p> Máte-li jakékoliv další dotazy nebo potřebujete jakoukoliv další pomoc, prosím, kontaktujte naši <a class=\"underlineLetters\" href=\"{$support_link}\">\"Zákaznickou podporu-služby zákazníkům\"</a>. </p>
                <p> Naše <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Všeobecné podmínky\"</a> naleznete na našich webových stránkách. </p>
                <p class=\"smallLetters\"> &nbsp; </p>
				<p> PŘEJEME HODNĚ ŠTĚSTÍ A PŘÍJEMNOU ZÁBAVU! </p>
				<p> S přátelským pozdravem, </p>
                <p> Váš <span class=\"boldLetters\">{$casino_name}</span> tým. </p>";
                break;
            case 'sv_SE':
                $mail_title = "{$casino_name} – felaktigt inloggningsförsök till ditt konto";
                $mail_message =
				"<p> Kära <span class=\"boldLetters\">{$player_username}</span>, </p>
                <p class=\"smallLetters\"> &nbsp; </p>
				<p> Vårat system har noterat felaktigt lösenord 10 gånger. <p>
				<p> Ditt konto har låsts för att förhindra obehörig åtkomst. </p>
				<p> Klicka på länken nedan för att låsa upp ditt konto: </p>
				<p> <a class=\"underlineLetters boldLetters\" href=\"{$playerUnlockLink}\">{$playerUnlockLink}</a> </p>
				<p class=\"smallLetters\"> &nbsp; </p>
				<p> Om du behöver ytterligare hjälp, kontakta vår kundtjänst. </p>
                <p> För våra Regler och Villkor vänligen läs, <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Regler och Villkor\"</a>. </p>
                <p class=\"smallLetters\"> &nbsp; </p>
				<p> Lycka Till! </p>
				<p> Vänligen, </p>
                <p> <span class=\"boldLetters\">{$casino_name}</span> supporten </p>";
                break;
            case 'rs_RS':
                $mail_title = "{$casino_name} - neuspešni pokušaji logovanja na Vaš nalog";
                $mail_message =
				"<p> Poštovani/a <span class=\"boldLetters\">{$player_username}</span>, </p>
                <p class=\"smallLetters\"> &nbsp; </p>
				<p> Naš sistem je prepoznao unos pogrešne lozinke 10 puta za Vaš korisnički nalog. <p>
				<p> Vaš nalog je zaključan da bi se sprečio neautorizovani pristup. Molimo Vaš da kliknete na link ispod da biste otključali vaš korisnički nalog: </p>
				<p> <a class=\"underlineLetters boldLetters\" href=\"{$playerUnlockLink}\">{$playerUnlockLink}</a> </p>
				<p class=\"smallLetters\"> &nbsp; </p>
				<p> Ukoliko Vam je potrebna dalja pomoć, molimo Vas da kontaktirate naš <a class=\"underlineLetters\" href=\"{$support_link}\">tim korisničke podrške</a>. </p>
                <p> Za naše Uslove korišćenja molimo Vas da posetite web sajt i kliknete na link <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Uslovi korišćenja\"</a>. </p>
                <p class=\"smallLetters\"> &nbsp; </p>
				<p> SREĆNO! </p>
				<p> Iskreno Vaš, </p>
                <p> <span class=\"boldLetters\">{$casino_name}</span> tim podrške</p>";
                break;
            case 'en_GB':
            default:
                $mail_title = "{$casino_name} - failed login attempts to your account";
                $mail_message =
				"<p> Dear <span class=\"boldLetters\">{$player_username}</span>, </p>
                <p class=\"smallLetters\"> &nbsp; </p>
				<p> Our system recognized a wrong password input of 10 times. <p>
				<p> Your account has been locked to prevent unauthorized access. Please click on the link below to unlock your account: </p>
				<p> <a class=\"underlineLetters boldLetters\" href=\"{$playerUnlockLink}\">{$playerUnlockLink}</a> </p>
				<p class=\"smallLetters\"> &nbsp; </p>
				<p> If you need further assistance, please contact our <a class=\"underlineLetters\" href=\"{$support_link}\">customer support team</a>. </p>
                <p> For our terms & conditions please visit our website <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Terms and Conditions\"</a>. </p>
                <p class=\"smallLetters\"> &nbsp; </p>
				<p> GOOD LUCK! </p>
				<p> Yours sincerly, </p>
                <p> The <span class=\"boldLetters\">{$casino_name}</span> support team</p>";
        }
		$message = self::getMailBody($language_settings, $mail_message, $site_images_location, $mail_title, $background_color, $text_color, $small_text_color, $footer_background,
				$text_font_size, $small_text_font_size,
				$hyperlink_text_color, $hyperlink_font_size,
				$hyperlink_small_text_color, $hyperlink_small_font_size);
		return array("mail_title"=>$mail_title, "mail_message"=>$message);
	}

	//message when purchase credit transfer has failed through apco - to be sent to player
    //8.  Declined APCO deposit - for whatever reason
	public static function getPurchaseFailedContent($transaction_amount, $transaction_fee, $transaction_currency, $transaction_id, $payment_method, $player_username,
	$site_images_location, $casino_name, $site_link, $contact_link, $support_link, $terms_link, $language_settings = 'en_GB',
	$background_color = "#FFFFFF", $text_color = "#000000", $small_text_color = "#000000", $footer_background = "#FFFFFF",
	$text_font_size = "15", $small_text_font_size = "8",
	$hyperlink_text_color = "#FFFFFF", $hyperlink_font_size = "15",
	$hyperlink_small_text_color = "#FFFFFF", $hyperlink_small_font_size = "8"){
        $date_processed = date('d-m-Y H:i:s');
        $transaction_amount_formatted = NumberHelper::format_double($transaction_amount);
        $transaction_fee_formatted = NumberHelper::format_double($transaction_fee);
        $payment_method = self::translatePaymentMethod($payment_method);
        switch ($language_settings){
            case 'de_DE':
                //$feePresentText = (isset($transaction_fee) && $transaction_fee > 0) ? "<p> Transaktionsspesen:   <span class=\'boldLetters\'>{$transaction_fee_formatted}</span> </p>" : "";
                $feePresentText = "<p> Transaktionsspesen:   <span class=\"boldLetters\">{$transaction_fee_formatted}</span> </p>";
                $mail_title = "Ihre Einzahlung auf Ihr {$casino_name}-Konto wurde abgelehnt";
                $mail_message =
                "<p> Liebe(r) <span class=\"boldLetters\">{$player_username}</span>, </p>
                <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Ihre Einzahlung wurde vom unsere Zahlungen durchführenden Prozessor (Payment provider) abgelehnt. </p>
                    <p> Betrag:          <span class=\"boldLetters\">{$transaction_amount_formatted}</span> </p>
                 "
                    . $feePresentText .
                 "
                    <p> Währung:         <span class=\"boldLetters\">{$transaction_currency}</span> </p>
                    <p> TransaktionsID:  <span class=\"boldLetters\">{$transaction_id}</span> </p>
                    <p> Datum/Uhrzeit:   <span class=\"boldLetters\">{$date_processed}</span> </p>
                    <p> Zahlungsmethode: <span class=\"boldLetters\">{$payment_method}</span> </p>
                    <p> Wenn eine Einzahlung abgelehnt wird, bekommen wir in den meisten Fällen keine Begründung von </p>
                    <p> Ihrem Kreditkarten-Provider oder E-Wallet-Provider mitgeteilt. Bitte überprüfen Sie die </p>
                    <p> nachfolgenden Schritte, um sicherzustellen, daß Ihre Einzahlung ordnungsgemäß durchgeführt wird:</p>
                    <p>1. Wurden alle Kreditkarten-Details korrekt eingetippt?</p>
                    <p>2. Haben Sie den richtigen CV2-Code eingetippt (auf der Rückseite Ihrer Karte)?</p>
                    <p>3. Versichern Sie sich, daß die Gültigkeit Ihrer Karte nicht abgelaufen ist.</p>
                    <p>4. Verwenden Sie Ihre Karte das erste Mal bei uns? Manchmal verhindern Banken die automatische</p>
                    <p>Verwendung einer Kreditkarte bei einem neuen Online-Händler. Ein kurzer Anruf bei Ihrer</p>
  	                <p>Bank kann dieses Problem lösen.</p>
                    <p>5. Manchmal erlauben Kreditkartenunternehmen die Verwendung von Karten bei Onlinecasinos</p>
                    <p>nicht. Bitte überprüfen Sie die Webseite Ihres Providers oder kontaktieren Sie ihn, um</p>
                    <p>dieses Problem abzuklären.</p>
                    <p>6. Bitte überprüfen Sie, ob genug Deckung für Ihre Einzahlung verfügbar ist.</p>
                    <p>7. Abgelehnete E-Wallet-Einzahlungen können eine vielzahl von Gründen haben. Aus</p>
                    <p>Datenschutzgründen bekommen wir keine Erklärung. Bitte kontaktieren Sie Ihren</p>
                    <p>E-Wallet-Provider, um genaue Informationen zu erhalten.</p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p>Sollten Sie weitere Fragen haben oder sonstige Unterstützung benötigen, bitten wir Sie, sich an unser <a class=\"underlineLetters\" href=\"{$support_link}\">Kundendienst-Service-Team</a> zu wenden.</p>
                    <p>Unsere <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Allgemeinen Geschäftsbedingungen\"</a> finden Sie auf unserer Webseite.</p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                 <p> WIR WÜNSCHEN VIEL GLÜCK UND GUTE UNTERHALTUNG! </p>
                 <p> Mit freundlichen Grüßen, </p>
                 <p> Ihr <span class=\"boldLetters\">{$casino_name}</span> Team</p>";
                break;
            case 'cs_CZ':
                //$feePresentText = (isset($transaction_fee) && $transaction_fee > 0) ? "<p> Poplatky za transakci:   <span class=\"boldLetters\">{$transaction_fee_formatted}</span> </p>" : "";
                $feePresentText = "<p> Poplatky za transakci:   <span class=\"boldLetters\">{$transaction_fee_formatted}</span> </p>";
                $mail_title = "Vklad na Váš {$casino_name} uživatelský účet byl zamítnut";
                $mail_message =
                "<p> Vážený/á <span class=\"boldLetters\">{$player_username}</span>, </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Váš vklad byl zamítnut naším procesorem (Payment provider): </p>
                 "
                    . $feePresentText .
                 "
                    <p> Měna:           <span class=\"boldLetters\">{$transaction_currency}</span> </p>
                    <p> Transakce ID:   <span class=\"boldLetters\">{$transaction_id}</span> </p>
                    <p> Datum/Čas:      <span class=\"boldLetters\">{$date_processed}</span> </p>
                    <p> Způsob platby:  <span class=\"boldLetters\">{$payment_method}</span> </p>
                    <p> Je-li vklad odmítnut, ve většině případů nejsme informování poskytovatelem </p>
                    <p> Vaší kreditní karty nebo e - peněženka z jakého důvodu byla platba zamítnuta. </p>
                    <p> Zkontrolujte prosím následující kroky k zajištění, že váš vklad byl správně proveden:</p>
                    <p>1. Zadali jste všechny údaje k Vaši kreditní kartě správně?</p>
                    <p>2. Zadali jste správný kód CV2 (na zadní straně karty)?</p>
                    <p>3. Ujistěte se , že platnost Vaši kreditni karty nevypršela.</p>
                    <p>4. Použili jste Vaší kartu u nás poprvé?Některé banky nepovolují platbu u onlne</p>
                    <p>obchodníků.Prosím ověřte u své banky.</p>
                    <p>5. Některé  společnosti vydávající kreditní karty neumožňují používání karet v </p>
                    <p>on-line kasinech Zkontrolujte prosím webové stránky svého poskytovatele nebo jej kontaktujte k vyjasnění</p>
                    <p>6. Prosím ověřte si zda máte dostatečné limit na Vaší kartě.</p>
                    <p>7. Odmítnuté E-Wallet-vklady můžou mít vícero důvodů.Z důvodů ochrany dat nedostaneme žadné vysvětlení.</p>
                    <p> Prosím kontaktujte Vašeho E-Wallet-poskytovatele pro obdržení potřebných informací.</p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p>Máte-li jakékoliv další dotazy nebo potřebujete jakoukoliv další pomoc, prosím, kontaktujte naši <a class=\"underlineLetters\" href=\"{$support_link}\">\"Zákaznickou podporu-služby zákazníkům\"</a>.</p>
                    <p>Naše <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Všeobecné podmínky\"</a> naleznete na našich webových stránkách.</p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                 <p> PŘEJEME HODNĚ ŠTĚSTÍ A PŘÍJEMNOU ZÁBAVU! </p>
                 <p> S přátelským pozdravem, </p>
                 <p> Váš <span class=\"boldLetters\">{$casino_name}</span> tým. </p>";
                break;
            case 'sv_SE':
               $feePresentText = "<p> Transaction fee:   <span class=\"boldLetters\">{$transaction_fee_formatted}</span> </p>";
               $mail_title = "Din betalning till {$casino_name} casinot medgavs inte";
               $mail_message =
                "<p> Kära <span class=\"boldLetters\">{$player_username}</span>, </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Din betalning blev nekad av våran betalningsleverantör. </p>
                    <p> Belopp:          <span class=\"boldLetters\">{$transaction_amount}</span> </p>
                    <p> Valuta:          <span class=\"boldLetters\">{$transaction_currency}</span> </p>
                    <p> Transaction ID:  <span class=\"boldLetters\">{$transaction_id}</span> </p>
                    <p> Datum/Tid:       <span class=\"boldLetters\">{$date_processed}</span> </p>
                    <p> Betalningssätt:  <span class=\"boldLetters\">{$payment_method}</span> </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> När en insättning avvisas, tyvärr så är vi inte försädda med en anledning från ditt kort- eller </p>
                    <p> e-plånboks utfärdare. Kontrollera några av följande åtgärder för att säkerställa att din betalning kommer att </p>
                    <p> behandlas på rätt sätt: </p>
                    <p>1. är alla kortuppgifter ifyllda korrekt </p>
                    <p>2. skrev du rätt CV2 kod </p>
                    <p>3. se till att utgångsdatum för kortet inte har löpt ut </p>
                    <p>4. är det första gången du använder kortet hos oss – ibland vill banken förhindra användning av </p>
                    <p> ett kort med nya online-återförsäljare. Kontakta din bank så hjälper dom dig att lösa detta. </p>
                    <p>5. vissa kortutfärdare tillåter inte insättningar till online-spels operatörer. Vänligen besök </p>
                    <p> din banks webbplats eller kontakta dem för att lösa detta problem. </p>
                    <p>6. finns det tillräckligt med medel på kontot? </p>
                    <p>7. nekad E-Plånboks insättning kan ha ett flertal anledningar; på grund av datasäkerheten </p>
                    <p> så blir vi inte alltid informerad av vilken anledning till att din insättning misslyckades. </p>
                    <p> Vänligen kontakta din e-plånboks leverantör för mer information. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Om du behöver ytterligare hjälp, kontakta vår kundtjänst.För våra Regler och Villkor vänligen läs, \"Regler </p>
                    <p> och Villkor\". </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                 <p> LYCKA TILL! </p>
                 <p> Vänligen, </p>
                 <p> <span class=\"boldLetters\">{$casino_name}</span> supporten</p>";
                break;
            case 'rs_RS':
               //$feePresentText = (isset($transaction_fee) && $transaction_fee > 0) ? "<p> Transaction fee:   <span class=\"boldLetters\">{$transaction_fee_formatted}</span> </p>" : "";
               $feePresentText = "<p> Administrativna taksa:   <span class=\"boldLetters\">{$transaction_fee_formatted}</span> </p>";
               $mail_title = "Vaša uplata u {$casino_name} kazino je odbijena";
               $mail_message =
                "<p> Poštovani/a <span class=\"boldLetters\">{$player_username}</span>, </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Vaša uplata je odbijena kod našeg procesora plaćanja. </p>
                    <p> Iznos:          <span class=\"boldLetters\">{$transaction_amount}</span> </p>
                "
                   . $feePresentText .
                "
                    <p> Valuta:             <span class=\"boldLetters\">{$transaction_currency}</span> </p>
                    <p> Transkcijski ID:    <span class=\"boldLetters\">{$transaction_id}</span> </p>
                    <p> Datum/Vreme:        <span class=\"boldLetters\">{$date_processed}</span> </p>
                    <p> Metod plaćanja:     <span class=\"boldLetters\">{$payment_method}</span> </p>
                    <p> Kada je uplata odbijena, nažalost nama nije prosleđen razlog od izdavaoca Vaše kartice. </p>
                    <p> Molimo Vas da proverite neke od sledećih koraka da bi ste se osigurali da će vaša uplata biti procesirana sa uspehom: </p>
                    <p>1. da li su svi detalji kartice uneti ispravno</p>
                    <p>2. da li ste uneli ispravan CV2 kod</p>
                    <p>3. da li je datum isteka kartice ispravan</p>
                    <p>4. da li ste već koristili karticu sa našim sistemom - ponekada banke sprečavaju korišćenje kartice</p>
                    <p> sa novim online sistemom. Poziv ka Vašoj banci će razrešiti vaše probleme.</p>
                    <p>5. ponekada izdavaoci kreditnih kartica ne dozvoljavaju uplate operatorima igara na sreću. Molimo Vas da proverite</p>
                    <p> detaljno na web sajtu Vaše banke ili kontaktirajte Vašu banku da biste razrešili ovaj problem.</p>
                    <p>6. da li imate dovoljno sredstava na vašem računu?</p>
                    <p>7. odbijena uplata moze imati više uzroka; usled zaštite podataka </p>
                    <p> mi nemamo tačno objašnjenje, zašto Vaša uplata nije uspela. Molimo Vas da kontaktirate </p>
                    <p> provajdera vašeg sredstva plaćanja za detaljne informacije.</p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p>Ukoliko Vaš je potrebna dalja pomoć, molimo Vas da kontaktirate naš <a class=\"underlineLetters\" href=\"{$support_link}\">tim korisničke podrške</a>.</p>
                    <p>Za naše Uslove korišćenja molimo Vas da posetite naš web sajt i kliknete na link <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Uslovi korišćenja\"</a>.</p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> SREĆNO! </p>
                    <p> Iskreno Vaš, </p>
                    <p> <span class=\"boldLetters\">{$casino_name}</span> tim podrške</p>";
                break;
            case 'en_GB':
            default:
               //$feePresentText = (isset($transaction_fee) && $transaction_fee > 0) ? "<p> Transaction fee:   <span class=\"boldLetters\">{$transaction_fee_formatted}</span> </p>" : "";
               $feePresentText = "<p> Transaction fee:   <span class=\"boldLetters\">{$transaction_fee_formatted}</span> </p>";
               $mail_title = "Your payment to {$casino_name} casino was declined";
               $mail_message =
                "<p> Dear <span class=\"boldLetters\">{$player_username}</span>, </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Your payment was declined by our payment processor. </p>
                    <p> Amount:          <span class=\"boldLetters\">{$transaction_amount}</span> </p>
                "
                   . $feePresentText .
                "
                    <p> Currency:        <span class=\"boldLetters\">{$transaction_currency}</span> </p>
                    <p> Transaction ID:  <span class=\"boldLetters\">{$transaction_id}</span> </p>
                    <p> Date/Time:       <span class=\"boldLetters\">{$date_processed}</span> </p>
                    <p> Payment method:  <span class=\"boldLetters\">{$payment_method}</span> </p>
                    <p> When a deposit is declined, unfortunately we are not provided with a reason from your card or </p>
                    <p> e-wallet issuer. Please check some of the following steps to ensure your payment will be </p>
                    <p> processed correctly:</p>
                    <p>1. are all card details typed correctly</p>
                    <p>2. did you type the correct CV2 code</p>
                    <p>3. ensure that the expiry date of your card has not elapsed</p>
                    <p>4. are you using the card first time with us - sometimes banks prevent using a card</p>
                    <p>with a new online retailer. A call to your bank can resolve this issue.</p>
                    <p>5. sometimes Credit Card issuers don't permit deposits into gaming operators. Please</p>
                    <p>check your bank's website or contact them to clear up this issue.</p>
                    <p>6. are enough funds available?</p>
                    <p>7. declined E-Wallet deposits can have a number of reasons; due to data protection</p>
                    <p>we aren't given any explanation, why your deposit failed. Please contact your</p>
                    <p>e-wallet provider for further information.</p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p>If you need further assistance, please contact our <a class=\"underlineLetters\" href=\"{$support_link}\">customer support team</a>.</p>
                    <p>For our terms and conditions please visit on our website <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Terms and Conditions\"</a>.</p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                 <p> GOOD LUCK! </p>
                 <p> Yours sincerly, </p>
                 <p> The <span class=\"boldLetters\">{$casino_name}</span> support team</p>";
        }
		$message = self::getMailBody($language_settings, $mail_message, $site_images_location, $mail_title, $background_color, $text_color, $small_text_color, $footer_background,
				$text_font_size, $small_text_font_size,
				$hyperlink_text_color, $hyperlink_font_size,
				$hyperlink_small_text_color, $hyperlink_small_font_size);
		return array("mail_title"=>$mail_title, "mail_message"=>$message);
	}

    //player's payout has been successful - IZMENA DODAO AMOUNT I CURRENCY
    //9. Payout request  - confirmation with TransactionID - after player ask payout and money is deducted
    //   from account balance and waiting on pending payout (still cancelable by player) confirmation of support and APCO
	public static function getPayoutSuccessContent($player_username, $transaction_id, $currency, $transaction_amount, $transaction_fee, $payment_method,
	$site_images_location, $casino_name, $site_link, $contact_link, $support_link, $terms_link, $language_settings = 'en_GB',
	$background_color = "#FFFFFF", $text_color = "#000000", $small_text_color = "#000000", $footer_background = "#FFFFFF",
	$text_font_size = "15", $small_text_font_size = "8",
	$hyperlink_text_color = "#FFFFFF", $hyperlink_font_size = "15",
	$hyperlink_small_text_color = "#FFFFFF", $hyperlink_small_font_size = "8"){
		$date_processed = date('d-m-Y H:i:s');
		$transaction_amount = NumberHelper::format_double($transaction_amount);
        $transaction_fee = NumberHelper::format_double($transaction_fee);
        $payment_method = self::translatePaymentMethod($payment_method);
        switch ($language_settings){
            case 'de_DE':
                $mail_title = "Ihre Auszahlungsanfrage (TransaktionsID {$transaction_id}) Betrag: {$currency} {$transaction_amount}";
                $mail_message =
                "<p> Liebe(r) <span class=\"boldLetters\">{$player_username}</span>, </p>
                 <p> Wir haben Ihre Auszahlungsanfrage erhalten. </p>
                 <p> Betrag:               <span class=\"boldLetters\">{$transaction_amount}</span> </p>
                 <p> Transaktionsspesen:   <span class=\"boldLetters\">{$transaction_fee}</span> </p>
                 <p> Währung:              <span class=\"boldLetters\">{$currency}</span> </p>
                 <p> TransaktionsID:       <span class=\"boldLetters\">{$transaction_id}</span> </p>
                 <p> Datum/Uhrzeit:        <span class=\"boldLetters\">{$date_processed}</span> </p>
                 <p> Zahlungsmethode:      <span class=\"boldLetters\">{$payment_method}</span> </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Wir informieren Sie sofort nach Erhalt einer Bestätigung durch den unsere Zahlungen durchführenden Prozessor (Payment provider). </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Wenn Sie sonstige Hilfe benötigen, unterstützen wir Sie gerne: <a class=\"underlineLetters\" href=\"{$support_link}\">Kundendienst-Team</a>. </p>
                 <p> Unsere <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Allgemeinen Geschäftsbedingungen\"</a> finden Sie auf unserer Webseite. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> WIR WÜNSCHEN VIEL GLÜCK UND GUTE UNTERHALTUNG! </p>
                 <p> Mit freundlichen Grüßen, </p>
                 <p> Ihr <span class=\"boldLetters\">{$casino_name}</span> Team </p>";
                break;
            case 'cs_CZ':
                $mail_title = "Téma: Vaše žádost o výplatě (TransactionsID {$transaction_id}) Částka: {$currency} {$transaction_amount}";
                $mail_message =
                "<p> Vážený/á <span class=\"boldLetters\">{$player_username}</span>, </p>
                 <p> Obdrželi jsme vaši žádost o výplatě. </p>
                 <p> Částka:                  <span class=\"boldLetters\">{$transaction_amount}</span> </p>
                 <p> Poplatky za transakci:   <span class=\"boldLetters\">{$transaction_fee}</span> </p>
                 <p> Měna:                    <span class=\"boldLetters\">{$currency}</span> </p>
                 <p> Transakce ID:            <span class=\"boldLetters\">{$transaction_id}</span> </p>
                 <p> Datum/Čas:               <span class=\"boldLetters\">{$date_processed}</span> </p>
                 <p> Způsob platby:           <span class=\"boldLetters\">{$payment_method}</span> </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Budeme Vás neprodleně informovat po obdržení potvrzení našich plateb vedoucím procesorem ( poskytovatel platebních transakcí ). </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Máte-li jakékoliv další dotazy nebo potřebujete jakoukoliv další pomoc, prosím, kontaktujte naši <a class=\"underlineLetters\" href=\"{$support_link}\">\"Zákaznickou podporu-služby zákazníkům\"</a>. </p>
                 <p> Naše <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Všeobecné podmínky\"</a> naleznete na našich webových stránkách. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> PŘEJEME HODNĚ ŠTĚSTÍ A PŘÍJEMNOU ZÁBAVU! </p>
                 <p> S přátelským pozdravem, </p>
                 <p> Váš <span class=\"boldLetters\">{$casino_name}</span> tým. </p>";
                break;
            case 'sv_SE':
                $mail_title = "Utbetalnings begäran (Transaction ID {$transaction_id}) Summa: {$currency} {$transaction_amount}";
                $mail_message =
                "<p> Kära <span class=\"boldLetters\">{$player_username}</span>, </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Vi har mottagit din utbetalnings begäran. </p>
                 <p> Belopp:                <span class=\"boldLetters\">{$transaction_amount}</span> </p>
                 <p> Transaktionsavgift:    <span class=\"boldLetters\">{$transaction_fee}</span> </p>
                 <p> Valuta:                <span class=\"boldLetters\">{$currency}</span> </p>
                 <p> Transaktions ID:       <span class=\"boldLetters\">{$transaction_id}</span> </p>
                 <p> Datum/Tid:             <span class=\"boldLetters\">{$date_processed}</span> </p>
                 <p> Betalningssätt:        <span class=\"boldLetters\">{$payment_method}</span> </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Vi kommer att informera dig omedelbart när vi fått en bekräftelse av våran betalningsleverantör. </p>
                 <p> Om du har några frågor, tveka inte att kontakta vår kundsupport för hjälp. </p>
                 <p> För våra Regler och Villkor vänligen läs, <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Regler och Villkor\"</a>. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> LYCKA TILL! </p>
                 <p> Vänligen, </p>
                 <p> <span class=\"boldLetters\">{$casino_name}</span> supporten </p>";
                break;
            case 'rs_RS':
                $mail_title = "Vaš zahtev za isplatu (Transakcijski ID {$transaction_id}) Iznos: {$currency} {$transaction_amount}";
                $mail_message =
                "<p> Poštovani/a <span class=\"boldLetters\">{$player_username}</span>, </p>
                 <p> Mi smo primili Vaš zahtev za isplatu. </p>
                 <p> Iznos:            <span class=\"boldLetters\">{$transaction_amount}</span> </p>
                 <p> Administrativna taksa:   <span class=\"boldLetters\">{$transaction_fee}</span> </p>
                 <p> Valuta:          <span class=\"boldLetters\">{$currency}</span> </p>
                 <p> Transakcijski ID:    <span class=\"boldLetters\">{$transaction_id}</span> </p>
                 <p> Datum/Vreme:         <span class=\"boldLetters\">{$date_processed}</span> </p>
                 <p> Metod plaćanja:    <span class=\"boldLetters\">{$payment_method}</span> </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Bićete obavešteni u što najkraćem roku, kada dobijemo potvrdu od našeg provajdera plaćanja. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Ukoliko Vam je potrebna dalja pomoć, biće nam drago da Vam pomognemo: <a class=\"underlineLetters\" href=\"{$support_link}\"> korisnička podrška</a>. </p>
                 <p> Za naše Uslove korišćenja molimo Vas da posetite na našem web sajtu i kliknete na link <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Uslovi korišćenja\"</a>. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> SREĆNO! </p>
                 <p> Iskreno Vaš, </p>
                 <p> <span class=\"boldLetters\">{$casino_name}</span> tim podrške </p>";
                break;
            case 'en_GB':
            default:
                $mail_title = "Your payout request (Transaction ID {$transaction_id}) Amount: {$currency} {$transaction_amount}";
                $mail_message =
                "<p> Dear <span class=\"boldLetters\">{$player_username}</span>, </p>
                 <p> We received your payout request. </p>
                 <p> Amount:            <span class=\"boldLetters\">{$transaction_amount}</span> </p>
                 <p> Transaction fee:   <span class=\"boldLetters\">{$transaction_fee}</span> </p>
                 <p> Currency:          <span class=\"boldLetters\">{$currency}</span> </p>
                 <p> Transaction ID:    <span class=\"boldLetters\">{$transaction_id}</span> </p>
                 <p> Date/Time:         <span class=\"boldLetters\">{$date_processed}</span> </p>
                 <p> Payment method:    <span class=\"boldLetters\">{$payment_method}</span> </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> We will inform you immediately, when a confirmation from our payment provider has arrived. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> If you need further help, we are glad to assist you: <a class=\"underlineLetters\" href=\"{$support_link}\">Customer support</a>. </p>
                 <p> For our terms and conditions please visit on our website <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Terms and Conditions\"</a>. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> GOOD LUCK! </p>
                 <p> Yours sincerly, </p>
                 <p> The <span class=\"boldLetters\">{$casino_name}</span> support team </p>";
        }
		$message = self::getMailBody($language_settings, $mail_message, $site_images_location, $mail_title, $background_color, $text_color, $small_text_color, $footer_background,
				$text_font_size, $small_text_font_size,
				$hyperlink_text_color, $hyperlink_font_size,
				$hyperlink_small_text_color, $hyperlink_small_font_size);
		return array("mail_title"=>$mail_title, "mail_message"=>$message);
	}

    //player receives mail that he has limit to deposit because not KYC (SiteMerchantManager::getTransactionLimitPurchase)
    //10. KYC request E-mail - automated - if 1st payout - if deposit with CC by new player more than 500 EUR
	public static function getDepositLimitPurchaseFailedContent($player_username, $amount, $currency, $player_limit,
	$site_images_location, $casino_name, $site_link, $contact_link, $support_link, $terms_link, $privacy_policy_link, $language_settings = 'en_GB',
	$background_color = "#FFFFFF", $text_color = "#000000", $small_text_color = "#000000", $footer_background = "#FFFFFF",
	$text_font_size = "15", $small_text_font_size = "8",
	$hyperlink_text_color = "#FFFFFF", $hyperlink_font_size = "15",
	$hyperlink_small_text_color = "#FFFFFF", $hyperlink_small_font_size = "8"){
        $amount = NumberHelper::format_double($amount);
        $player_limit = NumberHelper::format_double($player_limit);
        switch ($language_settings){
            case 'de_DE':
                $mail_title = "Ihre Auszahlung - Bitte geben Sie uns ein wenig mehr Information über Sich";
                $mail_message =
                   "<p> Liebe(r) <span class=\"boldLetters\">{$player_username}</span>, </p>
                    <p> Aufgrund unserer LIZENZBESTIMMUNGEN und der europäischen und INTERNATIONALEN GELDWÄSCHE </p>
                    <p> RICHTLINIEN sind wir verpflichtet, unsere Kunden zu kennen, bevor wir eine Auszahlung durchführen dürfen. </p>
                    <p> Für Einzahlungen benötigen wir diese Informationen, um der mißbräuchlichen Verwendung von Kreditkarten vorzubeugen. </p>
                    <p> Wenn Sie eine Kreditkarte oder Zahlkarte für Einzahlungen verwendet haben, und der Betrag {$currency} {$player_limit} (€500,00) übersteigt, </p>
                    <p> benötigen wir auch eine Kopie dieser Kreditkarte. Wenn Sie Dokumente </p>
                    <p> hochladen, verdecken Sie bitte die mittleren 8 Ziffern auf der Vorderseite Ihrer Kreditkarte, </p>
                    <p> sowie den CV2 Code auf der Rückseite. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> <span class=\"boldLetters\"> Bitte gehen Sie zu \"Mein Konto\" und wählen Sie den Tab \"Dokumente hochladen\" um die KENNE DEINEN KUNDEN Prozedur durhzuführen. </span> </p>
                    <p> Bitte laden Sie einen Scan oder ein Photo folgender Dokumente hoch: </p>
                    <p> <span class=\"boldLetters\"> - Gültiger Lichtbildausweis </span> </p>
                    <p> <span class=\"boldLetters\"> - Amtliche Meldebestätigung oder eine Strom- Gas- oder Wasserrechnung, nicht älter als 3 Monate </span> </p>
                    <p> <span class=\"boldLetters\"> - Kreditkarte - Vorder- und Rückseite </span> (NUR wenn Sie per Karte Einzahlungen geleistet haben) </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Selbstverständlich halten wir Ihre Daten strengstens vertraulich. Bitte informieren Sie auf unserer Webseite über unsere <a class=\"underlineLetters\" href=\"{$privacy_policy_link}\">Datenschutzrichtlinien</a>. </p>
                    <p> Wenn Sie sonstige Hilfe benötigen, unterstützen wir Sie gerne: <a class=\"underlineLetters\" href=\"{$support_link}\">Kundendienst-Team</a> </p>
                    <p> Unsere <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Allgemeinen Geschäftsbedingungen\"</a> finden Sie auf unserer Webseite. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> WIR WÜNSCHEN VIEL GLÜCK UND GUTE UNTERHALTUNG! </p>
                    <p> Mit freundlichen Grüßen, </p>
                    <p> Ihr <span class=\"boldLetters\">{$casino_name}</span> Team </p>";
                break;
            case 'cs_CZ':
                $mail_title = "Váš výběr – Prosím poskytněte nám více informací k Vaši osobě";
                $mail_message =
                   "<p> Vážený/á <span class=\"boldLetters\">{$player_username}</span>, </p>
                    <p> Vzhledem k naším licenčním podmínkám a evropských a mezinárodních směrnic o </p>
                    <p> praní špinavých peněz jsme povinni znát naše zákazníky, než budeme moci provést výplatu. </p>
                    <p> Pro vklad , potřebujeme tyto podklady , aby jsme zabránili zneužití.Při platbě </p>
                    <p> kreditní nebo platební kartou kde částka přesáhne {$currency} {$player_limit} (€500,00) </p>
                    <p> je zapotřebí nám zaslat kopii karty u které prosíme z přední strany o překrytí prostředních 8 čísel a CV2 kódu na zadní straně.</p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> <span class=\"boldLetters\"> Prosím, jděte na \"Můj účet\" a vyberte políčko \"Nahrát dokumenty\" do poznej svého zákazníka. </span> </p>
                    <p> <span class=\"boldLetters\"> Nahrajte prosím scan nebo fotografii těchto dokumentů:</span> </p>
                    <p> <span class=\"boldLetters\"> - Platný občanský průkaz nebo Pas </span> </p>
                    <p> <span class=\"boldLetters\"> - Potvrzení o trvalém bydlišti nebo úcet za elektřinu,vodu, plynu s vaším jménem a adresou trvalého bydliště ne starší 3 měsíců </span> </p>
                    <p> <span class=\"boldLetters\"> - Kreditní kartu přední a zadní stranu (jen pokud jste k vkladu použili kreditní kartu) </span> </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Samozřejmě budeme držet Vaše data přísně důvěrně. Prosím informujte se na našich webových stránkách o <a class=\"underlineLetters\" href=\"{$privacy_policy_link}\">zásadách ochrany osobních údajů</a>. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Máte-li jakékoliv další dotazy nebo potřebujete jakoukoliv další pomoc, prosím, kontaktujte naši <a class=\"underlineLetters\" href=\"{$support_link}\">\"Zákaznickou podporu-služby zákazníkům\"</a> </p>
                    <p> Naše <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Všeobecné podmínky\"</a> naleznete na našich webových stránkách. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> PŘEJEME HODNĚ ŠTĚSTÍ A PŘÍJEMNOU ZÁBAVU! </p>
                    <p> S přátelským pozdravem, </p>
                    <p> Váš <span class=\"boldLetters\">{$casino_name}</span> tým. </p>";
                break;
            case 'sv_SE':
                $mail_title = "Din Insättning/Uttag – Vänligen låt oss få lite mer information om dig";
                $mail_message =
                   "<p> Kära <span class=\"boldLetters\">{$player_username}</span>, </p>
                    <p> På grund av våra LICENSVILLKOR och den Europeiska och INTERNATIONELLA PENINGTVÄTTS LAGEN, är vi </p>
                    <p> tvingad att känna till våra kunder innan vi slutför en utbetalning. </p>
                    <p> För insättningar behöver vi denna information för att förhindra MISSBRUK AV KREDITKORT. Om du har </p>
                    <p> använt ett kreditkort eller betalkort för insättning, överstigande {$currency} {$player_limit} behöver vi också en kopia på </p>
                    <p> kreditkortet eller betalkortet som användes. När du laddar upp ett dokument, vänligen täck över. </p>
                    <p> de 8 siffrorna I mitten på kortet, även CV2 koden på baksidan på kortet. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> <span class=\"boldLetters\"> Vänligen gå till ditt konto och välj fliken “ladda upp document” för </span> att genomföra Know Your </p>
                    <p> Customer förfarandet. <span class=\"boldLetters\">Så vi behöver en kopia på:</span> </p>
                    <p> <span class=\"boldLetters\"> - giltig fotolegitimation (Pass, Id-kort eller körkort) och en kopia på </p>
                    <p> <span class=\"boldLetters\"> - var du är folkbokförd eller en räkning inte äldre än 3 månader </span> och en kopipa på </p>
                    <p> <span class=\"boldLetters\"> - kreditkortets framsida och baksida </span> (ENDAST om du gjort en insättning) </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Det är inte nödvändigt att nämna, att vi behandlar mottagna data strikt konfidentiellt.  </p>
                    <p> Se vår sekretesspolicy längst ner på webbplatsen. </p>
                    <p> Om du har några frågor, tveka inte att kontakta vår kundsupport för hjälp. </p>
                    <p> För våra Regler och Villkor vänligen läs, \"Regler och Villkor\". </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Lycka Till! </p>
                    <p> Vänligen, </p>
                    <p> <span class=\"boldLetters\">{$casino_name}</span> supporten </p>";
                break;
            case 'rs_RS':
                $mail_title = "Vaša uplata - molimo Vas da nam pružite više Vaših informacija";
                $mail_message =
                   "<p> Poštovani/a <span class=\"boldLetters\">{$player_username}</span>, </p>
                    <p> Usled naših USLOVA LICENCIRANJA i Evropskih i MEĐUNARODNIH OKVIRA PROTIV PRANJA NOVCA, mi smo </p>
                    <p> dužni da budemo upoznati sa našim korisnicima pre završavanja procesa uplate. </p>
                    <p> Za uplate potrebna nam je ova informacija da bismo sprečili ZLOUPOTREBU KREDITNIH KARTICA. U sličaju da ste Vi </p>
                    <p> iskoristili kreditnu karticu ili karticu plaćanja za uplate, koje prelaze {$currency} {$player_limit} (€500), </p>
                    <p> nama je potrebna kopija kreditne kartice ili kartice plaćanja koja je korišćena. </p>
                    <p> Prilikom postavljanja dokumenata, molimo Vas da sakrijete srednjih 8 cifara na prednjoj strani </p>
                    <p> Vaše kreditne kartice, kao i CV2 kod na zadnjoj strani Vaše kreditne kartice. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> <span class=\"boldLetters\"> Molimo Vas da posetite \"Moj nalog\" i odaberete tab \"Postavljeni dokumenti\" da biste izvršili proceduru prosleđivanja Vaših informacija. </span> </p>
                    <p> Potrebno je da postsavite fotografiju ili skeniranu: </p>
                    <p> <span class=\"boldLetters\"> - validnu fotografiju ID dokumenta (pasoš ili lična karta) </span> kao i kopiju Vašeg </p>
                    <p> <span class=\"boldLetters\"> - prijavljenog prebivališta ili račune ne starije od 3 meseca </span> kao i kopiju Vaše </p>
                    <p> <span class=\"boldLetters\"> - kreditne kartice sa prednjom i zadnjom stranom </span> (SAMO ako ste koristili za uplate) </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Nije potrebno napominjati, da mi tretiramo poslate podatke sa strogom poverljivošću. Molimo Vas da pogledate <a class=\"underlineLetters\" href=\"{$privacy_policy_link}\">polisu privatnosti</a> na dnu </p>
                    <p> našeg web sajta. </p>
                    <p> Ukoliko Vam je potrebna dalja pomoć, biće nam drago da Vam pomognemo: <a class=\"underlineLetters\" href=\"{$support_link}\">Korisnička podrška</a> </p>
                    <p> Za naše Uslove korišćenja molimo Vas da posetite naš web sajt i kliknete na link <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Uslovi korišćenja\"</a> </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> SREĆNO! </p>
                    <p> Iskreno Vaš, </p>
                    <p> <span class=\"boldLetters\">{$casino_name}</span> tim podrške </p>";
                break;
            case 'en_GB':
            default:
                $mail_title = "Your Deposit - please let us have some more information about you";
                $mail_message =
                   "<p> Dear <span class=\"boldLetters\">{$player_username}</span>, </p>
                    <p> Because of our LICENCING TERMS and the European and INTERNATIONAL MONEY LAUNDERING GUIDELINES, we </p>
                    <p> are obliged to be familiar with our customers before completing a payout. </p>
                    <p> For deposits we need this information in order to prevent the MISUSE OF CREDIT CARDS. In case you </p>
                    <p> have used a credit card or payment card for deposits, superating {$currency} {$player_limit} (€500), </p>
                    <p> we also need a copy of the credit card or payment card used.</p>
                    <p> When uploading  documents, please cover the middle 8 digits on the front of </p>
                    <p> your credit card, as well as the CV2 code on the back of your credit card. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> <span class=\"boldLetters\"> Please go to \"My Account\" and select the tab \"Documents upload\" to carry out the Know Your Customer procedure. </span> </p>
                    <p> We need you to upload a photo or scan of a: </p>
                    <p> <span class=\"boldLetters\"> - valid photo ID (passport or ID card) </span> and a copy of your </p>
                    <p> <span class=\"boldLetters\"> - residence registration or a bill of an utility service provider not older than 3 month </span> and a copy of your </p>
                    <p> <span class=\"boldLetters\"> - credit card frontside and backside </span> (ONLY when used for deposits) </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> It is not necessary to mention, that we treat received data strictly confidential. Please see our <a class=\"underlineLetters\" href=\"{$privacy_policy_link}\">privacy policy</a> at the  </p>
                    <p> bottom of the website. </p>
                    <p> If you need further help, we are glad to assist you: <a class=\"underlineLetters\" href=\"{$support_link}\">Customer support</a> </p>
                    <p> For our terms and conditions please visit on our website <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Terms and Conditions\"</a> </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> GOOD LUCK! </p>
                    <p> Yours sincerly, </p>
                    <p> The <span class=\"boldLetters\">{$casino_name}</span> support team </p>";
        }
		$message = self::getMailBody($language_settings, $mail_message, $site_images_location, $mail_title, $background_color, $text_color, $small_text_color, $footer_background,
				$text_font_size, $small_text_font_size,
				$hyperlink_text_color, $hyperlink_font_size,
				$hyperlink_small_text_color, $hyperlink_small_font_size);
		return array("mail_title"=>$mail_title, "mail_message"=>$message);
	}


    //player receives mail to his payout limit because he is not KYC (SiteMerchantManager::getTransactionLimitPayout)
    //10. KYC request E-mail - automated - if 1st payout - if deposit with CC by new player more than 500 EUR
	public static function getPayoutLimitPayoutFailedContent($player_username, $amount, $currency, $player_limit,
	$site_images_location, $casino_name, $site_link, $contact_link, $support_link, $terms_link, $privacy_policy_link, $language_settings = 'en_GB',
	$background_color = "#FFFFFF", $text_color = "#000000", $small_text_color = "#000000", $footer_background = "#FFFFFF",
	$text_font_size = "15", $small_text_font_size = "8",
	$hyperlink_text_color = "#FFFFFF", $hyperlink_font_size = "15",
	$hyperlink_small_text_color = "#FFFFFF", $hyperlink_small_font_size = "8"){
        $amount = NumberHelper::format_double($amount);
        $player_limit = NumberHelper::format_double($player_limit);
        switch ($language_settings){
            case 'de_DE':
                $mail_title = "Ihre Einzahlung - Bitte geben Sie uns ein wenig mehr Information über Sich";
                $mail_message =
                   "<p> Liebe(r) <span class=\"boldLetters\">{$player_username}</span>, </p>
                    <p> Aufgrund unserer LIZENZBESTIMMUNGEN und der europäischen und INTERNATIONALEN GELDWÄSCHE </p>
                    <p> RICHTLINIEN sind wir verpflichtet, unsere Kunden zu kennen, bevor wir eine Auszahlung durchführen dürfen. </p>
                    <p> Für Einzahlungen benötigen wir diese Informationen, um der mißbräuchlichen Verwendung von Kreditkarten vorzubeugen. </p>
                    <p> Wenn Sie eine Kreditkarte oder Zahlkarte für Einzahlungen verwendet haben, und der Betrag {$currency} {$player_limit} (€500,00) übersteigt, </p>
                    <p> benötigen wir auch eine Kopie dieser Kreditkarte. Wenn Sie Dokumente </p>
                    <p> hochladen, verdecken Sie bitte die mittleren 8 Ziffern auf der Vorderseite Ihrer Kreditkarte, </p>
                    <p> sowie den CV2 Code auf der Rückseite. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> <span class=\"boldLetters\"> Bitte gehen Sie zu \"Mein Konto\" und wählen Sie den Tab \"Dokumente hochladen\" um die KENNE DEINEN KUNDEN Prozedur durhzuführen. </span> </p>
                    <p> Bitte laden Sie einen Scan oder ein Photo folgender Dokumente hoch: </p>
                    <p> <span class=\"boldLetters\"> - Gültiger Lichtbildausweis </span> </p>
                    <p> <span class=\"boldLetters\"> - Amtliche Meldebestätigung oder eine Strom- Gas- oder Wasserrechnung, nicht älter als 3 Monate </span> </p>
                    <p> <span class=\"boldLetters\"> - Kreditkarte - Vorder- und Rückseite  </span> (NUR wenn Sie per Karte Einzahlungen geleistet haben) </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Selbstverständlich halten wir Ihre Daten strengstens vertraulich. Bitte informieren Sie auf unserer Webseite über unsere <a class=\"underlineLetters\" href=\"{$privacy_policy_link}\">Datenschutzrichtlinien</a>. </p>
                    <p> Wenn Sie sonstige Hilfe benötigen, unterstützen wir Sie gerne: <a class=\"underlineLetters\" href=\"{$support_link}\">Kundendienst-Team</a> </p>
                    <p> Unsere <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Allgemeinen Geschäftsbedingungen\"</a> finden Sie auf unserer Webseite. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> WIR WÜNSCHEN VIEL GLÜCK UND GUTE UNTERHALTUNG! </p>
                    <p> Mit freundlichen Grüßen, </p>
                    <p> Ihr <span class=\"boldLetters\">{$casino_name}</span> Team </p>";
                break;
            case 'cs_CZ':
                $mail_title = "Váš vklad – Prosím poskytněte nám více informací k Vaši osobě";
                $mail_message =
                   "<p> Vážený/á <span class=\"boldLetters\">{$player_username}</span>, </p>
                    <p> Vzhledem k naším licenčním podmínkám a evropských a mezinárodních směrnic o </p>
                    <p> praní špinavých peněz jsme povinni znát naše zákazníky, než budeme moci provést výplatu. </p>
                    <p> Pro vklad , potřebujeme tyto podklady , aby jsme zabránili zneužití.Při platbě </p>
                    <p> kreditní nebo platební kartou kde částka přesáhne {$currency} {$player_limit} (€500,00) </p>
                    <p> je zapotřebí nám zaslat kopii karty u které prosíme z přední strany o překrytí prostředních 8 čísel a CV2 kódu na zadní straně.</p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> <span class=\"boldLetters\"> Prosím, jděte na \"Můj účet\" a vyberte políčko \"Nahrát dokumenty\" do poznej svého zákazníka. </span> </p>
                    <p> <span class=\"boldLetters\"> Nahrajte prosím scan nebo fotografii těchto dokumentů:</span> </p>
                    <p> <span class=\"boldLetters\"> - Platný občanský průkaz nebo Pas </span> </p>
                    <p> <span class=\"boldLetters\"> - Potvrzení o trvalém bydlišti nebo úcet za elektřinu,vodu, plynu s vaším jménem a adresou trvalého bydliště ne starší 3 měsíců </span> </p>
                    <p> <span class=\"boldLetters\"> - Kreditní kartu přední a zadní stranu (jen pokud jste k vkladu použili kreditní kartu) </span> </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Samozřejmě budeme držet Vaše data přísně důvěrně. Prosím informujte se na našich webových stránkách o <a class=\"underlineLetters\" href=\"{$privacy_policy_link}\">zásadách ochrany osobních údajů</a>. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Máte-li jakékoliv další dotazy nebo potřebujete jakoukoliv další pomoc, prosím, kontaktujte naši <a class=\"underlineLetters\" href=\"{$support_link}\">\"Zákaznickou podporu-služby zákazníkům\"</a> </p>
                    <p> Naše <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Všeobecné podmínky\"</a> naleznete na našich webových stránkách. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> PŘEJEME HODNĚ ŠTĚSTÍ A PŘÍJEMNOU ZÁBAVU! </p>
                    <p> S přátelským pozdravem, </p>
                    <p> Váš <span class=\"boldLetters\">{$casino_name}</span> tým. </p>";
                break;
            case 'sv_SE':
                $mail_title = "Din Insättning/Uttag – Vänligen låt oss få lite mer information om dig";
                $mail_message =
                   "<p> Kära <span class=\"boldLetters\">{$player_username}</span>, </p>
                    <p> På grund av våra LICENSVILLKOR och den Europeiska och INTERNATIONELLA PENINGTVÄTTS LAGEN, är vi </p>
                    <p> tvingad att känna till våra kunder innan vi slutför en utbetalning. </p>
                    <p> För insättningar behöver vi denna information för att förhindra MISSBRUK AV KREDITKORT. Om du har </p>
                    <p> använt ett kreditkort eller betalkort för insättning, överstigande {$currency} {$player_limit} behöver vi också en kopia på </p>
                    <p> kreditkortet eller betalkortet som användes. När du laddar upp ett dokument, vänligen täck över. </p>
                    <p> de 8 siffrorna I mitten på kortet, även CV2 koden på baksidan på kortet. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> <span class=\"boldLetters\"> Vänligen gå till ditt konto och välj fliken “ladda upp document” för </span> att genomföra Know Your </p>
                    <p> Customer förfarandet. <span class=\"boldLetters\">Så vi behöver en kopia på:</span> </p>
                    <p> <span class=\"boldLetters\"> - giltig fotolegitimation (Pass, Id-kort eller körkort) och en kopia på </p>
                    <p> <span class=\"boldLetters\"> - var du är folkbokförd eller en räkning inte äldre än 3 månader </span> och en kopipa på </p>
                    <p> <span class=\"boldLetters\"> - kreditkortets framsida och baksida </span> (ENDAST om du gjort en insättning) </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Det är inte nödvändigt att nämna, att vi behandlar mottagna data strikt konfidentiellt.  </p>
                    <p> Se vår sekretesspolicy längst ner på webbplatsen. </p>
                    <p> Om du har några frågor, tveka inte att kontakta vår kundsupport för hjälp. </p>
                    <p> För våra Regler och Villkor vänligen läs, \"Regler och Villkor\". </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Lycka Till! </p>
                    <p> Vänligen, </p>
                    <p> <span class=\"boldLetters\">{$casino_name}</span> supporten </p>";
                break;
            case 'rs_RS':
                $mail_title = "Vaša isplata - molimo Vas da nam pružite više Vaših informacija";
                $mail_message =
                   "<p> Poštovani/a <span class=\"boldLetters\">{$player_username}</span>, </p>
                    <p> Usled naših USLOVA LICENCIRANJA i Evropskih i MEĐUNARODNIH OKVIRA PROTIV PRANJA NOVCA, mi smo </p>
                    <p> dužni da budemo upoznati sa našim korisnicima pre završavanja procesa isplate. </p>
                    <p> Za iplate potrebna nam je ova informacija da bismo sprečili ZLOUPOTREBU KREDITNIH KARTICA. U sličaju da ste Vi </p>
                    <p> iskoristili kreditnu karticu ili karticu plaćanja za isplate, koje prelaze {$currency} {$player_limit} (€500), </p>
                    <p> nama je potrebna kopija kreditne kartice ili kartice plaćanja koja je korišćena. </p>
                    <p> Prilikom postavljanja dokumenata, molimo Vas da sakrijete srednjih 8 cifara na prednjoj strani </p>
                    <p> Vaše kreditne kartice, kao i CV2 kod na zadnjoj strani Vaše kreditne kartice. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> <span class=\"boldLetters\"> Molimo Vas da posetite \"Moj nalog\" i odaberete tab \"Postavljeni dokumenti\" da biste izvršili proceduru prosleđivanja Vaših informacija. </span> </p>
                    <p> Potrebno je da postsavite fotografiju ili skeniranu: </p>
                    <p> <span class=\"boldLetters\"> - validnu fotografiju ID dokumenta (pasoš ili lična karta) </span> kao i kopiju Vašeg </p>
                    <p> <span class=\"boldLetters\"> - prijavljenog prebivališta ili račune ne starije od 3 meseca </span> kao i kopiju Vaše </p>
                    <p> <span class=\"boldLetters\"> - kreditne kartice sa prednjom i zadnjom stranom </span> (SAMO ako ste koristili za uplate) </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Nije potrebno napominjati, da mi tretiramo poslate podatke sa strogom poverljivošću. Molimo Vas da pogledate <a class=\"underlineLetters\" href=\"{$privacy_policy_link}\">polisu privatnosti</a> na dnu </p>
                    <p> našeg web sajta. </p>
                    <p> Ukoliko Vam je potrebna dalja pomoć, biće nam drago da Vam pomognemo: <a class=\"underlineLetters\" href=\"{$support_link}\">Korisnička podrška</a> </p>
                    <p> Za naše Uslove korišćenja molimo Vas da posetite naš web sajt i kliknete na link <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Uslovi korišćenja\"</a> </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> SREĆNO! </p>
                    <p> Iskreno Vaš, </p>
                    <p> <span class=\"boldLetters\">{$casino_name}</span> tim podrške </p>";
                break;
            case 'en_GB':
            default:
                $mail_title = "Your Payout - please let us have some more information about you";
                $mail_message =
                   "<p> Dear <span class=\"boldLetters\">{$player_username}</span>, </p>
                    <p> Because of our LICENCING TERMS and the European and INTERNATIONAL MONEY LAUNDERING GUIDELINES, we </p>
                    <p> are obliged to be familiar with our customers before completing a payout. </p>
                    <p> For deposits we need this information in order to prevent the MISUSE OF CREDIT CARDS. In case you </p>
                    <p> have used a credit card or payment card for deposits, superating {$currency} {$player_limit} (€ 500), </p>
                    <p> we also need a copy of the credit card or payment card used.</p>
                    <p> When uploading  documents, please cover the middle 8 digits on the front of </p>
                    <p> your credit card, as well as the CV2 code on the back of your credit card. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> <span class=\"boldLetters\"> Please go to \"My Account\" and select the tab \"Documents upload\" to carry out the Know Your Customer procedure. </span> </p>
                    <p> We need you to upload a photo or scan of a: </p>
                    <p> <span class=\"boldLetters\"> - valid photo ID (passport or ID card) </span> and a copy of your </p>
                    <p> <span class=\"boldLetters\"> - residence registration or a bill of an utility service provider not older than 3 month </span> and a copy of your </p>
                    <p> <span class=\"boldLetters\"> - credit card frontside and backside </span> (ONLY when used for deposits) </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> It is not necessary to mention, that we treat received data strictly confidential. Please see our <a class=\"underlineLetters\" href=\"{$privacy_policy_link}\">privacy policy</a> at the  </p>
                    <p> bottom of the website. </p>
                    <p> If you need further help, we are glad to assist you: <a class=\"underlineLetters\" href=\"{$support_link}\">Customer support</a> </p>
                    <p> For our terms and conditions please visit on our website <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Terms and Conditions\"</a> </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> GOOD LUCK! </p>
                    <p> Yours sincerly, </p>
                    <p> The <span class=\"boldLetters\">{$casino_name}</span> support team </p>";
        }
		$message = self::getMailBody($language_settings, $mail_message, $site_images_location, $mail_title, $background_color, $text_color, $small_text_color, $footer_background,
				$text_font_size, $small_text_font_size,
				$hyperlink_text_color, $hyperlink_font_size,
				$hyperlink_small_text_color, $hyperlink_small_font_size);
		return array("mail_title"=>$mail_title, "mail_message"=>$message);
	}

    //denied payout to player did not provide any document
    //11. Payout confirmation - support approved (no cancel withdraw button) - confirmed in APCO BO -APCO confirmed to our BO processing of payout
	public static function getPayoutConfirmationFromApcoBackofficeContent($player_username, $transaction_id, $transaction_amount, $transaction_fee, $transaction_currency,
	$site_images_location, $casino_name, $site_link, $contact_link, $support_link, $terms_link, $language_settings = 'en_GB',
	$background_color = "#FFFFFF", $text_color = "#000000", $small_text_color = "#000000", $footer_background = "#FFFFFF",
	$text_font_size = "15", $small_text_font_size = "8",
	$hyperlink_text_color = "#FFFFFF", $hyperlink_font_size = "15",
	$hyperlink_small_text_color = "#FFFFFF", $hyperlink_small_font_size = "8"){
		$transaction_amount = NumberHelper::format_double($transaction_amount);
        $transaction_fee = NumberHelper::format_double($transaction_fee);
        switch ($language_settings) {
            case 'de_DE':
                $mail_title = "AUSZAHLUNGSBESTÄTIGUNG - Ihre Auszahlung (TransaktionsID: {$transaction_id} Betrag: {$transaction_currency} {$transaction_amount})";
                $mail_message =
                    "<p> Liebe(r) <span class=\"boldLetters\">{$player_username}</span>, </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Ihre Auszahlungsanfrage mit der <span class=\"boldLetters\">TransaktionsID: {$transaction_id}</span> wurde bestätigt. </p>
                    <p> Abhängig von der von Ihnen gewählten Zahlungsart sollten Sie den Betrag innerhalb von 1 - 4 Werktagen erhalten. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Wenn Sie sonstige Hilfe benötigen, unterstützen wir Sie gerne: <a class=\"underlineLetters\" href=\"{$support_link}\">Kundendienst-Team</a>. </p>
                    <p> Unsere <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Allgemeinen Geschäftsbedingungen\"</a> finden Sie auf unserer Webseite. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> WIR WÜNSCHEN VIEL GLÜCK UND GUTE UNTERHALTUNG! </p>
                    <p> Mit freundlichen Grüßen, </p>
                    <p> Ihr <span class=\"boldLetters\">{$casino_name}</span> Team </p>";
                break;
            case 'cs_CZ':
                $mail_title = "POTVRZENÍ O VÝPLATĚ – Vaše výplata (TransaktionsID: {$transaction_id} Částka: {$transaction_currency} {$transaction_amount})";
                $mail_message =
                    "<p> Vážený/á <span class=\"boldLetters\">{$player_username}</span>, </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Váš požadavek na výplatu <span class=\"boldLetters\">trasakceID: {$transaction_id}</span> byl potvrzen. </p>
                    <p> S ohledem na způsobu platby, který jste zvolili, měli byste částku obdržet do  1 - 4 pracovních dnů. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Potřebujete jakoukoliv další pomoc, rádi Vám pomůžeme na naši: <a class=\"underlineLetters\" href=\"{$support_link}\">Zákaznické podpoře</a>. </p>
                    <p> Naše <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Všeobecné podmínky\"</a> naleznete na našich webových stránkách. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> PŘEJEME HODNĚ ŠTĚSTÍ A PŘÍJEMNOU ZÁBAVU! </p>
                    <p> S přátelským pozdravem, </p>
                    <p> Váš <span class=\"boldLetters\">{$casino_name}</span> tým. </p>";
                break;
            case 'sv_SE':
                $mail_title = "GODKÄNNANDE – Din uttagsbegäran (Transaction ID: {$transaction_id} Summa: {$transaction_currency} {$transaction_amount})";
                $mail_message =
                    "<p> Kära <span class=\"boldLetters\">{$player_username}</span>, </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Din utbetalningsbegäran <span class=\"boldLetters\">Transaktions ID: {$transaction_id}</span> bekräftades. Beroende på din begärda </p>
                    <p> utbetalningsmetod bör du mottaga betalning innom 1–4 arbetsdagar. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Om du har några frågor, tveka inte att kontakta vår kundsupport för hjälp. </p>
                    <p> För våra Regler och Villkor vänligen läs, \"Regler och Villkor\". </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> LYCKA TILL! </p>
                    <p> Vänligen, </p>
                    <p> <span class=\"boldLetters\">{$casino_name}</span> supporten </p>";
                break;
            case 'rs_RS':
                $mail_title = "POTVRDA - Vaš zahtev za isplatu (Transakcijski ID: {$transaction_id} Iznos: {$transaction_currency} {$transaction_amount})";
                $mail_message =
                    "<p> Poštovani/a <span class=\"boldLetters\">{$player_username}</span>, </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Vaš zahtev za isplatu sa <span class=\"boldLetters\">Brojem Transakcije ID: {$transaction_id}</span> je potvrđen. </p>
                    <p> U zavisnosti od tražene metode isplate trebali biste da budete isplaćeni 1 -4 radnih dana. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Ukoliko Vam je potrebna dalja pomoć, biće nam drago da Vam pomognemo: <a class=\"underlineLetters\" href=\"{$support_link}\">Korisnička podrška</a>. </p>
                    <p> Za naše Uslove korišćenja molimo Vas da posetite naš web sajt i kliknete na link <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Uslovi korišćenja\"</a>. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> SREĆNO! </p>
                    <p> Iskreno Vaš, </p>
                    <p> <span class=\"boldLetters\">{$casino_name}</span> tim podrške </p>";
                break;
            case 'en_GB':
            default:
                $mail_title = "CONFIRMATION - Your payout request (Transaction ID: {$transaction_id} Amount: {$transaction_currency} {$transaction_amount})";
                $mail_message =
                    "<p> Dear <span class=\"boldLetters\">{$player_username}</span>, </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Your payout request with <span class=\"boldLetters\">Transaction ID: {$transaction_id}</span> was confirmed. </p>
                    <p> Depending on your requested payout method you should receive payment between 1 -4 business days. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> If you need further help, we are glad to assist you: <a class=\"underlineLetters\" href=\"{$support_link}\">Customer support</a>. </p>
                    <p> For our terms and conditions please visit on our website <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Terms and Conditions\"</a>. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> GOOD LUCK! </p>
                    <p> Yours sincerly, </p>
                    <p> The <span class=\"boldLetters\">{$casino_name}</span> support team </p>";
        }
		$message = self::getMailBody($language_settings, $mail_message, $site_images_location, $mail_title, $background_color, $text_color, $small_text_color, $footer_background,
				$text_font_size, $small_text_font_size,
				$hyperlink_text_color, $hyperlink_font_size,
				$hyperlink_small_text_color, $hyperlink_small_font_size);
		return array("mail_title"=>$mail_title, "mail_message"=>$message);
	}

    //when player received failed payout credit transfer over apco services
    //12. Payout decline - support approved - confirmed in APCO BO - APCO-declined to our BO processing of payout- contact support
	public static function getPayoutDeclinedFromApcoBackofficeContent($player_username, $transaction_id, $transaction_amount, $transaction_fee, $transaction_currency,
	$site_images_location, $casino_name, $site_link, $contact_link, $support_link, $terms_link, $language_settings = 'en_GB',
	$background_color = "#FFFFFF", $text_color = "#000000", $small_text_color = "#000000", $footer_background = "#FFFFFF",
	$text_font_size = "15", $small_text_font_size = "8",
	$hyperlink_text_color = "#FFFFFF", $hyperlink_font_size = "15",
	$hyperlink_small_text_color = "#FFFFFF", $hyperlink_small_font_size = "8"){
		$transaction_amount = NumberHelper::format_double($transaction_amount);
        $transaction_fee = NumberHelper::format_double($transaction_fee);
        switch ($language_settings){
            case 'de_DE':
                $mail_title = "Ihre Auszahlungsanfrage wurde von unserem Payment Provider abgelehnt (TransaktionsID {$transaction_id}) Betrag: {$transaction_currency} {$transaction_amount}";
                $mail_message =
                   "<p> Liebe(r) <span class=\"boldLetters\">{$player_username}</span>, </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Ihre Auszahlungsanfrage mit der <span class=\"boldLetters\">TransaktionsID: {$transaction_id}</span> wurde von dem unsere Zahlungen durchführenden Prozessor (Payment provider) abgelehnt. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Der beantragte Betrag und eventuell damit verbundene Spesen wurden auf Ihr Benutzerkonto zurück gebucht. </p>
                    <p> Sie können diese Transaktionen in \"Mein Konto\" / \"Meine Transaktionen\" nachverfolgen. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Bitte kontaktieren Sie unser <a class=\"underlineLetters\" href=\"{$support_link}\">Kundendienst-Team</a>. </p>
                    <p> Wir werden versuchen, die Gründe für die Ablehnung herauszufinden und Sie in allen Belangen unterstützen. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Wir entschuldigen uns für entstandene Unannehmlichkeiten. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> WIR WÜNSCHEN VIEL GLÜCK UND GUTE UNTERHALTUNG! </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Mit freundlichen Grüßen, </p>
                    <p> Ihr <span class=\"boldLetters\">{$casino_name}</span> Team </p>";
                break;
            case 'cs_CZ':
                $mail_title = "Vaše žádost o výplatu byla odmítnuta naším Payment Providerem (TransaktionsID {$transaction_id}) Částka: {$transaction_currency} {$transaction_amount}";
                $mail_message =
                   "<p> Vážený/á <span class=\"boldLetters\">{$player_username}</span>, </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Vaše žádost na výplatu s <span class=\"boldLetters\">transakćnímID {$transaction_id}</span> byla naším procesorem (Payment provider) odmítnuta. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Požadována částka a eventuelní s tímto spojené náklady Vám byli poukázany zpět na Váš uživatelský účet. </p>
                    <p> Průběh této  transakce můžete vidět na \"Moje konto\" / \"Moje transakce\". </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Prosím kontaktujte naši <a class=\"underlineLetters\" href=\"{$support_link}\">zákaznickou linku</a>. </p>
                    <p> Budeme se snažit s Vámi zjistit důvody proč transakce selhala a podpořit Vás při dalším řešení. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Omlouváme se za vzniklé potíže. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> PŘEJEME HODNĚ ŠTĚSTÍ A PŘÍJEMNOU ZÁBAVU! </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> S přátelským pozdravem, </p>
                    <p> Váš <span class=\"boldLetters\">{$casino_name}</span> tým. </p>";
                break;
            case 'sv_SE':
                $mail_title = "Din uttagsbegäran har blivit nekad av våran betalningsleverantör. (Transaction ID {$transaction_id}) Summa: {$transaction_currency} {$transaction_amount}";
                $mail_message =
                   "<p> Kära <span class=\"boldLetters\">{$player_username}</span>, </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Din utbetalningsbegäran med <span class=\"boldLetters\">Transaktions ID: {$transaction_id}</span> har blivit nekad av våran. </p>
                    <p> betalningsleverantör. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Det begärda beloppet och eventuella avgifter har krediterats ditt konto. </p>
                    <p> Du kan följa upp dessa transaktioner i \"mitt konto / mina transaktioner\". </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Vänligen kontakta vår kundsupport för att få hjälp med ditt problem. </p>
                    <p> Vi kommer försöka hitta anledningen till problemet och försöka hjälpa dig. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Vi beklagar besväret. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> LYCKA TILL! </p>
                    <p> Vänligen, </p>
                    <p> <span class=\"boldLetters\">{$casino_name}</span> supporten </p>";
                break;
            case 'rs_RS':
                $mail_title = "Vaš zahtev za isplatu je odbijen od strane našeg provajdera plaćanja (Transakcijski ID {$transaction_id}) Iznos: {$transaction_currency} {$transaction_amount}";
                $mail_message =
                   "<p> Poštovani <span class=\"boldLetters\">{$player_username}</span>, </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Vaš zahtev za isplatu sa <span class=\"boldLetters\">Brojem Transakcije ID: {$transaction_id}</span> je odbijen kod našeg provajdera plaćanja. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Zahtevani iznos isplate i bilo koja administrativna taksa su vraćeni nazad na Vaš nalog. </p>
                    <p> Ove transakcije možete pogledati preko opcije \"Moj nalog\" / \"Moje transakcije\". </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Molimo Vas da kontaktirate <a class=\"underlineLetters\" href=\"{$support_link}\">korisničku podršku</a> da biste nastavili sa Vašom aplikacijom. </p>
                    <p> Pokušaćemo da otkrijemo razloge odbijanja i pružimo Vam odgovarajuću podršku. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Izvinjavamo se ovim putem zvog bilo kakve neprijatnosti. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> SREĆNO! </p>
                    <p> Iskreno Vaš, </p>
                    <p> <span class=\"boldLetters\">{$casino_name}</span> tim podrške </p>";
                break;
            case 'en_GB':
            default:
                $mail_title = "Your payout request has been declined by our payment processor (Transaction ID {$transaction_id}) Amount: {$transaction_currency} {$transaction_amount}";
                $mail_message =
                   "<p> Dear <span class=\"boldLetters\">{$player_username}</span>, </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Your payout request with <span class=\"boldLetters\">Transaction ID: {$transaction_id}</span> has been declined by our payment processor. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> The requested payout amount and any related fees has been credited back to your account. </p>
                    <p> You can follow up those transactions in \"My account\" / \"My transactions\". </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Please contact our <a class=\"underlineLetters\" href=\"{$support_link}\">Customer support</a> to proceed with your applications. </p>
                    <p> We will try to find out the reasons for disapproval and to support you. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> We apologize for any inconveniance. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> GOOD LUCK! </p>
                    <p> Yours sincerly, </p>
                    <p> The <span class=\"boldLetters\">{$casino_name}</span> support team </p>";
        }
		$message = self::getMailBody($language_settings, $mail_message, $site_images_location, $mail_title, $background_color, $text_color, $small_text_color, $footer_background,
				$text_font_size, $small_text_font_size,
				$hyperlink_text_color, $hyperlink_font_size,
				$hyperlink_small_text_color, $hyperlink_small_font_size);
		return array("mail_title"=>$mail_title, "mail_message"=>$message);
	}

    //if player's payout has been denied
    //13. Payout - declined by our support - fee return & payout return to credits - contact support
	public static function getPayoutRequestCanceledBySupportContent($player_username, $transaction_id, $transaction_amount, $transaction_currency,
	$site_images_location, $casino_name, $site_link, $contact_link, $support_link, $terms_link, $language_settings = 'en_GB',
	$background_color = "#FFFFFF", $text_color = "#000000", $small_text_color = "#000000", $footer_background = "#FFFFFF",
	$text_font_size = "15", $small_text_font_size = "8",
	$hyperlink_text_color = "#FFFFFF", $hyperlink_font_size = "15",
	$hyperlink_small_text_color = "#FFFFFF", $hyperlink_small_font_size = "8"){
		$transaction_amount = NumberHelper::format_double($transaction_amount);
        switch ($language_settings){
            case 'de_DE':
                $mail_title = "Ihre Auszahlungsanfrage wurde abgelehnt (TransaktionsID {$transaction_id}) Betrag: {$transaction_currency} {$transaction_amount}";
                $mail_message =
                   "<p> Liebe(r) <span class=\"boldLetters\">{$player_username}</span>, </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Wir konnten Ihre Auszahlungsanfrage mit der <span class=\"boldLetters\">TransaktionsID: {$transaction_id}</span> nicht bestätigen. </p>
                    <p> Der beantragte Betrag und eventuell damit verbundene Spesen wurden auf Ihr Benutzerkonto zurück gebucht. </p>
                    <p> Sie können diese Transaktionen in \"Mein Konto\" / \"Meine Transaktionen\" nachverfolgen. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Bitte kontaktieren Sie unser <a class=\"underlineLetters\" href=\"{$support_link}\">Kundendienst-Team</a> um mit Ihren Belangen fortzufahren. </p>
                    <p> Wir entschuldigen uns für entstandene Unannehmlichkeiten. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> WIR WÜNSCHEN VIEL GLÜCK UND GUTE UNTERHALTUNG! </p>
                    <p> Mit freundlichen Grüßen, </p>
                    <p> Ihr <span class=\"boldLetters\">{$casino_name}</span> Team </p>";
                break;
            case 'cs_CZ':
                $mail_title = "Vaše žádost o výplatu byla odmítnuta (TransaktionsID {$transaction_id}) Částka: {$transaction_currency} {$transaction_amount}";
                $mail_message =
                   "<p> Vážený/á <span class=\"boldLetters\">{$player_username}</span>, </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Vaši žádost o výplatu s <span class=\"boldLetters\">transakčním ID {$transaction_id}</span> jsme nemohli potvrdit. </p>
                    <p> Požadována částka a eventuelní s tímto spojené náklady Vám byli poukázany zpět na Váš uživatelský účet. </p>
                    <p> Průběh této  transakce můžete vidět na \"Moje konto\" / \"Moje transakce\". </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Prosím kontaktujte <a class=\"underlineLetters\" href=\"{$support_link}\">naši zákaznickou linku</a>, kde s Vámi budeme poračovat v řešení. </p>
                    <p> Omlouváme se za vzniklé potíže. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> PŘEJEME HODNĚ ŠTĚSTÍ A PŘÍJEMNOU ZÁBAVU! </p>
                    <p> S přátelským pozdravem, </p>
                    <p> Váš <span class=\"boldLetters\">{$casino_name}</span> tým. </p>";
                break;
            case 'sv_SE':
                $mail_title = "Nekad uttagsbegäran (Transactions ID {$transaction_id}) Summa: {$transaction_currency} {$transaction_amount}";
                $mail_message =
                   "<p> Kära <span class=\"boldLetters\">{$player_username}</span>, </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Vi kunde inte godkänna sin utbetalningsbegäran med <span class=\"boldLetters\">Transactions ID: {$transaction_id}</span>. </p>
                    <p> Det begärda beloppet och eventuella avgifter har krediterats ditt konto. </p>
                    <p> Du kan följa upp dessa transaktioner i \"mitt konto / mina transaktioner\". </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Vänligen kontakta vår kundsupport för att få hjälp med ditt problem. </p>
                    <p> Vi kommer försöka hitta anledningen till problemet och försöka hjälpa dig. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Vi beklagar besväret. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> LYCKA TILL! </p>
                    <p> Vänligen, </p>
                    <p> <span class=\"boldLetters\">{$casino_name}</span> supporten </p>";
                break;
            case 'rs_RS':
                $mail_title = "Odbijanje Vašeg zahteva za isplatu (Transakcijski ID {$transaction_id}) Iznos: {$transaction_currency} {$transaction_amount}";
                $mail_message =
                   "<p> Poštovani/a <span class=\"boldLetters\">{$player_username}</span>, </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Nismo mogli da odobrimo Vaš zahtev za isplatu <span class=\"boldLetters\">Transakcijski ID: {$transaction_id}</span>. </p>
                    <p> Zahtevani iznos isplate i bilo koja povezana administrativna taksa su uplaćeni na Vaš nalog. </p>
                    <p> Ove transakcije možete pregledati preko opcije \"Moj nalog\" / \"Moje tranaskcije\". </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Molimo Vas da kontaktirate našu <a class=\"underlineLetters\" href=\"{$support_link}\">korisničku podršku</a> da bi ste nastavili sa vašom aplikacijom. </p>
                    <p> Unapred se izvinjavamo zbog bilo kakve neprijatnosti. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> SREĆNO! </p>
                    <p> Iskreno Vaš, </p>
                    <p> <span class=\"boldLetters\">{$casino_name}</span> tim podrške </p>";
                break;
            case 'en_GB':
            default:
                $mail_title = "Disapproval to your payout request (Transaction ID {$transaction_id}) Amount: {$transaction_currency} {$transaction_amount}";
                $mail_message =
                   "<p> Dear <span class=\"boldLetters\">{$player_username}</span>, </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> We could not approve your payout request with <span class=\"boldLetters\">Transaction ID: {$transaction_id}</span>. </p>
                    <p> The requested payout amount and any related fees has been credited back to your account. </p>
                    <p> You can follow up those transactions in \"My account\" / \"My transactions\". </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> Please contact our <a class=\"underlineLetters\" href=\"{$support_link}\">Customer support</a> to proceed with your applications. </p>
                    <p> We apologize for any inconveniance. </p>
                    <p class=\"smallLetters\"> &nbsp; </p>
                    <p> GOOD LUCK! </p>
                    <p> Yours sincerly, </p>
                    <p> The <span class=\"boldLetters\">{$casino_name}</span> support team </p>";
        }
		$message = self::getMailBody($language_settings, $mail_message, $site_images_location, $mail_title, $background_color, $text_color, $small_text_color, $footer_background,
				$text_font_size, $small_text_font_size,
				$hyperlink_text_color, $hyperlink_font_size,
				$hyperlink_small_text_color, $hyperlink_small_font_size);
		return array("mail_title"=>$mail_title, "mail_message"=>$message);
	}

    //player's payout has been canceled by player
    //14. Pending Payout - cancelled by player
	public static function getPlayerCanceledHisPayoutContent($player_username, $transaction_id,
	$site_images_location, $casino_name, $site_link, $contact_link, $support_link, $terms_link, $language_settings = 'en_GB',
	$background_color = "#FFFFFF", $text_color = "#000000", $small_text_color = "#000000", $footer_background = "#FFFFFF",
	$text_font_size = "15", $small_text_font_size = "8",
	$hyperlink_text_color = "#FFFFFF", $hyperlink_font_size = "15",
	$hyperlink_small_text_color = "#FFFFFF", $hyperlink_small_font_size = "8"){
        switch ($language_settings){
            case 'de_DE':
                $mail_title = "STORNIERUNG Ihrer anstehenden Auszahlung(en)";
                $mail_message =
                "<p> Liebe(r) <span class=\"boldLetters\">{$player_username}</span>, </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Die Stornierung Ihrer  anstehenden Auszahlung(en) mit der <span class=\"boldLetters\">TransaktionsID: {$transaction_id}</span> wurde durchgeführt. </p>
                 <p> Die anstehende(n) Auszahlung(en) und eventuell damit verbundene Spesen wurden auf Ihr Benutzerkonto zurück gebucht. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Sie können diese Transaktionen in \"Mein Konto\" / \"Meine Transaktionen\" nachverfolgen. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Wenn Sie sonstige Hilfe benötigen, unterstützen wir Sie gerne: <a class=\"underlineLetters\" href=\"{$support_link}\">Kundendienst-Team</a> </p>
                 <p> Unsere <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Allgemeinen Geschäftsbedingungen\"</a> finden Sie auf unserer Webseite. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> WIR WÜNSCHEN VIEL GLÜCK UND GUTE UNTERHALTUNG! </p>
                 <p> Mit freundlichen Grüßen, </p>
                 <p> Ihr <span class=\"boldLetters\">{$casino_name}</span> Team </p>";
                break;
            case 'cs_CZ':
                $mail_title = "STORNO Vaší čekající výplaty";
                $mail_message =
                "<p> Vážený/á <span class=\"boldLetters\">{$player_username}</span>, </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Storno Vaší čekající výplaty s <span class=\"boldLetters\">transakčním ID: {$transaction_id}</span> bylo provedeno. </p>
                 <p> Čekající výplata/y a eventuelní s tím spojené poplatky byli pŕipsány zpět na Váš zákaznický účet. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Průběh této  transakce můžete vidět na \"Moje konto\" / \"Moje transakce\". </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Pokud budete potřebovat další pomoc, rádi Vás podpoříme na: <a class=\"underlineLetters\" href=\"{$support_link}\">Zákaznický servis - Team</a> </p>
                 <p> Naše <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Všeobecné podmínky\"</a> naleznete na našich webových stránkách. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> PŘEJEME HODNĚ ŠTĚSTÍ A PŘÍJEMNOU ZÁBAVU! </p>
                 <p> S přátelským pozdravem, </p>
                 <p> Váš <span class=\"boldLetters\">{$casino_name}</span> tým. </p>";
                break;
            case 'sv_SE':
                $mail_title = "Avbrutet pågående uttag";
                $mail_message =
                "<p> Kära <span class=\"boldLetters\">{$player_username}</span>, </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Din förfrågan om att avbryta pågående uttag med <span class=\"boldLetters\">Transaktions ID: {$transaction_id}</span> </p>
                 <p> har utförts. </p>
                 <p> Det begärda beloppet och eventuella avgifter har krediterats ditt konto. </p>
                 <p> Du kan följa upp dessa transaktioner i \"mitt konto / mina transaktioner\". </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Vänligen kontakta vår kundsupport för att få hjälp med ditt problem. </p>
                 <p> Vi kommer försöka hitta anledningen till problemet och försöka hjälpa dig. </p>
                 <p> Vi beklagar besväret. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> LYCKA TILL! </p>
                 <p> Vänligen, </p>
                 <p> <span class=\"boldLetters\">{$casino_name}</span> supporten </p>";
                break;
            case 'rs_RS':
                $mail_title = "OTKAZIVANJE vaših zahteva za isplatu";
                $mail_message =
                "<p> Poštovani <span class=\"boldLetters\">{$player_username}</span>, </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Vaš zahtev za otkazivanjem zahteva za isplatu sa <span class=\"boldLetters\">Brojem transakcije ID: {$transaction_id}</span> je izvršen. </p>
                 <p> Traženi iznos isplate i bilo koja povezana administrativna taksa su vraćeni na Vaš nalog. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Ove transakcije možete videti na opciji \"Moj nalog\" / \"Moje transakcije\". </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Ukoliko Vam je potrebna dalja pomoć, biće nam drago da Vam pomognemo: <a class=\"underlineLetters\" href=\"{$support_link}\">Korisnička podrška</a> </p>
                 <p> Za naše Uslove korišćenja molimo Vas da posetite naš web sajt i kliknete na link <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Uslovi korišćenja\"</a>. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> SREĆNO! </p>
                 <p> Iskreno Vaš, </p>
                 <p> <span class=\"boldLetters\">{$casino_name}</span> tim podrške </p>";
                break;
            case 'en_GB':
            default:
                $mail_title = "CANCELATION of your pending payout(s)";
                $mail_message =
                "<p> Dear <span class=\"boldLetters\">{$player_username}</span>, </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Your request to cancel pending payout(s) with <span class=\"boldLetters\">Transaction ID: {$transaction_id}</span> has been executed. </p>
                 <p> The requested payout amount(s) and any related fees has been credited back to your account. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> You can follow up those transactions in \"My account\" / \"My transactions\". </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> If you need further help, we are glad to assist you: <a class=\"underlineLetters\" href=\"{$support_link}\">Customer support</a> </p>
                 <p> For our terms and conditions please visit on our website <a class=\"underlineLetters\" href=\"{$terms_link}\">\"Terms and Conditions\"</a>. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> GOOD LUCK! </p>
                 <p> Yours sincerly, </p>
                 <p> The <span class=\"boldLetters\">{$casino_name}</span> support team </p>";
        }
		$message = self::getMailBody($language_settings, $mail_message, $site_images_location, $mail_title, $background_color, $text_color, $small_text_color, $footer_background,
				$text_font_size, $small_text_font_size,
				$hyperlink_text_color, $hyperlink_font_size,
				$hyperlink_small_text_color, $hyperlink_small_font_size);
		return array("mail_title"=>$mail_title, "mail_message"=>$message);
	}

    //player's payout has been canceled by player
    //15. KYC - renewal needed - automated - if support at payout see KYC older than 180 days & KYC status manually set back to Email-verified
	public static function getPlayerChangesVerificationStatusContent($player_username, $transaction_id, $transaction_amount, $transaction_currency,
	$site_images_location, $casino_name, $site_link, $contact_link, $support_link, $terms_link, $privacy_policy_link, $language_settings = 'en_GB',
	$background_color = "#FFFFFF", $text_color = "#000000", $small_text_color = "#000000", $footer_background = "#FFFFFF",
	$text_font_size = "15", $small_text_font_size = "8",
	$hyperlink_text_color = "#FFFFFF", $hyperlink_font_size = "15",
	$hyperlink_small_text_color = "#FFFFFF", $hyperlink_small_font_size = "8"){
        $transaction_amount = NumberHelper::format_double($transaction_amount);
        switch ($language_settings){
            case 'de_DE':
                $mail_title = "KENNE DEINEN KUNDEN AKTUALISIERUNG -- Ihre Auszahlung (Transaction ID {$transaction_id}) Betrag: {$transaction_currency} {$transaction_amount}";
                $mail_message =
                "<p> Liebe(r) <span class=\"boldLetters\">{$player_username}</span>, </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Aufgrund unserer LIZENZBESTIMMUNGEN und der europäischen und INTERNATIONALEN GELDWÄSCHE RICHTLINIEN sind wir verpflichtet, </p>
                 <p> die KENNE DEINEN KUNDEN PROZEDUR im Falle einer Auszahlung zu erneuern, wenn unsere Aufzeichnungen älter als <span class=\"boldLetters\">180 Tage</span> sind. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> <span class=\"boldLetters\"> Bitte gehen Sie zu \"Mein Konto\" und wählen Sie den Tab \"Dokumente hochladen\" um die aktuellen Dokumente hochzuladen. </span> </p>
                 <p> Bitte laden Sie einen Scan oder ein Photo folgender Dokumente hoch: </p>
                 <p> <span class=\"boldLetters\"> - Gültiger Lichtbildausweis </span> </p>
                 <p> <span class=\"boldLetters\"> - Amtliche Meldebestätigung oder eine Strom- Gas- oder Wasserrechnung, nicht älter als 3 Monate </span> </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Selbstverständlich halten wir Ihre Daten strengstens vertraulich. Bitte informieren Sie auf unserer Webseite über unsere <a class=\"underlineLetters\" href=\"{$privacy_policy_link}\">Datenschutzrichtlinien</a>. </p>
                 <p> Wenn Sie sonstige Hilfe benötigen, unterstützen wir Sie gerne: <a class=\"underlineLetters\" href=\"{$support_link}\">Kundendienst-Team</a>. </p>
                 <p> Unsere <a class=\"underlineLetters\" href=\"{$support_link}\">\"Allgemeinen Geschäftsbedingungen\"</a> finden Sie auf unserer Webseite. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> WIR WÜNSCHEN VIEL GLÜCK UND GUTE UNTERHALTUNG! </p>
                 <p> Mit freundlichen Grüßen, </p>
                 <p> Ihr <span class=\"boldLetters\">{$casino_name}</span> Team </p>";
                break;
            case 'cs_CZ':
                $mail_title = "POZNEJ SVÉHO ZÁKAZNÍKA AKTUALIZACE – Vaše výplata (Transaction ID {$transaction_id}) Částka: {$transaction_currency} {$transaction_amount}";
                $mail_message =
                "<p> Vážený/á <span class=\"boldLetters\">{$player_username}</span>, </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Vzhledem k naším licenčním podmínkám a evropských a mezinárodních směrnic o praní špinavých peněz </p>
                 <p> jsme povinni proces poznej svého zákazníka, obnovit, pokud jsou naše záznamy starší <span class=\"boldLetters\">180ti</span> dnů. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> <span class=\"boldLetters\"> Prosím, jděte na \"Můj účet\" a vyberte políčko \"Nahrát dokumenty\". </span> </p>
                 <p> <span class=\"boldLetters\"> Nahrajte prosím scan nebo fotografii těchto dokumentů: </span> </p>
                 <p> <span class=\"boldLetters\"> - Platný občanský průkaz nebo Pas </span> </p>
                 <p> <span class=\"boldLetters\"> - Potvrzení o trvalém bydlišti nebo úcet za elektřinu, vodu, plynu s vaším jménem a adresou trvalého bydliště ne starší 3 měsíců </span> </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Samozřejmě budeme držet Vaše data přísně důvěrně. Prosím informujte se na našich webových stránkách o <a class=\"underlineLetters\" href=\"{$privacy_policy_link}\">\"zásadách ochrany osobních údajů\"</a>. </p>
                 <p> Máte-li jakékoliv další dotazy nebo potřebujete jakoukoliv další pomoc, prosím, kontaktujte naši <a class=\"underlineLetters\" href=\"{$support_link}\">\"Zákaznickou podporu-služby zákazníkům\"</a>. </p>
                 <p> Naše <a class=\"underlineLetters\" href=\"{$support_link}\">\"Všeobecné podmínky\"</a> naleznete na našich webových stránkách. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> PŘEJEME HODNĚ ŠTĚSTÍ A PŘÍJEMNOU ZÁBAVU! </p>
                 <p> S přátelským pozdravem, </p>
                 <p> Váš <span class=\"boldLetters\">{$casino_name}</span> tým. </p>";
                 break;
            case 'sv_SE':
                $mail_title = "KYC-förnyelse nödvändig - Uttagsbegäran  (Transaction ID {$transaction_id}) Summa: {$transaction_currency} {$transaction_amount}";
                $mail_message =
                "<p> Kära <span class=\"boldLetters\">{$player_username}</span>, </p>
                <p class=\"smallLetters\"> &nbsp; </p>
                 <p> På grund av våra LICENSREGLER och den Europeiska och de INTERNATIONELLA PENGATVÄTTSLAGARNA, är vi </p>
                 <p> skylldiga att förnya \"Know Your Customer\" förfarande om ett uttag begärts, och om dina uppgifter </p>
                 <p> vi har sparade är äldre än <span class=\"boldLetters\">180 dagar</span>. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> <span class=\"boldLetters\"> Vänligen gå till ditt konto och välj \"ladda upp dokument\" för att ladda upp förnyade dokument. </span> </p>
                 <p> <span class=\"boldLetters\"> Vi behöver en kopia på: </span> </p>
                 <p> <span class=\"boldLetters\"> - giltig fotolegitimation (pass,Id-kort eller körkort) och en kopia på </p>
                 <p> <span class=\"boldLetters\"> - var du är folkbokförd eller en räkning inte äldre än 3 månader. </span> </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Det är inte nödvändigt att nämna, att vi behandlar mottagna data strikt konfidentiellt. </p>
                 <p> Se vår sekretesspolicy längst ner på webbplatsen. </p>
                 <p> Om du har några frågor, tveka inte att kontakta vår kundsupport för hjälp. </p>
                 <p> För våra Regler och Villkor vänligen läs, \"Regler och Villkor\". </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Lycka Till! </p>
                 <p> Vänligen, </p>
                 <p> <span class=\"boldLetters\">{$casino_name}</span> supporten </p>";
                break;
            case 'rs_RS':
                $mail_title = "KYC status - obnova statusa -- Zahtev isplate (Transakcijski ID {$transaction_id}) Iznos: {$transaction_currency} {$transaction_amount}";
                $mail_message =
                "<p> Poštovani <span class=\"boldLetters\">{$player_username}</span>, </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Usled naših USLOVA LICENCIRANJA i Evropske i MEĐUNARODNIH OKVIRA ZA SPREČAVANJE PRANJA NOVCA, </p>
                 <p> mi smo nadležni da obnovimo Vaše detaljnije informacije u slučaju isplate, ukoliko su naši podaci stariji od <span class=\"boldLetters\">180 dana</span>. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> <span class=\"boldLetters\"> Molimo Vas da posetite \"Moj nalog\" i izaberete tab \"Postavljanje dokumenata\" da biste podneli nova dokumenta. </span> </p>
                 <p> Potrebno je da nam postavite fotografiju ili skeniran dokument </p>
                 <p> <span class=\"boldLetters\"> - sa ispravnom fotografijom ID dokumenta (pasoš ili lična karta) </span> i kopiju Vaše </p>
                 <p> <span class=\"boldLetters\"> - registracije prebivališta ili račun (ne stariji od 3 meseca). </span> </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Nije potrebno napominjati, da mi naše podatke tretiramo sa isključivom poverljivošću. Molimo Vas da posetite <a class=\"underlineLetters\" href=\"{$privacy_policy_link}\">polisu privatnosti</a> na dnu našeg web sajta. </p>
                 <p> Ukoliko Vam je potrebna dalja pomoć, biće nam drago da Vam pomognemo: <a class=\"underlineLetters\" href=\"{$support_link}\">Korisnička podrška</a>. </p>
                 <p> Za naše Uslove korišćenja molimo Vas da posetite naš web sajt i kliknete na link <a class=\"underlineLetters\" href=\"{$support_link}\">\"Uslovi korišćenja\"</a>. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> SREĆNO! </p>
                 <p> Iskreno Vaš, </p>
                 <p> <span class=\"boldLetters\">{$casino_name}</span> tim podrške </p>";
                break;
            case 'en_GB':
            default:
                $mail_title = "KYC-renewal requisite -- Payout request (Transaction ID {$transaction_id}) Amount: {$transaction_currency} {$transaction_amount}";
                $mail_message =
                "<p> Dear <span class=\"boldLetters\">{$player_username}</span>, </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Because of our LICENCING TERMS and the European and INTERNATIONAL MONEY LAUNDERING GUIDELINES, we </p>
                 <p> are obliged to renew the Know Your Customer procedure in a case of payout, if our recordings are older than <span class=\"boldLetters\">180 days</span>. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> <span class=\"boldLetters\"> Please go to \"My Account\" and select the tab \"Documents upload\" to upload renewed documents. </span> </p>
                 <p> We need you to upload a photo or scan of a </p>
                 <p> <span class=\"boldLetters\"> - valid photo ID (passport or ID card) </span> and a copy of your </p>
                 <p> <span class=\"boldLetters\"> - residence registration or a bill of an utility service provider (not older than 3 month). </span> </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> It is not necessary to mention, that we treat received data strictly confidential. Please see our <a class=\"underlineLetters\" href=\"{$privacy_policy_link}\">privacy policy</a> at the bottom of the website. </p>
                 <p> If you need further help, we are glad to assist you: <a class=\"underlineLetters\" href=\"{$support_link}\">Customer support</a>. </p>
                 <p> For our terms and conditions please visit on our website <a class=\"underlineLetters\" href=\"{$support_link}\">\"Terms and Conditions\"</a>. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> GOOD LUCK! </p>
                 <p> Yours sincerly, </p>
                 <p> The <span class=\"boldLetters\">{$casino_name}</span> support team </p>";
        }
		$message = self::getMailBody($language_settings, $mail_message, $site_images_location, $mail_title, $background_color, $text_color, $small_text_color, $footer_background,
				$text_font_size, $small_text_font_size,
				$hyperlink_text_color, $hyperlink_font_size,
				$hyperlink_small_text_color, $hyperlink_small_font_size);
		return array("mail_title"=>$mail_title, "mail_message"=>$message);
	}

    //16. Reminder inactivity 890 days - announcement of "account closing" - if no login within 10 days - request to payout remaining funds
	public static function getPlayerBeforeAccountClosingContent($player_username, $site_images_location, $casino_name, $site_link, $support_link, $terms_link, $contact_link,
	$fee, $currency, $next_fee_date, $reactivate_before_fee_date, $inactive_time, $language_settings = 'en_GB',
	$background_color = "#FFFFFF", $text_color = "#000000", $small_text_color = "#000000", $footer_background = "#FFFFFF",
	$text_font_size = "15", $small_text_font_size = "8",
	$hyperlink_text_color = "#FFFFFF", $hyperlink_font_size = "15",
	$hyperlink_small_text_color = "#FFFFFF", $hyperlink_small_font_size = "8"){
        $inactive_time_minus_10_days = $inactive_time - 10;
        $fee = NumberHelper::format_double($fee);
        switch ($language_settings){
            case 'de_DE':
                $mail_title = "{$casino_name} - {$inactive_time_minus_10_days} Tage keine Kontoaktivitäten - Erinnerung";
                $mail_message =
                "<p> Liebe(r) <span class=\"boldLetters\">{$player_username}</span>, </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Sie waren nunmehr seit <span class=\"boldLetters\">{$inactive_time_minus_10_days} Tagen</span> nicht mehr in Ihrem Benutzerkonto eingeloggt. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Entsprechend unserer <a class=\"underlineLetters\" href=\"{$support_link}\">\"Allgemeinen Geschäftsbedingungen\"</a> sind wir berechtigt, Ihr Konto nach <span class=\"boldLetters\">{$inactive_time} Tagen</span> </p>
                 <p> Inaktivität zu schließen. Um Ihr Benutzerkonto zu reaktivieren, loggen Sie sich bitte </p>
                 <p> ein, oder zahlen Sie eventuelle Guthaben vor der Schließung aus. Entsprechend den gesetzlichen </p>
                 <p> Regelungen in Malta werden verbleibende Guthaben auf Benutzerkonten im Falle von </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Kontoschließungen der offiziellen maltesischen Lizenzbehörde gutgeschrieben. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Bitte melden Sie sich in Ihrem Konto an, um unsere tollen Spiele und eventuell verfügbare </p>
                 <p> Promotions anzusehen. </p>
                 <p> Sollten Sie weitere Fragen haben, bitten wir Sie, sich an unser <a class=\"underlineLetters\" href=\"{$support_link}\">Kundendienst-Service-Team</a> zu wenden. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Mit freundlichen Grüßen, </p>
                 <p> Ihr <span class=\"boldLetters\">{$casino_name}</span> Team </p>";
                break;
            case 'cs_CZ':
                $mail_title = "{$casino_name} - {$inactive_time_minus_10_days} dní žádná aktivita účtu - upozornění";
                $mail_message =
                "<p> Vážený/á <span class=\"boldLetters\">{$player_username}</span>, </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Již <span class=\"boldLetters\">{$inactive_time_minus_10_days} </span> jste se nepřihlásili na Váš účet. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Na základě naších všeobecných podmínek jsme oprávněni Váš účet zabanovat pokud byl <span class=\"boldLetters\">{$inactive_time} dní</span> </p>
                 <p> inaktivní. Pokud si přejete Váš účet reaktivovat,přihlašte se nebo si vyplaťte Váš deposit před  </p>
                 <p> zabanováním. Na základě Maltské regulace, po zabanování účtu zbylý zůstatek na účtě propadá licenčnímu </p>
                 <p> uřadu na Maltě. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Prosím, přihlaste se do svého uživatelského účtu pro zobrazení naších skvělých her a všech nových </p>
                 <p> dostupných  akcí.</p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Při dalších dotazech se prosím obraťte na naší: <a class=\"underlineLetters\" href=\"{$support_link}\">Zákaznickou  podporu - Team</a>. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> S přátelským pozdravem, </p>
                 <p> Váš <span class=\"boldLetters\">{$casino_name}</span> tým. </p>";
                 break;
            case 'sv_SE':
                $mail_title = "{$casino_name} - KontoInaktivitet {$inactive_time_minus_10_days} dagar - Påminnelse";
                $mail_message =
                "<p> Kära <span class=\"boldLetters\">{$player_username}</span>, </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Du har inte varit aktiv på ditt konto på <span class=\"boldLetters\">{$inactive_time_minus_10_days} dagar</span>. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Enligt våra \"Regler och Villkor\" är vi berättigad till att stänga ditt konto efter  <span class=\"boldLetters\">{$inactive_time} dagar av</span> </p>
                 <p> <span class=\"boldLetters\">inaktivitet</span>. Vänligen logga in på ditt konto för att återaktivera eller ta ut dina innestående </p>
                 <p> medel innan kontot stängs. Enligt Maltesiskt regelverk så kommer innestående medel på ditt konto </p>
                 <p> bokföras till ett konto som tillhör den officiella Maltesiska tillståndsmyndigheten om ditt konto </p>
                 <p> stängs. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Vänligen logga in och se våra fantastiska spel och tillgängliga erbjudanden. </p>
                 <p> Om du har några frågor, tveka inte att kontakta vår kundsupport för hjälp. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Vänligen, </p>
                 <p> <span class=\"boldLetters\">{$casino_name}</span> supporten </p>";
                break;
            case 'rs_RS':
                $mail_title = "{$casino_name} neaktivnost naloga {$inactive_time_minus_10_days} dana - Podsetnik";
                $mail_message =
                "<p> Poštovani/a <span class=\"boldLetters\">{$player_username}</span>, </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Niste bili ulogovani na Vaš nalog <span class=\"boldLetters\">{$inactive_time_minus_10_days} dana</span>. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Prema našim <a class=\"underlineLetters\" href=\"{$support_link}\">\"Uslovima korišćenja\"</a> mi smo nadležni da <span class=\"boldLetters\">zatvorimo vaš nalog</span> nakon <span class=\"boldLetters\">{$inactive_time} dana neaktivnosti.</span> </p>
                 <p> Molimo Vas da reaktivirate Vaš nalog ili povučete preostala sredstva pre zatvaranja naloga. </p>
                 <p> Prema pravnoj regulaciji Malte preostali krediti na Vašem nalogu će biti </p>
                 <p> uknjiženi na nalog zvanične Organizacije licencirane na Malti u slučaju zatvaranja naloga. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Molimo Vas da se ulogujete i posetite našu sjajnu ponudu i dostupne promocije. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Ukoliko imate nedoumica, molimo Vas da kontaktirate naš <a class=\"underlineLetters\" href=\"{$support_link}\">korisnički tim podrške</a> za pomoć. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Iskreno Vaš, </p>
                 <p> <span class=\"boldLetters\">{$casino_name}</span> tim podrške </p>";
                break;
            case 'en_GB':
            default:
                $mail_title = "{$casino_name} account inactivity {$inactive_time_minus_10_days} days - Reminder";
                $mail_message =
                "<p> Dear <span class=\"boldLetters\">{$player_username}</span>, </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> You have not been logged into your account for <span class=\"boldLetters\">{$inactive_time_minus_10_days} days</span>. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> According to our <a class=\"underlineLetters\" href=\"{$support_link}\">\"Terms and Conditions\"</a> we are entitled to <span class=\"boldLetters\">close your account</span> after <span class=\"boldLetters\">{$inactive_time} days</span> </p>
                 <p> of inactivity. Please login to reactivate your account or withdraw remaining funds before account closure. </p>
                 <p> According to the legal Maltese regulations remaining credits on your user account will </p>
                 <p> be booked to an account of the official  Maltese licensing authority in case of account closure. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Please login and see our great games and available promotions. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> If you have any concerns, please contact our <a class=\"underlineLetters\" href=\"{$support_link}\">customer support team</a> for assistance. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Yours sincerly, </p>
                 <p> The <span class=\"boldLetters\">{$casino_name}</span> support team </p>";
        }
		$message = self::getMailBody($language_settings, $mail_message, $site_images_location, $mail_title, $background_color, $text_color, $small_text_color, $footer_background,
				$text_font_size, $small_text_font_size,
				$hyperlink_text_color, $hyperlink_font_size,
				$hyperlink_small_text_color, $hyperlink_small_font_size);
		return array("mail_title"=>$mail_title, "mail_message"=>$message);
	}

    //17.  Account closure - after 900 days of inactivity
	public static function getPlayerForAccountClosingContent($player_username, $site_images_location, $casino_name, $site_link, $support_link, $terms_link, $contact_link,
	$fee, $currency, $next_fee_date, $reactivate_before_fee_date, $inactive_time, $language_settings = 'en_GB',
	$background_color = "#FFFFFF", $text_color = "#000000", $small_text_color = "#000000", $footer_background = "#FFFFFF",
	$text_font_size = "15", $small_text_font_size = "8",
	$hyperlink_text_color = "#FFFFFF", $hyperlink_font_size = "15",
	$hyperlink_small_text_color = "#FFFFFF", $hyperlink_small_font_size = "8"){
        $fee = NumberHelper::format_double($fee);
        switch ($language_settings){
            case 'de_DE':
                $mail_title = "{$casino_name} - Ihr Benutzerkonto wurde geschlossen";
                $mail_message =
                "<p> Liebe(r) <span class=\"boldLetters\">{$player_username}</span>, </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Sie waren nunmehr seit <span class=\"boldLetters\">{$inactive_time} Tagen</span> nicht mehr in Ihrem Benutzerkonto eingeloggt. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Entsprechend unserem Erinnerungsschreiben von vor 10 Tagen und unserer Allgemeinen Geschäftsbedingungen </p>
                 <p> haben wir Ihr Konto geschlossen. Eine Reaktivierung ist nur auf Antrag über unser Kundendienstteam </p>
                 <p> möglich.</p>
                 <p> Sollten Sie weitere Fragen haben, bitten wir Sie, sich an unser <a class=\"underlineLetters\" href=\"{$support_link}\">Kundendienst-Service-Team</a> zu wenden. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Mit freundlichen Grüßen, </p>
                 <p> Ihr <span class=\"boldLetters\">{$casino_name}</span> Team </p>";
                break;
            case 'cs_CZ':
                $mail_title = "{$casino_name} – Váš zákaznický účet byl zabanován";
                $mail_message =
                "<p> Vážený/á <span class=\"boldLetters\">{$player_username}</span>, </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> V posledních <span class=\"boldLetters\">{$inactive_time} dnech jste se nepřihlásili na Váš zákaznický účet</span>. </p>
                 <p> Na základě našeho písmného upozornění před 10 dny a naších „všeobecných podmínek“ byl Váš účet </p>
                 <p> zabanován(uzavřen). Reaktivace je možná pouze přes náš zákaznický servis. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> S přátelským pozdravem, </p>
                 <p> Váš <span class=\"boldLetters\">{$casino_name}</span> tým. </p>";
                break;
            case 'sv_SE':
                $mail_title = "{$casino_name} – ditt konto hos oss har stängts";
                $mail_message =
                "<p> Kära <span class=\"boldLetters\">{$player_username}</span>, </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Du har inte varit aktiv på ditt konto på <span class=\"boldLetters\">{$inactive_time} dagar</span>. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Enligt våran påminnelse för 10 dagar sen och våra \"Regler och Villkor\" så har vi stängt ditt. </p>
                 <p> konto. </p>
                 <p> Om du vill öppna ditt konto igen måste du ta kontakt med våran support. </p>
                 <p> Om du har några frågor, tveka inte att kontakta vår kundsupport för hjälp. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Vänligen, </p>
                 <p> <span class=\"boldLetters\">{$casino_name}</span> supporten </p>";
                break;
            case 'rs_RS':
                $mail_title = "{$casino_name} - vaš nalog je zatvoren";
                $mail_message =
                "<p> Poštovani/a <span class=\"boldLetters\">{$player_username}</span>, </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Niste bili ulogovani na Vaš nalog ukupno <span class=\"boldLetters\">{$inactive_time} dana</span>. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Prema našem podsetniku poslatom pre 10 dana i našim <a class=\"underlineLetters\" href=\"{$support_link}\">\"Uslovima korišćenja\"</a> zatvorili smo Vaš nalog. </p>
                 <p> Otvaranje je moguće samo uz posredovanje našeg tima korisničke podrške. </p>
                 <p> Ukoliko imate nedoumica, molimo Vas da kontaktirate naš <a class=\"underlineLetters\" href=\"{$support_link}\">tim korisničke podrške</a> za pomoć. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Iskreno Vaš, </p>
                 <p> <span class=\"boldLetters\">{$casino_name}</span> tim podrške </p>";
                break;
            case 'en_GB':
            default:
                $mail_title = "{$casino_name} - your account has been closed";
                $mail_message =
                "<p> Dear <span class=\"boldLetters\">{$player_username}</span>, </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> You have not been logged into your account for <span class=\"boldLetters\">{$inactive_time} days</span>. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> According to our reminder 10 days ago and our <a class=\"underlineLetters\" href=\"{$support_link}\">\"Terms and Conditions\"</a> we closed your account. </p>
                 <p> A reopening will be possible only by request to our support team. </p>
                 <p> If you have any concerns, please contact our <a class=\"underlineLetters\" href=\"{$support_link}\">customer support team</a> for assistance. </p>
                 <p class=\"smallLetters\"> &nbsp; </p>
                 <p> Yours sincerly, </p>
                 <p> The <span class=\"boldLetters\">{$casino_name}</span> support team </p>";
        }
		$message = self::getMailBody($language_settings, $mail_message, $site_images_location, $mail_title, $background_color, $text_color, $small_text_color, $footer_background,
				$text_font_size, $small_text_font_size,
				$hyperlink_text_color, $hyperlink_font_size,
				$hyperlink_small_text_color, $hyperlink_small_font_size);
		return array("mail_title"=>$mail_title, "mail_message"=>$message);
	}
    //////////////////////////////////////////

	//custom mail to player
	public static function getCustomMailContent($mail_title, $mail_content, $site_images_location,
	$background_color = "#FFFFFF", $text_color = "#000000", $small_text_color = "#000000", $footer_background = "#FFFFFF",
	$text_font_size = "15", $small_text_font_size = "8",
	$hyperlink_text_color = "#FFFFFF", $hyperlink_font_size = "15",
	$hyperlink_small_text_color = "#FFFFFF", $hyperlink_small_font_size = "8"){
		$date_processed = date('d-m-Y H:i:s');

        $mail_content = urldecode($mail_content);
		$mail_message =
            "<div style='font-size: {$text_font_size}px; text-align: left; font-family: Calibri, Arial, Helvetica, sans-serif; font-weight: normal; display: block; color: {$text_color}; margin: 1px; line-height: 20px;'>"
            . "{$mail_content}" .
            "</div>";
        ;
		$message = self::getMailBody("en_GB", $mail_message, $site_images_location, $mail_title, $background_color, $text_color, $small_text_color, $footer_background,
				$text_font_size, $small_text_font_size,
				$hyperlink_text_color, $hyperlink_font_size,
				$hyperlink_small_text_color, $hyperlink_small_font_size);
		return $message;
	}

	//custom mail content to send to administrator
	public static function getAdministratorMailContent($mail_title, $mail_content){
		$date_processed = date('d-m-Y H:i:s');

        $mail_content = urldecode($mail_content);
		$mail_message =
            "<div style='font-size: 15px; text-align: left; font-family: Calibri, Arial, Helvetica, sans-serif; font-weight: normal; display: block; color: black; margin: 1px; line-height: 20px;'>"
            . "{$mail_content}" .
            "</div>";
        ;
		$message = self::getAdministratorMailBody($mail_message, $mail_title, $date_processed);
		return $message;
	}

	//send mail to player to his email address
	//sends automatic mails
	public static function sendMailToPlayer($player_mail_send_from, $player_mail_address,
	$player_smtp_server, $player_mail_to_title, $player_mail_content, $playerMailFromTitle,
	$player_mail_subject_title, $logger_message, $site_images_location = ""){
		try{
            $playerMailFromTitle = "";
            $playerSmtpConfig = array(
                "port"=>25,
                //"ssl"=>"ssl",
                /*'auth' => 'login',
                'username' => $player_mail_send_from,
                'password' => 'NoNo24RePlY'*/
            );
			$tr = new Zend_Mail_Transport_Smtp($player_smtp_server, $playerSmtpConfig);
			$mail = new Zend_Mail('UTF-8');
			$recipients_arr = explode(',', $player_mail_address);
			$mail->addTo($recipients_arr, $player_mail_to_title);
			$mail->setBodyHtml($player_mail_content);
			$mail->setFrom($player_mail_send_from, $playerMailFromTitle);
            //$mail->setReplyTo($player_mail_send_from);
			$mail->setSubject($player_mail_subject_title);

            //extract server sending mail domain (email @ xlntcasino.com)

            $player_mail_send_fromArr = explode('@', $player_mail_send_from);
            $header_message_id = time() . '-' . md5($player_mail_send_from . $player_mail_address) . '@' . $player_mail_send_fromArr[1];
            $header_precendence = "bulk";
            $mail->setMessageId($header_message_id);
            //$mail->addHeader("Precedence", $header_precendence);


			//////////add attachments to mails sent to players/////////////
			/*
			$header_image = $site_images_location . self::$header_img;
			$footer_image = $site_images_location . self::$footer_img;
			$background_image = $site_images_location . self::$bg_img;

			$fileContentsHeaderImg = file_get_contents($header_image);
			$atHeader = $mail->createAttachment($fileContentsHeaderImg);
			$atHeader->id = md5($header_image);
			$atHeader->type = 'image/jpeg';
			$atHeader->disposition = Zend_Mime::DISPOSITION_INLINE;
			$atHeader->encoding    = Zend_Mime::ENCODING_BASE64;
			$atHeader->filename    = self::$header_img;

			$fileContentsFooterImg = file_get_contents($footer_image);
			$atFooter = $mail->createAttachment($fileContentsFooterImg);
			$atFooter->id = md5($footer_image);
			$atFooter->type = 'image/jpeg';
			$atFooter->disposition = Zend_Mime::DISPOSITION_INLINE;
			$atFooter->encoding    = Zend_Mime::ENCODING_BASE64;
			$atFooter->filename    = self::$footer_img;

			$fileContentsBackgroundImg = file_get_contents($background_image);
			$atBackground = $mail->createAttachment($fileContentsBackgroundImg);
			$atBackground->id = md5($background_image);
			$atBackground->type = 'image/jpeg';
			$atBackground->disposition = Zend_Mime::DISPOSITION_INLINE;
			$atBackground->encoding    = Zend_Mime::ENCODING_BASE64;
			$atBackground->filename    = self::$bg_img;
			*/
			/////////////////
			Zend_Mail::setDefaultTransport($tr);
			$mail->send();

            ///additional sending mails for players to support account(s)
            $config = Zend_Registry::get('config');
            if($config->sendMailsForPlayerToSupport == "true") {
                $supportSmtpConfig = array(
                    "port"=>25,
                    //"ssl"=>"ssl"
                    //"port"=>$config->playerToSupportMailStmpServerPort,
                );
                $transportToSupport = new Zend_Mail_Transport_Smtp($player_smtp_server, $supportSmtpConfig);
                $mail_toSupport = new Zend_Mail('UTF-8');
                $recipientsArray = explode(',', $config->playerToSupportMailSendErrorTo);
                $mail_toSupport->addTo($recipientsArray, $player_mail_to_title);
                $mail_toSupport->setBodyHtml($player_mail_content);
                //$mail_toSupport->setFrom($config->playerToSupportMailSendErrorFrom, $playerMailFromTitle);
                $mail_toSupport->setFrom($player_mail_send_from, $playerMailFromTitle);
                //$mail_toSupport->setReplyTo($player_mail_send_from);
                $mail_toSupport->setSubject($player_mail_subject_title);
                Zend_Mail::setDefaultTransport($transportToSupport);
			    $mail_toSupport->send();
            }
		}catch(Zend_Exception $ex){
			require_once HELPERS_DIR . DS . 'ErrorHelper.php';
			$errorHelper = new ErrorHelper();
			$errorHelper->sendMail(CursorToArrayHelper::getExceptionTraceAsString($ex));
			$errorHelper->siteErrorLog(CursorToArrayHelper::getExceptionTraceAsString($ex));
		}
	}

	//send mail to administrator from customer
	//sends mails received from backoffice
	public static function sendMailToAdministrator($mail_to, $mail_from, $mail_title, $mail_content){
		try{
			$config = Zend_Registry::get('config');
			$tr = new Zend_Mail_Transport_Smtp($config->smtpServer);
			$mail = new Zend_Mail('UTF-8');
            $recipients_arr = explode(",", $mail_to);
			$mail->addTo($recipients_arr, $mail_title);
			$mail->setBodyHtml($mail_content);
			$mail->setFrom($mail_from, $mail_title);
			$mail->setSubject($mail_title);
			Zend_Mail::setDefaultTransport($tr);
			$mail->send();
		}catch(Zend_Exception $ex){
			require_once HELPERS_DIR . DS . 'ErrorHelper.php';
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->siteError($message, $message);
		}
	}

    private static function translatePaymentMethod($payment_method){
        $translated_payment_method = $payment_method;
        switch (strtoupper($payment_method)) {
            case strtoupper('Envoy'):
                $translated_payment_method = "Envoy";
                break;
            case strtoupper('Maestro'):
                $translated_payment_method = "Maestro";
                break;
            case strtoupper('Mastercard'):
                $translated_payment_method = "MasterCard";
                break;
            case strtoupper('Visa'):
                $translated_payment_method = "Visa";
                break;
            case strtoupper('Sofort'):
                $translated_payment_method = "Sofort Banking";
                break;
            case strtoupper('NT'):
            case strtoupper('Neteller'):
                $translated_payment_method = "Neteller";
                break;
            case strtoupper('PSC'):
            case strtoupper('Paysafecard'):
                $translated_payment_method = "Paysafecard";
                break;
            case strtoupper('MBKR'):
                $translated_payment_method = "Skrill";
                break;
            case strtoupper('UKASH'):
                $translated_payment_method = "UKash";
                break;
            case strtoupper('ECOW'):
                $translated_payment_method = "Eco Card Wallet";
                break;
            case strtoupper('Entercash'):
                $translated_payment_method = "Entercash";
                break;
            case strtoupper('MasterCard Secure'):
            case strtoupper('MASTERCARDSECURE'):
                $translated_payment_method = "MasterCard Secure";
                break;
            case strtoupper('Przelewy24'):
            case strtoupper('Przlewy'):
                $translated_payment_method = "Przelewy24";
                break;
            case strtoupper('QIWI Wallet'):
            case strtoupper('QIWI'):
                $translated_payment_method = "QIWI Wallet";
                break;
            case strtoupper('SEPA'):
                $translated_payment_method = "SEPA";
                break;
            case strtoupper('SOFORT'):
                $translated_payment_method = "Sofort";
                break;
            case strtoupper('Trustpay'):
                $translated_payment_method = "TrustPay";
                break;
            case strtoupper("Trustly"):
                $translated_payment_method = "Trustly";
                break;
            case strtoupper("Visa 3D"):
            case strtoupper("VISA3D"):
                $translated_payment_method = "Visa 3D";
                break;
            case strtoupper("Zimpler"):
                $translated_payment_method = "Zimpler";
                break;
            case strtoupper("iDEAL"):
                $translated_payment_method = "iDEAL";
                break;
            case strtoupper("Euteller"):
                $translated_payment_method = "Euteller";
                break;
            default:
                $translated_payment_method = $payment_method;
        }
        return $translated_payment_method;
    }
}
