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
      'intro'         => '', // @todo
    ],
    'africa-spectrum-en' => [
      'item_template' => 'journal-family-item.html',
      'body_template' => 'journal-family-body-en.html',
      'header'        => 'journalfamily.jpg',
      'subject'       => 'New Issue | Afrika Spectrum',
      'family'        => '', // @todo
      'intro'         => '', // @todo
    ],
    'chinese-affairs-de' => [
      'item_template' => 'journal-family-item.html',
      'body_template' => 'journal-family-body-de.html',
      'header'        => 'journalfamily.jpg',
      'subject'       => 'Neue Ausgabe |  Journal of Current Southeast Asian Affairs',
      'family'        => '', // @todo
      'intro'         => '', // @todo
    ],
    'chinese-affairs-en' => [
      'item_template' => 'journal-family-item.html',
      'body_template' => 'journal-family-body-en.html',
      'header'        => 'journalfamily.jpg',
      'subject'       => 'New Issue |  Journal of Current Southeast Asian Affairs',
      'family'        => '', // @todo
      'intro'         => '', // @todo
    ],
    'latin-america-de' => [
      'item_template' => 'journal-family-item.html',
      'body_template' => 'journal-family-body-en.html',
      'header'        => 'journalfamily.jpg',
      'subject'       => 'Neue Ausgabe |  Journal of Politics in Latin America',
      'family'        => '', // @todo
      'intro'         => '', // @todo
    ],
    'latin-america-en' => [
      'item_template' => 'journal-family-item.html',
      'body_template' => 'journal-family-body-en.html',
      'header'        => 'journalfamily.jpg',
      'subject'       => 'New Issue |  Journal of Politics in Latin America',
      'family'        => '', // @todo
      'intro'         => '', // @todo
    ],
    'se-asia-de' => [
      'item_template' => 'journal-family-item.html',
      'body_template' => 'journal-family-body-en.html',
      'header'        => 'journalfamily.jpg',
      'subject'       => 'Neue Ausgabe |  Journal of Current Southeast Asian Affairs',
      'family'        => 'JOURNAL OF CURRENT SOUTHEAST ASIAN AFFAIRS', // @todo
      'intro'         => "The journal offers insights on policy implementation in the Philippines, Myanmar's foreign policy, territorial disputes and nationalism in Vietnam and China, and other issues.",
    ],
    'se-asia-en' => [
      'item_template' => 'journal-family-item.html',
      'body_template' => 'journal-family-body-en.html',
      'header'        => 'journalfamily.jpg',
      'subject'       => 'New Issue |  Journal of Current Southeast Asian Affairs',
      'family'        => 'JOURNAL OF CURRENT SOUTHEAST ASIAN AFFAIRS',
      'intro'         => "The journal offers insights on policy implementation in the Philippines, Myanmar's foreign policy, territorial disputes and nationalism in Vietnam and China, and other issues.",
    ],
  ];

  /**
   * Template the email.
   */
  public function getMailingHtml($items) {

    // As others.
    $html = parent::getMailingHtml();
    // ... except we also need to inject the name of the journal.
    $html = strtr($html, [
      '%FAMILY_TITLE%' => $this->giga_config['family'],
      '%FAMILY_INTRO%' => $this->giga_config['intro'],
    ]);

    return $html;
  }
}

