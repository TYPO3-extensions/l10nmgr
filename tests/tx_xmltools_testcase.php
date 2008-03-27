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

/**
 * Test case for checking the xmltools class
 *
 * 
 *
 * @author	Daniel Pötzinger
 */


require_once(t3lib_extMgm::extPath('l10nmgr').'models/tools/class.tx_l10nmgr_xmltools.php');
require_once (PATH_t3lib.'class.t3lib_tcemain.php');

class tx_xmltools_testcase extends tx_phpunit_testcase {
	
	public function test_isXMLString()
	{		
		//prepare testdatas
		$_fixture_noXML='<a>my test<p>test</p>';
		$_fixture_noXML2='my test & du';
		$_fixture_validXML='<a>my test</a><p>test</p><strong>&amp;<i></i><br /></strong>';		
		//do the tests
		$xmlTools=t3lib_div::makeInstance('tx_l10nmgr_xmltools');	      
    $this->assertFalse($xmlTools->isValidXMLString($_fixture_noXML), "invalid xml is detected as XML!");    
    $this->assertFalse($xmlTools->isValidXMLString($_fixture_noXML2), "invalid xml 2 is detected as XML!");    
    $this->assertTrue($xmlTools->isValidXMLString($_fixture_validXML), "XML should be valid");   
    
	}

	public function test_simpleTransformationTest()
	{
		//prepare testdata
		$fixtureRTE='<link 3>my link</link><strong>strong text</strong>'."\n";
		$fixtureRTE.='test';		
		//do the test:
		$xmlTools=t3lib_div::makeInstance('tx_l10nmgr_xmltools');										
		$transformed=$xmlTools->XML2RTE($xmlTools->RTE2XML($fixtureRTE));		
    $this->assertEquals($transformed, $fixtureRTE, "transformationresult:".$transformed." is not equal to source.");
	}
	
	public function test_transformationLinkTagTest()
	{
		//prepare testdata
		$fixtureRTE='<link 3 target class "name">my link</link><strong>strong text</strong>'."\n";
		$fixtureRTE.='test';
		
		//do the test:
		$xmlTools=t3lib_div::makeInstance('tx_l10nmgr_xmltools');										
		$transformed=$xmlTools->XML2RTE($xmlTools->RTE2XML($fixtureRTE));
		
    $this->assertEquals($transformed, $fixtureRTE, "transformationresult:".$transformed." is not equal to source.");    
    
    
	}
	
	
	public function test_transformationEntityTest()
	{
		//prepare testdata
		$fixtureRTE='& &amp; &nbsp; ich&du';
		
		$xmlTools=t3lib_div::makeInstance('tx_l10nmgr_xmltools');										
		$transfxml=$xmlTools->RTE2XML($fixtureRTE);
		
		//test if entities and & were transformed correct
		$this->assertEquals($transfxml, '<p>&amp; &amp;amp; &nbsp; ich&amp;du</p>', "entities transformed incorrect"); 
		
		//do the test:		
		$transformed=$xmlTools->XML2RTE($transfxml);
		
    $this->assertEquals($transformed, $fixtureRTE, "transformationresult is not equal to source.");    
    
    
	}
	
	
	
	

}
?>