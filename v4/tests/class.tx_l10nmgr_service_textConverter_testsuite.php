<?php

require_once t3lib_extMgm::extPath('mvc') . 'common/class.tx_mvc_common_classloader.php';
require_once t3lib_extMgm::extPath('l10nmgr') . 'tests/converter/class.tx_l10nmgr_service_textConverter_toXML_testcase.php';
require_once t3lib_extMgm::extPath('l10nmgr') . 'tests/converter/class.tx_l10nmgr_service_textConverter_toText_testcase.php';

/**
 * Static test suite.
 */
class tx_l10nmgr_service_textConverter_testsuite extends tx_phpunit_testsuite {

	/**
	 * Constructs the test suite handler.
	 */
	public function __construct() {

		$this->setName('Text converter');

		$this->addTestSuite('tx_l10nmgr_service_textConverter_toXML_testcase');
		$this->addTestSuite('tx_l10nmgr_service_textConverter_toText_testcase');
	}

	/**
	 * Creates the suite.
	 */
	public static function suite() {
		return new self();
	}
}

?>