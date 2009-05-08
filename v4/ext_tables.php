<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
/* if (TYPO3_MODE=="BE")    {

	t3lib_extMgm::addModule('txl10nmgrM1','','top',t3lib_extMgm::extPath($_EXTKEY).'mod1/');
	t3lib_extMgm::addModule("txl10nmgrM2",'txl10nmgrM1',"top",t3lib_extMgm::extPath($_EXTKEY)."mod2/");
}*/

if (TYPO3_MODE=='BE')   {
        $extPath = t3lib_extMgm::extPath($_EXTKEY);

                // add module before 'Help'
        if (!isset($TBE_MODULES['txl10nmgrM1']))     {
                $temp_TBE_MODULES = array();
                foreach($TBE_MODULES as $key => $val) {
                        if ($key == 'help') {
                                $temp_TBE_MODULES['txl10nmgrM3'] = '';
                                $temp_TBE_MODULES[$key] = $val;
                        } else {
                                $temp_TBE_MODULES[$key] = $val;
                        }
                }

                $TBE_MODULES = $temp_TBE_MODULES;
        }
        t3lib_extMgm::addModule('txl10nmgrM3', '', '', $extPath.'mod3/');
        t3lib_extMgm::addModule('txl10nmgrM3', 'txl10nmgrM1', 'bottom', $extPath.'mod1/');
        //t3lib_extMgm::addModule('txl10nmgrM3', 'txl10nmgrM4', 'bottom', $extPath.'translate/');
        t3lib_extMgm::addModule('txl10nmgrM3', 'txl10nmgrM2', 'bottom', $extPath.'mod2/');

        //add tsconfig for the export mvc controller
	t3lib_extMgm::addPageTSConfig('
plugin.tx_l10nmgr_controller_export.configuration {
	viewHelper.disable {
		linkCreator = 0
		label = 0
		fieldRenderer = 1
		tcaFieldRenderer = 1
		formElementRenderer = 0
	}
	pagesPerChunk = 1
	loadJQuery = 1
}

plugin.tx_l10nmgr_controller_import.configuration {
	viewHelper.disable {
		linkCreator = 0
		label = 0
		fieldRenderer = 1
		tcaFieldRenderer = 1
		formElementRenderer = 0
	}

	templates{
		form = EXT:l10nmgr/templates/import/form.php
	}
	
	loadJQuery = 1
}

plugin.tx_l10nmgr_controller_list.configuration {
	viewHelper.disable {
		linkCreator = 0
		label = 0
		fieldRenderer = 1
		tcaFieldRenderer = 1
		formElementRenderer = 0
	}
}
'
	);


}



t3lib_extMgm::allowTableOnStandardPages("tx_l10nmgr_cfg");
t3lib_extMgm::addLLrefForTCAdescr('tx_l10nmgr_cfg','EXT:l10nmgr/locallang_csh_l10nmgr.xml');

// Example for disabling localization of specific fields in tables like tt_content
// Add as many fields as you need

t3lib_div::loadTCA('tt_content');
//$TCA['tt_content']['columns']['imagecaption']['l10n_mode'] = 'exclude';
//$TCA['tt_content']['columns']['image']['l10n_mode'] = 'prefixLangTitle';
//$TCA['tt_content']['columns']['image']['l10n_display'] = 'defaultAsReadonly';

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



t3lib_extMgm::allowTableOnStandardPages("tx_l10nmgr_exportdata");

$TCA["tx_l10nmgr_exportdata"] = Array (
	"ctrl" => Array (
		'title' => 'LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_exportdata',
		'label' => 'title',
		'l10ncfg_id' => 'l10ncfg_id',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'source_lang' => 'source_lang',
		'translation_lang' => 'translation_lang',
		'source_lang' => 'source_lang',
		"default_sortby" => "ORDER BY title",
		"delete" => "deleted",
		'type' => 'export_type',
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_l10nmgr_cfg.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "title, source_lang, l10ncfg_id, crdate, delete, exclude, filename",
	)
);



t3lib_extMgm::allowTableOnStandardPages("tx_l10nmgr_exportfiles");

$TCA['tx_l10nmgr_exportfiles'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_exportfiles',
		'label' => 'filename',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY tstamp',
		'delete' => 'deleted',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_l10nmgr_cfg.gif',
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'filename',
	)
);



t3lib_extMgm::allowTableOnStandardPages("tx_l10nmgr_importdata");

$TCA["tx_l10nmgr_importdata"] = Array (
	"ctrl" => Array (
		'title' => 'LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_import',
		'label' => 'uid',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		"default_sortby" => "ORDER BY tstamp",
		"delete" => "deleted",
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_l10nmgr_cfg.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "",
	)
);

t3lib_extMgm::allowTableOnStandardPages("tx_l10nmgr_importfiles");

$TCA['tx_l10nmgr_importfiles'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_importfiles',
		'label' => 'filename',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY tstamp',
		'delete' => 'deleted',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_l10nmgr_cfg.gif',
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'filename',
	)
);



t3lib_extMgm::allowTableOnStandardPages("tx_l10nmgr_workflowstates");

$TCA['tx_l10nmgr_workflowstates'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_workflowstates',
		'label' => 'uid',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY tstamp',
		'delete' => 'deleted',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_l10nmgr_cfg.gif',
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'crdate, state',
	)
);

if (TYPO3_MODE=="BE")	{
	$GLOBALS["TBE_MODULES_EXT"]["xMOD_alt_clickmenu"]["extendCMclasses"][]=array(
		"name" => "tx_l10nmgr_cm1",
		"path" => t3lib_extMgm::extPath($_EXTKEY)."class.tx_l10nmgr_cm1.php"
	);
}

?>
