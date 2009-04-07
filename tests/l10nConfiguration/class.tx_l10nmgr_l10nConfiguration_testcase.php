<?php
	// autoload the mvc 
if (t3lib_extMgm::isLoaded('mvc')) {
	require_once(t3lib_extMgm::extPath('mvc').'common/class.tx_mvc_common_classloader.php');
	tx_mvc_common_classloader::loadAll();
} else {
	exit('Framework "mvc" not loaded!');
}

require_once(t3lib_extMgm::extPath('l10nmgr').'models/configuration/class.tx_l10nmgr_models_configuration_configuration.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/configuration/class.tx_l10nmgr_models_configuration_configurationRepository.php');

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
	 * @return tx_l10nmgr_models_configuration_configuration
	 */
	protected function getFixtureL10NConfig(){
		$fixtureConfigRepository = new tx_l10nmgr_models_configuration_configurationRepository();
		$fixtureConfig = $fixtureConfigRepository->findById(32);
				
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