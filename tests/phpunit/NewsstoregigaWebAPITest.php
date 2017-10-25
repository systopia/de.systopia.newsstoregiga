<?php

use CRM_Newsstoregiga_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * Test web api behaves.
 *
 * Tips:
 *  - With HookInterface, you may implement CiviCRM hooks directly in the test class.
 *    Simply create corresponding functions (e.g. "hook_civicrm_post(...)" or similar).
 *  - With TransactionalInterface, any data changes made by setUp() or test****() functions will
 *    rollback automatically -- as long as you don't manipulate schema or truncate tables.
 *    If this test needs to manipulate schema or truncate tables, then either:
 *       a. Do all that using setupHeadless() and Civi\Test.
 *       b. Disable TransactionalInterface, and handle all setup/teardown yourself.
 *
 * @group headless
 */
class NewsstoregigaWebAPITest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {
  public $fixtures = [];

  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://github.com/civicrm/org.civicrm.testapalooza/blob/master/civi-test.md
    return \Civi\Test::headless()
      ->install(['de.systopia.newsstore', 'de.systopia.newsstoremailer'])
      ->installMe(__DIR__)
      ->apply();
  }

  public function setUp() {

    // Create a couple of the groups.
    foreach(['giga_en_latinamerica', 'giga_de_latinamerica'] as $_) {
      $result = civicrm_api3('Group', 'create', [
        'name' => CRM_Newsstoregiga_Page_WebAPI::$mapped_groups[$_],
        'title' => CRM_Newsstoregiga_Page_WebAPI::$mapped_groups[$_],
        'group_type' => 'Mailing List',
      ]);
      $this->fixtures['groups'][$_] = $result['id'];
    }

    // Create a contacts.
    $result = civicrm_api3('Contact', 'create', [
      'first_name' => 'Wilma',
      'last_name' => 'Flintstone',
      'contact_type' => 'Individual',
    ]);
    $this->fixtures['contacts'][0] = ['contact_id' => $result['id'], 'email_ids' => []];
    $result = civicrm_api3('Email', 'create', [
      'contact_id' => $result['id'],
      'email' => 'wilma@example.com',
    ]);
    $this->fixtures['contacts'][0]['email_ids'][0] = $result['id'];

    parent::setUp();
  }

  public function tearDown() {
    foreach ($this->fixtures['contacts'] as $c) {
      civicrm_api3('Contact', 'delete', [
        'id' => $c['contact_id'],
        'skip_undelete' => TRUE,
      ]);
    }
    foreach ($this->fixtures['groups'] as $group_id) {
      civicrm_api3('Group', 'delete', [ 'id' => $group_id ]);
    }
    parent::tearDown();
  }

  public function testGetContactHash() {
    $api = new CRM_Newsstoregiga_Page_WebAPI();
    $api->request_data = [];
    $api->request_query = [
      'psk' => GIGA_EMAIL_SUBSCRIPTION_API_PSK,
      'email' => 'wilma@example.com',
    ];
    $result = $api->getContactHash();
    $this->assertRegExp('/^[a-zA-Z0-9_-]+$/', $result);
  }
  public function testInvalidKey() {
    $api = new CRM_Newsstoregiga_Page_WebAPI();
    $api->request_query = [
      'psk' => 'incorrect',
      'email' => 'wilma@example.com',
    ];
    $fail = FALSE;
    try {
      $result = $api->checkPSK();
      $fail = TRUE;
    }
    catch (Exception $e) {
      $this->assertEquals(401, $e->getCode());
    }
    if ($fail) {
      $this->fail('Failed to reject invalid key');
    }
  }
  public function testMissingKey() {
    $api = new CRM_Newsstoregiga_Page_WebAPI();
    $fail = FALSE;
    try {
      $result = $api->checkPSK();
      $fail = TRUE;
    }
    catch (Exception $e) {
      $this->assertEquals(401, $e->getCode());
    }
    if ($fail) {
      $this->fail('Failed to reject invalid key');
    }
  }
  public function testGetContactData() {
    $api = new CRM_Newsstoregiga_Page_WebAPI();
    $api->request_data = [];
    $api->request_query = [
      'psk' => GIGA_EMAIL_SUBSCRIPTION_API_PSK,
      'email' => 'wilma@example.com',
    ];
    $hash = $api->getContactHash();

    $api = new CRM_Newsstoregiga_Page_WebAPI();
    $api->request_data = [];
    $api->request_query = [
      'psk' => GIGA_EMAIL_SUBSCRIPTION_API_PSK,
      'email' => 'wilma@example.com',
      'hash' => $hash,
    ];
    $result = $api->getContactData();

    $this->assertEquals($this->fixtures['contacts'][0]['contact_id'], $result['contact_id']);
    $this->assertEquals('Wilma', $result['first_name']);
    $this->assertEquals('Flintstone', $result['last_name']);
    $this->assertEquals('wilma@example.com', $result['email']);
    foreach (CRM_Newsstoregiga_Page_WebAPI::$mapped_groups as $api_name => $civi_name) {
      $this->assertArrayHasKey($api_name, $result);
      $this->assertEquals(0, $result[$api_name]);
    }
  }
  public function testSetContactDataChangesNameAndSubscribes() {
    $api = new CRM_Newsstoregiga_Page_WebAPI();
    $api->request_data = [];
    $api->request_query = [
      'psk' => GIGA_EMAIL_SUBSCRIPTION_API_PSK,
      'email' => 'wilma@example.com',
    ];
    $hash = $api->getContactHash();

    $api = new CRM_Newsstoregiga_Page_WebAPI();
    $api->request_data = [
      'first_name' => 'Betty',
      'giga_de_latinamerica' => 1,
    ];
    $api->request_query = [
      'psk' => GIGA_EMAIL_SUBSCRIPTION_API_PSK,
      'email' => 'wilma@example.com',
      'hash' => $hash,
    ];
    $api->setContactData();

    // Now fetch data and check it changed.
    $api = new CRM_Newsstoregiga_Page_WebAPI();
    $api->request_data = [ ];
    $api->request_query = [
      'psk' => GIGA_EMAIL_SUBSCRIPTION_API_PSK,
      'email' => 'wilma@example.com',
      'hash' => $hash,
    ];
    $result = $api->getContactData();

    // Check unchanged fields left alone.
    $this->assertEquals($this->fixtures['contacts'][0]['contact_id'], $result['contact_id']);
    $this->assertEquals('Flintstone', $result['last_name']);
    $this->assertEquals('wilma@example.com', $result['email']);
    // Check first name changed.
    $this->assertEquals('Betty', $result['first_name']);
    // Check added to group.
    foreach (CRM_Newsstoregiga_Page_WebAPI::$mapped_groups as $api_name => $civi_name) {
      $this->assertArrayHasKey($api_name, $result);
      $expect = ($api_name == 'giga_de_latinamerica') ? 1 : 0;
      $this->assertEquals($expect, $result[$api_name]);
    }
  }
  public function testSetContactDataUnsubscribes() {
    $api = new CRM_Newsstoregiga_Page_WebAPI();
    $api->request_data = [];
    $api->request_query = [
      'psk' => GIGA_EMAIL_SUBSCRIPTION_API_PSK,
      'email' => 'wilma@example.com',
    ];
    $hash = $api->getContactHash();

    // Do subscription.
    $api = new CRM_Newsstoregiga_Page_WebAPI();
    $api->request_data = [
      'giga_de_latinamerica' => 1,
      'giga_en_latinamerica' => 1,
    ];
    $api->request_query = [
      'psk' => GIGA_EMAIL_SUBSCRIPTION_API_PSK,
      'email' => 'wilma@example.com',
      'hash' => $hash,
    ];
    $api->setContactData();
    // (assume that worked - tested in testSetContactDataChangesNameAndSubscribes)
    $api = new CRM_Newsstoregiga_Page_WebAPI();
    $api->request_data = [ ];
    $api->request_query = [
      'psk' => GIGA_EMAIL_SUBSCRIPTION_API_PSK,
      'email' => 'wilma@example.com',
      'hash' => $hash,
    ];
    $result = $api->getContactData();

    // Now unsubscribe from giga_de_latinamerica
    $api = new CRM_Newsstoregiga_Page_WebAPI();
    $api->request_data = [ 'giga_de_latinamerica' => 0 ];
    $api->request_query = [
      'psk' => GIGA_EMAIL_SUBSCRIPTION_API_PSK,
      'email' => 'wilma@example.com',
      'hash' => $hash,
    ];
    $api->setContactData();

    // Now fetch data and check it changed.
    $api = new CRM_Newsstoregiga_Page_WebAPI();
    $api->request_data = [ ];
    $api->request_query = [
      'psk' => GIGA_EMAIL_SUBSCRIPTION_API_PSK,
      'email' => 'wilma@example.com',
      'hash' => $hash,
    ];
    $result = $api->getContactData();

    // Check added to group.
    foreach (CRM_Newsstoregiga_Page_WebAPI::$mapped_groups as $api_name => $civi_name) {
      $this->assertArrayHasKey($api_name, $result);
      $expect = ($api_name == 'giga_en_latinamerica') ? 1 : 0;
      $this->assertEquals($expect, $result[$api_name]);
    }
  }
}
