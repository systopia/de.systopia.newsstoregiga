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
      'mosaico_tpl_name' => 'focus_template',
      'header'        => 'focus_latinamerica.jpg',
      'subject'       => 'New GIGA Focus | %ITEM_TITLE%'
    ],
    'de-latinamerica' => [
      'mosaico_tpl_name' => 'focus_template_de',
      'header'        => 'focus_lateinamerika.jpg',
      'subject'       => 'Neuer GIGA Focus | %ITEM_TITLE%'
    ],
    'en-middleeast' => [
      'mosaico_tpl_name' => 'focus_template',
      'header'        => 'focus_middleeast.jpg',
      'subject'       => 'New GIGA Focus | %ITEM_TITLE%'
    ],
    'de-middleeast' => [
      'mosaico_tpl_name' => 'focus_template_de',
      'header'        => 'focus_middleeast.jpg',
      'subject'       => 'Neuer GIGA Focus | %ITEM_TITLE%'
    ],
    'en-asia' => [
      'mosaico_tpl_name' => 'focus_template',
      'header'        => 'focus_asia.jpg',
      'subject'       => 'New GIGA Focus | %ITEM_TITLE%'
    ],
    'de-asia' => [
      'mosaico_tpl_name' => 'focus_template_de',
      'header'        => 'focus_asien.jpg',
      'subject'       => 'Neuer GIGA Focus | %ITEM_TITLE%'
    ],
    'en-global' => [
      'mosaico_tpl_name' => 'focus_template',
      'header'        => 'focus_global.jpg',
      'subject'       => 'New GIGA Focus | %ITEM_TITLE%'
    ],
    'de-global' => [
      'mosaico_tpl_name' => 'focus_template_de',
      'header'        => 'focus_global.jpg',
      'subject'       => 'Neuer GIGA Focus | %ITEM_TITLE%'
    ],
    'en-africa' => [
      'mosaico_tpl_name' => 'focus_template',
      'header'        => 'focus_africa.jpg',
      'subject'       => 'New GIGA Focus | %ITEM_TITLE%'
      ],
    'de-afrika' => [
      'mosaico_tpl_name' => 'focus_template',
      'header'        => 'focus_afrika.jpg',
      'subject'       => 'Neuer GIGA Focus | %ITEM_TITLE%'
      ],
  ];

}
