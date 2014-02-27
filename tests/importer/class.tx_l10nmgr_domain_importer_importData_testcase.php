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
 * class.tx_l10nmgr_domain_importer_importFile_testcase.php
 *
 * @author Timo Schmidt <schmidt@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.tx_l10nmgr_domain_importer_importData_testcase.php $
 * @date 30.04.2009 10:25:09
 * @see  tx_phpunit_testcas
 * @category testcase
 * @package TYPO3
 * @subpackage l10nmgr
 * @access public
 */

class tx_l10nmgr_domain_importer_importData_testcase extends tx_l10nmgr_tests_databaseTestcase {

	/**
	 * @var string Temporary import folder
	 */
	protected $tempImportFolder;

	/**
	 * The setup method create the testdatabase and loads the basic tables into the testdatabase
	 *
	 */
	public function setUp(){

		// Create temporary folder.
		$this->tempImportFolder = PATH_site.'typo3temp/l10nmr/unittest/';
		if(!file_exists($this->tempImportFolder)){
			if(!mkdir($this->tempImportFolder, 0775, true)) {
				$this->markTestIncomplete('Could not create ' . $this->tempImportFolder);
			};
		}

		// Moving the files to typo3temp dir, as we know we have permissions here. Tried to do it with the t3lib_extFileFunctions but didn't succeed.
		shell_exec("cp -r " . t3lib_extMgm::extPath('l10nmgr').'tests/importer/fixtures/importFile/ ' . $this->tempImportFolder);

		$this->skipInWrongWorkspaceContext();

		$this->createDatabase();
		$db = $this->useTestDatabase();
		$this->importStdDB();

		$import = array ('cms','l10nmgr');
		$optional = array('static_info_tables','templavoila', 'languagevisibility');
		foreach($optional as $ext) {
			if (t3lib_extMgm::isLoaded($ext)) {
				$import[] = $ext;
			}
		}
		$this->importExtensions($import);
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

		$ImporterFile = new tx_l10nmgr_domain_importer_importFile($row);

		// set importerFile->setImportFilePath to TYPO3temp
		$ImporterFile->setImportFilePath($this->tempImportFolder . 'importFile');

		$this->assertTrue (
			$ImporterFile->isZip(),
			'Testfile should be an zip file!'
		);

		$ImporterFile->extractZIPAndCreateImportFileForEach();

		$this->assertFileExists (
			$this->tempImportFolder . 'importFile/test__to_pt_BR_300409-113504_export.xml'
		);

		//remove test file
		if(file_exists($this->tempImportFolder .'importFile/test__to_pt_BR_300409-113504_export.xml')) {
			unlink($this->tempImportFolder . 'importFile/test__to_pt_BR_300409-113504_export.xml');
		}

		$this->assertFileNotExists(
			$this->tempImportFolder . 'importFile/test__to_pt_BR_300409-113504_export.xml'
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
			$this->tempImportFolder . 'importFile/file1.xml',
			$remainingFilenames->offsetGet(0),
			'Wrong filename in remaining files of importData'
		);
		$this->assertEquals (
			$this->tempImportFolder . 'importFile/file2.xml',
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
		$importData->removeFilenamesFromRemainingFilenames(new ArrayObject(array($this->tempImportFolder . 'importFile/file1.xml')));

		$remainingFilenames = $importData->getImportRemainingFilenames();

		$this->assertEquals($this->tempImportFolder . 'importFile/file2.xml',$remainingFilenames->getIterator()->current(),'Wrong filenames in remaining files of importData');
	}

	/**
	 * This method returns a fixture importData with two files in a zip file (file1.xml and file2.xml)
	 *
	 * @return tx_l10nmgr_domain_importer_importData
	 */
	protected function getFixtureImportDataWithTwoFiles(){
		$importFileRow['uid'] 			= 4712;
		$importFileRow['importdata_id'] = 1313;
		$importFileRow['filename'] 		= 'fixtureZIPWithTwoFiles.zip';

		$importFile = new tx_l10nmgr_domain_importer_importFile($importFileRow);
		$importFile->setImportFilePath( $this->tempImportFolder . 'importFile');
		$importFile->extractZIPAndCreateImportFileForEach();

		//create a fixture importData
		$importDataRow['uid'] = 1313;
		$importData	= new tx_l10nmgr_domain_importer_importData($importDataRow);

		//overwrite importfilepath for each related import file
		foreach($importData->getImportFiles() as $importFile){
			$importFile->setImportFilePath($this->tempImportFolder . 'importFile');
		}

		return $importData;
	}
}

?>