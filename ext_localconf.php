<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_l10nmgr_cfg=1
	options.saveDocNew.tx_l10nmgr_priorities=1
');

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['lowlevel']['cleanerModules']['tx_l10nmgr_index'] = array('EXT:tx_l10nmgr/class.tx_l10nmgr_index.php:tx_l10nmgr_index');
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['tx_l10nmgr'] = 'EXT:tx_l10nmgr/class.tx_l10nmgr_tcemain_hook.php:&tx_l10nmgr_tcemain_hook';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['recStatInfoHooks']['tx_l10nmgr'] = 'EXT:tx_l10nmgr/class.tx_l10nmgr_tcemain_hook.php:&tx_l10nmgr_tcemain_hook->stat';

?>
