<?php
/**
 * Integration tests for login functionality
 */
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\WebDriverBy;

class RegisterPlayerOnWebSiteTest extends PHPUnit\Framework\TestCase
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
        //$this->setBrowserUrl(getenv('WEB_SITE_URL'));
        $this->webDriver->get(getenv('WEB_SITE_URL'));
    }

    public function testPlayerRegistrationOnWebSite()  {

        $username = getenv('BACKOFFICE_USERNAME');
        $password = getenv('BACKOFFICE_PASSWORD');

        //$this->url("/");
        sleep(5);
        $registrationButton = $this->webDriver->findElement(WebDriverBy::linkText("REGISTRATION"));
        $registrationButton->click();

        sleep(5);

        //$this->byId("username")->value("Username");

        $random_username = HelperClass::player_random_name('TstPl', '', 6);

        $iframe = $this->webDriver->switchTo()->frame("register_iframe");
        sleep(5);

            $this->webDriver->findElement(WebDriverBy::id("username"))->sendKeys($random_username);
            $this->webDriver->findElement(WebDriverBy::id("password"))->sendKeys($random_username . "123");
            $this->webDriver->findElement(WebDriverBy::id("confirm_password"))->sendKeys($random_username . "123");
            $this->webDriver->findElement(WebDriverBy::id("email"))->sendKeys($random_username . "@" . "sharklasers.com");
            $this->webDriver->findElement(WebDriverBy::id("confirm_email"))->sendKeys($random_username . "@" . "sharklasers.com");
            $selectCurrency = new \Facebook\WebDriver\WebDriverSelect($this->webDriver->findElement(WebDriverBy::id("currency")));
            $selectCurrency->selectByVisiblePartialText(getenv("CURRENCY"));

            $selectLanguage = new \Facebook\WebDriver\WebDriverSelect($this->webDriver->findElement(WebDriverBy::id("language")));
            $selectLanguage->selectByVisiblePartialText("ENGLISH");

            $this->webDriver->findElement(WebDriverBy::id("btn_next"))->click();
            sleep(5);

            $this->webDriver->findElement(WebDriverBy::id("first_name"))->sendKeys("first name " . $random_username);
            $this->webDriver->findElement(WebDriverBy::id("last_name"))->sendKeys("last name " . $random_username);

            $selectDay = new \Facebook\WebDriver\WebDriverSelect($this->webDriver->findElement(WebDriverBy::id("day")));
            $selectDay->selectByVisiblePartialText(HelperClass::random_number('', '', 1, 27));

            $selectMonth = new \Facebook\WebDriver\WebDriverSelect($this->webDriver->findElement(WebDriverBy::id("month")));
            $selectMonth->selectByVisiblePartialText("May");

            $selectYear = new \Facebook\WebDriver\WebDriverSelect($this->webDriver->findElement(WebDriverBy::id("year")));
            $selectYear->selectByVisiblePartialText(HelperClass::random_number('', '', 1930, 1999));

            $this->webDriver->findElement(WebDriverBy::id("btn_next"))->click();
            sleep(5);

            $selectCountry = new \Facebook\WebDriver\WebDriverSelect($this->webDriver->findElement(WebDriverBy::id("country")));
            $selectCountry->selectByVisiblePartialText("Serbia");

            $this->webDriver->findElement(WebDriverBy::id("zip_code"))->sendKeys(HelperClass::random_number('', '', 10000, 25000));

            $this->webDriver->findElement(WebDriverBy::id("city"))->sendKeys("Belgrade");

            $this->webDriver->findElement(WebDriverBy::id("address_1"))->sendKeys("Street Address " . HelperClass::random_number('', '', 1, 10000));

            $this->webDriver->findElement(WebDriverBy::id("phone_number"))->sendKeys(HelperClass::random_number('', '', "1000000", "9999999"));

            $selectSecurityQuestion = new \Facebook\WebDriver\WebDriverSelect($this->webDriver->findElement(WebDriverBy::id("security_question")));
            $selectSecurityQuestion->selectByVisiblePartialText("In which city did you study abroad?");

            $this->webDriver->findElement(WebDriverBy::id("answer"))->sendKeys("Belgrade");




        sleep(15);


        $this->webDriver->switchTo()->defaultContent();
        sleep(5);
    }

    public function tearDown()
    {
        $this->webDriver->close();
        //$this->stop();
    }

} //LoginTest class