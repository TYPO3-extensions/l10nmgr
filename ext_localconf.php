<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig('
	options.saveDocNew.tx_l10nmgr_cfg=1
	options.saveDocNew.tx_l10nmgr_priorities=1
');

if (TYPO3_MODE == 'BE') {
    // Setting up scripts that can be run from the cli_dispatch.phpsh script.
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys']['l10nmgr_import'] = array(
        'EXT:' . $_EXTKEY . '/Classes/Cli/Import.php',
        '_CLI_user'
    );
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys']['l10nmgr_export'] = array(
        'EXT:' . $_EXTKEY . '/Classes/Cli/Export.php',
        '_CLI_user'
    );
}

//! increase with every change to XML Format
define('L10NMGR_FILEVERSION', '1.2');
define('L10NMGR_VERSION', '5.0.0');

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['lowlevel']['cleanerModules']['tx_l10nmgr_index'] = array('EXT:l10nmgr/Classes/Index.php:Index');
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['tx_l10nmgr'] = 'EXT:l10nmgr/Classes/Hooks/Tcemain.php:&Tcemain';
$_EXTCONF_ARRAY = unserialize($_EXTCONF);
if ($_EXTCONF_ARRAY['enable_stat_hook']) {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['recStatInfoHooks']['tx_l10nmgr'] = 'EXT:l10nmgr/Classes/Hooks/Tcemain.php:&Tcemain->stat';
}

// Add file cleanup task
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['LocalizationmanagerFileGarbageCollection'] = array(
    'extension' => $_EXTKEY,
    'title' => 'LLL:EXT:' . $_EXTKEY . 'Resources/Private/Language/Task/locallang.xml:fileGarbageCollection.name',
    'description' => 'LLL:EXT:' . $_EXTKEY . 'Resources/Private/Language/Task/locallang.xml:fileGarbageCollection.description',
    'additionalFields' => 'LocalizationmanagerFileGarbageCollectionAdditionalFieldProvider',
);
