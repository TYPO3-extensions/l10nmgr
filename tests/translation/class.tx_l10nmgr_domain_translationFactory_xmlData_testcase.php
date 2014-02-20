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
 * Verify that the TranslationFactory parse the XML
 * file correct and build the translationData collection as expected.
 *
 * {@inheritdoc}
 *
 * class.tx_l10nmgr_domain_translationFactory_xmlData_testcase.php
 *
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id$
 * @date $Date$
 * @since 24.04.2009 - 14:57:30
 * @see tx_l10nmgr_tests_databaseTestcase
 * @category testcase
 * @package TYPO3
 * @subpackage l10nmgr
 * @access public
 */
class tx_l10nmgr_domain_translationFactory_xmlData_testcase extends tx_l10nmgr_tests_baseTestcase {

	/**
	 * @var tx_l10nmgr_domain_translation_factory
	 */
	private $TranslationFactory = null;

	/**
	 * @var tx_l10nmgr_domain_translation_data
	 */
	private $TranslationData = null;

	/**
	 * The setup method create the test database and
	 * loads the basic tables into the testdatabase
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function setUp() {
		$fileName                 = t3lib_extMgm::extPath('l10nmgr') . 'tests/translation/fixtures/files/validContent/catxml_export__to_en_GB_210409-175557.xml';
		$this->TranslationFactory = new tx_l10nmgr_domain_translationFactory();
		$this->TranslationData    = $this->TranslationFactory->createFromXMLFile($fileName);
	}

	/**
	 * This testcase should ensure, that the translationData returns a valid collection of pageIds
	 * from an import file.
	 *
	 * @param void
	 * @return void
	 * @author Timo Schmidt
	 */
	public function test_canDetermineCorrectPageIdsFromImportFile(){

		$this->assertEquals (
			11,
			$this->TranslationData->getPageIdCollection()->count(),
			'Unexpected Number of relevant pageids in importFile'
		);
	}

	/**
	 * Verify that the TranslationFactury build the right amount of pages from the export XML file
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function test_translationDataContainsRightAmountOfPages() {
		$importFile = dirname(__FILE__) . '/fixtures/files/validContent/canImportServiceImportCorrectDataFixtureImport.xml';

		$TranslationData = $this->TranslationFactory->createFromXMLFile($importFile); /* @var $TranslationData tx_l10nmgr_domain_translation_data */

		$this->assertEquals (
			2,
			$TranslationData->getPageIdCollection()->count(),
			'The TranslationFactory should find 2 page items but contains: "' . $TranslationData->getPageIdCollection()->count() . '".'
		);
	}

	/**
	 * Verify that the page collection with the UID 175 contains the right amount elements within the elements collection.
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function test_translationDataContainsRightAmountOfElements() {
		$fixtureElementsCount = (int)$this->TranslationData->getPageCollection()->offsetGet(175)->getElementCollection()->count();

		$this->assertEquals (
			5,
			$fixtureElementsCount,
			'Wrong amount of elements returned. Expected amount of 5 and "' . $fixtureElementsCount . '" is returned.'
		);
	}

	/**
	 * Provides valid data records to test the stored table name
	 *
	 * <example>
	 * 	array (
	 * 		'pages', // expected tabel name
	 * 		1111, // Page UID
	 * 		'tt_content:1111', // Mixed key build from the table name and record UID like "tt_content:111"
	 * 	)
	 * </exampl>
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return array
	 */
	public function dataContainsRightTableNameForEntityDataProvider() {

		return array (
			array ('pages', 1111, 'pages:' . 1111),
			array ('pages', 175, 'pages:' . 175),
			array ('pages', 175, 'pages:' . 175),
			array ('tt_content', 175, 'tt_content:' . 423621),
			array ('tt_content', 175, 'tt_content:' . 3897),
			array ('tt_content', 535, 'tt_content:' . 1676),
		);
	}

	/**
	 * Verify that the right table name is stored
	 *
	 * @param string $expectedValue The expected result string
	 * @param integer $fixturePageId Page id where the elements (record) are located
	 * @param string $fixtureElementId Mixed key build from the table name and record UID like "tt_content:111"
	 * @access public
	 * @dataProvider dataContainsRightTableNameForEntityDataProvider
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function test_elementContainsRightTableNameForEntity($expectedValue, $fixturePageId, $fixtureElementId) {
		$fixtureValue = $this->TranslationData->getPageCollection()->offsetGet($fixturePageId)->getElementCollection()->offsetGet($fixtureElementId)->getTableName();

		$this->assertEquals (
			$expectedValue,
			$fixtureValue,
			'Wrong table name found. Expected table name "' . htmlspecialchars($expectedValue) . '" the following table name is given "' . htmlspecialchars($fixtureValue) . '"'
		);
	}

	/**
	 * Provides valid data records to test the "transformations" flag
	 *
	 * <example>
	 * 	array (
	 * 		true, // expected transformations status
	 * 		1111, // Page UID
	 * 		'tt_content:1111', // Mixed key build from the table name and record UID like "tt_content:111"
	 * 		'tt_content:523531:bodytext', // record command path
	 * 	)
	 * </exampl>
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return array
	 */
	public function validFieldTransoformationStatusDataProvider() {

		return array (
			array(false, 1111, 'pages:' . 1111, 'pages_language_overlay:NEW/1/1111:title'),
			array(false, 535, 'tt_content:' . 1674, 'tt_content:NEW/1/1674:header'),
			array(false, 535, 'tt_content:' . 1693, 'tt_content:NEW/1/1693:header'),
			array(false, 25271, 'pages:' . 25271, 'pages_language_overlay:NEW/1/25271:title'),
			array(true, 535, 'tt_content:' . 1676, 'tt_content:NEW/1/1676:bodytext'),
		);
	}

	/**
	 * Validate the transformations flag on fields entity
	 *
	 * @param string $expectedValue The expected result string
	 * @param integer $fixturePageId Page id where the elements (record) are located
	 * @param string $fixtureElementId Mixed key build from the table name and record UID like "tt_content:111"
	 * @param string $fixtureFieldName Field name like "title" or "bodytext"
	 * @access public
	 * @dataProvider validFieldTransoformationStatusDataProvider
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function test_fieldTransformationStatusIsSet($expectedValue, $fixturePageId, $fixtureElementId, $fixtureFieldName) {
		$fixtureValue = $this->TranslationData->getPageCollection()->offsetGet($fixturePageId)->getElementCollection()->offsetGet($fixtureElementId)->getFieldCollection()->offsetGet($fixtureFieldName)->getTransformation();

		$this->assertEquals (
			$expectedValue,
			$fixtureValue,
			'Wrong content found in field of element. Expected content'
		);
	}

	/**
	 * Provides valid data records to test the stored content between CDATA tags
	 *
	 * <example>
	 * 	array (
	 * 		'WebEx Customers, // expected content
	 * 		1111, // Page UID
	 * 		'tt_content:1111', // Mixed key build from the table name and record UID like "tt_content:111"
	 * 		'tt_content:523531:bodytext"', // record command path
	 * 	)
	 * </exampl>
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return array
	 */
	public function fieldContainsRightContentBetweenCdataDataProvider() {
		return array (
			array('WebEx Customers', 1111, 'pages:' . 1111, 'pages_language_overlay:NEW/1/1111:title'),
			array('Contact Us', 175, 'tt_content:' . 3887, 'tt_content:NEW/1/3887:header'),
			array('WebEx US offices', 536, 'pages:' . 536, 'pages_language_overlay:29181:abstract'),
			array('WebEx International Offices', 535, 'tt_content:' . 1674, 'tt_content:NEW/1/1674:header'),
		);
	}

	/**
	 * Verify that the right content is stored at the right place in the collection
	 *
	 * @param string $expectedValue The expected result string
	 * @param integer $fixturePageId Page id where the elements (record) are located
	 * @param string $fixtureElementId Mixed key build from the table name and record UID like "tt_content:111"
	 * @param string $fixtureFieldName Field name like "title" or "bodytext"
	 * @access public
	 * @dataProvider fieldContainsRightContentBetweenCdataDataProvider
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function test_fieldContainsRightContentBetweenCDATA($expectedValue, $fixturePageId, $fixtureElementId, $fixtureFieldName) {
		$fixtureFieldContent = $this->TranslationData->getPageCollection()->offsetGet($fixturePageId)->getElementCollection()->offsetGet($fixtureElementId)->getFieldCollection()->offsetGet($fixtureFieldName)->getContent();

		$this->assertEquals (
			$expectedValue,
			$fixtureFieldContent,
			'Wrong content found in field of element. Expected content "' . htmlspecialchars($expectedValue) . '" the following content is given "' . htmlspecialchars($fixtureFieldContent) . '"'
		);
	}

	/**
	 * Provides valid data records to test the stored content without CDATA tags
	 *
	 * <example>
	 * 	array (
	 * 		'WebEx Customers', // expected content
	 * 		1111', // Page UID
	 * 		'tt_content:1111', // Mixed key build from the table name and record UID like "tt_content:111"
	 * 		'tt_content:523531:bodytext', // record command path
	 * 	)
	 * </exampl>
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return array
	 */
	public function fieldContainsRightContentWithoutCdataDataProvider() {
		return array (
			array (
				'<link http://www.webex.co.uk/>WebEx Communications UK Ltd</link> <br />20 Garrick Street <br />London WC2E 9BT <br />United Kingdom <br />Tel: 0800 389 9772 <br />Email: <link europe@webex.com>europe@webex.com</link> ',
				535,
				'tt_content:' . 1693,
				'tt_content:NEW/1/1693:bodytext'
			),
			array (
//				'<h1>Your message has been sent</h1>Thank you for your message.  We have forwarded your communication to the appropriate department.  If this is a technical support matter, please call our customer care line at<b> 866-229-3239</b> for immediate attention.  To speak with a sales representative, please call <b>877-509-3239</b>.</p>',
//				'Test123',
				chr(10) . '<h1>Your message has been sent</h1>

Thank you for your message.  We have forwarded your communication to the appropriate department.  If this is a technical support matter, please call our customer care line at<b> 866-229-3239</b> for immediate attention.  To speak with a sales representative, please call <b>877-509-3239</b>.
',
				19761,
				'tt_content:' . 523511,
				'tt_content:523531:bodytext'
			),
		);
	}

	/**
	 * Verify that the right content is stored at the right place in the collection
	 *
	 * @param string $expectedValue The expected result string
	 * @param integer $fixturePageId Page id where the elements (record) are located
	 * @param string $fixtureElementId Mixed key build from the table name and record UID like "tt_content:111"
	 * @param string $fixtureFieldPath Field name like "tt_content:523531:bodytext"
	 * @access public
	 * @dataProvider fieldContainsRightContentWithoutCdataDataProvider
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function test_fieldContainsRightContentWithoutCDATA($expectedValue, $fixturePageId, $fixtureElementId, $fixtureFieldPath) {
		$fixtureFieldContent = $this->TranslationData->getPageCollection()->offsetGet($fixturePageId)->getElementCollection()->offsetGet($fixtureElementId)->getFieldCollection()->offsetGet($fixtureFieldPath)->getContent();

		$this->assertEquals (
			str_replace(array("\r\n", "\r", "\n"), '', $expectedValue),
			str_replace(array("\r\n", "\r", "\n"), '', $fixtureFieldContent),
			'Wrong content found in field of element. Expected content "' . htmlspecialchars($expectedValue) . '" the following content is given "' . htmlspecialchars($fixtureFieldContent) . '"'
		);
	}

	/**
	 * Provides invalid index keys to access the TranslationData
	 *
	 * <example>
	 * 	array (
	 * 		'WebEx Customers, // expected content
	 * 		1111, // Page UID
	 * 		'tt_content:1111', // Mixed key build from the table name and record UID like "tt_content:111"
	 * 		'tt_content:523531:bodytext"', // record command path
	 * 	)
	 * </exampl>
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return array
	 */
	public function throwsExceptionWhileAccessingNotAvailableIndexOfTranslationDataCollectionDataProvider() {
		return array (
			array (0, 'tt_content:' . 1693, 'tt_content:NEW/1/1693:bodytext'), // provides an invalid page uid
			array (535, 'invalidTableName:' . 1693, 'tt_content:NEW/1/1693:bodytext'), // provides an invalid mixed key to access the element record
			array (535, 'tt_content:' . 0, 'tt_content:NEW/1/1693:bodytext'), // provides an invalid mixed key to access the element record
		);
	}

	/**
	 * Verify that an exception is thrown if an not existing index of collection is tryed to access
	 *
	 * @param integer $fixturePageId  Page id where the elements (record) are located
	 * @param string $fixtureElementId Mixed key build from the table name and record UID like "tt_content:111"
	 * @param string $fixtureFieldPath  Field name like "tt_content:523531:bodytext"
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @dataProvider throwsExceptionWhileAccessingNotAvailableIndexOfTranslationDataCollectionDataProvider
	 * @expectedException tx_mvc_exception_argumentOutOfRange
	 * @return void
	 */
	public function test_throwsExceptionWhileAccessingNotAvailableIndexOfTranslationDataCollection($fixturePageId, $fixtureElementId, $fixtureFieldPath) {
		$this->TranslationData->getPageCollection()->offsetGet($fixturePageId)->getElementCollection()->offsetGet($fixtureElementId)->getFieldCollection()->offsetGet($fixtureFieldPath)->getContent();
	}

	/**
	 * Take sure that the TranslationFactory builds the TranslationData object with the right injected "forced target language uid"
	 *
	 * @access public
	 * @return void
	 *
	 * @test
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function forceTranslatationUidToTranslationData() {
		$importFile = dirname(__FILE__) . '/fixtures/files/validContent/canImportServiceImportCorrectDataFixtureImport.xml';
		$forcedTargetLanguageUid = 2;

		$TranslationData = $this->TranslationFactory->createFromXMLFile($importFile, $forcedTargetLanguageUid); /* @var $TranslationData tx_l10nmgr_domain_translation_data */

		$this->assertEquals (
			$forcedTargetLanguageUid,
			$TranslationData->getSysLanguageUid(),
			'The TranslationData must be contain the right $forcedTargetLanguageUid "' . $forcedTargetLanguageUid . '".'
		);

		$this->assertEquals (
			2,
			$TranslationData->getPageIdCollection()->count(),
			'The TranslationFactory should find 2 page items but contains: "' . $TranslationData->getPageIdCollection()->count() . '".'
		);
	}

	/**
	 * Take sure that the TranslationFactory builds the TranslationData object with the right target language uid
	 *
	 * @access public
	 * @return void
	 *
	 * @test
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function translationDataHoldsTheRightTargetLanguageUid() {
		$importFile = dirname(__FILE__) . '/fixtures/files/validContent/canImportServiceImportCorrectDataFixtureImport.xml';
		$forcedTargetLanguageUid  = 2;
		$fixtureTargetLanguageUid = 1;

		$TranslationData = $this->TranslationFactory->createFromXMLFile($importFile); /* @var $TranslationData tx_l10nmgr_domain_translation_data */

		$this->assertEquals (
			$fixtureTargetLanguageUid,
			$TranslationData->getSysLanguageUid(),
			'The TranslationData must be contain the right target language uid "' . $fixtureTargetLanguageUid . '".'
		);

		$TranslationData = $this->TranslationFactory->createFromXMLFile($importFile, $forcedTargetLanguageUid); /* @var $TranslationData tx_l10nmgr_domain_translation_data */

		$this->assertEquals (
			$forcedTargetLanguageUid,
			$TranslationData->getSysLanguageUid(),
			'The TranslationData must be contain the right target language uid "' . $forcedTargetLanguageUid . '".'
		);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/tests/translation/class.tx_l10nmgr_domain_translationFactory_xmlData_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/tests/translation/class.tx_l10nmgr_domain_translationFactory_xmlData_testcase.php']);
}

?>