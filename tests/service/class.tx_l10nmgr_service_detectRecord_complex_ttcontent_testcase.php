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
 * class.tx_l10nmgr_service_detectRecord_complex_ttcontent_testcase.php
 *
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.tx_l10nmgr_service_detectRecord_basic_testcase.php $
 * @date 29.09.2009 11:30:21
 * @seetx_phpunit_database_testcase
 * @category testcase
 * @package TYPO3
 * @subpackage l10nmgr
 * @access public
 */

class tx_l10nmgr_service_detectRecord_complex_ttcontent_testcase extends tx_phpunit_database_testcase {

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
	 * Import dataset into test database
	 *
	 * This will only work if the fixture locate at the same directory level as the testcase.
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
	 * @test
	 * @expectedException tx_mvc_exception_skipped
	 *
	 * @access public
	 * @return void
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function verifyIdentityKeyThrowsExceptionOnParentRecordNotFound() {
		$this->importDataSet('/fixtures/detectRecord/pages.xml');
		$this->importDataSet('/fixtures/detectRecord/language.xml');

		$localisationParentRecord = 111111111111;
		$forceTargetLanguageUid   = 2;
		$currentIdentityKey       = 'tt_content:NEW/' . $forceTargetLanguageUid . '/' . $localisationParentRecord . ':title';

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
	public function keepIdentityKeyForContentWithNoForcedLanguageUidOnNewElement() {
		$this->importDataSet('/fixtures/detectRecord/pages.xml');
		$this->importDataSet('/fixtures/detectRecord/ttcontent.xml');
		$this->importDataSet('/fixtures/detectRecord/language.xml');

		$localisationParentRecord = 619945;
		$forceTargetLanguageUid   = 2;
		$currentIdentityKey       = 'tt_content:NEW/2/619945:header';
		$expectedIdentityKey      = 'tt_content:NEW/' . $forceTargetLanguageUid . '/619945:header';

		$newIdentityKey = $this->DetectRecordService->verifyIdentityKey($currentIdentityKey, $forceTargetLanguageUid, $localisationParentRecord);
		$this->assertEquals(
			$expectedIdentityKey,
			$newIdentityKey,
			'Wrong generated identity key for the tt_content table!'
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
	public function keepIdentityKeyForContentWithNoForcedLanguageUidOnExistingElement() {
		$this->importDataSet('/fixtures/detectRecord/pages.xml');
		$this->importDataSet('/fixtures/detectRecord/ttcontent.xml');
		$this->importDataSet('/fixtures/detectRecord/language.xml');

		$localisationParentRecord = 619945;
		$forceTargetLanguageUid   = 3;
		$currentIdentityKey       = 'tt_content:619941:header';
		$expectedIdentityKey      = $currentIdentityKey;

		$newIdentityKey = $this->DetectRecordService->verifyIdentityKey($currentIdentityKey, $forceTargetLanguageUid, $localisationParentRecord);
		$this->assertEquals(
			$expectedIdentityKey,
			$newIdentityKey,
			'Wrong generated identity key for the tt_content table!'
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
	public function buildNewIdentityKeyForContentWithForcedLanguageUidOnNewElement() {
		$this->importDataSet('/fixtures/detectRecord/pages.xml');
		$this->importDataSet('/fixtures/detectRecord/ttcontent.xml');
		$this->importDataSet('/fixtures/detectRecord/language.xml');

		$localisationParentRecord = 619945;
		$forceTargetLanguageUid   = 1;
		$currentIdentityKey       = 'tt_content:NEW/2/619945:header';
		$expectedIdentityKey      = 'tt_content:NEW/' . $forceTargetLanguageUid . '/619945:header';

		$newIdentityKey = $this->DetectRecordService->verifyIdentityKey($currentIdentityKey, $forceTargetLanguageUid, $localisationParentRecord);
		$this->assertEquals(
			$expectedIdentityKey,
			$newIdentityKey,
			'Wrong generated identity key for the tt_content table!'
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
	public function buildNewIdentityKeyForContentWithNoForcedLanguageUidOnNotExistingElement() {
		$this->importDataSet('/fixtures/detectRecord/pages.xml');
		$this->importDataSet('/fixtures/detectRecord/ttcontent.xml');
		$this->importDataSet('/fixtures/detectRecord/language.xml');

		$localisationParentRecord = 619945;
		$forceTargetLanguageUid   = 2;
		$currentIdentityKey       = 'tt_content:12:header';
		$expectedIdentityKey      = 'tt_content:NEW/' . $forceTargetLanguageUid . '/619945:header';

		$newIdentityKey = $this->DetectRecordService->verifyIdentityKey($currentIdentityKey, $forceTargetLanguageUid, $localisationParentRecord);
		$this->assertEquals(
			$expectedIdentityKey,
			$newIdentityKey,
			'Wrong generated identity key for the tt_content table!'
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
	public function buildNewIdentityKeyForContentWithForcedLanguageUidOnNotExistingElement() {
		$this->importDataSet('/fixtures/detectRecord/pages.xml');
		$this->importDataSet('/fixtures/detectRecord/ttcontent.xml');
		$this->importDataSet('/fixtures/detectRecord/language.xml');

		$localisationParentRecord = 619945;
		$forceTargetLanguageUid   = 1;
		$currentIdentityKey       = 'tt_content:12:header';
		$expectedIdentityKey      = 'tt_content:NEW/' . $forceTargetLanguageUid . '/619945:header';

		$newIdentityKey = $this->DetectRecordService->verifyIdentityKey($currentIdentityKey, $forceTargetLanguageUid, $localisationParentRecord);
		$this->assertEquals(
			$expectedIdentityKey,
			$newIdentityKey,
			'Wrong generated identity key for the tt_content table!'
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
	public function buildNewIdentityKeyForContentWithNoForcedLanguageUidOnExistingElementWithTheNewIndicator() {
		$this->importDataSet('/fixtures/detectRecord/pages.xml');
		$this->importDataSet('/fixtures/detectRecord/ttcontent.xml');
		$this->importDataSet('/fixtures/detectRecord/language.xml');

		$localisationParentRecord = 619945;
		$forceTargetLanguageUid   = 3;
		$currentIdentityKey       = 'tt_content:NEW/3/619945:header';
		$expectedIdentityKey      = 'tt_content:619941:header';

		$newIdentityKey = $this->DetectRecordService->verifyIdentityKey($currentIdentityKey, $forceTargetLanguageUid, $localisationParentRecord);
		$this->assertEquals(
			$expectedIdentityKey,
			$newIdentityKey,
			'Wrong generated identity key for the tt_content table!'
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
	public function buildNewIdentityKeyForContentWithForcedLanguageUidOnNotExistingElementWithTheNewIndicator() {
		$this->importDataSet('/fixtures/detectRecord/pages.xml');
		$this->importDataSet('/fixtures/detectRecord/ttcontent.xml');
		$this->importDataSet('/fixtures/detectRecord/language.xml');

		$localisationParentRecord = 619945;
		$forceTargetLanguageUid   = 4;
		$currentIdentityKey       = 'tt_content:NEW/3/619945:header';
		$expectedIdentityKey      = 'tt_content:NEW/' . $forceTargetLanguageUid . '/619945:header';

		$newIdentityKey = $this->DetectRecordService->verifyIdentityKey($currentIdentityKey, $forceTargetLanguageUid, $localisationParentRecord);
		$this->assertEquals(
			$expectedIdentityKey,
			$newIdentityKey,
			'Wrong generated identity key for the tt_content table!'
		);
	}
}
?>