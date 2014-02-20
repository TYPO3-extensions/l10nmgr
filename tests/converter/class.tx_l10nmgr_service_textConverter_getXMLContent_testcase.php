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
 * Testcase for text convert from database text to XML text.
 *
 * class.tx_l10nmgr_service_textConverter_toXML_testcase.php
 *
 * {@inheritdoc}
 *
 * @author Tolleiv Nietsch <nietsch@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id$
 * @date $Date$
 * @since 07.05.2009 - 14:24:19
 * @see tx_l10nmgr_tests_baseTestcase
 * @package TYPO3
 * @subpackage tx_l10nmgr
 * @access public
 */
class tx_l10nmgr_service_textConverter_getXMLContent_testcase extends tx_l10nmgr_tests_baseTestcase {

	/**
	 * @var tx_l10nmgr_service_textConverter
	 */
	protected $textConverter = NULL;

	/**
	 * Initialize a fresh instance of the tx_l10nmgr_service_textConverter
	 *
	 * @access public
	 * @return void
	 */
	public function setUp() {
		$this->skipInWrongWorkspaceContext();
		$this->textConverter = new tx_l10nmgr_service_textConverter();
	}

	/**
	 * Reset the tx_l10nmgr_service_textConverter
	 *
	 * @access public
	 * @return void
	 */
	public function tearDown() {
		$this->textConverter = NULL;
	}

	/**
	 * test if simple content is extracted properly from XML
	 * whitespaces should be kept too
	 *
	 * @return void
	 */
	public function test_getXMLContentWorkForSimpleContent() {
		$output = 'the content ';
		$input = '<data>' . $output . '</data>';

		$this->assertEquals($output, $this->textConverter->getXMLContent(new SimpleXMLElement($input)));
	}

	/**
	 * test if nested content (which could be produced by the RTE)
	 * is properly retrieved
	 *
	 * @return void
	 */
	public function test_getXMLContentWorkForNestedContent() {
		$output = '<p>the content </p>';
		$input = '<data>' . $output . '</data>';

		$this->assertEquals($output, $this->textConverter->getXMLContent(new SimpleXMLElement($input)));
	}

	/**
	 * Check if nested content is kept and if newlines are kept as supposed
	 *
	 * @return void
	 */
	public function test_getXMLContentWorkForMixedContent() {
		$input = "<?xml version=\"1.0\"?><!DOCTYPE TYPO3L10N [ <!ENTITY nbsp \"&#160;\"> ]><data>mixed <p> &nbsp;the \ncontent&quot;</p> &quot;content </data>";
		$output = "mixed <p> &nbsp;the \ncontent\"</p> \"content ";

		$this->assertEquals($output, $this->textConverter->getXMLContent(new SimpleXMLElement($input)));
	}

	/**
	 * Check if single elements without nesting are "restored" as supposed
	 * this feature is required because some XML invalid content might be passed through html-entities during the export
	 *
	 * @return void
	 */
	public function test_getXMLContentWorkForEntityContent() {
		$input = '<data>blub&lt;huhu&gt;bla&lt;/huhu&gt;blub</data>';
		$output = 'blub<huhu>bla</huhu>blub';

		$this->assertEquals($output, $this->textConverter->getXMLContent(new SimpleXMLElement($input)));
	}

	/**
	 * Check if the process keeps UTF-8 chars as expected
	 *
	 * @return void
	 */
	public function test_getXMLContentWorkForRandomUnicodeContent() {
		$input = '<data>€ ' . chr(0xe2) . chr(0x80) . chr(0x94) . '</data>';
		$output = '€ ' . chr(0xe2) . chr(0x80) . chr(0x94);

		$this->assertEquals($output, $this->textConverter->getXMLContent(new SimpleXMLElement($input)));
	}

	/**
	 * Check if CDATA tags are processed in the right way
	 *
	 * @return void
	 */
	public function test_getXMLContentWorkForCDATAContent() {
		$input = '<data><![CDATA[ &lt;br /&gt;new line ]]></data>';
		$output = ' &lt;br /&gt;new line ';

		$this->assertEquals($output, $this->textConverter->getXMLContent(new SimpleXMLElement($input)));
	}


	/**
	 * Check if wether CDATA tags are processed in the right way
	 * and make sure that the cooperation with toText works as supposed
	 *
	 * @return void
	 */
	public function test_getXMLContentWorkForCDATAContentWithinRoundtrip() {
		$input = '<data><![CDATA[ &lt;br /&gt;new line ]]></data>';
		$output = ' <br />new line ';

		$realoutp = $this->textConverter->toText($this->textConverter->getXMLContent(new SimpleXMLElement($input)));
		$this->assertEquals($output, $realoutp);
	}


	/**
	 * Check if wether CDATA tags are processed in the right way and that UTF8 characters also keep their identity
	 *
	 * @return void
	 */
	public function test_getXMLContentWorkForRandomCDATAContent() {
		$input = '<data><![CDATA[ € ' . chr(0xe2) . chr(0x80) . chr(0x94) . ']]></data>';
		$output = ' € ' . chr(0xe2) . chr(0x80) . chr(0x94);

		$this->assertEquals($output, $this->textConverter->getXMLContent(new SimpleXMLElement($input)));
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/tests/translation/class.tx_l10nmgr_service_textConverter_getXMLContent_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/tests/translation/class.tx_l10nmgr_service_textConverter_getXMLContent_testcase.php']);
}

?>