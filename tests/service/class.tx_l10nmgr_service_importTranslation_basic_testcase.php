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

	// autoload the mvc
t3lib_extMgm::isLoaded('mvc', true);
tx_mvc_common_classloader::loadAll();

require_once t3lib_extMgm::extPath('l10nmgr') . 'domain/class.tx_l10nmgr_domain_translationFactory.php';
require_once t3lib_extMgm::extPath('l10nmgr') . 'service/class.tx_l10nmgr_service_importTranslation.php';

/**
 *
 * {@inheritdoc}
 *
 * class.tx_l10nmgr_service_importTranslation_basic_testcase.php
 *
 * @author Michael Klapper <klapper@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id$
 * @date $Date$
 * @since 28.04.2009 - 15:13:53
 * @see tx_phpunit_database_testcase
 * @category database testcase
 * @package TYPO3
 * @subpackage l10nmgr
 * @access public
 */
class tx_l10nmgr_service_importTranslation_basic_testcase extends tx_phpunit_database_testcase {

	/**
	 * @var tx_l10nmgr_domain_translationFactory
	 */
	private $TranslationFactory = null;

	/**
	 * @var tx_l10nmgr_models_translateable_translateableInformationFactory
	 */
	private $TranslatableFactory = null;

	/**
	 * @var tx_l10nmgr_service_importTranslation
	 */
	private $TranslationService = null;

	/**
	 * Changes current database to test database
	 *
	 * If the test database can not be created exit the process
	 *
	 * @param string $databaseName OPTIONAL default is null - Overwrite test database name
	 * @access protected
	 * @uses t3lib_db
	 * @return t3lib_db
	 */
	protected function useTestDatabase($databaseName = null) {
		$Database = $GLOBALS ['TYPO3_DB'];

		if ($databaseName) {
			$database = $databaseName;
		} else {
			$database = $this->testDatabase;
		}

		if (! $Database->sql_select_db ( $database )) {
			exit("Test Database not available");
		}

		return $Database;
	}

	/**
	 * The setup method create the test database and
	 * loads the basic tables into the testdatabase
	 *
	 * @access public
	 * @return void
	 */
	public function setUp() {
		$this->createDatabase();
		$this->useTestDatabase ();

		$this->importExtensions (
			array ('corefake','cms','l10nmgr','static_info_tables','templavoila', 'aoe_webex_tableextensions', 'languagevisibility', 'syslog', 'realurl', 'indexed_search', 'aoe_realurlpath')
		);

		$this->TranslationFactory  = new tx_l10nmgr_domain_translationFactory();
		$this->TranslatableFactory = new tx_l10nmgr_models_translateable_translateableInformationFactory();
		$this->TranslationService  = new tx_l10nmgr_service_importTranslation();
	}

	/**
	 * Close the test database and restore the
	 * original system database configured by the localconf.php
	 *
	 * @access public
	 * @uses t3lib_db
	 * @return void
	 */
	public function tearDown() {
		$this->cleanDatabase();
		$this->dropDatabase();
		$GLOBALS ['TYPO3_DB']->sql_select_db(TYPO3_db);
	}

	/**
	 * Import dataset into test database
	 *
	 * @example $this->importDataSet('/fixtures/__FILENAME__.xml');
	 * @param string $pathToFile The path beginning from the current location of the testcase
	 * @access protected
	 * @return void
	 */
	protected function importDataSet($pathToFile) {
		parent::importDataSet(dirname ( __FILE__ ) . $pathToFile);
	}

	/**
	 * Verify the instanceof Repository is of type "tx_l10nmgr_service_importTranslation"
	 *
	 * @access public
	 * @return void
	 */
	public function test_repositoryRightInstanceOf() {
		$this->assertTrue(($this->TranslationService instanceof tx_l10nmgr_service_importTranslation),'Object of wrong class');
	}

	/**
	 * first basicTest
	 *
	 * @access public
	 * @return void
	 */
	public function test_testCaseName() {

		$this->importDataSet('/fixtures/canImportServiceImportCorrectData_pages.xml');
		$this->importDataSet('/fixtures/canImportServiceImportCorrectData_tt_content.xml');
		$this->importDataSet('/fixtures/canImportServiceImportCorrectData_language.xml');
		$this->importDataSet('/fixtures/templavoilaTemplateDatastructure.xml');
		$this->importDataSet('/fixtures/templavoilaTemplateObject.xml');

//		$export = dirname(__FILE__) . '/fixtures/canImportServiceImportCorrectDataFixtureExport.xml';
		$import = dirname(__FILE__) . '/fixtures/canImportServiceImportCorrectDataFixtureImport.xml';

		$TranslationData = $this->TranslationFactory->create($import);

		$ExportDataRepository = new tx_l10nmgr_models_exporter_exportDataRepository();
		$ExportData           = $ExportDataRepository->findById($TranslationData->getExportDataRecordUid());

		$translateableFactoryDataProvider = new tx_l10nmgr_models_translateable_typo3TranslateableFactoryDataProvider($ExportData, $TranslationData->getPageIdCollection());
		$TranslatableInformation		  = $this->TranslatableFactory->create($translateableFactoryDataProvider);

		$this->TranslationService->save($TranslatableInformation, $TranslationData);

		/**
		 * expected result
		 * - two new page_language_overlay records
		 * - two new content element overlay records
		 */
		$this->fail('Implement this test');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/tests/service/class.tx_l10nmgr_service_importTranslation_basic_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/tests/service/class.tx_l10nmgr_service_importTranslation_basic_testcase.php']);
}

?>