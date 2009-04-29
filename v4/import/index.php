<?php

unset($MCONF);
require ('conf.php');
require ($BACK_PATH.'init.php');
require ($BACK_PATH.'template.php');
$LANG->includeLLFile('EXT:l10nmgr/import/locallang.xml');
require_once (PATH_t3lib.'class.t3lib_scbase.php');

	// autoload the mvc
if (t3lib_extMgm::isLoaded('mvc')) {
	require_once(t3lib_extMgm::extPath('mvc').'common/class.tx_mvc_common_classloader.php');
	tx_mvc_common_classloader::loadAll();
} else {
	exit('Framework "mvc" not loaded!');
}

$editConf = unserialize(t3lib_div::_GET('returnEditConf'));

$editedRecord = array_keys($editConf['tx_l10nmgr_importdata']);

if ($editConf['tx_l10nmgr_importdata'][$editedRecord[0]] == 'new') {
	echo 'Edit was aborted';
} elseif ($editConf['tx_l10nmgr_importdata'][$editedRecord[0]] == 'edit') {
	echo "Currently edited importdata uid is: " . $editedRecord[0];
} else {
	echo "Unknown";
}


?>