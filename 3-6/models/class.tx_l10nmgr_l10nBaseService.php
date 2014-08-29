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
 * @author     Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @author     Daniel Pötzinger <development@aoemedia.de>
 * @package    TYPO3
 * @subpackage tx_l10nmgr
 */
class tx_l10nmgr_l10nBaseService {

	/**
	 * @var bool Translate even if empty.
	 */
	protected $createTranslationAlsoIfEmpty = FALSE;

	/**
	 * @var bool Import as default language.
	 */
	protected $importAsDefaultLanguage = FALSE;

	protected static $targetLanguageID = NULL;

	/**
	 * @return integer|NULL
	 */
	public static function getTargetLanguageID() {
		return self::$targetLanguageID;
	}

	/**
	 * Setter for $importAsDefaultLanguage
	 *
	 * @param boolean $importAsDefaultLanguage
	 * @return void
	 */
	public function setImportAsDefaultLanguage($importAsDefaultLanguage) {
		$this->importAsDefaultLanguage = $importAsDefaultLanguage;
	}

	/**
	 * Getter for $importAsDefaultLanguage
	 *
	 * @return boolean
	 */
	public function getImportAsDefaultLanguage() {
		return $this->importAsDefaultLanguage;
	}

	/**
	 * @var array Extension's configuration as from the EM
	 */
	protected $extensionConfiguration = array();

	public function __construct() {
		// Load the extension's configuration
		$this->extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['l10nmgr']);
	}

	/**
	 * Save the translation
	 *
	 * @param tx_l10nmgr_l10nConfiguration $l10ncfgObj
	 * @param tx_l10nmgr_translationData $translationObj
	 * @return void
	 */
	function saveTranslation(tx_l10nmgr_l10nConfiguration $l10ncfgObj, tx_l10nmgr_translationData $translationObj) {
		// Provide a hook for specific manipulations before saving
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['l10nmgr']['savePreProcess'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['l10nmgr']['savePreProcess'] as $classReference) {
				$processingObject = t3lib_div::getUserObj($classReference);
				$processingObject->processBeforeSaving($l10ncfgObj, $translationObj, $this);
			}
		}

		$sysLang = $translationObj->getLanguage();
		$accumObj = $l10ncfgObj->getL10nAccumulatedInformationsObjectForLanguage($sysLang);
		$flexFormDiffArray = $this->_submitContentAndGetFlexFormDiff($accumObj->getInfoArray($sysLang), $translationObj->getTranslationData());

		if ($flexFormDiffArray !== FALSE) {
			$l10ncfgObj->updateFlexFormDiff($sysLang, $flexFormDiffArray);
		}

		// Provide a hook for specific manipulations after saving
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['l10nmgr']['savePostProcess'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['l10nmgr']['savePostProcess'] as $classReference) {
				$processingObject = t3lib_div::getUserObj($classReference);
				$processingObject->processAfterSaving($l10ncfgObj, $translationObj, $flexFormDiffArray, $this);
			}
		}
	}

	/**
	 * Submit incoming content to database. Must match what is available in $accum.
	 *
	 * @param array $accum Translation configuration
	 * @param array $inputArray Array with incoming translation. Must match what is found in $accum
	 * @return mixed False if error - else flexFormDiffArray (if $inputArray was an array and processing was performed.)
	 */
	function _submitContentAndGetFlexFormDiff($accum, $inputArray) {
		if ($this->getImportAsDefaultLanguage()) {
			return $this->_submitContentAsDefaultLanguageAndGetFlexFormDiff($accum, $inputArray);
		} else {
			return $this->_submitContentAsTranslatedLanguageAndGetFlexFormDiff($accum, $inputArray);
		}
	}

	/**
	 * Submit incoming content as translated language to database. Must match what is available in $accum.
	 *
	 * @param array $accum Translation configuration
	 * @param array $inputArray Array with incoming translation. Must match what is found in $accum
	 * @return mixed False if error - else flexFormDiffArray (if $inputArray was an array and processing was performed.)
	 */
	function _submitContentAsTranslatedLanguageAndGetFlexFormDiff($accum, $inputArray) {
		if (is_array($inputArray)) {
			// Initialize:
			/** @var $flexToolObj t3lib_flexformtools */
			$flexToolObj = t3lib_div::makeInstance('t3lib_flexformtools');
			$TCEmain_data = array();
			$TCEmain_cmd = array();

			$_flexFormDiffArray = array();
			// Traverse:
			foreach ($accum as $pId => $page) {
				foreach ($accum[$pId]['items'] as $table => $elements) {
					foreach ($elements as $elementUid => $data) {
						if (is_array($data['fields'])) {

							foreach ($data['fields'] as $key => $tData) {

								if (is_array($tData) && isset($inputArray[$table][$elementUid][$key])) {

									list($Ttable, $TuidString, $Tfield, $Tpath) = explode(':', $key);
									list($Tuid, $Tlang, $TdefRecord) = explode('/', $TuidString);

									if (!$this->createTranslationAlsoIfEmpty && $inputArray[$table][$elementUid][$key] == '' && $Tuid == 'NEW') {
										//if data is empty do not save it
										unset($inputArray[$table][$elementUid][$key]);
										continue;
									}

									// If new element is required, we prepare for localization
									if ($Tuid === 'NEW') {
										//print "\nNEW\n";
										$TCEmain_cmd[$table][$elementUid]['localize'] = $Tlang;
									}

									// If FlexForm, we set value in special way:
									if ($Tpath) {
										if (!is_array($TCEmain_data[$Ttable][$TuidString][$Tfield])) {
											$TCEmain_data[$Ttable][$TuidString][$Tfield] = array();
										}
										//TCEMAINDATA is passed as reference here:
										$flexToolObj->setArrayValueByPath($Tpath, $TCEmain_data[$Ttable][$TuidString][$Tfield], $inputArray[$table][$elementUid][$key]);
										$_flexFormDiffArray[$key] = array('translated' => $inputArray[$table][$elementUid][$key], 'default' => $tData['defaultValue']);
									} else {
										$TCEmain_data[$Ttable][$TuidString][$Tfield] = $inputArray[$table][$elementUid][$key];
									}
									unset($inputArray[$table][$elementUid][$key]); // Unsetting so in the end we can see if $inputArray was fully processed.
								} else {
									//debug($tData,'fields not set for: '.$elementUid.'-'.$key);
									//debug($inputArray[$table],'inputarray');
								}
							}
							if (is_array($inputArray[$table][$elementUid]) && !count($inputArray[$table][$elementUid])) {
								unset($inputArray[$table][$elementUid]); // Unsetting so in the end we can see if $inputArray was fully processed.
							}
						}
					}
					if (is_array($inputArray[$table]) && !count($inputArray[$table])) {
						unset($inputArray[$table]); // Unsetting so in the end we can see if $inputArray was fully processed.
					}
				}
			}
//debug($TCEmain_cmd,'$TCEmain_cmd');
//debug($TCEmain_data,'$TCEmain_data');

			self::$targetLanguageID = $Tlang;

			// Execute CMD array: Localizing records:
			/** @var $tce t3lib_TCEmain */
			$tce = t3lib_div::makeInstance('t3lib_TCEmain');
			if ($this->extensionConfiguration['enable_neverHideAtCopy'] == 1) {
				$tce->neverHideAtCopy = TRUE;
			}
			$tce->stripslashes_values = FALSE;
			if (count($TCEmain_cmd)) {
				$tce->start(array(), $TCEmain_cmd);
				$tce->process_cmdmap();
				if (count($tce->errorLog)) {
					debug($tce->errorLog, 'TCEmain localization errors:');
				}
			}

			// Before remapping
			if (TYPO3_DLOG) {
				t3lib_div::sysLog(__FILE__ . ': ' . __LINE__ . ': TCEmain_data before remapping: ' . t3lib_div::arrayToLogString($TCEmain_data), 'l10nmgr');
			}
			// Remapping those elements which are new:
			$this->lastTCEMAINCommandsCount = 0;
			foreach ($TCEmain_data as $table => $items) {
				foreach ($TCEmain_data[$table] as $TuidString => $fields) {
					list($Tuid, $Tlang, $TdefRecord) = explode('/', $TuidString);
					$this->lastTCEMAINCommandsCount++;
					if ($Tuid === 'NEW') {
						if ($tce->copyMappingArray_merged[$table][$TdefRecord]) {
							$TCEmain_data[$table][t3lib_BEfunc::wsMapId($table, $tce->copyMappingArray_merged[$table][$TdefRecord])] = $fields;
						} else {
							t3lib_div::sysLog(__FILE__ . ': ' . __LINE__ . ': Record "' . $table . ':' . $TdefRecord . '" was NOT localized as it should have been!', 'l10nmgr');
						}
						unset($TCEmain_data[$table][$TuidString]);
					}
				}
			}
			// After remapping
			if (TYPO3_DLOG) {
				t3lib_div::sysLog(__FILE__ . ': ' . __LINE__ . ': TCEmain_data after remapping: ' . t3lib_div::arrayToLogString($TCEmain_data), 'l10nmgr');
			}

			// Now, submitting translation data:
			/** @var $tce t3lib_TCEmain */
			$tce = t3lib_div::makeInstance('t3lib_TCEmain');
			if ($this->extensionConfiguration['enable_neverHideAtCopy'] == 1) {
				$tce->neverHideAtCopy = TRUE;
			}
			$tce->stripslashes_values = FALSE;
			$tce->dontProcessTransformations = TRUE;
			//print_r($TCEmain_data);
			$tce->start($TCEmain_data, array()); // check has been done previously that there is a backend user which is Admin and also in live workspace
			$tce->process_datamap();

			self::$targetLanguageID = NULL;

			if (count($tce->errorLog)) {
				t3lib_div::sysLog(__FILE__ . ': ' . __LINE__ . ': TCEmain update errors: ' . t3lib_div::arrayToLogString($tce->errorLog), 'l10nmgr');
			}

			if (count($tce->autoVersionIdMap) && count($_flexFormDiffArray)) {
				if (TYPO3_DLOG) {
					t3lib_div::sysLog(__FILE__ . ': ' . __LINE__ . ': flexFormDiffArry: ' . t3lib_div::arrayToLogString($this->flexFormDiffArray), 'l10nmgr');
				}
				foreach ($_flexFormDiffArray as $key => $value) {
					list($Ttable, $Tuid, $Trest) = explode(':', $key, 3);
					if ($tce->autoVersionIdMap[$Ttable][$Tuid]) {
						$_flexFormDiffArray[$Ttable . ':' . $tce->autoVersionIdMap[$Ttable][$Tuid] . ':' . $Trest] = $_flexFormDiffArray[$key];
						unset($_flexFormDiffArray[$key]);
					}
				}
				if (TYPO3_DLOG) {
					t3lib_div::sysLog(__FILE__ . ': ' . __LINE__ . ': autoVersionIdMap: ' . $tce->autoVersionIdMap, 'l10nmgr');
					t3lib_div::sysLog(__FILE__ . ': ' . __LINE__ . ': _flexFormDiffArray: ' . t3lib_div::arrayToLogString($_flexFormDiffArray), 'l10nmgr');
				}
			}

			// Should be empty now - or there were more information in the incoming array than there should be!
			if (count($inputArray)) {
				debug($inputArray, 'These fields were ignored since they were not in the configuration:');
			}

			return $_flexFormDiffArray;
		} else {
			return FALSE;
		}
	}

	/**
	 * Submit incoming content as default language to database. Must match what is available in $accum.
	 *
	 * @param array $accum Translation configuration
	 * @param array $inputArray Array with incoming translation. Must match what is found in $accum
	 * @return mixed False if error - else flexFormDiffArray (if $inputArray was an array and processing was performed.)
	 */
	function _submitContentAsDefaultLanguageAndGetFlexFormDiff($accum, $inputArray) {
		if (is_array($inputArray)) {
			// Initialize:
			/** @var $flexToolObj t3lib_flexformtools */
			$flexToolObj = t3lib_div::makeInstance('t3lib_flexformtools');
			$TCEmain_data = array();

			$_flexFormDiffArray = array();
			// Traverse:
			foreach ($accum as $pId => $page) {
				foreach ($accum[$pId]['items'] as $table => $elements) {
					foreach ($elements as $elementUid => $data) {
						if (is_array($data['fields'])) {

							foreach ($data['fields'] as $key => $tData) {

								if (is_array($tData) && isset($inputArray[$table][$elementUid][$key])) {

									list($Ttable, $TuidString, $Tfield, $Tpath) = explode(':', $key);
									list($Tuid, $Tlang, $TdefRecord) = explode('/', $TuidString);

									if (!$this->createTranslationAlsoIfEmpty && $inputArray[$table][$elementUid][$key] == '' && $Tuid == 'NEW') {
										//if data is empty do not save it
										unset($inputArray[$table][$elementUid][$key]);
										continue;
									}
									#t3lib_div::debug($elementUid);

									// If FlexForm, we set value in special way:
									if ($Tpath) {
										if (!is_array($TCEmain_data[$Ttable][$elementUid][$Tfield])) {
											$TCEmain_data[$Ttable][$elementUid][$Tfield] = array();
										}
										//TCEMAINDATA is passed as reference here:
										$flexToolObj->setArrayValueByPath($Tpath, $TCEmain_data[$Ttable][$elementUid][$Tfield], $inputArray[$table][$elementUid][$key]);
										$_flexFormDiffArray[$key] = array('translated' => $inputArray[$table][$elementUid][$key], 'default' => $tData['defaultValue']);
									} else {
										$TCEmain_data[$Ttable][$elementUid][$Tfield] = $inputArray[$table][$elementUid][$key];
									}
									unset($inputArray[$table][$elementUid][$key]); // Unsetting so in the end we can see if $inputArray was fully processed.
								} else {
									//debug($tData,'fields not set for: '.$elementUid.'-'.$key);
									//debug($inputArray[$table],'inputarray');
								}
							}
							if (is_array($inputArray[$table][$elementUid]) && !count($inputArray[$table][$elementUid])) {
								unset($inputArray[$table][$elementUid]); // Unsetting so in the end we can see if $inputArray was fully processed.
							}
						}
					}
					if (is_array($inputArray[$table]) && !count($inputArray[$table])) {
						unset($inputArray[$table]); // Unsetting so in the end we can see if $inputArray was fully processed.
					}
				}
			}

			if ($TCEmain_data['pages_language_overlay']) {
				$TCEmain_data['pages'] = $TCEmain_data['pages_language_overlay'];
				unset($TCEmain_data['pages_language_overlay']);
			}
			#t3lib_div::debug($TCEmain_data);
			//var_dump($TCEmain_data);

			$this->lastTCEMAINCommandsCount = 0;

			// Now, submitting translation data:
			$tce = t3lib_div::makeInstance('t3lib_TCEmain');
			$tce->stripslashes_values = FALSE;
			$tce->dontProcessTransformations = TRUE;
			$tce->start($TCEmain_data, array()); // check has been done previously that there is a backend user which is Admin and also in live workspace
			$tce->process_datamap();

			if (count($tce->errorLog)) {
				t3lib_div::sysLog(__FILE__ . ': ' . __LINE__ . ': TCEmain update errors: ' . t3lib_div::arrayToLogString($tce->errorLog), 'l10nmgr');
			}

			if (count($tce->autoVersionIdMap) && count($_flexFormDiffArray)) {

				foreach ($_flexFormDiffArray as $key => $value) {
					list($Ttable, $Tuid, $Trest) = explode(':', $key, 3);
					if ($tce->autoVersionIdMap[$Ttable][$Tuid]) {
						$_flexFormDiffArray[$Ttable . ':' . $tce->autoVersionIdMap[$Ttable][$Tuid] . ':' . $Trest] = $_flexFormDiffArray[$key];
						unset($_flexFormDiffArray[$key]);
					}
				}
			}

			// Should be empty now - or there were more information in the incoming array than there should be!
			if (count($inputArray)) {
				debug($inputArray, 'These fields were ignored since they were not in the configuration:');
			}

			return $_flexFormDiffArray;
		} else {
			return FALSE;
		}
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/models/class.tx_l10nmgr_l10nBaseService.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/models/class.tx_l10nmgr_l10nBaseService.php']);
}


?>
