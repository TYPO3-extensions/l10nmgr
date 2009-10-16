<?php


class tx_l10nmgr_div_testcase extends tx_10nmgr_tests_base_testcase {

	public function test_translate() {
		$tempOutputFile = tempnam(sys_get_temp_dir(), get_class($this) . '_');
		tx_l10nmgr_domain_tools_div::translate(t3lib_extMgm::extPath('l10nmgr').'tests/tools/fixtures/simpleTextOnly.xml', $tempOutputFile);

		$this->assertEquals(
			file_get_contents(t3lib_extMgm::extPath('l10nmgr').'tests/tools/fixtures/simpleTextOnly_translated.xml'),
			file_get_contents($tempOutputFile)
		);

		unlink($tempOutputFile);
	}

}

?>