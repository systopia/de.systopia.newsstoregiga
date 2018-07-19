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
    'latinamerica-en' => [
      'header'        => 'focus_latinamerica.jpg',
    ],
    'latinamerica-de' => [
      'header'        => 'focus_lateinamerika.jpg',
    ],
    'middleeast-en' => [
      'header'        => 'focus_middleeast.jpg',
    ],
    'middleeast-de' => [
      'header'        => 'focus_middleeast.jpg',
    ],
    'asia-en' => [
      'header'        => 'focus_asia.jpg',
    ],
    'asia-de' => [
      'header'        => 'focus_asien.jpg',
    ],
    'global-en' => [
      'header'        => 'focus_global.jpg',
    ],
    'global-de' => [
      'header'        => 'focus_global.jpg',
    ],
    'africa-en' => [
      'header'        => 'focus_africa.jpg',
      ],
    'afrika-de' => [
      'header'        => 'focus_afrika.jpg',
      ],
  ];

  /**
   * Set mosaico_tpl_name.
   */
  public function alterConfig() {
    // There are only two mosaico templates, a DE and an EN one.
    $is_english = (substr($this->giga_type, -3) === '-en');
    $defaults = [
      'mosaico_tpl_name' => $is_english ? 'focus_template_en' : 'focus_template_de',
      'subject'          => $is_english ?  'New GIGA Focus | %ITEM_TITLE%' : 'Neuer GIGA Focus | %ITEM_TITLE%',
    ];

    foreach ($this->giga_config as &$_) {
      $_ += $defaults;
    }
  }
}
