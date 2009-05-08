<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_l10nmgr_cfg"] = Array (
	"ctrl" => $TCA["tx_l10nmgr_cfg"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "title,depth,sourceLangStaticId,tablelist,exclude,incfcewithdefaultlanguage"
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
		"filenameprefix" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_cfg.filenameprefix",
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
		"sourceLangStaticId" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_cfg.sourceLang",
			"displayCond" => "EXT:static_info_tables:LOADED:true",
			"config" => Array (
				'type' => 'select',
				'items' => Array (
					Array('',0),
				),
				'foreign_table' => 'static_languages',
				'foreign_table_where' => 'AND static_languages.pid=0 ORDER BY static_languages.lg_name_en',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		"incfcewithdefaultlanguage" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_cfg.incfcewithdefaultall",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "title,filenameprefix;;;;2-2-2, depth;;;;3-3-3, sourceLangStaticId, tablelist, exclude, include, displaymode, incfcewithdefaultlanguage")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);

$TCA["tx_l10nmgr_priorities"] = Array (
	"ctrl" => $TCA["tx_l10nmgr_priorities"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,title,description,languages,element"
	),
	"feInterface" => $TCA["tx_l10nmgr_priorities"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.xml:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"title" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_priorities.title",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
		"description" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_priorities.description",
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
			)
		),
		"languages" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_priorities.languages",
			"config" => Array (
				"type" => "select",
				"foreign_table" => "sys_language",
				"foreign_table_where" => "AND sys_language.pid=###SITEROOT### AND sys_language.hidden=0 ORDER BY sys_language.uid",
				"size" => 5,
				"minitems" => 0,
				"maxitems" => 100,
			)
		),
		"element" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_priorities.element",
			"config" => Array (
				"type" => "group",
				"internal_type" => "db",
				"allowed" => "*",
				"prepend_tname" => TRUE,
				"size" => 10,
				"minitems" => 0,
				"maxitems" => 100,
				"show_thumbs" => 1
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2, description;;;;3-3-3, languages, element")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);

$TCA["tx_l10nmgr_exportdata"] = Array (
	"ctrl" => $TCA["tx_l10nmgr_exportdata"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "title, crdate, tablelist,translation_lang,source_lang,filename"
	),
	"feInterface" => $TCA["tx_l10nmgr_exportdata"]["feInterface"],
	"columns" => Array (
		'l10ncfg_id' => Array(
			'exclude' => 1,
			'label' => 'LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_exportdata.l10ncfg_id',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tx_l10nmgr_cfg',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'export_type' => Array(
			"exclude" => 1,
			"label" => 'LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_exportdata.export_type',
			"config" => Array (
                "type" => "select",
                "items" => Array (
                    Array('XML', 'xml'),
                    Array('MS Excel', 'xls'),
                ),
                'default' => 'xml',
                "size" => 1,
                "minitems" => 0,
                "maxitems" => 1,
            )
		),
		"title" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_exportdata.title",
			"config" => Array (
				"type" => "input",
				"size" => "48",
				"eval" => "required",
			)
		),
		"translation_lang" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_exportdata.translation_lang",
			"config" => Array (
                "type" => "select",
                "foreign_table" => "sys_language",
                "foreign_table_where" => "ORDER BY sys_language.sorting",
                "size" => 1,
                "minitems" => 0,
                "maxitems" => 1,
            )
		),
		"source_lang" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_exportdata.source_lang",
			"config" => Array (
                "type" => "select",
                "items" => Array (
                    Array("-- default --",0),
                ),
                "foreign_table" => "sys_language",
                "foreign_table_where" => "ORDER BY sys_language.sorting",
                "size" => 1,
                "minitems" => 0,
                "maxitems" => 1,
            )
		),
		'filename' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_exportdata.filename',
			'config' => Array (
				'type' => 'group',
				"internal_type" => "file",
                "allowed" => "zip",
                "max_size" => 50000,
                "uploadfolder" => 'uploads/tx_l10nmgr/exportfiles/zips',
                "size" => 1,
                "minitems" => 0,
                "maxitems" => 1,
				'size' => '1',
				'readOnly' => 1,
			)
		),
		'checkforexistingexports' => Array(
			'exclude' => 1,
			'label' => 'LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_exportdata.checkforexistingexports',
			'config' => Array (
				'type' => 'check',
			)
		),
		'onlychangedcontent' => Array(
			'exclude' => 1,
			'label' => 'LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_exportdata.onlychangedcontent',
			'config' => Array (
				'type' => 'check',
			)
		),
		'nohidden' => Array(
			'exclude' => 1,
			'label' => 'LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_exportdata.nohidden',
			'config' => Array (
				'type' => 'check',
			)
		),
		'noxmlcheck' => Array(
			'exclude' => 1,
			'label' => 'LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_exportdata.noxmlcheck',
			'config' => Array (
				'type' => 'check',
			)
		),
		'checkutf8' => Array(
			'exclude' => 1,
			'label' => 'LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_exportdata.checkutf8',
			'config' => Array (
				'type' => 'check',
			)
		)
	),
	"types" => Array (
		"xml" => Array("showitem" => "l10ncfg_id, title, export_type;;1, --palette--;Languages;3, --palette--;Export Options;2"),
		"xls" => Array("showitem" => "l10ncfg_id, title, export_type, --palette--;Languages;3, --palette--;Export Options;2")
	),
	"palettes" => Array (
		"1" => Array(
			"showitem" => 'noxmlcheck, checkutf8',
			'canNotCollapse' => 1
		),
		"2" => Array(
			"showitem" => 'checkforexistingexports, onlychangedcontent, nohidden',
			'canNotCollapse' => 1
		),
		"3" => Array(
			"showitem" => 'source_lang, translation_lang',
			'canNotCollapse' => 1
		)
	)
);

$TCA['tx_l10nmgr_exportfiles'] = Array (
	'ctrl' => $TCA['tx_l10nmgr_exportfiles']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'exportdata_id, filename'
	),
	'feInterface' => $TCA['tx_l10nmgr_exportfiles']['feInterface'],
	'columns' => Array (
		'exportdata_id' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_exportfiles.exportdata_id',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tx_l10nmgr_exportdata',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'crdate' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_exportfiles.crdate',
			'config' => Array (
				'type' => 'input',
				'eval' => 'date',
				'size' => '48',
				'readOnly' => 1,
			)
		),
		'filename' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_exportfiles.filename',
			'config' => Array (
				'type' => 'group',
				"internal_type" => "file",
                "allowed" => "xml",
                "max_size" => 50000,
                "uploadfolder" => 'uploads/tx_l10nmgr/exportfiles',
                "size" => 1,
                "minitems" => 1,
                "maxitems" => 1,
				'size' => '48',
				'eval' => 'required',
				'readOnly' => 1,
			)
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'exportdata_id, filename')
	),
);

$TCA["tx_l10nmgr_importdata"] = Array (
	"ctrl" => $TCA["tx_l10nmgr_importdata"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "configuration_id, exportdata_id, importfiles"
	),
	"feInterface" => $TCA["tx_l10nmgr_importdata"]["feInterface"],
	"columns" => Array (
		'configuration_id' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_importdata.configuration_id',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tx_l10nmgr_cfg',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'exportdata_id' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_importdata.exportdata_id',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tx_l10nmgr_exportdata',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'importfiles' => Array(
			'exclude' => 1,
			'label' => 'LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_importdata.importfiles',
			'config' => Array (
				'type' => 'inline',
				'foreign_table' => 'tx_l10nmgr_importfiles',
				'foreign_field' => 'importdata_id',
				'minitems' => 0,
				'maxitems' => 100,
			)
		)
	),
	"types" => Array (
		"0" => Array("showitem" => "configuration_id, exportdata_id, importfiles")
	)
);

$TCA['tx_l10nmgr_importfiles'] = Array (
	'ctrl' => $TCA['tx_l10nmgr_importfiles']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'importdata_id, crdate, filename'
	),
	'feInterface' => $TCA['tx_l10nmgr_importfiles']['feInterface'],
	'columns' => Array (
		'importdata_id' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_importfiles.importdata_id',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tx_l10nmgr_importdata',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'filename' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_importfiles.filename',
			'config' => Array (
				'type' => 'group',
				"internal_type" => "file",
                "allowed" => "xml,zip",
                "max_size" => 50000,
                "uploadfolder" => 'uploads/tx_l10nmgr/importfiles',
                "size" => 1,
                "minitems" => 0,
                "maxitems" => 1,
			)
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'importdata_id, filename')
	),
);

$TCA['tx_l10nmgr_workflowstates'] = Array (
	'ctrl' => $TCA['tx_l10nmgr_workflowstates']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'exportdata_id, crdate, state'
	),
	'feInterface' => $TCA['tx_l10nmgr_workflowstates']['feInterface'],
	'columns' => Array (
		'exportdata_id' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_workflowstates.exportdata_id',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tx_l10nmgr_exportdata',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'crdate' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_workflowstates.crdate',
			'config' => Array (
				'type' => 'input',
				'eval' => 'date',
				'size' => '48',
				'readOnly' => 1,
			)
		),
		'state' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:l10nmgr/locallang_db.xml:tx_l10nmgr_workflowstates.state',
			'config' => Array (
				'type' => 'input',
				'size' => '48',
				'eval' => 'required',
				'readOnly' => 1,
			)
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'exportdata_id, crdate, state')
	),
);

?>