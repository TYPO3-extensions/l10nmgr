<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Kasper Skårhøj <kasperYYYY@typo3.com>
*
*  @author	Fabian Seltmann <fs@marketing-factory.de>
*
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
* abstrakt Base class for rendering the export or htmllist of a l10ncfg
**/
class tx_l10nmgr_abstractExportView {

	/**
	 * @var	tx_l10nmgr_l10nConfiguration		$l10ncfgObj		The language configuration object
	 */
	var $l10ncfgObj;

	/**
	 * @var	integer		$sysLang		The sys_language_uid of language to export
	 */
	var $sysLang;

	/**
	*	 flags for controlling the fields which should render in the output:
	*/
	var $modeOnlyChanged=FALSE;
	var $modeOnlyNew=FALSE;

	function __construct($l10ncfgObj, $sysLang) {
		$this->sysLang = $sysLang;
		$this->l10ncfgObj = $l10ncfgObj;
	}

	function getExportType() {
		return $exportType;
	}

	function setModeOnlyChanged() {

		$this->modeOnlyChanged=TRUE;
	}
	function setModeOnlyNew() {
		$this->modeOnlyNew=TRUE;
	}

	/**
	 * create a filename to save the File
	 */
	function getLocalFilename(){
		if($this->exportType == '0'){
			$fileType = 'excel_export_';
		}else{
			$fileType = 'catxml_export_';
		}
		return $fileType.$this->sysLang.'_'.date('dmy-Hi').'.xml';
	}

	/**
	 * save the information of the export in the database table 'tx_l10nmgr_sava_data'
	 */
	function saveExportInformation(){

		// get current date
		$date = time();

		//To-Do get source language if another than default is selected
		$sourceLanguageId=0;

		// query to insert the data in the database
		$field_values = array(						'source_lang' => $sourceLanguageId,
													'translation_lang' => $this->sysLang,
													'crdate' => $date,
													'tstamp' => $date,
													'l10ncfg_id' => $this->l10ncfgObj->getData('uid'),
													'pid' => $this->l10ncfgObj->getData('pid'),
													'tablelist' => $this->l10ncfgObj->getData('tablelist'),
													'title' => $this->l10ncfgObj->getData('title'),
													'cruser_id' => $this->l10ncfgObj->getData('cruser_id'),
													'filename' => $this->getLocalFilename(),
													'exportType' => $this->exportType);

		$res = $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_l10nmgr_exportdata', $field_values);

		#t3lib_div::debug();
		return $res;
	}

	/**
	 * checks if an export exists
	 *
	 */
	function checkExports(){

		$sysLang = $this->sysLang;

		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('l10ncfg_id,exportType,translation_lang','tx_l10nmgr_exportdata','l10ncfg_id ='.$this->l10ncfgObj->getData('uid').' AND exportType ='.$this->exportType.' AND translation_lang ='.$sysLang);
		$numRows = mysql_num_rows($result);
		if ($numRows > 0){
			return FALSE;
		}else{
			return TRUE;
		}
	}

	/**
	 *  save the exported files in the file /uploads/tx_10lnmgr/saved_files/
	 */
	function saveExportFile($fileContent){
		$fileExportName = PATH_site . 'uploads/tx_l10nmgr/saved_files/'.$this->getLocalFilename();
		file_put_contents($fileExportName,$fileContent);
	}

	/**
	 * Diff-compare markup
	 *
	 * @param	string		Old content
	 * @param	string		New content
	 * @return	string		Marked up string.
	 */
	function diffCMP($old, $new)	{
			// Create diff-result:
		$t3lib_diff_Obj = t3lib_div::makeInstance('t3lib_diff');
		return $t3lib_diff_Obj->makeDiffDisplay($old,$new);
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS['TYPO3_MODE']['XCLASS']['ext/l10nmgr/views/class.tx_l10nmgr_abstractExportView.php'])	{
	include_once($TYPO3_CONF_VARS['TYPO3_MODE']['XCLASS']['ext/l10nmgr/views/class.tx_l10nmgr_abstractExportView.php']);
}
?>