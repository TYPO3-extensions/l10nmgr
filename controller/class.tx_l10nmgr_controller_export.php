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

require_once(t3lib_extMgm::extPath('l10nmgr').'controller/class.tx_l10nmgr_controller_abstractProgressable.php');

require_once(t3lib_extMgm::extPath('l10nmgr').'models/language/class.tx_l10nmgr_models_language_language.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/language/class.tx_l10nmgr_models_language_languageRepository.php');

require_once(t3lib_extMgm::extPath('l10nmgr').'models/exporter/class.tx_l10nmgr_models_exporter_exportData.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/exporter/class.tx_l10nmgr_models_exporter_exportDataRepository.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/exporter/class.tx_l10nmgr_models_exporter_exportFile.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/exporter/class.tx_l10nmgr_models_exporter_exportFileRepository.php');

require_once(t3lib_extMgm::extPath('l10nmgr').'models/exporter/class.tx_l10nmgr_models_exporter_exporter.php');
require_once t3lib_extMgm::extPath('l10nmgr').'models/exporter/class.tx_l10nmgr_models_exporter_workflowState.php';

require_once t3lib_extMgm::extPath('l10nmgr').'models/exporter/class.tx_l10nmgr_models_exporter_workflowStateRepository.php';

require_once(t3lib_extMgm::extPath('l10nmgr').'models/translateable/class.tx_l10nmgr_models_translateable_translateableInformation.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/translateable/class.tx_l10nmgr_models_translateable_translateableInformationFactory.php');

require_once(t3lib_extMgm::extPath('l10nmgr').'view/export/class.tx_l10nmgr_view_export_showExportList.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'view/export/class.tx_l10nmgr_view_export_showExportDetail.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'view/class.tx_l10nmgr_view_showProgress.php');


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
class tx_l10nmgr_controller_export extends tx_l10nmgr_controller_abstractProgressable {

	/**
	 * @var        string
	 */
	protected $extensionKey = 'l10nmgr';

	/**
	 * @var        string
	 */
	protected $argumentsNamespace = 'tx_l10nmgrexport';

	/**
	 * These arguments should be kept by the controller because
	 * they are needed in the polling ajax request.
	 *
	 * @var unknown_type
	 */
	protected $keepArgumentKeys = array('noHidden','noXMLCheck','checkUTF8','selectedExportFormat','exportDataId','warningCount');

	/**
	 * @var array mapping external parameters to arguments
	 */
	protected $mapParametersToArguments = array(
		'createdRecord' => 'returnEditConf',
	);

	/**
	 * Abort action: redirects to the list controller
	 *
	 * @param void
	 * @return void (never returns)
	 */
	public function abortAction() {
        // redirect to list controller by sending a "Location" header
        header('Location: '.t3lib_div::locationHeaderUrl('../mod1/index.php'));
        exit();
	}

	/**
	 * This method is used to process a submitted exportForm.
	 * Depending on the checkboxes we need to start an exportProcess
	 * or need to show all exports which have not been reimported yet.
	 *
	 * @param void
	 * @return void
	 * @author Timo Schmidt
	 */
	public function generateExportAction() {

		$exportDataId = tx_mvc_common_typo3::parseReturnEditConf($this->arguments['createdRecord'], 'tx_l10nmgr_exportdata');

		if ($exportDataId !== false) {
			$this->arguments['exportDataId'] = intval( $exportDataId );
			tx_mvc_validator_factory::getIntValidator()->isValid($this->arguments['exportDataId'], true);

			$exportDataRepository = new tx_l10nmgr_models_exporter_exportDataRepository();
			$exportData = $exportDataRepository->findById($this->arguments['exportDataId']);

			$exportData->setTitle(sprintf('%s [%s->%s] (site:%s)', $exportData->getTitle(), $exportData->getSourceIsoCode(), $exportData->getTranslationIsoCode(), $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']));
			$exportDataRepository->save($exportData);

			$this->arguments['configurationId'] = $exportData->getL10ncfg_id();

			if (!$exportData->getCheckForExistingExports()) {
				$l10Configuration = $exportData->getL10nConfigurationObject();
				if (!$l10Configuration->hasIncompleteExports()) {

					//route to action from abstract controller

					#@see tx_l10nmgr_controller_abstractProgressable
					$this->routeToAction('showProgressAction');
				} else {
					$this->routeToAction('showNotReimportedExportsAction');
				}
			} else {
				$this->routeToAction('showNotReimportedExportsAction');
			}

		} else {
			$this->routeToAction('abortAction');
		}
	}


	/**
	 * After an export is completed there should appear a list with all files. This method
	 * is used to overwrite the redircet after completion.
	 *
	 * @todo
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 * @see showProgressAction
	 */
	protected function getRedirectUrlOnCompletion() {
		return '../export/index.php'.$this->getViewHelper('tx_mvc_viewHelper_linkCreator')->getAjaxActionLink('showExportDetail')->useOverruledParameters()->makeUrl();
	}

	/**
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 */
	protected function getRedirectUrlOnAbort(){
		return '../mod1/index.php';		
	}
	
	/**
	 * This method is used to show a list of files for a generated export.
	 *
	 * @author Timo Schmidt
	 * @param void
	 * @return string html content
	 */
	public function showExportDetailAction() {
		tx_mvc_validator_factory::getIntValidator()->isValid($this->arguments['exportDataId'], true);

		$exportDataRepository	= new tx_l10nmgr_models_exporter_exportDataRepository();
		$exportData 			= $exportDataRepository->findById($this->arguments['exportDataId']);

		$this->view->setExportData($exportData);
		$this->view->showFiles();
		//@todo how can this be done with the link view helper?
		$this->view->setListLink('../mod1/index.php');
		$this->view->showListLink();
		$this->view->addBackendStylesHeaderData();
	}

	/**
	 * This method is used to show a list of existing exports.
	 * It uses tx_l10nmgr_view_export_showExportList view because
	 * it is a variant of a list view of exports.
	 *
	 * @see tx_l10nmgr_view_export_showExportList
	 * @param void
	 * @return void
	 */
	public function showNotReimportedExportsAction() {
		tx_mvc_validator_factory::getIntValidator()->isValid($this->arguments['exportDataId'], true);

		$exportDataRepository = new tx_l10nmgr_models_exporter_exportDataRepository();
		$exportData = $exportDataRepository->findById($this->arguments['exportDataId']);

		$exportDataRepository	= new tx_l10nmgr_models_exporter_exportDataRepository();
		$notReimportedExports 	= $exportDataRepository->findAllWithoutStateInHistoryByAssigendConfigurationAndTargetLanguage(
			tx_l10nmgr_models_exporter_workflowState::WORKFLOWSTATE_IMPORTED,
			$exportData->getL10ncfg_id(), // configuration id
			$exportData->getTranslation_lang() // target language id
		);

		$this->view = new tx_l10nmgr_view_export_showExportList();
		$this->initializeView($this->view);

		$this->view->setExportDataCollection($notReimportedExports);
		$this->view->addBackendStylesHeaderData();
	}


	/**
	 * This method returns a view for the subject that is progressable.
	 * In case of the export this is a view which displays informations of the export
	 * data.
	 *
	 * @param void
	 * @return tx_mvc_view_widget_phpTemplateListView
	 */
	protected function getProgressableSubjectView() {

		$view = new tx_l10nmgr_view_export_showExportDetail();
		$this->initializeView($view);
		$view->setExportData($this->getProgressableSubject());

		return $view;
	}

	/**
	 * This method returns the progressableSubject. In case of the import
	 * controller this is an exportData object.
	 *
	 * @return tx_l10nmgr_models_exporter_exportData
	 */
	protected function getProgressableSubject() {
		tx_mvc_validator_factory::getIntValidator()->isValid($this->arguments['exportDataId'],true);

		$exportDataRepository 	= new tx_l10nmgr_models_exporter_exportDataRepository();
		$exportData 			= $exportDataRepository->findById($this->arguments['exportDataId']);

		return $exportData;
	}

	/**
	 * Worker method called by ajaxPerformRunAction. It
	 * performs the use case specific logic. In case of the exporter
	 * this is the logic to export 5 files from the exportData.
	 *
	 * @see ajaxPerformRunAction
	 */
	protected function performProgressableRun($exportData) {
		$res = tx_l10nmgr_models_exporter_exporter::performFileExportRun($exportData,$this->configuration->get('pagesPerChunk'));
		
		return $res;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/controller/class.tx_l10nmgr_controller_xmlexport.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/controller/class.tx_l10nmgr_controller_xmlexport.php']);
}
?>