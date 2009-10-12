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
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * This object represents an importFile
 *
 * {@inheritdoc}
 *
 * class.tx_l10nmgr_domain_importer_importFile.php
 *
 * @author Timo Schmidt <schmidt@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.tx_l10nmgr_domain_importer_importFile.php $
 * @date 29.04.2009 18:56:28
 * @see tx_mvc_ddd_abstractDbObject
 * @category database
 * @package TYPO3
 * @subpackage l10nmgr
 * @access public
 */
class tx_l10nmgr_domain_importer_importFile extends tx_mvc_ddd_typo3_abstractTCAObject {

	/**
	 * @var string holds the path to the import files.
	 */
	protected $importFilePath;

	/**
	 * Initialisize the database object with
	 * the table name of current object
	 *
	 * @access     public
	 * @return     string
	 */
	public static function getTableName() {
		return 'tx_l10nmgr_importfiles';
	}

	/**
	 * The constructor initialized the importFile path with the configured
	 * value in the tsconfig.
	 *
	 * @param array row from the database passed to the parent constructor
	 */
	public function __construct($row = array()){
		parent::__construct($row);
		$importFilePath = t3lib_div::getFileAbsFileName(tx_mvc_common_typo3::getTCAConfigValue('uploadfolder', self::getTableName(), 'filename'));
		$this->setImportFilePath($importFilePath);
	}

	/**
	 * This method can be used to determine if this exportfile is a zipfile.
	 *
	 * @param void
	 * @return boolean
	 *
	 * @author Timo Schmidt <schmidt@aoemedia.de>
	 */
	public function isZip(){
		return ($this->getFilenameExtension($this->getAbsoluteFilename()) == 'zip');
	}

	/**
	 * This method can be used to determine, that the exportfile is an xml file.
	 *
	 *  @param void
	 *  @return boolean
	 *
 	 * @author Timo Schmidt <schmidt@aoemedia.de>
	 */
	public function isXml(){
		return ($this->getFilenameExtension($this->getAbsoluteFilename()) == 'xml');
	}

	/**
	 * Returns the filename extension of a file.
	 * This protected method is used to determine the filename extension from the importedFile.
	 *
	 * @param string filename
	 * @return string filename extension
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 */
	protected function getFilenameExtension($filename){
		return substr(strtolower($filename),strrpos($filename,'.') + 1);
	}

	/**
	 * Returns the configured path for importFiles.
	 *
	 * @param void
	 * @return string
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 */
	public function getImportFilePath(){
		return $this->importFilePath;
	}

	/**
	 * This method is used to overwrite the importPath
	 *
	 * @param string
	 * @return void
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 */
	public function setImportFilePath($path){
		$this->importFilePath = $path;
	}

	/**
	 * Returns the filename with the complete path as prefix of the filename
	 *
	 * @param void
	 * @return string
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 */
	public function getAbsoluteFilename(){
		$absolutepath = $this->getImportFilePath().'/'.$this->getFilename();

		return $absolutepath;
	}

	/**
	 * This methods is used to extract a zip file to diffrent files.
	 *
	 * @param string importFilePath This is an optional path where the importfile should
	 * be readed from. The paramater can be used for testing issues.
	 * @return bool true if the extraction was successful
	 *
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 */
	public function extractZIPAndCreateImportFileForEach(){

		if (class_exists('ZipArchive')) {

			$absoluteImportFile 	= $this->getAbsoluteFilename();

			if(tx_mvc_validator_factory::getFileValidator()->isValid($absoluteImportFile)){
				if($this->isZip()){
					//the record contains a valid file
					$zipper = new ZipArchive();

					if($zipper->open($absoluteImportFile)){
						//!TODO what should be done, when a file will be overwritten?
						$zipper->extractTo($this->getImportFilePath());

						//create a new importFile for each xml file in the zip
						for($i = 0; $i < $zipper->numFiles; $i++){
							$filename = $zipper->getNameIndex($i);
							$this->createImportFileFromArchiveContent($filename);
						}

						//remove the zip itsself
						$this->remove();
						$zipper->close();
					}else{
						throw new tx_mvc_exception();
					}
				}else{
					throw new tx_mvc_exception_invalidArgument('The current file is not zipfile, therefore it cannot be unzipped.');
				}
			}else{
				throw new tx_mvc_exception_fileNotFound('invalid zip file '.$absoluteImportFile.' in import');
			}

			return true;

		} else {
			return false;
		}
	}

	/**
	 * This method should be used internally to remove the importFile from the database:
	 *
	 * @param void
	 * @return void
	 */
	protected function remove(){
		$importFileRepository = new tx_l10nmgr_domain_importer_importFileRepository();
		$importFileRepository->remove($this);
	}

	/**
	 * This method is used to create an importFileRecord from an importFile that is a zipfile.
	 *
	 * @param string path
	 * @param string filename
	 *
	 * @author Timo Schmidt <schmidt@aoemedia.de>
	 */
	protected function createImportFileFromArchiveContent($filename){
		$importFile = new tx_l10nmgr_domain_importer_importFile();
		$importFile->setFilename($filename);
		$importFile->setImportdata_id($this->getImportdata_id());
		$importFile->setImportFilePath($this->getImportFilePath());

		$importFileRepository = new tx_l10nmgr_domain_importer_importFileRepository();
		$importFileRepository->add($importFile);
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/domain/importer/class.tx_l10nmgr_domain_importer_importFile.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/domain/importer/class.tx_l10nmgr_domain_importer_importFile.php']);
}
?>