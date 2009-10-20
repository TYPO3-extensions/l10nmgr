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
 * Verify that the tx_l10nmgr_domain_translation_page works as expected
 *
 * class.tx_l10nmgr_domain_translation_page_testcase.php
 *
 * {@inerhitdoc}
 *
 * @author Michael Klapper <klapper@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id$
 * @date $Date$
 * @since 04.05.2009 - 15:50:47
 * @see tx_l10nmgr_tests_baseTestcase
 * @package TYPO3
 * @subpackage tx_l10nmgr
 * @access public
 */
class tx_l10nmgr_domain_translation_page_testcase extends tx_l10nmgr_tests_baseTestcase {

	const INDEX_FIRST  = 'tt_content:1:header';
	const INDEX_SECOND = 'tt_content:1:subheader';
	const INDEX_THIRD  = 'tt_content:1:bodytext';

	/**
	 * @var tx_l10nmgr_domain_translation_page
	 */
	protected $Page = null;

	/**
	 * Initialize a fresh instance of the tx_l10nmgr_domain_translation_page object
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function setUp() {
		$this->Page = new tx_l10nmgr_domain_translation_page();
	}

	/**
	 * Reset the tx_l10nmgr_domain_translation_page object
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function tearDown() {
		$this->Page = null;
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
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return tx_l10nmgr_domain_translation_fieldCollection
	 */
	protected function fixtureFieldCollection() {

		$FieldCollection = new tx_l10nmgr_domain_translation_fieldCollection();
		$FieldCollection->offsetSet(self::INDEX_FIRST, $this->fixtureField());
		$FieldCollection->offsetSet(self::INDEX_SECOND, $this->fixtureField());
		$FieldCollection->offsetSet(self::INDEX_THIRD, $this->fixtureField());

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
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return tx_l10nmgr_domain_translation_elementCollection
	 */
	protected function fixtureElementCollection() {

		$ElementCollection = new tx_l10nmgr_domain_translation_elementCollection();
		$ElementCollection->offsetSet(self::INDEX_FIRST, $this->fixtureElement());

		return $ElementCollection;
	}

	/**
	 * Verify the instanceof field is of type "tx_l10nmgr_domain_translation_page"
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function test_rightInstanceOf() {
		$this->assertTrue (
			($this->Page instanceof tx_l10nmgr_domain_translation_page),
			'Object of wrong class'
		);
	}

	/**
	 * Verify that the Page contains the right values which are given before to it.
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return unknown
	 */
	public function test_verifyRightValuesHoldingThePage() {
		$this->Page->setElementCollection($this->fixtureElementCollection());
		$this->Page->setUid(111);

		$this->assertEquals (
			111,
			$this->Page->getUid(),
			'tx_l10nmgr_domain_translation_page contains wrong uid.'
		);

		$this->assertType (
			'tx_l10nmgr_domain_translation_elementCollection',
			$this->Page->getElementCollection(),
			'Page contains object of wrong class.'
		);
	}

	/**
	 * Verify that the Page contains the right isImported state.
	 *
	 * @test
	 *
	 * @access public
	 * @return void
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function verifyTheRightIsImportedStateOnPageWhichContainsFilledFieldCollection() {
		$this->assertFalse (
			($this->Page->isImported()),
			'tx_l10nmgr_domain_translation_page contains the wrong isImported state.'
		);

		$this->Page->setElementCollection($this->fixtureElementCollection());
		$this->assertFalse (
			($this->Page->isImported()),
			'tx_l10nmgr_domain_translation_page contains the wrong isImported state.'
		);

		$this->Page->getElementCollection()->offsetGet(self::INDEX_FIRST)->getFieldCollection()->offsetGet(self::INDEX_FIRST)->markImported();
		$this->Page->getElementCollection()->offsetGet(self::INDEX_FIRST)->getFieldCollection()->offsetGet(self::INDEX_SECOND)->markImported();
		$this->assertFalse (
			($this->Page->isImported()),
			'tx_l10nmgr_domain_translation_page contains the wrong isImported state.'
		);

		try {

			$this->Page->getElementCollection()->offsetGet(self::INDEX_FIRST)->getFieldCollection()->offsetGet(self::INDEX_THIRD)->markSkipped('Skipped while testing.');

		} catch (tx_mvc_exception_skipped $e) {

			$this->assertTrue (
				($this->Page->isImported()),
				'tx_l10nmgr_domain_translation_page contains the wrong isImported state.'
			);

			return null;
		}

		$this->fail('tx_l10nmgr_domain_translation_page can not marked as skipped.');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/tests/translation/class.tx_l10nmgr_domain_translation_page_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/tests/translation/class.tx_l10nmgr_domain_translation_page_testcase.php']);
}

?>