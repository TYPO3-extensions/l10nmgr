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

require_once(t3lib_extMgm::extPath('l10nmgr').'models/tools/class.tx_l10nmgr_xmltools.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/tools/class.tx_l10nmgr_utf8tools.php');

require_once(t3lib_extMgm::extPath('l10nmgr').'views/class.tx_l10nmgr_abstractExportView.php');

/**
 * CATXMLView: Renders the XML for the use for translation agencies
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @author	Daniel Poetzinger <development@aoemedia.de>
 * @author	Daniel Zielinski <d.zielinski@L10Ntech.de>
 * @author	Fabian Seltmann <fs@marketing-factory.de>
 * @author	Andreas Otto <andreas.otto@dkd.de>
 * @package TYPO3
 * @subpackage tx_l10nmgr
 */
class tx_l10nmgr_CATXMLView extends tx_l10nmgr_abstractExportView{

	protected $defaultTemplate = 'EXT:l10nmgr/templates/catxml/catxml.php';
	
	/**
	 * @var	array		$internalMessges		Part of XML with fail logging information content elements
	 */
	protected $internalMessages = array();

	/**
	 * @var	integer		$forcedSourceLanguage		Overwrite the default language uid with the desired language to export
	 */
	protected $forcedSourceLanguage = false;

	protected $exportType = '1';

	/**
	 * @var boolean
	 */
	protected $skipXMLCheck;
	
	/**
	 * @var boolean
	 */
	protected $useUTF8Mode;
	
	protected $xmlTool;
	
	function tx_l10nmgr_CATXMLView($l10ncfgObj, $translateableInformation) {
		parent::__construct($l10ncfgObj, $translateableInformation);
	}
	
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
	 * Render the simple XML export
	 *
	 * @param	array		Translation data for configuration
	 * @return	string		HTML content
	 */
/*	function renderOld() {
		global $LANG,$BE_USER;
		$sysLang=$this->sysLang;
		$accumObj=$this->l10ncfgObj->getL10nAccumulatedInformationsObjectForLanguage($sysLang);
		if ($this->forcedSourceLanguage) {
			$accumObj->setForcedPreviewLanguage($this->forcedSourceLanguage);
		}

		$accum=$accumObj->getInfoArray();
		$errorMessage=array();
		$xmlTool= t3lib_div::makeInstance("tx_l10nmgr_xmltools");
		$output = array();

			// Traverse the structure and generate XML output:
		foreach($accum as $pId => $page) {
			$output[] =  "\t" . '<pageGrp id="'.$pId.'">'."\n";
			foreach($accum[$pId]['items'] as $table => $elements) {
				foreach($elements as $elementUid => $data) {
					if (!empty($data['ISOcode'])) {
						$targetIso=$data['ISOcode'];
					}

					if (is_array($data['fields'])) {
						$fieldsForRecord = array();
						foreach($data['fields'] as $key => $tData) {
							if (is_array($tData)) {
								list(,$uidString,$fieldName) = explode(':',$key);
								list($uidValue) = explode('/',$uidString);

								$noChangeFlag = !strcmp(trim($tData['diffDefaultValue']),trim($tData['defaultValue']));

								if (!$this->modeOnlyChanged || !$noChangeFlag)	{

									// @DP: Why this check?
									if ( ($this->forcedSourceLanguage && isset($tData['previewLanguageValues'][$this->forcedSourceLanguage])) || $this->forcedSourceLanguage === false) {

										if ($this->forcedSourceLanguage) {
											$dataForTranslation = $tData['previewLanguageValues'][$this->forcedSourceLanguage];
										}
										else {
											$dataForTranslation=$tData['defaultValue'];
										}
										$_isTranformedXML=FALSE;
										// Following checks are not enough! Fields that could be transformed to be XML conform are not transformed! textpic fields are not isRTE=1!!! No idea why...
										if ($tData['fieldType']=='text' &&  $tData['isRTE']) {
											$dataForTranslationTranformed=$xmlTool->RTE2XML($dataForTranslation);
											if ($dataForTranslationTranformed!==false) {
												$_isTranformedXML=TRUE;
												$dataForTranslation=$dataForTranslationTranformed;
											}
										}
										if ($_isTranformedXML) {
											$output[]= "\t\t".'<data table="'.$table.'" elementUid="'.$elementUid.'" key="'.$key.'" transformations="1">'.$dataForTranslation.'</data>'."\n";
										}
										else {
											//Substitute & with &amp; in non-RTE fields
											$dataForTranslation=str_replace('&','&amp;',$dataForTranslation);
											//$dataForTranslation = t3lib_div::deHSCentities($dataForTranslation);

											$params = $BE_USER->getModuleData('l10nmgr/cm1/prefs', 'prefs');
											if ($params['utf8'] =='1') {
												$dataForTranslation=tx_l10nmgr_utf8tools::utf8_bad_strip($dataForTranslation);
											}
											if ($xmlTool->isValidXMLString($dataForTranslation)) {
												$output[]= "\t\t".'<data table="'.$table.'" elementUid="'.$elementUid.'" key="'.$key.'">'.$dataForTranslation.'</data>'."\n";
											}
											else {
												if ($params['noxmlcheck'] =='1') {
													$output[]= "\t\t".'<data table="'.$table.'" elementUid="'.$elementUid.'" key="'.$key.'"><![CDATA['.$dataForTranslation.']]></data>'."\n";
												} else {
													$this->setInternalMessage($LANG->getLL('export.process.error.invalid.message'), $elementUid.'/'.$table.'/'.$key);
												}
											}
										}

									} else {
										$this->setInternalMessage($LANG->getLL('export.process.error.empty.message'), $elementUid.'/'.$table.'/'.$key);
									}
								}
							}
						}
					}
				}
			}
			$output[] = "\t" . '</pageGrp>'."\r";
		}

			// get ISO2L code for source language
		if ($this->l10ncfgObj->getData('sourceLangStaticId') && t3lib_extMgm::isLoaded('static_info_tables'))        {
			$sourceIso2L = '';
			$staticLangArr = t3lib_BEfunc::getRecord('static_languages',$this->l10ncfgObj->getData('sourceLangStaticId'),'lg_iso_2');
			$sourceIso2L = ' sourceLang="'.$staticLangArr['lg_iso_2'].'"';
		}

		$XML  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$XML .= '<!DOCTYPE TYPO3L10N [ <!ENTITY nbsp " "> ]>'."\n".'<TYPO3L10N>' . "\n";
		$XML .= "\t"   . '<head>'."\n";
		$XML .= "\t\t" . '<t3_l10ncfg>'.$this->l10ncfgObj->getData('uid').'</t3_l10ncfg>'."\n";
		$XML .= "\t\t" . '<t3_sysLang>'.$sysLang.'</t3_sysLang>'."\n";
		$XML .= "\t\t" . '<t3_sourceLang>'.$staticLangArr['lg_iso_2'].'</t3_sourceLang>'."\n";
		$XML .= "\t\t" . '<t3_targetLang>'.$targetIso.'</t3_targetLang>'."\n";
		$XML .= "\t\t" . '<t3_baseURL>'.t3lib_div::getIndpEnv("TYPO3_SITE_URL").'</t3_baseURL>'."\n";
		$XML .= "\t\t" . '<t3_workspaceId>'.$GLOBALS['BE_USER']->workspace.'</t3_workspaceId>'."\n";
		$XML .= "\t\t" . '<t3_count>'.$accumObj->getFieldCount().'</t3_count>'."\n";
		$XML .= "\t\t" . '<t3_wordCount>'.$accumObj->getWordCount().'</t3_wordCount>'."\n";
		$XML .= "\t\t" . '<t3_internal>' . "\r\t" . implode("\n\t", $this->internalMessges) . "\t\t" . '</t3_internal>' . "\n";
		$XML .= "\t\t" . '<t3_formatVersion>'.L10NMGR_FILEVERSION.'</t3_formatVersion>'."\n";
		$XML .= "\t"   . '</head>'."\n";
		$XML .= implode('', $output) . "\n";
		$XML .= "</TYPO3L10N>";

		$this->saveExportFile($XML);

		//DZ: why return XML here
		return $XML;
	}*/
	
	

	function getFilename() {
		$filename = $this->getLocalFilename();
		return $filename;
	}

	/**
	 * Build single item of "internal" message
	 *
	 * @param	string		$message
	 * @param	string		$key
	 * @access	private
	 * @return	void
	 */
	function setInternalMessage($message, $key) {
		$this->internalMessages[] = "\t\t" . '<t3_skippedItem>' . "\n\t\t\t\t" . '<t3_description>' . $message . '</t3_description>' . "\n\t\t\t\t" . '<t3_key>' . $key . '</t3_key>' . "\n\t\t\t" . '</t3_skippedItem>' . "\r";
	}
	
	
	protected function getInternalMessagesXML(){
		return implode("\n\t",$this->internalMessages);
	}
	
	protected function getPageGroupXML(){
		return $this->pageGroupXML;
	}

	/**
	* Force a new source language to export the content to translate
	*
	* @param	integer		$id
	* @access	public
	* @return	void
	*/
	function setForcedSourceLanguage($id) {
		$this->forcedSourceLanguage = $id;
	}
	
	/**
	 * Internal method to build the pageGroupXML structure.
	 * 
	 * @param void
	 * @return void
	 *
	 */
	protected function buildPageGroupXML(){
		global $LANG;

		foreach($this->getTranslateableInformation()->getPageGroups() as $pageGroup){
			$pageStartTag = sprintf('<pageGrp id="%d">',$pageGroup->getPageId());
			$this->pageGroupXML .= $pageStartTag;
			
			foreach($pageGroup->getTranslateableElements() as $translateableElement){
				 foreach($translateableElement->getTranslateableFields() as $translateableField){
					if (!$this->modeOnlyChanged || $translateableField->isChanged()){

						try{				
							$table 		= $translateableElement->getTable();
							$uid 		= $translateableElement->getUid();
							$key 		= $translateableField->getIdentityKey();
							$data		= $this->getTransformedTranslationDataFromTranslateableField($this->getSkipXMLCheck(), $this->getUseUTF8Mode(),$translateableField,$this->forcedSourceLanguage);
							$needsTrafo = $translateableField->needsTransformation();
							$transformationAttribute = $needsTrafo ? 'transformations="1"' : '';
							
							$dataTag 	= sprintf('<data table="%s" elementUid="%d" key="%s" %s>%s</data>',$table,$uid,$key,$transformationAttribute,$data);
							$this->pageGroupXML .= $dataTag;
							
						}catch(Exception $e){
							$this->setInternalMessage($LANG->getLL('export.process.error.invalid.message'),$uid.'/'.$table.'/'.$key);
						}

					 } 
				 } 
			 } 
			$pageEndTag = '</pageGrp>';
			 $this->pageGroupXML .= $pageEndTag;
		}
	}
	
	/**
	 * Searches the internal XML Tool Singleton
	 *
	 * @return tx_l10nmgr_xmltools
	 */
	protected function findXMLTool(){
		if(!($this->xmlTool instanceof tx_l10nmgr_xmltools)){
			$this->xmlTool= t3lib_div::makeInstance("tx_l10nmgr_xmltools");
		}
		
		return $this->xmlTool;
	}

	protected function getTransformedTranslationDataFromTranslateableField($skipXMLCheck,$useUTF8mode,$translateableField,$forcedSourceLanguage){
		$dataForTranslation = $translateableField->getDataForTranslation($forcedSourceLanguage);
		
		if($translateableField->needsTransformation()){
			$result = $this->findXMLTool()->RTE2XML($dataForTranslation);
		}else{
			$result = str_replace('&','&amp;',$dataForTranslation);
			
			if($useUTF8mode){
				$result = tx_l10nmgr_utf8tools::utf8_bad_strip($result);
			}
			
			if($this->findXMLTool()->isValidXMLString($result)){
				return $result;
			}else{
				if($skipXMLCheck){
					$result = '<![CDATA['.$result.']]>';
				}else{
					throw new Exception("Invalid data in tag");
				}
			}
		}
		
		return $result;
	}
	
	public function preRenderProcessing(){
		$this->buildPageGroupXML();
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/views/CATXML/class.tx_l10nmgr_CATXMLView.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/views/CATXML/class.tx_l10nmgr_CATXMLView.php']);
}


?>
