# Custom functionality using NewsStore extension.

## API method: NewsStoreSource.Gigamail

This action takes a `news_source_id` and a `mailing_group_id` (and optionally a
`test_mode`) as inputs. It will then:

1. Check for unconsumed items in the NewsStoreSource. Exits here if there aren't
   any.

2. Creates a mailing to the given group, creating the HTML version of the
   mailing by applying each unconsumed item to an *item* template file and
   wrapping the items in the *body* template file.

3. If not in test mode: submit the mailing for sending.

4. If not in test mode: mark all the items it used as consumed.

This could then be set up as a scheduled job.

Example call:

    civicrm_api3('NewsStoreSource', 'newsstoremailer', array(
      'mailing_group_id' => 23, // e.g. group wanting GIGA Focus Global EN
      'news_source_id'   => 1,  // corresponds to the group.
      'formatter'        => "CRM_NewsstoreMailer_GigaFocus", // see below
      'test_mode'        => 1,  // create but don't send; don't mark items
                                // consumed
      'giga_type'        => "en-global",     // see below
      'mosaico_tpl_name' => "giga_focus_en", // see below
    ));

`formatter` values implemented in this extension which all require a `giga_type`
value.

- `CRM_NewsstoreMailer_GigaFocus` uses the following `giga_type` values:
   - `en-latinamerica`
   - `de-latinamerica`
   - `en-middleeast`
   - `de-middleeast`
   - `en-asia`
   - `de-asia`
   - `en-global`
   - `de-global`
   - `en-africa`
   - `de-afrika`

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

### About `mosaico_tpl_name`

This must match a suitable template name. It can be configured in the class
(hard-coded) or entered as an API parameter (which will override a hard-coded
value). Once designed they probably don't need changing.

