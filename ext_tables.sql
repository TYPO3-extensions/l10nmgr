#
# Table structure for table 'tx_l10nmgr_cfg'
#
CREATE TABLE tx_l10nmgr_cfg (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	title tinytext,
	depth int(11) DEFAULT '0' NOT NULL,
	pages text,
	displaymode int(11) DEFAULT '0' NOT NULL,
	tablelist text,
	exclude mediumtext,
	include mediumtext,
	flexformdiff mediumtext,
	sourceLangStaticId char(3) NOT NULL default '',
	incfcewithdefaultlanguage int(11) DEFAULT '0' NOT NULL,
	filenameprefix tinytext,
	overrideexistingtranslations tinyint(4) DEFAULT '0',
	pretranslatecontent tinyint(4) DEFAULT '0',
	
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
  serializedDiff mediumblob,
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
	title tinytext,
	description text,
	languages blob,
	element blob,
	
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
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	title tinytext,
	source_lang blob,
	translation_lang blob,
	tablelist text,
	exportType blob,
	filename text,
	
	PRIMARY KEY (uid),
);
