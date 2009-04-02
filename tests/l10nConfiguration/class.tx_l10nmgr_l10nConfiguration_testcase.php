<?php
require_once(t3lib_extMgm::extPath('l10nmgr').'models/class.tx_l10nmgr_l10nConfiguration.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/class.tx_l10nmgr_l10nLanguage.php');

/**
 * This class is used to test the functionallity of the l10nAccumulatedInformationsFactory class.
 * 
 * @author Timo Schmidt
 * @see tx_l10nmgr:l10nAccumulatedInformationFactory
 *
 */

class tx_l10nmgr_l10nConfiguration_testcase extends tx_phpunit_database_testcase {

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
	 * This method is used to load a FixtureL10NConfig
	 *
	 * @return tx_l10nmgr_l10nConfiguration
	 */
	protected function getFixtureL10NConfig(){
		$fixtureConfig = new tx_l10nmgr_l10nConfiguration();
		$fixtureConfig->load(32);
		
		return $fixtureConfig;
	}
	
	public function test_getPageIdsFromPageTree () {
		$this->importDataSet(dirname(__FILE__). '/fixtures/canDeterminePageIdsFromPageTree.xml');
		
		$fixtureConfig = $this->getFixtureL10NConfig();
		$pageCollection = $fixtureConfig->getExportPageIdCollection();
		
		$this->assertTrue(in_array(4711,$pageCollection->getArrayCopy()),'page could not be found in page collection');
		$this->assertFalse(in_array(4715,$pageCollection->getArrayCopy()),'page could not be found in page collection');
		//var_dump($pageCollection);
	}
}
?>