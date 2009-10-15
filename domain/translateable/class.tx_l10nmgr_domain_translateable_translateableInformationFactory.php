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
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * The factory is used to create a valid translateableInformation Object
 *  *
 * tx_l10nmgr_domain_translateable_translateableInformationFactory
 *
 * @author	 Timo Schmidt <schmidt@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.tx_l10nmgr_domain_translateable_translateableInformationFactory.php $
 * @date 02.04.2009 - 14:41:28
 * @package	TYPO3
 * @subpackage	l10nmgr
 * @access public
 */
class tx_l10nmgr_domain_translateable_translateableInformationFactory {

	/**
	 * Builds an translateableInformation from a given exportData object and a collection of page ids which should be exported.
	 *
	 * @param tx_l10nmgr_domain_exporter_exportData $exportData
	 * @param ArrayObject $pageIdCollection collection of page ids
	 * @param integer $workspaceId DEFAULT is null
	 *
	 * @access public
	 * @return tx_l10nmgr_domain_translateable_translateableInformation
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function createFromExportDataAndPageIdCollection(tx_l10nmgr_domain_exporter_exportData $exportData, ArrayObject $pageIdCollection, $workspaceId = NULL){
		$typo3DataProvider = new tx_l10nmgr_domain_translateable_typo3TranslateableFactoryDataProvider($exportData);
		$typo3DataProvider->addPageIdCollectionToRelevantPageIds($pageIdCollection);

		if(!is_null($workspaceId)){
			$typo3DataProvider->setWorkspaceId($workspaceId);
		}
		$tranlateableInformation 	= $this->createFromDataProvider($typo3DataProvider);

		return $tranlateableInformation;
	}

	/**
	 * The include list of the tx_l10nmgr_cfg database record.
	 *
	 * @param tx_l10nmgr_domain_exporter_exportData $exportData
	 * @param array $includeArray Comma seperated list of tt_content:1,*
	 * @param integer $workspaceId DEFAULT is null
	 *
	 * @access public
	 * @return tx_l10nmgr_domain_translateable_translateableInformation
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function createFromIncludeList(tx_l10nmgr_domain_exporter_exportData $exportData, array $includeArray, $workspaceId = NULL){
		$typo3DataProvider = new tx_l10nmgr_domain_translateable_typo3TranslateableFactoryDataProvider($exportData);
		$count = 0;
		foreach ($includeArray as $recordKey => $recordContent) {
			$count++;
			list($tableName, $recordUid) = explode(':', $recordKey);
			$typo3DataProvider->appendRecordsToProcess($this->getPageId($tableName, $recordUid), $tableName, $recordUid);
		}

		if (!is_null($workspaceId)) {
			$typo3DataProvider->setWorkspaceId($workspaceId);
		}

		$tranlateableInformation 	= $this->createFromDataProvider($typo3DataProvider);

		return $tranlateableInformation;
	}

	/**
	 * Return pid of the given record information.
	 *
	 * @param string $tableName
	 * @param integer $recordUid
	 *
	 * @access public
	 * @return integer
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	protected function getPageId($tableName, $recordUid) {
		$pid = 0;
		//TODO check if WS-overlay is required
		$recordArray = t3lib_BEfunc::getRecord($tableName, $recordUid);

		if (is_array($recordArray)) {
			$pid = $recordArray['pid'];
		}

		return $pid;
	}

	/**
	 * This method is used, to create a translateableInformation object structure from a
	 * configuration, a set of pageIds and a target and a previewLanguage.
	 * Internally it iterates the pageIdCollection and fetches the information for a translation using the
	 * tx_l10nmgr_tools class.
	 *
	 * @param tx_l10nmgr_domain_configuration_configuration $l10ncfg
	 * @param ArrayObject $pageIdCollection A set of pageIds. This set of pageIds is used, to create a translateable Information of the contentelments in these pages.
	 * @param tx_l10nmgr_domain_language_language $targetLanguage
	 * @param tx_l10nmgr_domain_language_language $previewLanguage
	 * @return tx_l10nmgr_domain_translateable_translateableInformation
	 * @todo we need to handle the include index
	 */
	protected function createFromDataProvider(tx_l10nmgr_interface_translateable_translateableFactoryDataProvider $dataProvider){
//!TODO change parameter $dataprovider handling...
		$this->dataProvider	= $dataProvider;

		$translateableInformation = new tx_l10nmgr_domain_translateable_translateableInformation();
		$translateableInformation->setSourceLanguage($this->dataProvider->getSourceLanguage());
		$translateableInformation->setTargetLanguage($this->dataProvider->getTargetLanguage());
		$translateableInformation->setExportData($this->dataProvider->getExportData());
		$translateableInformation->setSiteUrl($this->dataProvider->getSiteUrl());
		$translateableInformation->setWorkspaceId($this->dataProvider->getWorkspaceId());

		$pageIdCollection		= $this->dataProvider->getRelevantPageIds();
		$tables_to_process		= $this->dataProvider->getRelevantTables();

		// we only need to process tables which are in the TCA AND the configuration

		// iterate pageId collection
		foreach($pageIdCollection as $pageId){
			$pageRow = t3lib_BEfunc::getRecordWSOL('pages',$pageId);

			if(is_array($pageRow)){
				//create pageGroup and add metadata
				$pageGroup 	= new tx_l10nmgr_domain_translateable_pageGroup();
				$pageGroup->setPageRow($pageRow);

				// traverse tca tables:
				foreach($tables_to_process as $table)	{
					//the table pages its self is not needed because the informations of the page
					//are attached to the page group object
					if($table !== 'pages' ){
						//get all Records on the page

						$uidCollection	= $this->dataProvider->getRelevantElementIdsByTablenameAndPageId($table,$pageId);
						if(is_array($uidCollection)){
							foreach($uidCollection as $uid){
								$translateableElement 	= $this->getTranslateableElementFromDataProvider($table,$uid);
								$pageGroup->addTranslateableElement($translateableElement);
							}
						}
					}else{
						$translateablePageElement 	= $this->getTranslateableElementFromDataProvider($table,$pageId);
						$pageGroup->addTranslateableElement($translateablePageElement);
					}
				}
				$translateableInformation->addPageGroup($pageGroup);
			}
		}

		return $translateableInformation;
	}

	/**
	 * This method is used to fetch the translationInformation for each field and add it to a translateableElement. At the
	 * end the method returns the whole initialzid translateableElement.
	 *
	 * @param string name of the table
	 * @param int uid
	 * @return tx_l10nmgr_domain_translateable_translateableElement
	 */
	protected function getTranslateableElementFromDataProvider($table,$uid){
		$translationDetails = $this->dataProvider->getTranslationDetailsByTablenameAndElementId($table,$uid);

		$translationInfo 	= $translationDetails['translationInfo'];

		$translateableElement = new tx_l10nmgr_domain_translateable_translateableElement();
		$translateableElement->setTableName($table);

		$translateableElement->setLogs($translationDetails['log']);

		$translateableElement->setUid($translationInfo['uid']);
		$translateableElement->setCType($translationInfo['CType']);
		$translateableElement->setSysLanguageUid($translationInfo['sys_language_uid']);
		$translateableElement->setTranslationTable($translationInfo['translation_table']);
		$translateableElement->setTranslations($translationInfo['translations']);
		$translateableElement->setExcessiveTranslations($translationInfo['excessive_translations']);

		$translationFields = $translationDetails['fields'];
		if(is_array($translationFields)){
			foreach($translationFields as $key => $translationField) {

				//@todo refactor determination of fieldName
				list(,$uidString,$fieldName) = explode(':',$key);
				list($uidValue) = explode('/',$uidString);

				$translateableField = new tx_l10nmgr_domain_translateable_translateableField();
				$translateableField->setIdentityKey($key);
				$translateableField->setFieldName($fieldName);
				$translateableField->setUidValue($uidValue);
				$translateableField->setDefaultValue($translationField['defaultValue']);
				$translateableField->setTranslationValue($translationField['translationValue']);
				$translateableField->setDiffDefaultValue($translationField['diffDefaultValue']);
				$translateableField->setPreviewLanguageValues($translationField['previewLanguageValues']);
				$translateableField->setMessage($translationField['msg']);
				$translateableField->setReadOnly($translationField['readOnly']);
				$translateableField->setFieldType($translationField['fieldType']);
				$translateableField->setIsRTE($translationField['isRTE']);
				$translateableField->setIsHTML($translationField['isHTML']);

				//if it is changed and we jus what changed elements OR if we don't care about that an element was changed
				//@todo does this also work for the export at import time? in the old version new and changed elements where only excluded in the view
				if(	( 	($translateableField->isChanged() && $this->dataProvider->getOnlyNewAndChanged())
							||
							!$this->dataProvider->getOnlyNewAndChanged())
						&&
						$this->isTranslateableField($table,$translateableField)
					) {
						$translateableElement->addTranslateableField($translateableField);
				}
			}
		}

		return $translateableElement;
	}

	/**
	 * This method is used to test if an field of a pages element is configured a pageOverlayField.
	 *
	 * @param $table
	 * @param $translateableField
	 * @return boolean
	 */
	protected function isTranslateableField($table,$translateableField){
		if ($table == 'pages') {
			$overlayFieldArray = t3lib_div::trimExplode(",",$GLOBALS["TYPO3_CONF_VARS"]["FE"]["pageOverlayFields"]);
			if (is_array($overlayFieldArray) && in_array($translateableField->getFieldName(),$overlayFieldArray)) {
				return true;
			} else {
				//non existing overlay field
				return false;
			}
		} else {
			return true;
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/domain/translateable/class.tx_l10nmgr_domain_translateable_translateableInformationFactory.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/domain/translateable/class.tx_l10nmgr_domain_translateable_translateableInformationFactory.php']);
}
?>