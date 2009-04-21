<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 Timo Schmidt <schmidt@aoemedia.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/


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
require_once(t3lib_extMgm::extPath('l10nmgr').'controller/class.tx_l10nmgr_controller_export.php');

$front = new tx_mvc_backendModuleFrontController();
$front->setExtensionKey('l10nmgr');
$front->setModuleKey('txl10nmgrM3_tx_l10nmgr_export');
$front->process('export');


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/aoe_sajan/controller/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/aoe_sajan/controller/index.php']);
}

?>