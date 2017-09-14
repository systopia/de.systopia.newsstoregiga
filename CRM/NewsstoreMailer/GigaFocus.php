<?php
/**
 * This is the parent class of all mailing formatters.
 *
 * Formatters should implement getMailingHtml() and getMailingSubject() and
 * configure() if they need to take input params.
 */
class CRM_NewsstoreMailer_GigaFocus extends CRM_NewsstoreMailer
{
  /**
   * List of tags we allow to pass from RSS items direct to mailings.
   */
  const PERMITTED_HTML_TAGS= '<br><p><h1><h2><h3><h4><h5><h6><ul><ol><li><dd><dt><dl><hr><embed><object><a><div><table><thead><tbody><tr><th><td><strong><em><b><i><img>';

  /**
   * Template the email.
   */
  public function getMailingHtml($items) {

    // Up two levels from this file, and then down into the templates dir.
    $templates_dir = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
    $item_tpl = file_get_contents($templates_dir . "focus-item.html");
    $body_tpl = file_get_contents($templates_dir . "focus-body.html");

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

    $body_html = str_replace('%ITEMS%', $html_items, $body_tpl);

    return $body_html;
  }
  /**
   * Create the subject.
   */
  public function getMailingSubject($items) {

    $subject = count($items) . " article" . (count($items)>1 ? 's' : '') . " from GIGA Focus";

    return $subject;
  }
}

