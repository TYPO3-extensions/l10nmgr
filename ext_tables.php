<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
if (TYPO3_MODE == "BE") {

    $extPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('l10nmgr');

	/**
	 * Registers a Backend Module
	 */
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
		'web',
		'txl10nmgrM1', // Submodule key
		'',    // Position
		$extPath . 'Classes/Modules/Module1/',
		array(
			'script' => '_DISPATCH',
			'access' => 'user,group',
			'name' => 'web_txl10nmgrM1',
			'labels' => array(
				'tabs_images' => array(
					'tab' => '../../../Resources/Public/Icons/module1_icon.gif',
				),
				'll_ref' => 'LLL:EXT:l10nmgr/Resources/Private/Language/Modules/Module1/locallang_mod.xlf'
			)
		)
	);

	/**
	 * Registers a Backend Module
	 */
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
		'xMOD',
		'txl10nmgrCM1', // Submodule key
		'',    // Position
		$extPath . 'Classes/Controller/Cm1/',
		array(
			'script' => '_DISPATCH',
			'access' => 'user,group',
			'name' => 'xMOD_txl10nmgrCM1',
			'labels' => array(
				'tabs_images' => array(
					'tab' => '../../../Resources/Public/Icons/module1_icon.gif',
				),
				'll_ref' => 'LLL:EXT:l10nmgr/Resources/Private/Language/Modules/Module1/locallang_mod.xlf'
			)
		)
	);

	/**
     * Registers a Backend Module
     */
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
        'user',
        'txl10nmgrM2', // Submodule key
        'top',    // Position
        $extPath . 'Classes/Modules/Module2/',
        array(
            'script' => '_DISPATCH',
            'access' => 'user,group',
            'name' => 'user_txl10nmgrM2',
            'labels' => array(
                'tabs_images' => array(
                    'tab' => '../../../Resources/Public/Icons/module2_icon.gif',
                ),
                'll_ref' => 'LLL:EXT:l10nmgr/Resources/Private/Language/Modules/Module2/locallang_mod.xlf'
            )
        )
    );

	/**
	 * Registers a Backend Module
	 */
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
		'xMOD',
		'Module2List', // Submodule key
		'',    // Position
		$extPath . 'Classes/Modules/Module2List/',
		array(
			'script' => '_DISPATCH',
			'access' => 'user,group',
			'name' => 'xMOD_Module2List',
			'labels' => array(
				'tabs_images' => array(
					'tab' => '../../../Resources/Public/Icons/module1_icon.gif',
				),
				'll_ref' => 'LLL:EXT:l10nmgr/Resources/Private/Language/Modules/Module2/locallang_mod.xlf'
			)
		)
	);

}
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages("tx_l10nmgr_cfg");
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_l10nmgr_cfg',
        'EXT:l10nmgr/Resources/Private/Language/locallang_csh_l10nmgr.xlf');

// Example for disabling localization of specific fields in tables like tt_content
// Add as many fields as you need

//$TCA['tt_content']['columns']['imagecaption']['l10n_mode'] = 'exclude';
//$TCA['tt_content']['columns']['image']['l10n_mode'] = 'prefixLangTitle';
//$TCA['tt_content']['columns']['image']['l10n_display'] = 'defaultAsReadonly';

    if (TYPO3_MODE == "BE") {
        $GLOBALS["TBE_MODULES_EXT"]["xMOD_alt_clickmenu"]["extendCMclasses"][] = array(
            "name" => "Localizationteam\\L10nmgr\\ClickMenu",
            "path" => $extPath . "Classes/ClickMenu.php"
        );

        // Add context sensitive help (csh) for the Scheduler tasks
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('_tasks_txl10nmgr',
            'EXT:l10nmgr/Resources/Private/Language/Task/locallang_csh_tasks.xlf');
    }
