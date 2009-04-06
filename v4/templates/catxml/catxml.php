<?php echo'<?xml version="1.0" encoding="UTF-8"?>'; ?>
<!DOCTYPE TYPO3L10N [ <!ENTITY nbsp > ]>
<TYPO3L10N>
	<head>
		<t3_l10ncfg><?= $this->l10ncfgObj->getData('uid'); ?></t3_l10ncfg>
		<t3_sysLang><?= $this->getTranslateableInformation()->getTargetLanguage()->getId(); ?></t3_sysLang>
		<t3_sourceLang><?= $staticLangArr['lg_iso_2']; ?></t3_sourcelang>
		<t3_targetLang><?= $this->getTranslateableInformation()->getTargetLanguage()->getISOCode(); ?></t3_targetLang>
		<t3_baseURL><?= t3lib_div::getIndpEnv("TYPO3_SITE_URL"); ?></t3_baseURL>
		<t3_workspaceId><?= $GLOBALS['BE_USER']->workspace; ?></t3_workspaceId>
		<t3_count><?= $this->getTranslateableInformation()->countFields(); ?> </t3_count>
		<t3_wordCount><?= $this->getTranslateableInformation()->countWords(); ?></t3_wordCount>
		<t3_internal></t3_internal>
		<t3_formatVersion><?= $L10NMGR_FILEVERSION; ?></t3_formatVersion>
	</head>
<?php foreach($this->getTranslateableInformation()->getPageGroups() as $pageGroup){ ?>

	<pageGrp id="<?= $pageGroup->getPageId(); ?>">
		

	</pageGrp>

<?php } ?>
</TYPO3L10N>