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
 * This testcase is used to test a complete localisation workflow
 * with the l10nmgr.
 *
 * class.tx_l10nmgr_mixed_completeWorkflow_testcase.php
 *
 * @author Timo Schmidt <schmidt@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.classname.php $
 * @date 07.05.2009 15:44:40
 * @seetx_l10nmgr_tests_databaseTestcase
 * @category testcase
 * @package TYPO3
 * @subpackage l10nmgr
 * @access public
 */

class tx_l10nmgr_mixed_completeWorkflow_testcase extends tx_l10nmgr_tests_databaseTestcase {

	/**
	 * Creates the test environment.
	 *
	 */
	function setUp() {
		$this->skipInWrongWorkspaceContext();


		$this->unregisterIndexedSearchHooks();

		$this->createDatabase();
		$db = $this->useTestDatabase();

		$GLOBALS['TYPO3_DB']->debugOutput = TRUE;
		$this->importStdDB();

			// order of extension-loading is important !!!!
		$import = array ('cms','l10nmgr');
		$optional = array('static_info_tables','templavoila','realurl','aoe_realurlpath','languagevisibility','cc_devlog');
		foreach($optional as $ext) {
			if (t3lib_extMgm::isLoaded($ext)) {
				$import[] = $ext;
			}
		}
		t3lib_div::loadTCA('tx_l10nmgr_importfiles');
	}

	/**
	 * Resets the test enviroment after the test.
	 */
	function tearDown() {
		$this->cleanDatabase();
   		$this->dropDatabase();
   		$GLOBALS['TYPO3_DB']->sql_select_db(TYPO3_db);
		sleep(2);

   		$this->restoreIndexedSearchHooks();
	}

	/**
	 * This testcase should check that the importer creates no empty line (<p>&nbsp;</p> after importing
	 * an element with a heading (<h2>) in the bodytext from a catxml import file.
	 *
	 * @test
	 */
	public function importerDoesNotCreateEmptyRowAfterHeadingInCATXML(){
		$this->helper_importerDoesNotCreatesEmptyRowAfterHeading('xml');
	}

	/**
	 * This testcase should check that the importer creates no empty line (<p>&nbsp;</p> after importing
	 * an element with a heading (<h2>) in the bodytext from a excel import file.
	 *
	 * @test
	 */
	public function importerDoesNotCreateEmptyRowAfterHeadingInExcel(){
		$this->helper_importerDoesNotCreatesEmptyRowAfterHeading('xls');
	}


	/**
	 * This helper method will be triggered from the testcase methods to ensure that the
	 * importer does not create empty rows after heading.
	 *
	 * @todo use vfs:// instead of real filesystem
	 *
	 * @param string format
	 */
	protected function helper_importerDoesNotCreatesEmptyRowAfterHeading($format){
		$this->importDataSet('/mixed/fixtures/emptyLineAfterHeading/pages.xml');
		$this->importDataSet('/mixed/fixtures/emptyLineAfterHeading/ttcontent.xml');
		$this->importDataSet('/mixed/fixtures/emptyLineAfterHeading/exportdata.xml');
		$this->importDataSet('/mixed/fixtures/emptyLineAfterHeading/language.xml');
		$this->importDataSet('/mixed/fixtures/emptyLineAfterHeading/l10nconfiguration.xml');

		$GLOBALS['TCA']['tx_l10nmgr_importfiles']['columns']['filename']['config']['uploadfolder'] = t3lib_extMgm::extPath('l10nmgr').'tests/mixed/fixtures/emptyLineAfterHeading/import';

		$exportDataRepository 	= new tx_l10nmgr_domain_exporter_exportDataRepository();
		$exportData 			= $exportDataRepository->findById(67);
		$exportData->setExport_type($format);

		$exporter 				= new tx_l10nmgr_domain_exporter_exporter($exportData,1,$exportData->getInitializedExportView());
		$exporter->run();

		$exportedResult 		= $exporter->getResultForChunk();

		##
		# WRITE EXPORT TO FILE
		##
		$fileImportPath 		= 	t3lib_extMgm::extPath('l10nmgr').'tests/mixed/fixtures/emptyLineAfterHeading/import';
		$tempfile 				= $fileImportPath.'/temp.xml';

		$this->removeDirectoryAndContent($fileImportPath);
		mkdir($fileImportPath,0777);

		$exportedResult = str_replace('anywhere','anywhere translated@', $exportedResult);
		file_put_contents($tempfile,$exportedResult);
		unset($exportedResult);
		###
		# RUN IMPORT
		###
		$importData	= new tx_l10nmgr_domain_importer_importData();
		$importData->setConfiguration_id(384);
		$importData->setExportdata_id(67);
		$importData->setImport_type($format);
		$importDataRepository = new tx_l10nmgr_domain_importer_importDataRepository();
		$importDataRepository->add($importData);
		$importFile = new tx_l10nmgr_domain_importer_importFile();
		$importFile->setFilename('temp.xml');
		$importFile->setImportdata_id($importData->getUid());
		$importFileRepository = new tx_l10nmgr_domain_importer_importFileRepository();
		$importFileRepository->add($importFile);

		$importer 	= new tx_l10nmgr_domain_importer_importer($importData);
		$res 		= $importer->run();
		$this->assertTrue($res, 'Import seems to work incorrect ');

		$row 			= t3lib_beFunc::getRecord('tt_content',619945);
		$contentOverlay = tx_mvc_system_dbtools::getTYPO3RowOverlay($row, 'tt_content', 2);

		$expectedResult = 	'<h2>WebEx is an easy way to exchange ideas and information with anyone, anywhere translated@.</h2>'."\n".
							'It combines real-time desktop sharing with phone conferencing so everyone sees the same thing as you talk. It\'s far more productive than emailing files then struggling to get everyone on the same page in a phone conference. And, many times it eliminates the need for people to travel and meet on site.<br /><br /><link http://www.webex.com/go/buy_webex>Buy WebEx now</link>. WebEx is available for as low as<br />$59/mo for unlimited online meetings.'."\n".
							'<link http://www.webex.com/go/webex_ft>Take a free trial</link>. Get started now with a risk free 14-day<br />trial of WebEx.';

		$this->assertEquals($contentOverlay['bodytext'],$expectedResult,'unexpected import result');

		unset($contentOverlay);
		unset($importData);
		unset($importFileRepository);
		unset($importDataRepository);
		unset($row);
	}

	/**
	 * This method is a wrapper for the complete workflow test. It
	 * starts the complete workflow test for the xml format.
	 *
	 * @test
	 * @return void
	 */
	public function completeLocalisationWorkflowCATXML(){
		$this->helper_testCompleteLocalisationWorkflow('xml');
	}

	/**
	 * This is a wrapper for the complete workflow test, to
	 * start it for the excel format.
	 *
	 * @test
	 * @return void
	 */
	public function completeLocalisationWorkflowExcel(){
		$this->helper_testCompleteLocalisationWorkflow('xls');
	}

	/**
	 *
	 * @test
	 * @return void
	 */
	public function completeLocalisationWorkflowCATXMLWithImportIntoWorkspace(){
		$this->markTestIncomplete('This test is under construction.');
		$this->markTestSkipped('incomplete testcase');

		$this->importDataSet('/mixed/fixtures/completeWorkflow/workspace.xml');
		$this->helper_testCompleteLocalisationWorkflow('xml',142);

	}

	/**
	* The base for this testcase is the following structure:
 	*
 	* <ul>
 	* 	<li>page 1 (Testpage l10nmgr uid: 33153). This page cotains the following content elements:
 	* 		<ul>
 	* 			<li>tt_content uid 619634: content element with some special characters </li>
 	* 			<li>tt_content uid 619637: fce - grid element the fce grid element is a container element for other content elements</li>
 	*			<li>tt_content uid 619636: text content element (nested in the fce grid 619637)
 	* 		</ul>
 	*
 	*  </li>
 	*
 	*  <li>page 2 (Subpage of page 1 -> Testpage l10nmgr 33154):</li>
	* </ul>
	*
	* @todo use vfs:// instead of real filesystem
	*
	* @author Timo Schmidt <timo.schmidt@aoemedia.de>
	* @param void
	* @return void
	*/
	protected function helper_testCompleteLocalisationWorkflow($format,$workspaceContext = null){
		$GLOBALS['TCA']['tx_l10nmgr_importfiles']['columns']['filename']['config']['uploadfolder'] = t3lib_extMgm::extPath('l10nmgr').'tests/mixed/fixtures/completeWorkflow/import';
		$GLOBALS['TCA']['tx_l10nmgr_exportfiles']['columns']['filename']['config']['uploadfolder'] = t3lib_extMgm::extPath('l10nmgr').'tests/mixed/fixtures/completeWorkflow/export';
		$GLOBALS['TCA']['tx_l10nmgr_exportdata']['columns']['filename']['config']['uploadfolder'] = t3lib_extMgm::extPath('l10nmgr').'tests/mixed/fixtures/completeWorkflow/export/zip';

		$this->importDataSet('/mixed/fixtures/completeWorkflow/pages.xml');
		$this->importDataSet('/mixed/fixtures/completeWorkflow/ttcontent.xml');
		$this->importDataSet('/mixed/fixtures/completeWorkflow/exportdata.xml');
		$this->importDataSet('/mixed/fixtures/completeWorkflow/l10nconfiguration.xml');
		$this->importDataSet('/mixed/fixtures/completeWorkflow/languages.xml');
		$this->importDataSet('/mixed/fixtures/completeWorkflow/templavoila_data_structures.xml');
		$this->importDataSet('/mixed/fixtures/completeWorkflow/templavoila_template_objects.xml');
		//retrieve fixture exportData
		$exportDataRepository 	= new tx_l10nmgr_domain_exporter_exportDataRepository();

		/** @var tx_l10nmgr_domain_exporter_exportData */
		$exportData				= $exportDataRepository->findById(97);
		$exportData->setExport_type($format);

		//create export folders
		$fileExportPath = 	t3lib_extMgm::extPath('l10nmgr').'tests/mixed/fixtures/completeWorkflow/export';
		$zipExportPath	=	t3lib_extMgm::extPath('l10nmgr').'tests/mixed/fixtures/completeWorkflow/export/zip';

		//create import folders
		$fileImportPath = 	t3lib_extMgm::extPath('l10nmgr').'tests/mixed/fixtures/completeWorkflow/import';
		$zipImportPath	=	t3lib_extMgm::extPath('l10nmgr').'tests/mixed/fixtures/completeWorkflow/import/zip';

		//ensure that the directory do not exists
		$this->removeDirectoryAndContent($zipExportPath);
		$this->removeDirectoryAndContent($fileExportPath);
		$this->removeDirectoryAndContent($zipImportPath);
		$this->removeDirectoryAndContent($fileImportPath);

		t3lib_div::mkdir($fileExportPath);
		t3lib_div::mkdir($zipExportPath);
		t3lib_div::mkdir($fileImportPath);
		t3lib_div::mkdir($zipImportPath);

		$this->assertFileExists($fileExportPath);
		$this->assertFileExists($zipExportPath);

		$runcountExport = 1;
		//invoke export service to performExport
		while(!tx_l10nmgr_domain_exporter_exporter::performFileExportRun($exportData,1)){
			//exporting
			$runcountExport++;
		}

		//is the number of runs correct?
		$this->assertEquals(3,$runcountExport,'Unexpected number of exportRuns');

		$this->replaceContentInExportFiles($exportData, $fileExportPath, $fileImportPath);



		//if we have a workspace context configured, we switch to the workspace to import
		//the data into the workspace
		if(!is_null($workspaceContext )){
			$currentWorkspace 	= $GLOBALS['BE_USER']->workspace;
			$currentWorkspaceId = $BE_USER->user['workspace_id'];

			$GLOBALS['BE_USER']->workspace = $workspaceContext;
			$GLOBALS['BE_USER']->user['workspace_id'] = $workspaceContext;
		}

		$importData = $this->createFixtureImportDataWithImportFiles($exportData, $fileImportPath);
		$importData->setImport_type($format);

		$runcountImport = 1;
		while(!tx_l10nmgr_domain_importer_importer::performImportRun($importData)){
			$runcountImport++;
		}

		$this->assertEquals($runcountExport, ($runcountImport+1),'The import should have the same runcount as the export');

		//now check that there are overlays for the exported records with the correct translation settings
		$this->removeDirectoryAndContent($zipExportPath);
		$this->removeDirectoryAndContent($fileExportPath);

		//get overlay for 619634
		$row 			= t3lib_beFunc::getRecord('tt_content',619634);
		$contentOverlay = tx_mvc_system_dbtools::getTYPO3RowOverlay($row, 'tt_content', 1,$workspaceContext);

		//header
		$this->assertEquals($contentOverlay['header'],'@translated Content element with typolink translated@','No correct translation for header found');

		//bodytext
		$expectedBodytextResult = "@translated This is a test! translated@&nbsp;\n\na b c&nbsp;\n</data>\n!\"§$%&/()=?*+#'-_.:,;\n<link 24421>Typolink</link>\n";

		$this->assertEquals($contentOverlay['bodytext'],$expectedBodytextResult,'In expected result after import');

		//restore the original workspace context
		if(!is_null($workspaceContext)){
			$GLOBALS['BE_USER']->user['workspace_id'] = 0;
			$GLOBALS['BE_USER']->workspace = 0;
		}
	}

	/**
	* The base for this testcase is the following structure:
 	*
 	* <ul>
 	* 	<li>page 1 (Testpage l10nmgr uid: 33153). This page cotains the following content elements:
 	* 		<ul>
 	* 			<li>tt_content uid 619634: content element with some special characters </li>
 	* 			<li>tt_content uid 619637: fce - grid element the fce grid element is a container element for other content elements</li>
 	*			<li>tt_content uid 619636: text content element (nested in the fce grid 619637)
 	* 		</ul>
 	*
 	*  </li>
 	*
 	*  <li>page 2 (Subpage of page 1 -> Testpage l10nmgr 33154):</li>
	* </ul>
	*
	* @todo use vfs:// instead of real filesystem
	*
	* @test
	* @author Tolleiv Nietsch <tolleiv.nietsch@aoemedia.de
	* @param void
	* @return void
	*
	*/
	public function completeLocalisationWorkflowWithInvalidXMLinExport(){


		$basePath = t3lib_extMgm::extPath('l10nmgr').'tests/mixed/fixtures/completeLocalisationWorkflowWithInvalidXMLinExport/';
		$GLOBALS['TCA']['tx_l10nmgr_importfiles']['columns']['filename']['config']['uploadfolder'] = $basePath.'import';
		$GLOBALS['TCA']['tx_l10nmgr_exportfiles']['columns']['filename']['config']['uploadfolder'] = $basePath.'export';
		$GLOBALS['TCA']['tx_l10nmgr_exportdata']['columns']['filename']['config']['uploadfolder'] = $basePath.'export/zip';

		$this->importDataSet('/mixed/fixtures/completeLocalisationWorkflowWithInvalidXMLinExport/pages.xml');
		$this->importDataSet('/mixed/fixtures/completeLocalisationWorkflowWithInvalidXMLinExport/ttcontent.xml');
		$this->importDataSet('/mixed/fixtures/completeLocalisationWorkflowWithInvalidXMLinExport/exportdata.xml');
		$this->importDataSet('/mixed/fixtures/completeLocalisationWorkflowWithInvalidXMLinExport/l10nconfiguration.xml');
		$this->importDataSet('/mixed/fixtures/completeLocalisationWorkflowWithInvalidXMLinExport/languages.xml');
//		$this->importDataSet('/mixed/fixtures/completeLocalisationWorkflowWithInvalidXMLinExport/templavoila_data_structures.xml');
//		$this->importDataSet('/mixed/fixtures/completeLocalisationWorkflowWithInvalidXMLinExport/templavoila_template_objects.xml');
		//retrieve fixture exportData
		$exportDataRepository 	= new tx_l10nmgr_domain_exporter_exportDataRepository();

		/** @var tx_l10nmgr_domain_exporter_exportData */
		$exportData				= $exportDataRepository->findById(97);


		//create export folders
		$fileExportPath = 	$basePath.'export';
		$zipExportPath	=	$basePath.'export/zip';

		//create import folders
		$fileImportPath = 	$basePath.'import';
		$zipImportPath	=	$basePath.'import/zip';


		//ensure that the directory do not exists
		$this->removeDirectoryAndContent($zipExportPath);
		$this->removeDirectoryAndContent($fileExportPath);
		$this->removeDirectoryAndContent($zipImportPath);
		$this->removeDirectoryAndContent($fileImportPath);

		mkdir($fileExportPath,0777);
		mkdir($zipExportPath,0777);
		mkdir($fileImportPath,0777);
		mkdir($zipImportPath,0777);

		$this->assertFileExists($fileExportPath);
		$this->assertFileExists($zipExportPath);

		$runcountExport = 1;
		$runcountImport = 2;
		//invoke export service to performExport
		while(!tx_l10nmgr_domain_exporter_exporter::performFileExportRun($exportData,1,$zipExportPath,$fileExportPath)){
			//exporting
			$runcountExport++;
		}

		// is the number of runs correct?
		$this->assertEquals($runcountImport,$runcountExport,'Unexpected number of exportRuns');

		$this->prepareImportFilesFromExportData($exportData, $fileExportPath, $fileImportPath);

		$importData = $this->createFixtureImportDataWithImportFile($exportData);

		$runcountImport = 1;
		$runcountExport = 1;
		while(!tx_l10nmgr_domain_importer_importer::performImportRun($importData)){
			$runcountImport++;
		}

		$this->assertEquals($runcountExport, $runcountImport,'The import should have the same runcount as the export');


		//now check that there are overlays for the exported records with the correct translation settings
/*
		$this->removeDirectoryAndContent($zipExportPath);
		$this->removeDirectoryAndContent($fileExportPath);
		$this->removeDirectoryAndContent($zipImportPath);
		$this->removeDirectoryAndContent($fileImportPath);
*/

		// Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et &lt;link http://www.aoemedia.de/ - - linktitle&gt;dolore &lt;/link&gt;magna aliquyam erat, sed diam voluptua.

		$row 			= t3lib_beFunc::getRecord('tt_content',624071);
		$contentOverlay = tx_mvc_system_dbtools::getTYPO3RowOverlay($row, 'tt_content', 1);
		$expectedResult1 = 'Lorem ipsum <link http://www.aoemedia.de/ - - linktitle>dolore </link> sit amet...';
		$expectedResult2 = 'Lorem ipsum <link http://www.aoemedia.de/ - - "linktitle">dolore </link> sit amet...';
		$this->assertTrue (
			( ($expectedResult1 == $contentOverlay['bodytext']) || ($expectedResult2 == $contentOverlay['bodytext']) ),
			'1 Result after import did not match the expectations'
		);

		$row 			= t3lib_beFunc::getRecord('tt_content',624072);
		$contentOverlay = tx_mvc_system_dbtools::getTYPO3RowOverlay($row, 'tt_content', 1);
		$expectedResult = "<div style=\"color:green\">";
		$this->assertEquals($expectedResult, $contentOverlay['bodytext'],'Result after import did not match the expectations');

		$row 			= t3lib_beFunc::getRecord('tt_content',624074);
		$contentOverlay = tx_mvc_system_dbtools::getTYPO3RowOverlay($row, 'tt_content', 1);
		$expectedResult = "</div>\n<script type=\"text/javascript\">\n/* <![CDATA[ */\nalert('this will cause some trouble within the export data');\n/* ]]> */\n</script> ";
		$this->assertEquals($expectedResult, $contentOverlay['bodytext'],'Result after import did not match the expectations');

		$row 			= t3lib_beFunc::getRecord('tt_content',624075);
		$contentOverlay = tx_mvc_system_dbtools::getTYPO3RowOverlay($row, 'tt_content', 1);
		$expectedResult = "Strange <span> span</span> within headline";
		$this->assertEquals($expectedResult, $contentOverlay['header'],'Result after import did not match the expectations data[['.base64_encode($contentOverlay['header']).']] expected[['.base64_encode($expectedResult).']]');

	}

	/**
	 * This helpermethod is used to replace
	 *
	 * @param tx_l10nmgr_domain_exporter_exportData $exportData
	 * @param string fileExportPath
	 * @param string fileImportPath
	 */
	protected function replaceContentInExportFiles($exportData, $fileExportPath,$fileImportPath){
		//retrieve the exported files
		$exportFiles 	= $exportData->getExportFiles();

		//is the number of files correct and are they on the correct place?
		$exportFile1 = $fileExportPath.'/'.$exportFiles->offsetGet(0)->getFilename();
		$exportFile2 = $fileExportPath.'/'.$exportFiles->offsetGet(1)->getFilename();
		$importFile1 = $fileImportPath.'/'.$exportFiles->offsetGet(0)->getFilename();
		$importFile2 = $fileImportPath.'/'.$exportFiles->offsetGet(1)->getFilename();

		$this->assertEquals($exportFiles->count(),2,'Unexpected number of export files');
		$this->assertFileExists($exportFile1,'First exported file does not exists');
		$this->assertFileExists($exportFile2,'Second exported file does not exists');

		//so some replacements in the export files
		$contentFile1 = file_get_contents($exportFile1);
		$contentFile1	= str_replace('Lorem ipsum','@translated Lorem ipsum translated@',$contentFile1);
		$contentFile1	= str_replace('Testpage l10nmgr','@translated Testpage l10nmgr translated@',$contentFile1);
		$contentFile1	= str_replace('This is a test!','@translated This is a test! translated@',$contentFile1);
		$contentFile1 	= str_replace('Content element with typolink','@translated Content element with typolink translated@',$contentFile1);
		file_put_contents($importFile1,$contentFile1);

		$contentFile2 	= file_get_contents($exportFile2);
		$contentFile2	= str_replace('Original small label','@translated Original small label translated@',$contentFile2);
		$contentFile2	= str_replace('CTA Button header','@translated CTA Button header translated@',$contentFile2);
		file_put_contents($importFile2,$contentFile2);

	}

	/**
	 * This private method is used to create an importData and Importfile records for the imported
	 * files.
	 *
	 * @param tx_l10nmgr_domain_exporter_exportData
	 * @return tx_l10nmgr_domain_importer_importData
	 */
	protected function createFixtureImportDataWithImportFiles($exportData,$format='xml'){
		$exportFiles 	= $exportData->getExportFiles();
		$exportFile1 = $exportFiles->offsetGet(0)->getFilename();
		$exportFile2 = $exportFiles->offsetGet(1)->getFilename();


		$importData = new tx_l10nmgr_domain_importer_importData();
		$importData->setExportdata_id($exportData->getUid());
		$importData->setConfiguration_id(0);
		$importData->setImport_type($format);

		$importDataRepository = new tx_l10nmgr_domain_importer_importDataRepository();
		$importDataRepository->add($importData);

		$importFile1 = new tx_l10nmgr_domain_importer_importFile();
		$importFile1->setFilename($exportFile1);
		$importFile1->setImportdata_id($importData->getUid());

		$importFile2 = new tx_l10nmgr_domain_importer_importFile();
		$importFile2->setFilename($exportFile2);
		$importFile2->setImportdata_id($importData->getUid());


		$importFileRepository = new tx_l10nmgr_domain_importer_importFileRepository();
		$importFileRepository->add($importFile1);
		$importFileRepository->add($importFile2);

		return $importData;
	}

	/**
	 * This helpermethod is used to replace
	 *
	 * @param tx_l10nmgr_domain_exporter_exportData $exportData
	 * @param string fileExportPath
	 * @param string fileImportPath
	 */
	protected function prepareImportFilesFromExportData($exportData, $fileExportPath,$fileImportPath){
		//retrieve the exported files
		$exportFiles 	= $exportData->getExportFiles();

		//is the number of files correct and are they on the correct place?
		$exportFile1 = $fileExportPath.'/'.$exportFiles->offsetGet(0)->getFilename();
		$importFile1 = $fileImportPath.'/'.$exportFiles->offsetGet(0)->getFilename();

		$this->assertEquals($exportFiles->count(),1,'Unexpected number of export files');
		$this->assertFileExists($exportFile1,'First exported file does not exists');;

		//so some replacements in the export files
		$contentFile1 = file_get_contents($exportFile1);
//		$contentFile1	= str_replace('Lorem ipsum','@translated Lorem ipsum translated@',$contentFile1);
//		$contentFile1	= str_replace('Testpage l10nmgr','@translated Testpage l10nmgr translated@',$contentFile1);
//		$contentFile1	= str_replace('This is a test!','@translated This is a test! translated@',$contentFile1);
//		$contentFile1 	= str_replace('Content element with typolink','@translated Content element with typolink translated@',$contentFile1);
		file_put_contents($importFile1,$contentFile1);
	}


	/**
	 * This private method is used to create an importData and Importfile records for the imported
	 * files.
	 *
	 * @param tx_l10nmgr_domain_exporter_exportData
	 * @return tx_l10nmgr_domain_importer_importData
	 */
	protected function createFixtureImportDataWithImportFile($exportData,$format = 'xml'){
		$exportFiles = $exportData->getExportFiles();
		$importData = new tx_l10nmgr_domain_importer_importData();
		$importData->setExportdata_id($exportData->getUid());
		$importData->setConfiguration_id(0);
		$importData->setImport_type($format);
		$importDataRepository = new tx_l10nmgr_domain_importer_importDataRepository();
		$importDataRepository->add($importData);

		$importFile1 = new tx_l10nmgr_domain_importer_importFile();
		$importFile1->setFilename($exportFiles->offsetGet(0)->getFilename());
		$importFile1->setImportdata_id($importData->getUid());

		$importFileRepository = new tx_l10nmgr_domain_importer_importFileRepository();
		$importFileRepository->add($importFile1);

		return $importData;
	}

	/**
	 * Deletes a directory and all of its content.
	 *
	 * @param string path
	 */
	protected function removeDirectoryAndContent($path){
		if(!empty($path) && is_dir($path)){
			 for($it = new RecursiveDirectoryIterator($path); $it->valid(); $it->next()){
				if($it->current()->isDir()){
					$directorys[] 	= $it->current()->getPath();
				}elseif($it->current()->isFile()){
					$files[] 		= $it->current()->getPathname().'';
				}
			 }
		}

		if(is_array($files)){
			foreach(array_reverse($files) as $file){
				unlink($file);
			}
		}

		if(is_array($directorys)){
			foreach(array_reverse($directorys) as $directory){
				rmdir($directory);
			}
		}
	}
}
?>