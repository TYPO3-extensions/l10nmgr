<?php
/***************************************************************
 *  Copyright notice
 *
 *  Copyright (c) 2014, AOE GmbH <dev@aoe.com>
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
 * Class tx_l10nmgr_workflow_import_defaultLanguage_default_testcase
 *
 * Test case for the "importAsDefaultLanguage" workflow
 */
class tx_l10nmgr_workflow_import_defaultLanguage_default_testcase extends tx_l10nmgr_tests_databaseTestcase {

	/**
	 * @var tx_l10nmgr_domain_translationFactory
	 */
	protected $translationFactory  = NULL;

	/**
	 * @var tx_l10nmgr_domain_translateable_translateableInformationFactory
	 */
	protected $translatableFactory = NULL;

	/**
	 * @var tx_l10nmgr_service_importTranslation
	 */
	protected $translationService  = NULL;

	/**
	 * Creates the test environment
	 *
	 * @return void
	 */
	public function setUp() {
		$this->skipInWrongWorkspaceContext();
		$this->unregisterIndexedSearchHooks();

		$this->createDatabase();
		$this->useTestDatabase();

		$this->importStdDB();

			// order of extension-loading is important !!!!
		$import = array (
			'cms',
			'l10nmgr',
		);
		$optional = array(
			'aoe_dbsequenzer',
			'languagevisibility',
			'templavoila',
		);

		// Read extension dependencies from extension configuration
		$extConfigurationArray = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['l10nmgr']);
		if(isset($extConfigurationArray['ext_dependencies'])){
			$dependencyArray = explode(',', $extConfigurationArray['ext_dependencies']);
			$optional = array_merge($optional, $dependencyArray);
			$optional = array_unique($optional);
		}

		foreach ($optional as $ext) {
			if (t3lib_extMgm::isLoaded($ext)) {
				$import[] = $ext;
			}
		}
		$this->importExtensions($import);

		$this->translationFactory = new tx_l10nmgr_domain_translationFactory();
		$this->translatableFactory = $this->getMock(
			$this->buildAccessibleProxy('tx_l10nmgr_domain_translateable_translateableInformationFactory'),
			array('dummy'),
			array(),
			'',
			FALSE
		);
		$this->translationService = new tx_l10nmgr_service_importTranslation();
	}

	/**
	 * Reset the test environment
	 *
	 * @return void
	 */
	public function tearDown() {
		$this->cleanDatabase();
		$this->dropDatabase();
		$GLOBALS['TYPO3_DB']->sql_select_db(TYPO3_db);

		$this->restoreIndexedSearchHooks();
	}

	/**
	 * Test if translated records can be imported as default language,
	 * updating the already existing records
	 *
	 * @test
	 * @return void
	 */
	public function importTranslationAsDefaultLanguage() {
		$this->importDataset('/workflow/import/defaultLanguage/fixtures/default/pages.xml');
		$this->importDataset('/workflow/import/defaultLanguage/fixtures/default/ttcontent.xml');
		$this->importDataset('/workflow/import/defaultLanguage/fixtures/default/language.xml');
		$this->importDataset('/workflow/import/defaultLanguage/fixtures/default/l10nconfiguration.xml');
		$this->importDataset('/workflow/import/defaultLanguage/fixtures/default/exportdata.xml');

		$translationData = $this->translationFactory->createFromXMLFile (
			t3lib_extMgm::extPath('l10nmgr') . 'tests/workflow/import/defaultLanguage/fixtures/default/fixture-import.xml'
		);

		$exportDataRepository = new tx_l10nmgr_domain_exporter_exportDataRepository();
		$exportData = $exportDataRepository->findById(67);

		$translatableFactoryDataProvider = new tx_l10nmgr_domain_translateable_typo3TranslateableFactoryDataProvider($exportData);
		$translatableFactoryDataProvider->addPageIdCollectionToRelevantPageIds($translationData->getPageIdCollection());

		$translatableInformation = $this->translatableFactory->_call('createFromDataProvider', $translatableFactoryDataProvider);

		$this->translationService->setImportAsDefaultLanguage(TRUE);
		$this->translationService->save($translatableInformation, $translationData);

		$pagesRecord = t3lib_BEfunc::getRecord('pages', 150);
		$this->assertEquals(
			'A translated page title',
			$pagesRecord['title']
		);
		unset($pagesRecord);

		$contentRecord = t3lib_BEfunc::getRecord('tt_content', 839);
		$this->assertEquals(
			0,
			$contentRecord['sys_language_uid']
		);
		$this->assertEquals(
			'A translated headline',
			$contentRecord['header']
		);
		$this->assertEquals(
			'A translated simple text.',
			$contentRecord['bodytext']
		);
		unset($contentRecord);

		$pagesRecord = t3lib_BEfunc::getRecord('pages', 24596);
		$this->assertEquals(
			'A second translated page title',
			$pagesRecord['title']
		);
		unset($pagesRecord);

		$contentRecord = t3lib_BEfunc::getRecord('tt_content', 540806);
		$expectedContent = '<div style="width: 233px; height: 11px; background: url(bg-box-b.gif) 0 0 no-repeat;">' .
			'<div><img src="moc.gif" alt="" /></div>' .
			'<br />' .
			'<p>A translated paragraph inside some random HTML tags.</p>' .
			'</div>';
		$this->assertEquals(
			$expectedContent,
			$contentRecord['bodytext']
		);
		unset($contentRecord);
	}
}
?>