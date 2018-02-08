<?php
/***************************************************************
 * Extension Manager/Repository config file for ext "l10nmgr".
 * Auto generated 10-03-2015 18:54
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/
$EM_CONF[$_EXTKEY] = array(
  'title' => 'Localization Manager',
  'description' => 'Module for managing localization import and export',
  'category' => 'module',
  'version' => '8.2.4',
  'state' => 'beta',
  'uploadfolder' => false,
  'createDirs' => 'uploads/tx_l10nmgr/settings,uploads/tx_l10nmgr/saved_files,uploads/tx_l10nmgr/jobs,uploads/tx_l10nmgr/jobs/out,uploads/tx_l10nmgr/jobs/in,uploads/tx_l10nmgr/jobs/done,uploads/tx_l10nmgr/jobs/_cmd',
  'clearCacheOnLoad' => true,
  'author' => 'Kasper Skaarhoej, Daniel Zielinski, Daniel Poetzinger, Fabian Seltmann, Andreas Otto, Jo Hasenau, Peter Russ',
  'author_email' => 'kasperYYYY@typo3.com, info@loctimize.com, info@cybercraft.de, pruss@uon.li',
  'author_company' => 'Localization Manager Team',
  'constraints' => array(
    'depends' => array(
      'typo3' => '8.7.0-8.99.99',
      'static_info_tables' => '6.4.2-0.0.0'
    ),
    'conflicts' => array(),
    'suggests' => array()
  )
);
