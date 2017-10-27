<?php
use CRM_Newsstoregiga_ExtensionUtil as E;

/**
 * Pre-shared key must be defined before this function is included, e.g. in
 * your settings config file. Important that the file it is defined in is
 * never checked into a public repository like github!
 */
if (!defined('GIGA_EMAIL_SUBSCRIPTION_API_PSK')) {
  throw new Exception("You need to define GIGA_EMAIL_SUBSCRIPTION_API_PSK");
  // eg. define('GIGA_EMAIL_SUBSCRIPTION_API_PSK', 'insert-some-hash-here');
}

/**
 * JSON API.
 */
class CRM_Newsstoregiga_Page_WebAPI extends CRM_Core_Page {

  /** Cache group names */
  public $group_id_cache;
  public static $mapped_groups = [
    'giga_en_latinamerica' => 'GIGA Focus Latin America - EN',
    'giga_de_latinamerica' => 'GIGA Focus Lateinamerika - DE',
    'giga_en_middleeast' => 'GIGA Focus Middle East - EN',
    'giga_de_middleeast' => 'GIGA Focus Nahost - DE',
    'giga_en_asia' => 'GIGA Focus Asia - EN',
    'giga_de_asia' => 'GIGA Focus Asien - DE',
    'giga_en_global' => 'GIGA Focus Global - EN',
    'giga_de_global' => 'GIGA Focus Global - DE',
    'giga_en_africa' => 'GIGA Focus Africa - EN',
    'giga_de_afrika' => 'GIGA Focus Afrika - DE',

    'journal_africa_spectrum_en' => 'GIGA Journal Afrika Spectrum - EN',
    'journal_africa_spectrum_de' => 'GIGA Journal Africa Spectrum - DE',
    'journal_chinese_affairs_en' => 'GIGA Journal of Current Chinese Affairs - EN',
    'journal_chinese_affairs_de' => 'GIGA Journal of Current Chinese Affairs - DE',
    'journal_latin_america_en' => 'GIGA Journal of Politics in Latin America - EN',
    'journal_latin_america_de' => 'GIGA Journal of Politics in Latin America - DE',
    'journal_se_asia_en' => 'GIGA Journal of Current Southeast Asian Affairs - EN',
    'journal_se_asia_de' => 'GIGA Journal of Current Southeast Asian Affairs - DE',

    'working_papers_en' => 'GIGA Working Papers - EN',
    'working_papers_de' => 'GIGA Working Papers - DE',
  ];

  /** @param array of request data.
   */
  public $request_data = [];

  /** @param $_GET data.
   */
  public $request_query = [];

  /** The found contact ID */
  public $contact_id;
  /** The found email ID */
  public $email_id;
  /**
   * This is the main entry point called by CiviCRM's router.
   *
   * It handles the HTTP input and output.
   */
  public function run() {
    CRM_Core_Error::debug_log_message(__CLASS__ . ' GET: ' . json_encode($_GET));
    CRM_Core_Error::debug_log_message(__CLASS__ . ' POST: ' . json_encode($_POST));
    try {
      // Store _GET in request_query
      $this->request_query = $_GET;
      $this->checkPSK();
      if (empty($_GET['method'])
        || !in_array($_GET['method'], [
          'getContactData',
          'setContactData',
          'getContactHash',
          'addSubscriber'])) {
          CRM_Core_Error::debug_log_message(__CLASS__ . ': Missing or unknown method in request');
          throw new CRM_Newsstoregiga_WebAPIException('Bad request. Method name', 400);
      }
      // Parse the data sent.
      $request_body = file_get_contents('php://input');
      if ($request_body) {
        $this->request_data = json_decode($request_body, TRUE);
      }
      // Process the call.
      civicrm_initialize();
      $method = $_GET['method'];
      CRM_Core_Error::debug_log_message(__CLASS__ . ': Running ' . $method);
      try {
        $data_response = $this->$method();
        if ($data_response) {
          $data_response = json_encode($data_response);
          header('Content-Type: Application/json;charset=UTF-8');
          header('Content-Length: ' . strlen($data_response));
          echo $data_response;
          exit;
        }
      }
      catch (CRM_Newsstoregiga_WebAPIException $e) {
        // Handle this in the next catch.
        throw $e;
      }
      catch (Exception $e) {
        // Generic extensions, wrap them.
        throw new CRM_Newsstoregiga_WebAPIException( 'Server Error. Exception: ' . $e->getMessage(), 500);
      }
    }
    catch (CRM_Newsstoregiga_WebAPIException $e) {
      $code = $e->getCode();
      $msg = $e->getMessage();
      CRM_Core_Error::debug_log_message(__CLASS__ . ': FATAL ERROR ' . $code . ' ' . $msg);
      header("$_SERVER[SERVER_PROTOCOL] $code $msg");
      $data_response = json_encode(['error' => $msg]);
      header('Content-Type: Application/json;charset=UTF-8');
      header('Content-Length: ' . strlen($data_response));
      echo $data_response;
      exit;
    }

  }
  /**
   * Check PSK matches.
   */
  public function checkPSK() {
    if (empty($this->request_query['psk']) || $this->request_query['psk'] !== GIGA_EMAIL_SUBSCRIPTION_API_PSK) {
      throw new CRM_Newsstoregiga_WebAPIException('Unauthorised. Key failure.', 401);
    }
  }
  /**
   * Create checksum for the contact identified by the email given.
   */
  public function getContactHash() {
    $contact_id = $this->findContactByemail(FALSE);
    $checksum = CRM_Contact_BAO_Contact_Utils::generateChecksum($contact_id, NULL, 24);
    return ['hash' => $checksum];
  }
  /**
   * Create checksum for the contact identified by the email given.
   */
  public function getContactData() {
    $contact_id = $this->findContactByemail(TRUE);

    // Look up data.
    $response = civicrm_api3('Contact', 'getsingle', [
      'id' => $contact_id,
      'return' => ['individual_prefix', 'first_name', 'last_name'],
    ]);
    // Ensure we only return the same email.
    $response['email'] = $this->request_query['email'];
    // @todo Institution/Organisation
    // @todo Professional background
    // Each mapped group.
    foreach (static::$mapped_groups as $api_name => $civicrm_group_title) {
      $group_id = $this->getGroupIdFromTitle($civicrm_group_title);
      if (!$group_id) {
        // Cannot be in a group that does not exist!
        $result = FALSE;
      }
      else {
        $result = civicrm_api3('Contact', 'getcount', [
          'id' => $contact_id,
          'group' => $group_id,
        ]);
      }
      // Set 1 (in group) or 0 (not in group) on output.
      $response[$api_name] = $result ? 1 : 0;
    }
    return $response;
  }
  /**
   * Update an existing contact.
   */
  public function setContactData() {
    // Check hash and get contact data.
    $current_data = $this->getContactData(TRUE);

    // Look for changes to the Contact entity.
    $params = [];
    foreach (['individual_prefix', 'first_name', 'last_name'] as $field) {
      if (!empty($this->request_data[$field])
      && $this->request_data[$field] !== $current_data[$field])
      $params[$field] = $this->request_data[$field];
    }
    if ($params) {
      $this->validatePrefix();
      // Write changes to Contact entity.
      $params += ['id' => $this->contact_id];
      $result = civicrm_api3('Contact', 'create', $params);
    }

    // Update email, if we have new_email.
    if (!empty($this->request_data['new_email'])
      && $this->request_data['new_email'] != $current_data['email']) {

      // Update the appropriate email.
      $result = civicrm_api3('Email', 'create', [
        'id' => $this->email_id,
        'email' => $this->request_data['new_email'],
      ]);
    }

    // Update group membership.
    $groups_to_remove = $groups_to_add = [];
    foreach (static::$mapped_groups as $api_name => $civicrm_group_title) {
      if (isset($this->request_data[$api_name])
          && isset($current_data[$api_name])
          && $current_data[$api_name] != $this->request_data[$api_name]
          ) {
        // Look up the group ID.
        $group_id = $this->getGroupIdFromTitle($civicrm_group_title);
        if (!$group_id) {
          // If the group doesn't exist (any longer) then we cannot
          // process it.
          continue;
        }

        if ($this->request_data[$api_name]) {
          $groups_to_add[] = $group_id;
        }
        else {
          $groups_to_remove[] = $group_id;
        }
      }
    }
    if ($groups_to_remove) {
      foreach ($groups_to_remove as $group_id) {
        civicrm_api3('GroupContact', 'create', [
          'group_id'   => $group_id,
          'contact_id' => $this->contact_id,
          'status'     => 'Removed',
        ]);
      }
    }
    if ($groups_to_add) {
      foreach ($groups_to_add as $group_id) {
        civicrm_api3('GroupContact', 'create', [
          'group_id'   => $group_id,
          'contact_id' => $this->contact_id,
          'status'     => 'Added',
          'email_id'   => $this->email_id,
        ]);
      }
    }
    return [];
  }
  /**
   * Create a contact.
   *
   * If the email exists against multiple contacts, we cannot continue.
   *
   * If the email exists against one contact, we cannot continue but we return
   * an 'Authentication Required' error and helpfully include the hash required
   * to authenticate. The website should then send an email to the person with
   * the authentication link in it. This will ensure the person submitting the
   * form has control over the email address.
   *
   * If the email is not found, a new record is created and an empty array is returned (like from setContactData).
   *
   * @return Array.
   */
  public function addSubscriber() {

    // We require an email.
    if (empty($this->request_data['new_email'])) {
      throw new CRM_Newsstoregiga_WebAPIException('Bad Request. Email is required to create a contact.', 400);
    }

    // Do we already have a contact record for this email?
    $contact_id = $this->findContactByemail($validate_checksum=FALSE, $ok_if_not_found=TRUE, $this->request_data['new_email']);

    if ($contact_id) {
      // We already have a contact ID.
      // Continuing could result in someone changing someone else's data - we
      // only allow changes to data after authentication via the hash sent by
      // email route.
      $checksum = CRM_Contact_BAO_Contact_Utils::generateChecksum($contact_id, NULL, 24);
      return [
        'error' => 'Authentication Required',
        'hash'  => $checksum,
      ];
    }

    // OK, email did not exist so we're OK to create the contact now.

    // Check we have all the required data.

    // We require at least one subscription, otherwise what's the point.
    $groups_to_add = [];
    foreach (static::$mapped_groups as $api_name => $civicrm_group_title) {
      if (!empty($this->request_data[$api_name])) {

        $group_id = $this->getGroupIdFromTitle($civicrm_group_title);
        if (!$group_id) {
          // If the group doesn't exist (any longer) then we cannot
          // process it.
          continue;
        }

        if (!empty($this->request_data[$api_name])) {
          $groups_to_add[] = $group_id;
        }
      }
    }
    if (!$groups_to_add){
      throw new CRM_Newsstoregiga_WebAPIException('Bad Request. At least one subscription is required.', 400);
    }

    // We require at least something of a name.
    $params = [];
    foreach (['individual_prefix', 'first_name', 'last_name'] as $field) {
      if (!empty($this->request_data[$field])) {
        $params[$field] = $this->request_data[$field];
      }
    }
    if (!$params) {
      throw new CRM_Newsstoregiga_WebAPIException('Bad Request. Name is required to create a contact.', 400);
    }
    $this->validatePrefix();

    // OK, all looks fine to proceed. Write changes to Contact entity.
    $params['contact_type'] = 'Individual';
    $result = civicrm_api3('Contact', 'create', $params);
    $this->contact_id = $result['id'];

    // Set email, if we have new_email.
    $result = civicrm_api3('Email', 'create', [
      'contact_id'  => $this->contact_id,
      'email'       => $this->request_data['new_email'],
      'is_bulkmail' => 1,
    ]);
    $this->email_id = $result['id'];

    // Update group membership.
    foreach ($groups_to_add as $group_id) {
      civicrm_api3('GroupContact', 'create', [
        'group_id'   => $group_id,
        'contact_id' => $this->contact_id,
        'status'     => 'Added',
        'email_id'   => $this->email_id,
      ]);
    }

    return [];
  }
  /**
   * Find the contact by email.
   *
   * The email must belong to one contact and one contact only.
   *
   * @param bool $validate_checksum If set a contact is only returned if the hash is valid.
   *
   * @param bool $ok_if_not_found If set and the email is not found at all,
   *        just return NULL instead of throwing an exception. Used by the
   *        addSubscriber method.
   *
   * @return int|NULL contact id.
   *
   * @throws CRM_Newsstoregiga_WebAPIException if the email is missing, not
   *         found, or belongs to more than one contact.
   */
  public function findContactByEmail($validate_checksum=TRUE, $ok_if_not_found=FALSE, $email=NULL) {
    if ($email === NULL) {
      // Expect email in the request query.
      if (empty($this->request_query['email'])) {
        throw new CRM_Newsstoregiga_WebAPIException('Bad Request. email missing', 400);
      }
      $email = $this->request_query['email'];
    }

    // Look up the email.
    $result = civicrm_api3('Email', 'get', ['email' => $email]);
    // Count unique contacts (and email can sometimes be in twice but against
    // the same contact).
    $contacts = [];
    $emails = array_fill_keys(['bulk', 'primary', 'other'], FALSE);
    foreach ($result['values'] as $_) {
      $contacts[$_['contact_id']] = TRUE;
      $contact_id = $_['contact_id'];
      $priority = 'other';
      if ($_['is_bulkmail']) {
        $priority = 'bulk';
      }
      elseif ($_['is_primary']) {
        $priority = 'primary';
      }
      $emails[$priority] = $_['id'];
    }
    if (count($contacts) == 0 && $ok_if_not_found) {
      // Email was not found, but that's ok.
      return;
    }
    if (count($contacts) != 1) {
      // Either not found (and that's not OK), or found against multiple contacts.
      throw new CRM_Newsstoregiga_WebAPIException('Bad Request. Problem with input email.', 400);
    }

    if ($validate_checksum) {
      $hash = isset($this->request_query['hash']) ? $this->request_query['hash'] : '';
      if (!CRM_Contact_BAO_Contact_Utils::validChecksum($contact_id, $hash)) {
        throw new CRM_Newsstoregiga_WebAPIException('Unauthorised. Invalid hash.', 401);
      }
    }

    // OK!
    // Store contact_id and best email.
    $this->contact_id = $contact_id;
    // Store best email. Bulk if we have it, otherwise primary, otherwise whichever.
    foreach ($emails as $email_id) {
      if ($email_id !== FALSE) {
        $this->email_id = $email_id;
        break;
      }
    }
    return $contact_id;
  }
  /**
   * Find CiviCRM Group ID from Group Name.
   *
   * The  CiviCRM Contact API (as of 4.7.25 at least) is a bit quirky. I think
   * you're supposed to be able to provide a group name to it but sometimes
   * this gives a no such column error.
   *
   * Sending it Ids seems more reliable, so we do that work here.
   */
  public function getGroupIdFromTitle($group_title) {
    if (!isset($this->group_id_cache)) {
      $result = civicrm_api3('Group', 'get', [
        'title' => ['IN' => array_values(static::$mapped_groups)],
        'return' => 'id,title',
      ]);
      $this->group_id_cache = [];
      foreach ($result['values'] as $_) {
        $this->group_id_cache[$_['title']] = $_['id'];
      }
    }
    return isset($this->group_id_cache[$group_title])
      ? $this->group_id_cache[$group_title]
      : FALSE;
  }
  public function validatePrefix() {
    if (!empty($this->request_data['individual_prefix'])) {
      $result = civicrm_api3('OptionValue', 'getcount', [
        'option_group_id' => "individual_prefix",
        'name' => $this->request_data['individual_prefix'],
      ]);
      if (!$result) {
        throw new CRM_Newsstoregiga_WebAPIException('Bad Request. Unknown prefix', 400);
      }
    }
  }
}

