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

	// autoload the mvc
t3lib_extMgm::isLoaded('mvc', true);
tx_mvc_common_classloader::loadAll();

/**
 * description
 *
 * {@inheritdoc}
 *
 * class.tx_l10nmgr_controller_translate.php
 *
 * @author Timo Schmidt <schmidt@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id$
 * @date $Date$
 * @since 05.10.2009 - 11:15:10
 * @see tx_mvc_controller_action
 * @category controller
 * @package TYPO3
 * @subpackage mvc
 * @access public
 */
class tx_l10nmgr_controller_translate extends tx_mvc_controller_action {

	/**
	 * @var string
	 */
	protected $extensionKey = 'l10nmgr';

	/**
	 * @var string
	 */
	protected $defaultActionMethodName = 'inlineTranslateAction';

	/**
	 * @var string
	 */
	protected $argumentsNamespace = 'tx_l10nmgrtranslate';

	/**
	 * These arguments should be kept since they are needed in the ajax polling action
	 *
	 * @var array
	 */
	protected $keepArgumentKeys = array('configurationId','selectedTable','selectedUid','target_language','no_hidden','new_changed_only');

	/**
	 * These arguments will be stored in the session
	 *
	 * @var array
	 */
	protected $sessionArgumentKeys = array('configurationId');

	/**
	 * Called before processing - used to initialise the arguments
	 *
	 * @access protected
	 * @return void
	 */
	protected function initializeArguments() {}

	/**
	 * This method is used to show an form for inline content translation.
	 *
	 * @access public
	 * @return string HTML formated output
	 */
	public function inlineTranslateAction() {
		$this->view = new tx_l10nmgr_view_translate_borderPanel();
		$this->initializeView($this->view);

		$this->view->addView($this->getControlPanelView(),tx_mvc_view_widget_panelBorder::POSITION_NORTH);
		$this->view->addView($this->getInlineTranlationView(),tx_mvc_view_widget_panelBorder::POSITION_CENTER);

		return $this->view->render();
	}

	/**
	 * Returns the controll panel view.
	 *
	 * @return tx_l10nmgr_view_translate_controlPanel
	 */
	protected function getControlPanelView(){
		$languageRepository = new tx_l10nmgr_domain_language_languageRepository();
		$allLanguages		= $languageRepository->findAll();
		$l10ncfgObj = $this->getL10NConfigurationFromArguments();

		$default			= $languageRepository->findById(0);
		$allLanguages->append($default);

		$view = new tx_l10nmgr_view_translate_controlPanel();
		$this->initializeView($view);

		if($this->arguments['no_hidden'] == 1){ $view->setNoHidden(true); }
		if($this->arguments['new_changed_only']){ $view->setNewChangedOnly(true);	}

		$view->setAvailableLanguages($allLanguages);
		$view->setSelectedLanguage($this->getTargetLanguageFromArguments());
		$view->setL10NConfiguration($l10ncfgObj);

		return $view;
	}

	/**
	 * Returns an initialized view for inline translations.
	 *
	 * @return tx_l10nmgr_view_export_exporttypes_l10nHTMLList
	 */
	protected function getInlineTranlationView(){
		$l10ncfgObj 	= $this->getL10NConfigurationFromArguments();
		$targetLanguage = $this->getTargetLanguageFromArguments();

		$view 			= new tx_l10nmgr_view_export_exporttypes_l10nHTMLList();
		$view->setL10NConfiguration($l10ncfgObj);
		$this->initializeView($view);

		if($this->arguments['target_language'] != 0){

			$exportData	= $this->getBackendExportData();
			$translateableFactoryDataProvider 	= new tx_l10nmgr_domain_translateable_typo3TranslateableFactoryDataProvider($exportData);
			$translateableFactoryDataProvider->addPageIdCollectionToRelevantPageIds($l10ncfgObj->getExportPageIdCollection());
			$translateableFactoryDataProvider->setTargetLanguage($targetLanguage);
			$translateableInformationFactory 	= new tx_l10nmgr_domain_translateable_translateableInformationFactory();
			$translateableInformation			= $translateableInformationFactory->createFromDataProvider($translateableFactoryDataProvider);


			if($this->arguments['no_hidden'] == 1){
				$view->setModeNoHidden(true);
			}

			if($this->arguments['new_changed_only']){
				$view->setModeOnlyChanged(true);
			}

			$view->setModeWithInlineEdit();
			$view->setModeShowEditLinks();

			if($this->arguments['selectedTable'] != '' && $this->arguments['selectedUid'] != ''){
				$view->setSelectedItem($this->arguments['selectedTable'],$this->arguments['selectedUid']);
			}

			$view->setTranslateableInformation($translateableInformation);
			$view->setTargetLanguageId($targetLanguage->getUid());
		}

		$view->addBackendStylesHeaderData();

		return $view;
	}

	/**
	 * This method is used to save the translation to the database.
	 *
	 * @param void
	 */
	protected function saveTranslationAction(){
		$l10ncfgObj 		= $this->getL10NConfigurationFromArguments();
		$exportData			= $this->getBackendExportData();

		$TranslationFactory = new tx_l10nmgr_domain_translationFactory();
		$TranslationData    = $TranslationFactory->createFromFormSubmit($this->arguments['pageid'],$this->getTargetLanguageFromArguments()->getUid(),$this->arguments['translation'],$l10ncfgObj);

		// get collection of pageIds to create a translateableInformation for the relevantPages from the imported file
		$ImportPageIdCollection	= $TranslationData->getPageIdCollection();

		// create a dataProvider based on the exportData and the relevantPageIds of the importFile
		$factory                 = new tx_l10nmgr_domain_translateable_translateableInformationFactory();
		$TranlateableInformation = $factory->createFromExportDataAndPageIdCollection($exportData,$ImportPageIdCollection,$TranslationData->getWorkspaceId());

		// Save the translation into the database
		$TranslationService = new tx_l10nmgr_service_importTranslation();
		$TranslationService->save($TranlateableInformation, $TranslationData);

		$this->routeToAction('inlineTranslateAction');
	}

	protected function getBackendExportData(){
		$l10ncfgObj 	= $this->getL10NConfigurationFromArguments();
		$targetLanguage = $this->getTargetLanguageFromArguments();

		$exportData = new tx_l10nmgr_domain_exporter_exportData();
		$exportData->setOnlychangedcontent(false);
		$exportData->setL10NConfiguration($l10ncfgObj);
		$exportData->setTranslationLanguageObject($targetLanguage);


		return $exportData;
	}

	/**
	 * Creates an initialized target language object from the controller arguments.
	 *
	 * @param void
	 * @return tx_l10nmgr_domain_language_language
	 */
	protected function getTargetLanguageFromArguments(){
		$languageRepository = new tx_l10nmgr_domain_language_languageRepository();
		$targetLanguage = $languageRepository->findById($this->arguments['target_language']);

		return $targetLanguage;
	}

	/**
	 *
	 * @param void
	 * @return tx_l10nmgr_domain_configuration_configuration;
	 */
	protected function getL10NConfigurationFromArguments(){
		$cfgId = $this->arguments['configurationId'];
		$l10nmgrCfgRepository 	= new tx_l10nmgr_domain_configuration_configurationRepository();
		/* @var $l10ncfgObj tx_l10nmgr_domain_configuration_configuration */
		$l10ncfgObj = $l10nmgrCfgRepository->findById($cfgId);

		return $l10ncfgObj;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['class.tx_l10nmgr_controller_translate.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['class.tx_l10nmgr_controller_translate.php']);
}