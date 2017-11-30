<?php
/**
 * This is the parent class of all mailing formatters.
 *
 * Formatters should implement getMailingHtml() and getMailingSubject() and
 * configure() if they need to take input params.
 */
class CRM_NewsstoreMailer_GigaWorkingPapers extends CRM_NewsstoreMailer_GigaCommon
{
  /**
   * Map giga_type parameter to template data.
   */
  public $giga_type_map = [
    'en' => [
      'item_template' => 'working-papers-item.html',
      'body_template' => 'working-papers-body-en.html',
      'header'        => 'workingpapers.jpg',
      'subject'       => 'New GIGA Working Paper | %ITEM_TITLE%'
    ],
    'de' => [
      'item_template' => 'working-papers-item.html',
      'body_template' => 'working-papers-body-de.html',
      'header'        => 'workingpapers.jpg',
      'subject'       => 'Neuer GIGA Working Paper | %ITEM_TITLE%'
    ],
  ];

  /**
   * Template the email.
   */
  public function getMailingHtml($items) {

    // Up two levels from this file, and then down into the templates dir.
    $templates_dir = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
    $item_tpl = file_get_contents($templates_dir . $this->giga_config['item_template']);

    $html_items = '';
    foreach ($items as $item) {
      $obj = $item['object'];
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
      '%SUBJECT%' => $this->getMailingSubject($items),
    ]);

    return $body_html;
  }
}

