<?php
/**
 * This class brings in the code that's common to all GIGA mailings.
 *
 * Formatters should implement getMailingHtml() and getMailingSubject() and
 * configure() if they need to take input params.
 */
class CRM_NewsstoreMailer_GigaCommon extends CRM_NewsstoreMailer
{
  /**
   * List of tags we allow to pass from RSS items direct to mailings.
   */
  const PERMITTED_HTML_TAGS= '<br><p><h1><h2><h3><h4><h5><h6><ul><ol><li><dd><dt><dl><hr><embed><object><a><div><table><thead><tbody><tr><th><td><strong><em><b><i><img>';

  /**
   * Base URL for header images.
   */
  const GIGA_IMAGES_BASE_URL = 'https://www.giga-hamburg.de/sites/default/files/newsstore-template-images/';

  /**
   * Map giga_type parameter to template data.
   */
  public $giga_type_map;

  /**
   * Body template.
   */
  public $body_tpl;

  /**
   * Item template.
   */
  public $item_tpl;

  /**
   * Which config set is chosen.
   */
  protected $giga_config;

  /**
   * Configure from input params according to this formatter's requirements.
   *
   * Nb. mosaico_tpl_name can either be passed in as a param, or ommitted. If
   * ommitted we expect to find it in the $giga_config property.
   *
   * @param array $params
   * @return CRM_NewsstoreMailer $this
   */
  public function configure($params=[]) {
    if (empty($params['giga_type']) || !isset($this->giga_type_map[$params['giga_type']])) {
      throw new \Exception("Missing or invalid giga_type parameter. Should be one of: " . implode(', ', array_keys($this->giga_type_map)));
    }
    $this->giga_config = $this->giga_type_map[$params['giga_type']];

    // Fetch the Mosaico template.
    if (!empty($params['mosaico_tpl_name'])) {
      $mosaico_tpl_name = $params['mosaico_tpl_name'];
    }
    else {
      $mosaico_tpl_name = isset($this->giga_config['mosaico_tpl_name']) ? $this->giga_config['mosaico_tpl_name'] : NULL;
    }
    if ($mosaico_tpl_name) {
      // Fetch the template.
      $mosaico_tpl = civicrm_api3('MosaicoTemplate', 'getvalue', [
        'return' => "html",
        'title' => $mosaico_tpl_name,
      ]);
    }
    if (empty($mosaico_tpl)) {
      throw new \Exception("Missing mosaico_tpl_name, or template is not found.");
    }
    $this->parseMosaicoTpl($mosaico_tpl);
  }
  /**
   * Parse the HTML from the mosaico template into a body and a per-item template.
   *
   * @param string $mosaico_tpl HTML
   */
  public function parseMosaicoTpl($mosaico_tpl) {
    // The Mosiaco Builder insists on a div where we don't want one. Remove
    // that now to simplify the next bit.
    preg_match_all('@<div id="[^"]+">\s*(<!-- __ITEM_(START|END)__ -->)\s*</div>@', $mosaico_tpl, $matches, PREG_OFFSET_CAPTURE);
    if (empty($matches) || count($matches[0]) != 2) {
      throw new Exception("Mosaico Template is missing the item start or end marker.");
    }
    $start_of_start_marker = $matches[0][0][1];
    $end_of_start_marker   = $start_of_start_marker + strlen($matches[0][0][0]);
    $start_of_end_marker = $matches[0][1][1];
    $end_of_end_marker   = $start_of_end_marker + strlen($matches[0][1][0]);

    // Create body template with %ITEMS% in place of the repeatable item template.
    $this->body_tpl = substr($mosaico_tpl, 0, $start_of_start_marker)
      . '%ITEMS%'
      . substr($mosaico_tpl, $end_of_end_marker);

    $this->item_tpl = substr($mosaico_tpl, $end_of_start_marker, $start_of_end_marker - $end_of_start_marker);
  }


  /**
   * Template the email.
   */
  public function getMailingHtml($items) {

    $html_items = '';
    foreach ($items as $item) {
      $obj = $item['object'];
      $html_items .= strtr($this->item_tpl, [
        '%ITEM_TITLE%'           => htmlspecialchars($item['title']),
        '%ITEM_DESCRIPTION%'     => strip_tags($obj['item/description'], static::PERMITTED_HTML_TAGS),
        '%ITEM_TEASER%'          => htmlspecialchars($item['teaser']),
        '%ITEM_LINK%'            => $item['uri'],
        '%ITEM_IMAGE_SRC%'       => $obj['item/enclosure@url'],
        '%ITEM_SOURCE%'          => $obj['item/source'],
        '%ITEM_DC:CREATOR%'      => $obj['item/dc:creator'],
        '%ITEM_CONTENT_ENCODED%' => strip_tags($obj['item/content:encoded'], static::PERMITTED_HTML_TAGS),
      ]);
    }

    $body_html = strtr($this->body_tpl, [
      '%HEADER_IMG_URL%' => static::GIGA_IMAGES_BASE_URL . $this->giga_config['header'],
      '%ITEMS%' => $html_items,
      '%SUBJECT%' => $this->getMailingSubject($items),
    ]);

    // Now de-Mosaico this.
    // ... Step 1: this translates Mosaico style placeholders like
    // [unsubscribe_link] -> {action.unsubscribeUrl} and also changes image
    // URLs.
    \_mosaico_civicrm_alterMailContent($body_html);
    // ... Step 2: this handles smarty. Thanks totten!
    // $body_html = $this->_civicrm_api3_job_mosaico_msg_filter($body_html);

    return $body_html;
  }
  /**
   * Create the subject.
   */
  public function getMailingSubject($items) {
    $first_item = reset($items);
    $subject = str_replace('%ITEM_TITLE%', $first_item['title'], $this->giga_config['subject']);

    $substr = function_exists('mb_substr') ? 'mb_substr' : 'substr';

    if (strlen($subject) > 128) {
      $l = 128;
      while (strlen($subject) > 125) {
        // CiviCRM imposed limit of 128, I think.
        $subject = $substr($subject, 0, $l);
        $l--;
      }

      // Make it clear we have shortened it.
      $subject .= '...';
    }
    return $subject;
  }
  /**
   * Opportunity to edit the parameters of the mailing.
   *
   * @param Array $params API params used with Mailing.Create
   */
  public function alterCreateMailingParams(&$params) {
    $params['template_type'] = 'mosaico';
  }


  /**
   * Filter the HTML content.
   *
   * Poached from: https://github.com/civicrm/org.civicrm.mosaicomsgtpl/blob/master/api/v3/Job/MosaicoMsgSync.php
   *
   * @param string $html
   *   Template HTML, as generated by Mosaico.
   * @return string
   *   Template HTML, as appropriate for MessageTemplates.
   */
  function _civicrm_api3_job_mosaico_msg_filter($html) {
    if (defined('CIVICRM_MAIL_SMARTY') && CIVICRM_MAIL_SMARTY == 1) {
      // keep head section in literal to avoid smarty errors. Specially when CIVICRM_MAIL_SMARTY is turned on.
      $html = str_ireplace(array('<head>', '</head>'),
        array('{literal}<head>', '</head>{/literal}'), $html);
    }
    elseif (defined('CIVICRM_MAIL_SMARTY') && CIVICRM_MAIL_SMARTY == 0) {
      // get rid of any injected literal tags to avoid them appearing in emails
      $html = str_ireplace(array('{literal}<head>', '</head>{/literal}'),
        array('<head>', '</head>'), $html);
    }
    return $html;
  }

}


