<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_l10nmgr_cfg=1
	options.saveDocNew.tx_l10nmgr_priorities=1
');

if (TYPO3_MODE=='BE')    {
    // Setting up scripts that can be run from the cli_dispatch.phpsh script.
    $TYPO3_CONF_VARS['SC_OPTIONS']['GLOBAL']['cliKeys']['l10nmgr_import'] = array('EXT:'.$_EXTKEY.'/cli/cli.import.php','_CLI_user');
    $TYPO3_CONF_VARS['SC_OPTIONS']['GLOBAL']['cliKeys']['l10nmgr_export'] = array('EXT:'.$_EXTKEY.'/cli/cli.export.php','_CLI_user');
}

//! increase with every change to XML Format
define('L10NMGR_FILEVERSION','1.2');

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['lowlevel']['cleanerModules']['tx_l10nmgr_index'] = array('EXT:l10nmgr/class.tx_l10nmgr_index.php:tx_l10nmgr_index');
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['tx_l10nmgr'] = 'EXT:l10nmgr/class.l10nmgr_tcemain_hook.php:&tx_l10nmgr_tcemain_hook';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['recStatInfoHooks']['tx_l10nmgr'] = 'EXT:l10nmgr/class.l10nmgr_tcemain_hook.php:&tx_l10nmgr_tcemain_hook->stat';

// define some classes

// This class is used as a exportStateRepository withing the exportData class. The class has to be an instance (or inheriting) from tx_l10nmgr_models_exporter_workflowStateRepository
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['l10nmgr']['classes']['exportData_workflowStateRepository'] = 'EXT:l10nmgr/models/exporter/class.tx_l10nmgr_models_exporter_workflowStateRepository.php:tx_l10nmgr_models_exporter_workflowStateRepository';

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['l10nmgr']['exportPostProcessing'][] = 'EXT:l10nmgr/models/hooks/class.tx_l10nmgr_models_hooks_emailNotifier.php:tx_l10nmgr_models_hooks_emailNotifier->notify';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['l10nmgr']['exportPostProcessing'][] = 'EXT:l10nmgr/models/hooks/class.tx_l10nmgr_models_hooks_ftpUploader.php:tx_l10nmgr_models_hooks_ftpUploader->upload';

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['importExtensions_additionalDatabaseFiles'][] = 'EXT:l10nmgr/ext_tables.sql';

?>