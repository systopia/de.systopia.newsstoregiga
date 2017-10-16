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
      'item_template' => 'journal-family-item.html',
      'body_template' => 'journal-family-body-de.html',
      'header'        => 'journalfamily.jpg',
      'subject'       => 'Neue Ausgabe | Afrika Spectrum',
      'family'        => '', // @todo
    ],
    'africa-spectrum-en' => [
      'item_template' => 'journal-family-item.html',
      'body_template' => 'journal-family-body-en.html',
      'header'        => 'journalfamily.jpg',
      'subject'       => 'New Issue | Afrika Spectrum',
      'family'        => '', // @todo
    ],
    'chinese-affairs-de' => [
      'item_template' => 'journal-family-item.html',
      'body_template' => 'journal-family-body-de.html',
      'header'        => 'journalfamily.jpg',
      'subject'       => 'Neue Ausgabe |  Journal of Current Southeast Asian Affairs',
      'family'        => '', // @todo
    ],
    'chinese-affairs-en' => [
      'item_template' => 'journal-family-item.html',
      'body_template' => 'journal-family-body-en.html',
      'header'        => 'journalfamily.jpg',
      'subject'       => 'New Issue |  Journal of Current Southeast Asian Affairs',
      'family'        => '', // @todo
    ],
    'latin-america-de' => [
      'item_template' => 'journal-family-item.html',
      'body_template' => 'journal-family-body-en.html',
      'header'        => 'journalfamily.jpg',
      'subject'       => 'Neue Ausgabe |  Journal of Politics in Latin America',
      'family'        => '', // @todo
    ],
    'latin-america-en' => [
      'item_template' => 'journal-family-item.html',
      'body_template' => 'journal-family-body-en.html',
      'header'        => 'journalfamily.jpg',
      'subject'       => 'New Issue |  Journal of Politics in Latin America',
      'family'        => '', // @todo
    ],
    'se-asia-de' => [
      'item_template' => 'journal-family-item.html',
      'body_template' => 'journal-family-body-en.html',
      'header'        => 'journalfamily.jpg',
      'subject'       => 'Neue Ausgabe |  Journal of Current Southeast Asian Affairs',
      'family'        => 'JOURNAL OF CURRENT SOUTHEAST ASIAN AFFAIRS', // @todo
    ],
    'se-asia-en' => [
      'item_template' => 'journal-family-item.html',
      'body_template' => 'journal-family-body-en.html',
      'header'        => 'journalfamily.jpg',
      'subject'       => 'New Issue |  Journal of Current Southeast Asian Affairs',
      'family'        => 'JOURNAL OF CURRENT SOUTHEAST ASIAN AFFAIRS',
    ],
  ];

  /**
   * Template the email.
   */
  public function getMailingHtml($items) {

    // As others.
    $html = parent::getMailingHtml();
    // ... except we also need to inject the name of the journal.
    $html = strtr($html, [ '%FAMILY_TITLE%' => $this->giga_config['family'], ]);

    return $html;
  }
}

