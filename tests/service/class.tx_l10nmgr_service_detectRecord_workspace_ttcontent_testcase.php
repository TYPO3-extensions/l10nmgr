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
 * class.tx_l10nmgr_service_detectRecord_workspace_ttcontent_testcase.php
 *
 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.tx_l10nmgr_service_detectRecord_workspace_ttcontent_testcase.php $
 * @date 29.09.2009 11:30:21
 * @see tx_l10nmgr_tests_databaseTestcase
 * @category testcase
 * @package TYPO3
 * @subpackage l10nmgr
 * @access public
 */
class tx_l10nmgr_service_detectRecord_workspace_ttcontent_testcase extends tx_l10nmgr_tests_databaseTestcase {

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
		$import = array ('cms','l10nmgr');
		$optional = array('static_info_tables','templavoila','realurl','aoe_realurlpath','languagevisibility','cc_devlog');
		foreach($optional as $ext) {
			if (t3lib_extMgm::isLoaded($ext)) {
				$import[] = $ext;
			}
		}
		$this->importExtensions($import);
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
	 * When a workspace element will be imported into the workspace a
	 * identity key with a workspace uid should be verified. Therefore
	 * the verified key should be the same with a given workspace id.
	 *
	 * @access public
	 * @return void
	 *
	 * @author Michael Klapper & Timo Schmidt
	 */
	public function verifyIdentityKeyOfWorkspaceContentElement(){
		$this->importDataSet('/service/fixtures/detectRecord/draftWorkspace/pages.xml');
		$this->importDataSet('/service/fixtures/detectRecord/draftWorkspace/ttcontent.xml');
		$this->importDataSet('/service/fixtures/detectRecord/draftWorkspace/language.xml');

		//the given key contains a uid of a workspace overlay	tt_content:667982:title
		$currentIdentityKey 		= 'tt_content:619942:title';
		$localisationParentRecord 	= 619945;
		$forceTargetLanguageUid   = 3;

		//the verification method should verify the key if it exists
		$this->DetectRecordService->setWorkspaceId(131);
		$newIdentityKey = $this->DetectRecordService->verifyIdentityKey($currentIdentityKey, $forceTargetLanguageUid, $localisationParentRecord);

		//the identityKey should not be changed
		$this->assertEquals(	$newIdentityKey,
								$currentIdentityKey,
								'Could not verify identityKey of workspace content element.'	);
	}

	/**
	 * @test
	 *
	 * This test is used to check if the verifyIdentityKey returns the key of
	 * the live record, when no context workspace if configured for
	 * the detectionService (with setWorkspaceId()).
	 *
	 * @access public
	 * @return void
	 *
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 */
	public function verifyIdentityKeyReturnsLiveIdentityKeyForWorkspaceElementWithoutWorkspaceContext(){
		$this->importDataSet('/service/fixtures/detectRecord/draftWorkspace/pages.xml');
		$this->importDataSet('/service/fixtures/detectRecord/draftWorkspace/ttcontent.xml');
		$this->importDataSet('/service/fixtures/detectRecord/draftWorkspace/language.xml');

		//the given key contains a uid of a workspace overlay	tt_content:667982:title
		$currentIdentityKey 		= 'tt_content:619942:title';
		$expectedIdentityKey 		= 'tt_content:619941:title';
		$localisationParentRecord 	= 619945;
		$forceTargetLanguageUid   = 3;

		$newIdentityKey = $this->DetectRecordService->verifyIdentityKey($currentIdentityKey, $forceTargetLanguageUid, $localisationParentRecord);

		//the identityKey should not be changed
		$this->assertEquals(	$newIdentityKey,
								$expectedIdentityKey,
								'Could not retrieve live identity key from workspace record'	);
	}

	/**
	 * @test
	 *
	 * This testcase is used to check the a contextWorkspace of the backend user
	 * ($GLOBALS['BE_USER'] does not influence the detection service, when it
	 * is used in a diffrent workspace context.
	 *
	 * @access public
	 * @return void
	 *
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 */
	public function wrongWorkspaceContextDoesNotInfluenceIdentityKeyOfWorkspaceRecord(){
		$oldWorkspaceId = 	$GLOBALS['BE_USER']->user['workspace_id'];
		$oldWorkspace 	=	$GLOBALS['BE_USER']->workspace;

		$GLOBALS['BE_USER']->user['workspace_id'] 	= 999;
		$GLOBALS['BE_USER']->workspace				= 999;

		$this->importDataSet('/service/fixtures/detectRecord/draftWorkspace/pages.xml');
		$this->importDataSet('/service/fixtures/detectRecord/draftWorkspace/ttcontent.xml');
		$this->importDataSet('/service/fixtures/detectRecord/draftWorkspace/language.xml');

		//the given key contains a uid of a workspace overlay	tt_content:667982:title
		$currentIdentityKey 		= 'tt_content:619942:title';
		$localisationParentRecord 	= 619945;
		$forceTargetLanguageUid   = 3;

		//the verification method should verify the key if it exists
		$this->DetectRecordService->setWorkspaceId(131);
		$newIdentityKey = $this->DetectRecordService->verifyIdentityKey($currentIdentityKey, $forceTargetLanguageUid, $localisationParentRecord);

		//the identityKey should not be changed
		$this->assertEquals(	$newIdentityKey,
								$currentIdentityKey,
								'Determined wrong identityKey in workspace context' );

		$GLOBALS['BE_USER']->user['workspace_id'] 	= $oldWorkspaceId;
		$GLOBALS['BE_USER']->workspace				= $oldWorkspace;
	}

	/**
	 * @test
	 *
	 * This testcase is used to test that the detectionService even works with
	 * a workspaceId which has been set to zero (0)
	 *
	 * @access public
	 * @return void
	 *
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 */
	public function verifyIdentityKeyWorksWithZeroWorkspace(){
		$this->importDataSet('/service/fixtures/detectRecord/draftWorkspace/pages.xml');
		$this->importDataSet('/service/fixtures/detectRecord/draftWorkspace/ttcontent.xml');
		$this->importDataSet('/service/fixtures/detectRecord/draftWorkspace/language.xml');

		//identity key of the live record
		$currentIdentityKey 		= 'tt_content:619941:title';
		$localisationParentRecord 	= 619945;
		$forceTargetLanguageUid   = 3;

		$this->DetectRecordService->setWorkspaceId(0);
		$newIdentityKey = $this->DetectRecordService->verifyIdentityKey($currentIdentityKey, $forceTargetLanguageUid, $localisationParentRecord);

		//the identityKey should not be changed
		$this->assertEquals(	$newIdentityKey,
								$currentIdentityKey,
								'Determined wrong identityKey with workspace set to zero'	);
	}

	/**
	 * @test
	 *
	 * This testcase is used to check that the record detection service returns
	 * the workspace identity key for a live workspace identity key if the
	 * detection service has been initialized with a workspace.
	 *
	 * @access public
	 * @return void
	 *
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 */
	public function verifyIdentityKeyRetrievesWorkspaceKeyForLiveIdentityKeyInWorkspaceContext(){
		$this->importDataSet('/service/fixtures/detectRecord/draftWorkspace/pages.xml');
		$this->importDataSet('/service/fixtures/detectRecord/draftWorkspace/ttcontent.xml');
		$this->importDataSet('/service/fixtures/detectRecord/draftWorkspace/language.xml');

		$currentIdentityKey 		= 'tt_content:619941:title';
		$workspaceIdentityKey		= 'tt_content:619942:title';

		$localisationParentRecord 	= 619945;
		$forceTargetLanguageUid   = 3;

		$this->DetectRecordService->setWorkspaceId(131);
		$newIdentityKey = $this->DetectRecordService->verifyIdentityKey($currentIdentityKey, $forceTargetLanguageUid, $localisationParentRecord);

		$this->assertEquals(	$newIdentityKey,
								$workspaceIdentityKey,
								'Record detection service does not determine workspace identity key'	);
	}
}
?>