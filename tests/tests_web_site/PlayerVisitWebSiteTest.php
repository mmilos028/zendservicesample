<?php
/**
 * Integration tests for login functionality
 */
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\WebDriverBy;

class PlayerVisitWebSiteTest extends PHPUnit\Framework\TestCase
{

    /**
     * Defines which browsers are going to be tested
     * @var array
     */
    public static $browsers = array(
        array(
            "name" => "Chrome",
            "browserName" => "chrome",
        ),
    );

    /**
     * @var RemoteWebDriver
     */
    protected $webDriver;

    /**
     * setup will be run for all our tests
     */
    protected function setUp()  {
        $capabilities = array(
            WebDriverCapabilityType::BROWSER_NAME => 'chrome',
        );

        /**
         * @var RemoteWebDriver
         */
        $this->webDriver = RemoteWebDriver::create('http://127.0.0.1:4444/wd/hub', $capabilities);
        $this->webDriver->manage()->window()->maximize();
        $this->webDriver->get(getenv('WEB_SITE_URL'));
    }

    public function testPlayerRegistrationOnWebSite()  {

        $loginButton = $this->webDriver->findElement(WebDriverBy::linkText("LOGIN"));
        $loginButton->click();

        sleep(3);

        $player_username = getenv("PLAYER_USERNAME");
        $player_password = getenv("PLAYER_PASSWORD");
        $this->webDriver->findElement(WebDriverBy::id("ff_username"))->sendKeys($player_username);
        $this->webDriver->findElement(WebDriverBy::id("ff_password"))->sendKeys($player_username);
        $this->webDriver->findElement(WebDriverBy::id("ff_btn_login"))->click();

        sleep(5);

        $accountButton = $this->webDriver->findElement(WebDriverBy::linkText("ACCOUNT"));
        $accountButton->click();

            $personalInformationButton = $this->webDriver->findElement(WebDriverBy::linkText("Personal Information"));
            $personalInformationButton->click();

            sleep(5);

            $accountInformationButton = $this->webDriver->findElement(WebDriverBy::linkText("Account Information"));
            $accountInformationButton->click();

            sleep(5);

            $documentsUploadButton = $this->webDriver->findElement(WebDriverBy::linkText("Documents Upload"));
            $documentsUploadButton->click();

            sleep(5);

            $changePasswordButton = $this->webDriver->findElement(WebDriverBy::linkText("Change password"));
            $changePasswordButton->click();

            sleep(5);

            $securityQuestionButton = $this->webDriver->findElement(WebDriverBy::linkText("Security Question"));
            $securityQuestionButton->click();

            sleep(5);

            $responsibleGamingButton = $this->webDriver->findElement(WebDriverBy::linkText("RESPONSIBLE GAMING"));
            $responsibleGamingButton->click();

            sleep(5);

            $depositWithdrawButton = $this->webDriver->findElement(WebDriverBy::linkText("Deposit & Withdraw"));
            $depositWithdrawButton->click();

            sleep(5);

            $transactionsHistoryButton = $this->webDriver->findElement(WebDriverBy::linkText("Transactions History"));
            $transactionsHistoryButton->click();

            sleep(5);

            $pastTime = time() - 6 * 30 * 86400;
            $start_date = date('d-M-Y', $pastTime);
            $end_date = date('d-M-Y', time());

            $this->webDriver->findElement(WebDriverBy::id("start_date_transactions"))->sendKeys($start_date);
            $this->webDriver->findElement(WebDriverBy::id("end_date_transactions"))->sendKeys($end_date);
            $this->webDriver->findElement(WebDriverBy::id("search_transactions_btn"))->click();

            $gamingHistoryButton = $this->webDriver->findElement(WebDriverBy::linkText("Gaming History"));
            $gamingHistoryButton->click();

            sleep(5);

            $this->webDriver->findElement(WebDriverBy::id("start_date_search"))->sendKeys($start_date);
            $this->webDriver->findElement(WebDriverBy::id("end_date_search"))->sendKeys($end_date);
            $this->webDriver->findElement(WebDriverBy::id("search_btn"))->click();

            $bonusAndPromotionsButton = $this->webDriver->findElement(WebDriverBy::linkText("Bonus & Promotions"));
            $bonusAndPromotionsButton->click();

            sleep(5);

            $gamesButton = $this->webDriver->findElement(WebDriverBy::linkText("GAMES"));
            $gamesButton->click();

            sleep(5);

            $promoButton = $this->webDriver->findElement(WebDriverBy::linkText("PROMO"));
            $promoButton->click();

            sleep(5);

            $depositButton = $this->webDriver->findElement(WebDriverBy::linkText("DEPOSIT"));
            $depositButton->click();
    }

    public function tearDown()
    {
        $this->webDriver->close();
    }

} //LoginTest class