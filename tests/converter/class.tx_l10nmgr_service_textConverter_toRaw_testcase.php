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
class tx_l10nmgr_service_textConverter_toRaw_testcase extends tx_l10nmgr_tests_baseTestcase {

	/**
	 * @var tx_l10nmgr_service_textConverter
	 */
	protected $TextConverter = null;

	/**
	 * Initialize a fresh instance of the tx_l10nmgr_service_textConverter
	 *
	 * @access public
	 * @author Tolleiv Nietsch <nietsch@aoemedia.de>
	 * @return void
	 */
	public function setUp() {
		global $BE_USER;
		$this->assertEquals($BE_USER->user['workspace_id'],0,'Run this test only in the live workspace' );
		
		$this->TextConverter = new tx_l10nmgr_service_textConverter();
	}

	/**
	 * Reset the tx_l10nmgr_service_textConverter
	 *
	 * @access public
	 * @author Tolleiv Nietsch <nietsch@aoemedia.de>
	 * @return void
	 */
	public function tearDown() {
		$this->TextConverter = null;
	}

	/**
	 * toRaw is supposed to unify the format of the linebreaks
	 *
	 * @access public
	 * @test
	 * @author Tolleiv Nietsch <nietsch@aoemedia.de>
	 * @return void
	 */
	public function newlineReplacementTowardsUnixFormatWork() {

		$fixtureText = array("line1\nline2","line1\n\rline2","line1\r\nline2");
		$expectedText = "line1\nline2";

		$this->assertEquals (
			$expectedText,
			$this->TextConverter->toRaw($fixtureText[0]),
			'Regular newline is lost during transformation'
		);
		$this->assertEquals (
			$expectedText,
			$this->TextConverter->toRaw($fixtureText[1]),
			'Windows newline is lost during transformation'
		);
		$this->assertEquals (
			$expectedText,
			$this->TextConverter->toRaw($fixtureText[2]),
			'Mac Newline is lost during transformation'
		);
	}

	/**
	 * we expect to get an exception whenever the created XML isn't valid
	 *
	 *
	 * @access public
	 * @test
	 * @expectedException tx_mvc_exception_converter
	 * @see http://www.w3.org/TR/2006/REC-xml-20060816/#charsets
	 * @author Tolleiv Nietsch <nietsch@aoemedia.de>
	 * @return void
	 */
	public function invalidXMLthrowsException() {
		//$fixtureText = '<xml><[CDATA[</xml>';
		$fixtureText = chr(7);		// Bell-Character ASCII 7 is no valid XML
		$this->TextConverter->toRaw($fixtureText);
	}

	/**
	 * Ensure that the API works as supposed and nothing is broken - it this behavious was changed this might cause lot's of problems within other tests and functions
	 *
	 * @access public
	 * @test
	 * @author Tolleiv Nietsch <nietsch@aoemedia.de>
	 * @return void
	 */
	public function entitiesArenTConvertedByDefault() {
		$fixtureText = '<xml attr="blub">&amp;</xml>';

		$this->assertEquals (
			$fixtureText,
			$this->TextConverter->toRaw($fixtureText),
			''
		);
	}

	/**
	 *
	 * @access public
	 * @test
	 * @see http://www.w3.org/TR/2006/REC-xml-20060816/#charsets
	 * @author Tolleiv Nietsch <nietsch@aoemedia.de>
	 * @return void
	 */
	public function entitiesAreConvertedOptional() {
		$fixtureText = '<xml attr="blub">&amp;<[CDATA[</xml>';
		$expectedText = '&lt;xml attr=&quot;blub&quot;&gt;&amp;amp;&lt;[CDATA[&lt;/xml&gt;';

		$this->assertEquals (
			$expectedText,
			$this->TextConverter->toRaw($fixtureText, null, null, true),
			''
		);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/tests/translation/class.tx_l10nmgr_service_textConverter_toXML_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/tests/translation/class.tx_l10nmgr_service_textConverter_toXML_testcase.php']);
}

?>