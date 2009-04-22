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
require_once (t3lib_extMgm::extPath ( "mvc" ) . 'mvc/view/class.tx_mvc_view_phpTemplate.php');


/**
* abstrakt Base class for rendering the export or htmllist of a l10ncfg
**/
abstract class tx_l10nmgr_abstractExportView extends  tx_mvc_view_phpTemplate{

	/**
	 * @var	tx_l10nmgr_models_configuration_configuration		$l10ncfgObj		The language configuration object
	 */
	protected $l10ncfgObj;

	/**
	 * @var tx_l10nmgr_models_translateable_translateableInformation
	 */
	protected $translateableInformation;

	/**
	*	 flags for controlling the fields which should render in the output:
	*/
	protected $modeOnlyChanged=FALSE;
	protected $modeNoHidden=FALSE;
	protected $modeOnlyNew=FALSE;
	
	protected $sysLang;

	
	public function getPageGroup(){
		return $this->pageGroup;
	}
	
	protected function setPageGroup($pageGroup){
		$this->pageGroup = $pageGroup;
	}
	
	public function preRenderProcessing(){
		$this->buildPageGroup();
	}
		
	abstract protected function buildPageGroup();
	
	
	/**
	 * @var	integer		$forcedSourceLanguage		Overwrite the default language uid with the desired language to export
	 */
	protected $forcedSourceLanguage = false;
	
	/**
	 * Method to set the Configuration of this localisation
	 *
	 * @param  tx_l10nmgr_models_configuration_configuration $l10ncfg
	 * @deprecated 
	 */
	public function setL10NConfiguration($l10ncfg){
		$this->l10ncfgObj	= $l10ncfg;
	}
	
	/**
	 * Method to set the Translateable Information
	 * 
	 * @param tx_l10nmgr_models_configuration_translateableInformation $translateableInformation
	 */
	public function setTranslateableInformation($translateableInformation){
		$this->translateableInformation = $translateableInformation;
	}

	/**
	 * Method to set the id of the targetLanguage
	 *
	 * @param int $id
	 */
	public function setTargetLanguageId($id){
		$this->sysLang = $id;
	}
	
	/**
	 * Returns the translateableInformation
	 *
	 * @return tx_l10nmgr_models_translateable_translateableInformation
	 */
	protected function getTranslateableInformation(){
		return $this->translateableInformation;
	}
	
	/**
	 * Returns the id of the targetLanguage
	 *
	 * @return int
	 */
	protected function getTargetLanguageId(){
		if($this->getTranslateableInformation() instanceof tx_l10nmgr_models_translateable_translateableInformation ){
			return $this->translateableInformation->getTargetLanguage()->getUid();
		}elseif(isset($this->sysLang)){
			return $this->sysLang;
		}
	}
	
	/**
	* Force a new source language to export the content to translate
	*
	* @param	integer		$id
	* @access	public
	* @return	void
	*/
	public function setForcedSourceLanguage($id) {
		$this->forcedSourceLanguage = $id;
	}
	
	function getExportType() {
		return $this->exportType;
	}

	public function setModeNoHidden() {
		$this->modeNoHidden=TRUE;
	}
	
	
	public function setModeOnlyChanged() {
		$this->modeOnlyChanged=TRUE;
	}
	
	public function setModeOnlyNew() {
		$this->modeOnlyNew=TRUE;
	}

	/**
	 * create a filename to save the File
	 * @deprecated 
	 */
/*	function getLocalFilename(){
		$sourceLang = '';
		$targetLang = '';

		if($this->exportType == '0'){
			$fileType = 'excel_export';
		}else{
			$fileType = 'catxml_export';
		}

		if ($this->l10ncfgObj->getData('sourceLangStaticId') && t3lib_extMgm::isLoaded('static_info_tables'))        {
			$sourceIso2L = '';
			$staticLangArr = t3lib_BEfunc::getRecord('static_languages',$this->l10ncfgObj->getData('sourceLangStaticId'),'lg_iso_2');
			$sourceIso2L = ' sourceLang="'.$staticLangArr['lg_iso_2'].'"';
		}

		if ($this->getTargetLanguageId() && t3lib_extMgm::isLoaded('static_info_tables'))        {
			$targetLangSysLangArr = t3lib_BEfunc::getRecord('sys_language', $this->getTargetLanguageId());
			$targetLangArr = t3lib_BEfunc::getRecord('static_languages',$targetLangSysLangArr['static_lang_isocode']);
		}

			// Set sourceLang for filename
		if (isset( $staticLangArr['lg_iso_2'] ) && !empty( $staticLangArr['lg_iso_2'] )) {
			$sourceLang = $staticLangArr['lg_iso_2'];
		}

			// Use locale for targetLang in filename if available
		if (isset( $targetLangArr['lg_collate_locale'] ) && !empty( $targetLangArr['lg_collate_locale'] )) {
			$targetLang = $targetLangArr['lg_collate_locale'];
			// Use two letter ISO code if locale is not available
		}else if (isset( $targetLangArr['lg_iso_2'] ) && !empty( $targetLangArr['lg_iso_2'] )) {
			$targetLang = $targetLangArr['lg_iso_2'];
		}

		$fileNamePrefix = (trim( $this->l10ncfgObj->getData('filenameprefix') )) ? $this->l10ncfgObj->getData('filenameprefix') : $fileType ;

		// Setting filename:
		$filename =  $fileNamePrefix . '_' . $sourceLang . '_to_' . $targetLang . '_' . date('dmy-His').'.xml';
		return $filename;
	}*/
	
	abstract protected function getFilenamePrefix();
	
	/**
	 * Returns the filename.
	 *
	 * @param string $enumerationPostfix
	 * @return string
	 */
	public function getFilename($enumerationPostfix = 0){
		$prefix = $this->getFilenamePrefix();
		$targetLanguageId = $this->getTranslateableInformation()->getTargetLanguage()->getUid();
		
		return $prefix.'_'.$targetLanguageId.'_'.date('dmy-Hi').'_'.$enumerationPostfix.'.xml';
	}

	/**
	 * save the information of the export in the database table 'tx_l10nmgr_sava_data'
	 * @deprecated 
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
	 * @deprecated 
	 *
	 */
/*	function checkExports(){
		$ret = FALSE;

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('l10ncfg_id,exportType,translation_lang','tx_l10nmgr_exportdata','l10ncfg_id ='.$this->l10ncfgObj->getData('uid').' AND exportType ='.$this->exportType.' AND translation_lang ='.$this->sysLang);

		if ( !$GLOBALS['TYPO3_DB']->sql_error() ) {
			$numRows = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		}else{
			$numRows = 0;
		}

		if ( $numRows > 0){
			$ret = FALSE;
		}else{
			$ret = TRUE;
		}

		return $ret;
	}*/

	/**
	 * Fetches saved exports based on configuration, export format and target language.
	 *
	 * @deprecated 
	 * @author Andreas Otto <andreas.otto@dkd.de>
	 * @return array Information about exports.
	 */
/*	function fetchExports() {
		$exports = array();

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('crdate,l10ncfg_id,exportType,translation_lang,filename','tx_l10nmgr_exportdata','l10ncfg_id ='.$this->l10ncfgObj->getData('uid').' AND exportType ='.$this->exportType.' AND translation_lang ='.$this->sysLang);

		if ( is_array( $res ) ) {
			$exports = $res;
		}

		return $exports;
	}*/

	/**
	 * Renders a list of saved exports as HTML table.
	 * 
	 * @deprecated 
	 * @return string HTML table
	 */
/*	function renderExports() {
		global $LANG;
		$out = '';
		$content = array();
		$exports = $this->fetchExports();

		foreach( $exports AS $export => $exportData ) {
			$content[$export] = sprintf('
<tr class="bgColor3">
	<td>%s</td>
	<td>%s</td>
	<td>%s</td>
	<td>%s</td>
	<td>%s</td>
</tr>',
				t3lib_BEfunc::datetime($exportData['crdate']),
				$exportData['l10ncfg_id'],
				$exportData['exportType'],
				$exportData['translation_lang'],
				sprintf('<a href="%suploads/tx_l10nmgr/saved_files/%s">%s</a>', t3lib_div::getIndpEnv('TYPO3_SITE_URL'), $exportData['filename'], $exportData['filename'])
			);
		}

		$out = sprintf('
<table>
	<thead>
		<tr class="bgColor5 tableheader">
			<th>%s</th>
			<th>%s</th>
			<th>%s</th>
			<th>%s</th>
			<th>%s</th>
		</tr>
	</thead>
	<tbody>
%s
	</tbody>
</table>',
			$LANG->getLL('export.overview.date.label'),
			$LANG->getLL('export.overview.configuration.label'),
			$LANG->getLL('export.overview.type.label'),
			$LANG->getLL('export.overview.targetlanguage.label'),
			$LANG->getLL('export.overview.filename.label'),
			implode( chr(10), $content )
		);

		return $out;
	}*/

	/**
	 *  save the exported files in the file /uploads/tx_l10nmgr/saved_files/
	 * @deprecated 
	 */
	/*function saveExportFile($fileContent){
		$fileExportName = PATH_site . 'uploads/tx_l10nmgr/saved_files/'.$this->getLocalFilename();
		t3lib_div::writeFile($fileExportName,$fileContent);
	}*/

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


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/views/class.tx_l10nmgr_abstractExportView.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/views/class.tx_l10nmgr_abstractExportView.php']);
}


?>
