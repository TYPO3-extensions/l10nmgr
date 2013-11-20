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
 * The exporter is responsible to export a set of pages as xml files
 *
 * class.tx_l10nmgr_domain_exporter_exporter.php
 *
 * @author	 Timo Schmidt <schmidt@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.tx_l10nmgr_domain_exporter_exporter.php $
 * @date 01.04.2009 - 15:11:03
 * @package	TYPO3
 * @subpackage	extensionkey
 * @access public
 */
class tx_l10nmgr_domain_exporter_exporter {

	/**
	 * @var tx_l10nmgr_domain_exporter_exportData
	 */
	protected $exportData;

	/**
	 * @var int
	 */
	protected $numberOfPagesPerChunck;

	/**
	 * @var boolean
	 */
	protected $isChunkProcessed = false;

	/**
	 * @var string
	 */
	protected $resultForChunk = '';

	/**
	 * @var boolean indicates if the exporter was running or not.
	 */
	protected $wasRunning = false;

	/**
	 * @var int number of fields in exportfile
	 */
	protected $currentNumberOfFields;

	/**
	 * @var bool check for TYPO3 cli mode
	 */
	protected $cliMode = FALSE;

	/**
	 * Constructor to create an instance of the exporter object
	 *
	 * @param tx_l10nmgr_domain_exporter_exportData $exportData
	 * @param int number of pages per chunk
	 * @param tx_l10nmgr_view_export_abstractExportView export view
	 */
	public function __construct(tx_l10nmgr_domain_exporter_exportData $exportData, $numberOfPagesPerChunk, tx_l10nmgr_view_export_abstractExportView $exportView) {
		$this->exportData            = $exportData;
		$this->numberOfPagesPerChunk = $numberOfPagesPerChunk;
		$this->exportView            = $exportView;
	}

	public function __destruct(){
		unset($this->resultForChunk);
		unset($this->exportData);
		unset($this->exportView);
	}

	/**
	 * Run
	 *
	 * @todo Refactoring is required!
	 *
	 * @access public
	 * @return bool true if not completely processed
	 *
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function run() {
		$factory = new tx_l10nmgr_domain_translateable_translateableInformationFactory();
		$this->wasRunning = true;

		if ($this->exportData->countRemainingPages() <= 0) {

			// last chunk
			if (count($this->exportData->getL10nConfigurationObject()->getIncludeArray()) > 0 && !$this->exportData->getIncludeListProcessedState()) {

				$l10ncfg = $this->exportData->getL10nConfigurationObject();
				if ( $l10ncfg instanceOf tx_mvc_ddd_typo3_abstractTCAObject ) {
					$includeArray = $l10ncfg->getIncludeArray();
	//!FIXME verify the missing WS-ID this is probably needed if an export of an WS is processed
					$tranlateableInformation = $factory->createFromIncludeList($this->exportData, $includeArray);

					if ($this->cliMode) {
						$tranlateableInformation->setSiteUrl(tx_l10nmgr_domain_tools_div::getBaseUrlForPageUid($this->exportData->getL10nConfigurationObject()->getPid()));
					}

					$this->processExport($tranlateableInformation);
					$this->currentNumberOfFields = count($includeArray);
					$this->exportData->setIncludeListProcessedState();
					$this->exportData->setExportIsCompletelyProcessed(true);

					return true;
				}
			}

			$this->exportData->setExportIsCompletelyProcessed(true);
			return false;

		} else {

			if ($this->exportData->getIsCompletelyUnprocessed()) {
				$this->exportData->addWorkflowState(tx_l10nmgr_domain_exporter_workflowState::WORKFLOWSTATE_EXPORTING);
			}

			$pagesForChunk               = $this->getNextPagesChunk();
//!FIXME verify the missing WS-ID this is probably needed if an export of an WS is processed
			$tranlateableInformation 	 = $factory->createFromExportDataAndPageIdCollection($this->exportData,$pagesForChunk);

			if ($this->cliMode) {
				$tranlateableInformation->setSiteUrl(tx_l10nmgr_domain_tools_div::getBaseUrlForPageUid($this->exportData->getL10nConfigurationObject()->getPid()));
			}

			$this->currentNumberOfFields = $tranlateableInformation->countFields();
			$this->processExport($tranlateableInformation, $pagesForChunk);

			$this->exportData->setExportIsCompletelyProcessed(false);
			return true;
		}
	}

	/**
	 *
	 * @param tx_l10nmgr_domain_translateable_translateableInformation $tranlateableInformation
	 * @param ArrayObject $pagesForChunk DEFAULT is null
	 *
	 * @return void
	 * @access public
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	protected function processExport(tx_l10nmgr_domain_translateable_translateableInformation $tranlateableInformation, $pagesForChunk = null) {
		$this->exportView->setTranslateableInformation($tranlateableInformation);

		$this->resultForChunk 		= $this->exportView->render();
		if ($pagesForChunk instanceOf ArrayObject) {
			$this->removeProcessedChunkPages($pagesForChunk);
		}

		$this->setIsChunkProcessed(true);

		if ($this->exportData->countRemainingPages() <= 0) {
 			$this->exportData->addWorkflowState(tx_l10nmgr_domain_exporter_workflowState::WORKFLOWSTATE_EXPORTED);
		}

		$this->exportData->increaseNumberOfExportRuns();
	}

	/**
	 * In each run the exporter processes one chunk. This method
	 * returns true if the current chunk is processed
	 *
	 * @author Timo Schmidt
	 * @return boolean
	 */
	protected function getIsChunkProcessed() {
		return $this->isChunkProcessed;
	}

	/**
	 * Returns the result for the current chunk.
	 *
	 * @author Timo Schmidt
	 * @return string
	 */
	public function getResultForChunk() {
		return $this->resultForChunk;
	}

	/**
	 * Counts the items which haven been exported in the current chunk.
	 *
	 * @param void
	 * @return int
	 */
	protected function countItemsForChunk(){
		$count = 0;
		if(!$this->wasRunning){
			throw new Exception('export has did not run export->run() has to be called first');
		}

		if ($this->currentNumberOfFields > 0 ) {
			$count = $this->currentNumberOfFields;
		}
		return $count;
	}

	/**
	 * This method can be used internally to mark the chunk as processed.
	 *
	 * @author Timo Schmidt
	 * @param boolean $isChunkProcessed
	 */
	protected function setIsChunkProcessed($isChunkProcessed) {
		$this->isChunkProcessed = $isChunkProcessed;
	}

	/**
	 * Retuns the internal exportDataObject
	 *
	 * @author Timo Schmidt
	 * @return tx_l10nmgr_domain_exporter_exportData
	 */
	public function getExportData() {
		if (!$this->getIsChunkProcessed()) {
			throw new LogicException('it makes no sence to read the export data from an unprocessed run');
		} else {
			return $this->exportData;
		}
	}

	/**
	 * Builds a chunck of pageIds from the set of remaining pages of an export
	 *
	 * @author Timo Schmidt
	 * @return ArrayObject
	 */
	protected function getNextPagesChunk() {
		$allPages 			= $this->exportData->getExportRemainingPages();
		$chunk				= new ArrayObject();

		$allPagesIterator 	= $allPages->getIterator();
		for($pagesInChunk = 0; $pagesInChunk < $this->getNumberOfPagesPerChunk(); $pagesInChunk++) {
			if ($allPagesIterator->valid()) {
				$chunk->append($allPagesIterator->current());
				$allPagesIterator->next();
			}
		}
		return $chunk;
	}

	/**
	 * Returns the configuration option for the number of pages per chunck.
	 *
	 * @author Timo Schmidt
	 * @return int
	 */
	protected function getNumberOfPagesPerChunk() {
		return $this->numberOfPagesPerChunk;
	}

	/**
	 * Method removes a set of pages from the remaining pages in the exportData
	 *
	 * @param ArrayObject $pageIdCollection
	 */
	protected function removeProcessedChunkPages($pageIdCollection) {
		$this->exportData->removePagesIdsFromRemainingPages($pageIdCollection);
	}

	/**
	 * This method performs on run of an export. It is polled via ajax to perform a complete export.
	 * It saves the export to a file an returns a boolean value if a nother run is needed.
	 *
	 * @param tx_l10nmgr_domain_exporter_exportData $exportData
	 * @param int $numberOfPagesPerChunk
	 * @param bool $cliMode
	 * @throws tx_mvc_exception_skipped
	 * @return boolean
	 */
	public static function performFileExportRun(tx_l10nmgr_domain_exporter_exportData $exportData, $numberOfPagesPerChunk = 5, $cliMode = FALSE) {
		$exportView = $exportData->getInitializedExportView();
		$exporter = new tx_l10nmgr_domain_exporter_exporter($exportData, $numberOfPagesPerChunk, $exportView);
		$exporter->cliMode = $cliMode;

		$exporterWasRunning = $exporter->run();
		$numberOfItemsInChunk = $exporter->countItemsForChunk();
		$exportFileRepository = new tx_l10nmgr_domain_exporter_exportFileRepository();

		if ($exporterWasRunning && $numberOfItemsInChunk > 0) {
			// add the number of items in the current chunk to the whole number of items
			$exportData->addNumberOfItems($numberOfItemsInChunk);

			// now we write the exporter result to a file
			$exportFile	= new tx_l10nmgr_domain_exporter_exportFile();
			$exportFile->setFilename($exportData->getCurrentFilename());

			$exportFile->setExportDataObject($exportData);
			$exportFile->setContent($exporter->getResultForChunk());
			$exportFile->setPid($exportData->getPid()); // store the export file record on the same page as the export data record (and its configuration record)
			$exportFile->write();

			$exportFileRepository->add($exportFile);
		}

		if ($exportData->getExportIsCompletelyProcessed()) {
			if( ($exportData->getNumberOfItems() > 0) || (count($exportData->getL10nConfigurationObject()->getIncludeArray()) > 0 )){
				$exportData->createZip();
				// postProcessingHook
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['l10nmgr']['exportPostProcessing'])) {
					foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['l10nmgr']['exportPostProcessing'] as $userFunc) {
						$params = array();
						t3lib_div::callUserFunction($userFunc, $params, $exporter);
					}
				}
			}else{
				throw new tx_mvc_exception_skipped('The export has no items therefore no xml files have been created an no zip. Please check your export configuration');
			}
		}

		return $exportData->getExportIsCompletelyProcessed();
	}
}

?>