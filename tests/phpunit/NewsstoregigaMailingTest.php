<?php

use CRM_Newsstoregiga_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * Test formatting mails.
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
class NewsstoregigaMailingTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {
  public $fixtures = [];

  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://github.com/civicrm/org.civicrm.testapalooza/blob/master/civi-test.md
    return \Civi\Test::headless()
      ->install([
        'org.civicrm.flexmailer',
        'org.civicrm.shoreditch',
        'uk.co.vedaconsulting.mosaico',
        'de.systopia.newsstore', 'de.systopia.newsstoremailer'])
      ->installMe(__DIR__)
      ->apply();
  }

  public function setUp() {
    parent::setUp();
  }

  public function tearDown() {
    parent::tearDown();
  }

  /**
   * Test the parsing logic.
   */
  public function testParser() {
    // Fake a mosaico template.
    $mosaico_tpl = civicrm_api3('MosaicoTemplate', 'create', [
      'title' => 'test-tpl-a',
      'html' => 'Start <div id="foo">
<!-- __ITEM_START__ -->
</div>item template
goes
here.
   <div id="bar">
<!-- __ITEM_END__ -->
  </div>Body ends',
    ]);
    // Create a mailing group.
    $group = civicrm_api3('Group', 'create', [
        'name' => 'test1',
        'title' => 'test1',
        'group_type' => 'Mailing List',
      ]);

    $formatter = new CRM_NewsstoreMailer_GigaFocus([
      'formatter' => 'CRM_NewsstoreMailer_GigaFocus',
      'giga_type' => 'global-en',
      'mosaico_tpl_name' => 'test-tpl-a',// forces override.
      'mailing_group_id' => $group['id'],
      'news_store_source' => 1, // Does not exist, but that's fine for now.
    ]);

    // Now delete the template.
    civicrm_api3('MosaicoTemplate', 'delete', [ 'id' => $mosaico_tpl['id'] ]);
    // and the group
    civicrm_api3('Group', 'delete', [ 'id' => $group['id'] ]);

    $this->assertEquals('Start %ITEMS%Body ends', $formatter->body_tpl, "Body template did not match expected.");
    $this->assertEquals("item template\ngoes\nhere.\n   ", $formatter->item_tpl, "Item template did not match expected.");
  }
  public function testFormatter() {
    // Fake a mosaico template.
    $mosaico_tpl = civicrm_api3('MosaicoTemplate', 'create', [
      'title' => 'test-tpl-a',
      'html' => 'aaa
%HEADER_IMG_URL%
<div id="foo">
<!-- __ITEM_START__ -->
</div>
I say %ITEM_TITLE%
<div id="bar">
<!-- __ITEM_END__ -->
</div>
zzz',
    ]);
    // Create a mailing group.
    $group = civicrm_api3('Group', 'create', [
        'name' => 'test1',
        'title' => 'test1',
        'group_type' => 'Mailing List',
      ]);

    $formatter = new CRM_NewsstoreMailer_GigaFocus([
      'formatter' => 'CRM_NewsstoreMailer_GigaFocus',
      'giga_type' => 'global-en',
      'mosaico_tpl_name' => 'test-tpl-a',// forces override.
      'mailing_group_id' => $group['id'],
      'news_store_source' => 1, // Does not exist, but that's fine for now.
    ]);
    $dummy = [
      'object' => [
        'item/description' => '',
        'item/enclosure@url' => '',
        'item/source' => '',
        'item/dc:creator' => '',
        'item/content:encoded' => '',
      ],
      'uri' => 'http://example.com',
      'teaser' => '',
    ];
    $html = $formatter->getMailingHtml([
      $dummy + ['title' => 'bonjour'],
      $dummy + ['title' => 'hallo'],
      $dummy + ['title' => 'hi'],
    ]);

    // Now delete the template.
    civicrm_api3('MosaicoTemplate', 'delete', [ 'id' => $mosaico_tpl['id'] ]);
    // and the group
    civicrm_api3('Group', 'delete', [ 'id' => $group['id'] ]);

    $this->assertEquals('aaa
https://www.giga-hamburg.de/sites/default/files/newsstore-template-images/focus_global.jpg

I say bonjour

I say hallo

I say hi

zzz', $html);
  }
}
