<?php
/***************************************************************
 *  Copyright notice
 *
 *  Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
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

require_once t3lib_extMgm::extPath('l10nmgr') . '/service/class.tx_l10nmgr_service_detectRecord.php';

/**
 * Translation base
 *
 * class.tx_l10nmgr_service_importTranslation.php
 *
 * @author Michael Klapper <klapper@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id$
 * @date $Date$
 * @since 28.04.2009 - 14:43:36
 * @package TYPO3
 * @subpackage ex_l10nmgr
 * @access public
 */
class tx_l10nmgr_service_importTranslation {

	/**
	 * Enable the debug output
	 *
	 * @var boolean
	 */
	const SHOW_DEBUG_INFORMATION = false;

	/**
	 * Define that an empty translated element (without any content) should be translated.
	 *
	 * @var boolean
	 */
	const FORCE_CREATE_TRANSLATION = false;

	/**
	 * Command array information
	 * to prepare the translation import
	 *
	 * @var array
	 */
	protected $TCEmain_cmd = array();

	/**
	 * Sturct which contains the translaiton data
	 *
	 * @var array
	 */
	protected $TCEmain_data = array();

	/**
	 * Enter description here...
	 *
	 * @var t3lib_flexformtools
	 */
	protected $flexToolObj = null;

	/**
	 * Initialize the service
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function __construct() {
		$this->flexToolObj = t3lib_div::makeInstance('t3lib_flexformtools');
	}

	/**
	 * Save the incoming translationData object into the database
	 * if the available translatableObject are match the configuration.
	 *
	 * @param tx_l10nmgr_domain_translateable_translateableInformation $TranslatableInformation
	 * @param tx_l10nmgr_domain_translation_data $TranslationData
	 *
	 * @access public
	 * @return void
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function save(tx_l10nmgr_domain_translateable_translateableInformation $TranslatableInformation, tx_l10nmgr_domain_translation_data $TranslationData) {
		$TranslatablePageGroupCollection = $TranslatableInformation->getPageGroups();

		foreach ( $TranslatablePageGroupCollection as $Page) {
			$TranslatableElementsCollection = $Page->getTranslateableElements();

			foreach ($TranslatableElementsCollection as $Element) {

				$TranslatableFieldsCollection = $Element->getTranslateableFields();
				$DetectRecordService          = t3lib_div::makeInstance('tx_l10nmgr_service_detectRecord'); /* @var $DetectRecordService tx_l10nmgr_service_detectRecord */
				$DetectRecordService->flushRecordCache();
				$DetectRecordService->setWorkspaceId($TranslatableInformation->getWorkspaceId());

				foreach ($TranslatableFieldsCollection as $Field) { /* @var $Field tx_l10nmgr_domain_translateable_translateableField */
					try {
						$TranslationField = $TranslationData->findByTableUidAndKey($Page->getUid(), $Element->getTableName(), $Element->getUid(), $Field->getIdentityKey());

						try {
							$oldKey = $Field->getIdentityKey();
							$newKey = $DetectRecordService->verifyIdentityKey (
								$oldKey,
								$TranslationData->getSysLanguageUid(),
								$Element->getUid(),
								$TranslationData->getWorkspaceId()
							);
							$Field->setIdentityKey ($newKey);

							if(($oldKey != $newKey) && !$TranslationData->isTargetLanguageForced()){
								$TranslationField->addChange('Generated new key without forced target language: '.$oldKey.' new: '.$newKey);
							}

						} catch (tx_mvc_exception_skipped $e) {
							$TranslationField->markSkipped($e->getMessage());
						}

						$this->buildDataCommandArray($Element, $Field, $TranslationField);
					}catch (tx_mvc_exception_argumentOutOfRange $e ) {
						tx_mvc_common_debug::debug($e->getMessage(), 'Exception out of range - Thrown because a element from the database is not in the import avaliable.', self::SHOW_DEBUG_INFORMATION);
					} catch (tx_mvc_exception_skipped $e) {
						tx_mvc_common_debug::logException($e);
					} catch (tx_mvc_exception $e) {
						tx_mvc_common_debug::logException($e);
					}
				}
			}
		}

		$this->blackBoxDoNotModifyIt();
		$this->processDataMapCommands();

		if ( $TranslationData->isImported()) {
			$TranslationData->writeProcessingLog();
		}
	}

	/**
	 * Process the t3lib_TCEmain commands
	 *
	 * Remap new translated elements to their l18n_parent records
	 *
	 * @todo Find name for it
	 * @todo Get rid od that magic and make it  right
	 *
	 * @access protected
	 * @return void
	 */
	protected function blackBoxDoNotModifyIt() {
		$TCEmain = t3lib_div::makeInstance('t3lib_TCEmain'); /* @var $TCEmain t3lib_TCEmain */
		$TCEmain->stripslashes_values = false;
		$errorMessages = '';

		if (count($this->TCEmain_cmd))	{
			$TCEmain->start(array(), $this->TCEmain_cmd);
			$TCEmain->process_cmdmap();

				//!TODO add the errorLog to the import record for better handling
			tx_mvc_common_debug::debug($TCEmain->errorLog, 'TCEmain localization errors:', (bool)count($TCEmain->errorLog));
		}

		tx_mvc_common_debug::debug($TCEmain->copyMappingArray_merged, '$TCEmain->copyMappingArray_merged', self::SHOW_DEBUG_INFORMATION);
		tx_mvc_common_debug::debug($this->TCEmain_data, '$TCEmain_data', self::SHOW_DEBUG_INFORMATION);
		tx_mvc_common_debug::debug($this->TCEmain_cmd, '$this->TCEmain_cmd', self::SHOW_DEBUG_INFORMATION);

			// Remap new translated elements to their l18n_parent records
		foreach (array_keys($this->TCEmain_data) as $tableName)	{

			foreach ($this->TCEmain_data[$tableName] as $cmdProcessString => $fields) {

				list($cmdForceCreateNew, , $cmdl18nParentRecordUid) = explode('/', $cmdProcessString);

				if ($cmdForceCreateNew === 'NEW') {
					tx_mvc_common_debug::debug($this->TCEmain_data, '$this->TCEmain_data', self::SHOW_DEBUG_INFORMATION);

					if ($TCEmain->copyMappingArray_merged[$tableName][$cmdl18nParentRecordUid])	{

						$this->TCEmain_data[$tableName][t3lib_BEfunc::wsMapId($tableName, $TCEmain->copyMappingArray_merged[$tableName][$cmdl18nParentRecordUid])] = $fields;
					} else {

							//!FIXME add logging to the error handling
						$errorMessages .= "\n" . 'Record "'.$tableName.':'.$cmdl18nParentRecordUid.'" was NOT localized as it should have been!';
					}

					tx_mvc_common_debug::debug($this->TCEmain_data, '$this->TCEmain_data', self::SHOW_DEBUG_INFORMATION);
					unset($this->TCEmain_data[$tableName][$cmdProcessString]);
				}
			}

			if (count($errorMessages) > 1) {
				trigger_error('HERE NOT LOCALIZED!!!' . "\n" . $errorMessages,E_USER_WARNING);
			}
		}
	}

	/**
	 * Process the datamap command array to aply
	 * the new translation to the database.
	 *
	 * @access protected
	 * @return void
	 */
	protected function processDataMapCommands() {

			// Now, submitting translation data:
		$TCEmain = t3lib_div::makeInstance('t3lib_TCEmain'); /* @var $TCEMain t3lib_TCEmain */
		$TCEmain->stripslashes_values        = false;
		$TCEmain->dontProcessTransformations = true;

		$TCEmain->start($this->TCEmain_data, array());	// check has been done previously that there is a backend user which is Admin and also in live workspace
		$TCEmain->process_datamap();

			//!TODO add the errorLog to the import record for better handling
		if ((bool)count($TCEmain->errorLog)) {
			trigger_error('TCEmain update errors:' . "\n\n" . implode("\n", $TCEmain->errorLog),E_USER_WARNING);
		}
	}

	/**
	 * Build the TCE_main command array to process the final translation import later
	 *
	 * @param tx_l10nmgr_domain_translateable_translateableElement $Element
	 * @param tx_l10nmgr_domain_translateable_translateableField $Field
	 * @param tx_l10nmgr_domain_translation_field $TranslationField
	 *
	 * @access protected
	 * @return void
	 */
	protected function buildDataCommandArray($Element, $Field, $TranslationField) {
		if (
				! self::FORCE_CREATE_TRANSLATION
			&&
				! tx_mvc_validator_factory::getNotEmptyStringValidator()->isValid($TranslationField->getContent())
			 ) {
			$TranslationField->markSkipped('Empty filed content: Skipped while "tx_l10nmgr_service_importTranslation::FORCE_CREATE_TRANSLATION" is set to false.');
		}

			// If new element is required, we prepare for localization
		if ( $Field->getCmdForceCreateNew() ) {
			$this->TCEmain_cmd[$Element->getTableName()][$Element->getUid()]['localize'] = $Field->getCmdTargetSysLanguageUid();
		}

		$cmdTableName         = $Field->getCmdTableName();
		$cmdProcessingString  = $Field->getCmdProcessString();
		$cmdFieldName         = $Field->getCmdFieldName();
		$cmdFieldFlexformPath = $Field->getCmdFieldFlexformPath();

			// If FlexForm, we set value in special way:
		if ( tx_mvc_validator_factory::getNotEmptyStringValidator()->isValid($Field->getCmdFieldFlexformPath()) ) {

			if (! is_array($this->TCEmain_data[$cmdTableName][$cmdProcessingString][$cmdFieldName]) ) {
				$this->TCEmain_data[$cmdTableName][$cmdProcessingString][$cmdFieldName] = array();
			}

			/**
			 * @internal $this->TCEmain_data is passed as refernece here:
			 */
			$this->flexToolObj->setArrayValueByPath (
				$cmdFieldFlexformPath,
				$this->TCEmain_data[$cmdTableName][$cmdProcessingString][$cmdFieldName],
				$TranslationField->getContent()
			);

			//!TODO move this diff
			//flexFormDiffArray is the value before the translation ($tData['defaultValue']) and the translated Value ($inputArray[$table][$elementUid][$key])
			//$_flexFormDiffArray[$key] = array('translated' => $inputArray[$table][$elementUid][$key], 'default' => $tData['defaultValue']);
		} else {
			$this->TCEmain_data[$cmdTableName][$cmdProcessingString][$cmdFieldName] = $TranslationField->getContent();
		}

			// Mark field as imported so we can verify later the processed progress.
		$TranslationField->markImported();
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/service/class.tx_l10nmgr_service_importTranslation.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/service/class.tx_l10nmgr_service_importTranslation.php']);
}

?>