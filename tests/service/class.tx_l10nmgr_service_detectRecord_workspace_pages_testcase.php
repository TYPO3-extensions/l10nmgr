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
 * class.tx_l10nmgr_service_detectRecord_workspace_pages_testcase.php
 *
 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.tx_l10nmgr_service_detectRecord_workspace_pages_testcase.php $
 * @date 29.09.2009 11:30:21
 * @see tx_l10nmgr_tests_database_testcase
 * @category testcase
 * @package TYPO3
 * @subpackage l10nmgr
 * @access public
 */
class tx_l10nmgr_service_detectRecord_workspace_pages_testcase extends tx_l10nmgr_tests_database_testcase {

	/**
	 * Creates the test environment.
	 *
	 * @access public
	 * @return void
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function setUp() {
		$this->createDatabase();
		$db = $this->useTestDatabase();

		$this->importStdDB();

			// order of extension-loading is important !!!!
		$this->importExtensions(array ('cms','l10nmgr','static_info_tables','templavoila','realurl','aoe_realurlpath','languagevisibility','cc_devlog'));
		$this->DetectRecordService = t3lib_div::makeInstance('tx_l10nmgr_service_detectRecord');
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

   		$this->DetectRecordService = null;
	}

	/**
	 * @test
	 *
	 * This testcase is used to check that a pages key will be verified to a pages_languages_overlay
	 * key with a workspace uid, when the record detection service will be initialized with a workspace id.
	 *
	 * @access public
	 * @return void
	 *
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 */
	public function verifyPageKeyResultsInWorkspacePagesLanguageOverlayIdentityKey(){
		$this->markTestSkipped('This testcase can be ignored since this can currently not happen in an export');

		$this->importDataSet('/service/fixtures/detectRecord/draftWorkspace/pages.xml');
		$this->importDataSet('/service/fixtures/detectRecord/draftWorkspace/ttcontent.xml');
		$this->importDataSet('/service/fixtures/detectRecord/draftWorkspace/language.xml');

		$liveWsIdentityKey 		= 'pages:33155:title';
		$expectedWsIdentityKey	= 'pages_language_overlay:486:title';

		$localisationParentRecord 	= 33155;
		$forceTargetLanguageUid  	= 1;

		$this->DetectRecordService->setWorkspaceId(131);
		$newIdentityKey = $this->DetectRecordService->verifyIdentityKey($liveWsIdentityKey, $forceTargetLanguageUid, $localisationParentRecord);

		$this->assertEquals($newIdentityKey,$expectedWsIdentityKey,'Record detection service does not determine workspace identity key');
	}

	/**
	 * @test
	 *
	 * When the detection service will be called with an overlay identity key and a workspace context and
	 * an workspace version for this overlay exists, the identity key should be corrected to
	 * the identity key of the workspace version.
	 *
	 * @access public
	 * @return void
	 *
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 */
	public function verifyPageWithOverlayAndExistingWorkspaceVersionReturnsWorkspaceIdentityKey(){
		$this->importDataSet('/service/fixtures/detectRecord/draftWorkspace/pages.xml');
		$this->importDataSet('/service/fixtures/detectRecord/draftWorkspace/ttcontent.xml');
		$this->importDataSet('/service/fixtures/detectRecord/draftWorkspace/language.xml');

		$liveWsIdentityKey 		= 'pages_language_overlay:485:title';
		$expectedWsIdentityKey	= 'pages_language_overlay:486:title';

		$localisationParentRecord 	= 33155;
		$forceTargetLanguageUid  	= 1;

		$this->DetectRecordService->setWorkspaceId(131);
		$newIdentityKey = $this->DetectRecordService->verifyIdentityKey($liveWsIdentityKey, $forceTargetLanguageUid, $localisationParentRecord);

		$this->assertEquals($newIdentityKey,$expectedWsIdentityKey,'Record detection service does not determine workspace identity key');
	}

	/**
	 * @test
	 *
	 * This method is used to check that a identityKey of a pages_language_overlay record
	 * will be verified an returned.
	 *
	 * @access public
	 * @return void
	 *
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 */
	public function verifyOverlayIdentityStringWillBeVerified(){
		$this->importDataSet('/service/fixtures/detectRecord/draftWorkspace/pages.xml');
		$this->importDataSet('/service/fixtures/detectRecord/draftWorkspace/ttcontent.xml');
		$this->importDataSet('/service/fixtures/detectRecord/draftWorkspace/language.xml');

		$workspaceWsIdentityKey = 'pages_language_overlay:486:title';
		$expectedWsIdentityKey	= 'pages_language_overlay:486:title';

		$localisationParentRecord 	= 33155;
		$forceTargetLanguageUid  	= 1;

		$this->DetectRecordService->setWorkspaceId(131);
		$newIdentityKey = $this->DetectRecordService->verifyIdentityKey($workspaceWsIdentityKey, $forceTargetLanguageUid, $localisationParentRecord);

		$this->assertEquals($newIdentityKey,$expectedWsIdentityKey,'Record detection service does not determine workspace identity key');
	}
}
?>