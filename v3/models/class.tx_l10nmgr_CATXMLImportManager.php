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

require_once(t3lib_extMgm::extPath('l10nmgr').'models/class.tx_l10nmgr_translationData.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/tools/class.tx_l10nmgr_xmltools.php');

/**
 * Function for managing the Import of CAT XML
 *
 * @author	Daniel Poetzinger <ext@aoemedia.de>
 *
 * @package TYPO3
 * @subpackage tx_l10nmgr
 */
class tx_l10nmgr_CATXMLImportManager {

	/**
	 * @var	string		$file		filepath with XML
	 */
	var $file = '';

	/**
	 * @var	string		$xmlNodes		parsed XML
	 */
	var $xmlNodes = '';

	/**
	 * @var	string		$headerData		headerData of the XML
	 */
	var $headerData = '';

	/**
	 * @var	integer		$sysLang		selected import language (for check purposes - sys_language_uid)
	 */
	var $sysLang;

	/**
	 * @var	array		$_errorMsg		accumulated errormessages
	 */
	var $_errorMsg = array();

	function tx_l10nmgr_CATXMLImportManager($file, $sysLang) {
		$this->sysLang = $sysLang;
		$this->file    = $file;
	}

	function parseAndCheckXMLFile() {
		global $LANG;

		$fileContent    = t3lib_div::getUrl($this->file);
		$this->xmlNodes = t3lib_div::xml2tree(str_replace('&nbsp;',' ',$fileContent),3);	// For some reason PHP chokes on incoming &nbsp; in XML!

		if (!is_array($this->xmlNodes)) {
			$this->_errorMsg[] = $LANG->getLL('import.manager.error.parsing.xml2tree.message') . $this->xmlNodes;
			return false;
		}

		$headerInformationNodes = $this->xmlNodes['TYPO3LOC'][0]['ch']['head'][0]['ch'];
		if (!is_array($headerInformationNodes)) {
			$this->_errorMsg[] = $LANG->getLL('import.manager.error.missing.head.message');
			return false;
		}

		$this->_setHeaderData($headerInformationNodes);
		if ($this->_isIncorrectXMLFile()) {
			return false;
		}
	}

	function getErrorMessages() {
		return implode('<br />', $this->_errorMsg);
	}

	function &getXMLNodes() {
		return $this->xmlNodes;
	}

	function _isIncorrectXMLFile() {
		$error = array();

		if (!isset($this->headerData['FormatVersion']) || $this->headerData['FormatVersion'] != L10NMGR_FILEVERSION) {
			$error[] = sprintf(
							$LANG->getLL('import.manager.error.version.message'),
							$this->headerData['FormatVersion'],
							L10NMGR_FILEVERSION
						);
		}
		if (!isset($this->headerData['workspaceId']) || $this->headerData['workspaceId'] != $GLOBALS['BE_USER']->workspace) {
			$error[] = sprintf(
							$LANG->getLL('import.manager.error.workspace.message'),
							$GLOBALS['BE_USER']->workspace,
							$this->headerData['workspaceId']
						);
		}
		if (!isset($this->headerData['sysLang']) || $this->headerData['sysLang'] != $this->sysLang) {

			$error[] = sprintf(
							$LANG->getLL('import.manager.error.language.message'),
							$this->sysLang,
							$this->headerData['sysLang']
						);
		}
		if (count($error)>0) {
			$this->_errorMsg = array_merge($this->_errorMsg, $error);
			return true;
		}
		return false;
	}

	function _setHeaderData($headerInformationNodes) {

		if (!is_array($headerInformationNodes)) {
			return;
		}
		foreach ($headerInformationNodes as $k=>$v) {
			$this->headerData[$k]=$v[0]['values'][0];
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/models/class.tx_l10nmgr_translationDataFactory.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/models/class.tx_l10nmgr_translationDataFactory.php']);
}
?>
