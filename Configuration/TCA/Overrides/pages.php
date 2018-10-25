<?php

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', [
    'l10nmgr_configuration' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:l10nmgr/Resources/Private/Language/locallang_db.xlf:pages.l10nmgr_configuration',
        'config' => [
            'type' => 'select',
            'items' => [
                [
                    0 => 'LLL:EXT:l10nmgr/Resources/Private/Language/locallang_db.xlf:pages.l10nmgr_configuration.I.' . \Localizationteam\L10nmgr\Constants::L10NMGR_CONFIGURATION_DEFAULT,
                    1 => \Localizationteam\L10nmgr\Constants::L10NMGR_CONFIGURATION_DEFAULT
                ],
                [
                    0 => 'LLL:EXT:l10nmgr/Resources/Private/Language/locallang_db.xlf:pages.l10nmgr_configuration.I.' . \Localizationteam\L10nmgr\Constants::L10NMGR_CONFIGURATION_NONE,
                    1 => \Localizationteam\L10nmgr\Constants::L10NMGR_CONFIGURATION_NONE
                ],
                [
                    0 => 'LLL:EXT:l10nmgr/Resources/Private/Language/locallang_db.xlf:pages.l10nmgr_configuration.I.' . \Localizationteam\L10nmgr\Constants::L10NMGR_CONFIGURATION_EXCLUDE,
                    1 => \Localizationteam\L10nmgr\Constants::L10NMGR_CONFIGURATION_EXCLUDE
                ],
                [
                    0 => 'LLL:EXT:l10nmgr/Resources/Private/Language/locallang_db.xlf:pages.l10nmgr_configuration.I.' . \Localizationteam\L10nmgr\Constants::L10NMGR_CONFIGURATION_INCLUDE,
                    1 => \Localizationteam\L10nmgr\Constants::L10NMGR_CONFIGURATION_INCLUDE
                ]
            ],
            'default' => \Localizationteam\L10nmgr\Constants::L10NMGR_CONFIGURATION_DEFAULT
        ]
    ],
    'l10nmgr_configuration_next_level' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:l10nmgr/Resources/Private/Language/locallang_db.xlf:pages.l10nmgr_configuration_next_level',
        'config' => [
            'type' => 'select',
            'items' => [
                [
                    0 => 'LLL:EXT:l10nmgr/Resources/Private/Language/locallang_db.xlf:pages.l10nmgr_configuration.I.' . \Localizationteam\L10nmgr\Constants::L10NMGR_CONFIGURATION_DEFAULT,
                    1 => \Localizationteam\L10nmgr\Constants::L10NMGR_CONFIGURATION_DEFAULT
                ],
                [
                    0 => 'LLL:EXT:l10nmgr/Resources/Private/Language/locallang_db.xlf:pages.l10nmgr_configuration.I.' . \Localizationteam\L10nmgr\Constants::L10NMGR_CONFIGURATION_NONE,
                    1 => \Localizationteam\L10nmgr\Constants::L10NMGR_CONFIGURATION_NONE
                ],
                [
                    0 => 'LLL:EXT:l10nmgr/Resources/Private/Language/locallang_db.xlf:pages.l10nmgr_configuration.I.' . \Localizationteam\L10nmgr\Constants::L10NMGR_CONFIGURATION_EXCLUDE,
                    1 => \Localizationteam\L10nmgr\Constants::L10NMGR_CONFIGURATION_EXCLUDE
                ],
                [
                    0 => 'LLL:EXT:l10nmgr/Resources/Private/Language/locallang_db.xlf:pages.l10nmgr_configuration.I.' . \Localizationteam\L10nmgr\Constants::L10NMGR_CONFIGURATION_INCLUDE,
                    1 => \Localizationteam\L10nmgr\Constants::L10NMGR_CONFIGURATION_INCLUDE
                ],
            ],
            'default' => \Localizationteam\L10nmgr\Constants::L10NMGR_CONFIGURATION_DEFAULT
        ]
    ]
]);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette('pages', 'l10nmgr_configuration', 'l10nmgr_configuration,l10nmgr_configuration_next_level');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('pages', '--palette--;LLL:EXT:l10nmgr/Resources/Private/Language/locallang_db.xlf:pages.palettes.l10nmgr_configuration;l10nmgr_configuration', '', 'after:l18n_cfg');

\Localizationteam\L10nmgr\Utility\L10nmgrExtensionManagementUtility::makeTranslationsRestrictable(
    'core',
    'pages'
);
