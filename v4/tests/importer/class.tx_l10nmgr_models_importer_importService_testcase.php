<?php

require_once t3lib_extMgm::extPath('l10nmgr') . 'domain/class.tx_l10nmgr_domain_translationFactory.php';

class tx_l10nmgr_models_importer_importService_testcase extends tx_phpunit_database_testcase {
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

		$this->importExtensions(array('corefake','cms','l10nmgr','static_info_tables','templavoila'));
	}

	public function tearDown(){
		$this->cleanDatabase();
		$this->dropDatabase();
		$GLOBALS['TYPO3_DB']->sql_select_db(TYPO3_db);
	}

	/** 
	 * This test should ensure, that the import service modifies the database in a correct way.
	 */
	public function test_canImportServiceImportCorrectData(){
		$this->importDataSet(dirname(__FILE__). '/fixtures/canImportServiceImportCorrectData.xml');
		
		$export = dirname(__FILE__).'/fixtures/canImportServiceImportCorrectDataFixtureExport.xml';
		$import = dirname(__FILE__).'/fixtures/canImportServiceImportCorrectDataFixtureImport.xml';
		
		//create translationData for the current file
		$translationDataFactory = new tx_l10nmgr_domain_translationFactory();
		$translationData		= $translationDataFactory->create($import);
		
		//the translationdata should contain two pages (33153 and 33154)
		$this->assertEquals(2,	$translationData->getPageIdCollection()->count());
		
		$exportDataRepository = new tx_l10nmgr_models_exporter_exportDataRepository();
		$exportData = $exportDataRepository->findById(251);
		
		$translateableFactoryDataProvider = new tx_l10nmgr_models_translateable_typo3TranslateableFactoryDataProvider($exportData,$translationData->getPageIdCollection());
		$translateableInformationFactory  = new tx_l10nmgr_models_translateable_translateableInformationFactory();

		$translateableInformation		  = $translateableInformationFactory->create($translateableFactoryDataProvider);

		//on this point an created xml export should have the same structure as the exportfile from $export 
		
		//now we use the service to perform the update to the typo3 database, after the update has been performed
		//the should be an overlay for each page and each content element with the australian language
		// tx_l10nmgr_models_importer_importService::performImport($translateableInformation,$translationData);		
	}
}
?>