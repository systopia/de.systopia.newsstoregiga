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
      'mosaico_tpl_name' => 'working_papers_template_en',
      'header'           => 'workingpapers.jpg',
      'subject'          => 'New GIGA Working Paper | %ITEM_TITLE%'
    ],
    'de' => [
      'mosaico_tpl_name' => 'working_papers_template_de',
      'header'           => 'workingpapers.jpg',
      'subject'          => 'Neuer GIGA Working Paper | %ITEM_TITLE%'
    ],
  ];
}

