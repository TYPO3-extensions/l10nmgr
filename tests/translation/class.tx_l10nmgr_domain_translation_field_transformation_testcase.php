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
 *
 * {@inheritdoc}
 *
 * class.tx_l10nmgr_domain_translation_field_testcase.php
 *
 * @author Tolleiv Nietsch <tolleiv.nietsch@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id$
 * @date $Date$
 * @since 24.04.2009 - 14:57:30
 * @see tx_l10nmgr_tests_baseTestcase
 * @category database testcase
 * @package TYPO3
 * @subpackage l10nmgr
 * @access public
 */
class tx_l10nmgr_domain_translation_field_transformation_testcase extends tx_l10nmgr_tests_databaseTestcase {

	/**
	 * @var tx_l10nmgr_domain_translation_field
	 */
	protected $Field = null;

	public function setUp() {
		$this->skipInWrongWorkspaceContext();

		$this->createDatabase();
		$db = $this->useTestDatabase();

		$GLOBALS['TYPO3_DB']->debugOutput = TRUE;
		$this->importStdDB();

			// order of extension-loading is important !!!!
		$import = array ('cms','l10nmgr');
		$optional = array('static_info_tables','templavoila','realurl','aoe_realurlpath','languagevisibility','cc_devlog', 'aoe_xml2array');
		foreach($optional as $ext) {
			if (t3lib_extMgm::isLoaded($ext)) {
				$import[] = $ext;
			}
		}
		$this->importExtensions($import);
		$this->Field = new tx_l10nmgr_domain_translation_field();
	}

	public function tearDown() {
		$GLOBALS['TYPO3_DB']->sql_select_db(TYPO3_db);
		$this->Field = null;
	}

	/**
	 * check whether the detection-method returns success or fails
	 * after we made the attempt to detect it automatically
	 *
	 */
	public function test_transformationDetectionsReturnsStateOfProcess() {

		$xmlTrue = new SimpleXMLElement('<data transformationType="plain">datadatadata</data>');
		$xmlFalse = new SimpleXMLElement('<data>datadatadata</data>');

		$this->assertEquals(true,$this->Field->detectTransformationType($xmlTrue));
		$this->assertEquals(false,$this->Field->detectTransformationType($xmlFalse));
	}

	/**
	 * check whether the normal plain transformaiton attribute is passed on
	 * within XML
	 *
	 */
	public function test_canDetectPlainTransformationFromXML() {

		$xml = new SimpleXMLElement('<data transformationType="plain">datadatadata</data>');

		$this->assertEquals(true,$this->Field->detectTransformationType($xml));
		$this->assertEquals('plain',$this->Field->getTransformationType());

	}

	/**
	 * check whether the normal text transformaiton attribute is passed on
	 * within XML
	 *
	 */
	public function test_canDetectTextTransformationFromXML() {

		$xml = new SimpleXMLElement('<data transformationType="text">datadatadata</data>');

		$this->assertEquals(true,$this->Field->detectTransformationType($xml));
		$this->assertEquals('text',$this->Field->getTransformationType());

	}

	/**
	 * check whether the old transformations attribute from XML version 1.1 is passed on and detected as text transformation
	 *
	 */
	public function test_canDetectTextTransformationFromXML_v1_1() {

		$xml = new SimpleXMLElement('<data transformations="1">datadatadata</data>');

		$this->assertEquals(true,$this->Field->detectTransformationType($xml,'1.1.'));
		$this->assertEquals('text',$this->Field->getTransformationType());

	}

	/**
	 * check whether the normal html transformaiton attribute is passed on
	 * within XML
	 *
	 */
	public function test_canDetectHTMLTransformationFromXML() {

		$xml = new SimpleXMLElement('<data transformationType="html">datadatadata</data>');

		$this->assertEquals(true,$this->Field->detectTransformationType($xml));
		$this->assertEquals('html',$this->Field->getTransformationType());

	}

	/**
	 * check whether the auto detection works for TCA fields with the l10nTransformationType attribute
	 *
	 */
	public function test_canAutoDetectHTMLTransformationForTcaField() {

		$path = array(
			'table' => 'tx_'.substr(md5(rand()),-6),
			'uid'=>rand(0,100),
			'field' => 'field_'.substr(md5(rand()),-6)
		);

		$GLOBALS['TCA'][$path['table']]['columns'][$path['field']]['config']['l10nTransformationType'] = 1;

		$xml = new SimpleXMLElement('<data>datadatadata</data>');
		$this->Field->setFieldPath(implode(':',$path));
		$this->assertEquals(implode(':',$path),$this->Field->getFieldPath());
		$this->assertEquals('html',$this->Field->getTransformationType(1,true));

	}

	/**
	 * check whether the auto detection works for the tt_content html-element (bodytext field)
	 *
	 */
	public function test_canAutoDetectHTMLTransformationForExistingHtmlContentElement() {
		$this->importDataSet('/translation/fixtures/field_transformation/ttcontent.xml');
		$path = array(
			'table' => 'tt_content',
			'uid'=>'2',
			'field' => 'bodytext'
		);

		$this->Field->setFieldPath(implode(':',$path));
		$this->assertEquals(implode(':',$path),$this->Field->getFieldPath());
		$this->assertEquals('html',$this->Field->getTransformationType(1,true));
	}

	/**
	 * check whether the auto detection works for the tt_content html-element (header field)
	 * test is supposed to assure that we've no false positives for these fields
	 *
	 */
	public function test_canAutoDetectHTMLTransformationForExistingHtmlContentElement2() {
		$this->importDataSet('/translation/fixtures/field_transformation/ttcontent.xml');
		$path = array(
			'table' => 'tt_content',
			'uid'=>'2',
			'field' => 'header'
		);

		$this->Field->setFieldPath(implode(':',$path));
		$this->assertEquals(implode(':',$path),$this->Field->getFieldPath());
		$this->assertEquals('plain',$this->Field->getTransformationType(1,true));
	}

	/**
	 * check whether the autodetection works for new fields as well
	 *
	 */
	public function test_canAutoDetectHTMLTransformationForNewHtmlContentElement() {
		$this->importDataSet('/translation/fixtures/field_transformation/ttcontent.xml');
		$path = array(
			'table' => 'tt_content',
			'uid'=>'NEW/1/1',
			'field' => 'bodytext'
		);

		$this->Field->setFieldPath(implode(':',$path));
		$this->assertEquals(implode(':',$path),$this->Field->getFieldPath());
		$this->assertEquals('html',$this->Field->getTransformationType(1,true));

	}

	/**
	 * check whether the autodetection works for non-html fields
	 * avoid false-positives
	 *
	 */
	public function test_canAutoDetectNonHTMLTransformationForExistingHtmlContentElement() {
		$this->importDataSet('/translation/fixtures/field_transformation/ttcontent.xml');
		$path = array(
			'table' => 'tt_content',
			'uid'=>'11',
			'field' => 'bodytext'
		);

		$this->Field->setFieldPath(implode(':',$path));
		$this->assertEquals(implode(':',$path),$this->Field->getFieldPath());
		$this->assertEquals('plain',$this->Field->getTransformationType(10,true));

	}

	/**
	 * check whether the autodetection works for new fields as well
	 *
	 */
	public function test_canAutoDetectNonHTMLTransformationForNewHtmlContentElement() {
		$this->importDataSet('/translation/fixtures/field_transformation/ttcontent.xml');
		$path = array(
			'table' => 'tt_content',
			'uid'=>'NEW/1/10',
			'field' => 'bodytext'
		);

		$this->Field->setFieldPath(implode(':',$path));
		$this->assertEquals(implode(':',$path),$this->Field->getFieldPath());
		$this->assertEquals('plain',$this->Field->getTransformationType(10,true));

	}

	/**
	 * check whether the autodetection works for flexformfields
	 *
	 */
	public function test_canAutoDetectHTMLTransformationForFlexformField() {
		$this->importDataSet('/translation/fixtures/field_transformation/ttcontent.xml');
		$this->importDataSet('/translation/fixtures/field_transformation/tx_templavoila_datastructure.xml');
		$path = array(
			'table' => 'tt_content',
			'uid'=>'22',
			'field' => 'tx_templavoila_flex',
			'path' => 'data/sDEF/lDEF/field_author/vDEF'
		);

		$this->Field->setFieldPath(implode(':',$path));
		$this->assertEquals(implode(':',$path),$this->Field->getFieldPath());
		$this->assertEquals('html',$this->Field->getTransformationType($path['uid'],true));

	}

	/**
	 * check whether the autodetection works for new flexformfields
	 *
	 */
	public function test_canAutoDetectHTMLTransformationForNewFlexformField() {
		$this->importDataSet('/translation/fixtures/field_transformation/ttcontent.xml');
		$this->importDataSet('/translation/fixtures/field_transformation/tx_templavoila_datastructure.xml');
		$path = array(
			'table' => 'tt_content',
			'uid'=>'NEW/1/22',
			'field' => 'tx_templavoila_flex',
			'path' => 'data/sDEF/lDEF/field_author/vDEF'
		);

		$this->Field->setFieldPath(implode(':',$path));
		$this->assertEquals(implode(':',$path),$this->Field->getFieldPath());
		$this->assertEquals('html',$this->Field->getTransformationType(22,true));

	}


	/**
	 * make sure that the systems handles wrong transformationType attributes as supposed
	 *
	 */
	public function test_wrongTransformationAttributeDefaultsToPlain() {

		$xml = new SimpleXMLElement('<data transformationType="wrongsetting">datadatadata</data>');

		$this->assertEquals(false,$this->Field->detectTransformationType($xml));
		$this->assertEquals('plain',$this->Field->getTransformationType());

	}


	/**
	 * @test
	 *
	 * @access public
	 * @return void
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function autoDetectPlainTransformationForExistingHtmlContentElementOfCTypeTable() {
		$this->importDataSet('/translation/fixtures/field_transformation/ttcontent-table.xml');
		$path = array(
			'table' => 'tt_content',
			'uid'=>'11909',
			'field' => 'bodytext'
		);

		$this->Field->setFieldPath(implode(':',$path));
		$this->assertEquals(implode(':',$path),$this->Field->getFieldPath());
		$this->assertEquals('plain',$this->Field->getTransformationType(1,true));
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/tests/translation/class.tx_l10nmgr_domain_translation_field_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/tests/translation/class.tx_l10nmgr_domain_translation_field_testcase.php']);
}

?>