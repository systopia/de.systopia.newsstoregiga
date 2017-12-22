<?php
use CRM_Newsstoregiga_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_Newsstoregiga_Upgrader extends CRM_Newsstoregiga_Upgrader_Base {
  /**
   * Create the custom fields.
   *
   * @return TRUE on success
   * @throws Exception
   */
  protected function upgrade_4700() {
    $this->ctx->log->info('Applying update 4700: Create custom fields.');

    // Ensure the custom fieldset is installed.
    static::executeCustomDataFile('xml/auto_install.xml');

    /*
    // Below is the original code to generate the custom fields.
    // I think the xml way is more modern as it is documented at https://docs.civicrm.org/dev/en/latest/extensions/civix/#generate-upgrader
    // The above code is necessary to ensure the xml runs on sites that already have this extension installed.
    //


    // Create the custom field group.
    $custom_group = civicrm_api3('CustomGroup', 'create', [
      'name' => 'Subscriber_Details',
      'title' => 'Subscriber Details',
      'extends' => 'Contact',
      'style' => 'inline',
      'collapse_display' => 0,
      'is_multiple' => 0,
    ]);
    // Create the institution field.
    $result = civicrm_api3('CustomField', 'create', [
      "custom_group_id" => $custom_group['id'],
      "name"            => "Institution_Organisation",
      "label"           => "Institution / Organisation",
      "data_type"       => "String",
      "html_type"       => "Text",
      "is_required"     => "0",
      "is_searchable"   => "0",
      "is_search_range" => "0",
      "weight"          => "1",
      "is_active"       => "1",
      "is_view"         => "0",
      "text_length"     => "255",
    ]);
    // Create the professional_background field.
    // ...First we need a set of options.
    // ......Create an option group.
    $opts = civicrm_api3('OptionGroup', 'create', [
      'name'      => 'professional_background',
      'title'     => 'Professional Background',
      'data_type' => 'string',
    ]);
    // ......Create the options
    foreach ([
      'research' => 'Research',
      'agency' => 'Ministry / Agency',
      'policy' => 'Parliament / Political Parties',
      'foundation' => 'Political Foundation',
      'ngo' => 'NGO',
      'media' => 'Media',
      'business' => 'Business',
      'other' => 'Other',
    ] as $name => $title) {
      $result = civicrm_api3('OptionValue', 'create', [
        'option_group_id' => $opts['id'],
        'name' => $name,
        'value' => $name,
        'label' => $title,
      ]);
    }
    // ...Create the professional_background field.
    $result = civicrm_api3('CustomField', 'create', [
      "custom_group_id" => $custom_group['id'],
      "name"            => "professional_background",
      "label"           => "Professional Background",
      "data_type"       => "String",
      "html_type"       => "Select",
      "is_required"     => "0",
      "is_searchable"   => "0",
      "is_search_range" => "0",
      "text_length"     => "255",
      "option_group_id" => $opts['id'],
      "in_selector"     => "0"
    ]);

     */
    return TRUE;
  }

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Example: Run an external SQL script when the module is installed.
   *
  public function install() {
    $this->executeSqlFile('sql/myinstall.sql');
  }

  /**
   * Example: Work with entities usually not available during the install step.
   *
   * This method can be used for any post-install tasks. For example, if a step
   * of your installation depends on accessing an entity that is itself
   * created during the installation (e.g., a setting or a managed entity), do
   * so here to avoid order of operation problems.
   *
  public function postInstall() {
    $customFieldId = civicrm_api3('CustomField', 'getvalue', array(
      'return' => array("id"),
      'name' => "customFieldCreatedViaManagedHook",
    ));
    civicrm_api3('Setting', 'create', array(
      'myWeirdFieldSetting' => array('id' => $customFieldId, 'weirdness' => 1),
    ));
  }

  /**
   * Example: Run an external SQL script when the module is uninstalled.
   *
  public function uninstall() {
   $this->executeSqlFile('sql/myuninstall.sql');
  }

  /**
   * Example: Run a simple query when a module is enabled.
   *
  public function enable() {
    CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 1 WHERE bar = "whiz"');
  }

  /**
   * Example: Run a simple query when a module is disabled.
   *
  public function disable() {
    CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 0 WHERE bar = "whiz"');
  }

  /**
   * Example: Run a couple simple queries.
   *
   * @return TRUE on success
   * @throws Exception
   *
  public function upgrade_4200() {
    $this->ctx->log->info('Applying update 4200');
    CRM_Core_DAO::executeQuery('UPDATE foo SET bar = "whiz"');
    CRM_Core_DAO::executeQuery('DELETE FROM bang WHERE willy = wonka(2)');
    return TRUE;
  } // */


  /**
   * Example: Run an external SQL script.
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4201() {
    $this->ctx->log->info('Applying update 4201');
    // this path is relative to the extension base dir
    $this->executeSqlFile('sql/upgrade_4201.sql');
    return TRUE;
  } // */


  /**
   * Example: Run a slow upgrade process by breaking it up into smaller chunk.
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4202() {
    $this->ctx->log->info('Planning update 4202'); // PEAR Log interface

    $this->addTask(E::ts('Process first step'), 'processPart1', $arg1, $arg2);
    $this->addTask(E::ts('Process second step'), 'processPart2', $arg3, $arg4);
    $this->addTask(E::ts('Process second step'), 'processPart3', $arg5);
    return TRUE;
  }
  public function processPart1($arg1, $arg2) { sleep(10); return TRUE; }
  public function processPart2($arg3, $arg4) { sleep(10); return TRUE; }
  public function processPart3($arg5) { sleep(10); return TRUE; }
  // */


  /**
   * Example: Run an upgrade with a query that touches many (potentially
   * millions) of records by breaking it up into smaller chunks.
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4203() {
    $this->ctx->log->info('Planning update 4203'); // PEAR Log interface

    $minId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(min(id),0) FROM civicrm_contribution');
    $maxId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(max(id),0) FROM civicrm_contribution');
    for ($startId = $minId; $startId <= $maxId; $startId += self::BATCH_SIZE) {
      $endId = $startId + self::BATCH_SIZE - 1;
      $title = E::ts('Upgrade Batch (%1 => %2)', array(
        1 => $startId,
        2 => $endId,
      ));
      $sql = '
        UPDATE civicrm_contribution SET foobar = whiz(wonky()+wanker)
        WHERE id BETWEEN %1 and %2
      ';
      $params = array(
        1 => array($startId, 'Integer'),
        2 => array($endId, 'Integer'),
      );
      $this->addTask($title, 'executeSql', $sql, $params);
    }
    return TRUE;
  } // */

}
