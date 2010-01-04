<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Kasper Skårhøj <kasperYYYY@typo3.com>
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
/**
 * Module 'L10N Manager' for the 'l10nmgr' extension.
 *
 * @author	Kasper Skårhøj <kasperYYYY@typo3.com>
 */

	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require ("conf.php");
require ($BACK_PATH."init.php");
require ($BACK_PATH."template.php");
$LANG->includeLLFile("EXT:l10nmgr/mod3/locallang.xml");
require_once (PATH_t3lib."class.t3lib_scbase.php");
require_once(t3lib_extMgm::extPath('l10nmgr') . 'views/class.tx_l10nmgr_template.php');
$BE_USER->modAccess($MCONF,1);

/**
 * Translation management tool
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage tx_l10nmgr
 */
class tx_l10nmgr_module0 extends t3lib_SCbase {

	var $pageinfo;

	/**
	 * Initializes the Module
	 *
	 * @return	void
	 */
	function init() {
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		parent::init();
	}

	/**
	 * Main function of the module. Write the content to $this->content
	 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	 *
	 * @return	void
	 */
	function main() {
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS,$_EXTKEY;

			// Draw the header.
		$this->doc            = t3lib_div::makeInstance("bigDoc");
		$this->doc->backPath  = $BACK_PATH;
		$this->doc->form      = '<form action="" method="POST">';

		$configurationObjectsArray = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
                        '*',
			'tx_l10nmgr_cfg',
                        '1'.t3lib_BEfunc::deleteClause('tx_l10nmgr_cfg')
                 );

			// JavaScript
		$this->doc->JScode = '
			<link rel="stylesheet" type="text/css" href="' . t3lib_div::resolveBackPath($BACK_PATH . t3lib_extMgm::extRelPath('l10nmgr') . 'templates/mod1_list.css') . '" />
			<link rel="stylesheet" type="text/css" href="' . t3lib_div::resolveBackPath($BACK_PATH . t3lib_extMgm::extRelPath('l10nmgr') . 'res/contrib/jquery.tooltip.css') . '" />
			<script language="javascript" type="text/javascript" src="' . t3lib_div::resolveBackPath($BACK_PATH . t3lib_extMgm::extRelPath('l10nmgr') . 'res/contrib/webtoolkit.scrollabletable.js') . '"></script>
			<script language="javascript" type="text/javascript" src="' . t3lib_div::resolveBackPath($BACK_PATH . t3lib_extMgm::extRelPath('l10nmgr') . 'res/contrib/jquery-1.2.3.js') . '"></script>
			<script language="javascript" type="text/javascript" src="' . t3lib_div::resolveBackPath($BACK_PATH . t3lib_extMgm::extRelPath('l10nmgr') . 'res/contrib/jquery.scrollable.js') . '"></script>
			<script language="javascript" type="text/javascript" src="' . t3lib_div::resolveBackPath($BACK_PATH . t3lib_extMgm::extRelPath('l10nmgr') . 'res/contrib/jquery.dimensions.js') . '"></script>
			<script language="javascript" type="text/javascript" src="' . t3lib_div::resolveBackPath($BACK_PATH . t3lib_extMgm::extRelPath('l10nmgr') . 'res/contrib/jquery.tooltip.js') . '"></script>
			<script language="javascript" type="text/javascript" src="' . t3lib_div::resolveBackPath($BACK_PATH . t3lib_extMgm::extRelPath('l10nmgr') . 'templates/mod1_list.js') . '"></script>
		';

		$TemplateClass = t3lib_div::makeInstanceClassName('tx_l10nmgr_template');
		/* @var $Template tx_l10nmgr_template */
		$Template = new $TemplateClass(
							$configurationObjectsArray,
							t3lib_div::resolveBackPath($BACK_PATH . t3lib_extMgm::extRelPath('l10nmgr') . 'templates/mod1_list.php')
						);
		$Template->setDocument($this->doc);
		$Template->setPageId($this->id);
		$this->content = $Template->render();
	}

	/**
	 * Prints out the module HTML
	 *
	 * @return	void
	 */
	function printContent() {
		print $this->content;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/mod3/index.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/mod3/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_l10nmgr_module0');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>
