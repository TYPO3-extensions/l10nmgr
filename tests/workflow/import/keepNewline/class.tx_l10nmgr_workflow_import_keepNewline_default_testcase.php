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

require_once t3lib_extMgm::extPath('l10nmgr') . 'service/class.tx_l10nmgr_service_detectRecord.php';

/**
 *
 * {@inheritdoc}
 *
 * class.tx_l10nmgr_workflow_import_keepNewline_default_testcase.php
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
class tx_l10nmgr_workflow_import_keepNewline_default_testcase extends tx_l10nmgr_tests_databaseTestcase {

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
			'templavoila',
		);
		foreach ($optional as $ext) {
			if (t3lib_extMgm::isLoaded($ext)) {
				$import[] = $ext;
			}
		}
		$this->importExtensions($import);

		$this->translationFactory  = new tx_l10nmgr_domain_translationFactory();
		$this->translatableFactory = $this->getMock($this->buildAccessibleProxy('tx_l10nmgr_domain_translateable_translateableInformationFactory'), array('dummy'), array(), '', FALSE);
		$this->translationService  = new tx_l10nmgr_service_importTranslation();
	}

	/**
	 * Resets the test enviroment after the test.
	 *
	 * @access public
	 * @return void
	 */
	public function tearDown() {
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
	 */
	public function importTranslationWithForcedLanguageUidOnExistingElement() {
		$this->markTestSkipped(
			'The default RTE configuration removes all linebreaks, causing this test to fail. ' .
			'Overriding the RTE configuration for this specific test would require a lot of mocking, so it is skipped for now.'
		);

		$this->importDataset('/workflow/import/keepNewline/fixtures/default/pages.xml');
		$this->importDataset('/workflow/import/keepNewline/fixtures/default/ttcontent.xml');
		$this->importDataset('/workflow/import/keepNewline/fixtures/default/language.xml');
		$this->importDataset('/workflow/import/keepNewline/fixtures/default/l10nconfiguration.xml');
		$this->importDataset('/workflow/import/keepNewline/fixtures/default/exportdata.xml');
		$forceTargetLanguageUid = 2;
		$expectedSysLanguageUid = $forceTargetLanguageUid;

		$translationData = $this->translationFactory->createFromXMLFile (
			t3lib_extMgm::extPath('l10nmgr') . 'tests/workflow/import/keepNewline/fixtures/default/fixture-import.xml',
			$forceTargetLanguageUid
		);

		$exportDataRepository = new tx_l10nmgr_domain_exporter_exportDataRepository();
		$exportData           = $exportDataRepository->findById(67);

		$translateableFactoryDataProvider = new tx_l10nmgr_domain_translateable_typo3TranslateableFactoryDataProvider($exportData);
		$translateableFactoryDataProvider->addPageIdCollectionToRelevantPageIds($translationData->getPageIdCollection());

		$translatableInformation = $this->translatableFactory->_call('createFromDataProvider', $translateableFactoryDataProvider);
		$this->translationService->save($translatableInformation, $translationData);

		$translationRecordArray = t3lib_BEfunc::getRecordLocalization('tt_content', 839, $expectedSysLanguageUid, 'AND pid=' . intval(150));
		$this->assertEquals (
			$expectedSysLanguageUid,
			$translationRecordArray[0]['sys_language_uid'],
			'The sys_language_uid of the new record are wrong.'
		);
		$this->assertEquals (
			'Competition Information',
			$translationRecordArray[0]['header'],
			'The header of the new record is wrong.'
		);

		$fixtureRecord = t3lib_BEfunc::getRecord('tt_content', 839);
		$this->assertEquals(
			str_replace(CRLF, LF, $fixtureRecord['bodytext']),
			str_replace(CRLF, LF, $translationRecordArray[0]['bodytext']),
			'The bodytext contains wrong / missing newline characters in some way.'
		);

			// check page overlay
		$recordOverlayArray = t3lib_BEfunc::getRecordLocalization('pages_language_overlay', 150, $expectedSysLanguageUid, 'AND pid=' . intval(150));
		$this->assertEquals(
			'headertest translated',
			$recordOverlayArray[0]['title'],
			'Check the pages_language_overlay record!'
		);
	}
}
?>