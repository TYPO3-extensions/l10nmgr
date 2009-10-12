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

require_once t3lib_extMgm::extPath('l10nmgr') . 'tests/translation/class.tx_l10nmgr_domain_translation_field_testcase.php';
require_once t3lib_extMgm::extPath('l10nmgr') . 'tests/translation/class.tx_l10nmgr_domain_translation_fieldCollection_testcase.php';
require_once t3lib_extMgm::extPath('l10nmgr') . 'tests/translation/class.tx_l10nmgr_domain_translation_element_testcase.php';
require_once t3lib_extMgm::extPath('l10nmgr') . 'tests/translation/class.tx_l10nmgr_domain_translation_elementCollection_testcase.php';
require_once t3lib_extMgm::extPath('l10nmgr') . 'tests/translation/class.tx_l10nmgr_domain_translation_page_testcase.php';
require_once t3lib_extMgm::extPath('l10nmgr') . 'tests/translation/class.tx_l10nmgr_domain_translation_pageCollection_testcase.php';
require_once t3lib_extMgm::extPath('l10nmgr') . 'tests/translation/class.tx_l10nmgr_domain_translation_data_testcase.php';
require_once t3lib_extMgm::extPath('l10nmgr') . 'tests/translation/class.tx_l10nmgr_domain_translationFactory_basic_testcase.php';
require_once t3lib_extMgm::extPath('l10nmgr') . 'tests/translation/class.tx_l10nmgr_domain_translationFactory_xmlData_testcase.php';

/**
 * Static test suite for the translation package
 *
 * class.tx_l10nmgr_translation_testsuite.php
 *
 * {@inheritdoc}
 *
 * @author Michael Klapper <klapper@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id$
 * @date $Date$
 * @since 13.05.2009 - 14:24:19
 * @see tx_phpunit_testsuite
 * @package TYPO3
 * @subpackage tx_l10nmgr
 * @access public
 */
class tx_l10nmgr_translation_testsuite extends tx_phpunit_testsuite {

	/**
	 * Constructs the test suite handler.
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function __construct() {

		$this->setName('Translation package');
		$this->addTestSuite('tx_l10nmgr_domain_translation_page_testcase');
		$this->addTestSuite('tx_l10nmgr_domain_translation_fieldCollection_testcase');
		$this->addTestSuite('tx_l10nmgr_domain_translationFactory_xmlData_testcase');
		$this->addTestSuite('tx_l10nmgr_domain_translation_elementCollection_testcase');
		$this->addTestSuite('tx_l10nmgr_domain_translation_pageCollection_testcase');
		$this->addTestSuite('tx_l10nmgr_domain_translationFactory_basic_testcase');
		$this->addTestSuite('tx_l10nmgr_domain_translation_data_testcase');
		$this->addTestSuite('tx_l10nmgr_domain_translation_element_testcase');
		$this->addTestSuite('tx_l10nmgr_domain_translation_field_transformation_testcase');
		$this->addTestSuite('tx_l10nmgr_domain_translation_field_testcase');

	}

	/**
	 * Creates the suite.
	 *
	 * @access public
	 * @return tx_phpunit_testsuite
	 */
	public static function suite() {
		return new self();
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/tests/class.tx_l10nmgr_translation_testsuite.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/tests/class.tx_l10nmgr_translation_testsuite.php']);
}

?>