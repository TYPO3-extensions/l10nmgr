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


class tx_l10nmgr_models_exporter_exportFile extends tx_mvc_ddd_typo3_abstractTCAObject {

	/**
	 * Overwrite getDatabaseFieldNames to remove the "virtual files" that should not be stored in the database
	 *
	 * @return array array of field names to store in the database
	 * @see ddd/tx_mvc_ddd_abstractDbObject#getDatabaseFieldNames()
	 */
	public function getDatabaseFieldNames() {
		$fields = parent::getDatabaseFieldNames();

		$key = array_search('exportdata_object', $fields);
		if ($key !== false) {
			unset($fields[$key]);
		}

		$key = array_search('content', $fields);
		if ($key !== false) {
			unset($fields[$key]);
		}

		return $fields;
	}

	/**
	 * Initialize the database object with
	 * the table name of current object
	 *
	 * @access     public
	 * @return     string
	 */
	public static function getTableName() {
		return 'tx_l10nmgr_exportfiles';
	}

	/**
	 * Set the exportdata object that is relevant for this export file.
	 *
	 * @param tx_l10nmgr_models_exporter_exportData $exportData
	 */
	public function setExportDataObject(tx_l10nmgr_models_exporter_exportData $exportData) {
		$this->row['exportdata_object'] 	= $exportData;
		$this->row['exportdata_id']			= $exportData->getUid();
	}

	/**
	 * Method to set the content of the exportfile
	 *
	 * @param string $content
	 */
	public function setContent($content) {
		$this->row['content'] = $content;
	}

	/**
	 * Writes the file to the harddisk
	 *
	 * @param void
	 * @return void
	 */
	public function write() {
		if(empty($this->row['content'])) {
			throw new LogicException('The exportfile has no content');
		}

		$path = t3lib_div::getFileAbsFileName(tx_mvc_common_typo3::getTCAConfigValue('uploadfolder', $this->getTablename(), 'filename') . '/' . $this->row['filename']);
		t3lib_div::writeFile($path, $this->row['content']);
	}


}

?>