<?php
	// autoload the mvc
if (t3lib_extMgm::isLoaded('mvc')) {
	require_once(t3lib_extMgm::extPath('mvc').'common/class.tx_mvc_common_classloader.php');
	tx_mvc_common_classloader::loadAll();
} else {
	exit('Framework "mvc" not loaded!');
}

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

/**
 * Testclass used to test the functionallity of the exporter. The exporter
 * is used to export a set of pages as xml structure.
 *  *
 * class.tx_l10nmgr_l10nExporter_testcase.php
 *
 * @author	 Timo Schmidt <schmidt@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.tx_l10nmgr_l10nExporter_testcase.php $
 * @date 01.04.2009 - 15:03:35
 * @package	TYPO3
 * @subpackage	l10nmgr
 * @access public
 */

class tx_l10nmgr_domain_exporter_exporter_basic_testcase extends tx_phpunit_database_testcase {
	/**
	 * The setup method create the testdatabase and loads the basic tables into the testdatabase
	 *
	 */
	public function setUp(){
		global $BE_USER;
		$this->assertEquals($BE_USER->user['workspace_id'],0,'Run this test only in the live workspace' );

		$this->createDatabase();
		$db = $this->useTestDatabase();
		$this->importStdDB();

		$this->importExtensions(array('cms','l10nmgr','static_info_tables','templavoila'));
	}

	public function tearDown(){
		$this->cleanDatabase();
		$this->dropDatabase();
		$GLOBALS['TYPO3_DB']->sql_select_db(TYPO3_db);
	}

	/**
	 * This testcase is used to check that the exporter stops after an expected number of runs
	 *
	 * @param void
	 * @return void
	 * @author Timo Schmidt
	 */
	public function test_exporterTerminatesAfterExpectedNumberOfRuns(){
		$this->importDataSet(t3lib_extMgm::extPath('l10nmgr'). 'tests/exporter/fixtures/basic/canLoadFixtureExportConfiguration.xml');
		$this->importDataSet(t3lib_extMgm::extPath('l10nmgr'). 'tests/exporter/fixtures/basic/canLoadFixtureExportData.xml');
		$this->importDataSet(t3lib_extMgm::extPath('l10nmgr'). 'tests/exporter/fixtures/basic/exporterTerminatesAfterExpectedNumberOfRuns.xml');

		$exportData = $this->getFixtureExportData();

		$view 		= new tx_l10nmgr_view_export_exporttypes_CATXML();
		$view->setL10NConfiguration($exportData->getL10nConfigurationObject());

		$exporter 	= new tx_l10nmgr_domain_exporter_exporter($exportData,1,$view);

		$runCount = 0;
		while($exporter->run()){
			$exportData = $exporter->getExportData();
			$exporter 	= new tx_l10nmgr_domain_exporter_exporter($exportData,1,$view);
			$runCount++;

		}

		$this->assertEquals($runCount,3,'unexpected number of run counts in export');
	}

	/**
	 * We only load the export configuration and exportdata, no content is in the database therefore the export should be empty.
	 *
	 * @expectedException tx_mvc_exception_skipped
	 * @author Timo Schmidt
	 * @test
	 */
	public function exporterThrowsExceptionOnEmptyExport(){
		$this->importDataSet(t3lib_extMgm::extPath('l10nmgr'). 'tests/exporter/fixtures/basic/canLoadFixtureExportConfiguration.xml');
		$this->importDataSet(t3lib_extMgm::extPath('l10nmgr'). 'tests/exporter/fixtures/basic/canLoadFixtureExportData.xml');

		$exportData = $this->getFixtureExportData();

		 while (true)
		 	tx_l10nmgr_domain_exporter_exporter::performFileExportRun($exportData, 1);
	}

	/**
	 * Method to check that the fixtureExportData can be loaded
	 *
	 * @param void
	 * @return void
	 * @author Timo Schmidt
	 */
	public function test_canGetFixtureExportData(){
		$this->importDataSet(t3lib_extMgm::extPath('l10nmgr'). 'tests/exporter/fixtures/basic/canLoadFixtureExportConfiguration.xml');
		$this->importDataSet(t3lib_extMgm::extPath('l10nmgr'). 'tests/exporter/fixtures/basic/canLoadFixtureExportData.xml');

		$exportData = $this->getFixtureExportData();

		$this->assertEquals($exportData->getUid(),1111, 'The fixture exportData can not be loaded from the database');
		$this->assertEquals($exportData->getL10nConfigurationObject()->getUid(),999,'Can not determine configuration from exportData');
	}

	/**
	 * Helpermethod to load the fixture exportData from the test database
	 *
	 * @return tx_l10nmgr_domain_exporter_exportData
	 */
	protected function getFixtureExportData(){
		$exportDataRepository = new tx_l10nmgr_domain_exporter_exportDataRepository();
		$exportData = $exportDataRepository->findById(1111);

		return $exportData;
	}
}
?>