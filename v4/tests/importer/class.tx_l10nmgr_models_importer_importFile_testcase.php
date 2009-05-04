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
 * class.tx_l10nmgr_models_importer_importFile_testcase.php
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
 

require_once t3lib_extMgm::extPath('l10nmgr') . 'models/importer/class.tx_l10nmgr_models_importer_importFile.php';

class tx_l10nmgr_models_importer_importFile_testcase extends tx_phpunit_database_testcase {

	/**
	 * Changes current database to test database
	 *
	 * @param string $databaseName Overwrite test database name
	 * @return object
	 */
	protected function useTestDatabase($databaseName = null) {
		$db = $GLOBALS ['TYPO3_DB'];
		
		if ($databaseName) {
			$database = $databaseName;
		} else {
			$database = $this->testDatabase;
		}
		
		if (! $db->sql_select_db ( $database )) {
			die ( "Test Database not available" );
		}
		
		return $db;
	}
		
	/**
	 * The setup method create the testdatabase and loads the basic tables into the testdatabase
	 *
	 */
	public function setUp(){
		$this->createDatabase();
		$db = $this->useTestDatabase();

		$this->importExtensions(array('l10nmgr','corefake'));
	}

	public function tearDown(){
		$this->cleanDatabase();
		$this->dropDatabase();
		$GLOBALS['TYPO3_DB']->sql_select_db(TYPO3_db);
	}

	/**
	 *
	 * This testcase is used to ensure that an exportFile which contains a zip can be unzipped
	 */
	public function test_canExtractZip() {

	//	try{
			$row['uid'] = 4711;
			$row['importdata_id'] = 1212;
			$row['filename'] = 'test.zip';
			
			$importFile = new tx_l10nmgr_models_importer_importFile($row);

			$importFile->setImportFilePath(t3lib_extMgm::extPath('l10nmgr').'tests/importer/fixtures/importFile/');
			$importFile->isXml();
			
			$importFile->extractZIP();
			
			die();
	/*	}catch(Exception $e){
			echo $e->getMessage();
			echo 'Debug in '.__FILE__.' at line '.__LINE__;
			print('<pre>');
			print_r($e->getTrace());
			print('</pre>');
		}*/
	}
}

?>