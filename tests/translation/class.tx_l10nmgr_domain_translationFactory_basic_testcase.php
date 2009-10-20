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

/**
 * bla
 *
 * {@inheritdoc}
 *
 * class.tx_l10nmgr_domain_translationFactory_basic_testcase.php
 *
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id$
 * @date $Date$
 * @since 24.04.2009 - 14:57:30
 * @see tx_l10nmgr_tests_databaseTestcase
 * @category database testcase
 * @package TYPO3
 * @subpackage l10nmgr
 * @access public
 */
class tx_l10nmgr_domain_translationFactory_basic_testcase extends tx_l10nmgr_tests_baseTestcase {

	/**
	 * @var tx_l10nmgr_domain_translation_factory
	 */
	private $TranslationFactory = null;

	/**
	 * The setup method create the test database and
	 * loads the basic tables into the testdatabase
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function setUp() {
		$this->TranslationFactory = new tx_l10nmgr_domain_translationFactory();
	}

	/**
	 * Reset the test environment
	 *
	 * @access public
	 * @return void
	 */
	public function tearDown() {
		$this->TranslationFactory = null;
	}

	/**
	 * Verify the instanceof Repository is of type "tx_l10nmgr_domain_translationFactory"
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function test_factoryRightInstanceOf() {
		$this->assertTrue(($this->TranslationFactory instanceof tx_l10nmgr_domain_translationFactory),'Object of wrong class');
	}

	/**
	 * Verify the returned value is truly of the right instanceof
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function test_factoryReturnsRightInstanceOfTranslationData() {
		$fileName = t3lib_extMgm::extPath('l10nmgr') . 'tests/translation/fixtures/files/validContent/catxml_export__to_en_GB_210409-175557.xml';
		$this->assertTrue(($this->TranslationFactory->createFromXMLFile($fileName) instanceof tx_l10nmgr_domain_translation_data), 'Object of wrong class - expected instanceof "tx_l10nmgr_domain_translation_data" ');
	}

	/**
	 * Provides fullQualifiedFilenames to files with invalid content
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return array
	 */
	public function invalidFileContentProvider() {
		$pathToFile = t3lib_extMgm::extPath('l10nmgr') . 'tests/translation/fixtures/files/invalidContent/';

		return array (
			array($pathToFile . 'empty.xml'),
			array($pathToFile . 'echo.php'),
			array($pathToFile . 'integer.xml'),
			array($pathToFile . 'invalid.xml'),
			array($pathToFile . 'string.xml'),
		);
	}

	/**
	 * Test that an tx_mvc_exception_invalidContent is thrown if the given file is empty or contains no XML
	 *
	 * @param string $fullQualifiedFileName
	 * @dataProvider invalidFileContentProvider
	 * @expectedException tx_mvc_exception_invalidContent
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @access public
	 * @return void
	 */
	public function test_throwInvalidContentExceptionOnEmptyFile($fullQualifiedFileName) {
		$this->TranslationFactory->createFromXMLFile($fullQualifiedFileName);
	}

	/**
	 * Provides fullQualifiedFilenames to files with invalid content
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return array
	 */
	public function invalidFilePathProvider() {
		$pathToFile = t3lib_extMgm::extPath('l10nmgr') . 'tests/translation/fixtures/files/';
		$siteUrl = tx_mvc_common_typo3::getContextIndependentSiteUrl();

		return array (
			array($pathToFile . 'noFile.xml'),
			array($siteUrl . 'typo3conf/ext/l10nmgr/tests/translation/fixtures/files/validContent/catxml_export__to_en_GB_210409-175557.xml'), // Remote files are not allowed
			array(''),
			array(0),
			array(true),
			array(false),
			array(null),
			array(new ArrayObject()),
			array(array()),
		);
	}

	/**
	 * Test that an tx_mvc_exception_invalidContent is thrown if the given file is empty or contains no XML
	 *
	 * @param string $fullQualifiedFileName
	 * @dataProvider invalidFilePathProvider
	 * @expectedException tx_mvc_exception_fileNotFound
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @access public
	 * @return void
	 */
	public function test_throwFileNotFoundExceptionOnWrongFilePath($fullQualifiedFileName) {
		$this->TranslationFactory->createFromXMLFile($fullQualifiedFileName);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/tests/translation/class.tx_l10nmgr_domain_translationFactory_basic_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/tests/translation/class.tx_l10nmgr_domain_translationFactory_basic_testcase.php']);
}

?>