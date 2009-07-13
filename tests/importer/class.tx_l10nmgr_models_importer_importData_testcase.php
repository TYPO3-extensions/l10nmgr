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
 *
 * {@inheritdoc}
 *
 * class.tx_l10nmgr_models_importer_importFile_testcase.php
 *
 * @author Timo Schmidt <schmidt@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.tx_l10nmgr_models_importer_importData_testcase.php $
 * @date 30.04.2009 10:25:09
 * @see  tx_phpunit_testcas
 * @category testcase
 * @package TYPO3
 * @subpackage l10nmgr
 * @access public
 */

require_once t3lib_extMgm::extPath('l10nmgr') . 'models/importer/class.tx_l10nmgr_models_importer_importData.php';
require_once t3lib_extMgm::extPath('l10nmgr') . 'models/importer/class.tx_l10nmgr_models_importer_importFile.php';

class tx_l10nmgr_models_importer_importData_testcase extends tx_phpunit_database_testcase {


	/**
	 * The setup method create the testdatabase and loads the basic tables into the testdatabase
	 *
	 */
	public function setUp(){
		$this->createDatabase();
		$db = $this->useTestDatabase();
		$this->importStdDB();
		
		$this->importExtensions(array('l10nmgr'));
	}

	public function tearDown(){

		$GLOBALS['TYPO3_DB']->sql_select_db(TYPO3_db);
	}

	/**
	 * This testcase is used to ensure that an exportFile which contains a zip can be unzipped.
	 *
	 * @param void
	 * @return void
	 *
	 */
	public function test_canExtractZip() {
		$row['uid']           = 4711;
		$row['importdata_id'] = 1212;
		$row['filename']      = 'canExtractZip.zip';

		$ImporterFile = new tx_l10nmgr_models_importer_importFile($row);
		$ImporterFile->setImportFilePath(t3lib_extMgm::extPath('l10nmgr').'tests/importer/fixtures/importFile');

		$this->assertTrue (
			$ImporterFile->isZip(),
			'Testfile should be an zip file!'
		);

		$ImporterFile->extractZIPAndCreateImportFileForEach();

		$this->assertFileExists (
			t3lib_extMgm::extPath('l10nmgr').'tests/importer/fixtures/importFile/test__to_pt_BR_300409-113504_export.xml'
		);

		//remove test file
		unlink(t3lib_extMgm::extPath('l10nmgr').'tests/importer/fixtures/importFile/test__to_pt_BR_300409-113504_export.xml');

		$this->assertFileNotExists(
			t3lib_extMgm::extPath('l10nmgr').'tests/importer/fixtures/importFile/test__to_pt_BR_300409-113504_export.xml'
		);
	}

	/**
	 * This testcase is used to test that the remaining files of an importDataRecord can
	 * be determined correctly.
	 *
	 * @param void
	 * @return void
	 * @author Timo Schmidt
	 */
	public function test_canGetRemainingFilesFromImportData(){
		$importData			= $this->getFixtureImportDataWithTwoFiles();
		$remainingFilenames = $importData->getImportRemainingFilenames();

		$this->assertEquals (
			t3lib_extMgm::extPath('l10nmgr') . 'tests/importer/fixtures/importFile/file1.xml',
			$remainingFilenames->offsetGet(0),
			'Wrong filename in remaining files of importData'
		);
		$this->assertEquals (
			t3lib_extMgm::extPath('l10nmgr') . 'tests/importer/fixtures/importFile/file2.xml',
			$remainingFilenames->offsetGet(1),
			'Wrong filename in remaining files of importData'
		);
	}

	/**
	 * This testcase is used to test that filenames can be removed from a set of remaining filenames.
	 *
	 * @param void
	 * @return void
	 * @author Timo Schmidt
	 */
	public function test_canRemoveFilenamesFromRemainingFilenames(){
		$importData		= $this->getFixtureImportDataWithTwoFiles();
		$importData->removeFilenamesFromRemainingFilenames(new ArrayObject(array(t3lib_extMgm::extPath('l10nmgr').'tests/importer/fixtures/importFile/file1.xml')));

		$remainingFilenames = $importData->getImportRemainingFilenames();

		$this->assertEquals(t3lib_extMgm::extPath('l10nmgr').'tests/importer/fixtures/importFile/file2.xml',$remainingFilenames->getIterator()->current(),'Wrong filenames in remaining files of importData');
	}

	/**
	 * This method returns a fixture importData with two files in a zip file (file1.xml and file2.xml)
	 *
	 * @return tx_l10nmgr_models_importer_importData
	 */
	protected function getFixtureImportDataWithTwoFiles(){
		$importFileRow['uid'] 			= 4712;
		$importFileRow['importdata_id'] = 1313;
		$importFileRow['filename'] 		= 'fixtureZIPWithTwoFiles.zip';

		$importFile = new tx_l10nmgr_models_importer_importFile($importFileRow);
		$importFile->setImportFilePath(t3lib_extMgm::extPath('l10nmgr').'tests/importer/fixtures/importFile');
		$importFile->extractZIPAndCreateImportFileForEach();

		//create a fixture importData
		$importDataRow['uid'] = 1313;
		$importData	= new tx_l10nmgr_models_importer_importData($importDataRow);

		//overwrite importfilepath for each related import file
		foreach($importData->getImportFiles() as $importFile){
			$importFile->setImportFilePath(t3lib_extMgm::extPath('l10nmgr').'tests/importer/fixtures/importFile');
		}

		return $importData;
	}
}

?>