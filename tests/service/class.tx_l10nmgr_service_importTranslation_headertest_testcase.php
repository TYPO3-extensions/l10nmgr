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

require_once t3lib_extMgm::extPath('l10nmgr') . 'domain/class.tx_l10nmgr_domain_translationFactory.php';
require_once t3lib_extMgm::extPath('l10nmgr') . 'service/class.tx_l10nmgr_service_importTranslation.php';

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
 * @seetx_phpunit_database_testcase
 * @category testcase
 * @package TYPO3
 * @subpackage l10nmgr
 * @access public
 */

class tx_l10nmgr_service_importTranslation_headertest_testcase extends tx_phpunit_database_testcase {
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
		$this->createDatabase();
		$db = $this->useTestDatabase();

		// order of extension-loading is important !!!!
		$this->importExtensions(array ('corefake','cms','l10nmgr','static_info_tables','templavoila','realurl','indexed_search','aoe_realurlpath','languagevisibility'));

		$this->TranslationFactory  = new tx_l10nmgr_domain_translationFactory();
		$this->TranslatableFactory = new tx_l10nmgr_models_translateable_translateableInformationFactory();
		$this->TranslationService  = new tx_l10nmgr_service_importTranslation();
	}

	/**
	* Resets the test enviroment after the test.
	*/
	function tearDown() {
		$this->cleanDatabase();
   		$this->dropDatabase();
   		$GLOBALS['TYPO3_DB']->sql_select_db(TYPO3_db);
	}

	/**
	* Imports a fixture xml import file and uses the api to import it into typo3.
	* After import there should be a translated page and a translated content element.
	* <br> tag should be kept in the overlay after import.
	*
	* @todo
	*/
	public function test_canImportserviceImportCorrectContentelement(){

		$import = t3lib_extMgm::extPath('l10nmgr').'tests/service/fixtures/headertest/test__to_pt_BR_300409-113504_import.xml';

		$this->importDataSet(t3lib_extMgm::extPath('l10nmgr').'/tests/service/fixtures/headertest/pages.xml');
		$this->importDataSet(t3lib_extMgm::extPath('l10nmgr').'/tests/service/fixtures/headertest/ttcontent.xml');
		$this->importDataSet(t3lib_extMgm::extPath('l10nmgr').'/tests/service/fixtures/headertest/l10nconfiguration.xml');
		$this->importDataSet(t3lib_extMgm::extPath('l10nmgr').'/tests/service/fixtures/headertest/exportdata.xml');
		$this->importDataSet(t3lib_extMgm::extPath('l10nmgr').'/tests/service/fixtures/headertest/language.xml');

		$TranslationData = $this->TranslationFactory->create($import);

		$exportDataRepository = new tx_l10nmgr_models_exporter_exportDataRepository();
		$exportData = $exportDataRepository->findById(67);

		$translateableFactoryDataProvider = new tx_l10nmgr_models_translateable_typo3TranslateableFactoryDataProvider($exportData,$TranslationData->getPageIdCollection());
		$TranslatableInformation		  = $this->TranslatableFactory->create($translateableFactoryDataProvider);

		$this->TranslationService->save($TranslatableInformation, $TranslationData);

		##
		# Check the content overlay
		##
		$row 			= t3lib_beFunc::getRecord('tt_content',619945);
		$contentOverlay = tx_mvc_system_dbtools::getTYPO3RowOverlay($row, 'tt_content', 2);

		//there should be an overlay of the content element with the uid 619945
		$this->assertEquals($contentOverlay['l18n_parent'],619945);

		//the sys_language_uid should be 2 for portugal
		$this->assertEquals($contentOverlay['sys_language_uid'],2);

		//the value of the translation should be
		$this->assertEquals($contentOverlay['header'],'This is a dirty header element & uses an <br> ampersand translated');
		//@todo check page overlay

	}
}
?>