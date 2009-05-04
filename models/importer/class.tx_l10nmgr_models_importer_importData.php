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

require_once t3lib_extMgm::extPath('l10nmgr').'models/exporter/class.tx_l10nmgr_models_exporter_exportDataRepository.php';
require_once t3lib_extMgm::extPath('l10nmgr').'models/importer/class.tx_l10nmgr_models_importer_importDataRepository.php';
require_once t3lib_extMgm::extPath('l10nmgr').'models/importer/class.tx_l10nmgr_models_importer_importFile.php';
require_once t3lib_extMgm::extPath('l10nmgr').'models/importer/class.tx_l10nmgr_models_importer_importFileRepository.php';
require_once t3lib_extMgm::extPath('l10nmgr').'models/importer/class.tx_l10nmgr_models_importer_importFileCollection.php';

/**
 * Object of this class represent one import
 *
 * class.tx_l10nmgr_models_importer_importData.php
 *
 * @author Timo Schmidt <schmidt@aoemedia.de>
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.tx_l10nmgr_models_importer_importData.php $
 * @date 24.04.2009 - 13:24:06
 * @see tx_mvc_ddd_typo3_abstractTCAObject
 * @category database
 * @package TYPO3
 * @subpackage tx_l10nmgr
 * @access public
 */
class tx_l10nmgr_models_importer_importData extends tx_mvc_ddd_typo3_abstractTCAObject implements tx_l10nmgr_interface_progressable  {

	/**
	 * Initialisize the database object with
	 * the table name of current object
	 *
	 * @access     public
	 * @return     string                                  HTML formated output
	 */
	public static function getTableName() {
		return 'tx_l10nmgr_importdata';
	}

	/**
	 * Overwrite getDatabaseFieldNames to remove the "virtual files" that should not be stored in the database
	 *
	 * @see ddd/tx_mvc_ddd_abstractDbObject#getDatabaseFieldNames()
	 * @return array array of field names to store in the database
	 */
	public function getDatabaseFieldNames() {
		$fields = parent::getDatabaseFieldNames();

		// remove some "virtual fields"
		$key = array_search('exportdata_object', $fields);
		if ($key !== false) {
			unset($fields[$key]);
		}

		return $fields;
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
	 * Returns the related exportData object of this import data object
	 *
	 * @return tx_l10nmgr_models_export_exportData
	 */
	public function getExportDataObject() {
		if (!empty($this->row['exportdata_id'])) {
			if (empty($this->row['exportdata_object'])) {
				$exportDataRepository = new tx_l10nmgr_models_exporter_exportDataRepository();
				$this->row['exportdata_object'] = $exportDataRepository->findById($this->row['exportdata_id']);
			}
			return $this->row['exportdata_object'];
		} else {
			throw new LogicException('This importData has no exportData assigned');
		}
	}
	
	/**
	 * Determines all files which are assigned to this import
	 * 
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 * @return tx_models_importer_importFileCollection 
	 */
	public function getImportFiles() {
		if (!empty($this->row['uid'])) {
			if (empty($this->row['importfilecollection_object'])) {
				$importFileRepository 						= new tx_l10nmgr_models_importer_importFileRepository();
				$importFiles								= $importFileRepository->findCollectionByProperty('importdata_id',$this->getUid());
				$this->row['importfilecollection_object'] 	= new tx_l10nmgr_models_importer_importFileCollection($importFiles);
			}
			
			return $this->row['importfilecollection_object'];
		}
	}
	
	/**
	 * This method is used to extract all importFiles which are zip files.
	 * 
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 * @param void
	 * @return void
	 */
	public function extractAllZipContent(){
		foreach($this->getImportFiles() as $importFile){
			if($importFile->isZip()){
				$importFile->extractZIPAndCreateImportFileForEach();
			}
		}
		//invalidate old cached results
		unset($this->row['importfilecollection_object']);
	}


	/**
	 * Returns the progress of the processed importData as pecentage value
	 * 
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 * @return float
	 * 
	 */
	public function getProgressPercentage(){
		return 0;
	}
		
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/models/importer/class.tx_l10nmgr_models_importer_importData.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/models/importer/class.tx_l10nmgr_models_importer_importData.php']);
}
?>