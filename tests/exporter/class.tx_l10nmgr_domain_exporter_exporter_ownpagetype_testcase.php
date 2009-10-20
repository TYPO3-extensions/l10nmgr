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
 * This testcase shoul check that the exporter even export pages with an own pagetype
 *
 * {@inheritdoc}
 *
 * class.tx_l10nmgr_domain_exporter_exporter_ownpagetype_testcase.php
 *
 * @author Timo Schmidt <schmidt@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.tx_l10nmgr_domain_exporter_exporter_ownpagetype_testcase.php $
 * @date 18.05.2009 13:37:19
 * @seetx_l10nmgr_tests_databaseTestcase
 * @category testcase
 * @package TYPO3
 * @subpackage l10nmgr
 * @access public
 */

class tx_l10nmgr_domain_exporter_exporter_ownpagetype_testcase extends tx_l10nmgr_tests_databaseTestcase {
	/**
	* This method overwrites the method of the baseclass to ensure that no live database will be used.
	*
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
	* Creates the test environment.
	*
	*/
	function setUp() {
		global $BE_USER;
		$this->assertEquals($BE_USER->user['workspace_id'],0,'Run this test only in the live workspace' );

		$this->createDatabase();
		$db = $this->useTestDatabase();
		$this->importStdDB();

		// order of extension-loading is important !!!!
		$this->importExtensions(array('cms','l10nmgr','static_info_tables','templavoila'));
	}

	/**
	* Resets the test enviroment after the test.
	*/
	function tearDown() {

   		$GLOBALS['TYPO3_DB']->sql_select_db(TYPO3_db);
	}

	/**
	* This test should check that pages are will even be exported if they are on
	* a non default pagetype.
	*
	* @param void
	* @return void
	* @author Timo Schmidt <timo.schmidt@aoemedia.de>
	* @test
	*/
	public function exportsPagesWithOwnPageType(){
		$this->importDataSet('/exporter/fixtures/ownpagetype/canLoadFixtureExportConfiguration.xml');
		$this->importDataSet('/exporter/fixtures/ownpagetype/canLoadFixtureExportData.xml');
		$this->importDataSet('/exporter/fixtures/ownpagetype/exporterTerminatesAfterExpectedNumberOfRuns.xml');

		$exportData = $this->getFixtureExportData();

		$view 		= new tx_l10nmgr_view_export_exporttypes_CATXML();
		$view->setL10NConfiguration($exportData->getL10nConfigurationObject());

		$exporter 	= new tx_l10nmgr_domain_exporter_exporter($exportData,4,$view);

		$exporter->run();
		$exportData = $exporter->getExportData();

		$exporterResult = simplexml_load_string  ($exporter->getResultForChunk(), 'SimpleXMLElement', LIBXML_NOCDATA );

		$this->assertEquals((string)$exporterResult->pageGrp[0]->data,'Translate Me 1');
		$this->assertEquals((string)$exporterResult->pageGrp[1]->data,'Translate Me 2');
		$this->assertEquals((string)$exporterResult->pageGrp[2]->data,'Translate Me 3');

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