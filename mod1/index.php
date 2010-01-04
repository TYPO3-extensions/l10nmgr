<?php

unset($MCONF);

require_once('conf.php');
require_once($BACK_PATH.'init.php');
require_once($BACK_PATH.'template.php');

// TODO: this should go into the backend action controller
//$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.


require_once t3lib_extMgm::extPath('mvc').'mvc/class.tx_mvc_backendModuleFrontController.php';
require_once t3lib_extMgm::extPath('mvc').'common/class.tx_mvc_common_classloader.php';

tx_mvc_common_classloader::loadAll();

// controller class
require_once(t3lib_extMgm::extPath('l10nmgr').'controller/class.tx_l10nmgr_controller_list.php');

$front = new tx_mvc_backendModuleFrontController();
$front->setExtensionKey('l10nmgr');
$front->setModuleKey('txl10nmgrM3_txl10nmgrM1');
$front->process('list');


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/export/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/export/index.php']);
}

?>