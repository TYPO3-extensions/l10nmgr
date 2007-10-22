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
 * Module 'L10N Manager' for the 'tx_l10nmgr' extension.
 *
 * @author	Kasper Skårhøj <kasperYYYY@typo3.com>
 */



	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require ("conf.php");
require ($BACK_PATH."init.php");
require ($BACK_PATH."template.php");
$LANG->includeLLFile("EXT:tx_l10nmgr/mod1/locallang.xml");
require_once (PATH_t3lib."class.t3lib_scbase.php");
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]








/**
 * Translation management tool
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage tx_l10nmgr
 */
class tx_l10nmgr_module1 extends t3lib_SCbase {

	var $pageinfo;

	/**
	 * Initializes the Module
	 *
	 * @return	void
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		parent::init();
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return	void
	 */
	function menuConfig()	{
		global $LANG;
		$this->MOD_MENU = Array (
			"function" => Array (
				"1" => $LANG->getLL("function1"),
				"2" => $LANG->getLL("function2"),
				"3" => $LANG->getLL("function3"),
			)
		);
		parent::menuConfig();
	}

	/**
	 * Main function of the module. Write the content to $this->content
	 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	 *
	 * @return	[type]		...
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

			// Draw the header.
		$this->doc = t3lib_div::makeInstance("mediumDoc");
		$this->doc->backPath = $BACK_PATH;
		$this->doc->form='<form action="" method="POST">';

			// JavaScript
		$this->doc->JScode = '
			<script language="javascript" type="text/javascript">
				script_ended = 0;
				function jumpToUrl(URL)	{
					document.location = URL;
				}
			</script>
		';

		$this->content.=$this->doc->startPage($LANG->getLL("title"));
		$this->content.=$this->doc->header($LANG->getLL("title"));
		$this->content.=$this->doc->divider(5);

		// Render content:
		$this->moduleContent();

		$this->content.=$this->doc->spacer(10);
	}

	/**
	 * Prints out the module HTML
	 *
	 * @return	void
	 */
	function printContent()	{

		$this->content.=$this->doc->endPage();
		echo $this->content;
	}

	/**
	 * Generates the module content
	 *
	 * @return	void
	 */
	function moduleContent()	{
		$configurations = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			'tx_l10nmgr_cfg',
			'1'.t3lib_BEfunc::deleteClause('tx_l10nmgr_cfg')
		);
		
		$tableRow = '';
		$tableRow.= '
			<tr class="bgColor5 tableheader">
				<td>Title:</td>
				<td>Path:</td>
				<td>Depth:</td>
				<td>Tables:</td>
				<td>Exclude:</td>
				<td>Include</td>
			</tr>
		
		';
		
		$this->perms_clause = $GLOBALS['BE_USER']->getPagePermsClause(1);
		
		foreach($configurations as $cfg)	{
			if (is_array(t3lib_BEfunc::readPageAccess($cfg['pid'],$this->perms_clause)))	{
				$tableRow.= '
					<tr class="bgColor3">
						<td nowrap="nowrap"><a href="'.htmlspecialchars('../cm1/index.php?id='.$cfg['uid'].'&srcPID='.$this->id).'"><u>'.htmlspecialchars($cfg['title']).'</u></a></td>
						<td nowrap="nowrap">'.current(t3lib_BEfunc::getRecordPath($cfg['pid'], '1', 20, 50)).'</td>
						<td>'.htmlspecialchars($cfg['depth']).'</td>
						<td>'.htmlspecialchars($cfg['tablelist']).'</td>
						<td>'.htmlspecialchars($cfg['exclude']).'</td>
						<td>'.htmlspecialchars($cfg['include']).'</td>
					</tr>
			
				';
			}
		}
		
		$this->content.=$this->doc->section('Task configurations:','<table border="1" cellpadding="1" cellspacing="0">'.$tableRow.'</table>');
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tx_l10nmgr/mod1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tx_l10nmgr/mod1/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_l10nmgr_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>
