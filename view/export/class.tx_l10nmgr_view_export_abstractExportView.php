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
 * Abstract Base class for rendering the export or htmllist of a l10ncfg
 */
abstract class tx_l10nmgr_view_export_abstractExportView extends tx_mvc_view_backendModule {

	/**
	 * @var	tx_l10nmgr_domain_configuration_configuration		$l10ncfgObj		The language configuration object
	 */
	protected $l10ncfgObj;

	/**
	 * @var tx_l10nmgr_domain_translateable_translateableInformation
	 */
	protected $translateableInformation;

	/**
	*	 flags for controlling the fields which should render in the output:
	*/
	protected $modeOnlyChanged=FALSE;

	protected $modeNoHidden=FALSE;

	protected $modeOnlyNew=FALSE;

	protected $sysLang;

	protected $pageGroups;

	/**
	 * @var tx_l10nmgr_service_textConverter
	 */
	protected $xmlTool;

	/**
	 * @var boolean
	 */
	protected $skipXMLCheck;

	/**
	 * @var boolean
	 */
	protected $useUTF8Mode;

	/**
	 * @return boolean
	 */
	protected function getSkipXMLCheck() {
		return $this->skipXMLCheck;
	}

	/**
	 * @return boolean
	 */
	protected function getUseUTF8Mode() {
		return $this->useUTF8Mode;
	}

	/**
	 * @param boolean $skipXMLCheck
	 */
	public function setSkipXMLCheck($skipXMLCheck) {
		$this->skipXMLCheck = $skipXMLCheck;
	}

	/**
	 * @param boolean $useUTF8Mode
	 */
	public function setUseUTF8Mode($useUTF8Mode) {
		$this->useUTF8Mode = $useUTF8Mode;
	}

	/**
	 * This method is used to get the content of the rendered pagegroups to display it in the exportView.
	 * The implementation of the renderPageGroups method sould write the result using setRenderedPageGroups
	 *
	 * @param void
	 * @return string rendered pagegroups as steing
	 */
	public function getRenderedPageGroups(){
		return $this->pageGroups;
	}

	/**
	 * This method should be used internally from the implementation of the abstract renderPageGroups mehtod
	 * to set the content for the rendered pagegroups.
	 *
	 * @param string the page group rendered in the export format.
	 */
	protected function setRenderedPageGroups($pageGroups){
		$this->pageGroups = $pageGroups;
	}

	/**
	 * Befor an implemetation of this abtract view is rendered will call renderPageGroups
	 * to render the pageGroups for the current export format. This is needed to register error for the export header
	 * during the export-
	 */
	public function preRenderProcessing(){
		$this->renderPageGroups();
	}

	/**
	 * The implementation of this method should render the pageGroups in the export format
	 * of this view and set it with setRenderedPageGroups(). In the template the method getRenderedPageGroups can be
	 * used to read the rendering result for the pagegroups and include it in the export
	 */
	abstract protected function renderPageGroups();


	/**
	 * @var	integer		$forcedSourceLanguage		Overwrite the default language uid with the desired language to export
	 */
	protected $forcedSourceLanguage = false;

	/**
	 * Method to set the Configuration of this localisation
	 *
	 * @param  tx_l10nmgr_domain_configuration_configuration $l10ncfg
	 * @deprecated
	 */
	public function setL10NConfiguration($l10ncfg){
		$this->l10ncfgObj	= $l10ncfg;
	}

	/**
	 * Method to set the Translateable Information
	 *
	 * @param tx_l10nmgr_domain_translateable_translateableInformation $translateableInformation
	 */
	public function setTranslateableInformation(tx_l10nmgr_domain_translateable_translateableInformation $translateableInformation){
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
	 * @return tx_l10nmgr_domain_translateable_translateableInformation
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
		$targetLanguage = 0;

		if ($this->getTranslateableInformation() instanceof tx_l10nmgr_domain_translateable_translateableInformation ) {
			$targetLanguage = $this->translateableInformation->getTargetLanguage()->getUid();
		} elseif (isset($this->sysLang)) {
			$targetLanguage = $this->sysLang;
		}

		return $targetLanguage;
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

	/**
	 * This mehtod is used to configure the view to show only noHidden elements
	 *
	 * @param boolean
	 */
	public function setModeNoHidden($modeNoHidden = true) {
		$this->modeNoHidden = $modeNoHidden;
	}

	/**
	 * This method is used to configure the view to display only changed elements.
	 *
	 * @param boolean
	 */
	public function setModeOnlyChanged($setModeOnlyChanged = true) {
		$this->modeOnlyChanged = $setModeOnlyChanged;
	}

	/**
	 * This method is used to configure that only new elements should be displayed
	 *
	 * @param boolean
	 */
	public function setModeOnlyNew($setModeOnlyNew = true) {
		$this->modeOnlyNew = $setModeOnlyNew;
	}


	abstract protected function getExporttypePrefix();

	/**
	 * Returns the filename.
	 *
	 * @param string prefix of the export filename. This method should be used to set the prefix
	 * from the l10n configuration
	 * @param string $enumerationPostfix
	 * @return string
	 */
	public function getFilename($configurationPrefix='',$postfix = 0){

		$exporttypePrefix = $this->getExporttypePrefix();
		$targetLanguageId = $this->getTargetLanguageId();

		if ($postfix != '') {
			$filename = $exporttypePrefix. '_' . $targetLanguageId . '_' . date('dmy-Hi') . '_' . $postfix . '.xml';
		} else {
			$filename = $exporttypePrefix. '_' .  $targetLanguageId . '_' . date('dmy-Hi') . '.xml';
		}

		//if theres a prefix, prepend it to the file
		if($configurationPrefix != ''){ $filename = $configurationPrefix.'_'.$filename; }

		return $filename;
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


	/**
	 * Searches the internal XML Tool Singleton
	 *
	 * @return tx_l10nmgr_service_textConverter
	 */
	protected function TextConverter() {
		if (! ($this->xmlTool instanceof tx_l10nmgr_service_textConverter) ) {
			$this->xmlTool= t3lib_div::makeInstance('tx_l10nmgr_service_textConverter');
		}

		return $this->xmlTool;
	}

	/**
	 * This method is used to transform the data of the export for the correct presentation.
	 *
	 * @var boolean
	 * @var boolean
	 * @todo this should be the new way
	 * @throws tx_mvc_exception_invalidContent
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return string
	 */
	protected function getTransformedTranslationDataFromTranslateableField($skipXMLCheck, $useUTF8mode, $translateableField, $forcedSourceLanguage) {
		$dataForTranslation = $translateableField->getDataForTranslation($forcedSourceLanguage);
		$result = '';

		try {
			if ($translateableField->needsTransformation()) {
				$result = $this->TextConverter()->toXML($dataForTranslation);
			} else {
				$result = $this->TextConverter()->toRaw($dataForTranslation, (bool)$useUTF8mode, true, false);
			}
		} catch (tx_mvc_exception_converter $e) {

			try {
				$result = $this->TextConverter()->toRaw($dataForTranslation, (bool)$useUTF8mode, true, true);

			} catch(tx_mvc_exception_converter $e) {
				if ($skipXMLCheck) {
					$result = '<![CDATA[' . $this->TextConverter()->toRaw($dataForTranslation, (bool)$useUTF8mode, false) . ']]>';
				} else {
					throw new tx_mvc_exception_invalidContent('Content not exported: No valid XML and option skipXMLCheck not active.');
				}
			}
		}

		return $result;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/views/class.tx_l10nmgr_view_export_abstractExportView.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/views/class.tx_l10nmgr_view_export_abstractExportView.php']);
}

?>