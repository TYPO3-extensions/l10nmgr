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
 * baseService class for offering common services like saving translation etc...
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @author	Daniel Pötzinger <development@aoemedia.de>
 * @package TYPO3
 * @subpackage tx_l10nmgr
 */
class tx_l10nmgr_l10nBaseService {
	var $createTranslationAlsoIfEmpty=FALSE;
	
	function saveTranslation($l10ncfgObj,$translationObj) {
		$sysLang=$translationObj->getLanguage();
		$accumObj=$l10ncfgObj->getL10nAccumulatedInformationsObjectForLanguage($sysLang);
		$flexFormDiffArray=$this->_submitContentAndGetFlexFormDiff($accumObj->getInfoArray($sysLang),$translationObj->getTranslationData());

		if ($flexFormDiffArray !== false) {
			$l10ncfgObj->updateFlexFormDiff($sysLang,$flexFormDiffArray);
		}
	}
	
		
	
	/**
	 * Submit incoming content to database. Must match what is available in $accum.
	 *
	 * @param	array		Translation configuration
	 * @param	array		Array with incoming translation. Must match what is found in $accum
	 * @return	mixed		False if error - else flexFormDiffArray (if $inputArray was an array and processing was performed.)
	 */
	function _submitContentAndGetFlexFormDiff($accum,$inputArray)	{

		if (is_array($inputArray))	{

				// Initialize:
			$flexToolObj = t3lib_div::makeInstance('t3lib_flexformtools');
			$TCEmain_data = array();
			$TCEmain_cmd = array();
			
			$_flexFormDiffArray = array();
				// Traverse:
			foreach($accum as $pId => $page)	{
				foreach($accum[$pId]['items'] as $table => $elements)	{
					foreach($elements as $elementUid => $data)	{
						if (is_array($data['fields']))	{
								
							foreach($data['fields'] as $key => $tData)	{	
														
								if ( is_array($tData) && isset($inputArray[$table][$elementUid][$key])) {									
									
									list($Ttable,$TuidString,$Tfield,$Tpath) = explode(':',$key);
									list($Tuid,$Tlang,$TdefRecord) = explode('/',$TuidString);
									
									if (!$this->createTranslationAlsoIfEmpty  && $inputArray[$table][$elementUid][$key] =='' && $Tuid=='NEW')	{
										//if data is empty do not save it
										unset($inputArray[$table][$elementUid][$key]);
										continue;
									}

										// If new element is required, we prepare for localization
									if ($Tuid==='NEW')	{
										$TCEmain_cmd[$table][$elementUid]['localize'] = $Tlang;
									}

										// If FlexForm, we set value in special way:
									if ($Tpath)	{
										if (!is_array($TCEmain_data[$Ttable][$TuidString][$Tfield]))	{
											$TCEmain_data[$Ttable][$TuidString][$Tfield] = array();
										}
										//TCEMAINDATA is passed as refernece here:
										$flexToolObj->setArrayValueByPath($Tpath,$TCEmain_data[$Ttable][$TuidString][$Tfield],$inputArray[$table][$elementUid][$key]);
										$_flexFormDiffArray[$key] = array('translated' => $inputArray[$table][$elementUid][$key], 'default' => $tData['defaultValue']);
									} else {
										$TCEmain_data[$Ttable][$TuidString][$Tfield] = $inputArray[$table][$elementUid][$key];
									}
									unset($inputArray[$table][$elementUid][$key]);	// Unsetting so in the end we can see if $inputArray was fully processed.
								}
								else {
									//debug($tData,'fields not set for: '.$elementUid.'-'.$key);							
									//debug($inputArray[$table],'inputarray');
								}	
							}
							if (is_array($inputArray[$table][$elementUid]) && !count($inputArray[$table][$elementUid]))	{
								unset($inputArray[$table][$elementUid]);	// Unsetting so in the end we can see if $inputArray was fully processed.
							}
						}
					}
					if (is_array($inputArray[$table]) && !count($inputArray[$table]))	{
						unset($inputArray[$table]);	// Unsetting so in the end we can see if $inputArray was fully processed.
					}
				}
			}
//debug($TCEmain_cmd,'$TCEmain_cmd');
//debug($TCEmain_data,'$TCEmain_data');

				// Execute CMD array: Localizing records:
			$tce = t3lib_div::makeInstance('t3lib_TCEmain');
			$tce->stripslashes_values = FALSE;
			if (count($TCEmain_cmd))	{
				$tce->start(array(),$TCEmain_cmd);
				$tce->process_cmdmap();
				if (count($tce->errorLog))	{
					debug($tce->errorLog,'TCEmain localization errors:');
				}
			}
//debug($tce->copyMappingArray_merged,'$tce->copyMappingArray_merged');
				// Remapping those elements which are new:
			foreach($TCEmain_data as $table => $items)	{
				foreach($TCEmain_data[$table] as $TuidString => $fields)	{
					list($Tuid,$Tlang,$TdefRecord) = explode('/',$TuidString);
					if ($Tuid === 'NEW')	{
						if ($tce->copyMappingArray_merged[$table][$TdefRecord])	{
							$TCEmain_data[$table][t3lib_BEfunc::wsMapId($table,$tce->copyMappingArray_merged[$table][$TdefRecord])] = $fields;
						} else {
							debug('Record "'.$table.':'.$TdefRecord.'" was NOT localized as it should have been!');
						}
						unset($TCEmain_data[$table][$TuidString]);
					}
				}
			}
//debug($TCEmain_data,'$TCEmain_data');

				// Now, submitting translation data:
			$tce = t3lib_div::makeInstance('t3lib_TCEmain');
			$tce->stripslashes_values = FALSE;
			$tce->dontProcessTransformations = TRUE; 
			//print_r($TCEmain_data);
			$tce->start($TCEmain_data,array());	// check has been done previously that there is a backend user which is Admin and also in live workspace
			$tce->process_datamap();
			
			if (count($tce->errorLog))	{
				debug($tce->errorLog,'TCEmain update errors:');
			}
			
			if (count($tce->autoVersionIdMap) && count($_flexFormDiffArray))	{
			#	debug($this->flexFormDiffArray);
				foreach($_flexFormDiffArray as $key => $value)	{
					list($Ttable,$Tuid,$Trest) = explode(':',$key,3);
					if ($tce->autoVersionIdMap[$Ttable][$Tuid])	{
						$_flexFormDiffArray[$Ttable.':'.$tce->autoVersionIdMap[$Ttable][$Tuid].':'.$Trest] = $_flexFormDiffArray[$key];
						unset($_flexFormDiffArray[$key]);
					}
				}
#				debug($tce->autoVersionIdMap);
#				debug($_flexFormDiffArray);
			}
			
			
				// Should be empty now - or there were more information in the incoming array than there should be!
			if (count($inputArray))	{
				debug($inputArray,'These fields were ignored since they were not in the configuration:');
			}

			return $_flexFormDiffArray;
		}
		return false;
	}
	
	
	
}




if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/views/tx_l10nmgr_l10nmgrconfiguration_detail.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/views/tx_l10nmgr_l10nmgrconfiguration_detail.php']);
}


?>
