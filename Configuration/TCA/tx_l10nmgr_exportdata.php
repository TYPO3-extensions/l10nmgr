<?php

$l10n = 'LLL:EXT:l10nmgr/Resources/Private/Language/locallang_db.xlf';

return array (
    'ctrl' => array(
        'title' => $l10n . ':tx_l10nmgr_export',
        'label' => 'title',
        'l10ncfg_id' => 'l10ncfg_id',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'source_lang' => 'source_lang',
        'translation_lang' => 'translation_lang',
        'default_sortby' => 'ORDER BY title',
        'delete' => 'deleted',
        'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('l10nmgr') . 'Resources/Public/Icons/icon_tx_l10nmgr_cfg.gif',
    ),
    'feInterface' => array(
        'fe_admin_fieldList' => 'title, source_lang, l10ncfg_id, crdate, delete, exclude',
    ),
    'interface' => array(
        'showRecordFieldList' => 'title,crdate,tablelist,translation_lang,source_lang,configuration,l10ncfg_id,filename'
    ),
    'columns' => array(
        'title' => array(
            'exclude' => 1,
            'label' => $l10n . ':tx_l10nmgr_cfg.title',
            'config' => array(
                'type' => 'input',
                'size' => '48',
                'eval' => 'required',
                'readOnly' => 1,
            )
        ),
        'crdate' => array(
            'exclude' => 1,
            'label' => $l10n . ':tx_l10nmgr_cfg.crdate',
            'config' => array(
                'type' => 'input',
                'eval' => 'date',
                'size' => '48',
                'readOnly' => 1,
            )
        ),
        'tablelist' => array(
            'exclude' => 1,
            'label' => $l10n . ':tx_l10nmgr_cfg.exporttablelist',
            'config' => array(
                'type' => 'input',
                'size' => '48',
                'readOnly' => 1,
            )
        ),
        'translation_lang' => array(
            'exclude' => 1,
            'label' => $l10n . ':tx_l10nmgr_cfg.translationLang',
            'config' => array(
                'type' => 'input',
                'size' => '48',
                'readOnly' => 1,
            )
        ),
	    'source_lang' => array(
		    'exclude' => 1,
		    'label' => $l10n . ':tx_l10nmgr_cfg.sourceLang',
		    'config' => array(
			    'type' => 'input',
			    'size' => '48',
			    'readOnly' => 1,
		    )
	    ),
	    'l10ncfg_id' => array(
			'exclude' => 1,
			'label' => $l10n . ':tx_l10nmgr_priorities.configuration',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tx_l10nmgr_cfg',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
                'readOnly' => 1,
			)
	    ),
	    'filename' => array(
		    'exclude' => 1,
		    'label' => $l10n . ':tx_l10nmgr_cfg.filename',
		    'config' => array(
			    'type' => 'input',
			    'size' => '48',
			    'readOnly' => 1,
		    )
	    ),
    ),
    'types' => array(
        '0' => array('showitem' => 'title, crdate, translation_lang, tablelist, source_lang, l10ncfg_id, exportType, filename')
    ),
    'palettes' => array(
        '1' => array('showitem' => '')
    )
);