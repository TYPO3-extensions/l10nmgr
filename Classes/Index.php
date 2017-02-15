<?php
namespace Localizationteam\L10nmgr;

/***************************************************************
 * Copyright notice
 * (c) 2007 Kasper Skaarhoj (kasperYYYY@typo3.com)
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
 * Cleaner module: Building index for translation
 * User function called from tx_lowlevel_cleaner_core configured in ext_localconf.php
 * See system extension, lowlevel!
 *
 * @author Kasper Skårhøj <kasperYYYY@typo3.com>
 */
use Localizationteam\L10nmgr\Model\Tools\Tools;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Lowlevel\CleanerCommand;

/**
 * Finding unused content elements
 *
 * @authorKasper Skårhøj <kasperYYYY@typo3.com>
 * @packageTYPO3
 * @subpackage tx_lowlevel
 */
class Index extends CleanerCommand
{
    /**
     * @var array List of not allowed doktypes
     */
    protected $disallowDoktypes = array('--div--', '255');
    /**
     * @var bool Check reference index
     */
    protected $checkRefIndex = false;
    /**
     * @var bool
     */
    protected $genTree_traverseDeleted = false;
    /**
     * @var bool
     */
    protected $genTree_traverseVersions = false;
    /**
     * @var array Extension's configuration as from the EM
     */
    protected $extensionConfiguration = array();
    /**
     * @var array
     */
    protected $resultArray;

    /**
     * Constructor
     *
     * @return void
     */
    public function Index()
    {
        // Load the extension's configuration
        $this->extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['l10nmgr']);
        $this->disallowDoktypes = GeneralUtility::trimExplode(',', $this->extensionConfiguration['disallowDoktypes']);
        parent::__construct();
        // Setting up help:
        $this->cli_options[] = array(
            '--echotree level',
            'When "level" is set to 1 or higher you will see the page of the page tree outputted as it is traversed. A value of 2 for "level" will show even more information.'
        );
        $this->cli_options[] = array(
            '--pid id_list',
            'Setting start page in page tree. Default is the page tree root, 0 (zero). You can specify a list of ids, eg "22,7,3" if you like. If you specify a negative id (eg. -1) nothing is index, but the index table is just flushed.'
        );
        $this->cli_options[] = array(
            '--workspace id',
            'Setting workspace uid for the session. Default is "0" for live workspace. The translation index depends on the workspace.'
        );
        $this->cli_options[] = array(
            '--depth int',
            'Setting traversal depth. 0 (zero) will only analyse start page (see --pid), 1 will traverse one level of subpages etc.'
        );
        $this->cli_options[] = array(
            '--noFlush',
            'If set, the index for the workspace will not be flushed. Normally you want to flush the index as a part of the process to make sure the rebuild of the index is empty before building it. But in cases you build individual parts of the tree you may like to use this option.'
        );
        $this->cli_options[] = array(
            '--bypassFilter',
            'If set, the external filter will not be called. The external filter allows other extensions to block certain records from getting processed. For instance TemplaVoila provides such a filter than will make sure records which are not used on a page are not indexed.'
        );
        $this->cli_help['name'] = 'tx_l10nmgr_index -- Building translation index';
        $this->cli_help['description'] = trim('
Traversing page tree and building an index of translation needs
');
        $this->cli_help['examples'] = '';
    }

    /**
     * @return array
     */
    public function main()
    {
        // Initialize result array:
        $resultArray = array(
            'message' => $this->cli_help['name'] . chr(10) . chr(10) . $this->cli_help['description'],
            'headers' => array(
                'index' => array('Full index of translation', 'NEEDS MUCH MORE WORK....', 1),
            ),
            'index' => array(),
        );
        $startingPoints = GeneralUtility::intExplode(',', $this->cli_argValue('--pid'));
        $workspaceID = $this->cli_isArg('--workspace') ? MathUtility::forceIntegerInRange($this->cli_argValue('--workspace'),
            -1) : 0;
        $depth = $this->cli_isArg('--depth') ? MathUtility::forceIntegerInRange($this->cli_argValue('--depth'),
            0) : 1000;
        if ($workspaceID != 0) {
            $this->getBackendUser()->setWorkspace($workspaceID);
            if ($this->getBackendUser()->workspace != $workspaceID) {
                die('Workspace ' . $workspaceID . ' did not exist!' . chr(10));
            }
        }
        $this->resultArray = &$resultArray;
        foreach ($startingPoints as $pidPoint) {
            if ($pidPoint >= 0) {
                $this->genTree($pidPoint, $depth, (int)$this->cli_argValue('--echotree'), 'main_parseTreeCallBack');
            }
        }
        return $resultArray;
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
     * Call back function for page tree traversal!
     *
     * @param string $tableName Table name
     * @param integer $uid UID of record in processing
     * @param integer $echoLevel Echo level (see calling function
     * @param string $versionSwapmode Version swap mode on that level (see calling function
     * @param integer $rootIsVersion Is root version (see calling function
     *
     * @return void
     */
    public function main_parseTreeCallBack($tableName, $uid, $echoLevel, $versionSwapmode, $rootIsVersion)
    {
        if ($tableName == 'pages' && $uid > 0) {
            $pageId = $uid;
            $excludeIndex = array();
            if (!$versionSwapmode) {
                // Init:
                /** @var Tools $t8Tools */
                $t8Tools = GeneralUtility::makeInstance(Tools::class);
                $t8Tools->verbose = false; // Otherwise it will show records which has fields but none editable.
                $t8Tools->bypassFilter = $this->cli_isArg('--bypassFilter') ? true : false;
                $pageRecord = BackendUtility::getRecord('pages', $uid);
                if (!in_array($pageRecord['doktype'],
                        $this->disallowDoktypes) && !isset($excludeIndex['pages:' . $pageId])
                ) {
                    $accum['header']['title'] = $pageRecord['title'];
                    $accum['items'] = $t8Tools->indexDetailsPage($pageId);
                    $this->resultArray['index'][$uid] = $accum;
                }
            } else {
                if ($echoLevel > 2) {
                    echo chr(10) . '[tx_templavoila_unusedce:] Did not check page - was on offline page.';
                }
            }
        }
    }

    /**
     * Mandatory autofix function
     * Will run auto-fix on the result array. Echos status during processing.
     *
     * @param array $resultArray Result array from main() function
     *
     * @return void
     */
    public function main_autoFix($resultArray)
    {
        // Init:
        /** @var Tools $t8Tools */
        $t8Tools = GeneralUtility::makeInstance(Tools::class);
        $t8Tools->verbose = false; // Otherwise it will show records which has fields but none editable.
        if (!$this->cli_isArg('--noFlush')) {
            echo 'Flushing translation index for workspace ' . $this->getBackendUser()->workspace . chr(10);
            $t8Tools->flushIndexOfWorkspace($this->getBackendUser()->workspace);
        } else {
            echo 'Did NOT flush translation index for workspace ' . $this->getBackendUser()->workspace . ' since it was disabled by --noFlush' . chr(10);
        }
        foreach ($this->resultArray['index'] as $pageId => $accum) {
            echo 'Adding entries for page ' . $pageId . ' "' . $accum['header']['title'] . '":' . chr(10);
            if (is_array($accum['items'])) {
                foreach ($accum['items'] as $tt => $rr) {
                    foreach ($rr as $rUid => $rDetails) {
                        $t8Tools->updateIndexTableFromDetailsArray($rDetails, true);
                    }
                }
            }
        }
    }
}