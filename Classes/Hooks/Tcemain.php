<?php
namespace Localizationteam\L10nmgr\Hooks;

/***************************************************************
 * Copyright notice
 * (c) 2001-2006 Kasper Skaarhoj (kasperYYYY@typo3.com)
 * All rights reserved
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 * A copy is found in the textfile GPL.txt and important notices to the license
 * from the author is found in LICENSE.txt distributed with these scripts.
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
/**
 * Updating translation index - hook for tcemain
 *
 * @author Kasper Skårhøj <kasperYYYY@typo3.com>
 */
use Localizationteam\L10nmgr\Model\L10nBaseService;
use Localizationteam\L10nmgr\Model\Tools\Tools;
use TYPO3\CMS\Backend\Configuration\TranslationConfigurationProvider;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Hook for updating translation index
 *
 * @authorKasper Skaarhoj <kasperYYYY@typo3.com>
 * @packageTYPO3
 * @subpackage tx_l10nmgr
 */
class Tcemain
{
    /**
     * Hook for updating translation index when records are edited (hooks into TCEmain)
     *
     * @param $status
     * @param $table
     * @param $id
     * @param $fieldArray
     * @param $pObj
     */
    public function processDatamap_afterDatabaseOperations($status, $table, $id, $fieldArray, &$pObj)
    {
        global $TCA;
        // Check if
        // debug(array($status, $table, $id));
        // Map id for new records:
        if ($status == "new") {
            $id = $pObj->substNEWwithIDs[$id];
            // echo "New fixed<br>";
        }
        // Find live record if any:
        if (!($liveRecord = BackendUtility::getLiveVersionOfRecord($table,
            $id))
        ) {
            // If it was a version we find live...
            $liveRecord = BackendUtility::getRecord($table, $id); // Otherwise we load live record.
            //	echo "Live version<br>";
        }
        if (is_array($liveRecord)) {
            // Now, see if this record is a translation of another one:
            /** @var TranslationConfigurationProvider $t8ToolsObj */
            $t8ToolsObj = GeneralUtility::makeInstance(TranslationConfigurationProvider::class);
            if ($t8ToolsObj->isTranslationInOwnTable($table) && $liveRecord[$TCA[$table]['ctrl']['transOrigPointerField']]) {
                // So it had a translation pointer - lets look for the root record then:
                $liveRecord = BackendUtility::getRecord($table,
                    $liveRecord[$TCA[$table]['ctrl']['transOrigPointerField']], 'uid');
                // echo "Finding root version<br>";
            } elseif ($TCA[$table]['ctrl']['transOrigPointerTable'] && $liveRecord[$TCA[$table]['ctrl']['transOrigPointerField']]) {
                $fld = $TCA[$table]['ctrl']['transOrigPointerField'];
                $table = $TCA[$table]['ctrl']['transOrigPointerTable']; // Changeing table value here on purpose!
                $liveRecord = BackendUtility::getRecord($table, $liveRecord[$fld], 'uid');
            }
            $languageID = L10nBaseService::getTargetLanguageID();
            if (is_array($liveRecord)) {
                // echo "indexing id ".$liveRecord['uid'];
                //// Finally, we have found the "root record" and will check it:
                /** @var Tools $t8Tools */
                $t8Tools = GeneralUtility::makeInstance(Tools::class);
                $t8Tools->verbose = false; // Otherwise it will show records which has fields but none editable.
                //	debug($t8Tools->indexDetailsRecord($table,$liveRecord['uid']));
                $t8Tools->updateIndexTableFromDetailsArray($t8Tools->indexDetailsRecord($table, $liveRecord['uid'],
                    $languageID));
            }
        }
    }

    /**
     * Hook for displaying small icon in page tree, web>List and page module.
     *
     * @param $p
     * @param $pObj
     *
     * @return string [type]...
     */
    function stat($p, $pObj)
    {
        if (strcmp($this->getBackendUser()->groupData['allowed_languages'], '')) {
            return $this->calcStat($p,
                $this->getDatabaseConnection()->cleanIntList($this->getBackendUser()->groupData['allowed_languages']));
        } else {
            return '';
        }
    }

    /**
     * Returns the Backend User
     * @return BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }

    function calcStat($p, $languageList, $noLink = false)
    {
        $output = '';
//
        if ($p[0] != 'pages') {
            $records = $this->getDatabaseConnection()->exec_SELECTgetRows('*', 'tx_l10nmgr_index',
                'tablename=' . $this->getDatabaseConnection()->fullQuoteStr($p[0],
                    'tx_l10nmgr_index') . ' AND recuid=' . (int)$p[1] . ' AND translation_lang IN (' . $languageList . ')' . ' AND workspace=' . (int)$this->getBackendUser()->workspace);
        } else {
            $records = $this->getDatabaseConnection()->exec_SELECTgetRows('*', 'tx_l10nmgr_index',
                'recpid=' . (int)$p[1] . ' AND translation_lang IN (' . $languageList . ')' . ' AND workspace=' . (int)$this->getBackendUser()->workspace);
        }
        $flags = array();
        foreach ($records as $r) {
            $flags['new'] += $r['flag_new'];
            $flags['unknown'] += $r['flag_unknown'];
            $flags['update'] += $r['flag_update'];
            $flags['noChange'] += $r['flag_noChange'];
        }
        if (count($records)) {
            // Setting icon:
            $msg = '';
            if ($flags['new'] && !$flags['unknown'] && !$flags['noChange'] && !$flags['update']) {
                $msg .= 'None of ' . $flags['new'] . ' elements are translated.';
                $output = '<img src="' . $GLOBALS['BACK_PATH'] . ExtensionManagementUtility::siteRelPath('l10nmgr') . 'flags_new.png" hspace="2" width="10" height="16" alt="' . htmlspecialchars($msg) . '" title="' . htmlspecialchars($msg) . '" />';
            } elseif ($flags['new'] || $flags['update']) {
                if ($flags['update']) {
                    $msg .= $flags['update'] . ' elements to update. ';
                }
                if ($flags['new']) {
                    $msg .= $flags['new'] . ' new elements found. ';
                }
                $output = '<img src="' . $GLOBALS['BACK_PATH'] . ExtensionManagementUtility::siteRelPath('l10nmgr') . 'flags_update.png" hspace="2" width="10" height="16" alt="' . htmlspecialchars($msg) . '" title="' . htmlspecialchars($msg) . '" />';
            } elseif ($flags['unknown']) {
                $msg .= 'Translation status is unknown for ' . $flags['unknown'] . ' elements. Please check and update. ';
                $output = '<img src="' . $GLOBALS['BACK_PATH'] . ExtensionManagementUtility::siteRelPath('l10nmgr') . 'flags_unknown.png" hspace="2" width="10" height="16" alt="' . htmlspecialchars($msg) . '" title="' . htmlspecialchars($msg) . '" />';
            } elseif ($flags['noChange']) {
                $msg .= 'All ' . $flags['noChange'] . ' translations OK';
                $output = '<img src="' . $GLOBALS['BACK_PATH'] . ExtensionManagementUtility::siteRelPath('l10nmgr') . 'flags_ok.png" hspace="2" width="10" height="16" alt="' . htmlspecialchars($msg) . '" title="' . htmlspecialchars($msg) . '" />';
            } else {
                $msg .= 'Nothing to do. ';
                $msg .= '[n/?/u/ok=' . implode('/', $flags) . ']';
                $output = '<img src="' . $GLOBALS['BACK_PATH'] . ExtensionManagementUtility::siteRelPath('l10nmgr') . 'flags_none.png" hspace="2" width="10" height="16" alt="' . htmlspecialchars($msg) . '" title="' . htmlspecialchars($msg) . '" />';
            }
            $output = !$noLink ? '<a href="#" onclick="' . htmlspecialchars('parent.list_frame.location.href="' . $GLOBALS['BACK_PATH'] . ExtensionManagementUtility::siteRelPath('l10nmgr') . 'cm2/index.php?table=' . $p[0] . '&uid=' . $p[1] . '&languageList=' . rawurlencode($languageList) . '"; return false;') . '" target="listframe">' . $output . '</a>' : $output;
        }
        return $output;
    }

    /**
     * Get DatabaseConnection instance - $GLOBALS['TYPO3_DB']
     *
     * This method should be used instead of direct access to
     * $GLOBALS['TYPO3_DB'] for easy IDE auto completion.
     *
     * @return DatabaseConnection
     * @deprecated since TYPO3 v8, will be removed in TYPO3 v9
     */
    protected function getDatabaseConnection()
    {
        GeneralUtility::logDeprecatedFunction();
        return $GLOBALS['TYPO3_DB'];
    }
}