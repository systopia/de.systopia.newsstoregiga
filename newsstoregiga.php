<?php

require_once 'newsstoregiga.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function newsstoregiga_civicrm_config(&$config) {
  _newsstoregiga_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param array $files
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function newsstoregiga_civicrm_xmlMenu(&$files) {
  _newsstoregiga_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function newsstoregiga_civicrm_install() {
  _newsstoregiga_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function newsstoregiga_civicrm_uninstall() {
  _newsstoregiga_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function newsstoregiga_civicrm_enable() {
  _newsstoregiga_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function newsstoregiga_civicrm_disable() {
  _newsstoregiga_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function newsstoregiga_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _newsstoregiga_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function newsstoregiga_civicrm_managed(&$entities) {
  // SLOW: _newsstoregiga_civix_civicrm_managed($entities);

  $common = [
    'module' => 'de.systopia.newsstoregiga',
    'entity' => 'NewsStoreSource',
  ];
  $common_params = [
    'version' => 3,
    'retention_days' => 60,
    'fetch_frequency' => "daily",
    'type' => "Rss",
  ];

  foreach ([
    'GIGA Focus Global EN' => 'https://www.giga-hamburg.de/en/feed/civicrm/focus/49',
    'GIGA Focus Global DE' => 'https://www.giga-hamburg.de/de/feed/civicrm/focus/49',
    'GIGA Focus Focus Afrika' => 'https://www.giga-hamburg.de/de/feed/civicrm/focus/46',
    'GIGA Focus Focus Africa' => 'https://www.giga-hamburg.de/en/feed/civicrm/focus/46',
    'GIGA Focus Asien' => 'https://www.giga-hamburg.de/de/feed/civicrm/focus/6',
    'GIGA Focus Asia' => 'https://www.giga-hamburg.de/en/feed/civicrm/focus/6',
    'GIGA Focus Lateinamerika' => 'https://www.giga-hamburg.de/de/feed/civicrm/focus/48',
    'GIGA Focus Latin America' => 'https://www.giga-hamburg.de/en/feed/civicrm/focus/48',
    'GIGA Focus Nahost' => 'https://www.giga-hamburg.de/de/feed/civicrm/focus/47',
    'GIGA Focus Middle East' => 'https://www.giga-hamburg.de/en/feed/civicrm/focus/47',
    'GIGA Working Papers DE' => 'https://www.giga-hamburg.de/de/feed/civicrm/wp',
    'GIGA Working Papers EN' => 'https://www.giga-hamburg.de/en/feed/civicrm/wp',
    'Africa Spectrum DE' => 'https://www.giga-hamburg.de/de/feed/civicrm/news/1655',
    'Africa Spectrum EN' => 'https://www.giga-hamburg.de/en/feed/civicrm/news/1655',
    'Journal of Current Chinese Affairs DE' => 'https://www.giga-hamburg.de/de/feed/civicrm/news/1657',
    'Journal of Current Chinese Affairs EN' => 'https://www.giga-hamburg.de/en/feed/civicrm/news/1657',
    'Journal of Politics in Latin America DE' => 'https://www.giga-hamburg.de/de/feed/civicrm/news/1659',
    'Journal of Politics in Latin America EN' => 'https://www.giga-hamburg.de/en/feed/civicrm/news/1659',
    'Journal of Current Southeast Asian Affairs DE' => 'https://www.giga-hamburg.de/de/feed/civicrm/news/1661',
    'Journal of Current Southeast Asian Affairs EN' => 'https://www.giga-hamburg.de/en/feed/civicrm/news/1661',
  ] as $name => $uri) {
    $entities[] = $common + [
      'name' => $name,
      'params' => $common_params + [ 'uri' => $uri, 'name' => $name ]
    ];
  }

}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * @param array $caseTypes
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function newsstoregiga_civicrm_caseTypes(&$caseTypes) {
  _newsstoregiga_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function newsstoregiga_civicrm_angularModules(&$angularModules) {
_newsstoregiga_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function newsstoregiga_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _newsstoregiga_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

function newsstoregiga_civicrm_permission(&$permissions) {
  $permissions['access GIGA subscriptions API'] = [
    ts('NewsStoreGIGA: Access GIGA subscriptions API'),
    ts('This should be given to anonymous users since the website that consumes this API is not authenticated.'),
  ];
}
/**
 * Functions below this ship commented out. Uncomment as required.
 *

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function newsstoregiga_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function newsstoregiga_civicrm_navigationMenu(&$menu) {
  _newsstoregiga_civix_insert_navigation_menu($menu, NULL, array(
    'label' => ts('The Page', array('domain' => 'de.systopia.newsstoregiga')),
    'name' => 'the_page',
    'url' => 'civicrm/the-page',
    'permission' => 'access CiviReport,access CiviContribute',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _newsstoregiga_civix_navigationMenu($menu);
} // */

/**
 * Implements hook_newsstoremailer_formatters
 */
function newsstoregiga_newsstoremailer_formatters(&$formatters) {
  $formatters += [
    'CRM_NewsstoreMailer_GigaFocus' => 'GIGA Focus',
    'CRM_NewsstoreMailer_GigaJournalFamily' => 'GIGA Journal Family',
    'CRM_NewsstoreMailer_GigaWorkingPapers' => 'GIGA Working Papers',
  ];
}
