<?php
require_once(t3lib_extMgm::extPath('l10nmgr').'models/class.tx_l10nmgr_l10nConfiguration.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/language/class.tx_l10nmgr_models_language_Language.php');

require_once(t3lib_extMgm::extPath('l10nmgr').'interfaces/interface.tx_l10nmgr_interfaces_wordsCountable.php');

require_once(t3lib_extMgm::extPath('l10nmgr').'models/translateable/class.tx_l10nmgr_models_translateable_PageGroup.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/translateable/class.tx_l10nmgr_models_translateable_translateableElement.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/translateable/class.tx_l10nmgr_models_translateable_translateableField.php');

require_once(t3lib_extMgm::extPath('l10nmgr').'models/translateable/class.tx_l10nmgr_models_translateable_translateableInformation.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/translateable/class.tx_l10nmgr_models_translateable_translateableInformationFactory.php');

/**
 * This class is used to test the functionallity of the l10nAccumulatedInformationsFactory class.
 * 
 * @author Timo Schmidt
 * @see tx_l10nmgr:l10nAccumulatedInformationFactory
 *
 */

class tx_l10nmgr_translateableInformationFactory_testcase extends tx_phpunit_database_testcase {

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
	 * Method to ensure that the fixtureL10NConfig can be loaded from the testdatabase.
	 * 
	 * @param void
	 * @return void
	 *
	 */
	public function test_canLoadFixtureL10NConfig(){
		$this->importDataSet(dirname(__FILE__). '/fixtures/canLoadFixtureL10NConfig.xml');
		
		$fixtureConfig = $this->getFixtureL10NConfig();

		$this->assertEquals($fixtureConfig->getId(),4711,'Fixture l10nConfig can not be loaded');
		
	}
	
	/**
	 * Testcase to ensure that the fixtureTargetLanguage can be loaded from the test database.
	 *
	 * @param void
	 * @return void
	 */
	public function test_canLoadFixtureTargetLanguage(){
		$this->importDataSet(dirname(__FILE__). '/fixtures/canLoadFixtureTargetLanguage.xml');
		$fixtureTargetLanguage 	= $this->getFixtureTargetLanguage();
		
		$this->assetEquals($fixtureTargetLanguage['uid'],999,'Fixture Targetlanguage can not be loaded');
	}

	/**
	 * Testcase to ensuse that the fixturePreviewLanguage can be loaded from the test database.
	 * 
	 * @param void
	 * @return void
	 *
	 */
	public function test_canLoadFixturePreviewLanguage(){
		$this->importDataSet(dirname(__FILE__). '/fixtures/canLoadFixturePreviewLanguage.xml');
		$fixturePreviewLanguage = $this->getFixturePreviewLanguage();
		

		$this->assertEquals($fixturePreviewLanguage['uid'],998,'Fixture Previewlanguage can not be loaded');
	}
	
	/**
	 * This testcase should ensure that the TranslateableInformationsFactory creates a 
	 * translateableInformation with the correct pageGroups
	 *
	 * @param void
	 * @return void
	 */
	public function test_canCreateTranslateableInformationForPageId(){
		$this->importDataSet(dirname(__FILE__). '/fixtures/canCreateTranslateableInformationsForPageId.xml');
		$this->importDataSet(dirname(__FILE__). '/fixtures/canLoadFixturePreviewLanguage.xml');
		$this->importDataSet(dirname(__FILE__). '/fixtures/canLoadFixtureTargetLanguage.xml');		
		$this->importDataSet(dirname(__FILE__). '/fixtures/canLoadFixtureL10NConfig.xml');
				
		$fixtureL10NConfig 			= $this->getFixtureL10NConfig();
		$fixturePreviewLanguage		= $this->getFixturePreviewLanguage();
		$fixtureTargetLanguage		= $this->getFixtureTargetLanguage();
		$fixtureLimitToPageIds		= $this->getFixtureLimitToPageids();
		
		$ids						= array();
		$factory 					= new tx_l10nmgr_models_translateable_translateableInformationFactory();
		$translateableInformations 	= $factory->create($fixtureL10NConfig,$fixtureLimitToPageIds,$fixtureTargetLanguage,$fixturePreviewLanguage);
		$pageGroups					= $translateableInformations->getPageGroups();

		foreach($pageGroups as $pageGroup){
			$ids[$pageGroup->getPageId()] = (int)$pageGroup->getPageId();
		}
						
		$this->assertTrue(in_array(4711,$ids,'translatedable page could not be found in pageid array'));
		$this->assertTrue(in_array(4712,$ids,'translatedable page could not be found in pageid array'));
		$this->assertFalse(in_array(4713,$ids,'page not in limit found in translateable pages'));	
	}
	
	public function test_canDetermineTranslateableElementsForPageIds(){
		$this->importDataSet(dirname(__FILE__). '/fixtures/canDetermineTranslateableElementsForPageIds.xml');
		$this->importDataSet(dirname(__FILE__). '/fixtures/canLoadFixturePreviewLanguage.xml');
		$this->importDataSet(dirname(__FILE__). '/fixtures/canLoadFixtureTargetLanguage.xml');		
		$this->importDataSet(dirname(__FILE__). '/fixtures/canLoadFixtureL10NConfig.xml');
				
		$fixtureL10NConfig 			= $this->getFixtureL10NConfig();
		$fixturePreviewLanguage		= $this->getFixturePreviewLanguage();
		$fixtureTargetLanguage		= $this->getFixtureTargetLanguage();
		$fixtureLimitToPageIds		= $this->getFixtureLimitToPageids();
		
		$factory 					= new tx_l10nmgr_models_translateable_translateableInformationFactory();
		$translateableInformations 	= $factory->create($fixtureL10NConfig,$fixtureLimitToPageIds,$fixtureTargetLanguage,$fixturePreviewLanguage);
		$pageGroups					= $translateableInformations->getPageGroups();
		
		echo "Debug".__FILE__." ".__LINE__;
		print('<pre>');
		print_r($pageGroups);					
		print('</pre>');
	}
	
	/**
	 * This method is used to load a FixtureL10NConfig
	 *
	 * @return tx_l10nmgr_l10nConfiguration
	 */
	protected function getFixtureL10NConfig(){
		$fixtureConfig = new tx_l10nmgr_l10nConfiguration();
		$fixtureConfig->load(4711);
		
		return $fixtureConfig;
	}
	
	/**
	 * This method loads an instance if a fixture Target Language
	 *
	 * @return tx_l10nmgr_l10nLanguage
	 */
	protected function getFixtureTargetLanguage(){
		$fixtureLanguage = new tx_l10nmgr_models_language_Language();
		$fixtureLanguage->load(999);
		
		return $fixtureLanguage;
	}
	
	/**
	 * This method loads an instance on a fixture Preview Language
	 *
	 * @return tx_l10nmgr_l10nLanguage
	 */
	protected function getFixturePreviewLanguage(){
		$fixtureLanguage = new tx_l10nmgr_models_language_Language();
		$fixtureLanguage->load(998);
		
		return $fixtureLanguage;
	}
	
	/**
	 * A list of Accumulated Informations can be limited to a set of pageIds (to limit the size of the resulting xml file)
	 * This method returns a fixtureCollection of pageIds that should be used as limit of pageIds
	 *
	 * @return ArrayObject
	 */
	protected function getFixtureLimitToPageids(){
		$limitPageIdCollection  = new ArrayObject();
		$limitPageIdCollection->append(4711);
		$limitPageIdCollection->append(4712);
		
		return $limitPageIdCollection;
		
	}

}
	
?>