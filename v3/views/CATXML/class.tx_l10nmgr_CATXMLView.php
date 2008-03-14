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

/**
 * CATXMLView: Renders the XML for the use for translation agencies
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @author	Daniel Pötzinger <development@aoemedia.de>
 * @author	Daniel Zielinski <d.zielinski@L10Ntech.de>
 * @package TYPO3
 * @subpackage tx_l10nmgr
 */
class tx_l10nmgr_CATXMLView {


	var $l10ncfgObj;
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
	function render() {
		$sysLang=$this->sysLang;
		$accumObj=$this->l10ncfgObj->getL10nAccumulatedInformationsObjectForLanguage($sysLang);
		$accum=$accumObj->getInfoArray();

		$errorMessage=array();	
		$xmlTool= t3lib_div::makeInstance("tx_l10nmgr_xmltools");
		$output = array();

			// Traverse the structure and generate XML output:
		foreach($accum as $pId => $page) {
			$output[]='<PageGrp id="'.$pId.'">'."\n";
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
									reset($tData['previewLanguageValues']);
									$dataForTranslation=$tData['defaultValue'];
									// Substitutions for XML conformity here
									$_isTranformedXML=FALSE;
									if ($tData['fieldType']=='text' &&  $tData['isRTE']) { 
										$dataForTranslationTranformed=$xmlTool->RTE2XML($dataForTranslation);										
										if ($dataForTranslationTranformed!==false) {
											$_isTranformedXML=TRUE;
											$dataForTranslation=$dataForTranslationTranformed;
										}
									}
									if ($_isTranformedXML) {
										$output[]= "\t\t".'<Data table="'.$table.'" elementUid="'.$elementUid.'" key="'.$key.'" transformations="1">'.$dataForTranslation.'</Data>'."\n";
									}
									else {
										$dataForTranslation=tx_l10nmgr_utf8tools::utf8_bad_strip($dataForTranslation);
										if ($xmlTool->isValidXMLString($dataForTranslation)) {
											$output[]= "\t\t".'<Data table="'.$table.'" elementUid="'.$elementUid.'" key="'.$key.'"><![CDATA['.$dataForTranslation.']]></Data>'."\n";
										}
										else {
											$errorMessage[]="\t\t".'<InternalMessage><![CDATA['.$elementUid.'/'.$table.'/'.$key.' is no valid XML (invalid characters or invalid chars)]]></InternalMessage>';												
										}
									}
								}
							}
						}
					}
				}
			}
			$output[]='</PageGrp>'."\n";
		}

			// get ISO2L code for source language
		if ($this->l10ncfgObj->getData('sourceLangStaticId') && t3lib_extMgm::isLoaded('static_info_tables'))        {
			$sourceIso2L = '';
			$staticLangArr = t3lib_BEfunc::getRecord('static_languages',$this->l10ncfgObj->getData('sourceLangStaticId'),'lg_iso_2');
			$sourceIso2L = ' sourceLang="'.$staticLangArr['lg_iso_2'].'"';
		}

		$XML = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$XML .= '<!DOCTYPE TYPO3LOC [ <!ENTITY nbsp " "> ]>'."\n".'<TYPO3LOC l10ncfg="' . $this->l10ncfgObj->getData('uid') . '" sysLang="' . $sysLang . '"' . $sourceIso2L . $targetIso2L . ' baseURL="'.t3lib_div::getIndpEnv("TYPO3_SITE_URL").'">' . "\n";
		$XML .= '<head>'."\n";
		$XML .=	"\t".'<l10ncfg>'.$this->l10ncfgObj->getData('uid').'</l10ncfg>'."\n";
		$XML .=	"\t".'<sysLang>'.$sysLang.'</sysLang>'."\n";
		$XML .=	"\t".'<sourceLang>'.$staticLangArr['lg_iso_2'].'</sourceLang>'."\n";
		$XML .=	"\t".'<targetLang>'.$targetIso.'</targetLang>'."\n";
		$XML .=	"\t".'<baseURL>'.t3lib_div::getIndpEnv("TYPO3_SITE_URL").'</baseURL>'."\n";
		$XML .=	"\t".'<workspaceId>'.$GLOBALS['BE_USER']->workspace.'</workspaceId>'."\n";				
		$XML .=	"\t".'<count>'.$accumObj->getFieldCount().'</count>'."\n";
		$XML .=	"\t".'<wordCount>'.$accumObj->getWordCount().'</wordCount>'."\n";
		$XML .=	"\t".'<Internal>'.implode("\n\t", $errorMessage).'</Internal>'."\n";
		$XML .= '</head>'."\n";
		$XML .= implode('', $output) . "\n";
		$XML .= "</TYPO3LOC>"; 

		return $XML;
	}


	function getFilename() {

		if ($this->l10ncfgObj->getData('sourceLangStaticId') && t3lib_extMgm::isLoaded('static_info_tables'))        {
			$sourceIso2L = '';
			$staticLangArr = t3lib_BEfunc::getRecord('static_languages',$this->l10ncfgObj->getData('sourceLangStaticId'),'lg_iso_2');
			$sourceIso2L = ' sourceLang="'.$staticLangArr['lg_iso_2'].'"';
		}

		$fileNamePrefix = (trim( $this->l10ncfgObj->getData('filenameprefix') )) ? $this->l10ncfgObj->getData('filenameprefix') : 'export_language' ;

		// Setting filename:
		$filename =  $fileNamePrefix . '_' . $staticLangArr['lg_iso_2'] . '_' . date('dmy-Hi').'.xml';
		return $filename;
	}


	function setModeOnlyChanged() {
		$this->modeOnlyChanged=TRUE;
	}

}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS['TYPO3_MODE']['XCLASS']['ext/l10nmgr/views/CATXML/class.tx_l10nmgr_CATXMLView.php'])	{
	include_once($TYPO3_CONF_VARS['TYPO3_MODE']['XCLASS']['ext/l10nmgr/views/CATXML/class.tx_l10nmgr_CATXMLView.php']);
}
?>
