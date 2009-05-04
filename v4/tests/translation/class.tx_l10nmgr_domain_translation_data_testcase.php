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

require_once t3lib_extMgm::extPath('l10nmgr') . 'domain/translation/class.tx_l10nmgr_domain_translation_data.php';

/**
 * Verify that the tx_l10nmgr_domain_translation_data works as expected
 *
 * class.tx_l10nmgr_domain_translation_data_testcase.php
 *
 * {@inerhitdoc}
 *
 * @author Michael Klapper <klapper@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id$
 * @date $Date$
 * @since 04.05.2009 - 15:50:47
 * @see tx_phpunit_testcase
 * @package TYPO3
 * @subpackage tx_l10nmgr
 * @access public
 */
class tx_l10nmgr_domain_translation_data_testcase extends tx_phpunit_testcase {

	/**
	 * @var tx_l10nmgr_domain_translation_data
	 */
	protected $Data = null;

	/**
	 * Initialize a fresh instance of the tx_l10nmgr_domain_translation_data object
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function setUp() {
		$this->Data = new tx_l10nmgr_domain_translation_data();
	}

	/**
	 * Reset the tx_l10nmgr_domain_translation_data object
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function tearDown() {
		$this->Data = null;
	}

	/**
	 * Retrieve a field for testing.
	 *
	 * The field is not marked as imported or skipped.
	 *
	 * @access protected
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return tx_l10nmgr_domain_translation_field
	 */
	protected function fixtureField() {

		$Field = new tx_l10nmgr_domain_translation_field();
		$Field->setContent('Test content');
		$Field->setFieldPath('tt_content:1:title');
		$Field->setTransformation(false);

		return $Field;
	}

	/**
	 * Retrieve a fieldCollection for testing
	 *
	 * Containing three fields with the index key:
	 * - first
	 * - second
	 * - third
	 *
	 * @access protected
	 * @see tx_l10nmgr_domain_translation_data_testcase::fixtureField
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return tx_l10nmgr_domain_translation_fieldCollection
	 */
	protected function fixtureFieldCollection() {

		$FieldCollection = new tx_l10nmgr_domain_translation_fieldCollection();
		$FieldCollection->offsetSet('first', $this->fixtureField());
		$FieldCollection->offsetSet('second', $this->fixtureField());
		$FieldCollection->offsetSet('third', $this->fixtureField());

		return $FieldCollection;
	}

	/**
	 * Retrieve a element for testing issues
	 *
	 * The Element contains:
	 * - FieldCollection
	 * - Three Fields which are located into the FieldCollection
	 *
	 * @access protected
	 * @see tx_l10nmgr_domain_translation_data_testcase::fixtureFieldCollection
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return tx_l10nmgr_domain_translation_element
	 */
	protected function fixtureElement() {

		$Element = new tx_l10nmgr_domain_translation_element();
		$Element->setUid(111);
		$Element->setTableName('tt_content');
		$Element->setFieldCollection($this->fixtureFieldCollection());

		return $Element;
	}

	/**
	 * Retrieve a elementCollection for testing issues
	 *
	 * The Element contains:
	 * - FieldCollection
	 * - Three Fields which are located into the FieldCollection
	 *
	 * @access protected
	 * @see tx_l10nmgr_domain_translation_data_testcase::fixtureElement
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return tx_l10nmgr_domain_translation_elementCollection
	 */
	protected function fixtureElementCollection() {

		$ElementCollection = new tx_l10nmgr_domain_translation_elementCollection();
		$ElementCollection->offsetSet('first', $this->fixtureElement());

		return $ElementCollection;
	}

	/**
	 * Retrieve an Page object containing a full Page
	 *
	 * @see tx_l10nmgr_domain_translation_data_testcase::fixtureElementCollection
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return tx_l10nmgr_domain_translation_page
	 */
	protected function fixturePage() {

		$Page = new tx_l10nmgr_domain_translation_page();
		$Page->setUid(111);
		$Page->setElementCollection($this->fixtureElementCollection());

		return $Page;
	}

	/**
	 * Retrieve an Page object containing a full PageCollection
	 *
	 * @see tx_l10nmgr_domain_translation_data_testcase::fixtureElementCollection
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return tx_l10nmgr_domain_translation_pageCollection
	 */
	protected function fixturePageCollection() {

		$PageCollection = new tx_l10nmgr_domain_translation_pageCollection();
		$PageCollection->offsetSet('first', $this->fixturePage());

		return $PageCollection;
	}

	/**
	 * Verify the instanceof field is of type "tx_l10nmgr_domain_translation_data"
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function test_rightInstanceOf() {
		$this->assertTrue (
			($this->Data instanceof tx_l10nmgr_domain_translation_data),
			'Object of wrong class'
		);
	}

	/**
	 * Verify that the Data contains the right values which are given before to it.
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return unknown
	 */
	public function test_verifyRightValuesHoldingTheData() {
		$fixtureBaseUrl = 'http://www.aoemedia.de/';
		$fixtureExportDataRecordUid = 111;
		$fixtureFieldCount = 12;
		$fixtureFormatVersion = '1.2';
		$fixtureL10ncfgUid = 9;
		$fixtureSourceLanguageISOcode = 'en';
		$fixtureSysLanguageUid = 3;
		$fixtureTargetLanguageUid = 4;
		$fixtureWordCount = 99;
		$fixtureWorkspaceId = -1;

		$this->Data->setPageCollection($this->fixturePageCollection());
		$this->Data->setBaseUrl($fixtureBaseUrl);
		$this->Data->setExportDataRecordUid($fixtureExportDataRecordUid);
		$this->Data->setFieldCount($fixtureFieldCount);
		$this->Data->setFormatVersion($fixtureFormatVersion);
		$this->Data->setL10ncfgUid($fixtureL10ncfgUid);
		$this->Data->setSourceLanguageISOcode($fixtureSourceLanguageISOcode);
		$this->Data->setSysLanguageUid($fixtureSysLanguageUid);
		$this->Data->setTargetLanguageUid($fixtureTargetLanguageUid);
		$this->Data->setWordCount($fixtureWordCount);
		$this->Data->setWorkspaceId($fixtureWorkspaceId);

		$this->assertAttributeEquals (
			$fixtureBaseUrl,
			'baseUrl',
			$this->Data,
			'tx_l10nmgr_domain_translation_data member "baseUrl" contains wrong value'
		);
		$this->assertAttributeEquals (
			$fixtureExportDataRecordUid,
			'exportDataRecordUid',
			$this->Data,
			'tx_l10nmgr_domain_translation_data member "exportDataRecordUid" contains wrong value'
		);
		$this->assertAttributeEquals (
			$fixtureFieldCount,
			'fieldCount',
			$this->Data,
			'tx_l10nmgr_domain_translation_data member "fieldCount" contains wrong value'
		);
		$this->assertAttributeEquals (
			$fixtureFormatVersion,
			'formatVersion',
			$this->Data,
			'tx_l10nmgr_domain_translation_data member "formatVersion" contains wrong value'
		);
		$this->assertAttributeEquals (
			$fixtureL10ncfgUid,
			'l10ncfgUid',
			$this->Data,
			'tx_l10nmgr_domain_translation_data member "l10ncfgUid" contains wrong value'
		);
		$this->assertAttributeEquals (
			$fixtureSourceLanguageISOcode,
			'sourceLanguageISOcode',
			$this->Data,
			'tx_l10nmgr_domain_translation_data member "sourceLanguageISOcode" contains wrong value'
		);
		$this->assertAttributeEquals (
			$fixtureSysLanguageUid,
			'sysLanguageUid',
			$this->Data,
			'tx_l10nmgr_domain_translation_data member "sysLanguageUid" contains wrong value'
		);
		$this->assertAttributeEquals (
			$fixtureTargetLanguageUid,
			'targetLanguageUid',
			$this->Data,
			'tx_l10nmgr_domain_translation_data member "targetLanguageUid" contains wrong value'
		);
		$this->assertAttributeEquals (
			$fixtureWordCount,
			'wordCount',
			$this->Data,
			'tx_l10nmgr_domain_translation_data member "wordCount" contains wrong value'
		);
		$this->assertAttributeEquals (
			$fixtureWorkspaceId,
			'workspaceId',
			$this->Data,
			'tx_l10nmgr_domain_translation_data member "workspaceId" contains wrong value'
		);

		$this->assertType (
			'tx_l10nmgr_domain_translation_pageCollection',
			$this->Data->getPageCollection(),
			'Data contains object of wrong class.'
		);
	}

	/**
	 * Verify that the Data contains the right isImported state.
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function test_verifyTheRightIsImportedStateOnDataWhichContainsFilledFieldCollection() {
		$this->assertFalse (
			($this->Data->isImported()),
			'tx_l10nmgr_domain_translation_data contains the wrong isImported state.'
		);

		$this->Data->setPageCollection($this->fixturePageCollection());
		$this->assertFalse (
			($this->Data->isImported()),
			'tx_l10nmgr_domain_translation_data contains the wrong isImported state.'
		);

		$this->Data->getPageCollection()->offsetGet('first')->getElementCollection()->offsetGet('first')->getFieldCollection()->offsetGet('first')->markImported();
		$this->Data->getPageCollection()->offsetGet('first')->getElementCollection()->offsetGet('first')->getFieldCollection()->offsetGet('second')->markImported();

		$this->assertFalse (
			($this->Data->isImported()),
			'tx_l10nmgr_domain_translation_data contains the wrong isImported state.'
		);

		try {

			$this->Data->getPageCollection()->offsetGet('first')->getElementCollection()->offsetGet('first')->getFieldCollection()->offsetGet('third')->markSkipped('Skipped while testing.');

		} catch (tx_mvc_exception_skipped $e) {

			$this->assertTrue (
				($this->Data->isImported()),
				'tx_l10nmgr_domain_translation_data contains the wrong isImported state.'
			);

			return null;
		}

		$this->fail('tx_l10nmgr_domain_translation_data can not marked as skipped.');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/tests/translation/class.tx_l10nmgr_domain_translation_data_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/tests/translation/class.tx_l10nmgr_domain_translation_data_testcase.php']);
}

?>