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

require_once(t3lib_extMgm::extPath('l10nmgr').'interface/interface.tx_l10nmgr_interface_wordsCountable.php');

require_once(t3lib_extMgm::extPath('l10nmgr').'models/translateable/class.tx_l10nmgr_models_translateable_pageGroup.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/translateable/class.tx_l10nmgr_models_translateable_translateableElement.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/translateable/class.tx_l10nmgr_models_translateable_translateableField.php');

require_once(t3lib_extMgm::extPath('l10nmgr').'models/translateable/class.tx_l10nmgr_models_translateable_translateableInformation.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/translateable/class.tx_l10nmgr_models_translateable_translateableInformationFactory.php');

require_once(t3lib_extMgm::extPath('l10nmgr').'view/export/class.tx_l10nmgr_view_export_showExportList.php');

require_once(t3lib_extMgm::extPath('mvc').'mvc/view/widget/class.tx_mvc_view_widget_progress.php');
require_once(t3lib_extMgm::extPath('mvc').'mvc/view/widget/class.tx_mvc_view_widget_progressAjax.php');
require_once t3lib_extMgm::extPath('mvc').'util/class.tx_mvc_util_zip.php';

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
	protected $argumentsNamespace = 'l10nmgr';

	/**
	 * These arguments should be kept by the controller because
	 * they are needed in the polling ajax request.
	 *
	 * @var unknown_type
	 */
	protected $keepArgumentKeys = array('noHidden','noXMLCheck','checkUTF8','selectedExportFormat','exportDataId');

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
			$this->arguments['exportDataId'] = $exportDataId;

			$exportDataRepository = new tx_l10nmgr_models_exporter_exportDataRepository();
			$exportData = $exportDataRepository->findById($this->arguments['exportDataId']);

//		$tsConf = t3lib_BEfunc::getModTSconfig($this->getPid(), 'mod.SHARED.');
//		var_dump($tsConf['properties']);
//
//		// defaultLanguageFlag
//		// defaultLanguageLabel
//
//		// defaultLanguageISOCode
//		// defaultLanguageSajanCode

			$exportData->setTitle(sprintf('%s [%s->%s]', $exportData->getTitle(), $exportData->getSourceIsoCode(), $exportData->getTranslationIsoCode()));
			$exportDataRepository->save($exportData);

			$this->arguments['configurationId'] = $exportData->getL10ncfg_id();

			if (!$exportData->getCheckForExistingExports()) {
				$l10Configuration = $exportData->getL10nConfigurationObject();
				if (!$l10Configuration->hasIncompleteExports()) {
					$this->routeToAction('showExportProgressAction');
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
	 * This method is used to show a list of existing exports.
	 * It uses tx_l10nmgr_view_export_showExportList view because
	 * it is a variant of a list view of exports.
	 *
	 * @see tx_l10nmgr_view_export_showExportList
	 * @param void
	 * @return void
	 */
	public function showNotReimportedExportsAction() {

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
	 * This method is used to start an export. It creates an exportData object
	 * and loads the exportView with a progressbar.
	 *
	 * @param void
	 * @return void
	 *
	 */
	public function showExportProgressAction() {

		$exportDataRepository = new tx_l10nmgr_models_exporter_exportDataRepository();
		$exportData = $exportDataRepository->findById($this->arguments['exportDataId']);

		$progressView = new tx_mvc_view_widget_progress();
		$this->initializeView($progressView );
		$progressView->setProgress(0);
		$progressView->setAjaxEnabled(true);
		$progressView->setProgressUrl($this->getViewHelper('tx_mvc_viewHelper_linkCreator')->getAjaxActionLink('ajaxDoExportRun')->useOverruledParameters()->makeUrl());
		$progressView->setRedirectOnCompletedUrl('../mod1/index.php');

		$this->view->setExportData($exportData);
		$this->view->setProgressView($progressView);
		$this->view->addBackendStylesHeaderData();
	}

	/**
	 * This method is used to do an export run via ajax. It internally routes
	 * the request to the doExportRunAction
	 *
	 * @param void
	 */
	public function ajaxDoExportRunAction() {

		$exportDataRepository = new tx_l10nmgr_models_exporter_exportDataRepository();
		$exportData = $exportDataRepository->findById($this->arguments['exportDataId']);

		tx_l10nmgr_models_exporter_exporter::performExportRun($exportData, 5);

		$progressView = new tx_mvc_view_widget_progressAjax();
		$this->initializeView($progressView);
		$percent = $exportData->getExportProgressPercentage();
		$progressView->setProgress($percent);
		if ($percent < 100) {
			$progressView->setProgressLabel(round($exportData->getExportProgressPercentage()). ' %');
		} else {
			$progressView->setProgressLabel('Completed');
			$progressView->setCompleted(true);
		}

		echo $progressView->render();

		exit();
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/controller/class.tx_l10nmgr_controller_xmlexport.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/controller/class.tx_l10nmgr_controller_xmlexport.php']);
}
?>