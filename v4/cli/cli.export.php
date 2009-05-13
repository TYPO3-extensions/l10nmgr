<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 Fabrizio Branca (fabrizio.branca@aoemedia.de)
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

if (!defined('TYPO3_cliMode'))  die('You cannot run this script directly!');

require_once t3lib_extMgm::extPath('mvc').'mvc/class.tx_mvc_cliFrontController.php';

// controller class
require_once(t3lib_extMgm::extPath('l10nmgr').'controller/class.tx_l10nmgr_controller_exportCli.php');

// Load language support
require_once(t3lib_extMgm::extPath('lang') . 'lang.php');
$LANG = t3lib_div::makeInstance('language');

$front = new tx_mvc_cliFrontController();
$front->setExtensionKey('l10nmgr');
$front->setConfiguration(array(
	'pagesPerChunk' => 5,
	'viewHelper.' => array(
		'disable.' => array(
			'linkCreator' => 1,
			'label' => 1,
			'fieldRenderer' => 1,
			'backend_fieldRenderer' => 1,
			'tcaFieldRenderer' => 1,
			'formElementRenderer' => 1
		)
	)
));
$front->process('exportCli');


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/export/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/export/index.php']);
}

?>