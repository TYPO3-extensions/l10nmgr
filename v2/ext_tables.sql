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
	tablelist varchar(80) DEFAULT '' NOT NULL,
	exclude text NOT NULL,
	include text NOT NULL,
	flexformdiff mediumtext NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);