#
# Table structure for table 'tx_l10nmgr_cfg'
#
CREATE TABLE tx_l10nmgr_cfg (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	title tinytext NOT NULL,
	depth int(11) DEFAULT '0' NOT NULL,
	displaymode int(11) DEFAULT '0' NOT NULL,
	tablelist TEXT DEFAULT '' NOT NULL,
	exclude text NOT NULL,
	include text NOT NULL,
	flexformdiff mediumtext NOT NULL,
	sourceLangStaticId char(3) NOT NULL default '',
	incfcewithdefaultlanguage int(11) DEFAULT '0' NOT NULL,
	filenameprefix tinytext NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);


#
# Table structure for table 'sys_refindex'
#
CREATE TABLE tx_l10nmgr_index (
  hash varchar(32) DEFAULT '' NOT NULL,
  tablename varchar(40) DEFAULT '' NOT NULL,
  recuid int(11) DEFAULT '0' NOT NULL,
  recpid int(11) DEFAULT '0' NOT NULL,
  sys_language_uid int(11) DEFAULT '0' NOT NULL,
  translation_lang int(11) DEFAULT '0' NOT NULL,
  translation_recuid int(11) DEFAULT '0' NOT NULL,
  workspace int(11) DEFAULT '0' NOT NULL,
  serializedDiff mediumblob NOT NULL,
  flag_new int(11) DEFAULT '0' NOT NULL,
  flag_unknown int(11) DEFAULT '0' NOT NULL,
  flag_noChange int(11) DEFAULT '0' NOT NULL,
  flag_update int(11) DEFAULT '0' NOT NULL,

  PRIMARY KEY (hash),
  KEY lookup_rec (tablename,recuid,translation_lang,workspace),
  KEY lookup_pid (recpid,translation_lang,workspace)
);


#
# Table structure for table 'tx_l10nmgr_priorities'
#
CREATE TABLE tx_l10nmgr_priorities (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	title tinytext NOT NULL,
	description text NOT NULL,
	languages blob NOT NULL,
	element blob NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);

#
# Table structure for table 'tx_l10nmgr_exportdata'
#
CREATE TABLE tx_l10nmgr_exportdata (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	l10ncfg_id int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(30) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3_origuid int(11) DEFAULT '0' NOT NULL,
	
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	title tinytext NOT NULL,
	source_lang blob NOT NULL,
	translation_lang blob NOT NULL,
	export_type tinytext NOT NULL,
	progress blob NOT NULL,
	filename text NOT NULL,
	exportfiles int(11) DEFAULT '0' NOT NULL,
	checkforexistingexports tinyint(4) DEFAULT '0' NOT NULL,
	onlychangedcontent tinyint(4) DEFAULT '0' NOT NULL,
	nohidden tinyint(4) DEFAULT '0' NOT NULL,
	noxmlcheck tinyint(4) DEFAULT '0' NOT NULL,
	checkutf8 tinyint(4) DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
);

#
# Table structure for table 'tx_l10nmgr_exportfiles'
#
CREATE TABLE tx_l10nmgr_exportfiles (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	exportdata_id int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(30) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,	
	t3_origuid int(11) DEFAULT '0' NOT NULL,
	
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	filename text NOT NULL,

	PRIMARY KEY (uid),
);

#
# Table structure for table 'tx_l10nmgr_importdata'
#
CREATE TABLE tx_l10nmgr_importdata (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	exportdata_id int(11) DEFAULT '0' NOT NULL,
	configuration_id int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(30) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,	
	t3_origuid int(11) DEFAULT '0' NOT NULL,
	import_type tinytext NOT NULL,	
	
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	progress blob NOT NULL,
	force_target_lang tinyint(4) NOT NULL,
	importfiles int(11) DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
);

#
# Table structure for table 'tx_l10nmgr_importfiles'
#
CREATE TABLE tx_l10nmgr_importfiles (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	importdata_id int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(30) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,	
	t3_origuid int(11) DEFAULT '0' NOT NULL,
	
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	filename text NOT NULL

	PRIMARY KEY (uid),
);

#
# Table structure for table 'tx_l10nmgr_workflowstates'
#
CREATE TABLE tx_l10nmgr_workflowstates (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	exportdata_id int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(30) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,	
	t3_origuid int(11) DEFAULT '0' NOT NULL,
	
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	state tinytext NOT NULL,

	PRIMARY KEY (uid),
);
