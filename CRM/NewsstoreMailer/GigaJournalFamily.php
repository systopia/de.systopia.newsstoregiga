<?php
/**
 * This is the parent class of all mailing formatters.
 *
 * Formatters should implement getMailingHtml() and getMailingSubject() and
 * configure() if they need to take input params.
 */
class CRM_NewsstoreMailer_GigaJournalFamily extends CRM_NewsstoreMailer_GigaCommon
{
  /**
   * Map giga_type parameter to template data.
   */
  public $giga_type_map = [
    'africa-spectrum-de' => [
      'subject'       => 'Neue Ausgabe | Africa Spectrum',
      'family'        => 'AFRICA SPECTRUM',
    ],
    'africa-spectrum-en' => [
      'subject'       => 'New Issue | Africa Spectrum',
      'family'        => 'AFRICA SPECTRUM',
    ],
    'chinese-affairs-de' => [
      'subject'       => 'Neue Ausgabe | Journal of Current Chinese Affairs',
      'family'        => 'JOURNAL OF CURRENT CHINESE AFFAIRS',
    ],
    'chinese-affairs-en' => [
      'subject'       => 'New Issue | Journal of Current Chinese Affairs',
      'family'        => 'JOURNAL OF CURRENT CHINESE AFFAIRS',
    ],
    'latin-america-de' => [
      'subject'       => 'Neue Ausgabe | Journal of Politics in Latin America',
      'family'        => 'JOURNAL OF POLITICS IN LATIN AMERICA',
    ],
    'latin-america-en' => [
      'subject'       => 'New Issue | Journal of Politics in Latin America',
      'family'        => 'JOURNAL OF POLITICS IN LATIN AMERICA',
    ],
    'se-asia-de' => [
      'subject'       => 'Neue Ausgabe | Journal of Current Southeast Asian Affairs',
      'family'        => 'JOURNAL OF CURRENT SOUTHEAST ASIAN AFFAIRS',
    ],
    'se-asia-en' => [
      'subject'       => 'New Issue | Journal of Current Southeast Asian Affairs',
      'family'        => 'JOURNAL OF CURRENT SOUTHEAST ASIAN AFFAIRS',
    ],
  ];

  /**
   * Add in common header image and set mosaico_tpl_name.
   */
  public function alterConfig() {
    // There are only two mosaico templates, a DE and an EN one.
    $tpl = (substr($this->giga_type, -3) === '-en')
      ? 'journal_template_en'
      : 'journal_template_de';

    $this->giga_config += [
      'header'           => 'journalfamily.jpg',
      'mosaico_tpl_name' => $tpl,
    ];
  }
  /**
   * Template the email.
   */
  public function getMailingHtml($items) {

    // As others.
    $html = parent::getMailingHtml($items);
    // ... except we also need to inject the name of the journal.
    $html = strtr($html, [
      '%FAMILY_TITLE%' => $this->giga_config['family'],
    ]);

    return $html;
  }
}

