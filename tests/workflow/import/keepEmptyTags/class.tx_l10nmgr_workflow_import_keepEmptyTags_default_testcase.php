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
 * class.tx_l10nmgr_workflow_import_keepEmptyTags_default_testcase.php
 *
 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.tx_l10nmgr_workflow_import_keepEmptyTags_default_testcase.php $
 * @date 29.09.2009 11:30:21
 * @seetx_l10nmgr_tests_databaseTestcase
 * @category testcase
 * @package TYPO3
 * @subpackage l10nmgr
 * @access public
 */
class tx_l10nmgr_workflow_import_keepEmptyTags_default_testcase extends tx_l10nmgr_tests_databaseTestcase {


	/**
	 * @var tx_l10nmgr_domain_translationFactory
	 */
	protected $TranslationFactory  = null;

	/**
	 * @var tx_l10nmgr_domain_translateable_translateableInformationFactory
	 */
	protected $TranslatableFactory = null;

	/**
	 * @var tx_l10nmgr_service_importTranslation
	 */
	protected $TranslationService  = null;

	/**
	 * Creates the test environment.
	 *
	 * @access public
	 * @return void
	 *
	 * @author Timo Schmidt
	 */
	public function setUp() {
		$this->skipInWrongWorkspaceContext();
		$this->unregisterIndexedSearchHooks();

		$this->createDatabase();
		$db = $this->useTestDatabase();

		$this->importStdDB();

			// order of extension-loading is important !!!!
		$import = array ('cms','l10nmgr');
		$optional = array('static_info_tables','templavoila','realurl','aoe_realurlpath','languagevisibility','cc_devlog');
		foreach($optional as $ext) {
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
	 * Resets the test enviroment after the test.
	 *
	 * @access public
	 * @return void
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
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
	 * This testcase is used to test, that empty tags will be converted to selfclosing tags.
	 * See issue #11578
	 *
	 * @access public
	 * @return void
	 *
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 */
	public function emptyTagIsKeptInImportAndNotConvertedToSelfClosingTag() {
		$this->importDataset('/workflow/import/keepEmptyTags/fixtures/default/pages.xml');
		$this->importDataset('/workflow/import/keepEmptyTags/fixtures/default/ttcontent.xml');
		$this->importDataset('/workflow/import/keepEmptyTags/fixtures/default/language.xml');
		$this->importDataset('/workflow/import/keepEmptyTags/fixtures/default/l10nconfiguration.xml');
		$this->importDataset('/workflow/import/keepEmptyTags/fixtures/default/exportdata.xml');
		$forceTargetLanguageUid = 2;
		$expectedSysLanguageUid = $forceTargetLanguageUid;

		$TranslationData = $this->TranslationFactory->createFromXMLFile (
			t3lib_extMgm::extPath('l10nmgr').'tests/workflow/import/keepEmptyTags/fixtures/default/fixture-import.xml',
			$forceTargetLanguageUid
		);

		$exportDataRepository = new tx_l10nmgr_domain_exporter_exportDataRepository();
		$exportData           = $exportDataRepository->findById(67);

		$translateableFactoryDataProvider = new tx_l10nmgr_domain_translateable_typo3TranslateableFactoryDataProvider($exportData);
		$translateableFactoryDataProvider->addPageIdCollectionToRelevantPageIds($TranslationData->getPageIdCollection());

		$TranslatableInformation = $this->TranslatableFactory->_call('createFromDataProvider', $translateableFactoryDataProvider);
		$this->TranslationService->save($TranslatableInformation, $TranslationData);

		$translationRecordArray = t3lib_BEfunc::getRecordLocalization('tt_content',626451, $expectedSysLanguageUid, 'AND pid='.intval(150));

		$this->assertEquals (
			$expectedSysLanguageUid,
			$translationRecordArray[0]['sys_language_uid'],
			'The sys_language_uid of the new record are wrong.'
		);
		$fixtureRecord = t3lib_BEfunc::getRecord('tt_content', 626451);

		$this->assertEquals(
			trim($fixtureRecord['bodytext']),
			trim($translationRecordArray[0]['bodytext']),
			'The imported bodytext is not as expected'
		);


		$secondTranslationRecordArray = t3lib_BEfunc::getRecordLocalization('tt_content',626452, $expectedSysLanguageUid, 'AND pid='.intval(150));
		$secondFixtureRecord = t3lib_BEfunc::getRecord('tt_content', 626452);

		$this->assertEquals(
			trim($secondFixtureRecord['bodytext']),
			trim($secondTranslationRecordArray[0]['bodytext']),
			'The imported bodytext is not as expected'
		);
	}
}
?>