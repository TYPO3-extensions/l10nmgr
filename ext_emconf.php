<?php

########################################################################
# Extension Manager/Repository config file for ext: "l10nmgr"
#
# Auto generated 16-03-2008 06:59
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Localization Manager',
	'description' => 'Module for managing localization import and export',
	'category' => 'module',
	'shy' => 0,
	'version' => '3.0.0-dev',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => 'cm1,cm2,mod1,mod2',
	'state' => 'beta',
	'uploadfolder' => 1,
	'createDirs' => 'uploads/tx_l10nmgr/settings',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Kasper Skårhøj, Daniel Zielinski, Daniel Pötzinger',
	'author_email' => 'kasperYYYY@typo3.com',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:64:{s:9:"ChangeLog";s:4:"66bf";s:10:"README.txt";s:4:"ee2d";s:4:"TODO";s:4:"a3ef";s:30:"class.l10nmgr_tcemain_hook.php";s:4:"b42c";s:24:"class.tx_l10nmgr_cm1.php";s:4:"a89f";s:26:"class.tx_l10nmgr_index.php";s:4:"be0e";s:12:"ext_icon.gif";s:4:"ec72";s:17:"ext_localconf.php";s:4:"4301";s:14:"ext_tables.php";s:4:"f626";s:14:"ext_tables.sql";s:4:"903a";s:13:"flags_new.png";s:4:"88c4";s:14:"flags_none.png";s:4:"4f46";s:12:"flags_ok.png";s:4:"9407";s:17:"flags_unknown.png";s:4:"13df";s:16:"flags_update.png";s:4:"ca64";s:23:"icon_tx_l10nmgr_cfg.gif";s:4:"ec72";s:30:"icon_tx_l10nmgr_priorities.gif";s:4:"dc05";s:13:"locallang.xml";s:4:"7ecc";s:25:"locallang_csh_l10nmgr.xml";s:4:"f83c";s:16:"locallang_db.xml";s:4:"33a2";s:7:"tca.php";s:4:"e32c";s:13:"cm1/clear.gif";s:4:"cc11";s:15:"cm1/cm_icon.gif";s:4:"ec72";s:12:"cm1/conf.php";s:4:"6e5e";s:13:"cm1/index.php";s:4:"7b7a";s:17:"cm1/locallang.xml";s:4:"99c3";s:15:"cm2/cm_icon.gif";s:4:"8074";s:12:"cm2/conf.php";s:4:"d912";s:13:"cm2/index.php";s:4:"23b2";s:17:"cm2/locallang.xml";s:4:"ce71";s:55:"models/class.tx_l10nmgr_l10nAccumulatedInformations.php";s:4:"381e";s:43:"models/class.tx_l10nmgr_l10nBaseService.php";s:4:"c1fb";s:45:"models/class.tx_l10nmgr_l10nConfiguration.php";s:4:"e94f";s:43:"models/class.tx_l10nmgr_translationData.php";s:4:"7cd3";s:50:"models/class.tx_l10nmgr_translationDataFactory.php";s:4:"4abf";s:39:"models/tools/class.tx_l10nmgr_tools.php";s:4:"ed0a";s:43:"models/tools/class.tx_l10nmgr_utf8tools.php";s:4:"90c3";s:42:"models/tools/class.tx_l10nmgr_xmltools.php";s:4:"2ac8";s:43:"views/class.tx_l10nmgr_l10nHTMLListView.php";s:4:"d662";s:44:"views/class.tx_l10nmgr_l10ncfgDetailView.php";s:4:"baa5";s:48:"views/excelXML/class.tx_l10nmgr_excelXMLView.php";s:4:"fd56";s:33:"views/excelXML/excel_template.xml";s:4:"6b5d";s:44:"views/CATXML/class.tx_l10nmgr_CATXMLView.php";s:4:"86dc";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"3564";s:14:"mod1/index.php";s:4:"33f8";s:18:"mod1/locallang.xml";s:4:"e7a2";s:22:"mod1/locallang_mod.xml";s:4:"e3d5";s:19:"mod1/moduleicon.gif";s:4:"ec72";s:23:"settings/SDLPassolo.xfg";s:4:"9971";s:31:"settings/SDLTradosTagEditor.ini";s:4:"5d76";s:32:"settings/acrossL10nmgrConfig.dst";s:4:"238c";s:34:"settings/dejaVuL10nmgrConfig.dvflt";s:4:"dffe";s:14:"mod2/clear.gif";s:4:"cc11";s:13:"mod2/conf.php";s:4:"176b";s:14:"mod2/index.php";s:4:"77c7";s:13:"mod2/list.php";s:4:"63f8";s:18:"mod2/locallang.xml";s:4:"c811";s:22:"mod2/locallang_mod.xml";s:4:"90d2";s:19:"mod2/moduleicon.gif";s:4:"8074";s:30:"tests/tx_xmltools_testcase.php";s:4:"ba53";s:12:"doc/TODO.txt";s:4:"2411";s:19:"doc/wizard_form.dat";s:4:"9c22";s:20:"doc/wizard_form.html";s:4:"e328";}',
	'suggests' => array(
	),
);

?>