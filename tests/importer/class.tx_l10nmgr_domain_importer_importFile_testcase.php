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
 * documenation
 *
 * {@inheritdoc}
 *
 * class.tx_l10nmgr_domain_importer_importFile_testcase.php
 *
 * @author Timo Schmidt <schmidt@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.classname.php $
 * @date 30.04.2009 10:25:09
 * @see  tx_phpunit_testcas
 * @category testcase
 * @package TYPO3
 * @subpackage extensionkey
 * @access public
 */

class tx_l10nmgr_domain_importer_importFile_testcase extends tx_l10nmgr_tests_databaseTestcase {

	/**
	 * The setup method create the testdatabase and loads the basic tables into the testdatabase
	 *
	 */
	public function setUp(){
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
	 */
	public function test_canExtractZip() {

		$row['uid'] = 4711;
		$row['importdata_id'] = 1212;
		$row['filename'] = 'test.zip';

		$importFile = new tx_l10nmgr_domain_importer_importFile($row);

		$importFile->setImportFilePath(t3lib_extMgm::extPath('l10nmgr').'tests/importer/fixtures/importFile');

		$this->assertTrue($importFile->isZip(),'Testfile should be an zip file!');

		$importFile->extractZIPAndCreateImportFileForEach();

		$fileHasBeenWritten = tx_mvc_validator_factory::getFileValidator()->isValid(t3lib_extMgm::extPath('l10nmgr').'tests/importer/fixtures/importFile/test__to_pt_BR_300409-113504_export.xml');

		$this->assertTrue($fileHasBeenWritten);

		unlink(t3lib_extMgm::extPath('l10nmgr').'tests/importer/fixtures/importFile/test__to_pt_BR_300409-113504_export.xml');

		$fileHasBeeRemoved = !tx_mvc_validator_factory::getFileValidator()->isValid(t3lib_extMgm::extPath('l10nmgr').'tests/importer/fixtures/importFile/test__to_pt_BR_300409-113504_export.xml');

		$this->assertTrue($fileHasBeeRemoved);
	}
}

?>