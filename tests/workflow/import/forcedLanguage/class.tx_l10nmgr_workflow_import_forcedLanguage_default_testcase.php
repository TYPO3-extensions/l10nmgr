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
 * {@inheritdoc}
 *
 * class.tx_l10nmgr_workflow_import_forcedLanguage_default_testcase.php
 *
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.tx_l10nmgr_workflow_import_forcedLanguage_default_testcase.php $
 * @date 29.09.2009 11:30:21
 * @seetx_l10nmgr_tests_databaseTestcase
 * @category testcase
 * @package TYPO3
 * @subpackage l10nmgr
 * @access public
 */
class tx_l10nmgr_workflow_import_forcedLanguage_default_testcase extends tx_l10nmgr_tests_databaseTestcase {

	/**
	 * @var tx_l10nmgr_domain_translationFactory
	 */
	protected $TranslationFactory  = NULL;

	/**
	 * @var tx_l10nmgr_domain_translateable_translateableInformationFactory
	 */
	protected $TranslatableFactory = NULL;

	/**
	 * @var tx_l10nmgr_service_importTranslation
	 */
	protected $TranslationService  = NULL;

	/**
	 * Creates the test environment.
	 *
	 * @access public
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
			'templavoila'
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

		$this->TranslationFactory  = new tx_l10nmgr_domain_translationFactory();
		$this->TranslatableFactory = $this->getMock($this->buildAccessibleProxy('tx_l10nmgr_domain_translateable_translateableInformationFactory'), array('dummy'), array(), '', FALSE);
		$this->TranslationService  = new tx_l10nmgr_service_importTranslation();
	}

	/**
	 * Resets the test environment after the test.
	 *
	 * @access public
	 * @return void
	 */
	public function tearDown() {
		restore_error_handler();

		$this->cleanDatabase();
		$this->dropDatabase();
		$GLOBALS['TYPO3_DB']->sql_select_db(TYPO3_db);

		$this->restoreIndexedSearchHooks();
	}

	/**
	 * @test
	 *
	 * @access public
	 * @return void
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function importTranslationWithNoForcedLanguageUidOnNotExistingElement() {
		$this->importDataset('/workflow/import/forcedLanguage/fixtures/default/pages.xml');
		$this->importDataset('/workflow/import/forcedLanguage/fixtures/default/ttcontent.xml');
		$this->importDataset('/workflow/import/forcedLanguage/fixtures/default/language.xml');
		$this->importDataset('/workflow/import/forcedLanguage/fixtures/default/l10nconfiguration.xml');
		$this->importDataset('/workflow/import/forcedLanguage/fixtures/default/exportdata.xml');
		$forceTargetLanguageUid = 0;
		$expectedSysLanguageUid = 1;

		$TranslationData = $this->TranslationFactory->createFromXMLFile (
			t3lib_extMgm::extPath('l10nmgr').'tests/workflow/import/forcedLanguage/fixtures/default/fixture-import.xml',
			$forceTargetLanguageUid
		);

		$translationRecordArray = t3lib_BEfunc::getRecord('tt_content', 540807);
		$this->assertTrue (
			is_null($translationRecordArray)
		);

		$exportDataRepository = new tx_l10nmgr_domain_exporter_exportDataRepository();
		$exportData           = $exportDataRepository->findById(67);

		$translateableFactoryDataProvider = new tx_l10nmgr_domain_translateable_typo3TranslateableFactoryDataProvider($exportData);
		$translateableFactoryDataProvider->addPageIdCollectionToRelevantPageIds($TranslationData->getPageIdCollection());

		$TranslatableInformation = $this->TranslatableFactory->_call('createFromDataProvider', $translateableFactoryDataProvider);
		$this->TranslationService->save($TranslatableInformation, $TranslationData);

		$translationRecordArray = array_shift(t3lib_BEfunc::getRecordLocalization('tt_content', 540806, $expectedSysLanguageUid));
		$this->assertEquals (
			$expectedSysLanguageUid,
			$translationRecordArray['sys_language_uid'],
			'The sys_language_uid of the new record are wrong.'
		);

			// check page overlay
		$recordOverlayArray = t3lib_BEfunc::getRecordLocalization('pages_language_overlay', 24596, $expectedSysLanguageUid, 'AND pid='.intval(24596));
		$this->assertEquals(
			'HMTL element',
			$recordOverlayArray[0]['title'],
			'Check the pages_language_overlay record!'
		);
	}

	/**
	 * @test
	 *
	 * @access public
	 * @return void
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function importTranslationWithForcedLanguageUidOnNotExistingElement() {
		$this->importDataset('/workflow/import/forcedLanguage/fixtures/default/pages.xml');
		$this->importDataset('/workflow/import/forcedLanguage/fixtures/default/ttcontent.xml');
		$this->importDataset('/workflow/import/forcedLanguage/fixtures/default/language.xml');
		$this->importDataset('/workflow/import/forcedLanguage/fixtures/default/l10nconfiguration.xml');
		$this->importDataset('/workflow/import/forcedLanguage/fixtures/default/exportdata.xml');
		$forceTargetLanguageUid = 2;
		$expectedSysLanguageUid = $forceTargetLanguageUid;

		$TranslationData = $this->TranslationFactory->createFromXMLFile (
			t3lib_extMgm::extPath('l10nmgr').'tests/workflow/import/forcedLanguage/fixtures/default/fixture-import.xml',
			$forceTargetLanguageUid
		);

		$exportDataRepository = new tx_l10nmgr_domain_exporter_exportDataRepository();
		$exportData           = $exportDataRepository->findById(67);

		$translateableFactoryDataProvider = new tx_l10nmgr_domain_translateable_typo3TranslateableFactoryDataProvider($exportData);
		$translateableFactoryDataProvider->addPageIdCollectionToRelevantPageIds($TranslationData->getPageIdCollection());

		$TranslatableInformation = $this->TranslatableFactory->_call('createFromDataProvider', $translateableFactoryDataProvider);
		$this->TranslationService->save($TranslatableInformation, $TranslationData);

		$translationRecordArray = array_shift(t3lib_BEfunc::getRecordLocalization('tt_content', 540806, $expectedSysLanguageUid));

		$this->assertEquals (
			$expectedSysLanguageUid,
			$translationRecordArray['sys_language_uid'],
			'The sys_language_uid of the new record are wrong.'
		);

			// check page overlay
		$recordOverlayArray = t3lib_BEfunc::getRecordLocalization('pages_language_overlay', 24596, $expectedSysLanguageUid, 'AND pid='.intval(24596));
		$this->assertEquals(
			'HMTL element',
			$recordOverlayArray[0]['title'],
			'Check the pages_language_overlay record!'
		);
	}

	/**
	 * @test
	 *
	 * @access public
	 * @return void
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function importTranslationWithNoForcedLanguageUidOnExistingElement() {
		$this->importDataset('/workflow/import/forcedLanguage/fixtures/default/pages.xml');
		$this->importDataset('/workflow/import/forcedLanguage/fixtures/default/ttcontent-2.xml');
		$this->importDataset('/workflow/import/forcedLanguage/fixtures/default/language.xml');
		$this->importDataset('/workflow/import/forcedLanguage/fixtures/default/l10nconfiguration.xml');
		$this->importDataset('/workflow/import/forcedLanguage/fixtures/default/exportdata.xml');
		$forceTargetLanguageUid = 0;
		$expectedSysLanguageUid = 1;

		$TranslationData = $this->TranslationFactory->createFromXMLFile (
			t3lib_extMgm::extPath('l10nmgr').'tests/workflow/import/forcedLanguage/fixtures/default/fixture-import-2.xml',
			$forceTargetLanguageUid
		);

		$exportDataRepository = new tx_l10nmgr_domain_exporter_exportDataRepository();
		$exportData           = $exportDataRepository->findById(67);

		$translateableFactoryDataProvider = new tx_l10nmgr_domain_translateable_typo3TranslateableFactoryDataProvider($exportData);
		$translateableFactoryDataProvider->addPageIdCollectionToRelevantPageIds($TranslationData->getPageIdCollection());

		$TranslatableInformation = $this->TranslatableFactory->_call('createFromDataProvider', $translateableFactoryDataProvider);

		$this->TranslationService->save($TranslatableInformation, $TranslationData);

		$translationRecordArray = t3lib_BEfunc::getRecord('tt_content', 619946);
		$this->assertEquals (
			$expectedSysLanguageUid,
			$translationRecordArray['sys_language_uid'],
			'The sys_language_uid of the new record are wrong.'
		);
		$this->assertEquals (
			'Translated header',
			$translationRecordArray['header'],
			'The header of the new record is wrong.'
		);

			// check page overlay
		$recordOverlayArray = t3lib_BEfunc::getRecordLocalization('pages_language_overlay', 33155, $expectedSysLanguageUid, 'AND pid='.intval(33155));
		$this->assertEquals(
			'headertest translated',
			$recordOverlayArray[0]['title'],
			'Check the pages_language_overlay record!'
		);
	}

	/**
	 * @test
	 *
	 * @access public
	 * @return void
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function importTranslationWithForcedLanguageUidOnExistingElement() {
		$this->importDataset('/workflow/import/forcedLanguage/fixtures/default/pages.xml');
		$this->importDataset('/workflow/import/forcedLanguage/fixtures/default/ttcontent-2.xml');
		$this->importDataset('/workflow/import/forcedLanguage/fixtures/default/language.xml');
		$this->importDataset('/workflow/import/forcedLanguage/fixtures/default/l10nconfiguration.xml');
		$this->importDataset('/workflow/import/forcedLanguage/fixtures/default/exportdata.xml');
		$forceTargetLanguageUid = 2;
		$expectedSysLanguageUid = $forceTargetLanguageUid;

		$TranslationData = $this->TranslationFactory->createFromXMLFile (
			t3lib_extMgm::extPath('l10nmgr').'tests/workflow/import/forcedLanguage/fixtures/default/fixture-import-2.xml',
			$forceTargetLanguageUid
		);

		$exportDataRepository = new tx_l10nmgr_domain_exporter_exportDataRepository();
		$exportData           = $exportDataRepository->findById(67);

		$translateableFactoryDataProvider = new tx_l10nmgr_domain_translateable_typo3TranslateableFactoryDataProvider($exportData);
		$translateableFactoryDataProvider->addPageIdCollectionToRelevantPageIds($TranslationData->getPageIdCollection());

		$TranslatableInformation = $this->TranslatableFactory->_call('createFromDataProvider', $translateableFactoryDataProvider);

		$this->TranslationService->save($TranslatableInformation, $TranslationData);

		$translationRecordArray = t3lib_BEfunc::getRecordLocalization('tt_content', 619945, $expectedSysLanguageUid, 'AND pid='.intval(33155));
		$this->assertEquals (
			$expectedSysLanguageUid,
			$translationRecordArray[0]['sys_language_uid'],
			'The sys_language_uid of the new record are wrong.'
		);
		$this->assertEquals (
			'Translated header',
			$translationRecordArray[0]['header'],
			'The header of the new record is wrong.'
		);
			// check page overlay
		$recordOverlayArray = t3lib_BEfunc::getRecordLocalization('pages_language_overlay', 33155, $expectedSysLanguageUid, 'AND pid='.intval(33155));
		$this->assertEquals(
			'headertest translated',
			$recordOverlayArray[0]['title'],
			'Check the pages_language_overlay record!'
		);
	}
}
?>