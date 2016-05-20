<?php

$l10n = 'LLL:EXT:l10nmgr/Resources/Private/Language/locallang_db.xlf';

return array(
    'ctrl' => array(
        'title' => $l10n . ':tx_l10nmgr_priorities',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'sortby' => 'sorting',
        'delete' => 'deleted',
        'rootLevel' => 1,
        'enablecolumns' => array(
            'disabled' => 'hidden',
        ),
        'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('l10nmgr') . 'Resources/Public/Icons/icon_tx_l10nmgr_priorities.gif',
    ),
    'feInterface' => array(
        'fe_admin_fieldList' => 'hidden, title, description, languages, element',
    ),
    'interface' => array(
        'showRecordFieldList' => 'hidden,title,description,languages,element'
    ),
    'columns' => array(
        'hidden' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
            'config' => array(
                'type' => 'check',
                'default' => '0'
            )
        ),
        'title' => array(
            'exclude' => 1,
            'label' => $l10n . ':tx_l10nmgr_priorities.title',
            'config' => array(
                'type' => 'input',
                'size' => '30',
            )
        ),
        'description' => array(
            'exclude' => 1,
            'label' => $l10n . ':tx_l10nmgr_priorities.description',
            'config' => array(
                'type' => 'text',
                'cols' => '30',
                'rows' => '5',
            )
        ),
        'languages' => array(
            'exclude' => 1,
            'label' => $l10n . ':tx_l10nmgr_priorities.languages',
            'config' => array(
                'type' => 'select',
                'foreign_table' => 'sys_language',
                'foreign_table_where' => 'AND sys_language.pid=###SITEROOT### AND sys_language.hidden=0 ORDER BY sys_language.uid',
                'size' => 5,
                'minitems' => 0,
                'maxitems' => 100,
            )
        ),
        'element' => array(
            'exclude' => 1,
            'label' => $l10n . ':tx_l10nmgr_priorities.element',
            'config' => array(
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => '*',
                'prepend_tname' => true,
                'size' => 10,
                'minitems' => 0,
                'maxitems' => 100,
                'show_thumbs' => 1
            )
        ),
    ),
    'types' => array(
        '0' => array('showitem' => 'hidden;;1;;1-1-1, title;;;;2-2-2, description;;;;3-3-3, languages, element')
    ),
    'palettes' => array(
        '1' => array('showitem' => '')
    )
);