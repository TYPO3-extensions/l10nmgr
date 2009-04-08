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

require_once t3lib_extMgm::extPath('l10nmgr').'models/exporter/class.tx_l10nmgr_models_exporter_exportState.php';
require_once t3lib_extMgm::extPath('l10nmgr').'models/exporter/class.tx_l10nmgr_models_exporter_exportStateRepository.php';

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
	 * Get l10nConfiguration record
	 *
	 * @return tx_l10nmgr_models_configuration_Configuration
	 * @author Fabrizio Branca <fabrizio.branca@aoemedia.de>
	 * @since 2009-04-03
	 */
	public function getL10nConfiguration() {
		if (empty($this->row['l10ncfg_id'])) {
			throw new LogicException('No "l10ncfg_id" found!');
		}

		if (empty($this->row['l10nconfiguration'])) {
			$l10nconfigurationRepository = new tx_l10nmgr_models_configuration_configurationRepository();
			$this->row['l10nconfiguration'] = $l10nconfigurationRepository->findById($this->row['l10ncfg_id']);
		}
		return $this->row['l10nconfiguration'];
	}

	/**
	 * Returns the remaining pages for the export
	 *
	 * @return ArrayObject
	 */
	public function getRemainingPages(){
		if(empty($this->row['remaining_pages'])){
			//if there are no remaining pages configured, all pages of the configuration are remaining pages
			$res = $this->getL10nConfiguration()->getExportPageIdCollection();
		}else{
			$res = new ArrayObject(unserialize($this->row['remaining_pages']));
		}
		
		if($res->count() == 0){
			$this->setIsCompletlyProcessed(true);
		}
			
		return $res;
	}
	
	/**
	 * Method to remove a set of pageIds from the remaining pages
	 *
	 * @param ArrayObject $pageIdCollection
	 */
	public function removePagesIdsFromRemainingPages($pageIdCollection){	
		$remainingPagesLeft = array_diff($this->getRemainingPages()->getArrayCopy(),$pageIdCollection->getArrayCopy());
		
		if(empty($remainingPagesLeft)){
			$this->setIsCompletelyProcessed(true);
		}
		$this->getRemainingPages()->exchangeArray($remainingPagesLeft); 	
		$this->row['remaining_pages'] = serialize($remainingPagesLeft);
	}
	
	/**
	 * Mehtod to mark the export as completely processed
	 *
	 * @param boolan $boolean
	 */
	protected function setIsCompletelyProcessed($boolean){
		$this->row['is_compeletly_processed'] = $boolean;
	}
	
	/**
	 * Method to determine if the export is completly processed.
	 * 
	 * @return boolean
	 */
	public function getIsCompletelyProcessed(){
		return $this->row['is_compeletly_processed'];
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
			$statesRepositoryClass = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['l10nmgr']['classes']['exportData_exportStateRepository'];
			tx_mvc_validator_factory::getNotEmptyValidator()->setMessage('No "repositoryClass" found!')->isValid($statesRepositoryClass);
			$statesRepository = t3lib_div::getUserObj($statesRepositoryClass);
			tx_mvc_validator_factory::getInstanceValidator()->setClassOrInterface('tx_l10nmgr_models_exporter_exportStateRepository')->isValid($statesRepository);

			$this->row['statescollection'] = $statesRepository->findByexportdata_id($this->row['uid']);
		}
		return $this->row['statescollection'];
	}


	public function getCurrentState() {
		$statesCollection = $this->getStatesCollection();
		$currentState = NULL;

		// loop through all states to get tha newest one
		foreach ($statesCollection as $state) { /* @var $state tx_l10nmgr_models_exporter_exportState */
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

}

?>