# Custom functionality using NewsStore extension.

## API method: NewsStoreSource.newsstoremailer

See the [NewsStoreMailer README][1] for an intro to this method. This
GIGA-specific extension adds custom formatter classes.

Example call:

    civicrm_api3('NewsStoreSource', 'newsstoremailer', array(
      'mailing_group_id' => 23, // e.g. group wanting GIGA Focus Global EN
      'news_source_id'   => 1,  // e.g. GIGA Focus Global EN
      'test_mode'        => 1,  // create but don't send;
                                // don't mark items consumed
      'formatter'        => "CRM_NewsstoreMailer_GigaFocus", // see below
      'giga_type'        => "global-en",     // see below
    ));

`formatter` classes implemented in this extension all require a `giga_type`
value.

## About the GIGA Custom Formatters

There are three and they share a lot in common. They create mailings based on
Mosaico Templates, as you can configure at `/civicrm/a/#/mosaico-template`.

The Mosaico Templates all extend a custom [Mosaico Base Template][2]. In brief:
the template includes content with various `%PLACEHOLDERS%` which get swapped
out for content from the RSS feed (or formatter config) by the formatter.

A section of the template is marked by special code comments to identify a
(potentially) repeatable section of the template which will be used for
formatting each item. However, usually there is only one item in the feed.

The placeholders implemented can be seen in the `getMailingHtml()` method. At
the time of writing these are:

- `%ITEM_TITLE%` Item's `title` field.
- `%ITEM_DESCRIPTION%` Item's `description` field, limited HTML allowed.
- `%ITEM_TEASER%` Plain text version of item's `teaser`
- `%ITEM_LINK%`  Item's `uri`
- `%ITEM_IMAGE_SRC%` Image SRC URI, found in item's `url` attribute of its `enclosure`
- `%ITEM_SOURCE%` Text image credit from item's `source` field.
- `%ITEM_DC:CREATOR%` Content of `dc:creator` field
- `%ITEM_CONTENT_ENCODED%` Item's `content:encoded` field, limited HTML allowed.
- `%HEADER_IMG_URL%` URL to header image, provided by formatter class
- `%SUBJECT%` Mailing subject from `getMailingSubject()`
- `%FAMILY_TITLE%` Journal Family only. Set by formatter.

### `giga_type` API values implemented by the formatters.

- `CRM_NewsstoreMailer_GigaFocus` uses the following `giga_type` values:
   - `latinamerica-en`
   - `latinamerica-de`
   - `middleeast-en`
   - `middleeast-de`
   - `asia-en`
   - `asia-de`
   - `global-en`
   - `global-de`
   - `africa-en`
   - `afrika-de`

- `CRM_NewsstoreMailer_GigaJournalFamily` uses the following `giga_type` values:
   - `africa-spectrum-de`
   - `africa-spectrum-en`
   - `chinese-affairs-de`
   - `chinese-affairs-en`
   - `latin-america-de`
   - `latin-america-en`
   - `se-asia-de`
   - `se-asia-en`

- `CRM_NewsstoreMailer_GigaWorkingPapers` uses the following `giga_type` values:
   - `en`
   - `de`

### Coding the formatters.

The three formatter implementations have a lot in common, and so they all extend
a class called `CRM_NewsstoreMailer_GigaCommon`.

The simplest style is Working Papers since there are only two variations.

The Focus formatter uses the `alterConfig()` method to set certain parameters that
are only distinguished by the language.

The Journal Family formatter also overrides the `getMailingHtml()` method
because as well as the common template replacements it uses some extra ones.
(e.g. `%FAMILY_TITLE%`).

### `mosaico_tpl_name` parameter.

If this optional parameter is provided in the API call it will overrride the
template names configured in the formatter code. This may be useful e.g. if you
want to test a new template.


   [1]: https://github.com/artfulrobot/de.systopia.newsstoremailer/blob/master/README.md
   [2]: https://github.com/pbatroff/giga_template
