<?php
namespace Localizationteam\L10nmgr\Controller;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006 Kasper Skårhøj <kasperYYYY@typo3.com>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
/**
 * Module 'L10N Manager' for the 'l10nmgr' extension.
 *
 * @author  Kasper Skårhøj <kasperYYYY@typo3.com>
 */

// DEFAULT initialization of a module [BEGIN]
use TYPO3\CMS\Backend\Module\BaseScriptClass;
use TYPO3\CMS\Backend\Template\DocumentTemplate;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Translation management tool
 *
 * @author     Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package    TYPO3
 * @subpackage tx_l10nmgr
 */
class Module1 extends BaseScriptClass
{

    var $pageinfo;

    /**
     * @var array Cache of the page details already fetched from the database
     */
    protected $pageDetails = array();

    /**
     * @var array Cache of the language records already fetched from the database
     */
    protected $languageDetails = array();

    /**
     * Initializes the Module
     *
     * @return  void
     */
    public function init()
    {
        $this->MCONF['name'] = 'web_txl10nmgrM1';
	    $GLOBALS['BE_USER']->modAccess($this->MCONF, 1);
	    $GLOBALS['LANG']->includeLLFile("EXT:l10nmgr/Resources/Private/Language/Modules/Module1/locallang.xlf");
        parent::init();
    }

    /**
     * Main function of the module. Write the content to $this->content
     * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
     *
     * @return  void
     */
    public function main()
    {
        $extRelPath = ExtensionManagementUtility::extRelPath('l10nmgr');

        // Get a template instance and load the template
        $this->doc = GeneralUtility::makeInstance(DocumentTemplate::class);
        $this->doc->backPath = $GLOBALS['BACK_PATH'];
        // NOTE: this module uses the same template as the CM1 module
        $this->doc->setModuleTemplate('EXT:l10nmgr/Resources/Private/Templates/Cm1Template.html');
        $this->doc->form = '<form action="" method="POST">';
        // Load the styles and JavaScript for the tooltips
        $this->doc->addStyleSheet('tx_l10nmgr_module1',
            ExtensionManagementUtility::extRelPath('l10nmgr') . 'Resources/Public/Contrib/jquery.tooltip.css');
        $this->doc->loadJavascriptLib($extRelPath . 'Resources/Public/Contrib/jquery-1.2.3.js');
        $this->doc->loadJavascriptLib($extRelPath . 'Resources/Public/Contrib/jquery.tooltip.js');
        $this->doc->loadJavascriptLib($extRelPath . 'Resources/Private/Templates/mod1_list.js');

        // Get the actual content
        $this->content = $this->moduleContent();
        $markers['CONTENT'] = $this->content;

        // Build the <body> for the module
        $docHeaderButtons = $this->getButtons();
        $this->content = $this->doc->startPage($GLOBALS['LANG']->getLL('general.title'));
        $this->content .= $this->doc->moduleBody($this->pageinfo, $docHeaderButtons, $markers);
        $this->content .= $this->doc->endPage();
        $this->content = $this->doc->insertStylesAndJS($this->content);
    }

    /**
     * Generates and returns the content of the module
     *
     * @return string HTML to display
     */
    protected function moduleContent()
    {
        $content = '';
        $content .= $this->doc->header($GLOBALS['LANG']->getLL('general.title'));
        // Get the available configurations
        $l10nConfigurations = $this->getAllConfigurations();
        // No configurations, issue a simple message
        if (count($l10nConfigurations) == 0) {
            $content .= $this->doc->section('', nl2br($GLOBALS['LANG']->getLL('general.no_date')));
            // List all configurations
        } else {
            $content .= $this->doc->section('', nl2br($GLOBALS['LANG']->getLL('general.description.message')));
            $content .= $this->doc->section($GLOBALS['LANG']->getLL('general.list.configuration.title'), '');
            $content .= '<table class="typo3-dblist" border="0" cellpadding="0" cellspacing="0">';
            // Assemble the header row
            $content .= '<thead>';
            $content .= '<tr class="t3-row-header">';
            $content .= '<td>' . $GLOBALS['LANG']->getLL('general.list.headline.info.title') . '</td>';
            $content .= '<td>' . $GLOBALS['LANG']->getLL('general.list.headline.title.title') . '</td>';
            $content .= '<td>' . $GLOBALS['LANG']->getLL('general.list.headline.path.title') . '</td>';
            $content .= '<td>' . $GLOBALS['LANG']->getLL('general.list.headline.depth.title') . '</td>';
            $content .= '<td>' . $GLOBALS['LANG']->getLL('general.list.headline.tables.title') . '</td>';
            $content .= '<td>' . $GLOBALS['LANG']->getLL('general.list.headline.exclude.title') . '</td>';
            $content .= '<td>' . $GLOBALS['LANG']->getLL('general.list.headline.include.title') . '</td>';
            $content .= '<td>' . $GLOBALS['LANG']->getLL('general.list.headline.incfcewithdefaultlanguage.title') . '</td>';
            $content .= '</tr>';
            $content .= '</thead>';
            $content .= '<tbody>';
            $informationIcon = IconUtility::getSpriteIcon('actions-document-info', array());
            foreach ($l10nConfigurations as $record) {
                $configurationDetails = '<a class="tooltip" href="#tooltip_' . $record['uid'] . '">' . $informationIcon . '</a>';
                $configurationDetails .= '<div style="display:none;" id="tooltip_' . $record['uid'] . '" class="infotip">';
                $configurationDetails .= $this->renderConfigurationDetails($record);
                $configurationDetails .= '</div>';
                $content .= '<tr class="db_list_normal">';
                $content .= '<td>' . $configurationDetails . '</td>';
                $content .= '<td><a href="' . BackendUtility::getModuleUrl('xMOD_txl10nmgrCM1', array(
		                'id' => $record['uid'],
		                'srcPID' => (int)$this->id
	                )) . '">' . $record['title'] . '</a>' . '</td>';
                // Get the full page path
                // If very long, make sure to still display the full path
                $pagePath = BackendUtility::getRecordPath($record['pid'], '1', 20, 50);
                $path = (is_array($pagePath)) ? $pagePath[1] : $pagePath;
                $content .= '<td>' . $path . '</td>';
                $content .= '<td>' . $record['depth'] . '</td>';
                $content .= '<td>' . $record['tablelist'] . '</td>';
                $content .= '<td>' . $record['exclude'] . '</td>';
                $content .= '<td>' . $record['include'] . '</td>';
                $content .= '<td>' . $record['incfcewithdefaultlanguage'] . '</td>';
                $content .= '</tr>';
            }
            $content .= '</tbody>';
        }

        return $content;
    }

    /**
     * Returns all l10nmgr configurations to which the current user has access, based on page permissions
     *
     * @return array List of l10nmgr configurations
     */
    protected function getAllConfigurations()
    {
        // Read all l10nmgr configurations from the database
        $configurations = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
            '*',
            'tx_l10nmgr_cfg',
            '1=1' . BackendUtility::deleteClause('tx_l10nmgr_cfg')
        );
        // Filter out the configurations which the user is allowed to see, base on the page access rights
        $pagePermissionsClause = $GLOBALS['BE_USER']->getPagePermsClause(1);
        $allowedConfigurations = array();
        foreach ($configurations as $row) {
            if (BackendUtility::readPageAccess($row['pid'], $pagePermissionsClause) !== false) {
                $allowedConfigurations[] = $row;
            }
        }

        return $allowedConfigurations;
    }

    /**
     * Renders a detailed view of a l10nmgr configuration
     *
     * @param array $configuration A configuration record from the database
     * @return string The HTML to display
     */
    protected function renderConfigurationDetails($configuration)
    {
        $parentPageArray = $this->getPageDetails($configuration['pid']);
        $languageArray = $this->getPageDetails($configuration['sourceLangStaticId']);
        $details = '';
        $details .= '<table class="typo3-dblist" border="0" cellspacing="0" cellpadding="0">';
        $details .= '<tr>';
        $details .= '<td>' . $GLOBALS['LANG']->getLL('general.list.infodetail.pid.title') . '</td>';
        $details .= '<td>' . $parentPageArray['title'] . ' (' . $parentPageArray['uid'] . ')</td>';
        $details .= '</tr><tr class="db_list_normal">';
        $details .= '<td>' . $GLOBALS['LANG']->getLL('general.list.infodetail.title.title') . '</td>';
        $details .= '<td>' . $configuration['title'] . '</td>';
        $details .= '</tr><tr class="db_list_normal">';
        $details .= '<td>' . $GLOBALS['LANG']->getLL('general.list.infodetail.filenameprefix.title') . '</td>';
        $details .= '<td>' . $configuration['filenameprefix'] . '</td>';
        $details .= '</tr><tr class="db_list_normal">';
        $details .= '<td>' . $GLOBALS['LANG']->getLL('general.list.infodetail.depth.title') . '</td>';
        $details .= '<td>' . $configuration['depth'] . '</td>';
        $details .= '</tr><tr class="db_list_normal">';
        $details .= '<td>' . $GLOBALS['LANG']->getLL('general.list.infodetail.sourceLangStaticId.title') . '</td>';
        $details .= '<td>' . ((empty($languageArray['lg_name_en'])) ? $GLOBALS['LANG']->getLL('general.list.infodetail.default') : $languageArray['lg_name_en']) . '</td>';
        $details .= '</tr><tr class="db_list_normal">';
        $details .= '<td>' . $GLOBALS['LANG']->getLL('general.list.infodetail.tablelist.title') . '</td>';
        $details .= '<td>' . $configuration['tablelist'] . '</td>';
        $details .= '</tr><tr class="db_list_normal">';
        $details .= '<td>' . $GLOBALS['LANG']->getLL('general.list.infodetail.exclude.title') . '</td>';
        $details .= '<td>' . $configuration['exclude'] . '</td>';
        $details .= '</tr><tr class="db_list_normal">';
        $details .= '<td>' . $GLOBALS['LANG']->getLL('general.list.infodetail.include.title') . '</td>';
        $details .= '<td>' . $configuration['include'] . '</td>';
        $details .= '</tr><tr class="db_list_normal">';
        $details .= '<td>' . $GLOBALS['LANG']->getLL('general.list.infodetail.displaymode.title') . '</td>';
        $details .= '<td>' . $configuration['displaymode'] . '</td>';
        $details .= '</tr><tr class="db_list_normal">';
        $details .= '<td>' . $GLOBALS['LANG']->getLL('general.list.infodetail.incfcewithdefaultlanguage.title') . '</td>';
        $details .= '<td>' . $configuration['incfcewithdefaultlanguage'] . '</td>';
        $details .= '</tr>';
        $details .= '</table>';

        return $details;
    }

    /**
     * Returns the details of a given page record, possibly from cache if already fetched earlier
     *
     * @param int $uid Id of a page
     * @return array Page record from the database
     */
    protected function getPageDetails($uid)
    {
        $uid = (int)$uid;
        if (isset($this->pageDetails[$uid])) {
            $record = $this->pageDetails[$uid];
        } else {
            $record = BackendUtility::getRecord('pages', $uid);
            $this->pageDetails[$uid] = $record;
        }

        return $record;
    }

    /**
     * Create the panel of buttons for submitting the form or otherwise perform operations.
     *
     * @return  array  all available buttons as an assoc. array
     */
    protected function getButtons()
    {
        $buttons = array();

        $buttons['reload'] = '<a href="' . $GLOBALS['MCONF']['_'] . '" title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xml:labels.reload',
                true) . '">' .
            IconUtility::getSpriteIcon('actions-system-refresh', array()) .
            '</a>';

        // Shortcut
        if ($GLOBALS['BE_USER']->mayMakeShortcut()) {
            $buttons['shortcut'] = $this->doc->makeShortcutIcon('', 'function', $this->MCONF['name']);
        }

        return $buttons;
    }

    /**
     * Prints out the module HTML
     *
     * @return  void
     */
    public function printContent()
    {
        print $this->content;
    }

    /**
     * Returns the details of a given static language record, possibly from cache if already fetched earlier
     *
     * @param int $uid Id of a language
     * @return array Language record from the database
     */
    protected function getLanguageDetails($uid)
    {
        $record = array();
        $uid = (int)$uid;
        if ($uid > 0) {
            if (isset($this->languageDetails[$uid])) {
                $record = $this->languageDetails[$uid];
            } else {
                $record = BackendUtility::getRecord('static_languages', $uid);
                $this->languageDetails[$uid] = $record;
            }
        }

        return $record;
    }
}