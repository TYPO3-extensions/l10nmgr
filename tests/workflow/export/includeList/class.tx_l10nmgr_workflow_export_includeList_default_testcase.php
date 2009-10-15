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
 *
 * {@inheritdoc}
 *
 * class.tx_l10nmgr_workflow_export_includeList_default_testcase.php
 *
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.tx_l10nmgr_workflow_import_forcedLanguage_default_testcase.php $
 * @date 11.10.2009 11:30:21
 * @seetx_phpunit_database_testcase
 * @category testcase
 * @package TYPO3
 * @subpackage l10nmgr
 * @access public
 */
class tx_l10nmgr_workflow_export_includeList_default_testcase extends tx_phpunit_database_testcase {

	/**
	 * Temporary store for the indexed_search registered HOOKS.
	 *
	 * The hooks must be reset because they produce an side effect on the tests which is not desired.
	 *
	 * @var array
	 */
	private $indexedSearchHook = array();

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
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function setUp() {

			// unset the indexed_search hooks
		if (t3lib_extMgm::isLoaded('indexed_search')) {
			$this->indexedSearchHook['processCmdmapClass']  = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['tx_indexedsearch'];
			$this->indexedSearchHook['processDatamapClass'] = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['tx_indexedsearch'];
			unset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['tx_indexedsearch']);
			unset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['tx_indexedsearch']);
		}

		$this->createDatabase();
		$db = $this->useTestDatabase();

		$this->importStdDB();

			// order of extension-loading is important !!!!
		$this->importExtensions(array ('cms','l10nmgr','static_info_tables','templavoila','realurl','aoe_realurlpath','languagevisibility','cc_devlog'));

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

			// restore the indexed_search hooks
		if (t3lib_extMgm::isLoaded('indexed_search')) {
			$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['tx_indexedsearch']  = $this->indexedSearchHook['processCmdmapClass'];
			$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['tx_indexedsearch'] = $this->indexedSearchHook['processDatamapClass'];
		}
	}

	/**
	 * Import dataset into test database
	 *
	 * This will only work if the fixture locate at the same directory level as the testcase.
	 *
	 * @example $this->importDataSet('/fixtures/__FILENAME__.xml');
	 *
	 * @param string $pathToFile The path beginning from the current location of the testcase
	 *
	 * @access protected
	 * @return void
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	protected function importDataSet($pathToFile) {
		parent::importDataSet(dirname ( __FILE__ ) . $pathToFile);
	}

	/**
	 * @test
	 *
	 * @access public
	 * @return void
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function buildExportFromIncludeListValuesAndRunTwoTimes() {
		$this->importDataset('/fixtures/default/pages.xml');
		$this->importDataset('/fixtures/default/tt_content.xml');
		$this->importDataset('/fixtures/default/sys_language.xml');
		$this->importDataset('/fixtures/default/l10nmgr_cfg.xml');
		$this->importDataset('/fixtures/default/l10nmgr_exportdata.xml');
		$this->importDataset('/fixtures/default/static_languages.xml');

		$ExportDataRepository = new tx_l10nmgr_domain_exporter_exportDataRepository();
		$ExportData = $ExportDataRepository->findById(1); /* @var $ExportData tx_l10nmgr_domain_exporter_exportData */

		$ViewCatXml = $ExportData->getInitializedExportView();
		$Exporter = new tx_l10nmgr_domain_exporter_exporter($ExportData, 5, $ViewCatXml);

		$runCount = 0;
		while ( $Exporter->run() ) {
			$ExportData = $Exporter->getExportData();
			$Exporter   = new tx_l10nmgr_domain_exporter_exporter($ExportData, 5, $ViewCatXml);
			$runCount++;
		}

		$this->assertEquals(
			$runCount,
			2,
			'unexpected number of run counts in export'
		);
	}
}
?>