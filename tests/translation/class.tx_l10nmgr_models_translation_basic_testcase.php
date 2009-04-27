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

//require_once t3lib_extMgm::extPath('mvc') . 'ddd/class.tx_mvc_abstractDbObject.php';
//require_once t3lib_extMgm::extPath('mvc') . 'ddd/class.tx_mvc_abstractRepository.php';
require_once t3lib_extMgm::extPath('l10nmgr') . 'models/translation/class.tx_l10nmgr_models_translation_factory.php';

/**
 * bla
 *
 * {@inheritdoc}
 *
 * class.tx_l10nmgr_models_translation_testcase.php
 *
 * @author Michael Klapper <klapper@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id$
 * @date $Date$
 * @since 24.04.2009 - 14:57:30
 * @see tx_phpunit_database_testcase
 * @category database testcase
 * @package TYPO3
 * @subpackage l10nmgr
 * @access public
 */
class tx_l10nmgr_models_translation_basic_testcase extends tx_phpunit_testcase {

	/**
	 * @var tx_l10nmgr_models_translation_factory
	 */
	private $TranslationFactory = null;

	/**
	 * The setup method create the test database and
	 * loads the basic tables into the testdatabase
	 *
	 * @access public
	 * @return void
	 */
	public function setUp() {
		$this->TranslationFactory = new tx_l10nmgr_models_translation_factory();
	}

	/**
	 * Verify the instanceof Repository is of type "tx_l10nmgr_models_translation_factory"
	 *
	 * @access     public
	 * @return     void
	 */
	public function test_factoryRightInstanceOf() {
		$this->assertTrue(($this->TranslationFactory instanceof tx_l10nmgr_models_translation_factory),'Object of wrong class');
	}

	/**
	 * Verify the returned value is truly of the right instanceof
	 *
	 * @access     public
	 * @return     void
	 */
	public function test_factoryReturnsRightInstanceOf() {
		$fileName = t3lib_extMgm::extPath('l10nmgr') . 'tests/translation/fixtures/files/validContent/catxml_export__to_en_GB_210409-175557.xml';
		$this->assertTrue(($this->TranslationFactory->create($fileName) instanceof tx_l10nmgr_models_translation_data), 'Object of wrong class - expected instanceof "tx_l10nmgr_models_translation_data" ');
	}

	/**
	 * load file
	 *
	 * @access     public
	 * @return     void
	 */
	public function test_canLoadFullQualifiedFileName() {
		$fileName = t3lib_extMgm::extPath('l10nmgr') . 'tests/translation/fixtures/files/validContent/catxml_export__to_en_GB_210409-175557.xml';
		$this->TranslationFactory->create($fileName);
	}

	/**
	 * Provides fullQualifiedFilenames to files with invalid content
	 *
	 * @access public
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
	 * @access public
	 * @return void
	 */
	public function test_throwInvalidContentExceptionOnEmptyFile($fullQualifiedFileName) {
		$this->TranslationFactory->create($fullQualifiedFileName);
	}

	/**
	 * Provides fullQualifiedFilenames to files with invalid content
	 *
	 * @access public
	 * @return array
	 */
	public function invalidFilePathProvider() {
		$pathToFile = t3lib_extMgm::extPath('l10nmgr') . 'tests/translation/fixtures/files/';

		return array (
			array($pathToFile . 'noFile.xml'),
			array(''),
			array(0),
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
	 * @access public
	 * @return void
	 */
	public function test_throwFileNotFoundExceptionOnWrongFilePath($fullQualifiedFileName) {
		$this->TranslationFactory->create($fullQualifiedFileName);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/tests/translation/class.tx_l10nmgr_models_translation_basic_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/tests/translation/class.tx_l10nmgr_models_translation_basic_testcase.php']);
}

?>