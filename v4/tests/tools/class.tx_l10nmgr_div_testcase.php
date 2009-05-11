<?php

require_once t3lib_extMgm::extPath('l10nmgr').'models/tools/class.tx_l10nmgr_div.php';

class tx_l10nmgr_div_testcase extends tx_phpunit_testcase {

	public function test_translate() {
		$tempOutputFile = tempnam(sys_get_temp_dir(), get_class($this) . '_');
		tx_l10nmgr_div::translate(t3lib_extMgm::extPath('l10nmgr').'tests/tools/fixtures/simpleTextOnly.xml', $tempOutputFile);

		$this->assertEquals(
			file_get_contents(t3lib_extMgm::extPath('l10nmgr').'tests/tools/fixtures/simpleTextOnly_translated.xml'),
			file_get_contents($tempOutputFile)
		);

		unlink($tempOutputFile);
	}

}

?>