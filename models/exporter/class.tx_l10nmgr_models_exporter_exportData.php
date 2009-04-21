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

require_once t3lib_extMgm::extPath('l10nmgr').'models/configuration/class.tx_l10nmgr_models_configuration_configuration.php';
require_once t3lib_extMgm::extPath('l10nmgr').'models/configuration/class.tx_l10nmgr_models_configuration_configurationRepository.php';


require_once t3lib_extMgm::extPath('l10nmgr').'models/language/class.tx_l10nmgr_models_language_language.php';
require_once t3lib_extMgm::extPath('l10nmgr').'models/language/class.tx_l10nmgr_models_language_languageRepository.php';



class tx_l10nmgr_models_exporter_exportData extends /* tx_mvc_ddd_abstractDbObject */ tx_mvc_ddd_typo3_abstractTCAObject {

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

		return $fields;
	}

	/**
	 * Get l10nConfiguration record
	 *
	 * @return tx_l10nmgr_models_configuration_Configuration
	 * @author Fabrizio Branca <fabrizio.branca@aoemedia.de>
	 * @since 2009-04-03
	 */
	public function getL10nConfigurationObject() {
		if (empty($this->row['l10ncfg_id'])) {
			throw new LogicException('No "l10ncfg_id" found!');
		}

		if (empty($this->row['l10nconfigurationobject'])) {
			$l10nconfigurationRepository = new tx_l10nmgr_models_configuration_configurationRepository();
			$this->row['l10nconfigurationobject'] = $l10nconfigurationRepository->findById($this->row['l10ncfg_id']);
		}
		return $this->row['l10nconfigurationobject'];
	}

	/**
	 * Method to set a configuration for the exportData
	 *
	 * @param tx_l10nmgr_models_configuration_configuration $configuration
	 */
	public function setL10NConfiguration(tx_l10nmgr_models_configuration_configuration $configuration){
		$this->row['l10nconfigurationobject'] = $configuration;
		$this->row['l10ncfg_id'] = $configuration->getUid();
	}

	/**
	 * Returns the remaining pages for the export
	 *
	 * @return ArrayObject
	 */
	public function getExportRemainingPages(){
		if($this->getProgress('export_remaining_pages')){
			//if there are no remaining pages configured, all pages of the configuration are remaining pages
			$res = $this->getL10nConfigurationObject()->getExportPageIdCollection();
			$this->setProgress('export_total_number_of_pages',$res->count());
		}else{
			$res = new ArrayObject($this->getProgress('export_remaining_pages'));
		}

		return $res;
	}
	

	/**
	 * Returns the procress of this export
	 *
	 * @return float
	 */
	public function getExportProgressPercentage(){
		$numberOfProcessedPages = $this->getExportTotalNumberOfPages()- $this->getExportRemainingPages()->count();
		
		return (100 / $this->getExportTotalNumberOfPages()) * $numberOfProcessedPages;
	}
	
	/**
	 * Returns the total number of pages which will be exported in this export run.
	 *
	 * @return int
	 */
	public function getExportTotalNumberOfPages(){
		if($this->getProgress('export_total_number_of_pages') == 0){
			$this->setProgress('export_total_number_of_pages', $this->getL10nConfigurationObject()->getExportPageIdCollection()->count());
		}
		
		return $this->getProgress('export_total_number_of_pages');
	}

	/**
	 * Method to set the number of pages that are relevant for this export
	 *
	 * @param int $numberOfPages
	 */
	protected function setExportTotalNumberOfPages($numberOfPages){
		$this->setProgress('export_total_number_of_pages',$numberOfPages);
	}
	
	/**
	 * Method to remove a set of pageIds from the remaining pages
	 *
	 * @param ArrayObject $pageIdCollection
	 */
	public function removePagesIdsFromRemainingPages($pageIdCollection){
		$remainingPagesLeft = array_diff($this->getRemainingPages()->getArrayCopy(),$pageIdCollection->getArrayCopy());

		$this->getRemainingPages()->exchangeArray($remainingPagesLeft);
		$this->setProgress('export_remaining_pages',$remainingPagesLeft);
	}

	/**
	 * Returns the number of remaining pages
	 *
	 * @return int
	 */
	public function countRemainingPages(){
		return $this->getRemainingPages()->count();
	}
	
	/**
	 * Mehtod to mark the export as completely processed
	 *
	 * @param boolan $boolean
	 */
	public function setExportIsCompletelyProcessed($boolean){
		$this->setProgress('export_is_completely_processed', $boolean);
	}

	/**
	 * Method to determine if the export is completly processed.
	 *
	 * @return boolean
	 */
	public function getExportIsCompletelyProcessed(){
		return $this->getProgress('export_is_completely_processed');
	}

	/**
	 * This method can be used to determine, that the export has not been started
	 *
	 * @return boolean
	 */
	public function getIsCompletelyUnprocessed(){
		return ($this->countRemainingPages() == $this->getTotalNumberOfPages());
	}
	
	protected function setProgress($key,$value){
		if(!empty($this->row['progress'])){
			$progress = unserialize($this->row['progress']);
		}else{
			$progress = array();
		}
		$progress[$key] = $value;
		$this->row['progress'] = serialize($progress);
	}
	
	protected function getProgress($key){
		if(!empty($this->row['progress'])){
			$progress = unserialize($this->row['progress']);
		}else{
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
			tx_mvc_validator_factory::getInstanceValidator()->setClassOrInterface('tx_l10nmgr_models_exporter_workflowStateRepository')->isValid($statesRepository);

			$this->row['statescollection'] = $statesRepository->findByexportdata_id($this->row['uid']);
		}
		return $this->row['statescollection'];
	}

	/**
	 * Get current state
	 *
	 * @param void
	 * @return tx_l10nmgr_models_exporter_workflowState
	 */
	public function getCurrentState() {
		$statesCollection = $this->getStatesCollection();
		$currentState = NULL;

		// loop through all states to get tha newest one
		foreach ($statesCollection as $state) { /* @var $state tx_l10nmgr_models_exporter_workflowState */
			if (empty($currentState['tstamp']) || ($state['tstamp'] > $currentState['tstamp'])) {
				$currentState = $state;
			}
		}

		return $currentState;
	}

	/**
	 * Get collection of tx_l10nmgr_exportFile objects
	 *
	 * @return ArrayObject Collection of tx_l10nmgr_exportFile objects
	 * @author Fabrizio Branca <fabrizio.branca@aoemedia.de>
	 * @since 2009-04-03
	 */
	public function getFiles() {
		throw new Exception('Not implemented yet');
	}


	/**
	 * Get the source language object
	 *
	 * @param void
	 * @return tx_l10nmgr_models_language_language|NULL
	 * @author Fabrizio Branca <fabrizio.branca@aoemedia.de>
	 * @since 2009-04-06
	 */
	public function getSourceLanguageObject() {
		if (!empty($this->row['source_lang'])) {
			if (empty($this->row['sourcelanguageobject'])) {
				$languageRepository = new tx_l10nmgr_models_language_LanguageRepository();
				$this->row['sourcelanguageobject'] = $languageRepository->findById($this->row['source_lang']);
			}
			return $this->row['sourcelanguageobject'];
		}
	}

	/**
	 * Get the translation language object
	 *
	 * @param void
	 * @return tx_l10nmgr_models_language_language|NULL
	 * @author Fabrizio Branca <fabrizio.branca@aoemedia.de>
	 * @since 2009-04-06
	 */
	public function getTranslationLanguageObject() {
		if (!empty($this->row['translation_lang'])) {
			if (empty($this->row['translationlanguageobject'])) {
				$languageRepository = new tx_l10nmgr_models_language_LanguageRepository();
				$this->row['translationlanguageobject'] = $languageRepository->findById($this->row['translation_lang']);
			}
			return $this->row['translationlanguageobject'];
		}
	}

	/**
	 * Method to attach a translationLanguage to the exportData
	 *
	 * @param tx_l10nmgr_models_language_language $translationLanguage
	 */
	public function setTranslationLanguageObject($translationLanguage){
		$this->row['translationlanguageobject'] = $translationLanguage;
		$this->row['translation_lang'] = $translationLanguage->getUid();
	}

	/**
	 * Method to set a source language id
	 *
	 * @param int $id
	 */
	public function setSourceLanguageId($id){
		$this->row['source_lang'] = $id;
	}

	public function setResult($result){
		$this->row['result'] = $result;
	}

	public function getResult(){
		return $this->row['result'];
	}

}

?>