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
 * This testcase should ensure that a header contentelement with html code and
 * apersand should be imported correctlly
 *
 * {@inheritdoc}
 *
 * class.tx_l10nmgr_service_importTranslation_headertest_testcase.php
 *
 * @author Timo Schmidt <schmidt@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.tx_l10nmgr_service_importTranslation_headertest_testcase.php $
 * @date 30.04.2009 11:30:21
 * @seetx_l10nmgr_tests_databaseTestcase
 * @category testcase
 * @package TYPO3
 * @subpackage l10nmgr
 * @access public
 */
class tx_l10nmgr_service_importTranslation_headertest_testcase extends tx_l10nmgr_tests_databaseTestcase {

	/**
	 * This method overwrites the method of the baseclass to ensure that no live database will be used.
	 *
	 */
	protected function useTestDatabase($databaseName = null) {
		$db = $GLOBALS['TYPO3_DB'];
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
	 * Resets the test environment after the test
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
	* Imports a fixture xml import file and uses the api to import it into typo3.
	* After import there should be a translated page and a translated content element.
	* <br> tag should be kept in the overlay after import.
	*
	*/
	public function test_canImportserviceImportCorrectContentelement(){

		$import = t3lib_extMgm::extPath('l10nmgr').'tests/service/fixtures/headertest/test__to_pt_BR_300409-113504_import.xml';

		$this->importDataSet('/service/fixtures/headertest/pages.xml');
		$this->importDataSet('/service/fixtures/headertest/ttcontent.xml');
		$this->importDataSet('/service/fixtures/headertest/l10nconfiguration.xml');
		$this->importDataSet('/service/fixtures/headertest/exportdata.xml');
		$this->importDataSet('/service/fixtures/headertest/language.xml');

		$TranslationData = $this->TranslationFactory->createFromXMLFile($import); /* @var $TranslationData tx_l10nmgr_domain_translation_data */

		$exportDataRepository = new tx_l10nmgr_domain_exporter_exportDataRepository();
		$exportData = $exportDataRepository->findById(67);

		$translateableFactoryDataProvider = new tx_l10nmgr_domain_translateable_typo3TranslateableFactoryDataProvider($exportData);
		$translateableFactoryDataProvider->addPageIdCollectionToRelevantPageIds($TranslationData->getPageIdCollection());
		$TranslatableInformation = $this->TranslatableFactory->_call('createFromDataProvider', $translateableFactoryDataProvider);

		$this->TranslationService->save($TranslatableInformation, $TranslationData);

		$row 			= t3lib_beFunc::getRecord('tt_content', 619945);
		$contentOverlay = tx_mvc_system_dbtools::getTYPO3RowOverlay (
			$row,
			'tt_content',
			$TranslationData->getSysLanguageUid()
		);

			//there should be an overlay of the content element with the uid 619945
		$this->assertEquals($contentOverlay['l18n_parent'], 619945);

			// the sys_language_uid should be 2 for portugal
		$this->assertEquals (
			$contentOverlay['sys_language_uid'],
			$TranslationData->getSysLanguageUid()
		);

			// the value of the translation should be
		$this->assertEquals (
			$contentOverlay['header'],
			'This is a dirty header element & uses an <br /> ampersand translated ' // expected
		);

			// check page overlay
		$recordOverlayArray = t3lib_BEfunc::getRecordLocalization('pages_language_overlay', 33155, 2, 'AND pid='.intval(33155));
		$this->assertEquals(
			'headertest translated',
			$recordOverlayArray[0]['title'],
			'Check the pages_language_overlay record!'
		);
	}

	/**
	 * Imports a fixture xml import file and uses the api to import it into typo3.
	 * After import there should be a translated page and a translated content element.
	 * <br> tag should be kept in the overlay after import.
	 *
	 */
	public function test_findName(){
$this->markTestSkipped('');
		$import = t3lib_extMgm::extPath('l10nmgr').'tests/service/fixtures/headertest/test__to_pt_BR_300409-113504_import-2.xml';

		$this->importDataSet('/service/fixtures/headertest/pages.xml');
		$this->importDataSet('/service/fixtures/headertest/ttcontent-2.xml');
		$this->importDataSet('/service/fixtures/headertest/l10nconfiguration.xml');
		$this->importDataSet('/service/fixtures/headertest/exportdata.xml');
		$this->importDataSet('/service/fixtures/headertest/language.xml');

		$TranslationData = $this->TranslationFactory->createFromXMLFile($import); /* @var $TranslationData tx_l10nmgr_domain_translation_data */

		$exportDataRepository = new tx_l10nmgr_domain_exporter_exportDataRepository();
		$exportData = $exportDataRepository->findById(67);

		$translateableFactoryDataProvider = new tx_l10nmgr_domain_translateable_typo3TranslateableFactoryDataProvider($exportData);
		$translateableFactoryDataProvider->addPageIdCollectionToRelevantPageIds($TranslationData->getPageIdCollection());
		$TranslatableInformation = $this->TranslatableFactory->_call('createFromDataProvider', $translateableFactoryDataProvider);

		$this->TranslationService->save($TranslatableInformation, $TranslationData);

		$row 			= t3lib_beFunc::getRecord('tt_content', 619945);
		$contentOverlay = tx_mvc_system_dbtools::getTYPO3RowOverlay (
			$row,
			'tt_content',
			$TranslationData->getSysLanguageUid()
		);

			//there should be an overlay of the content element with the uid 619945
		$this->assertEquals($contentOverlay['l18n_parent'], 619945);

			// the sys_language_uid should be 2 for portugal
		$this->assertEquals (
			$contentOverlay['sys_language_uid'],
			$TranslationData->getSysLanguageUid(),
			__LINE__
		);

			// the value of the translation should be
		$this->assertEquals (
			$contentOverlay['header'],
			'This is a dirty header element & uses an <br /> ampersand translated ',
			__LINE__
		);

			// check page overlay
		$recordOverlayArray = t3lib_BEfunc::getRecordLocalization('pages_language_overlay', 33155, 2, 'AND pid='.intval(33155));
		$this->assertEquals(
			'headertest translated',
			$recordOverlayArray[0]['title'],
			'Check the pages_language_overlay record!'
		);
	}
}
?>