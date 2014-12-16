<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "l10nmgr_new".
 *
 * Auto generated 28-08-2014 16:03
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Localization Manager',
	'description' => 'Module for managing localization import and export',
	'category' => 'module',
	'shy' => 0,
	'version' => '3.6.2',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => 'cm1,cm2,cm3,mod1,mod2',
	'state' => 'beta',
	'uploadfolder' => 1,
	'createDirs' => 'uploads/tx_l10nmgr/settings,uploads/tx_l10nmgr/saved_files,uploads/tx_l10nmgr/jobs,uploads/tx_l10nmgr/jobs/out,uploads/tx_l10nmgr/jobs/in,uploads/tx_l10nmgr/jobs/done,uploads/tx_l10nmgr/jobs/_cmd',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Kasper Skaarhoej, Daniel Zielinski, Daniel Poetzinger, Fabian Seltmann, Andreas Otto',
	'author_email' => 'kasperYYYY@typo3.com, info@loctimize.com',
	'author_company' => 'Localization Manager Team',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'php' => '5.2.0-0.0.0',
			'typo3' => '6.2.0-6.2.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
			'static_info_tables' => '',
		),
	),
	'_md5_values_when_last_written' => 'a:101:{s:9:"ChangeLog";s:4:"adfe";s:30:"class.l10nmgr_tcemain_hook.php";s:4:"5ef5";s:24:"class.tx_l10nmgr_cm1.php";s:4:"2591";s:26:"class.tx_l10nmgr_index.php";s:4:"ae43";s:24:"class.tx_l10nmgr_zip.php";s:4:"9c4e";s:16:"ext_autoload.php";s:4:"d43a";s:21:"ext_conf_template.txt";s:4:"e166";s:12:"ext_icon.gif";s:4:"ec72";s:17:"ext_localconf.php";s:4:"c937";s:14:"ext_tables.php";s:4:"68ea";s:14:"ext_tables.sql";s:4:"14fe";s:13:"flags_new.png";s:4:"88c4";s:14:"flags_none.png";s:4:"4f46";s:12:"flags_ok.png";s:4:"9407";s:17:"flags_unknown.png";s:4:"13df";s:16:"flags_update.png";s:4:"ca64";s:23:"icon_tx_l10nmgr_cfg.gif";s:4:"ec72";s:30:"icon_tx_l10nmgr_priorities.gif";s:4:"dc05";s:13:"locallang.xml";s:4:"c6f2";s:25:"locallang_csh_l10nmgr.xml";s:4:"f83c";s:16:"locallang_db.xml";s:4:"c310";s:10:"README.txt";s:4:"ee2d";s:7:"tca.php";s:4:"0352";s:4:"TODO";s:4:"15a7";s:18:"cli/cli.export.php";s:4:"5862";s:18:"cli/cli.import.php";s:4:"7af3";s:17:"cli/locallang.xml";s:4:"c9e5";s:10:"cli/README";s:4:"bc83";s:13:"cm1/clear.gif";s:4:"cc11";s:15:"cm1/cm_icon.gif";s:4:"ec72";s:12:"cm1/conf.php";s:4:"814c";s:13:"cm1/index.php";s:4:"1d18";s:17:"cm1/locallang.xml";s:4:"7ba3";s:15:"cm2/cm_icon.gif";s:4:"8074";s:12:"cm2/conf.php";s:4:"3845";s:13:"cm2/index.php";s:4:"02f5";s:17:"cm2/locallang.xml";s:4:"a551";s:15:"cm3/cm_icon.gif";s:4:"8074";s:12:"cm3/conf.php";s:4:"bf88";s:13:"cm3/index.php";s:4:"d14d";s:17:"cm3/locallang.xml";s:4:"f9a4";s:14:"doc/manual.sxw";s:4:"29e8";s:12:"doc/TODO.txt";s:4:"2411";s:19:"doc/wizard_form.dat";s:4:"9c22";s:20:"doc/wizard_form.html";s:4:"e328";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"20cc";s:14:"mod1/index.php";s:4:"ee7d";s:18:"mod1/locallang.xml";s:4:"06f0";s:22:"mod1/locallang_mod.xml";s:4:"45a2";s:19:"mod1/moduleicon.gif";s:4:"ec72";s:14:"mod2/clear.gif";s:4:"cc11";s:13:"mod2/conf.php";s:4:"5c8c";s:14:"mod2/index.php";s:4:"77ec";s:13:"mod2/list.php";s:4:"c7fa";s:18:"mod2/locallang.xml";s:4:"5d30";s:22:"mod2/locallang_mod.xml";s:4:"6c48";s:19:"mod2/moduleicon.gif";s:4:"2dbb";s:47:"models/class.tx_l10nmgr_CATXMLImportManager.php";s:4:"a180";s:55:"models/class.tx_l10nmgr_l10nAccumulatedInformations.php";s:4:"f8d1";s:43:"models/class.tx_l10nmgr_l10nBaseService.php";s:4:"5d72";s:45:"models/class.tx_l10nmgr_l10nConfiguration.php";s:4:"21d6";s:48:"models/class.tx_l10nmgr_mkPreviewLinkService.php";s:4:"65d4";s:43:"models/class.tx_l10nmgr_translationData.php";s:4:"9fa3";s:50:"models/class.tx_l10nmgr_translationDataFactory.php";s:4:"ef26";s:39:"models/tools/class.tx_l10nmgr_tools.php";s:4:"016b";s:43:"models/tools/class.tx_l10nmgr_utf8tools.php";s:4:"86f8";s:42:"models/tools/class.tx_l10nmgr_xmltools.php";s:4:"b7bf";s:27:"res/contrib/jquery-1.2.3.js";s:4:"4a3d";s:32:"res/contrib/jquery.dimensions.js";s:4:"0f94";s:32:"res/contrib/jquery.scrollable.js";s:4:"34ed";s:30:"res/contrib/jquery.tooltip.css";s:4:"8c48";s:29:"res/contrib/jquery.tooltip.js";s:4:"01ca";s:20:"res/contrib/tabs.css";s:4:"a144";s:19:"res/contrib/tabs.js";s:4:"a448";s:41:"res/contrib/webtoolkit.scrollabletable.js";s:4:"f2b7";s:32:"settings/acrossL10nmgrConfig.dst";s:4:"7f83";s:34:"settings/dejaVuL10nmgrConfig.dvflt";s:4:"dffe";s:20:"settings/memoQ.mqres";s:4:"48d2";s:18:"settings/MemoQ.xml";s:4:"524c";s:23:"settings/SDLPassolo.xfg";s:4:"89cf";s:31:"settings/SDLTradosTagEditor.ini";s:4:"f428";s:34:"settings/TYPO3_l10nmgr.sdlfiletype";s:4:"1f12";s:58:"settings/TYPO3_LocalizationManager_v3.6.free.sdlftsettings";s:4:"d6d6";s:46:"settings/XMLConverter_TYPO3_l10nmgr_v3.6.mqres";s:4:"5ff6";s:48:"tasks/class.tx_l10nmgr_filegarbagecollection.php";s:4:"66d9";s:72:"tasks/class.tx_l10nmgr_filegarbagecollection_additionalfieldprovider.php";s:4:"e468";s:19:"tasks/locallang.xml";s:4:"b53c";s:29:"tasks/locallang_csh_tasks.xml";s:4:"5609";s:27:"templates/cm1_template.html";s:4:"20b8";s:23:"templates/mod1_list.css";s:4:"f506";s:22:"templates/mod1_list.js";s:4:"e501";s:23:"templates/mod1_list.php";s:4:"504d";s:30:"tests/tx_xmltools_testcase.php";s:4:"2452";s:45:"views/class.tx_l10nmgr_abstractExportView.php";s:4:"6ee7";s:44:"views/class.tx_l10nmgr_l10ncfgDetailView.php";s:4:"2f3c";s:43:"views/class.tx_l10nmgr_l10nHTMLListView.php";s:4:"0ef8";s:35:"views/class.tx_l10nmgr_template.php";s:4:"e912";s:44:"views/CATXML/class.tx_l10nmgr_CATXMLView.php";s:4:"c10b";s:48:"views/excelXML/class.tx_l10nmgr_excelXMLView.php";s:4:"1682";s:33:"views/excelXML/excel_template.xml";s:4:"95b3";}',
	'suggests' => array(
	),
);

?>
