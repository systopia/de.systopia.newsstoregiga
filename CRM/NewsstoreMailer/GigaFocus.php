<?php
/**
 * This is the parent class of all mailing formatters.
 *
 * Formatters should implement getMailingHtml() and getMailingSubject() and
 * configure() if they need to take input params.
 */
class CRM_NewsstoreMailer_GigaFocus extends CRM_NewsstoreMailer_GigaCommon
{
  /**
   * Map giga_type parameter to template data.
   */
  public $giga_type_map = [
    'en-latinamerica' => [
      'item_template' => 'focus-item.html',
      'body_template' => 'focus-body-en.html',
      'header'        => 'focus_latinamerica.jpg',
      'subject'       => 'New GIGA Focus | %ITEM_TITLE%'
    ],
    'de-latinamerica' => [
      'item_template' => 'focus-item.html',
      'body_template' => 'focus-body-de.html',
      'header'        => 'focus_lateinamerika.jpg',
      'subject'       => 'Neuer GIGA Focus | %ITEM_TITLE%'
    ],
    'en-middleeast' => [
      'item_template' => 'focus-item.html',
      'body_template' => 'focus-body-en.html',
      'header'        => 'focus_middleeast.jpg',
      'subject'       => 'New GIGA Focus | %ITEM_TITLE%'
    ],
    'de-middleeast' => [
      'item_template' => 'focus-item.html',
      'body_template' => 'focus-body-de.html',
      'header'        => 'focus_middleeast.jpg',
      'subject'       => 'Neuer GIGA Focus | %ITEM_TITLE%'
    ],
    'en-asia' => [
      'item_template' => 'focus-item.html',
      'body_template' => 'focus-body-en.html',
      'header'        => 'focus_asia.jpg',
      'subject'       => 'New GIGA Focus | %ITEM_TITLE%'
    ],
    'de-asia' => [
      'item_template' => 'focus-item.html',
      'body_template' => 'focus-body-de.html',
      'header'        => 'focus_asien.jpg',
      'subject'       => 'Neuer GIGA Focus | %ITEM_TITLE%'
    ],
    'en-middleeast' => [
      'item_template' => 'focus-item.html',
      'body_template' => 'focus-body-en.html',
      'header'        => 'focus_middleeast.jpg',
      'subject'       => 'New GIGA Focus | %ITEM_TITLE%'
    ],
    'de-middleeast' => [
      'item_template' => 'focus-item.html',
      'body_template' => 'focus-body-de.html',
      'header'        => 'focus_nahost.jpg',
      'subject'       => 'Neuer GIGA Focus | %ITEM_TITLE%'
    ],
    'en-global' => [
      'item_template' => 'focus-item.html',
      'body_template' => 'focus-body-en.html',
      'header'        => 'focus_global.jpg',
      'subject'       => 'New GIGA Focus | %ITEM_TITLE%'
    ],
    'de-global' => [
      'item_template' => 'focus-item.html',
      'body_template' => 'focus-body-en.html',
      'header'        => 'focus_global.jpg',
      'subject'       => 'Neuer GIGA Focus | %ITEM_TITLE%'
    ],
    'en-africa' => [
      'item_template' => 'focus-item.html',
      'body_template' => 'focus-body-en.html',
      'header'        => 'focus_africa.jpg',
      'subject'       => 'New GIGA Focus | %ITEM_TITLE%'
      ],
    'de-afrika' => [
      'item_template' => 'focus-item.html',
      'body_template' => 'focus-body-en.html',
      'header'        => 'focus_afrika.jpg',
      'subject'       => 'Neuer GIGA Focus | %ITEM_TITLE%'
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
