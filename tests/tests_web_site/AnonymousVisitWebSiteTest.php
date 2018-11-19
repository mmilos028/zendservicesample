<?php
/**
 * Integration tests for login functionality
 */
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\WebDriverBy;

class AnonymousVisitWebSiteTest extends PHPUnit\Framework\TestCase
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

    public function testAnonymousVisitWebSite()  {
        //$this->url("/");


        sleep(2);
        $gamesButton = $this->webDriver->findElement(WebDriverBy::linkText("GAMES"));
        $gamesButton->click();

        sleep(1);

        $promoButton = $this->webDriver->findElement(WebDriverBy::linkText("PROMO"));
        $promoButton->click();

        sleep(1);

        $promoButton = $this->webDriver->findElement(WebDriverBy::linkText("PROMO"));
        $promoButton->click();

        $this->webDriver->get(getenv('WEB_SITE_URL'));
    }

    public function tearDown()
    {
        $this->webDriver->close();
    }

} //LoginTest class