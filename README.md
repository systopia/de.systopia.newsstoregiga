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
