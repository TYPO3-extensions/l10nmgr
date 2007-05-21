<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_l10nmgr_cfg"] = Array (
	"ctrl" => $TCA["tx_l10nmgr_cfg"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "title,depth,tablelist,exclude"
	),
	"feInterface" => $TCA["tx_l10nmgr_cfg"]["feInterface"],
	"columns" => Array (
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_cfg.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "48",	
				"eval" => "required",
			)
		),
		"depth" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_cfg.depth",		
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_cfg.depth.I.0", "0"),
					Array("LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_cfg.depth.I.1", "1"),
					Array("LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_cfg.depth.I.2", "2"),
					Array("LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_cfg.depth.I.3", "3"),
					Array("LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_cfg.depth.I.4", "100"),
					Array("LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_cfg.depth.I.-1", "-1"),
				),
				"size" => 1,	
				"maxitems" => 1,
			)
		),
		"displaymode" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_cfg.displaymode",		
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_cfg.displaymode.I.0", "0"),
					Array("LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_cfg.displaymode.I.1", "1"),
					Array("LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_cfg.displaymode.I.2", "2"),
				),
				"size" => 1,	
				"maxitems" => 1,
			)
		),
		"tablelist" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_cfg.tablelist",		
			"config" => Array (
				'type' => 'select',
				'special' => 'tables',
				'size' => '5',
				'autoSizeMax' => 50,
				'maxitems' => 20,
				'renderMode' => $GLOBALS['TYPO3_CONF_VARS']['BE']['accessListRenderMode'],
				'iconsInOptionTags' => 1,
			)
		),
		"exclude" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_cfg.exclude",		
			"config" => Array (
				"type" => "text",
				"cols" => "48",	
				"rows" => "3",
			)
		),
		"include" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_cfg.include",		
			"config" => Array (
				"type" => "text",
				"cols" => "48",	
				"rows" => "3",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "title;;;;2-2-2, depth;;;;3-3-3, tablelist, exclude, include, displaymode")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);
?>