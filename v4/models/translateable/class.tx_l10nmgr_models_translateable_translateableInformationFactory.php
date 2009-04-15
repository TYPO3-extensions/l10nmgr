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
 * tx_l10nmgr_models_translateable_translateableInformationFactory
 *
 * @author	 Timo Schmidt <schmidt@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.tx_l10nmgr_models_translateable_translateableInformationFactory.php $
 * @date 02.04.2009 - 14:41:28
 * @package	TYPO3
 * @subpackage	l10nmgr
 * @access public
 */
class tx_l10nmgr_models_translateable_translateableInformationFactory {

	protected $disallowDoktypes;
	
	/**
	 * Constructor
	 * 
	 * @param void
	 * @return void
	 */
	public function __construct(){
		$this->disallowDoktypes = array('--div--','3','255');
	}
	
	/**
	 * 
	 *
	 * @param tx_l10nmgr_models_configuration_configuration $l10ncfg
	 * @param ArrayObject $pageIdCollection
	 * @param tx_l10nmgr_models_language_Language $targetLanguage
	 * @param tx_l10nmgr_models_language_Language $previewLanguage
	 * @todo we need to handle the include index
	 */
	public function create(	tx_l10nmgr_models_configuration_configuration $l10ncfg, 
							ArrayObject $pageIdCollection, 
							tx_l10nmgr_models_language_Language $targetLanguage, 
							tx_l10nmgr_models_language_Language $previewLanguage = NULL){

		$translateableInformation 	= new tx_l10nmgr_models_translateable_translateableInformation();
		$translateableInformation->setPreviewLanguage($previewLanguage);
		$translateableInformation->setTargetLanguage($targetLanguage);
		$translateableInformation->setSiteUrl(t3lib_div::getIndpEnv("TYPO3_SITE_URL"));
		$translateableInformation->setWorkspaceId($GLOBALS['BE_USER']->workspace);
		
		
		$t8Tools					= $this->getInitializedt8Tools($l10ncfg,$previewLanguage);
		$flexFormDiff				= $this->getFlexFormDiffForTargetLanguage($l10ncfg,$targetLanguage);
		
		//we only need to process tables which are in the TCA AND the configuration
		$tca_tables					= $this->getTCATablenames();
		$tables_to_process			= array_intersect($tca_tables,$l10ncfg->getTableArray());

		$excludeArray				= $l10ncfg->getExcludeArray();
		
		//iterate pageId collection
		foreach($pageIdCollection as $pageId){
			$pageRow = t3lib_BEfunc::getRecordWSOL('pages',$pageId);
			
			/**
			 * old check:
			 * 
			 *if(!self::isInIncludeOrExcludeArray($excludeArray,'pages',$pageId)  && ($pageRow['l18n_cfg']&2)!=2 && !in_array($pageRow['doktype'], $this->disallowDoktypes)){
			 * 
			 * $pageRow['l18n_cfg']&2)!=2 This check meens that the following options should not be checked in the backend:
			 * "Hide default translation of page" but it should be possible to translate pages where the default translation is hidden, therefore
			 * this check has been removed.
			 */
			if(!self::isInIncludeOrExcludeArray($excludeArray,'pages',$pageId)  && $this->hasAllowedDoctype($pageRow)){
				
				if(is_array($pageRow)){	
					
					//create pageGroup and add metadata
					$pageGroup 	= new tx_l10nmgr_models_translateable_PageGroup();
					$pageGroup->setPageRow($pageRow);
	
					// traverse tca tables:
					foreach($tables_to_process as $table)	{
						//the table pages its self is not needed because the informations of the page 
						//are attached to the page group object
						if($table !== 'pages'){					
							//get all Records on the page
							
							$tableRowCollection 	= $t8Tools->getRecordsToTranslateFromTable($table, $pageId);
							if(is_array($tableRowCollection)){
								foreach($tableRowCollection as $tableRow){
									if(!self::isInIncludeOrExcludeArray($excludeArray,$table,$tableRow['uid'])){									
										$translateableElement 	= $this->getTranslateableElementFromTableRow($table,$tableRow,$targetLanguage,$flexFormDiff,$t8Tools);
										$pageGroup->addTranslateableElement($translateableElement);
									}
								}
							}
						}else{
							$translateablePageElement =	 $this->getTranslateableElementFromTableRow($table,$pageRow,$targetLanguage,$flexFormDiff,$t8Tools);
							$pageGroup->addTranslateableElement($translateablePageElement);
						}
					}
				
					$translateableInformation->addPageGroup($pageGroup);
				}
			}
		}
		
		
		return $translateableInformation;
	}
	
	/**
	 * Method to check that the doctype of the page is not an disallowed doctype
	 *
	 * @param array $pageRow
	 * @return boolean
	 */
	protected function hasAllowedDoctype($pageRow){
		return !in_array($pageRow['doktype'], $this->disallowDoktypes);
	}

	/**
	 * This method is used to determine if an element is in the list of include or excluded elements.
	 *
	 * @param array $array
	 * @param string $table
	 * @param int $id
	 * @return boolean
	 */
	private static function isInIncludeOrExcludeArray($array,$table,$id){
		return isset($array[$table.':'.$id]);
	}
	
	/**
	 * This method is used to determine all tables, configured in the TCA
	 * 
	 * @return array
	 */
	protected function getTCATablenames(){
		global $TCA;
		$tca_tables = array_keys($TCA);
		return $tca_tables;
	}
	
	/**
	 * This method is used to fetch the translationInformation for each field and add it to a translateableElement. At the 
	 * end the method returns the whole initialzid translateableElement.
	 * 
	 * @param string name of the table
	 * @param 
	 * 
	 */
	protected function getTranslateableElementFromTableRow($tableName, $tableRow,$targetLanguage,$flexFormDiff,$t8Tools){
		t3lib_BEfunc::workspaceOL($tableName,$tableRow);

		/**
		 * Load the translationDetails from the t8Tools
		 */
		$translationDetails = $t8Tools->translationDetails($tableName,$tableRow,$targetLanguage->getUid(),$flexFormDiff);
		$translationInfo 	= $translationDetails['translationInfo'];
		
		$translateableElement = new tx_l10nmgr_models_translateable_translateableElement();
		$translateableElement->setTable($tableName);
		
		$translateableElement->setLogs($translationDetails['log']);
		
		$translateableElement->setUid($translationInfo['uid']);
		$translateableElement->setSysLanguageUid($translationInfo['sys_language_uid']);
		$translateableElement->setTranslationTable($translationInfo['translation_table']);
		$translateableElement->setTranslations($translationInfo['translations']);
		$translateableElement->setExcessiveTranslations($translationInfo['excessive_translations']);
		
		$translationFields = $translationDetails['fields'];
		if(is_array($translationFields)){
			foreach($translationFields as $key => $translationField){
				
				$translateableField = new tx_l10nmgr_models_translateable_translateableField();
				$translateableField->setIdentityKey($key);
				$translateableField->setDefaultValue($translationField['defaultValue']);
				$translateableField->setTranslationValue($translationField['translationValue']);
				$translateableField->setDiffDefaultValue($translationField['diffDefaultValue']);
				$translateableField->setPreviewLanguageValues($translationField['previewLanguageValues']);
				$translateableField->setMessage($translationField['msg']);
				$translateableField->setReadOnly($translationField['readOnly']);
				$translateableField->setFieldType($translationField['fieldType']);
				$translateableField->setIsRTE($translationField['isRTE']);
				
				$translateableElement->addTranslateableField($translateableField);
			}
		}
		
		return $translateableElement;
	}
	
	/**
	 * The factory uses internally the t8tools to collect informations about a translation.
	 * This method is used to get an configured intance of the tools object
	 *
	 * @param tx_l10nmgr_models_configuration_configuration $l10ncfg
	 * @param tx_l10nmgr_models_language_Language $previewLanguage
	 * @return tx_l10nmgr_tools
	 */
	protected function getInitializedt8Tools($l10ncfg,$previewLanguage = NULL){
		// Init:
		$t8Tools = t3lib_div::makeInstance('tx_l10nmgr_tools');
		$t8Tools->verbose = FALSE;	// Otherwise it will show records which has fields but none editable.
		if ($l10ncfg->getIncludeFCEWithDefaultLanguage()) {
			$t8Tools->includeFceWithDefaultLanguage=TRUE;
		}
		
		if($previewLanguage instanceof tx_l10nmgr_models_language_Language ){
			$previewLanguageIds = $previewLanguage->getUid();
		}
		
		if(!$previewLanguageIds){
			$previewLanguageIds = current(t3lib_div::intExplode(',',$GLOBALS['BE_USER']->getTSConfigVal('options.additionalPreviewLanguages')));
		}
		if($previewLanguage)	{
			$t8Tools->previewLanguages = array($previewLanguageIds);
		}
				
		return $t8Tools;
	}
	
	/**
	 * Helpermethod to get the flexform diff
	 *
	 * @param tx_l10nmgr_models_configuration_configuration $l10ncfg
	 * @param tx_l10nmgr_models_language_Language $targetLanguage
	 * @return string
	 */
	protected function getFlexFormDiffForTargetLanguage($l10ncfg,$targetLanguage){
		// FlexForm Diff data:
		$flexFormDiff = unserialize($l10ncfg->getFlexFormDiff());
		$flexFormDiff = $flexFormDiff[$targetLanguage->getUid()];
		
		return $flexFormDiff;
	}
	
	/**
	 * just copyed to have the old code in place
	 * 
	 * @deprecated 
	 *
	 */
	protected function calculateInternalAccumulatedInformationsArray() {
//		global $TCA;
//		$tree=$this->tree;
//		$l10ncfg=$this->l10ncfg;
//		$accum = array();
//		$sysLang=$this->sysLang;
//
//			// FlexForm Diff data:
//		$flexFormDiff = unserialize($l10ncfg['flexformdiff']);
//		$flexFormDiff = $flexFormDiff[$sysLang];
//
//		$excludeIndex = array_flip(t3lib_div::trimExplode(',',$l10ncfg['exclude'],1));
//		$tableUidConstraintIndex = array_flip(t3lib_div::trimExplode(',',$l10ncfg['tableUidConstraint'],1));
//
//			// Init:
//		$t8Tools = t3lib_div::makeInstance('tx_l10nmgr_tools');
//		$t8Tools->verbose = FALSE;	// Otherwise it will show records which has fields but none editable.
//		if ($l10ncfg['incfcewithdefaultlanguage']==1) {
//			$t8Tools->includeFceWithDefaultLanguage=TRUE;
//		}
//
//			// Set preview language (only first one in list is supported):
//		if ($this->forcedPreviewLanguage!='') {
//			$previewLanguage=$this->forcedPreviewLanguage;
//		}
//		else {
//			$previewLanguage = current(t3lib_div::intExplode(',',$GLOBALS['BE_USER']->getTSConfigVal('options.additionalPreviewLanguages')));
//		}
//		if ($previewLanguage)	{
//			$t8Tools->previewLanguages = array($previewLanguage);
//		}
//
//			// Traverse tree elements:
//		foreach($tree->tree as $treeElement)	{
//
//			$pageId = $treeElement['row']['uid'];
//			if (!isset($excludeIndex['pages:'.$pageId]) && ($treeElement['row']['l18n_cfg']&2)!=2 && !in_array($treeElement['row']['doktype'], $this->disallowDoktypes) )	{
//
//				$accum[$pageId]['header']['title']	= $treeElement['row']['title'];
//				$accum[$pageId]['header']['icon']	= $treeElement['HTML'];
//				$accum[$pageId]['header']['prevLang'] = $previewLanguage;
//				$accum[$pageId]['items'] = array();
//
//					// Traverse tables:
//				foreach($TCA as $table => $cfg)	{
//
//						// Only those tables we want to work on:
//					if (t3lib_div::inList($l10ncfg['tablelist'], $table))	{
//
//						if ($table === 'pages')	{
//							$accum[$pageId]['items'][$table][$pageId] = $t8Tools->translationDetails('pages',t3lib_BEfunc::getRecordWSOL('pages',$pageId),$sysLang, $flexFormDiff);
//							$this->_increaseInternalCounters($accum[$pageId]['items'][$table][$pageId]['fields']);
//						} else {
//							$allRows = $t8Tools->getRecordsToTranslateFromTable($table, $pageId);
//
//							if (is_array($allRows))	{
//								if (count($allRows))	{
//										// Now, for each record, look for localization:
//									foreach($allRows as $row)	{
//										t3lib_BEfunc::workspaceOL($table,$row);
//										if (is_array($row) && count($tableUidConstraintIndex) > 0) {
//											if (is_array($row) && isset($tableUidConstraintIndex[$table.':'.$row['uid']]))	{
//												$accum[$pageId]['items'][$table][$row['uid']] = $t8Tools->translationDetails($table,$row,$sysLang,$flexFormDiff);
//												$this->_increaseInternalCounters($accum[$pageId]['items'][$table][$row['uid']]['fields']);
//											}
//										}else if (is_array($row) && !isset($excludeIndex[$table.':'.$row['uid']]))	{
//											$accum[$pageId]['items'][$table][$row['uid']] = $t8Tools->translationDetails($table,$row,$sysLang,$flexFormDiff);
//											$this->_increaseInternalCounters($accum[$pageId]['items'][$table][$row['uid']]['fields']);
//										}
//									}
//								}
//							}
//						}
//					}
//				}
//			} 
//		}
//
//
//		$includeIndex = array_unique(t3lib_div::trimExplode(',',$l10ncfg['include'],1));
//		foreach($includeIndex as $recId)	{
//			list($table, $uid) = explode(':',$recId);
//			$row = t3lib_BEfunc::getRecordWSOL($table, $uid);
//			if (count($row))	{
//				$accum[-1]['items'][$table][$row['uid']] = $t8Tools->translationDetails($table,$row,$sysLang,$flexFormDiff);
//				$this->_increaseInternalCounters($accum[-1]['items'][$table][$row['uid']]['fields']);
//			}
//		}
//
//		$this->_accumulatedInformations=$accum;
	}	
}

?>