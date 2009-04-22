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

require_once(t3lib_extMgm::extPath('l10nmgr').'models/language/class.tx_l10nmgr_models_language_language.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/language/class.tx_l10nmgr_models_language_languageRepository.php');

require_once(t3lib_extMgm::extPath('l10nmgr').'models/exporter/class.tx_l10nmgr_models_exporter_exportData.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/exporter/class.tx_l10nmgr_models_exporter_exportDataRepository.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/exporter/class.tx_l10nmgr_models_exporter_exportFile.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/exporter/class.tx_l10nmgr_models_exporter_exportFileRepository.php');

require_once(t3lib_extMgm::extPath('l10nmgr').'models/exporter/class.tx_l10nmgr_models_exporter_exporter.php');
require_once t3lib_extMgm::extPath('l10nmgr').'models/exporter/class.tx_l10nmgr_models_exporter_workflowState.php';

require_once t3lib_extMgm::extPath('l10nmgr').'models/exporter/class.tx_l10nmgr_models_exporter_workflowStateRepository.php';

require_once(t3lib_extMgm::extPath('l10nmgr').'interfaces/interface.tx_l10nmgr_interfaces_wordsCountable.php');

require_once(t3lib_extMgm::extPath('l10nmgr').'models/translateable/class.tx_l10nmgr_models_translateable_pageGroup.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/translateable/class.tx_l10nmgr_models_translateable_translateableElement.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/translateable/class.tx_l10nmgr_models_translateable_translateableField.php');

require_once(t3lib_extMgm::extPath('l10nmgr').'models/translateable/class.tx_l10nmgr_models_translateable_translateableInformation.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/translateable/class.tx_l10nmgr_models_translateable_translateableInformationFactory.php');

require_once(t3lib_extMgm::extPath('l10nmgr').'view/export/class.tx_l10nmgr_view_export_showExportForm.php');

require_once(t3lib_extMgm::extPath('mvc').'mvc/view/widget/class.tx_mvc_view_widget_progress.php');
require_once(t3lib_extMgm::extPath('mvc').'mvc/view/widget/class.tx_mvc_view_widget_progressAjax.php');

###
# OLD VIEWS
###
require_once(t3lib_extMgm::extPath('l10nmgr').'views/CATXML/class.tx_l10nmgr_CATXMLView.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'views/excelXML/class.tx_l10nmgr_excelXMLView.php');
	// autoload the mvc
if (t3lib_extMgm::isLoaded('mvc')) {
	tx_mvc_common_classloader::loadAll();
} else {
	exit('Framework "mvc" not loaded!');
}

/**
 * description
 *
 * {@inheritdoc }
 *
 * class.tx_l10nmgr_controller_xmlexport.php
 *
 * @author	 Timo Schmidt <schmidt@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @version $Id: class.tx_l10nmgr_controller_xmlexport.php $
 * @date 16.04.2009 - 12:28:56
 * @see tx_mvc_controller_action
 * @category controller
 * @package	TYPO3
 * @subpackage	l10nmgr'
 * @access public
 */
class tx_l10nmgr_controller_export extends tx_mvc_controller_action {

	/**
	 * @var        string
	 */
	protected $extensionKey = 'l10nmgr';

	/**
	 * @var        string
	 */
	protected $defaultActionMethodName = 'showExportFormAction';

	/**
	 * @var        string
	 */
	protected $argumentsNamespace = 'l10nmgr';

	protected $keepArgumentKeys = array('noHidden','noXMLCheck','checkUTF8','selectedExportFormat','exportDataId');


	/**
	 * This action method is used
	 *
	 */
	public function showExportFormAction(){

		$doc = t3lib_div::makeInstance('mediumDoc'); /* @var $doc mediumDoc */
		$doc->backPath = $GLOBALS['BACK_PATH'];

		$content .= $doc->startPage($this->getViewHelper('tx_mvc_viewHelper_label')->get('export_xml'));
		$content .= $doc->header($this->getViewHelper('tx_mvc_viewHelper_label')->get('export_xml'));
		$content .= $doc->spacer(5) . $doc->divider(5);

		$this->view->setAvailableSourceLanguages($this->getLanguagesForLanguageMenu(true));
		$this->view->setAvailableTargetLanguages($this->getLanguagesForLanguageMenu(false));
		$this->view->setSelectedExportFormat($this->arguments['selectedExportFormat']);
		$this->view->setRenderAction('generateExport');
		$this->view->setAvailableExportFormats(array('xml' => 'general.action.export.xml.title', 'xls' => 'general.action.export.xls.title'));
		$this->view->setConfigurationId($this->arguments['configurationId']);

		//set backend related
		$this->view->setPrepend($content);
		$this->view->setAppend($doc->endPage());
	}


	public function generateExportAction(){
		$configurationId			= $this->arguments['configurationId'];
		$checkForExistingExports	= $this->arguments['checkForExistingExports'];

		###
		# LOAD CONFIGURATION
		##
		$configurationRepository	= new tx_l10nmgr_models_configuration_configurationRepository();
		$l10Configuration			= $configurationRepository->findById($configurationId);

		if(!$checkForExistingExports && !$l10Configuration->hasIncompleteExports()){
			$this->routeToAction('startExportAction');
		}else{

			$this->routeToAction('showExportsAction');
		}
	}

	
	/**
	 * This method is used to show a list of existing exports
	 *
	 */
	public function showExportsAction(){

		$workflowsStateRepository	= new tx_l10nmgr_models_exporter_workflowStateRepository();
		$notReimportedExports 		= $workflowsStateRepository->findAllWhereLatestState(tx_l10nmgr_models_exporter_workflowState::WORKFLOWSTATE_EXPORTED);		
				
		echo "Debug".__FILE__." ".__LINE__;
		print('<pre>');
		print_r($notReimportedExports);					
		print('</pre>');
	}


	/**
	 * This method is used to start an export. It creates an exportData object
	 * and loads the exportView with a progressbar.
	 *
	 */
	public function startExportAction(){
		$configurationId			= $this->arguments['configurationId'];
		$sourceLanguageId 			= $this->arguments['sourceLanguageId'];

		###
		# LOAD CONFIGURATION
		##
		$configurationRepository	= new tx_l10nmgr_models_configuration_configurationRepository();
		$l10Configuration			= $configurationRepository->findById($configurationId);

		##
		# HANDLE LANGUAGES
		##
		$languageRespository 	= new  tx_l10nmgr_models_language_languageRepository();
		$targetLanguage 		= $languageRespository->findById($this->arguments['targetLanguageId']);


		##
		# CREATE EXPORT DATA
		##
		$exportData		= new tx_l10nmgr_models_exporter_exportData();
		$exportData->setTablelist($l10Configuration->getTablelist());
		$exportData->setExportType(1);
		$exportData->setTranslationLanguageObject($targetLanguage);
		$exportData->setL10NConfiguration($l10Configuration);
		$exportData->setSourceLanguageId($sourceLanguageId);
		$exportData->setTitle($l10Configuration->getTitle());

		$exportDataRepository = new tx_l10nmgr_models_exporter_exportDataRepository();
		$exportDataRepository->add($exportData);
		$this->arguments['exportDataId'] = $exportData->getUid();


		$linkCreator	= $this->getViewHelper('tx_mvc_viewHelper_linkCreator');
		$ajaxLink 		= $linkCreator->getAjaxActionLink ( 'ajaxDoExportRun' );
		$progressUrl 	= $ajaxLink->useOverruledParameters()->makeUrl ();


		$progressView = new tx_mvc_view_widget_progress();
		$this->initializeView($progressView );
		$progressView ->setProgress(0);
		$progressView->setProgressUrl($progressUrl);
		$progressView->setAjaxEnabled(true);

		$this->view->setExportData($exportData);
		$this->view->setProgressView($progressView);
	}

	/**
	 * This method performs on run of an export. It is polled via ajax to perform a complete export.
	 * It returns an exportData object
	 *
	 * @return tx_l10nmgr_models_exporter_exportData
	 */
	public function performExportRun(){
		##
		# GET ARGUMENTS, NOTE: Arguments are available here because they have been marked as keepArguments
		##
		$onlyChangedContent 	= $this->arguments['onlyChangedContent'];
		$noHidden				= $this->arguments['noHidden'];
		$noXMLCheck				= $this->arguments['noXMLCheck'];
		$checkUTF8				= $this->arguments['checkUTF8'];
		$exportFormat			= $this->arguments['selectedExportFormat'];
		$exportDataID			= $this->arguments['exportDataId'];
		//@todo is valid int?
		
		$exportPath 			= $this->configuration->get('exportPath');
		//@todo is correct path?
		
		$exportDataRepository 	= new tx_l10nmgr_models_exporter_exportDataRepository();
		$exportData 			= $exportDataRepository->findById($exportDataID);

		$sourceLanguage			= $exportData-> getSourceLanguageObject();
		$l10Configuration		= $exportData-> getL10nConfigurationObject();

		//perform Export
		$exportView				= $this->getInitializedExportView($exportFormat,$sourceLanguage,$l10Configuration,$noXMLCheck,$checkUTF8,$onlyChangedContent,$noHidden);
		$exporter 				= new tx_l10nmgr_models_exporter_exporter($exportData,5,$exportView);

		$res 					= $exporter->run();
		
		if($res){
			$exportData 	= $exporter->getExportData();
			$exportData->increaseNumberOfExportRuns();
			$chunkResult 	= $exporter->getResultForChunk();
			$exportFile		= new tx_l10nmgr_models_exporter_exportFile();

			$exportFile->setFilename($exportView->getFilename($exportData->getNumberOfExportRuns()));
			$exportFile->setExportDataObject($exportData);
			$exportFile->setContent($chunkResult);

			$exportFile->setPath($exportPath);
			$exportFile->write();

			$exportFileRepository = new tx_l10nmgr_models_exporter_exportFileRepository();
			$exportFileRepository->add($exportFile);
			$exportDataRepository->save($exportData);
		
		}
		return $exportData;
	}




	/**
	 * This method is used to do an export run via ajax. It internally routes
	 * the request to the doExportRunAction
	 *
	 * @param void
	 */
	public function ajaxDoExportRunAction(){

		// $exportData = $this->routeToAction('performExportRun');
		$exportData = $this->performExportRun();

		$progressView = new tx_mvc_view_widget_progressAjax();
		$this->initializeView($progressView);
		$progressView->setProgress($exportData->getExportProgressPercentage());
		$progressView->setProgressLabel(round($exportData->getExportProgressPercentage()). ' %');

		echo $progressView->render();
		exit();
	}

	/**
	 * Helper method to determin all relevant languages for the dropdown language menu
	 *
	 * @param boolean $includeDefaultLanguage
	 * @return array
	 */
	protected function getLanguagesForLanguageMenu($includeDefaultLanguage = false){
		$t8Tools = t3lib_div::makeInstance('t3lib_transl8tools');
		$sysL = $t8Tools->getSystemLanguages();

		//add the default language
		if($includeDefaultLanguage){
			$languages[0] = "-default-";
		}

		foreach($sysL as $sL)	{
			if ($sL['uid']>0 && $GLOBALS['BE_USER']->checkLanguageAccess($sL['uid']))	{
				if ($this->configuration->get('enable_hidden_languages') == 1 || $sL['hidden'] == 0) {
					$languages[$sL['uid']] = $sL['title'];
				}
			}
		}

		return $languages;
	}


	/**
	* Creats an instance of a configured xml export view
	*
	 * @param tx_l10nmgr_models_language_language $previewLanguage
	 * @return tx_l10nmgr_CATXMLView
	 */
	protected function getInitializedExportView($exportFormat,$sourceLanguage,$l10ncfgObj,$noXmlCheck=false,$useUtf8Mode=false,$onlyChangedContent=false,$noHidden=false){

		if($exportFormat == 'xml'){
			$viewClassName	= t3lib_div::makeInstanceClassName('tx_l10nmgr_CATXMLView');
			$viewClass		= new $viewClassName();
			$viewClass->setSkipXMLCheck($noXmlCheck);
			$viewClass->setUseUTF8Mode($useUtf8Mode);

		}elseif($exportFormat == 'xls'){
			$viewClassName	= t3lib_div::makeInstanceClassName('tx_l10nmgr_excelXMLView');
			$viewClass		= new $viewClassName();
		}

		$viewClass->setForcedSourceLanguage($sourceLanguage);
		$viewClass->setL10NConfiguration($l10ncfgObj);

		if ($onlyChangedContent) {
			$viewClass->setModeOnlyChanged();
		}
		if ($noHidden) {
			$viewClass->setModeNoHidden();
		}

		return $viewClass;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/controller/class.tx_l10nmgr_controller_xmlexport.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/controller/class.tx_l10nmgr_controller_xmlexport.php']);
}
?>