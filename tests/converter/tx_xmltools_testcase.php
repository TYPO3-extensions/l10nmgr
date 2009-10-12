<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007 Kasper Ligaard (ligaard@daimi.au.dk)
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

require_once(t3lib_extMgm::extPath('l10nmgr').'models/tools/class.tx_l10nmgr_xmltools.php');
require_once (PATH_t3lib.'class.t3lib_tcemain.php');

/**
 * {@inheritdoc}
 *
 * class.tx_xmltools_testcase.php
 *
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 * @author Daniel Poetzinger <daniel.poetzinger@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id$
 * @date $Date$
 * @since 06.05.2009 - 14:57:30
 * @see tx_phpunit_testcase
 * @category testcase
 * @package TYPO3
 * @subpackage tx_l10nmgr
 * @access public
 */
class tx_xmltools_testcase extends tx_phpunit_testcase {

	/**
	 * @var 	tx_l10nmgr_xmltools 		$XMLtools
	 */
	protected $XMLtools = null;

	/**
	 * Create the XMLtools object
	 *
	 * @abstract public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function setUp() {

		try {
			$this->XMLtools = t3lib_div::makeInstance('tx_l10nmgr_xmltools');
		} catch(Exception $e) {
			$this->markTestSkipped($e->getMessage());
		}
	}

	/**
	 * Create the XMLtools object
	 *
	 * @abstract public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function tearDown() {
		$this->XMLtools = null;
	}

	/**
	 * Take sure that the isValidXMLString method can detect invalid XML structures
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function test_isXMLString() {
		$fixtureNoXML    = '<a>my test<p>test</p>';
		$fixtureNoXML2   = 'my test & du';
		$fixtureValidXML = '<a>my test</a><p>test</p><strong>&amp;<i></i><br /></strong>';

		$this->assertFalse (
			$this->XMLtools->isValidXMLString($fixtureNoXML),
			"invalid xml is detected as XML!"
		);
		$this->assertFalse (
			$this->XMLtools->isValidXMLString($fixtureNoXML2),
			"invalid xml 2 is detected as XML!"
		);
		$this->assertTrue (
			$this->XMLtools->isValidXMLString($fixtureValidXML),
			"XML should be valid"
		);
	}

	/**
	 * Verify that the round transformation of an link is made as expected.
	 *
	 * That means that the typolink tag "<link>" is transformed to the corresponding "<a>" and back again to the "<link>" tag.
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function test_simpleTransformationTest() {
		$fixtureRTE  = '<link 3>my link</link><strong>strong text</strong>'."\n";
		$fixtureRTE .= 'test';

		$transformed = $this->XMLtools->XML2RTE (
			$this->XMLtools->RTE2XML($fixtureRTE)
		);

		$this->assertEquals (
			$transformed,
			$fixtureRTE,
			"transformationresult:" . $transformed . " is not equal to source."
		);
	}

	/**
	 * Verify that the round transformation of the link incl. the further parameter are made as expected.
	 *
	 * The link parameter should be placed in the link correct and transformed them back to the typolinktag correct.
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function test_transformationLinkTagTest() {
		$fixtureRTE  = '<link 3 target class "title text" name>my link</link><strong>strong text</strong>'."\n";
		$fixtureRTE .= 'test';

		$transformed = $this->XMLtools->XML2RTE (
			$this->XMLtools->RTE2XML($fixtureRTE)
		);

		$this->assertEquals (
			$fixtureRTE,
			$transformed,
			"transformationresult:".$transformed." is not equal to source."
		);
	}

	/**
	 * This test verify that entitys are handled correct both ways.
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function test_transformationEntityTest()	{
		$fixtureRTE = '& &amp; &nbsp; ich&du';

		$content = $this->XMLtools->RTE2XML($fixtureRTE);
		$transformedXML = $content;

		$this->assertEquals (
			$transformedXML,
			'<p>&amp; &amp;amp; &nbsp; ich&amp;du</p>',
			'entities transformed incorrect'
		);

		$transformedBackToRTEstyle = $this->XMLtools->XML2RTE($transformedXML);
		$this->assertEquals (
			$transformedBackToRTEstyle,
			$fixtureRTE,
			"transformation result is not equal to source."
		);
	}


	/**
	 * This test verify that the text to transform are only returned
	 * if it is possible when there are valid XML structure given to the RTE2XML.
	 *
	 * For example:
	 * - "<" shold be escaped to "&quot;"
	 * - "<br />", "<br></br>" are valid
	 * - "<br>" are not valid, the method RTE2XML will return false
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function test_keepXHTMLValidBRTest() {
		$fixtureRTE     = 'here coms some .. 8747()/=<="($<br />';
		$fixtureRTE2    = 'here coms some .. 8747()/=<="($<br></br>';
		$fixtureFailRTE = 'here coms some .. 8747()/=<="($<br>';

			// RTE2XML should retrive false while the "<br>" is not a valid XML structure
			// if we want to use breaks they should formated like "<br />" or "<br></br>"
		$this->assertFalse (
			($this->XMLtools->RTE2XML($fixtureFailRTE)),
			'Method RTE2XML returned content, but we expected false while no valid XML string was given.'
		);

		$this->assertEquals (
			$this->XMLtools->RTE2XML($fixtureRTE2),
			'<p>here coms some .. 8747()/=&lt;=&quot;($<br></br></p>',
			'Transformation are not made as excpeted.'
		);

		$transformed = $this->XMLtools->XML2RTE (
			$this->XMLtools->RTE2XML($fixtureRTE)
		);
		$this->assertEquals (
			$transformed,
			$fixtureRTE,
			'Transformation are not made as excpeted.'
		);
	}

	/**
	 * Verify that the "<br />" tags are keept while round trip transformed.
	 * The breaks are placed at the end of an list item.
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function testr_keepXHTMLValidBRInnerList() {
		$fixtureRTE = '<ul><li>    Sign on with a single user name and password to simplify user management and support    </li><li> Easily share individual applications and documents with the click of a mouse    </li><li> Simplify meeting participation with callbacks and 800 numbers through our integrated telephony and audio<br /><br /> </li></ul>';

		$transformed  = $this->XMLtools->XML2RTE (
			$this->XMLtools->RTE2XML($fixtureRTE)
		);

		$this->assertEquals (
			$transformed,
			$fixtureRTE,
			'transformation result is not as expected'
		);
	}

	/**
	 * Verify that the dead links will not removed while transformating
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function test_removeDeadLinkHandlingTest() {
		$fixtureRTE  = 'here comes some ... <link 92783928>this is my link</link>';

		$transformed = $this->XMLtools->XML2RTE (
			$this->XMLtools->RTE2XML($fixtureRTE)
		);

		$this->assertEquals (
			$transformed,
			$fixtureRTE,
			'transformation result is not as expected'
		);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/tests/tx_xmltools_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/tests/tx_xmltools_testcase.php']);
}

?>