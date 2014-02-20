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
 * Testcase for text convert from XML to database text.
 *
 * class.tx_l10nmgr_service_textConverter_toText_testcase.php
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
class tx_l10nmgr_service_textConverter_toText_testcase extends tx_l10nmgr_tests_baseTestcase {

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
	 * Verify that the transformation of the a-tag incl. further parameter are made as expected into typolink tag.
	 *
	 * @access public
	 * @return void
	 */
	public function test_transformationOfLinkWithFurtherParameterToTypolink() {

		$fixtureText  = '<p><a href="' . t3lib_div::getIndpEnv('TYPO3_SITE_URL') . '?id=3" target="target" class="class" title="title text">&gt;my link</a><strong>strong text</strong></p><p>test</p>';
		$expectedText = '<link 3 target class "title text">>my link</link><b>strong text</b>' . CRLF . 'test';

		$this->assertEquals(
			$expectedText,
			$this->textConverter->toText(
				$fixtureText
			),
			'Problem with the conversion from HTML content (RTE) to the database.'
		);
	}

	/**
	 * This test verify that html entities are converted to UTF-8 character.
	 *
	 * Tested entities are:
	 * - &amp;
	 * - &nbsp;
	 * - &auml;
	 * - &quot;
	 *
	 * @access public
	 * @return void
	 */
	public function test_transformBasicEntitiesToUTF8() {
		$fixtureText  = '<p>&amp; &amp; &nbsp; ich&amp;du �&quot;</p>';
		$expectedText = '& & &nbsp; ich&du �"';

		$this->assertSame(
			$expectedText,
			$this->textConverter->toText(
				$fixtureText
			),
			'Entities not round trip converted as expected.'
		);
	}

	/**
	 * Test that the entities "&lt;", "&quot;" are conveted to "<" and """
	 *
	 * @access public
	 * @return void
	 */
	public function test_verifyThatDoubleQuoteAndLowerSignAreTransformedToUTF8() {
		$fixtureText  = '<p>here coms some .. 8747()/=&lt;=&quot;($<br /></p>';
		$expectedText = 'here coms some .. 8747()/=<="($<br />';

		$this->assertEquals(
			$expectedText,
			$this->textConverter->toText($fixtureText),
			'The transormation toText work not as expected.'
		);
	}

	/**
	 * Make sure that the not closed "<br>" is transformed
	 * to the XHTML valid empty tag like "<br />"
	 *
	 * @access public
	 * @return void
	 */
	public function test_thatNoneClosedBrTagsAreClosed() {
		$fixtureText  = '<p>here coms some .. 8747()/=&lt;=&quot;($<br></p>';
		$expectedText = 'here coms some .. 8747()/=<="($<br />';

		$this->assertEquals(
			$expectedText,
			$this->textConverter->toText($fixtureText),
			'The transormation toText work not as expected.'
		);
	}

	/**
	 * Make sure that the not valid closed "<br/>" is transformed
	 * to the XHTML valid empty tag like "<br />"
	 *
	 * @access public
	 * @return void
	 */
	public function test_thatClosedBrTagsWithoutWhitespaceAreClosedCorrectly() {
		$fixtureText  = '<p>here coms some .. 8747()/=&lt;=&quot;($<br/></p>';
		$expectedText = 'here coms some .. 8747()/=<="($<br />';

		$this->assertEquals(
			$expectedText,
			$this->textConverter->toText($fixtureText),
			'The transormation toText work not as expected.'
		);
	}

	/**
	 * Test that the htmlspecialchar "<", ">" and """ escaped with "&lt;","&gt;","&quot;"
	 *
	 * @access public
	 * @return void
	 */
	public function test_transformBasicHtmlspecialCharToUTF8() {
		$fixtureText  = '<p>&lt;&gt;&quot;<br /></p>';
		$expectedText = '<>"<br />';

		$this->assertEquals(
			$expectedText,
			$this->textConverter->toText($fixtureText),
			'The transormation toText work not as expected.'
		);
	}

	/**
	 *
	 * @access public
	 * @return void
	 */
	public function test_transformBasicHtmlspecialBetweenParagraph() {
		$fixtureText  = '<p>&lt;&gt;&quot;&lt;br /&gt;</p>';
		$expectedText = '<>"<br />';

		$this->assertEquals(
			$expectedText,
			$this->textConverter->toText($fixtureText),
			'The transormation toText work not as expected.'
		);
	}

	/**
	 * Test that the htmlspecialchar are escaped while we import the text into flexform XML structure.
	 *
	 * @access public
	 * @return void
	 */
	public function test_transformKeepBasicHtmlSpecialCharWhileWeImportForFlexForms() {
		$fixtureText  = '<p>&lt;&gt;&quot;<br />&nbsp;</p>';
		$expectedText = '&lt;&gt;&quot;&lt;br /&gt;&nbsp;';

		$this->assertEquals(
			$expectedText,
			$this->textConverter->toText($fixtureText, TRUE),
			'The transormation toText for flexform fields work not as expected.'
		);
	}

	/**
	 *
	 * @access public
	 * @return void
	 */
	public function test_transformAmpToUTF8WithoutParagraph() {
		$fixtureText  = '&amp;';
		$expectedText = '&';

		$this->assertEquals(
			$expectedText,
			$this->textConverter->toText($fixtureText),
			'The transormation &amp to & also when no paragraph is wrapped around.'
		);
	}

	/**
	 *
	 * @access public
	 * @return void
	 */
	public function test_transformNbspToUTF8WithoutParagraph() {
		$fixtureText  = '&nbsp;';
		$expectedText = '&nbsp;';

		$this->assertEquals(
			$expectedText,
			$this->textConverter->toText($fixtureText),
			'The transormation &amp to & also when no paragraph is wrapped around.'
		);
	}

	/**
	 *
	 * @access public
	 * @return void
	 */
	public function test_transformNbspToUTF8WithParagraph() {
		$fixtureText  = '<p>&nbsp;</p>';
		$expectedText = '';

		$this->assertEquals(
			$expectedText,
			$this->textConverter->toText($fixtureText),
			''
		);
	}

	/**
	 * @access public
	 * @test
	 * @return void
	 */
	public function convertDirtyHeaderValue() {
		$fixture  = '<p>This is a dirty header element &amp; uses an <br> ampersand translated </p>';
		$expected = 'This is a dirty header element & uses an <br /> ampersand translated ';

		$this->assertEquals(
			$expected,
			$this->textConverter->toText($fixture),
			''
		);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/tests/translation/class.tx_l10nmgr_service_textConverter_toText_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/tests/translation/class.tx_l10nmgr_service_textConverter_toText_testcase.php']);
}

?>