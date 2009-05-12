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
 * This testcase should be used to determine if the exporter creates a correct exportfile from a given structure
 *
 * {@inheritdoc}
 *
 * class.tx_l10nmgr_model_exporter_export_complex_testcase.php
 *
 * @author Timo Schmidt <schmidt@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.tx_l10nmgr_model_exporter_export_complex_testcase.php $
 * @date 30.04.2009 17:11:48
 * @seetx_phpunit_database_testcase
 * @category testcase
 * @package TYPO3
 * @subpackage l10nmgr
 * @access public
 */

class tx_l10nmgr_model_exporter_export_complex_testcase extends tx_phpunit_database_testcase {
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
	* This testcase should ensure, that the exporter can create a simple export from
	* a given database state.
	*
	* @param void
	* @return void
	* @author Timo Schmidt <timo.schmidt@aoemedia.de>
	*
	* @todo we need to ensure that the result in tests/exporter/fixtures/complex/test__to_pt_BR_300409-113504_export.xml is really the result when "noxmlcheck" in exportData has the value 1, i think it should be the result when "noxmlcheck" has the value 0
	*/
	public function test_canExporterCreateCorrectFileFromGivenStructure(){
		//created without option "do not check xml"

		$this->importDataset(t3lib_extMgm::extPath('l10nmgr') . 'tests/exporter/fixtures/complex/pages.xml');
		$this->importDataset(t3lib_extMgm::extPath('l10nmgr') . 'tests/exporter/fixtures/complex/ttcontent.xml');
		$this->importDataset(t3lib_extMgm::extPath('l10nmgr') . 'tests/exporter/fixtures/complex/language.xml');
		$this->importDataset(t3lib_extMgm::extPath('l10nmgr') . 'tests/exporter/fixtures/complex/l10nconfiguration.xml');
		$this->importDataset(t3lib_extMgm::extPath('l10nmgr') . 'tests/exporter/fixtures/complex/exportdata.xml');

		$expectedOutputfile = t3lib_extMgm::extPath('l10nmgr').'tests/exporter/fixtures/complex/test__to_pt_BR_300409-113504_export.xml';

		$exportdataRepository 	= new tx_l10nmgr_models_exporter_exportDataRepository();
		$exportData				= $exportdataRepository->findById(67);

		$exporter 				= new tx_l10nmgr_models_exporter_exporter($exportData,2,$exportData->getInitializedExportView());

		if($exporter->run()){
			$result	= $exporter->getResultForChunk();
		}

		##
		# Check test results
		##
				echo 'Debug in '.__FILE__.' at line '.__LINE__;
				print('<pre>');
				print_r($result);
				print('</pre>');
		//now we analyse the result of the exporter, it should be valid xml therefore we use simplexml to parse it
        $exporterResult = simplexml_load_string  ($result, 'SimpleXMLElement', LIBXML_NOCDATA );

        //check the iso code of the target language
        $this->assertEquals('PT',(string)$exporterResult->head->t3_targetLang,'Invalid ISO-Code of target language');

        //check the uid of the target language, in the current format this is sysLang
        $this->assertEquals(2,(int) $exporterResult->head->t3_sysLang,'Invalid uid of target language !');

        //check if old an new version determine the same wordCount
        $this->assertEquals(11, (int) $exporterResult->head->t3_wordCount,'Invalid word count in export');

        //this should come from a normal, non cdata tag
		$this->assertEquals((string)$exporterResult->pageGrp->data[0],'headertest');

		//this comes from a cdata tag
		$this->assertEquals((string)$exporterResult->pageGrp->data[1],'This is a <br> dirty header element &amp; uses an ampersand');
	}

	/**
	 * When we do an export of the testdata, change the export and import it, one translation process is done.
	 * If we start an export with only new and changed elemnts then, this export should be empty.
	 * This testcase should simulate this situation.
	 *
	 * @param void
	 * @return void
	 * @author Timo Schmidt
	 *
	 */
	public function test_isExportEmptyAfterReimpoertingExportAndExportingOnlyNewAndChangedElements(){
		$this->importDataset(t3lib_extMgm::extPath('l10nmgr') . 'tests/exporter/fixtures/complex/pages.xml');
		$this->importDataset(t3lib_extMgm::extPath('l10nmgr') . 'tests/exporter/fixtures/complex/ttcontent.xml');
		$this->importDataset(t3lib_extMgm::extPath('l10nmgr') . 'tests/exporter/fixtures/complex/language.xml');
		$this->importDataset(t3lib_extMgm::extPath('l10nmgr') . 'tests/exporter/fixtures/complex/l10nconfiguration.xml');
		$this->importDataset(t3lib_extMgm::extPath('l10nmgr') . 'tests/exporter/fixtures/complex/exportdata.xml');

		$import = t3lib_extMgm::extPath('l10nmgr').'tests/exporter/fixtures/complex/fixture-import.xml';

		$TranslationData = $this->TranslationFactory->create($import);

		$exportDataRepository = new tx_l10nmgr_models_exporter_exportDataRepository();
		$exportData = $exportDataRepository->findById(67);

		$translateableFactoryDataProvider = new tx_l10nmgr_models_translateable_typo3TranslateableFactoryDataProvider($exportData,$TranslationData->getPageIdCollection());
		$TranslatableInformation		  = $this->TranslatableFactory->create($translateableFactoryDataProvider);

		$this->TranslationService->save($TranslatableInformation, $TranslationData);

		$exportdataRepository 	= new tx_l10nmgr_models_exporter_exportDataRepository();
		$exportData				= $exportdataRepository->findById(67);

		//now the export should be empty because we imported content for all new and changed elements and there are no new and changed elements left in the database
		$exporter 				= new tx_l10nmgr_models_exporter_exporter($exportData,2,$exportData->getInitializedExportView());

		if($exporter->run()){
			$result	= $exporter->getResultForChunk();
		}

		$exporterResult = simplexml_load_string  ($result, 'SimpleXMLElement', LIBXML_NOCDATA );

		$this->assertEquals((int)$exporterResult->head->t3_wordCount,0,'There should not be any word in the export because there no new and changed elements left.');
		$this->assertEquals(count($exporterResult->children()),2,'Unexpected number of childnotes in export, there should only be a header in the export');
		$this->assertEquals(count($exporterResult->pageGrp->children()),0,'There should only be one pageGroup without children because there is nothing to translate');
	}

}
?>