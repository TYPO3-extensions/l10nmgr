<?php

require_once t3lib_extMgm::extPath('mvc')     . 'common/class.tx_mvc_common_classloader.php';
require_once t3lib_extMgm::extPath('l10nmgr') . 'tests/service/class.tx_l10nmgr_service_importTranslation_basic_testcase.php';
require_once t3lib_extMgm::extPath('l10nmgr') . 'tests/service/class.tx_l10nmgr_service_importTranslation_headertest_testcase.php';

/**
 * Static test suite.
 */
class tx_l10nmgr_service_importTranslation_testsuite extends tx_phpunit_testsuite {

	/**
	 * Constructs the test suite handler.
	 */
	public function __construct() {

		$this->setName('Translation import');

		$this->addTestSuite('tx_l10nmgr_service_importTranslation_basic_testcase');
		$this->addTestSuite('tx_l10nmgr_service_importTranslation_headertest_testcase');
	}

	/**
	 * Creates the suite.
	 */
	public static function suite() {
		return new self();
	}
}

?>