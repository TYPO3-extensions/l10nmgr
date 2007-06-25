<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
if (TYPO3_MODE=="BE")    {
        
    t3lib_extMgm::addModule("web","txl10nmgrM1","",t3lib_extMgm::extPath($_EXTKEY)."mod1/");
}
t3lib_extMgm::allowTableOnStandardPages("tx_l10nmgr_cfg");

$TCA["tx_l10nmgr_cfg"] = Array (
	"ctrl" => Array (
		'title' => 'LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_cfg',		
		'label' => 'title',	
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		"default_sortby" => "ORDER BY title",	
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_l10nmgr_cfg.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "title, depth, tablelist, exclude",
	)
);


if (TYPO3_MODE=="BE")	{
	$GLOBALS["TBE_MODULES_EXT"]["xMOD_alt_clickmenu"]["extendCMclasses"][]=array(
		"name" => "tx_l10nmgr_cm1",
		"path" => t3lib_extMgm::extPath($_EXTKEY)."class.tx_l10nmgr_cm1.php"
	);
}
?>