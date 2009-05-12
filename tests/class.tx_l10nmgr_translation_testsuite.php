<?php

require_once t3lib_extMgm::extPath('l10nmgr') . 'tests/translation/class.tx_l10nmgr_domain_translation_field_testcase.php';
require_once t3lib_extMgm::extPath('l10nmgr') . 'tests/translation/class.tx_l10nmgr_domain_translation_fieldCollection_testcase.php';
require_once t3lib_extMgm::extPath('l10nmgr') . 'tests/translation/class.tx_l10nmgr_domain_translation_element_testcase.php';
require_once t3lib_extMgm::extPath('l10nmgr') . 'tests/translation/class.tx_l10nmgr_domain_translation_elementCollection_testcase.php';
require_once t3lib_extMgm::extPath('l10nmgr') . 'tests/translation/class.tx_l10nmgr_domain_translation_page_testcase.php';
require_once t3lib_extMgm::extPath('l10nmgr') . 'tests/translation/class.tx_l10nmgr_domain_translation_pageCollection_testcase.php';
require_once t3lib_extMgm::extPath('l10nmgr') . 'tests/translation/class.tx_l10nmgr_domain_translation_data_testcase.php';
require_once t3lib_extMgm::extPath('l10nmgr') . 'tests/translation/class.tx_l10nmgr_domain_translationFactory_basic_testcase.php';
require_once t3lib_extMgm::extPath('l10nmgr') . 'tests/translation/class.tx_l10nmgr_domain_translationFactory_xmlData_testcase.php';

/**
 * Static test suite.
 */
class tx_l10nmgr_translation_testsuite extends tx_phpunit_testsuite {

	/**
	 * Constructs the test suite handler.
	 */
	public function __construct() {

		$this->setName('Translation package');

		$this->addTestSuite('tx_l10nmgr_domain_translation_field_testcase');
		$this->addTestSuite('tx_l10nmgr_domain_translation_fieldCollection_testcase');
		$this->addTestSuite('tx_l10nmgr_domain_translation_element_testcase');
		$this->addTestSuite('tx_l10nmgr_domain_translation_elementCollection_testcase');
		$this->addTestSuite('tx_l10nmgr_domain_translation_page_testcase');
		$this->addTestSuite('tx_l10nmgr_domain_translation_pageCollection_testcase');
		$this->addTestSuite('tx_l10nmgr_domain_translation_data_testcase');
		$this->addTestSuite('tx_l10nmgr_domain_translationFactory_basic_testcase');
		$this->addTestSuite('tx_l10nmgr_domain_translationFactory_xmlData_testcase');
	}

	/**
	 * Creates the suite.
	 */
	public static function suite() {
		return new self();
	}
}

?>