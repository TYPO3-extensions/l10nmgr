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
 * class.tx_l10nmgr_service_detectRecord_basic_testcase.php
 *
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.tx_l10nmgr_service_detectRecord_basic_testcase.php $
 * @date 29.09.2009 11:30:21
 * @see tx_phpunit_testcase
 * @category testcase
 * @package TYPO3
 * @subpackage l10nmgr
 * @access public
 */

class tx_l10nmgr_service_detectRecord_basic_testcase extends tx_phpunit_testcase {

	/**
	 * @var tx_l10nmgr_service_detectRecord
	 */
	protected $DetectRecordService = null;

	public function setUp() {
		$this->DetectRecordService = t3lib_div::makeInstance('tx_l10nmgr_service_detectRecord');
	}

	/**
	 * @access public
	 * @return void
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function tearDown() {
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
	public function verifyIdentityKeyThrowsExceptionOnInvalidLanguageUid() {

		$this->DetectRecordService->verifyIdentityKey('tt_content:12:title', 0, 12);
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
	public function verifyIdentityKeyThrowsExceptionOnInvalidCmdProcessingString() {

		$this->DetectRecordService = $this->getMock('tx_l10nmgr_service_detectRecord', array('buildIdentityKey', 'getProcessingString', 'getRecordTranslation', 'getParentRecord'));
		$this->DetectRecordService->verifyIdentityKey('tt_content:--NULL--:title', 1, 12);
	}
}
?>