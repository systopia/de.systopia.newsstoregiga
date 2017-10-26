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
    $this->assertArrayHasKey('hash', $result);
    $this->assertRegExp('/^[a-zA-Z0-9_-]+$/', $result['hash']);
  }
  /**
   * Test things fail if the pre shared key is wrong.
   *
   * @expectedException CRM_Newsstoregiga_WebAPIException
   */
  public function testInvalidKey() {
    $api = new CRM_Newsstoregiga_Page_WebAPI();
    $api->request_query = [
      'psk' => 'incorrect',
      'email' => 'wilma@example.com',
    ];
    $api->checkPSK();
  }
  /**
   * Test things fail if pre shared key is missing.
   *
   * @expectedException CRM_Newsstoregiga_WebAPIException
   */
  public function testMissingKey() {
    $api = new CRM_Newsstoregiga_Page_WebAPI();
    $api->request_query = [];
    $api->checkPSK();
  }
  /**
   * Test things fail if pre shared key is missing.
   *
   * @expectedException CRM_Newsstoregiga_WebAPIException
   */
  public function testMissingHash() {
    $api = new CRM_Newsstoregiga_Page_WebAPI();
    $api->request_query = [
      'psk' => GIGA_EMAIL_SUBSCRIPTION_API_PSK,
      'email' => 'wilma@example.com',
    ];
    $result = $api->getContactData();
  }
  /**
   * Test things fail if pre shared key is missing.
   *
   * @expectedException CRM_Newsstoregiga_WebAPIException
   */
  public function testInvalidHash() {
    $api = new CRM_Newsstoregiga_Page_WebAPI();
    $api->request_query = [
      'psk' => GIGA_EMAIL_SUBSCRIPTION_API_PSK,
      'email' => 'wilma@example.com',
      'hash' => 'this is wrong',
    ];
    $result = $api->getContactData();
  }
  public function testGetContactData() {
    $api = new CRM_Newsstoregiga_Page_WebAPI();
    $api->request_data = [];
    $api->request_query = [
      'psk' => GIGA_EMAIL_SUBSCRIPTION_API_PSK,
      'email' => 'wilma@example.com',
    ];
    $hash = $api->getContactHash();
    $hash = $hash['hash'];

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
  /**
   * We want the system to fail if multiple contacts own the same email since
   * this means we cannot safely authenticate by email.
   *
   * @expectedException CRM_Newsstoregiga_WebAPIException
   * @expectedExceptionMessage Bad Request. Problem with input email.
   */
  public function testGetContactDataFailsIfEmailOwnedByMultipleContacts() {

    // Create a second contact.
    $result = civicrm_api3('Contact', 'create', [
      'first_name' => 'Betty',
      'last_name' => 'Rubble',
      'contact_type' => 'Individual',
    ]);
    $this->fixtures['contacts'][1] = ['contact_id' => $result['id'], 'email_ids' => []];
    $result = civicrm_api3('Email', 'create', [
      'contact_id' => $result['id'],
      'email' => 'wilma@example.com', // Same as main contact.
    ]);
    $this->fixtures['contacts'][0]['email_ids'][0] = $result['id'];

    $api = new CRM_Newsstoregiga_Page_WebAPI();
    $api->request_data = [];
    $api->request_query = [
      'psk' => GIGA_EMAIL_SUBSCRIPTION_API_PSK,
      'email' => 'wilma@example.com',
    ];
    $hash = $api->getContactHash();
    $hash = $hash['hash'];
  }
  public function testSetContactDataChangesNameAndSubscribes() {
    $api = new CRM_Newsstoregiga_Page_WebAPI();
    $api->request_data = [];
    $api->request_query = [
      'psk' => GIGA_EMAIL_SUBSCRIPTION_API_PSK,
      'email' => 'wilma@example.com',
    ];
    $hash = $api->getContactHash();
    $hash = $hash['hash'];

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
    $hash = $hash['hash'];

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
  public function testSetContactDataChangesEmail() {
    // Adjust the fixture, adding in a 2nd email. We'll check this is not touched.
    $other_email = civicrm_api3('Email', 'create', [
      'contact_id' => $this->fixtures['contacts'][0]['contact_id'],
      'email' => 'foo@example.com',
      'is_primary' => TRUE,
    ]);
    $this->fixtures['contacts'][0]['email_ids'][] = $other_email['id'];


    // Get our access hash.
    $api = new CRM_Newsstoregiga_Page_WebAPI();
    $api->request_data = [];
    $api->request_query = [
      'psk' => GIGA_EMAIL_SUBSCRIPTION_API_PSK,
      'email' => 'wilma@example.com',
    ];
    $hash = $api->getContactHash();
    $hash = $hash['hash'];

    // Send the update.
    $api = new CRM_Newsstoregiga_Page_WebAPI();
    $api->request_data = [
      'new_email' => 'wilma2@example.com',
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
      'email' => 'wilma2@example.com', // use new email to look up.
      'hash' => $hash,
    ];
    $result = $api->getContactData();

    // Check correct contact returned.
    $this->assertEquals($this->fixtures['contacts'][0]['contact_id'], $result['contact_id']);
    $this->assertEquals('wilma2@example.com', $result['email']);

    // Check that email, and only that email, was changed.
    foreach ([
      $this->fixtures['contacts'][0]['email_ids'][0] => 'wilma2@example.com',
      $this->fixtures['contacts'][0]['email_ids'][1] => 'foo@example.com',
    ] as $email_id => $expected_value) {
      $result = civicrm_api3('email', 'get', ['id' => $email_id, 'return' => 'email', 'sequential' => 1]);
      $this->assertEquals($expected_value, $result['values'][0]['email']);
    }
  }
  /**
   * We need to be able to create contacts.
   */
  public function testAddSubscriberCreatesContact() {
    // Send the update.
    $api = new CRM_Newsstoregiga_Page_WebAPI();
    $api->request_data = [
      'new_email'            => 'betty@example.com',
      'first_name'           => 'Betty',
      'last_name'            => 'Rubble',
      'giga_en_latinamerica' => 1,
    ];
    $api->request_query = ['psk' => GIGA_EMAIL_SUBSCRIPTION_API_PSK];
    $api->addSubscriber();

    // Check the email was created.
    $result = civicrm_api3('Email', 'get', ['email' => 'betty@example.com', 'sequential' => 1]);
    $this->assertEquals(1, $result['count']);
    $contact_id = $result['values'][0]['contact_id'];

    // Check the contact was created correctly.
    $result = civicrm_api3('Contact', 'get', ['id' => $contact_id]);
    $this->assertEquals(1, $result['count']);
    $this->assertEquals('Betty', $result['values'][$contact_id]['first_name']);
    $this->assertEquals('Rubble', $result['values'][$contact_id]['last_name']);

    // Check the contact was added to the group.
    $group_id = $this->fixtures['groups']['giga_en_latinamerica'];
    $result = civicrm_api3('Contact', 'getcount', [
      'id' => $contact_id,
      'group' => $group_id,
    ]);
    $this->assertEquals(1, $result);
  }
  /**
   * Check we get a hash back if we try to subscribe someone already subscribed.
   */
  public function testAddSubscriberFailsHelpfullyIfEmailKnown() {

    $api = new CRM_Newsstoregiga_Page_WebAPI();
    $api->request_data = [
      'new_email'            => 'wilma@example.com',
      'first_name'           => 'Betty',
      'last_name'            => 'Rubble',
      'giga_en_latinamerica' => 1,
    ];
    $api->request_query = ['psk' => GIGA_EMAIL_SUBSCRIPTION_API_PSK];
    $result = $api->addSubscriber();

    $this->assertInternalType('array', $result);
    $this->assertArrayHasKey('error', $result);
    $this->assertEquals('Authentication Required', $result['error']);
    $this->assertArrayHasKey('hash', $result);
    $this->assertRegExp('/^[a-zA-Z0-9_-]+$/', $result['hash']);
  }
  /**
   * We should receive an Unknown prefix error.
   *
   * @expectedException CRM_Newsstoregiga_WebAPIException
   * @expectedExceptionMessage Bad Request. Unknown prefix
   */
  public function testSetContactDataErrorsWithUnknownPrefix() {
    // Get our access hash.
    $api = new CRM_Newsstoregiga_Page_WebAPI();
    $api->request_data = [];
    $api->request_query = [
      'psk' => GIGA_EMAIL_SUBSCRIPTION_API_PSK,
      'email' => 'wilma@example.com',
    ];
    $hash = $api->getContactHash();
    $hash = $hash['hash'];

    // Send the update.
    $api = new CRM_Newsstoregiga_Page_WebAPI();
    $api->request_data = [
      'individual_prefix' => 'Moogle.',
    ];
    $api->request_query = [
      'psk' => GIGA_EMAIL_SUBSCRIPTION_API_PSK,
      'email' => 'wilma@example.com',
      'hash' => $hash,
    ];
    $result = $api->setContactData();
  }
}
