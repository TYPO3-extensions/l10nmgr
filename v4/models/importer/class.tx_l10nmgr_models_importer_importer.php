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
require_once t3lib_extMgm::extPath('l10nmgr') . 'domain/class.tx_l10nmgr_domain_translationFactory.php';

require_once t3lib_extMgm::extPath('l10nmgr') . 'models/translateable/class.tx_l10nmgr_models_translateable_typo3TranslateableFactoryDataProvider.php';
require_once t3lib_extMgm::extPath('l10nmgr') . 'models/translateable/class.tx_l10nmgr_models_translateable_translateableInformationFactory.php';


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
	}

	/**
  	 * Initializes the internalExportdata property from the translationData
  	 *
  	 * @param tx_l10nmgr_domain_translation_data $Translationdata
  	 * @return tx_l10nmgr_models_exporter_exportData $exportData
	 */
	protected function getExportDataFromTranslationData($Translationdata){
		$exportDataUid			= $Translationdata->getExportDataRecordUid();
		tx_mvc_validator_factory::getIntValidator()->isValid($exportDataUid,true);

		$exportDataRepository 	= new tx_l10nmgr_models_exporter_exportDataRepository();
		$exportData		= $exportDataRepository->findById($exportDataUid);

		return $exportData;
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

			// determine the next file to import
			$currentFile = $this->getNextFilename();

			$TranslationFactory = new tx_l10nmgr_domain_translationFactory();
			$TranslationData    = $TranslationFactory->create($currentFile);
			$exportData 		= $this->getExportDataFromTranslationData($TranslationData);

			#check pre requirements
			$targetLanguageFromExport = $exportData->getTranslationLanguageObject()->getUid();
			$targetLanguageFromImport =	$TranslationData->getSysLanguageUid();

			if($targetLanguageFromExport != $targetLanguageFromImport){
				throw new tx_mvc_exception_invalidArgument('The import ('.$targetLanguageFromImport.') has a diffrent target language the the export ('.$targetLanguageFromExport.') it results from');
			}

			if ( $this->importData->getImportIsCompletelyUnprocessed() ) {
				/**
				 * @internal  workflowStates depend on the exportData object therefore we have to use it to mark the import as started
				 */
				$exportData->addWorkflowState(tx_l10nmgr_models_exporter_workflowState::WORKFLOWSTATE_IMPORTING);
			}

				// get collection of pageIds to create a translateableInformation for the relevantPages from the imported file
			$ImportPageIdCollection	= $TranslationData->getPageIdCollection();

				// create a dataProvider based on the exportData and the relevantPageIds of the importFile
			$translateableFactoryDataProvider = $this->getTranslateableFactoryDataProviderFromPageIdCollectionAndExportData($ImportPageIdCollection,$exportData);
			$translateableInformationFactory  = new tx_l10nmgr_models_translateable_translateableInformationFactory();
			$TranslateableInformation         = $translateableInformationFactory->create($translateableFactoryDataProvider);

				// Save the translation into the database
			$TranslationService = new tx_l10nmgr_service_importTranslation();
			$TranslationService->save($TranslateableInformation, $TranslationData);

			if ( $this->importData->countRemainingImportFilenames() <= 0 ) {
				$this->importData->setImportIsCompletelyProcessed(true);
				$exportData->addWorkflowStat(tx_l10nmgr_models_exporter_workflowState::WORKFLOWSTATE_IMPORTED);
			}

			$this->removeProcessedFilename($currentFile);

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
	protected function getTranslateableFactoryDataProviderFromPageIdCollectionAndExportData($PageIdCollection, $exportData) {
		$TranslatableDataProvider = new tx_l10nmgr_models_translateable_typo3TranslateableFactoryDataProvider (
			$exportData,
			$PageIdCollection
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
		$it = $remainingFilenames->getIterator();

		return $it->current();
	}

	/**
	 * This method introduces the importData object to remove a file from the remaining filenames
	 * that need to be processed.
	 *
	 * @param string
	 * @return void
	 */
	protected function removeProcessedFilename($filename){
		$this->importData->removeFilenamesFromRemainingFilenames(new ArrayObject(array($filename)));
	}

	/**
	 * This static method is used to process one importChunk of an importData object.
	 *
	 * @param tx_l10nmgr_models_importer_importData $importData
	 */
	public static function performImportRun($importData){
		$importer 	= new tx_l10nmgr_models_importer_importer($importData);
		$res 		= $importer->run();

		if ($res) { $importData->increaseNumberOfImportRuns(); }

		$importDataRepository = new tx_l10nmgr_models_importer_importDataRepository();
		$importDataRepository->save($importData);

		return $importData->getImportIsCompletelyProcessed();
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/models/importer/class.tx_l10nmgr_models_importer_importer.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/models/importer/class.tx_l10nmgr_models_importer_importer.php']);
}

?>