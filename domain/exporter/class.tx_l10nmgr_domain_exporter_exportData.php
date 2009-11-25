<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 Fabrizio Branca (fabrizio.branca@aoemedia.de)
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

require_once t3lib_extMgm::extPath('l10nmgr').'interface/interface.tx_l10nmgr_interface_progressable.php';

/**
 * An exportData object represents one export. Each export can have multiple files.
 * The exportData object is used by the exporter, to process the export. It
 * has internal methods to save the state of the whole export.
 * The exporter uses these states during the processing.
 *
 * class.tx_l10nmgr_domain_exporter_exportData.php
 *
 * @author	Timo Schmidt <schmidt@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.tx_l10nmgr_domain_importer_importData.php $
 * @date 24.04.2009 - 13:24:06
 * @see tx_mvc_ddd_typo3_abstractTCAObject
 * @category database
 * @package	TYPO3
 * @subpackage	l10nmgr
 * @access public
 */

class tx_l10nmgr_domain_exporter_exportData extends tx_mvc_ddd_typo3_abstractTCAObject implements tx_l10nmgr_interface_progressable{

	/**
	 * @var string hold the absolute path to the folder where normal files should be exported to
	 */
	protected $absoluteFilePath;

	/**
	 * @var string holds the absolut path to the folder were zip files should be stored
	 */
	protected $absoluteZipPath;

	/**
	 * @var string
	 */
	protected $relativeFilePath;

	/**
	 * @var string
	 */
	protected $relativeZipPath;

	/**
	 * The diffrent exportFormats are realized with diffrent views which
	 * will be rendered at exportTime. This variable holds the exportView which
	 * is responsible to render the exportFile for a given format.
	 *
	 * @var tx_l10nmgr_view_export_abstractExportView
	 */
	protected $initializedView;

	/**
	 *
	 */
	public function __construct($row = array()){
		parent::__construct($row);
		$fileExportPath = tx_mvc_common_typo3::getTCAConfigValue('uploadfolder', tx_l10nmgr_domain_exporter_exportFile::getTableName(), 'filename');
		$zipExportPath	=  tx_mvc_common_typo3::getTCAConfigValue('uploadfolder', tx_l10nmgr_domain_exporter_exportData::getTableName(), 'filename');

		$this->setRelativeFilePath($fileExportPath);
		$this->setRelativeZipPath($zipExportPath);
	}

	/**
	 * Unset referenced objects and data.
	 *
	 */
	public function __destruct(){

		unset($this->initializedView);
		unset($this->relativeZipPath);
		unset($this->relativeFilePath);
		unset($this->absoluteFilePath);
		unset($this->absoluteZipPath);
		unset($this->row);
	}

	/**
	 * Method to set the relative filePath for this export.
	 *
	 * @param string
	 */
	protected function setRelativeFilePath($path){
		$this->relativeFilePath = $path;
		$this->setAbsoluteFilePath(t3lib_div::getFileAbsFileName($path));
	}

	/**
	 * Returns the relative file path.
	 *
	 * @return string
	 */
	public function getRelativeFilePath(){
		return $this->relativeFilePath;
	}

	/**
	 * Method to set the relative filepath for the zipfile of an export.
	 *
	 * @param string
	 */
	protected function setRelativeZipPath($path){
		$this->relativeZipPath = $path;
		$this->setAbsoluteZipPath(t3lib_div::getFileAbsFileName($path));
	}

	/**
	 * Returns the relative file path of the zip file.
	 *
	 * @return string
	 */
	public function getRelativeZipPath(){
		return $this->relativeZipPath;
	}

	/**
	 * Method to set the absoulte filename.
	 *
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 * @param string
	 */
	protected function setAbsoluteFilePath($path){
		$this->absoluteFilePath = $path;
	}

	/**
	 * Returns the configured absolute file name.
	 *
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 * @return string
	 */
	protected function getAbsoluteFilePath(){
		return $this->absoluteFilePath;
	}

	/**
	 * Method to set an absolute path to the folder where the zip file should
	 * be stored.
	 *
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 * @param string path to exported zip files
	 */
	protected function setAbsoluteZipPath($path){
		$this->absoluteZipPath = $path;
	}

	/**
	 * Returns the absoulte path to the zip file of the export.
	 *
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 * @return string returns the configured absolute path to the zip file
	 */
	protected function getAbsoluteZipPath(){
		return $this->absoluteZipPath;
	}

	/**
	 * This method can be used to get the download url of this file
	 *
	 * @return string url to download the file
	 */
	public function getDownloadUrl(){
		$site 	= t3lib_div::getIndpEnv('TYPO3_SITE_URL');
		$url 	= $site.''.$this->getRelativeZipPath().'/'.$this->getFilename();
		return $url;
	}

	/**
	 * Initialize the database object with
	 * the table name of current object
	 *
	 * @access public
	 * @return string
	 */
	public static function getTableName() {
		return 'tx_l10nmgr_exportdata';
	}

	/**
	 * Overwrite getDatabaseFieldNames to remove the "virtual files" that should not be stored in the database
	 *
	 * @return array array of field names to store in the database
	 * @see ddd/tx_mvc_ddd_abstractDbObject#getDatabaseFieldNames()
	 */
	public function getDatabaseFieldNames() {
		$fields = parent::getDatabaseFieldNames();

		// remove some "virtual fields"
		$key = array_search('l10nconfigurationobject', $fields);
		if ($key !== false) {
			unset($fields[$key]);
		}

		$key = array_search('sourcelanguageobject', $fields);
		if ($key !== false) {
			unset($fields[$key]);
		}

		$key = array_search('translationlanguageobject', $fields);
		if ($key !== false) {
			unset($fields[$key]);
		}

		$key = array_search('exportfiles', $fields);
		if ($key !== false) {
			unset($fields[$key]);
		}

		return $fields;
	}

	/**
	 * Get l10nConfiguration record
	 *
	 * @return tx_l10nmgr_domain_configuration_Configuration
	 * @author Fabrizio Branca <fabrizio.branca@aoemedia.de>
	 * @since 2009-04-03
	 */
	public function getL10nConfigurationObject() {
		if (empty($this->row['l10ncfg_id'])) {
			throw new LogicException('No "l10ncfg_id" found!');
		}

		if (empty($this->row['l10nconfigurationobject'])) {
			$l10nconfigurationRepository = new tx_l10nmgr_domain_configuration_configurationRepository();
			$this->row['l10nconfigurationobject'] = $l10nconfigurationRepository->findById($this->row['l10ncfg_id']);
		}
		return $this->row['l10nconfigurationobject'];
	}

	/**
	 * Method to set a configuration for the exportData
	 *
	 * @param tx_l10nmgr_domain_configuration_configuration $configuration
	 */
	public function setL10NConfiguration(tx_l10nmgr_domain_configuration_configuration $configuration) {
		$this->row['l10nconfigurationobject'] = $configuration;
		$this->row['l10ncfg_id'] = $configuration->getUid();
	}

	/**
	 * Returns the remaining pages for the export
	 *
	 * @return ArrayObject
	 */
	public function getExportRemainingPages() {
		if (!is_array($this->getProgress('export_remaining_pages'))) {
			//if there are no remaining pages configured, all pages of the configuration are remaining pages
			$res = $this->getL10nConfigurationObject()->getExportPageIdCollection();
			$this->setProgress('export_total_number_of_pages', $res->count());
			$this->setProgress('export_remaining_pages', $res);
		}
		return new ArrayObject($this->getProgress('export_remaining_pages'));
	}

	/**
	 * This method is used to attach a warning message to the export progress.
	 *
	 * @param string $type
	 * @param string $message
	 */
	public function addMessage($type,$message){
		$this->row['messages'] = $this->row['messages'] .$type. ' in File '.$this->getCurrentFilename().': \n '.(string) $message;
	}

	/**
	 * Returns the procress of this export
	 *
	 * @return float
	 */
	public function getProgressPercentage() {
		$numberOfProcessedPages = $this->getExportTotalNumberOfPages() - $this->getExportRemainingPages()->count();

		return (100 / $this->getExportTotalNumberOfPages()) * $numberOfProcessedPages;
	}

	/**
	 * Returns the output which should be displayed in the progress bar.
	 *
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 * @return string
	 */
	public function getProgressOutput(){
		return round($this->getProgressPercentage()).' %';
	}

	/**
	 * Returns the total number of pages which will be exported in this export run.
	 *
	 * @return int
	 */
	public function getExportTotalNumberOfPages() {
		if($this->getProgress('export_total_number_of_pages') == 0) {
			$this->setProgress('export_total_number_of_pages', $this->getL10nConfigurationObject()->getExportPageIdCollection()->count());
		}

		return $this->getProgress('export_total_number_of_pages');
	}

	/**
	 * Method to indicate that the include list of the export has been processed.
	 *
	 * @param boolean $isProcessed DEFAULT is true
	 *
	 * @access public
	 * @return void
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function setIncludeListProcessedState($isProcessed = true){
		$this->setProgress('includeListProcessedState', $isProcessed);
	}

	/**
	 * Returns the processing state of the include list of this export.
	 *
	 * @access public
	 * @return boolean
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function getIncludeListProcessedState(){
		return $this->getProgress('includeListProcessedState');
	}

	/**
	 * Method to set the number of pages that are relevant for this export
	 *
	 * @param int $numberOfPages
	 */
	protected function setExportTotalNumberOfPages($numberOfPages) {
		$this->setProgress('export_total_number_of_pages',$numberOfPages);
	}

	/**
	 * Method to remove a set of pageIds from the remaining pages
	 *
	 * @param ArrayObject $pageIdCollection
	 */
	public function removePagesIdsFromRemainingPages($pageIdCollection) {
		$remainingPagesLeft = array_diff($this->getExportRemainingPages()->getArrayCopy(),$pageIdCollection->getArrayCopy());

		$this->getExportRemainingPages()->exchangeArray($remainingPagesLeft);
		$this->setProgress('export_remaining_pages',$remainingPagesLeft);
	}

	/**
	 * Returns the number of remaining pages
	 *
	 * @return int
	 */
	public function countRemainingPages() {
		return $this->getExportRemainingPages()->count();
	}

	/**
	 * Mehtod to mark the export as completely processed
	 *
	 * @param boolan $boolean
	 */
	public function setExportIsCompletelyProcessed($boolean) {
		$this->setProgress('export_is_completely_processed', $boolean);
	}

	/**
	 * Method to determine if the export is completly processed.
	 *
	 * @return boolean
	 */
	public function getExportIsCompletelyProcessed() {
		return $this->getProgress('export_is_completely_processed');
	}

	/**
	 * This method can be used to determine, that the export has not been started
	 *
	 * @return boolean
	 */
	public function getIsCompletelyUnprocessed() {
		return ($this->countRemainingPages() == $this->getExportTotalNumberOfPages());
	}

	/**
	 * The idea of the progress property is, to save state informations in on serializeable
	 * field structure in the database. It can be used internally to save state information.
	 *
	 * @param string key
	 * @param mixed value
	 * @return void
	 */
	protected function setProgress($key, $value) {
		if (!empty($this->row['progress'])) {
			$progress = unserialize($this->row['progress']);
		} else {
			$progress = array();
		}
		$progress[$key] = $value;
		$this->row['progress'] = serialize($progress);
	}

	/**
	 * Return the progress which was registered for a given value.
	 *
	 * @param string key
	 * @return mixed value
	 */
	protected function getProgress($key) {
		if (!empty($this->row['progress'])) {
			$progress = unserialize($this->row['progress']);
		} else {
			$progress = array();
		}
		return $progress[$key];
	}

	/**
	 * Get collection of tx_l10nmgr_exportState objects
	 *
	 * @return ArrayObject Collection of tx_l10nmgr_exportState objects
	 * @author Fabrizio Branca <fabrizio.branca@aoemedia.de>
	 * @since 2009-04-03
	 */
	public function getStatesCollection() {
		if (empty($this->row['uid'])) {
			throw new LogicException('No "uid" found!');
		}

		if (empty($this->row['statescollection'])) {

			// load exportStateRepository from based on configuration
			$statesRepositoryClass = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['l10nmgr']['classes']['exportData_workflowStateRepository'];
			tx_mvc_validator_factory::getNotEmptyValidator()->setMessage('No "repositoryClass" found!')->isValid($statesRepositoryClass);
			$statesRepository = t3lib_div::getUserObj($statesRepositoryClass);
			tx_mvc_validator_factory::getInstanceValidator()->setClassOrInterface('tx_l10nmgr_domain_exporter_workflowStateRepository')->isValid($statesRepository);

			$this->row['statescollection'] = $statesRepository->findByExportdDataNewestFirst($this->row['uid']);
		}
		return $this->row['statescollection'];
	}

	/**
	 * Get current state
	 *
	 * @param void
	 * @return tx_l10nmgr_domain_exporter_workflowState
	 */
	public function getCurrentState() {
		$statesCollection = $this->getStatesCollection();
		$currentState = NULL;

		// loop through all states to get tha newest one
		foreach ($statesCollection as $state) { /* @var $state tx_l10nmgr_domain_exporter_workflowState */
			if (empty($currentState['tstamp']) || ($state['tstamp'] > $currentState['tstamp'])) {
				$currentState = $state;
			}
		}

		return $currentState;
	}

	/**
	 * Creates a workflowstate for this exportData object
	 *
	 * @param string workflowstate
	 */
	public function addWorkflowState($stateName) {

		sleep(1); // this is a workaround to make sure that the new workflow state gets a timestamp that is younger than the previous one

		$workflowState = new tx_l10nmgr_domain_exporter_workflowState();
		$workflowState->setExportdata_id($this->getUid());
		$workflowState->setState($stateName);
		$workflowState->setPid($this->getPid());

		$workflowRepository = new tx_l10nmgr_domain_exporter_workflowStateRepository();
		$workflowRepository->add($workflowState);
	}

	/**
	 * Increases the number of exportruns for this exportData object.
	 *
	 * @param void
	 * @return void
	 */
	public function increaseNumberOfExportRuns(){
		$numRuns = $this->getNumberOfExportRuns();
		$numRuns++;
		$this->setNumberOfExportRuns($numRuns);
	}

	/**
	 * Returns the number of runs which have been performed during the export yet.
	 *
	 * @return int
	 */
	public function getNumberOfExportRuns(){
		return $this->getProgress('export_number_of_runs');
	}

	/**
	 * Method to save the number of runs during the export
	 *
	 * @param int $value
	 */
	protected function setNumberOfExportRuns($value){
		$this->setProgress('export_number_of_runs',$value);
	}

	/**
	 * Adds a number of processed items to the export data
	 * used to determine if any data has been exported
	 *
	 * @param int
	 * @return void
	 */
	public function addNumberOfItems($itemsInChunk){
		$currentItemsInChunk = $this->getNumberOfItems();
		$currentItemsInChunk += $itemsInChunk;
		$this->setProgress('NumProcessedItems',$currentItemsInChunk);
	}

	/**
	 * Returns the overall number of all processed items.
	 *
	 * @param void
	 * @return integer
	 */
	public function getNumberOfItems(){
		return $this->getProgress('NumProcessedItems');
	}

	/**
	 * Get the source language object
	 *
	 * @param void
	 * @return tx_l10nmgr_domain_language_language|NULL
	 * @author Fabrizio Branca <fabrizio.branca@aoemedia.de>
	 * @since 2009-04-06
	 */
	public function getSourceLanguageObject() {
		if (t3lib_div::intval_positive($this->row['source_lang']) > 0) {
			if (! $this->row['sourcelanguageobject'] instanceOf tx_l10nmgr_domain_language_language) {
				$languageRepository = new tx_l10nmgr_domain_language_languageRepository();
				$this->row['sourcelanguageobject'] = $languageRepository->findById($this->row['source_lang']);
			}
			return $this->row['sourcelanguageobject'];
		}
	}

	/**
	 * Get the translation language object
	 *
	 * @param void
	 * @return tx_l10nmgr_domain_language_language|NULL
	 * @author Fabrizio Branca <fabrizio.branca@aoemedia.de>
	 * @since 2009-04-06
	 */
	public function getTranslationLanguageObject() {
		if (!empty($this->row['translation_lang'])) {
			if (empty($this->row['translationlanguageobject'])) {
				$languageRepository = new tx_l10nmgr_domain_language_languageRepository();
				$this->row['translationlanguageobject'] = $languageRepository->findById($this->row['translation_lang']);
			}
			return $this->row['translationlanguageobject'];
		}
	}

	/**
	 * Method to attach a translationLanguage to the exportData
	 *
	 * @param tx_l10nmgr_domain_language_language $translationLanguage
	 */
	public function setTranslationLanguageObject($translationLanguage) {
		$this->row['translationlanguageobject'] = $translationLanguage;
		$this->row['translation_lang'] = $translationLanguage->getUid();
	}

	/**
	 * Get export files
	 *
	 * @param void
	 * @return ArrayObject of tx_l10nmgr_domain_exporter_exportFile objects
	 */
	public function getExportFiles() {
		if (empty($this->row['exportfiles'])) {
			$exportFileRepository = new tx_l10nmgr_domain_exporter_exportFileRepository();
			$this->row['exportfiles'] = $exportFileRepository->findByExportdata_id($this->getUid());
		}
		return $this->row['exportfiles'];
	}

	/**
	 * Method to set a source language id
	 *
	 * @param int $id
	 */
	public function setSourceLanguageId($id) {
		$this->row['source_lang'] = $id;
	}

	/**
	 * Create one zip files including all export files
	 *
	 * @param string filename
	 * @return bool true if the zip was created successfully
	 */
	public function createZip() {
		$fileName = $this->getZipFilename();
		if (class_exists('ZipArchive')) {
			$absoluteExportZipPath 	= $this->getAbsoluteZipPath();
			$absoluteExportFilePath	= $this->getAbsoluteFilePath();

			$fullPath	= $absoluteExportZipPath.'/'.$fileName;
			$zipper 	= new ZipArchive();

			if(is_file($fullPath)) {
				unlink($fullPath);
			}
			$res = $zipper->open($fullPath, ZipArchive::CREATE);
			if ($res !== true) {
				throw new Exception(sprintf('Error while creating zipfile (Error code: "%s")', $res));
			}

			if (TYPO3_DLOG) t3lib_div::devLog(sprintf('Creating new zip file "%s"', $fullPath), 'l10nmgr', 1);

			$fileCounter = 0;

			foreach ($this->getExportFiles() as $exportFile) { /* @var $exportFile tx_l10nmgr_domain_exporter_exportFile */
				$sourceFileName = $exportFile->getFilename();
				$sourceFile = t3lib_div::getFileAbsFileName($absoluteExportFilePath . '/' . $sourceFileName);
				if (!is_file($sourceFile)) {
					throw new Exception(sprintf('File "%s" not found (for adding to zip archive)', $sourceFile));
				}

				// due to a file descriptor limit we close and reopen the zip file after 100 added files
				if ($fileCounter++ > 100) {
					$fileCounter = 0;
					$res = $zipper->close();
					if ($res !== true) {
						throw new Exception('Error while closing zip file');
					}
					$res = $zipper->open($fullPath, ZipArchive::CREATE);
					if ($res !== true) {
						throw new Exception(sprintf('Error while reopen zipfile (Error code: "%s")', $res));
					}
				}

				$res = $zipper->addFile($sourceFile, $sourceFileName);
				if ($res !== true) {
					throw new Exception(sprintf('Error while adding file "%s" to archive', $sourceFileName));
				}
			}

			$res = $zipper->close();
			if ($res !== true) {
				throw new Exception('Error while closing zip file');
			}

			t3lib_div::fixPermissions($fullPath);

			if (!is_file($fullPath)) {
				throw new Exception(sprintf('Zip file "%s" not found', $fullPath));
			}

			// update exportdata record
			$this->setFilename($fileName);
			return true;
		} else {
			if (TYPO3_DLOG) t3lib_div::devLog('No zipfile created because class "ZipArchive" is not available', 'l10nmgr', 3);
			return false;
		}
	}

	/**
	 * Creats an instance of a configured xml export view
	 *
	 * @param void
	 * @return tx_l10nmgr_view_export_abstractExportView
	 */
	public function getInitializedExportView() {

		if(!$this->initializedView instanceof tx_l10nmgr_view_export_abstractExportView){
			switch ($this->getExport_type()) {
				case 'xml' : {
					$viewClass = new tx_l10nmgr_view_export_exporttypes_CATXML();
					$viewClass->setSkipXMLCheck($this->getNoxmlcheck());
					$viewClass->setUseUTF8Mode($this->getCheckutf8());
				} break;

				case 'xls' : {
					$viewClass = new tx_l10nmgr_view_export_exporttypes_excelXML();
				} break;

				default: {
					throw new LogicException('ExportFormat is invalid (must be "xml" or "xls")!');
				}
			}

			$viewClass->setForcedSourceLanguage($this->getSourceLanguageObject());
			$viewClass->setL10NConfiguration($this->getL10nConfigurationObject());
			$viewClass->setModeOnlyChanged($this->getOnlychangedcontent());
			$viewClass->setModeNoHidden($this->getNohidden());
			$viewClass->setTargetLanguageId($this->getTranslationLanguageObject()->getUid());
			$this->initializedView = $viewClass;
		}

		return $this->initializedView;
	}

	/**
	 * Method to determine the filename for the currenly exported file.
	 *
	 * @param void
	 * @return string filename
	 */
	public function getCurrentFilename(){
		$prefix = $this->getL10nConfigurationObject()->getFilenameprefix();
		return $this->getInitializedExportView()->getFilename($prefix,$this->getNumberOfExportRuns());
	}

	/**
	 * Generates the name of the zipfile for this export process.
	 *
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 * @return string
	 */
	protected function getZipFilename(){
		$prefix = $this->getL10nConfigurationObject()->getFilenameprefix();
		return $this->getInitializedExportView()->getFilename($prefix) . '.zip';
	}

	/**
	 * Gets the language title
	 *
	 * @param tx_l10nmgr_domain_language_language|null language object, if empty the label from configuration will be read
	 * @return string language title
	 */
	protected function getLanguageLabel(tx_l10nmgr_domain_language_language $lang=NULL) {
		if (is_null($lang)) {
			// default language
			$tsConf = t3lib_BEfunc::getModTSconfig($this->getPid(), 'mod.SHARED.');
			$languageLabel = $tsConf['properties']['defaultLanguageLabel'];

			// TODO: extension manager configuration if empty
		} else {
			$languageLabel = $lang->getTitle();
		}

		return $languageLabel;
	}

	/**
	 * Get the title of the source language
	 *
	 * @return string
	 */
	public function getSourceLanguageTitle() {
		return $this->getLanguageLabel($this->getSourceLanguageObject());
	}

	/**
	 * Get the title of the translation language
	 *
	 * @return string
	 */
	public function getTranslationLanguageTitle() {
		return $this->getLanguageLabel($this->getTranslationLanguageObject());
	}

	/**
	 * Get the iso code
	 *
	 * @param tx_l10nmgr_domain_language_language|null if empty the iso code from configuration will be read
	 * @return string iso code
	 */
	protected function getIsoCode(tx_l10nmgr_domain_language_language $lang=NULL) {
		if (is_null($lang)) {
			// default language
			$tsConf = t3lib_BEfunc::getModTSconfig($this->getPid(), 'mod.SHARED.');
			$isoCode = $tsConf['properties']['defaultLanguageISOCode'];

			// TODO: extension manager configuration if empty
		} else {
			$isoCode = $lang->getStaticLanguage()->getLg_collate_locale();
		}

		return $isoCode;
	}

	/**
	 * Get the iso code for the translation language
	 *
	 * @return string iso code
	 */
	public function getTranslationIsoCode() {
		return $this->getIsoCode($this->getTranslationLanguageObject());
	}

	/**
	 * Get the iso code for the source language
	 *
	 * @return string iso code
	 */
	public function getSourceIsoCode() {
		return $this->getIsoCode($this->getSourceLanguageObject());
	}
}
?>