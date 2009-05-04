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

	// autoload the mvc
t3lib_extMgm::isLoaded('mvc', true);
tx_mvc_common_classloader::loadAll();

require_once t3lib_extMgm::extPath('l10nmgr') . 'domain/translation/class.tx_l10nmgr_domain_translation_field.php';

/**
 * bla
 *
 * {@inheritdoc}
 *
 * class.tx_l10nmgr_domain_translation_field_testcase.php
 *
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id$
 * @date $Date$
 * @since 24.04.2009 - 14:57:30
 * @see tx_phpunit_testcase
 * @category database testcase
 * @package TYPO3
 * @subpackage l10nmgr
 * @access public
 */
class tx_l10nmgr_domain_translation_field_testcase extends tx_phpunit_testcase {

	/**
	 * @var tx_l10nmgr_domain_translation_field
	 */
	protected $Field = null;

	/**
	 * Initialize a fresh instance of the tx_l10nmgr_domain_translation_field object
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function setUp() {
		$this->Field = new tx_l10nmgr_domain_translation_field();
	}

	/**
	 * Reset the tx_l10nmgr_domain_translation_field object
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function tearDown() {
		$this->Field = null;
	}

	/**
	 * Verify the instanceof field is of type "tx_l10nmgr_domain_translation_field"
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function test_fieldRightInstanceOf() {
		$this->assertTrue(($this->Field instanceof tx_l10nmgr_domain_translation_field), 'Object of wrong class');
	}

	/**
	 * Verify that the given value are also returned as expected.
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function test_translationFieldSetValueToMember() {

		$fixtureContent        = 'WebEx Customers,';
		$fixtureFieldPath      = 'tt_content:523531:bodytext';
		$fixtureTransformation = true;

		$this->Field->setContent($fixtureContent);
		$this->Field->setFieldPath($fixtureFieldPath);
		$this->Field->setTransformation($fixtureTransformation);

		$this->assertEquals (
			$fixtureContent,
			$this->Field->getContent(),
			'tx_l10nmgr_domain_translation_field contains wrong value on the member "content"'
		);

		$this->assertEquals (
			$fixtureFieldPath,
			$this->Field->getFieldPath(),
			'tx_l10nmgr_domain_translation_field contains wrong value on the member "fieldPath"'
		);

		$this->assertEquals (
			$fixtureTransformation,
			$this->Field->getTransformation(),
			'tx_l10nmgr_domain_translation_field contains wrong value on the member "transformation"'
		);
	}


	/**
	 * Verify that a field can mark as imported
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function test_markFieldAsImported() {

		$this->Field->markImported();

		$this->assertTrue (
			$this->Field->isImported(),
			'tx_l10nmgr_domain_translation_field can not mark as imported'
		);
	}

	/**
	 * Verify that a field can mark as imported
	 *
	 * @expectedException tx_mvc_exception_skipped
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function test_markFieldAsSkipped() {

		$this->Field->markSkipped('Field was skipped while test the skipped exception');
	}

	/**
	 * Verify that the field can be marked as skipped
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @access public
	 * @return null
	 */
	public function test_readFieldSkippedInformation() {
		$fixtureSkippedMessage = 'Field was skipped while test the skipped exception';

		try {

			$this->Field->markSkipped($fixtureSkippedMessage);

		} catch (tx_mvc_exception_skipped $e) {

			$this->assertTrue (
				($this->Field->isSkipped()),
				'tx_l10nmgr_domain_translation_field can not be marked as skipped.'
			);

			$this->assertEquals (
				$fixtureSkippedMessage,
				$this->Field->getSkippedMessage(),
				'tx_l10nmgr_domain_translation_field skipped message contains wrong message.'
			);

			return null;
		}

		$this->fail('tx_l10nmgr_domain_translation_field can not mark as imported');
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/tests/translation/class.tx_l10nmgr_domain_translation_field_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/tests/translation/class.tx_l10nmgr_domain_translation_field_testcase.php']);
}

?>