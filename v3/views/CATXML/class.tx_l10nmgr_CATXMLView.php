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

/**
 * excelXML: Renders the XML
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage tx_l10nmgr
 */
class tx_l10nmgr_CATXMLView {

	
	var $l10ncfgObj;	// 
	var $sysLang;
	

	
	function tx_l10nmgr_CATXMLView($l10ncfgObj, $sysLang) {
		global $BACK_PATH;
		$this->sysLang=$sysLang;
		$this->l10ncfgObj=$l10ncfgObj;
		
		$this->doc = t3lib_div::makeInstance('noDoc');
		$this->doc->backPath = $BACK_PATH;
		
	}


	/**
	 * Render the simple XML export
	 *
	 * @param	array		Translation data for configuration
	 * @return	string		HTML content
	 */
	function render()	{
		$sysLang=$this->sysLang;
		$accumObj=$this->l10ncfgObj->getL10nAccumulatedInformationsObjectForLanguage($sysLang);
		$accum=$accumObj->getInfoArray();	
	
		
		$parseHTML = t3lib_div::makeInstance("t3lib_parseHTML_proc");
		$output = array();

			// Traverse the structure and generate XML output:
		foreach($accum as $pId => $page)	{
			foreach($accum[$pId]['items'] as $table => $elements)	{
				foreach($elements as $elementUid => $data)	{
					if (!empty($data['ISOcode']))	{
						$targetIso2L = ' targetLang="'.$data['ISOcode'].'"';
					}
					
					if (is_array($data['fields']))	{
						$fieldsForRecord = array();
						foreach($data['fields'] as $key => $tData)	{
							if (is_array($tData))	{
								list(,$uidString,$fieldName) = explode(':',$key); 
								list($uidValue) = explode('/',$uidString);


								if (!$this->MOD_SETTINGS["onlyChangedContent"] || !$noChangeFlag)	{
									reset($tData['previewLanguageValues']);
									$dataForTranslation=$tData['defaultValue'];
									// Substitutions for XML conformity here
									$_isTranformedXML=FALSE;
									if ($tData['fieldType']=='text' &&  $tData['isRTE']) { // to be substituted with check if field is RTE-enabled ($fieldName == "bodytext")
										$dataForTranslationTranformed = $parseHTML->TS_images_rte($dataForTranslation);
										$dataForTranslationTranformed = $parseHTML->TS_links_rte($dataForTranslationTranformed);
										$dataForTranslationTranformed = $parseHTML->TS_transform_rte($dataForTranslationTranformed,$css=1); // which mode is best?
										//substitute & with &amp;
										$dataForTranslationTranformed=str_replace('&','&amp;',$dataForTranslationTranformed);
										if (tx_l10nmgr_xmltools::isValidXML($dataForTranslationTranformed)) {
											$_isTranformedXML=TRUE;
											$dataForTranslation=$dataForTranslationTranformed;
										}										
									}
									if ($_isTranformedXML) {
										$output[]= "\t\t".'<Data table="'.$table.'" elementUid="'.$elementUid.'" key="'.$key.'" transformations="1">'.$dataForTranslation.'</Data>'."\n";	
									}
									else {
										$output[]= "\t\t".'<Data table="'.$table.'" elementUid="'.$elementUid.'" key="'.$key.'"><![CDATA['.$dataForTranslation.']]></Data>'."\n";
									}
									
								}
							}
						}						
					}
				}
			}

		}
		
		// get ISO2L code for source language
			if ($this->l10ncfgObj->getData('sourceLangStaticId') && t3lib_extMgm::isLoaded('static_info_tables'))        {
					$sourceIso2L = '';
					$staticLangArr = t3lib_BEfunc::getRecord('static_languages',$this->l10ncfgObj->getData('sourceLangStaticId'),'lg_iso_2');
	   			$sourceIso2L = ' sourceLang="'.$staticLangArr['lg_iso_2'].'"';
   		}

		
		$XML = '<?xml version="1.0" encoding="UTF-8"?>'."\n<TYPO3LOC sysLang=\"".$sysLang."\"".$sourceIso2L.$targetIso2L.">\n###INSERT_ROWS###\n<count>###INSERT_ROW_COUNT###</count></TYPO3LOC>"; //Here we need source language iso-2-letter code for CAT tools. sysLang should be named sysTargetLang.

		$XML = str_replace('###INSERT_ROWS###',implode('', $output), $XML);
		$XML = str_replace('###INSERT_ROW_COUNT###',count($output), $XML);

			
		return $XML;
		exit;
	}
	
	function getFilename() {
		
		if ($this->l10ncfgObj->getData('sourceLangStaticId') && t3lib_extMgm::isLoaded('static_info_tables'))        {
					$sourceIso2L = '';
					$staticLangArr = t3lib_BEfunc::getRecord('static_languages',$this->l10ncfgObj->getData('sourceLangStaticId'),'lg_iso_2');
	   			$sourceIso2L = ' sourceLang="'.$staticLangArr['lg_iso_2'].'"';
   		}
		// Setting filename:
		$filename = 'xml_export_'.$staticLangArr['lg_iso_2'].'_'.date('dmy-Hi').'.xml';
		return $filename;
	}
	
}




if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/views/tx_l10nmgr_l10nmgrconfiguration_detail.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/views/tx_l10nmgr_l10nmgrconfiguration_detail.php']);
}


?>
