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

require_once t3lib_extMgm::extPath('l10nmgr') . 'service/class.tx_l10nmgr_service_importTranslation.php';

/**
 *
 * class.tx_l10nmgr_models_importer_importer.php
 *
 * @author Timo Schmidt <schmidt@aoemedia.de>
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.tx_l10nmgr_models_importer_importer.php $
 * @date 24.04.2009 - 15:21:20
 * @package	TYPO3
 * @subpackage	tx_l10nmgr
 * @access public
 */
class tx_l10nmgr_models_importer_importer {

	/**
	 * @var tx_l10nmgr_models_importer_importData
	 */
	protected $importData;

	/**
	 * @var tx_l10nmgr_models_exporter_exportData
	 */
	protected $exportData;

	/**
	 * This method is used to create an import of a given
	 * tx_l10nmgr_models_importer_importData.
	 *
	 * @param tx_l10nmgr_models_importer_importData
	 * @access public
	 * @return void
	 */
	public function __construct($importData){
		$this->importData = $importData;
		$this->exportData = $importData->getExportDataObject();
	}

	/**
	 * This is the worker method of the importer, it uses the importData to get translationInform
	 *
	 * @access public
	 * @author Timo Schmidt <schmidt@aoemedia.de>
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return boolean
	 */
	public function run(){
		$isRunning = false;

			//!TODO  maybe importData
		if (! $this->importData->getImportIsCompletelyProcessed() ) {

			if ( $this->importData->getIsCompletelyUnprocessed() ) {
				/**
				 * @internal  workflowStates depend on the exportData object therefore we have to use it to mark the import as started
				 */
				$this->exportData->addWorkflowState(tx_l10nmgr_models_exporter_workflowState::WORKFLOWSTATE_IMPORTING);
			}

				// determine the next file to import
			$currentFile = $this->getNextFile();

			$TranslationFactory = new tx_l10nmgr_domain_translationFactory();
			$TranslationData    = $TranslationFactory->create($currentFile);

				// get collection of pageIds to create a translateableInformation for the relevantPages from the imported file
			$ImportPageIdCollection	= $TranslationData->getRelevantPageIds();

				// create a dataProvider based on the exportData and the relevantPageIds of the importFile
			$translateableFactoryDataProvider = $this->getTranslateableFactoryDataProviderFromExportData($ImportPageIdCollection);
			$translateableInformationFactory  = new tx_l10nmgr_models_translateable_translateableInformationFactory();
			$TranslateableInformation         = $translateableInformationFactory->create($translateableFactoryDataProvider);

				// Save the translation into the database
			$TranslationService = new tx_l10nmgr_service_importTranslation();
			$TranslationService->save($TranslateableInformation, $TranslationData);

			if ( $this->importData->countRemainingFiles() <= 0 ) {
				$this->importData->setImportIsCompletelyProcessed(true);
				$this->exportData->addWorkflowStat(tx_l10nmgr_models_exporter_workflowState::WORKFLOWSTATE_IMPORTED);
			}

			$this->removeProcessedFile($currentFile);
			
			$isRunning = true;
		}

		return $isRunning;
	}

	/**
	 * Create a dataProvider for the translateableInformationFactory from the current exportData
	 *
	 * @param ArrayObject $PageIdCollection
	 * @access protected
	 * @return tx_l10nmgr_models_translateable_typo3TranslateableFactoryDataProvider
	 */
	protected function getTranslateableFactoryDataProviderFromExportData($PageIdCollection) {
		$TranslatableDataProvider = new tx_l10nmgr_models_translateable_typo3TranslateableFactoryDataProvider (
			$this->exportData->getL10nConfigurationObject(),
			$PageIdCollection,
			$this->exportData->getTranslationLanguageObject(),
			$this->exportData->getSourceLanguageObject()
		);

		return $TranslatableDataProvider;
	}

	/**
	 * Returns the next file for the import.
	 *
	 * @access protected
	 * @return string $fileName
	 */
	protected function getNextFilename() {
		$remainingFilenames = $this->importData->getImportRemainingFilenames();
		
	}

	protected function saveProcessedFile($filename){
		$this->importData->removeFilesFromRemainingFiles(new ArrayObject($filename));
	}
	
	/**
	 *
	 */
	public static function performImportRun($importData){
		$importer 	= new tx_l10nmgr_models_importer_importer($importData);
		$res 		= $importer->run();


		if ($res) { $exportData->increaseNumberOfImportRuns(); }


		$importDataRepository = new tx_l10nmgr_models_importer_importDataRepository();
		$importDataRepository->save($importData);	
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/models/importer/class.tx_l10nmgr_models_importer_importer.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/models/importer/class.tx_l10nmgr_models_importer_importer.php']);
}

?>