<?php
/**
 * GIGA specific use of NewsStore.
 */

class CRM_Giga_AutoMail
{
  /**
   * Body template.
   */
  public $body_tpl;
  /**
   * Item template.
   */
  public $item_tpl;

  /**
   *
   * Constructor.
   *
   * $params array can contain these keys:
   * - body_tpl_file
   * - body_tpl
   * - item_tpl_file
   * - item_tpl
   *
   * @param array $params
   */
  public function __construct($params = []) {

    // Up two levels from this file.
    $extension_dir = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR;

    // Load the body template.
    if (isset($params['body_tpl'])) {
      $this->body_tpl = $params['body_tpl'];
    }
    else {
      // Load template from a file.
      $filename = isset($params['body_tpl_file'])
        ? $params['body_tpl_file']
        : ($extension_dir  . 'auto-mail-body-template.html');
      $this->body_tpl = file_get_contents($filename);
    }

    // Load the item template.
    if (isset($params['item_tpl'])) {
      $this->item_tpl = $params['item_tpl'];
    }
    else {
      // Load template from a file.
      $filename = isset($params['item_tpl_file'])
        ? $params['item_tpl_file']
        : ($extension_dir  . 'auto-mail-item-template.html');
      $this->item_tpl = file_get_contents($filename);
    }
  }
  /**
   * Send items out.
   *
   * @param int $news_source_id
   * @param int $mailing_group_id
   * @return int number of items included in the mailing sent.
   */
  public function process($news_source_id, $mailing_group_id) {

    // Fetch data.
    $group = civicrm_api3('Group', 'getsingle', [
      'id'         => $mailing_group_id,
      'group_type' => "Mailing List",
      'is_active'  => 1,
      ]);
    $items = civicrm_api3('NewsStoreItem', 'getwithusage', array(
      'source'      => $news_source_id,
      'is_consumed' => 0, // Only unconsumed items.
    ));

    // Create and send mailing.
    $mailing_id = $this->createMailing($items['values'], $group);
    if ($mailing_id) {
      $this->sendMailing($mailing_id);
    }

    // Mark items as consumed.
    if ($items['values']) {
      // Mark each of these items consumed.
      foreach (array_keys($items['values']) as $consumed_id) {
        $result = civicrm_api3('NewsStoreConsume', 'create', [
          'id'          => $consumed_id,
          'is_consumed' => 1,
        ]);
      }
    }

    return (int) $items['count'];
  }
  /**
   * Create a CiviMail mailing.
   *
   * @return null|int ID of mailing created, if one was.
   */
  public function createMailing($items, $mailing_group) {
    if (empty($items)) {
      // Nothing to do, don't do anything!
      return;
    }

    // Got items. Create a mailing.
    $mailing_result = civicrm_api3('Mailing', 'create', [
      'sequential' => 1,
      'name'       => ts(count($items)>1 ? "$items items: " : '1 item: ') . $mailing_group['title'],
      'from_name'  => "Testers", // @todo
      'from_email' => "forums@artfulrobot.uk", // @todo
      'subject'    => "test 1",
      'body_html'  => $this->getMailingHtml($items),
      'groups'     => ['include' => [$mailing_group['id']]],
    ]);

    return $mailing_result['id'];
  }
  /**
   * Schedule the mailing to be sent immediately.
   *
   * @todo schedule 30 mins in future?
   */
  public function sendMailing($mailing_id) {
    // Send it.
    $submit_result = civicrm_api3('Mailing', 'submit', [
      'id' => $mailing_id,
      'scheduled_date' => date('Y-m-d H:i:s'),
      'approval_date' => date('Y-m-d H:i:s'),
      ]);
    return $submit_result;
  }

  /**
   * Template the email.
   */
  public function getMailingHtml($items) {

    $html_items = '';
    foreach ($items as $item) {
      $html_items .= strtr($this->item_tpl, [
        '%ITEM_TITLE%'       => htmlspecialchars($item['title']),
        '%ITEM_DESCRIPTION%' => $item['description'],
        '%ITEM_TEASER%'      => htmlspecialchars($item['teaser']),
        '%ITEM_LINK%'        => $item['uri'],
        '%ITEM_IMAGE%'       => '',
      ]);
    }

    $body_html = str_replace('%ITEMS%', $html_items, $this->body_tpl);

    return $body_html;
  }
}
