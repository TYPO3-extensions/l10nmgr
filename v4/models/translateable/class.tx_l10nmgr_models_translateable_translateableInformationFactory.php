<?php
require_once (t3lib_extMgm::extPath ( 'l10nmgr' ) . 'models/translateable/class.tx_l10nmgr_models_translateable_typo3TranslateableFactoryDataProvider.php');

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


	
	/**
	 * Constructor
	 * 
	 * @param void
	 * @return void
	 */
	public function __construct(){

	}
	
	/**
	 * This method is used, to create a translateableInformation object structure from a
	 * configuration, a set of pageIds and a target and a previewLanguage.
	 * Internally it iterates the pageIdCollection and fetches the information for a translation using the 
	 * tx_l10nmgr_tools class.
	 *
	 * @param tx_l10nmgr_models_configuration_configuration $l10ncfg
	 * @param ArrayObject $pageIdCollection A set of pageIds. This set of pageIds is used, to create a translateable Information of the contentelments in these pages.
	 * @param tx_l10nmgr_models_language_language $targetLanguage
	 * @param tx_l10nmgr_models_language_language $previewLanguage
	 * @todo we need to handle the include index
	 */
	public function create(tx_l10nmgr_interaces_translateable_translateableFactoryDataProvider $dataProvider){

		$this->dataProvider			= $dataProvider;
		
		$translateableInformation 	= new tx_l10nmgr_models_translateable_translateableInformation();
		$translateableInformation->setSourceLanguage($this->dataProvider->getSourceLanguage());
		$translateableInformation->setTargetLanguage($this->dataProvider->getTargetLanguage());
		$translateableInformation->setSiteUrl($this->dataProvider->getSiteUrl());
		$translateableInformation->setWorkspaceId($this->dataProvider->getWorkspaceId());
		
		$pageIdCollection		= $this->dataProvider->getRelevantPageIds();
		$tables_to_process		= $this->dataProvider->getRelevantTables();
		
		//we only need to process tables which are in the TCA AND the configuration
		
		//iterate pageId collection
		foreach($pageIdCollection as $pageId){
			$pageRow = t3lib_BEfunc::getRecordWSOL('pages',$pageId);

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
	 * @param 
	 * 
	 */
	protected function getTranslateableElementFromDataProvider($table,$uid){
		
		$translationDetails = $this->dataProvider->getTranslationDetailsByTablenameAndElementId($table,$uid);
		
		$translationInfo 	= $translationDetails['translationInfo'];
		
		$translateableElement = new tx_l10nmgr_models_translateable_translateableElement();
		$translateableElement->setTable($table);
		
		$translateableElement->setLogs($translationDetails['log']);
		
		$translateableElement->setUid($translationInfo['uid']);
		$translateableElement->setSysLanguageUid($translationInfo['sys_language_uid']);
		$translateableElement->setTranslationTable($translationInfo['translation_table']);
		$translateableElement->setTranslations($translationInfo['translations']);
		$translateableElement->setExcessiveTranslations($translationInfo['excessive_translations']);
		
		$translationFields = $translationDetails['fields'];
		if(is_array($translationFields)){
			foreach($translationFields as $key => $translationField){
				
				//@todo refactor determination of fieldName
				list(,$uidString,$fieldName) = explode(':',$key);
				list($uidValue) = explode('/',$uidString);
				
				$translateableField = new tx_l10nmgr_models_translateable_translateableField();
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
				
				$translateableElement->addTranslateableField($translateableField);
			}
		}
		
		return $translateableElement;
	}
}

?>