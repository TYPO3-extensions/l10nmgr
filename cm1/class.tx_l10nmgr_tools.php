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
 * Contains translation tools
 *
 * $Id: class.t3lib_loaddbgroup.php 1816 2006-11-26 00:43:24Z mundaun $
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   61: class tx_l10nmgr_tools
 *   85:     function tx_l10nmgr_tools()
 *   96:     function getRecordsToTranslateFromTable($table,$pageId)
 *  123:     function translationDetails($table,$row,$sysLang)
 *  231:     function translationDetails_flexFormCallBack($dsArr, $dataValue, $PA, $structurePath, &$pObj)
 *  266:     function translationDetails_addField($key, $TCEformsCfg, $dataValue, $translationValue, $diffDefaultValue='', $previewLanguageValues=array())
 *
 * TOTAL FUNCTIONS: 5
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */







/**
 * Contains translation tools
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage tx_l10nmgr
 */
class tx_l10nmgr_tools {

		// External:
	var $filters = array(
			'fieldTypes' => 'text,input',
			'noEmptyValues' => TRUE,
			'noIntegers' => TRUE,
			'l10n_categories' => ''	// could be "text,media" for instance.
		);

	var $previewLanguages = array();	// Array of sys_language_uids, eg. array(1,2) 
	var $verbose = TRUE;		// If TRUE, when fields are not included there will be shown a detailed explanation.

		// Internal:
	var $t8Tools = NULL;				// Object to t3lib_transl8tools, set in constructor
	var $detailsOutput = array();		// Output for translation details
	var $sysLanguages = array();		// System languages initialized
	var $flexFormDiff = array();		// FlexForm diff data

	/**
	 * Constructor
	 * Setting up internal variable ->t8Tools
	 *
	 * @return	void
	 */
	function tx_l10nmgr_tools()	{
		$this->t8Tools = t3lib_div::makeInstance('t3lib_transl8tools');
	}

	/**
	 * Selecting records from a table from a page which are candidates to be translated.
	 *
	 * @param	string		Table name
	 * @param	integer		Page id
	 * @return	array		Array of records from table (with all fields selected)
	 */
	function getRecordsToTranslateFromTable($table,$pageId)	{
		global $TCA;

		if ($this->t8Tools->isTranslationInOwnTable($table))	{

				// First, select all records that are default language OR international:
			$allRows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
				'*',
				$table,
				'pid='.intval($pageId).
					' AND '.$TCA[$table]['ctrl']['languageField'].'<=0'.
					($GLOBALS['TCA'][$table]['ctrl']['versioningWS'] ? ' AND '.$table.'.t3ver_state!=1' : '').
					t3lib_BEfunc::deleteClause($table).
					t3lib_BEfunc::versioningPlaceholderClause($table)
			);

			return $allRows;
		}
	}

	/**
	 * Generate details about translation
	 *
	 * @param	string		Table name
	 * @param	array		Row (one from getRecordsToTranslateFromTable())
	 * @param	integer		sys_language uid
	 * @param	array		FlexForm diff data
	 * @return	array		Returns details array
	 */
	function translationDetails($table,$row,$sysLang,$flexFormDiff)	{
		global $TCA;

			// Initialize:
		$tInfo = $this->t8Tools->translationInfo($table,$row['uid'],$sysLang);
		$this->detailsOutput = array();
		$this->flexFormDiff = $flexFormDiff;

		if (is_array($tInfo))	{

				// Initialize some more:
			$this->detailsOutput['translationInfo'] = $tInfo;
			t3lib_div::loadTCA($table);
			$this->sysLanguages = $this->t8Tools->getSystemLanguages();
			$this->detailsOutput['ISOcode'] = $this->sysLanguages[$sysLang]['ISOcode'];

				// ALL language; then look for flexform:
			$flexFormTranslation = $tInfo['sys_language_uid']==-1 && !count($tInfo['translations']);
			if ($flexFormTranslation || $table === 'pages')	{
				$this->detailsOutput['log'][] = 'Mode: "ALL" language with no translation set; looking for flexform fields';

				foreach($TCA[$table]['columns'] as $field => $cfg)	{
					$conf = $cfg['config'];

						// For "flex" fieldtypes we need to traverse the structure looking for file and db references of course!
					if ($conf['type']=='flex')	{
						// We might like to add the filter that detects if record is tt_content/CType is "tx_flex...:" since otherwise we would translate flexform content that might be hidden if say the record had a DS set but was later changed back to "Text w/Image" or so... But probably this is a rare case.

							// Get current data structure to see if translation is needed:
						$dataStructArray = t3lib_BEfunc::getFlexFormDS($conf, $row, $table);

						$this->detailsOutput['log'][] = 'FlexForm field "'.$field.'": DataStructure status: '.(is_array($dataStructArray) ? 'OK' : 'Error: '.$dataStructArray);

						if (is_array($dataStructArray) && !$dataStructArray['meta']['langDisable'])	{
							$this->detailsOutput['log'][] = 'FlexForm Localization enabled, type: '.($dataStructArray['meta']['langChildren'] ? 'Inheritance: Continue' : 'Separate: Stop');

							if ($dataStructArray['meta']['langChildren'])	{
								$currentValueArray = t3lib_div::xml2array($row[$field]);
									// Traversing the XML structure, processing files:
								if (is_array($currentValueArray))	{

										// Create and call iterator object:
									$flexObj = t3lib_div::makeInstance('t3lib_flexformtools');
									$flexObj->traverseFlexFormXMLData($table,$field,$row,$this,'translationDetails_flexFormCallBack');
								}
							}
						} else {
							$this->detailsOutput['log'][] = 'FlexForm Localization disabled. Nothing to do.';
						}
					}
				}
			}
			
			if (!$flexFormTranslation)	{
				if (count($tInfo['translations']))	{
					$this->detailsOutput['log'][] = 'Mode: translate existing record';
					$translationUID = $tInfo['translations'][$sysLang]['uid'];
					$translationRecord = t3lib_BEfunc::getRecordWSOL($tInfo['translation_table'], $tInfo['translations'][$sysLang]['uid']);
				} else {
						// Will also suggest to translate a default language record which are in a container block with Inheritance or Separate mode. This might not be something people wish, but there is no way we can prevent it because its a deprecated localization paradigm to use container blocks with localization. The way out might be setting the langauge to "All" for such elements.
					$this->detailsOutput['log'][] = 'Mode: translate to new record';
					$translationUID = 'NEW/'.$sysLang.'/'.$row['uid'];
					$translationRecord = array();
				}

				if ($TCA[$tInfo['translation_table']]['ctrl']['transOrigDiffSourceField'])	{
					$diffArray = unserialize($translationRecord[$TCA[$tInfo['translation_table']]['ctrl']['transOrigDiffSourceField']]);
#					debug($diffArray);
				} else {
					$diffArray = array();
				}

				$prevLangRec = array();
				foreach($this->previewLanguages as $prevSysUid)	{
					$prevLangInfo = $this->t8Tools->translationInfo($table,$row['uid'],$prevSysUid);
					if ($prevLangInfo['translations'][$prevSysUid])	{
						$prevLangRec[$prevSysUid] = t3lib_BEfunc::getRecordWSOL($prevLangInfo['translation_table'],$prevLangInfo['translations'][$prevSysUid]['uid']);
					}
				}

				foreach($TCA[$tInfo['translation_table']]['columns'] as $field => $cfg)	{
					if ($TCA[$tInfo['translation_table']]['ctrl']['languageField']!==$field
						&& $TCA[$tInfo['translation_table']]['ctrl']['transOrigPointerField']!==$field
						&& $TCA[$tInfo['translation_table']]['ctrl']['transOrigDiffSourceField']!==$field)	{

							$diffDefaultValue = $diffArray[$field];

							$previewLanguageValues = array();
							foreach($this->previewLanguages as $prevSysUid)	{
								$previewLanguageValues[$prevSysUid] = $prevLangRec[$prevSysUid][$field];
							}

							$this->translationDetails_addField($tInfo['translation_table'].':'.t3lib_BEfunc::wsMapId($tInfo['translation_table'],$translationUID).':'.$field, $cfg, $row[$field], $translationRecord[$field], $diffDefaultValue, $previewLanguageValues);
					}
				}
			}
		} else {
			$this->detailsOutput['log'][] = 'ERROR: '.$tInfo;
		}

		return $this->detailsOutput;
	}

	/**
	 * FlexForm call back function, see translationDetails
	 *
	 * @param	array		Data Structure
	 * @param	string		Data value
	 * @param	array		Various stuff in an array
	 * @param	string		path to location in flexform
	 * @param	object		Reference to parent object
	 * @return	void
	 */
	function translationDetails_flexFormCallBack($dsArr, $dataValue, $PA, $structurePath, &$pObj)	{

			// Only take lead from default values (since this is "Inheritance" localization we parse for)
		if (substr($structurePath,-5)=='/vDEF')	{

				// So, find translated value:
			$baseStructPath = substr($structurePath,0,-3);
			$structurePath = $baseStructPath.$this->detailsOutput['ISOcode'];
			$translValue = $pObj->getArrayValueByPath($structurePath, $pObj->traverseFlexFormXMLData_Data);

				// Generate preview values:
			$previewLanguageValues = array();
			foreach($this->previewLanguages as $prevSysUid)	{
				$previewLanguageValues[$prevSysUid] = $pObj->getArrayValueByPath($baseStructPath.$this->sysLanguages[$prevSysUid]['ISOcode'], $pObj->traverseFlexFormXMLData_Data);
			}
			
			$key = $ffKey = $PA['table'].':'.t3lib_BEfunc::wsMapId($PA['table'],$PA['uid']).':'.$PA['field'].':'.$structurePath;
			$ffKeyOrig = $PA['table'].':'.$PA['uid'].':'.$PA['field'].':'.$structurePath;

				// Now, in case this record has just been created in the workspace the diff-information is still found bound to the UID of the original record. So we will look for that until it has been created for the workspace record:
			if (!is_array($this->flexFormDiff[$ffKey]) && is_array($this->flexFormDiff[$ffKeyOrig]))	{
				$ffKey = $ffKeyOrig;
			#	debug('orig...');
			}
				// Set diff-value
			if (is_array($this->flexFormDiff[$ffKey]) && trim($this->flexFormDiff[$ffKey]['translated'])===trim($translValue))	{
				$diffDefaultValue = $this->flexFormDiff[$ffKey]['default'];
			} else {
				$diffDefaultValue = '';
			}

				// Add field:
			$this->translationDetails_addField($key, $dsArr['TCEforms'], $dataValue, $translValue, $diffDefaultValue, $previewLanguageValues);
		}
	}

	/**
	 * Add field to detailsOutput array. First, a lot of checks are done...
	 *
	 * @param	string		Key is a combination of table, uid, field and structure path, identifying the field
	 * @param	array		TCA configuration for field
	 * @param	string		Default value (current)
	 * @param	string		Translated value (current)
	 * @param	string		Default value of time of current translated value (used for diff'ing with $dataValue)
	 * @param	array		Array of preview language values identified by keys (which are sys_language uids)
	 * @return	void
	 */
	function translationDetails_addField($key, $TCEformsCfg, $dataValue, $translationValue, $diffDefaultValue='', $previewLanguageValues=array())	{
		$msg = '';

		list(,,$kFieldName) = explode(':',$key);

		if ($TCEformsCfg['config']['type']!=='flex')	{
			if ($TCEformsCfg['l10n_mode']!='exclude')	{
				if ($TCEformsCfg['l10n_mode']=='mergeIfNotBlank')	{
					$msg.='This field is optional. If not filled in, the default language value will be used.';
				}
				if (!t3lib_div::isFirstPartOfStr($TCEformsCfg['displayCond'],'HIDE_L10N_SIBLINGS'))	{
					if (!t3lib_div::isFirstPartOfStr($kFieldName,'t3ver_'))	{
						if (!$this->filters['l10n_categories'] || t3lib_div::inList($this->filters['l10n_categories'],$TCEformsCfg['l10n_cat']))	{
							if (!$this->filters['fieldTypes'] || t3lib_div::inList($this->filters['fieldTypes'],$TCEformsCfg['config']['type']))	{
								if (!$this->filters['noEmptyValues'] || !(!$dataValue && !$translationValue))	{	// Checking that no translation value exists either; if a translation value is found it is considered that it should be translated even if the default value is empty for some reason.
									if (!$this->filters['noIntegers'] || !t3lib_div::testInt($dataValue))	{
										$this->detailsOutput['fields'][$key] = array(
												'defaultValue' => $dataValue,
												'translationValue' => $translationValue,
												'diffDefaultValue' => $TCEformsCfg['l10n_display']!='hideDiff' ? $diffDefaultValue : '',
												'previewLanguageValues' => $previewLanguageValues,
												'msg' => $msg,
												'readOnly' => $TCEformsCfg['l10n_display']=='defaultAsReadonly',
												'fieldType' => $TCEformsCfg['config']['type']
											);
									} elseif ($this->verbose) $this->detailsOutput['fields'][$key] = 'Bypassing; ->filters[noIntegers] was set and dataValue "'.$dataValue.'" was an integer';
								} elseif ($this->verbose) $this->detailsOutput['fields'][$key] = 'Bypassing; ->filters[noEmptyValues] was set and dataValue "'.$dataValue.'" was empty and no translation found either.';
							} elseif ($this->verbose) $this->detailsOutput['fields'][$key] = 'Bypassing; fields of type "'.$TCEformsCfg['config']['type'].'" was filtered out in ->filters[fieldTypes]';
						} elseif ($this->verbose) $this->detailsOutput['fields'][$key] = 'Bypassing; ->filters[l10n_categories] was set to "'.$this->filters['l10n_categories'].'" and l10n_cat for field ("'.$TCEformsCfg['l10n_cat'].'") did not match.';
					} elseif ($this->verbose) $this->detailsOutput['fields'][$key] = 'Bypassing; Fieldname "'.$kFieldName.'" was prefixed "t3ver_"';
				} elseif ($this->verbose) $this->detailsOutput['fields'][$key] = 'Bypassing; displayCondition HIDE_L10N_SIBLINGS was set.';
			} elseif ($this->verbose) $this->detailsOutput['fields'][$key] = 'Bypassing; "l10n_mode" for the field was "exclude" and field is not translated then.';
		} elseif ($this->verbose) $this->detailsOutput['fields'][$key] = 'Bypassing; fields of type "flex" can only be translated in the context of an "ALL" language record';
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/cm1/class.tx_l10nmgr_tools.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/cm1/class.tx_l10nmgr_tools.php']);
}
?>