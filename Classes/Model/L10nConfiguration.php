<?php
namespace Localizationteam\L10nmgr\Model;

/***************************************************************
 * Copyright notice
 * (c) 2006 Kasper Skårhøj <kasperYYYY@typo3.com>
 * All rights reserved
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Backend\Tree\View\PageTreeView;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * l10nConfiguration
 * Capsulate a 10ncfg record.
 * Has factory method to get a relevant AccumulatedInformationsObject
 *
 * @authorKasper Skaarhoj <kasperYYYY@typo3.com>
 * @authorDaniel Pötzinger <ext@aoemedia.de>
 * @packageTYPO3
 * @subpackage tx_l10nmgr
 */
class L10nConfiguration
{
    /**
     * @var array
     */
    public $l10ncfg;

    /**
     * @var int
     */
    protected $sourcePid;

    /**
     * loads internal array with l10nmgrcfg record
     *
     * @param int $id Id of the cfg record
     *
     * @return void
     **/
    public function load($id)
    {
        $this->l10ncfg = BackendUtility::getRecord('tx_l10nmgr_cfg', $id);
    }

    /**
     * checks if configuration is valid
     *
     * @return boolean
     **/
    public function isLoaded()
    {
        // array must have values also!
        if (is_array($this->l10ncfg) && (!empty($this->l10ncfg))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * get uid field
     *
     * @return int
     **/
    public function getId()
    {
        return $this->getData('uid');
    }

    /**
     * get a field of the current cfgr record
     *
     * @param string $key Key of the field. E.g. title,uid...
     *
     * @return string Value of the field
     **/
    public function getData($key)
    {
        return $key === 'pid' && (int)$this->l10ncfg['depth'] === -1 && (int)$this->sourcePid ? (int)$this->sourcePid : $this->l10ncfg[$key];
    }

    /**
     * Factory method to create AccumulatedInformations Object (e.g. build tree etc...) (Factorys should have all dependencies passed as parameter)
     *
     * @param int $sysLang sys_language_uid
     *
     * @return L10nAccumulatedInformation
     */
    public function getL10nAccumulatedInformationsObjectForLanguage($sysLang)
    {
        $l10ncfg = $this->l10ncfg;
        $depth = (int)$l10ncfg['depth'];
        $treeStartingRecords = array();
        // Showing the tree:
        // Initialize starting point of page tree:
        if ($depth === -1) {
            $sourcePid = (int)$this->sourcePid ? (int)$this->sourcePid : (int)GeneralUtility::_GET('srcPID');
            $treeStartingPoints = array($sourcePid);
        } else {
            if ($depth === -2 && !empty($l10ncfg['pages'])) {
                $treeStartingPoints = GeneralUtility::intExplode(',', $l10ncfg['pages']);
            } else {
                $treeStartingPoints = array((int)$l10ncfg['pid']);
            }
        }
        /** @var $tree PageTreeView */
        if (!empty($treeStartingPoints)) {
            foreach ($treeStartingPoints as $treeStartingPoint) {
                $treeStartingRecords[] = BackendUtility::getRecordWSOL('pages', $treeStartingPoint);
            }
            // Initialize tree object:
            $tree = GeneralUtility::makeInstance(PageTreeView::class);
            $tree->init('AND ' . $this->getBackendUser()->getPagePermsClause(1));
            $tree->addField('l18n_cfg');
            /** @var IconFactory $iconFactory */
            $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
            $page = array_shift($treeStartingRecords);
            $HTML = $iconFactory->getIconForRecord('pages', $page, Icon::SIZE_SMALL)->render();
            $tree->tree[] = array(
                'row' => $page,
                'HTML' => $HTML
            );
            // Create the tree from starting point or page list:
            if ($depth > 0) {
                $tree->getTree($page['uid'], $depth, '');
            } else {
                if (!empty($treeStartingRecords)) {
                    foreach ($treeStartingRecords as $page) {
                        $HTML = $iconFactory->getIconForRecord('pages', $page, Icon::SIZE_SMALL)->render();
                        $tree->tree[] = array(
                            'row' => $page,
                            'HTML' => $HTML
                        );
                    }
                }
            }
        }
        //now create and init accum Info object:
        /** @var L10nAccumulatedInformation $accumObj */
        $accumObj = GeneralUtility::makeInstance(L10nAccumulatedInformation::class, $tree, $l10ncfg, $sysLang);
        return $accumObj;
    }

    /**
     * Returns the Backend User
     * @return BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * @param int $sysLang
     * @param array $flexFormDiffArray
     */
    public function updateFlexFormDiff($sysLang, $flexFormDiffArray)
    {
        $l10ncfg = $this->l10ncfg;
        // Updating diff-data:
        // First, unserialize/initialize:
        $flexFormDiffForAllLanguages = unserialize($l10ncfg['flexformdiff']);
        if (!is_array($flexFormDiffForAllLanguages)) {
            $flexFormDiffForAllLanguages = array();
        }
        // Set the data (
        $flexFormDiffForAllLanguages[$sysLang] = array_merge((array)$flexFormDiffForAllLanguages[$sysLang],
            $flexFormDiffArray);
        // Serialize back and save it to record:
        $l10ncfg['flexformdiff'] = serialize($flexFormDiffForAllLanguages);
        $this->getDatabaseConnection()->exec_UPDATEquery('tx_l10nmgr_cfg', 'uid=' . (int)$l10ncfg['uid'],
            array('flexformdiff' => $l10ncfg['flexformdiff']));
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

    /**
     * @param int $id
     * @return void
     */
    public function setSourcePid($id)
    {
        $this->sourcePid = (int)$id;
    }

}