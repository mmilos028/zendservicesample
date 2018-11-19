<?php
require_once HELPERS_DIR . DS . 'ApplicationConstants.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
require_once HELPERS_DIR . DS . 'IPHelper.php';

/**
 *
 * Web site web service main calls ...
 *
 */

class Rest_WebSiteController extends Zend_Controller_Action
{


    public function init()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();
        //set output header Content-Type to application/json
        header('Content-Type: application/json');
    }

    public function preDispatch()
    {
        header("Access-Control-Allow-Origin: *");
        if ($_SERVER['REQUEST_METHOD'] != "POST") {
            $config = Zend_Registry::get('config');
            if ($config->onlinecasinoserviceWSDLMode == "false" || $config->onlinecasinoserviceWSDLMode == false) { // not in wsdl mode
                $response = array(
                    "status" => NOK,
                    "message" => NOK_POST_METHOD_MESSAGE
                );
                exit(Zend_Json::encode($response));
            } else {
                $message =
                    "\n\n /onlinecasinoservice/rest/web-site ";
                exit($message);
            }
        }
    }

    public function indexAction()
    {
        $config = Zend_Registry::get('config');
        if ($config->onlinecasinoserviceWSDLMode == "false" || $config->onlinecasinoserviceWSDLMode == false) { // not in wsdl mode
            header('Location: http://www.google.com/');
        }
    }

    /**
     *
     * setTimeModified on web site action (user changes page)
     * @return mixed
     */
    public function setTimeModifiedAction()
    {
        $site_session_id = strip_tags($this->getRequest()->getParam('site_session_id', null));
        $pc_session_id = strip_tags($this->getRequest()->getParam('pc_session_id', null));
        require_once "services" . DS . "WebSiteManager.php";
        WebSiteManager::setTimeModified($site_session_id, $pc_session_id);
    }

    /**
     * validateSiteSession method by using site session id
     * @return mixed
     */
    public function validateSiteSessionAction()
    {
        $site_session_id = strip_tags($this->getRequest()->getParam('site_session_id', null));
        $pc_session_id = strip_tags($this->getRequest()->getParam('pc_session_id', null));

        require_once "services" . DS . "WebSiteManager.php";
        WebSiteManager::validateSiteSession($site_session_id, $pc_session_id);
    }

    /**
     * list all games for anonymous session
     * @return mixed
     */
    public function listAnonymousGamesAction()
    {
        $affiliate_name = strip_tags($this->getRequest()->getParam('affiliate_name', null));
        $ip_address = strip_tags($this->getRequest()->getParam('ip_address', null));

        require_once "services" . DS . "WebSiteManager.php";
        WebSiteManager::listAnonymousGames($affiliate_name, $ip_address);
    }

    /**
     * list all games for anonymous session for mobile platform
     * @return mixed
     */
    public function listAnonymousGamesForMobileAction()
    {
        $affiliate_name = strip_tags($this->getRequest()->getParam('affiliate_name', null));
        $ip_address = strip_tags($this->getRequest()->getParam('ip_address', null));

        require_once "services" . DS . "WebSiteManager.php";
        WebSiteManager::listAnonymousGamesForMobile($affiliate_name, $ip_address);
    }

    /**
     * list player favourite games
     * @return mixed
     */
    public function listPlayerFavouriteGamesAction()
    {
        $player_id = strip_tags($this->getRequest()->getParam('player_id', null));
        $ip_address = strip_tags($this->getRequest()->getParam('ip_address', null));

        require_once "services" . DS . "WebSiteManager.php";
        WebSiteManager::listPlayerFavouriteGames($player_id, $ip_address);
    }

    /**
     * open anonymous session
     * @return mixed
     */
    public function openAnonymousSessionAction()
    {
        $ip_address = strip_tags($this->getRequest()->getParam('ip_address', null));

        require_once "services" . DS . "WebSiteManager.php";
        WebSiteManager::openAnonymousSession($ip_address);
    }

    /**
     * siteLogoutPC method by using pc session
     * @return mixed
     */
    public function siteLogoutPCAction()
    {
        $session_id = strip_tags($this->getRequest()->getParam('session_id', null));

        require_once "services" . DS . "WebSiteManager.php";
        WebSiteManager::siteLogoutPC($session_id);
    }

    /**
     * siteLogout method by using pc session or site session
     * @return mixed
     */
    public function siteLogoutAction()
    {
        $site_session_id = strip_tags($this->getRequest()->getParam('site_session_id', null));
        $pc_session_id = strip_tags($this->getRequest()->getParam('pc_session_id', null));

        require_once "services" . DS . "WebSiteManager.php";
        WebSiteManager::siteLogoutPC($site_session_id, $pc_session_id);
    }

    /**
     * siteLoginPC method by using pc session
     * @return mixed
     */
    public function siteLoginPCAction()
    {
        $username = strip_tags($this->getRequest()->getParam('username', null));
        $password = strip_tags($this->getRequest()->getParam('password', null));
        $mac_address = strip_tags($this->getRequest()->getParam('mac_address', null));
        $version = strip_tags($this->getRequest()->getParam('version', null));
        $ip_address = strip_tags($this->getRequest()->getParam('ip_address', null));
        $country = strip_tags($this->getRequest()->getParam('country', null));
        $city = strip_tags($this->getRequest()->getParam('city', null));

        require_once "services" . DS . "WebSiteManager.php";
        WebSiteManager::siteLoginPC($username, $password, $mac_address, $version, $ip_address, $country, $city);
    }

    /**
     * siteLogin method
     * @return mixed
     */
    public function siteLoginAction()
    {
        $site_name = strip_tags($this->getRequest()->getParam('site_name', null));
        $username = strip_tags($this->getRequest()->getParam('username', null));
        $password = strip_tags($this->getRequest()->getParam('password', null));
        $mac_address = strip_tags($this->getRequest()->getParam('mac_address', null));
        $version = strip_tags($this->getRequest()->getParam('version', null));
        $ip_address = strip_tags($this->getRequest()->getParam('ip_address', null));
        $country = strip_tags($this->getRequest()->getParam('country', null));
        $city = strip_tags($this->getRequest()->getParam('city', null));
        $device_aff_id = strip_tags($this->getRequest()->getParam('device_aff_id', null));
        $gp_mac_address = strip_tags($this->getRequest()->getParam('gp_mac_address', null));

        require_once "services" . DS . "WebSiteManager.php";
        WebSiteManager::siteLogin($site_name, $username, $password, $mac_address, $version, $ip_address, $country, $city, $device_aff_id, $gp_mac_address);
    }

    /**
     * listCountries method
     * @return mixed
     */
    public function listCountriesAction()
    {
        require_once "services" . DS . "WebSiteManager.php";
        WebSiteManager::listCountries();
    }

    /**
     * listCountries method
     * @return mixed
     */
    public function listCountriesAllowedForPlayerAction()
    {
        $white_label_id = strip_tags($this->getRequest()->getParam('white_label_id', null));
        require_once "services" . DS . "WebSiteManager.php";
        WebSiteManager::listCountriesAllowedForPlayer($white_label_id);
    }

    /**
     * Currency for promo code
     * @return mixed
     */
    public function currencyForPromoCodeAction()
    {
        $affiliate_name = strip_tags($this->getRequest()->getParam('affiliate_name', null));
        require_once "services" . DS . "WebSiteManager.php";
        WebSiteManager::currencyForPromoCode($affiliate_name);
    }

    /**
     *
     * Opens player session for players coming from external client integration to our system
     * Returns list of games and parameters
     * Game (param1, param2, ... paramN) and so on
     * @return mixed
     */
    public function loginExternalIntegrationAction()
    {
        $token = strip_tags($this->getRequest()->getParam('token', null));
        $ip_address = strip_tags($this->getRequest()->getParam('ip_address', null));
        require_once "services" . DS . "WebSiteManager.php";
        WebSiteManager::loginExternalIntegration($token, $ip_address);
    }

    /**
     * Returns number of games per page for web site
     * @return mixed
     */
    public function getNumberOfGamesPerPageAction()
    {
        $white_label_name = strip_tags($this->getRequest()->getParam('white_label_name', null));
        require_once "services" . DS . "WebSiteManager.php";
        WebSiteManager::getNumberOfGamesPerPage($white_label_name);
    }

    /**
     * check if country is prohibited
     * @return mixed
     */
    public function checkIfCountryIsProhibitedAction()
    {
        $affiliate_id = strip_tags($this->getRequest()->getParam('affiliate_id', null));
        $ip_address = strip_tags($this->getRequest()->getParam('ip_address', null));
        require_once "services" . DS . "WebSiteManager.php";
        WebSiteManager::checkIfCountryIsProhibited($affiliate_id, $ip_address);
    }
}
