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
 * @author Michael Klapper <klapper@aoemedia.de>
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
class tx_l10nmgr_service_textConverter_toXML_testcase extends tx_l10nmgr_tests_baseTestcase {

	/**
	 * @var tx_l10nmgr_service_textConverter
	 */
	protected $TextConverter = null;

	/**
	 * Initialize a fresh instance of the tx_l10nmgr_service_textConverter
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
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
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function tearDown() {
		$this->TextConverter = null;
	}

	/**
	 * Verify that the Text from the database which are can be called as
	 * RTE text valid conveted to XML based text struckter.
	 *
	 * This includes the valid convert of each enttiy that it is valid XML.
	 *
	 * Valid are:
	 * - &amp;
	 * - &nbsp;
	 * - &lt;
	 * - &gt;
	 *
	 * The &nbsp; entity must be protected and can not be convertet to a simple " ".
	 *
	 * @access public
	 * @test
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function convertTextToXML() {
		$fixtureRTE  = '& &amp; &nbsp; =< &auml;';
		$expectedXML = '<p>&amp; &amp; &nbsp; =&lt; ä</p>';

        $this->assertEquals (
        	$expectedXML,
        	$this->TextConverter->toXML($fixtureRTE),
        	'Transfomation of the text failes.'
        );
	}

	/**
	 * Test that valid XHTML styled break tags (empty element) are keeped by the converter.
	 *
	 * @access public
	 * @test
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function keepValidBreakAndMaskTheLowerThanSign() {
		$fixtureText     = 'here coms some .. 8747()/=<="($<br />';
		$expectedText    = '<p>here coms some .. 8747()/=&lt;=&quot;($<br /></p>';

		$this->assertEquals (
			$expectedText,
			$this->TextConverter->toXML($fixtureText),
			'The transormation toXML work not as expected.'
		);
	}

	/**
	 * The break tag with an closing tag will not removed while it's valid XML structure.
	 *
	 * @access public
	 * @test
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function thatNoneEmptyElementStyledBreakTagsNotRemoved() {
		$fixtureText  = 'here coms some .. 8747()/=<="($<br></br>';
		//!TODO @dazi001 please clairify what should happend if there is a "<br></br>"
		$expectedText = '<p>here coms some .. 8747()/=&lt;=&quot;($<br></br></p>';

		$this->assertEquals (
			$expectedText,
			$this->TextConverter->toXML($fixtureText),
			'The transormation toXML work not as expected.'
		);
	}

	/**
	 * Verify that an "tx_mvc_exception_converter" is thrown
	 * if the given string is not XML conform.
	 *
	 * @expectedException tx_mvc_exception_converter
	 * @test
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function throwExceptionOnInvalidClosedHTMLLineBreak() {
		$fixtureText  = 'here coms some .. 8747()/=<="($<br>';

		$this->TextConverter->toXML($fixtureText);
	}

	/**
	 * Test that the htmlspecialchar "<" escaped with "&lt;".
	 *
	 * @access publc
	 * @test
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function escapeTheLowerSignCorrect() {
		$fixtureText  = '&lt;&gt;&quot;<br />';
		$expectedText = '<p>&lt;&gt;&quot;<br /></p>';

		$this->assertEquals (
			$expectedText,
			$this->TextConverter->toXML($fixtureText),
			'The transormation toXML work not as expected.'
		);
	}

	/**
	 * Verify that entities are converted to the UTF-8 charachter but
	 * the htmlspechialchar "&amp;" is untouched.
	 *
	 * @access public
	 * @test
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function convertEntiesToUTF8ButKeepTheHtmlSpecialCharAmp() {
		$fixtureText  = '&auml;<br />&amp;';
		$expectedText = '<p>ä<br />&amp;</p>';

		$this->assertEquals (
			$expectedText,
			$this->TextConverter->toXML($fixtureText),
			'The transormation toXML work not as expected.'
		);
	}

	/**
	 * Verify that a unicode entity are convert to the UTF-8 charakter as well.
	 *
	 * @access public
	 * @test
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function convertUnicodeCharacterToUTF8() {
		$fixtureText  = '&auml;<br />&amp;&#x20AC;';
		$expectedText = '<p>ä<br />&amp;€</p>';

		$this->assertEquals (
			$expectedText,
			$this->TextConverter->toXML($fixtureText),
			'The transormation toXML work not as expected.'
		);
	}

	/**
	 * Verify that no paragraph is wrapped around div-Tag.
	 *
	 * @access public
	 * @test
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function dontSetParagraphAroundDivElements() {
		$fixtureText  = '<div id="lipsum"> Lorem ipsum dolor sit amet, consectetur </div>';
		$expectedText = '<div id="lipsum"><p> Lorem ipsum dolor sit amet, consectetur </p></div>';

		$this->assertEquals (
			$expectedText,
			$this->TextConverter->toXML($fixtureText),
			'The transormation toXML work not as expected.'
		);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/tests/translation/class.tx_l10nmgr_service_textConverter_toXML_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/tests/translation/class.tx_l10nmgr_service_textConverter_toXML_testcase.php']);
}

?>