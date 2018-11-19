<?php
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

require_once "helpers/HelperClass.php";

class BasicWebSiteTest extends PHPUnit\Framework\TestCase
{

    /**
     * setup will be run for all our tests
     */
    protected function setUp()  {

    } // setUp()

    /**
     * Test that logins work
     *
     */

    public function testVisitWebSite()  {

        $url = getenv('WEB_SERVICE_URL') . '/rest';
        $client = new Client(['headers' => [ 'Content-Type' => 'application/json' ]]);

        $site_name = getenv('SITE_NAME');
        $affiliate_username = getenv('AFFILIATE_USERNAME');
        $white_label_username = getenv('WHITE_LABEL_USERNAME');
        $username = getenv('PLAYER_USERNAME');
        $password = getenv('PLAYER_PASSWORD');
        $ip_address = getenv('WEB_SITE_IP_ADDRESS');
        $start_date = getenv('START_DATE');
        $end_date = getenv('END_DATE');
        $currency = getenv('CURRENCY');

        //SITE LOGIN
        $data = array(
            'operation' => 'site-login',
            'site_name' => $site_name,
            'username' => $username,
            'password' => $password,
            'mac_address' => '',
            'version' => '',
            'ip_address' => $ip_address,
            'country' => '',
            'city' => '',
        );

        $response_site_login = $client->post($url, ['body' => json_encode($data)]);

        $this->assertEquals(200, $response_site_login->getStatusCode());
        //print_r($response->getBody());
        $response_data_site_login = json_decode($response_site_login->getBody(), true);
        //print_r($response_data);

        $this->assertArrayHasKey('status', $response_data_site_login);
        $this->assertEquals('OK', $response_data_site_login['status']);

        //VALIDATE SITE SESSION
        $data = array(
            'operation' => 'validate-site-session',
            'site_session_id' => $response_data_site_login['site_session_id'],
            'pc_session_id' => $response_data_site_login['pc_session_id']
        );

        $response_validate_site_session = $client->post($url, ['body' => json_encode($data)]);

        $this->assertEquals(200, $response_validate_site_session->getStatusCode());
        $response_data_validate_site_session = json_decode($response_validate_site_session->getBody(), true);

        $this->assertArrayHasKey('status', $response_data_validate_site_session);
        $this->assertEquals('OK', $response_data_validate_site_session['status']);

        //PLAYER DETAILS
        $data = array(
            'operation' => 'player-details',
            'ip_address' => $ip_address,
            'session_id' => $response_data_site_login['pc_session_id']
        );

        $response_player_details = $client->post($url, ['body' => json_encode($data)]);

        $this->assertEquals(200, $response_player_details->getStatusCode());
        $response_data_player_details = json_decode($response_player_details->getBody(), true);

        $this->assertArrayHasKey('status', $response_data_player_details);
        $this->assertEquals('OK', $response_data_player_details['status']);

        //TRANSACTIONS HISTORY
        $data = array(
            'operation' => 'list-player-history',
            'session_id' => $response_data_site_login['pc_session_id'],
            'start_date' => $start_date,
            'end_date' => $end_date,
            'page_number' => 1,
            'per_page' => 200,
            'column' => 1,
            'order' => 'asc'
        );

        $response_transactions_history = $client->post($url, ['body' => json_encode($data)]);

        $this->assertEquals(200, $response_transactions_history->getStatusCode());
        $response_data_transactions_history = json_decode($response_transactions_history->getBody(), true);

        $this->assertArrayHasKey('status', $response_data_transactions_history);
        $this->assertEquals('OK', $response_data_transactions_history['status']);

        //CREDIT TRANSFERS
        $data = array(
            'operation' => 'list-credit-transfers',
            'session_id' => $response_data_site_login['pc_session_id'],
            'start_date' => $start_date,
            'end_date' => $end_date,
            'page_number' => 1,
            'per_page' => 200,
            'column' => 1,
            'order' => 'asc'
        );

        $response_list_credit_transfers = $client->post($url, ['body' => json_encode($data)]);

        $this->assertEquals(200, $response_list_credit_transfers->getStatusCode());
        $response_data_list_credit_transfers = json_decode($response_list_credit_transfers->getBody(), true);

        $this->assertArrayHasKey('status', $response_data_list_credit_transfers);
        $this->assertEquals('OK', $response_data_list_credit_transfers['status']);

        //AVAILABLE BONUS CAMPAIGNS
        $data = array(
            'operation' => 'list-available-bonus-campaigns',
            'affiliate_username' => $white_label_username,
            'player_id' => $response_data_site_login['player_id'],
        );

        $response_list_available_bonus_campaigns = $client->post($url, ['body' => json_encode($data)]);

        $this->assertEquals(200, $response_list_available_bonus_campaigns->getStatusCode());
        $response_data_list_available_bonus_campaigns = json_decode($response_list_available_bonus_campaigns->getBody(), true);

        $this->assertArrayHasKey('status', $response_data_list_available_bonus_campaigns);
        $this->assertEquals('OK', $response_data_list_available_bonus_campaigns['status']);

        //PLAYER AVAILABLE BONUS CAMPAIGNS
        $data = array(
            'operation' => 'list-player-available-bonus-campaigns',
            'player_id' => $response_data_site_login['player_id'],
        );

        $response_list_player_available_bonus_campaigns = $client->post($url, ['body' => json_encode($data)]);

        $this->assertEquals(200, $response_list_player_available_bonus_campaigns->getStatusCode());
        $response_data_list_player_available_bonus_campaigns = json_decode($response_list_player_available_bonus_campaigns->getBody(), true);

        $this->assertArrayHasKey('status', $response_data_list_player_available_bonus_campaigns);
        $this->assertEquals('OK', $response_data_list_player_available_bonus_campaigns['status']);

        //PLAYER AVAILABLE ACTIVE BONUSES AND PROMOTIONS
        $data = array(
            'operation' => 'list-player-active-bonuses-and-promotions',
            'player_id' => $response_data_site_login['player_id'],
        );

        $response_list_player_available_active_bonuses_and_promotions = $client->post($url, ['body' => json_encode($data)]);

        $this->assertEquals(200, $response_list_player_available_active_bonuses_and_promotions->getStatusCode());
        $response_data_list_player_available_active_bonuses_and_promotions = json_decode($response_list_player_available_active_bonuses_and_promotions->getBody(), true);

        $this->assertArrayHasKey('status', $response_data_list_player_available_active_bonuses_and_promotions);
        $this->assertEquals('OK', $response_data_list_player_available_active_bonuses_and_promotions['status']);

        //TOP WON JACKPOTS
        $data = array(
            'operation' => 'list-top-won-jackpots',
            'affiliate_name' => $white_label_username,
        );

        $response_list_top_won_jackpots = $client->post($url, ['body' => json_encode($data)]);

        $this->assertEquals(200, $response_list_top_won_jackpots->getStatusCode());
        $response_data_list_top_won_jackpots = json_decode($response_list_top_won_jackpots->getBody(), true);

        $this->assertArrayHasKey('status', $response_data_list_top_won_jackpots);
        $this->assertEquals('OK', $response_data_list_top_won_jackpots['status']);

        //CURRENT JACKPOT LEVELS
        $data = array(
            'operation' => 'list-current-jackpot-levels',
            'affiliate_name' => $white_label_username,
            'currency' => $currency
        );

        $response_list_current_jackpot_levels = $client->post($url, ['body' => json_encode($data)]);

        $this->assertEquals(200, $response_list_current_jackpot_levels->getStatusCode());
        $response_data_list_current_jackpot_levels = json_decode($response_list_current_jackpot_levels->getBody(), true);

        $this->assertArrayHasKey('status', $response_data_list_current_jackpot_levels);
        $this->assertEquals('OK', $response_data_list_current_jackpot_levels['status']);

        //HIGH WINS
        $data = array(
            'operation' => 'list-high-wins',
            'affiliate_name' => $white_label_username,
        );

        $response_list_high_wins = $client->post($url, ['body' => json_encode($data)]);

        $this->assertEquals(200, $response_list_high_wins->getStatusCode());
        $response_data_list_high_wins = json_decode($response_list_high_wins->getBody(), true);

        $this->assertArrayHasKey('status', $response_data_list_high_wins);
        $this->assertEquals('OK', $response_data_list_high_wins['status']);

        //LIST LIMITS
        $data = array(
            'operation' => 'list-limits-new',
            'session_id' => $response_data_site_login['pc_session_id'],
        );

        $response_list_limits = $client->post($url, ['body' => json_encode($data)]);

        $this->assertEquals(200, $response_list_limits->getStatusCode());
        $response_data_list_limits = json_decode($response_list_limits->getBody(), true);

        $this->assertArrayHasKey('status', $response_data_list_limits);
        $this->assertEquals('OK', $response_data_list_limits['status']);

        //LIST PLAYER BONUS HISTORY
        $data = array(
            'operation' => 'list-player-bonus-history',
            'session_id' => $response_data_site_login['pc_session_id'],
        );

        $response_list_player_bonus_history = $client->post($url, ['body' => json_encode($data)]);

        $this->assertEquals(200, $response_list_player_bonus_history->getStatusCode());
        $response_data_list_player_bonus_history = json_decode($response_list_player_bonus_history->getBody(), true);

        $this->assertArrayHasKey('status', $response_data_list_player_bonus_history);
        $this->assertEquals('OK', $response_data_list_player_bonus_history['status']);

        //LIST PLAYER DOCUMENTS
        $data = array(
            'operation' => 'list-player-documents',
            'site_session_id' => $response_data_site_login['site_session_id'],
            'player_id' => $response_data_site_login['player_id'],
        );

        $response_list_player_documents = $client->post($url, ['body' => json_encode($data)]);

        $this->assertEquals(200, $response_list_player_documents->getStatusCode());
        $response_data_list_player_documents = json_decode($response_list_player_documents->getBody(), true);

        $this->assertArrayHasKey('status', $response_data_list_player_documents);
        $this->assertEquals('OK', $response_data_list_player_documents['status']);

        //LIST ALL PAYMENT METHODS
        $data = array(
            'operation' => 'get-all-payment-methods',
        );

        $response_get_all_payment_methods = $client->post($url, ['body' => json_encode($data)]);

        $this->assertEquals(200, $response_get_all_payment_methods->getStatusCode());
        $response_data_get_all_payment_methods = json_decode($response_get_all_payment_methods->getBody(), true);

        $this->assertArrayHasKey('status', $response_data_get_all_payment_methods);
        $this->assertEquals('OK', $response_data_get_all_payment_methods['status']);

        //PENDING PAYOUT STATUS
        $data = array(
            'operation' => 'pending-payout-status',
            'site_session_id' => $response_data_site_login['site_session_id'],
        );

        $response_pending_payout_status = $client->post($url, ['body' => json_encode($data)]);

        $this->assertEquals(200, $response_pending_payout_status->getStatusCode());
        $response_data_pending_payout_status = json_decode($response_pending_payout_status->getBody(), true);

        $this->assertArrayHasKey('status', $response_data_pending_payout_status);
        $this->assertEquals('OK', $response_data_pending_payout_status['status']);

        //LIST PAYMENT LIMITS FOR WHITE LABEL
        $data = array(
            'operation' => 'list-payment-limits-for-white-label',
            'site_session_id' => $response_data_site_login['site_session_id'],
            'currency' => $currency,
        );

        $response_list_payment_limits_for_wl = $client->post($url, ['body' => json_encode($data)]);

        $this->assertEquals(200, $response_list_payment_limits_for_wl->getStatusCode());
        $response_data_list_payment_limits_for_wl = json_decode($response_list_payment_limits_for_wl->getBody(), true);

        $this->assertArrayHasKey('status', $response_data_list_payment_limits_for_wl);
        $this->assertEquals('OK', $response_data_list_payment_limits_for_wl['status']);

        //SITE LOGOUT
        $data = array(
            'operation' => 'site-logout',
            'site_session_id' => $response_data_site_login['site_session_id'],
            'pc_session_id' => $response_data_site_login['pc_session_id'],
        );

        $response_site_logout = $client->post($url, ['body' => json_encode($data)]);

        $this->assertEquals(200, $response_site_logout->getStatusCode());
        //print_r($response->getBody());
        $response_data_site_logout = json_decode($response_site_logout->getBody(), true);
        $this->assertArrayHasKey('status', $response_data_site_logout);
        $this->assertEquals('OK', $response_data_site_logout['status']);
    }


    public function tearDown()
    {
    }

}