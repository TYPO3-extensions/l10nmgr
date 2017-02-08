<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
if (TYPO3_MODE == "BE") {
    
    $extPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('l10nmgr');
    
    /**
     * Registers a Backend Module
     */
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule('web', 'ConfigurationManager', '', '', array(
        'routeTarget' => \Localizationteam\L10nmgr\Controller\ConfigurationManager::class . '::mainAction',
        'access' => 'user,group',
        'name' => 'web_ConfigurationManager',
        'icon' => 'EXT:l10nmgr/Resources/Public/Icons/module1_icon.gif',
        'labels' => 'LLL:EXT:l10nmgr/Resources/Private/Language/Modules/ConfigurationManager/locallang_mod.xlf'
    ));
    
    /**
     * Registers a Backend Module
     */
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule('ConfigurationManager', 'LocalizationManager', '', '',
        array(
            'routeTarget' => \Localizationteam\L10nmgr\Controller\LocalizationManager::class . '::mainAction',
            'access' => 'user,group',
            'name' => 'ConfigurationManager_LocalizationManager',
            'icon' => 'EXT:l10nmgr/Resources/Public/Icons/module1_icon.gif',
            'labels' => 'LLL:EXT:l10nmgr/Resources/Private/Language/Modules/ConfigurationManager/locallang_mod.xlf'
        ));
    
    /**
     * Registers a Backend Module
     */
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule('user', 'txl10nmgrM2', 'top', '', array(
        'routeTarget' => \Localizationteam\L10nmgr\Controller\Module2::class . '::main',
        'access' => 'user,group',
        'name' => 'user_txl10nmgrM2',
        'icon' => 'EXT:l10nmgr/Resources/Public/Icons/module2_icon.gif',
        'labels' => 'LLL:EXT:l10nmgr/Resources/Private/Language/Modules/Module2/locallang_mod.xlf'
    ));
    
    /**
     * Registers a Backend Module
     */
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule('ConfigurationManager', 'TranslationTasks', '', '',
        array(
            'routeTarget' => \Localizationteam\L10nmgr\Controller\TranslationTasks::class . '::mainAction',
            'access' => 'user,group',
            'name' => 'ConfigurationManager_TranslationTasks',
            'licon' => 'EXT:l10nmgr/Resources/Public/Icons/module1_icon.gif',
            'll_ref' => 'LLL:EXT:l10nmgr/Resources/Private/Language/Modules/Module2/locallang_mod.xlf'
        ));
    
    $GLOBALS["TBE_MODULES_EXT"]["xMOD_alt_clickmenu"]["extendCMclasses"][] = array(
        "name" => "Localizationteam\\L10nmgr\\ClickMenu",
        "path" => $extPath . "Classes/ClickMenu.php"
    );
    
    // Add context sensitive help (csh) for the Scheduler tasks
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('_tasks_txl10nmgr',
        'EXT:l10nmgr/Resources/Private/Language/Task/locallang_csh_tasks.xlf');
    
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages("tx_l10nmgr_cfg");
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_l10nmgr_cfg',
    'EXT:l10nmgr/Resources/Private/Language/locallang_csh_l10nmgr.xlf');

// Example for disabling localization of specific fields in tables like tt_content
// Add as many fields as you need

//$TCA['tt_content']['columns']['imagecaption']['l10n_mode'] = 'exclude';
//$TCA['tt_content']['columns']['image']['l10n_mode'] = 'prefixLangTitle';
//$TCA['tt_content']['columns']['image']['l10n_display'] = 'defaultAsReadonly';