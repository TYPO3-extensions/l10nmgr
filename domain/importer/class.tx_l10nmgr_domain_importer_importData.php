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

require_once t3lib_extMgm::extPath('l10nmgr').'interface/interface.tx_l10nmgr_interface_progressable.php';

/**
 * Object of this class represent one import
 *
 * class.tx_l10nmgr_domain_importer_importData.php
 *
 * @author Timo Schmidt <schmidt@aoemedia.de>
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.tx_l10nmgr_domain_importer_importData.php $
 * @date 24.04.2009 - 13:24:06
 * @see tx_mvc_ddd_typo3_abstractTCAObject
 * @category database
 * @package TYPO3
 * @subpackage tx_l10nmgr
 * @access public
 */
class tx_l10nmgr_domain_importer_importData extends tx_mvc_ddd_typo3_abstractTCAObject implements tx_l10nmgr_interface_progressable  {

	/**
	 * @var string
	 */
	protected $currentFilename;

	/**
	 * Initialisize the database object with
	 * the table name of current object
	 *
	 * @access     public
	 * @return     string
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

		$key = array_search('importfilecollection_object', $fields);
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
	 * This method can be used to determine, that an import is completly processed.
	 *
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 * @return boolean
	 */
	public function getImportIsCompletelyProcessed(){
		return $this->getProgress('import_is_completely_processed');
	}

	/**
	 * This internal method can be used to set a processing state of the importData.
	 * The importData is completely processed when no remaining file is left.
	 *
	 * @author Timo Schmidt
	 * @param boolean
	 * @return void
	 */
	public function setImportIsCompletelyProcessed($boolean){
		$this->setProgress('import_is_completely_processed',$boolean);
	}

	/**
 	 * This method can be used to determine, that the import is completly unprocessed.
 	 *
 	 * @return boolean
	 */
	public function getImportIsCompletelyUnprocessed(){
		return ($this->getImportTotalNumberOfFiles() == $this->countRemainingImportFilenames());
	}

	/**
	 * Returns an array with filenames which are marked have not yet
	 * been processed.
	 *
	 * @param void
	 * @return ArrayObject
	 */
	public function getImportRemainingFilenames(){
		if (!($this->getProgress('import_remaining_filenames') instanceof ArrayObject)) {

			$remaining_files = new ArrayObject();
			foreach($this->getImportFiles() as $importFile){
				$remaining_files->append($importFile->getAbsoluteFilename());
			}

			$this->setProgress('import_remaining_filenames',$remaining_files);
		}

		return $this->getProgress('import_remaining_filenames');
	}

	/**
	 * Counts the number of remaining, unprocessed filenames.
	 *
	 * @return int
	 */
	protected function getImportNumberOfRemainingFilenames(){
		return $this->getImportRemainingFilenames()->count();
	}

	/**
	 * This method is used to remove a collection of filenames from the remaining filenames
	 * when they have been processed.
	 *
	 *@param ArrayObject $filenames Collection of filenames to remove
	 *@return void
	 */
	public function removeFilenamesFromRemainingFilenames($filenames){
		$remainingFilenames = $this->getImportRemainingFilenames();
		$remainingFilenamesLeft = array_diff($this->getImportRemainingFilenames()->getArrayCopy(),$filenames->getArrayCopy());
		$remainingFilenames ->exchangeArray($remainingFilenamesLeft);

		if(count($remainingFilenamesLeft) == 0){
			$this->setImportIsCompletelyProcessed(true);
		}

		$this->setProgress('import_remaining_filenames',$remainingFilenames );
	}

	/**
	 * Increases the number of import runs for this importData object.
	 *
	 * @param void
	 * @return void
	 */
	public function increaseNumberOfImportRuns(){
		$numRuns = $this->getNumberOfImportRuns();
		$numRuns++;
		$this->setNumberOfImportRuns($numRuns);
	}

	/**
	 * Returns the number of runs which have been performed during the import yet.
	 *
	 * @return int
	 */
	protected function getNumberOfImportRuns(){
		return $this->getProgress('import_number_of_runs');
	}

	/**
	 * Method to save the number of runs during the import
	 *
	 * @param int $value
	 */
	protected function setNumberOfImportRuns($value){
		$this->setProgress('import_number_of_runs',$value);
	}

	/**
	 * Returns the number of remaining filesnames which need to be imported.
	 *
	 * @author Timo Schmidt
	 * @return int
	 */
	public function countRemainingImportFilenames(){
		return $this->getImportRemainingFilenames()->count();
	}

	/**
	 * Returns the number of importfiles which are relevant for this import
	 *
	 * @author Timo Schmidt
	 * @return int
	 */
	public function getImportTotalNumberOfFiles(){
		return $this->getImportFiles()->count();
	}

	/**
	 * Returns the related exportData object of this import data object
	 *
	 * @return tx_l10nmgr_domain_export_exportData
	 */
	public function getExportDataObject() {
		if (!empty($this->row['exportdata_id'])) {
			if (empty($this->row['exportdata_object'])) {
				$exportDataRepository = new tx_l10nmgr_domain_exporter_exportDataRepository();
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
	 * @return tx_domain_importer_importFileCollection
	 */
	public function getImportFiles() {
		if (!empty($this->row['uid'])) {
			if (empty($this->row['importfilecollection_object'])) {
				$importFileRepository 						= new tx_l10nmgr_domain_importer_importFileRepository();
				$importFiles								= $importFileRepository->findCollectionByProperty('importdata_id',$this->getUid());
				$this->row['importfilecollection_object'] 	= new tx_l10nmgr_domain_importer_importFileCollection($importFiles);
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
		return (100 / $this->getImportTotalNumberOfFiles()) * ($this->getImportTotalNumberOfFiles() - $this->getImportNumberOfRemainingFilenames());
	}

	/**
	 * Returns the output which should be displayed in the progress bar.
	 *
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 * @return string
	 */
	public function getProgressOutput(){
		$completeFilenameWithPath = $this->getCurrentFilename();
		//remove path from filename
		$displayFilename = substr($completeFilenameWithPath,strrpos($completeFilenameWithPath,'/')+1);
		return '<strong>'.round($this->getProgressPercentage()).'</strong> % (Importing: '.$displayFilename.')';
	}

	/**
	 * Method to set the filename that is currently processed
	 *
	 * @param string filename
	 */
	protected function setCurrentFilename($filename){
		$this->currentFilename = $filename;
	}

	/**
	 * Returns the filename that is currently processed
	 *
	 * @return string
	 */
	protected function getCurrentFilename(){
		return $this->currentFilename;
	}

	/**
	 * Returns the next file for the import.
	 *
	 * @access protected
	 * @return string $fileName
	 */
	public function getNextFilename() {
		$remainingFilenames = $this->getImportRemainingFilenames();
		$it = $remainingFilenames->getIterator();
		$filename =  $it->current();

		$this->setCurrentFilename($filename);

		return $filename;
	}

	/**
	 * This method is used to attach a warning message to the export progress.
	 *
	 * @param string $type
	 * @param string $message
	 */
	public function addMessage($type,$message){
		$this->row['messages'] .= 	$type.' in File '.$this->getCurrentFilename().': '.
											"\n\n".
											(string) $message.
											"\n".
											"--------------------------------------------------".
											"\n";
	}

	/**
	 * This method introduces the importData object to remove a file from the remaining filenames
	 * that need to be processed.
	 *
	 * @param string
	 * @return void
	 */
	public function removeProcessedFilename($filename){
		$this->removeFilenamesFromRemainingFilenames(new ArrayObject(array($filename)));
	}

	/**
	 * Get the forced target language uid
	 *
	 * @access public
	 * @return integer DEFAULT is 0
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function getForceTargetLanguageUid() {
		$forceLangUid = 0;

		if (is_array($this->row) && array_key_exists('force_target_lang', $this->row)) {
			$forceLangUid = t3lib_div::intval_positive($this->row['force_target_lang']);
		}

		return $forceLangUid;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/domain/importer/class.tx_l10nmgr_domain_importer_importData.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/domain/importer/class.tx_l10nmgr_domain_importer_importData.php']);
}
?>