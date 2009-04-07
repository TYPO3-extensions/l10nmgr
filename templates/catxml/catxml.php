<?php echo'<?xml version="1.0" encoding="UTF-8"?>'; ?>
<!DOCTYPE TYPO3L10N [ <!ENTITY nbsp " "> ]>
<TYPO3L10N>
	<head>
		<?php
			$staticSourceLanguage = $this->l10ncfgObj->getStaticSourceLanguage();
		?>
		<t3_l10ncfg><?= $this->l10ncfgObj->getData('uid'); ?></t3_l10ncfg>
		<t3_sysLang><?= $this->getTranslateableInformation()->getTargetLanguage()->getUid(); ?></t3_sysLang>
		<t3_sourceLang><?php if($staticSourceLanguage instanceof tx_l10nmgr_models_language_staticLanguage ){?><?= $staticSourceLanguage->getLg_iso_2(); ?><?php } ?></t3_sourcelang>
		<t3_targetLang><?= $this->getTranslateableInformation()->getTargetLanguage()->getStaticLanguage()->getLg_iso_2(); ?></t3_targetLang>
		<t3_baseURL><?= $this->getTranslateableInformation()->getSiteUrl(); ?></t3_baseURL>
		<t3_workspaceId><?= $this->getTranslateableInformation()->getWorkspaceId(); ?></t3_workspaceId>
		<t3_count><?= $this->getTranslateableInformation()->countFields(); ?> </t3_count>
		<t3_wordCount><?= $this->getTranslateableInformation()->countWords(); ?></t3_wordCount>
		<t3_internal><?= $this->getInternalMessagesXML(); ?></t3_internal>
		<t3_formatVersion><? echo L10NMGR_FILEVERSION; ?></t3_formatVersion>
	</head>

	<?= $this->getPageGroupXML(); ?>
	
</TYPO3L10N>