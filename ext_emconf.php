<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "l10nmgr".
 *
 * Auto generated 08-06-2015 21:05
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
	'title' => 'Localization Manager',
	'description' => 'Module for managing localization import and export',
	'category' => 'module',
	'version' => '3.6.3',
	'state' => 'beta',
	'uploadfolder' => false,
	'createDirs' => 'uploads/tx_l10nmgr/settings,uploads/tx_l10nmgr/saved_files,uploads/tx_l10nmgr/jobs,uploads/tx_l10nmgr/jobs/out,uploads/tx_l10nmgr/jobs/in,uploads/tx_l10nmgr/jobs/done,uploads/tx_l10nmgr/jobs/_cmd',
	'clearcacheonload' => true,
	'author' => 'Kasper Skaarhoej, Daniel Zielinski, Daniel Poetzinger, Fabian Seltmann, Andreas Otto',
	'author_email' => 'kasperYYYY@typo3.com, info@loctimize.com',
	'author_company' => 'Localization Manager Team',
	'constraints' => 
	array (
		'depends' => 
		array (
			'php' => '5.2.0-0.0.0',
			'typo3' => '6.2.0-6.2.99',
		),
		'conflicts' => 
		array (
		),
		'suggests' => 
		array (
		),
	),
);

