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
 * class.tx_l10nmgr_service_detectRecord_complex_pages_testcase.php
 *
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.tx_l10nmgr_service_detectRecord_basic_testcase.php $
 * @date 29.09.2009 11:30:21
 * @seetx_l10nmgr_tests_database_testcase
 * @category testcase
 * @package TYPO3
 * @subpackage l10nmgr
 * @access public
 */
class tx_l10nmgr_service_detectRecord_complex_pages_testcase extends tx_l10nmgr_tests_database_testcase {

	/**
	 * @var tx_l10nmgr_service_detectRecord
	 */
	protected $DetectRecordService = null;

	/**
	 * Creates the test environment.
	 *
	 * @access public
	 * @return void
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function setUp() {
//		global $BE_USER;
//		$this->assertEquals($BE_USER->user['workspace_id'],0,'Run this test only in the live workspace' );

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
	 * @expectedException tx_mvc_exception_skipped
	 *
	 * @access public
	 * @return void
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function verifyIdentityKeyThrowsExceptionOnParentRecordNotFound() {
		$this->importDataSet('/service/fixtures/detectRecord/liveWorkspace/pages.xml');
		$this->importDataSet('/service/fixtures/detectRecord/liveWorkspace/language.xml');

		$localisationParentRecord = 111111111111;
		$forceTargetLanguageUid   = 2;
		$currentIdentityKey       = 'pages_language_overlay:NEW/' . $forceTargetLanguageUid . '/' . $localisationParentRecord . ':title';

		$newIdentityKey = $this->DetectRecordService->verifyIdentityKey($currentIdentityKey, $forceTargetLanguageUid, $localisationParentRecord);
	}

	/**
	 * @test
	 *
	 * @access public
	 * @return void
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function keepIdentityKeyForPagesWithNoForcedLanguageUidOnNewElement() {
		$this->importDataSet('/service/fixtures/detectRecord/liveWorkspace/pages.xml');
		$this->importDataSet('/service/fixtures/detectRecord/liveWorkspace/ttcontent.xml');
		$this->importDataSet('/service/fixtures/detectRecord/liveWorkspace/language.xml');

		$localisationParentRecord = 33155;
		$forceTargetLanguageUid   = 2;
		$currentIdentityKey       = 'pages_language_overlay:NEW/2/33155:title';
		$expectedIdentityKey      = $currentIdentityKey;

		$newIdentityKey = $this->DetectRecordService->verifyIdentityKey($currentIdentityKey, $forceTargetLanguageUid, $localisationParentRecord);
		$this->assertEquals(
			$expectedIdentityKey,
			$newIdentityKey,
			'Wrong generated identity key for the pages table!'
		);
	}

	/**
	 * @test
	 *
	 * @access public
	 * @return void
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function keepIdentityKeyForPagesWithNoForcedLanguageUidOnExistingElement() {
		$this->importDataSet('/service/fixtures/detectRecord/liveWorkspace/pages.xml');
		$this->importDataSet('/service/fixtures/detectRecord/liveWorkspace/ttcontent.xml');
		$this->importDataSet('/service/fixtures/detectRecord/liveWorkspace/language.xml');

		$localisationParentRecord = 33155;
		$forceTargetLanguageUid   = 1;
		$currentIdentityKey       = 'pages_language_overlay:485:title';
		$expectedIdentityKey      = $currentIdentityKey;

		$newIdentityKey = $this->DetectRecordService->verifyIdentityKey($currentIdentityKey, $forceTargetLanguageUid, $localisationParentRecord);
		$this->assertEquals(
			$expectedIdentityKey,
			$newIdentityKey,
			'Wrong generated identity key for the pages table!'
		);
	}

	/**
	 * @test
	 *
	 * @access public
	 * @return void
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function buildNewIdentityKeyForPagesWithNoForcedLanguageUidOnNotExistingElement() {
		$this->importDataSet('/service/fixtures/detectRecord/liveWorkspace/pages.xml');
		$this->importDataSet('/service/fixtures/detectRecord/liveWorkspace/ttcontent.xml');
		$this->importDataSet('/service/fixtures/detectRecord/liveWorkspace/language.xml');

		$localisationParentRecord = 33155;
		$forceTargetLanguageUid   = 1;
		$currentIdentityKey       = 'pages_language_overlay:12:title';
		$expectedIdentityKey      = 'pages_language_overlay:485:title';

		$newIdentityKey = $this->DetectRecordService->verifyIdentityKey($currentIdentityKey, $forceTargetLanguageUid, $localisationParentRecord);
		$this->assertEquals(
			$expectedIdentityKey,
			$newIdentityKey,
			'Wrong generated identity key for the pages table!'
		);
	}

	/**
	 * @test
	 *
	 * @access public
	 * @return void
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function buildNewIdentityKeyForPagesWithForcedLanguageUidOnRemovedElement() {
		$this->importDataSet('/service/fixtures/detectRecord/liveWorkspace/pages.xml');
		$this->importDataSet('/service/fixtures/detectRecord/liveWorkspace/ttcontent.xml');
		$this->importDataSet('/service/fixtures/detectRecord/liveWorkspace/language.xml');

		$localisationParentRecord = 33155;
		$forceTargetLanguageUid   = 3;
		$currentIdentityKey       = 'pages_language_overlay:12:title';
		$expectedIdentityKey      = 'pages_language_overlay:NEW/' . $forceTargetLanguageUid . '/33155:title';

		$newIdentityKey = $this->DetectRecordService->verifyIdentityKey($currentIdentityKey, $forceTargetLanguageUid, $localisationParentRecord);
		$this->assertEquals(
			$expectedIdentityKey,
			$newIdentityKey,
			'Wrong generated identity key for the pages table!'
		);
	}

	/**
	 * @test
	 *
	 * @access public
	 * @return void
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function buildNewIdentityKeyForPagesWithForcedLanguageUidOnNotExistingElement() {
		$this->importDataSet('/service/fixtures/detectRecord/liveWorkspace/pages.xml');
		$this->importDataSet('/service/fixtures/detectRecord/liveWorkspace/ttcontent.xml');
		$this->importDataSet('/service/fixtures/detectRecord/liveWorkspace/language.xml');

		$localisationParentRecord = 33155;
		$forceTargetLanguageUid   = 3;
		$currentIdentityKey       = 'pages_language_overlay:NEW/2/33155:title';
		$expectedIdentityKey      = 'pages_language_overlay:NEW/' . $forceTargetLanguageUid . '/33155:title';

		$newIdentityKey = $this->DetectRecordService->verifyIdentityKey($currentIdentityKey, $forceTargetLanguageUid, $localisationParentRecord);
		$this->assertEquals(
			$expectedIdentityKey,
			$newIdentityKey,
			'Wrong generated identity key for the pages table!'
		);
	}
}
?>