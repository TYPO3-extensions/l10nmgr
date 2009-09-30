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

require_once t3lib_extMgm::extPath('l10nmgr') . 'service/class.tx_l10nmgr_service_textConverter.php';

/**
 * This will test the round trip transformation of the textconverter.
 *
 * class.tx_l10nmgr_service_textConverter_testcase.php
 *
 * {@inheritdoc}
 *
 * @author Michael Klapper <klapper@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id$
 * @date $Date$
 * @since 07.05.2009 - 14:24:19
 * @see tx_phpunit_testcase
 * @package TYPO3
 * @subpackage tx_l10nmgr
 * @access public
 */
class tx_l10nmgr_service_textConverter_testcase extends tx_phpunit_testcase {

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
	 * Verify that the round transformation of an link is made as expected.
	 *
	 * That means that the typolink tag "<link>" is transformed to the corresponding "<a>" and back again to the "<link>" tag.
	 *
	 * @access public
	 * @test
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function roundTransformationOfTypoLinkInlcudingNewlineCharacter() {
		$fixtureText  = '<link 3>my link</link><strong>strong text</strong>'."\n" . 'test';
		$expectedText = $fixtureText;

		$this->assertEquals (
			$expectedText,
			$this->TextConverter->toText (
				$this->TextConverter->toXml($fixtureText)
			),
			'Transformation result is not equal to source.'
		);
	}

	/**
	 * Verify that the round transformation of the link incl. the further parameter are made as expected.
	 *
	 * The link parameter should be placed in the link correct and transformed them back to the typolinktag correct.
	 *
	 * @access public
	 * @test
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function roundTransformationOfTypoLinkWithFurtherParameterIncludingNewlineCharacter() {
		$fixtureText  = '<link 3 target class "title text" name>>my link</link><strong>strong text</strong>'."\n" . 'test';
		$expectedText = $fixtureText;

		$this->assertEquals (
			$expectedText,
			$this->TextConverter->toText (
				$this->TextConverter->toXml($fixtureText)
			),
			'Transformation result is not equal to source.'
		);
	}

	/**
	 * This test verify that entitys are handled correct both ways.
	 *
	 * Tested entities are:
	 * - &amp;
	 * - &nbsp;
	 * - &auml;
	 *
	 * @access public
	 * @test
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function roundTransformationOfBasicEntities() {
		$fixtureText           = '& &amp; &nbsp; ich&du &auml;';
		$expectedXML           = '<p>&amp; &amp; &nbsp; ich&amp;du ä</p>';
		$roundTripExpectedText = '& & &nbsp; ich&du ä';

		$this->assertEquals (
			$expectedXML,
			$this->TextConverter->toXML($fixtureText),
			'Convertion to XML produces unexpected text'
		);

		$this->assertEquals (
			$roundTripExpectedText,
			$this->TextConverter->toText (
				$this->TextConverter->toXML($fixtureText)
			),
			'Entities not round trip converted as expected.'
		);
	}

	/**
	 * Verify that the retrieved content within div section are don't contains paragraph tags.
	 *
	 * @access public
	 * @test
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function roundTransformationDontSetParagraphIntoDivElements() {
		$fixtureText  = '<div id="lipsum"> Lorem ipsum dolor sit amet, consectetur </div>';
		$expectedText = $fixtureText;

		$this->assertEquals (
			$expectedText,
			$this->TextConverter->toText($this->TextConverter->toXML($fixtureText)),
			'The transormation toXML work not as expected.'
		);
	}

	/**
	 * Verify that the dead links will not removed while transformating
	 *
	 * @access public
	 * @test
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function roundTransformationKeepDeadLink() {
		$fixtureRTE  = 'here comes some ... <link 92783928>this is my link</link>';

		$transformed = $this->TextConverter->toText (
			$this->TextConverter->toXML($fixtureRTE)
		);

		$this->assertEquals (
			$transformed,
			$fixtureRTE,
			'transformation result is not as expected'
		);
	}

	/**
	 * Verify that the '\xe2\x80\x94' is not touched
	 *
	 * @access public
	 * @test
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function roundTransformationOfHighLevelSign() {
		$fixtureRTE  = chr(0xe2).chr(0x80).chr(0x94); /* "\xe2\x80\x94" */

		$transformed = $this->TextConverter->toText (
			$this->TextConverter->toXML($fixtureRTE)
		);

		$this->assertEquals (
			$transformed,
			$fixtureRTE,
			'transformation result is not as expected'
		);
	}

	/**
	 * Verify that a simple Contentelement with a bullet list can be transformed.
	 *
	 * @access public
	 * @test
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 * @return void
	 */
	public function transformBulletlistWithTypolinksAndEntity(){
		$fixtureRTE 	= '<ul><li><link 25771 >WebEx Meeting Center</link></li><li><link 25611 >Who uses WebEx &amp; why?</link></li><li><link 24961 >Buy WebEx</link></li><li><link http://www.webex.com/go/live_demo >Live online demo</link></li><li><link http://www.webex.com/go/quick_tour 1050x700:resizable=0>Quick tour</link></li></ul>';
		$expected		= '<ul><li><link 25771>WebEx Meeting Center</link></li><li><link 25611>Who uses WebEx & why?</link></li><li><link 24961>Buy WebEx</link></li><li><link http://www.webex.com/go/live_demo>Live online demo</link></li><li><link http://www.webex.com/go/quick_tour 1050x700:resizable=0>Quick tour</link></li></ul>';

		$transformed = $this->TextConverter->toText (
			$this->TextConverter->toXML($fixtureRTE)
		);

		$this->assertEquals($transformed,$expected,'translation result is not as expected');
	}
	
	/**
	 * This test should ensure that the is no empty line after a heading when a field
	 * has beens converted to XML and back to Text.
	 *
	 * @access public
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 * @return void
	 */
	public function test_roundTransformationRTEWithHeadingsAndLinebreaksAfter(){
		$fixtureRTE 	= 	'<h2>WebEx is an easy way to exchange ideas and information with anyone, anywhere.</h2>'."\r\n".
							'It combines real-time desktop sharing with phone conferencing so everyone sees the same thing as you talk. It\'s far more productive than emailing files then struggling to get everyone on the same page in a phone conference. And, many times it eliminates the need for people to travel and meet on site.<br /><br /><link http://customer.com/ >Buy WebEx now</link>. WebEx is available for as low as<br />$59/mo for unlimited online meetings.'."\r\n".
							'<link http://customer.com/ >Take a free trial</link>. Get started now with a risk free 14-day<br />trial of WebEx.';
		
		$expectedResult =   '<h2>WebEx is an easy way to exchange ideas and information with anyone, anywhere.</h2>'."\n".
							'It combines real-time desktop sharing with phone conferencing so everyone sees the same thing as you talk. It\'s far more productive than emailing files then struggling to get everyone on the same page in a phone conference. And, many times it eliminates the need for people to travel and meet on site.<br /><br /><link http://customer.com/>Buy WebEx now</link>. WebEx is available for as low as<br />$59/mo for unlimited online meetings.'."\n".
							'<link http://customer.com/>Take a free trial</link>. Get started now with a risk free 14-day<br />trial of WebEx.';


		
		$xmlResult		= $this->TextConverter->toXML($fixtureRTE);
		
		$transformed = $this->TextConverter->toText (
			$xmlResult
		);

		
		
		$this->assertEquals (
			$expectedResult,
			$transformed,
			'translation result is not expected'
		);

		try{
			$this->TextConverter->isValidXML($xmlResult);

			$isValidXML = true;
		}catch(Exception $e){

		}
		$this->assertTrue($isValidXML);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/tests/translation/class.tx_l10nmgr_service_textConverter_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/tests/translation/class.tx_l10nmgr_service_textConverter_testcase.php']);
}

?>