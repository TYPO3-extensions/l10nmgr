<?php echo'<?xml version="1.0" encoding="UTF-8"?>'; ?>
<!DOCTYPE TYPO3L10N [ <!ENTITY nbsp "&#160;"> ]>
<TYPO3L10N>
	<head>
		<?php $staticSourceLanguage = $this->l10ncfgObj->getStaticSourceLanguage(); ?>
		<t3_l10ncfg><?php echo $this->l10ncfgObj->getUid(); ?></t3_l10ncfg>
		<t3_sysLang><?php echo $this->getTranslateableInformation()->getTargetLanguage()->getUid(); ?></t3_sysLang>
		<t3_sourceLang><?php if($staticSourceLanguage instanceof tx_l10nmgr_domain_language_staticLanguage ){?><?php echo $staticSourceLanguage->getLg_iso_2(); ?><?php } ?></t3_sourceLang>
		<t3_targetLang><?php echo $this->getTranslateableInformation()->getTargetLanguage()->getStaticLanguage()->getLg_iso_2(); ?></t3_targetLang>
		<t3_baseURL><?php echo $this->getTranslateableInformation()->getSiteUrl(); ?></t3_baseURL>
		<t3_workspaceId><?php echo $this->getTranslateableInformation()->getWorkspaceId(); ?></t3_workspaceId>
		<t3_count><?php echo $this->getTranslateableInformation()->countFields(); ?> </t3_count>
		<t3_wordCount><?php echo $this->getTranslateableInformation()->countWords(); ?></t3_wordCount>
		<t3_internal><?php echo $this->getInternalMessagesXML(); ?></t3_internal>
		<t3_exportDataId><?php echo $this->getTranslateableInformation()->getExportData()->getUid(); ?></t3_exportDataId>
		<t3_formatVersion><?php echo L10NMGR_FILEVERSION; ?></t3_formatVersion>
	</head>

	<?php echo $this->getRenderedPageGroups(); ?>

</TYPO3L10N>