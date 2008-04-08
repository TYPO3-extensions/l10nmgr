<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
if (TYPO3_MODE=="BE")    {
        
    t3lib_extMgm::addModule("web","txl10nmgrM1","",t3lib_extMgm::extPath($_EXTKEY)."mod1/");
	t3lib_extMgm::addModule("user","txl10nmgrM2","top",t3lib_extMgm::extPath($_EXTKEY)."mod2/");
}
t3lib_extMgm::allowTableOnStandardPages("tx_l10nmgr_cfg");
t3lib_extMgm::addLLrefForTCAdescr('tx_l10nmgr_cfg','EXT:l10nmgr/locallang_csh_l10nmgr.php');

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

$TCA["tx_l10nmgr_priorities"] = Array (
	"ctrl" => Array (
		'title' => 'LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_priorities',		
		'label' => 'title',	
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		"sortby" => "sorting",	
		"delete" => "deleted",	
		"rootLevel" => 1,
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_l10nmgr_priorities.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, title, description, languages, element",
	)
);

$TCA["tx_l10nmgr_exportdata"] = Array (
	"ctrl" => Array (
		'title' => 'LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_cfg',		
		'label' => 'title',
		'l10ncfg_id' => 'l10ncfg_id',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		"default_sortby" => "ORDER BY title",
		"delete" => "deleted",	
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_l10nmgr_cfg.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "title, l10ncfg_id, crdate, delete",
	)
);

if (TYPO3_MODE=="BE")	{
	$GLOBALS["TBE_MODULES_EXT"]["xMOD_alt_clickmenu"]["extendCMclasses"][]=array(
		"name" => "tx_l10nmgr_cm1",
		"path" => t3lib_extMgm::extPath($_EXTKEY)."class.tx_l10nmgr_cm1.php"
	);
}

?>
