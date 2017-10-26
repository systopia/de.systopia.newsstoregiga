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
   * Which config set is chosen.
   */
  protected $giga_config;

  /**
   * Configure from input params according to this formatter's requirements.
   *
   * @param array $params
   * @return CRM_NewsstoreMailer $this
   */
  public function configure($params=[]) {
    if (empty($params['giga_type']) || !isset($this->giga_type_map[$params['giga_type']])) {
      throw new \Exception("Missing or invalid giga_type parameter. Should be one of: " . implode(', ', array_keys($this->giga_type_map)));
    }
    $this->giga_config = $this->giga_type_map[$params['giga_type']];
  }


  /**
   * Template the email.
   */
  public function getMailingHtml($items) {

    // Up two levels from this file, and then down into the templates dir.
    $templates_dir = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
    $item_tpl = file_get_contents($templates_dir . $this->giga_config['item_template']);

    $html_items = '';
    foreach ($items as $item) {
      $obj = unserialize($item['object']);
      $html_items .= strtr($item_tpl, [
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

    $body_tpl = file_get_contents($templates_dir . $this->giga_config['body_template']);
    $body_html = strtr($body_tpl, [
      '%HEADER_IMG_URL%' => static::GIGA_IMAGES_BASE_URL . $this->giga_config['header'],
      '%ITEMS%' => $html_items,
    ]);

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
}

