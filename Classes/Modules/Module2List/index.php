<?php
namespace Localizationteam\L10nmgr\Modules\Module2List;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007 Kasper Skårhøj <kasperYYYY@typo3.com>
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
 * Module 'Workspace Tasks' for the 'l10nmgr' extension.
 *
 * @author  Kasper Skårhøj <kasperYYYY@typo3.com>
 */

use TYPO3\CMS\Backend\Module\BaseScriptClass;
use TYPO3\CMS\Backend\Template\DocumentTemplate;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Localizationteam\L10nmgr\Hooks\Tcemain;
use Localizationteam\L10nmgr\Model\Tools\Tools;

class Module2List extends BaseScriptClass
{

    var $pageinfo;

	function init() {
		$this->MCONF['name'] = 'xMOD_Module2List';
		$GLOBALS['BE_USER']->modAccess($this->MCONF, 1);
		$GLOBALS['LANG']->includeLLFile("EXT:l10nmgr/Resources/Private/Language/Modules/Module2/locallang.xlf");
		parent::init();
	}

    /**
     * Adds items to the ->MOD_MENU array. Used for the function menu selector.
     *
     * @return  void
     */
    function menuConfig()
    {
        global $LANG;
        parent::menuConfig();
    }

	/**
	 * Main function of the module. Write the content to $this->content
	 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	 *
	 */
    function main()
    {
        // Draw the header.
        $this->doc = GeneralUtility::makeInstance(DocumentTemplate::class);
        $this->doc->backPath = $GLOBALS['BACK_PATH'];
        $this->doc->form = '<form action="" method="post">';

        // JavaScript
        $this->doc->JScode = '
			<script language="javascript" type="text/javascript">
				script_ended = 0;
				function jumpToUrl(URL)	{
					document.location = URL;
				}
			</script>
		';

        // Setting up the context sensitive menu:
        $CMparts = $this->doc->getContextMenuCode();
        $this->doc->JScode .= $CMparts[0];
        $this->doc->bodyTagAdditions = $CMparts[1];
        $this->doc->postCode .= $CMparts[2];

        $this->content .= $this->doc->startPage($GLOBALS['LANG']->getLL("title"));
        $this->content .= $this->doc->header($GLOBALS['LANG']->getLL("title"));
        $this->content .= $this->doc->spacer(5);

        // Render content:
        $this->moduleContent();

        // ShortCut
        if ($GLOBALS['BE_USER']->mayMakeShortcut()) {
            $this->content .= $this->doc->spacer(20) . $this->doc->section("",
                    $this->doc->makeShortcutIcon("id", implode(",", array_keys($this->MOD_MENU)),
                        $this->MCONF["name"]));
        }

        $this->content .= $this->doc->spacer(10);
    }

    /**
     * Generates the module content
     *
     * @return  void
     */
    function moduleContent()
    {

        // Selecting priorities:
        $priorities = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tx_l10nmgr_priorities',
            '1=1' . BackendUtility::deleteClause('tx_l10nmgr_priorities'), '', 'sorting');
        $tRows = array();
        $c = 0;
        foreach ($priorities as $priorityRecord) {

            if ($lTable = $this->languageRows($priorityRecord['languages'], $priorityRecord['element'])) {
                $c++;
                $tRows[] = '
				<tr>
					<td class="bgColor5"><strong>#' . ($c) . ': ' . htmlspecialchars($priorityRecord['title']) . '</strong><br>' . htmlspecialchars($priorityRecord['description']) . '</td>
				</tr>
				<tr>
					<td>' . $lTable . '</td>
				</tr>';
            }
        }

        $content .= '<table border="0" cellpadding="4" cellspacing="2">' . implode('', $tRows) . '</table>';

        $this->content .= $this->doc->section("Priority list:", $content, 0, 1);
    }

    function languageRows($languageList, $elementList)
    {

        // Initialization:
        $elements = $this->explodeElement($elementList);
        $firstEl = current($elements);
        $hookObj = GeneralUtility::makeInstance(Tcemain::class);

        $this->l10nMgrTools = GeneralUtility::makeInstance(Tools::class);
        $this->l10nMgrTools->verbose = false; // Otherwise it will show records which has fields but none editable.
        $inputRecord = BackendUtility::getRecord($firstEl[0], $firstEl[1], 'pid');
        $this->sysLanguages = $this->l10nMgrTools->t8Tools->getSystemLanguages($firstEl[0] == 'pages' ? $firstEl[1] : $inputRecord['pid']);
        $languages = $this->getLanguages($languageList, $this->sysLanguages);

        if (count($languages)) {
            $tRows = array();

            // Header:
            $cells = '<td class="bgColor2 tableheader">Element:</td>';
            foreach ($languages as $l) {
                if ($l >= 1) {
                    $baseRecordFlag = '<img src="' . htmlspecialchars($GLOBALS['BACK_PATH'] . $this->sysLanguages[$l]['flagIcon']) . '" alt="' . htmlspecialchars($this->sysLanguages[$l]['title']) . '" title="' . htmlspecialchars($this->sysLanguages[$l]['title']) . '" />';
                    $cells .= '<td class="bgColor2 tableheader">' . $baseRecordFlag . '</td>';
                }
            }
            $tRows[] = $cells;

            foreach ($elements as $el) {
                $cells = '';
				$rec_on = array();
                // Get CURRENT online record and icon based on "t3ver_oid":
	            if ($el[0] !== '' && $el[1] > 0) {
		            $rec_on = BackendUtility::getRecord($el[0], $el[1]);
	            }
                $icon = IconUtility::getSpriteIconForRecord($el[0], $rec_on);
                $icon = $this->doc->wrapClickMenuOnIcon($icon, $el[0], $rec_on['uid'], 2);

                $linkToIt = '<a href="#" onclick="' . htmlspecialchars('parent.list_frame.location.href="' . $GLOBALS['BACK_PATH'] . ExtensionManagementUtility::extRelPath('l10nmgr') . 'cm2/index.php?table=' . $el[0] . '&uid=' . $el[1] . '"; return false;') . '" target="listframe">
					' . BackendUtility::getRecordTitle($el[0], $rec_on, true) . '
						</a>';

                if ($el[0] == 'pages') {
                    // If another page module was specified, replace the default Page module with the new one
                    $newPageModule = trim($GLOBALS['BE_USER']->getTSConfigVal('options.overridePageModule'));
                    $pageModule = BackendUtility::isModuleSetInTBE_MODULES($newPageModule) ? $newPageModule : 'web_layout';

                    $path_module_path = GeneralUtility::resolveBackPath($GLOBALS['BACK_PATH'] . '../' . substr($GLOBALS['TBE_MODULES']['_PATHS'][$pageModule],
                            strlen(PATH_site)));
                    $onclick = 'parent.list_frame.location.href="' . $path_module_path . '?id=' . $el[1] . '"; return false;';
                    $pmLink = '<a href="#" onclick="' . htmlspecialchars($onclick) . '" target="listframe"><i>[Edit page]</i></a>';
                } else {
                    $pmLink = '';
                }

                $cells = '<td>' . $icon . $linkToIt . $pmLink . '</td>';

                foreach ($languages as $l) {
                    if ($l >= 1) {
                        $cells .= '<td align="center">' . $hookObj->calcStat(array($el[0], $el[1]), $l) . '</td>';
                    }
                }

                $tRows[] = $cells;
            }

            return '<table border="0" cellpadding="0" cellspacing="0"><tr>' . implode('</tr><tr>',
                $tRows) . '</tr></table>';
        }
    }

    function explodeElement($elementList)
    {
        $elements = GeneralUtility::trimExplode(',', $elementList);
        foreach ($elements as $k => $element) {
            $elements[$k] = GeneralUtility::revExplode('_', $element, 2);
        }

        return $elements;
    }

    function getLanguages($limitLanguageList, $sysLanguages)
    {
        $languageListArray = explode(',',
            $GLOBALS['BE_USER']->groupData['allowed_languages'] ? $GLOBALS['BE_USER']->groupData['allowed_languages'] : implode(',',
                array_keys($sysLanguages)));

        foreach ($languageListArray as $kkk => $val) {
            if ($limitLanguageList && !GeneralUtility::inList($limitLanguageList, $val)) {
                unset($languageListArray[$kkk]);
            }
        }

        return $languageListArray;
    }

    /**
     * Prints out the module HTML
     *
     * @return  void
     */
    function printContent()
    {

        $this->content .= $this->doc->endPage();
        echo $this->content;
    }
}

// Make instance:
$SOBE = GeneralUtility::makeInstance(Module2List::class);
$SOBE->init();

// Include files?
foreach ($SOBE->include_once as $INC_FILE) {
    include_once($INC_FILE);
}

$SOBE->main();
$SOBE->printContent();

?>
