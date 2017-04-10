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
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;

/**
 * Function for managing the Import of CAT XML
 *
 * @author Daniel Poetzinger <ext@aoemedia.de>
 * @package TYPO3
 * @subpackage tx_l10nmgr
 */
class CatXmlImportManager
{
    /**
     * @var string $headerData headerData of the XML
     */
    public $headerData = '';
    /**
     * @var string $file filepath with XML
     */
    protected $file = '';
    /**
     * @var string $xml CATXML
     */
    protected $xml = '';
    /**
     * @var array $xmlNodes parsed XML
     */
    protected $xmlNodes;
    /**
     * @var integer $sysLang selected import language (for check purposes - sys_language_uid)
     */
    protected $sysLang;
    /**
     * @var array $_errorMsg accumulated errormessages
     */
    protected $_errorMsg = array();
    /**
     * @var LanguageService
     */
    protected $languageService;

    public function __construct($file, $sysLang, $xmlString)
    {
        $this->sysLang = $sysLang;
        if (!empty($file)) {
            $this->file = $file;
        }
        if (!empty($xmlString)) {
            $this->xmlString = $xmlString;
        }
    }

    /**
     * @return bool
     */
    public function parseAndCheckXMLFile()
    {
        $fileContent = GeneralUtility::getUrl($this->file);
        $this->xmlNodes = GeneralUtility::xml2tree(str_replace('&nbsp;', '&#160;', $fileContent),
            3); // For some reason PHP chokes on incoming &nbsp; in XML!
        if (!is_array($this->xmlNodes)) {
            $this->_errorMsg[] = $this->getLanguageService()->getLL('import.manager.error.parsing.xml2tree.message') . $this->xmlNodes . ' Content: ' . $fileContent;
            return false;
        }
        $headerInformationNodes = $this->xmlNodes['TYPO3L10N'][0]['ch']['head'][0]['ch'];
        if (!is_array($headerInformationNodes)) {
            $this->_errorMsg[] = $this->getLanguageService()->getLL('import.manager.error.missing.head.message');
            return false;
        }
        $this->_setHeaderData($headerInformationNodes);
        if ($this->_isIncorrectXMLFile()) {
            return false;
        }
        return true;
    }

    /**
     * getter/setter for LanguageService object
     *
     * @return LanguageService $languageService
     */
    protected function getLanguageService()
    {
        if (!$this->languageService instanceof LanguageService) {
            $this->languageService = GeneralUtility::makeInstance(LanguageService::class);
        }
        if ($this->getBackendUser()) {
            $this->languageService->init($this->getBackendUser()->uc['lang']);
        }
        return $this->languageService;
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
     * @param array $headerInformationNodes
     */
    protected function _setHeaderData($headerInformationNodes)
    {
        if (!is_array($headerInformationNodes)) {
            return;
        }
        foreach ($headerInformationNodes as $k => $v) {
            $this->headerData[$k] = '';
            if (is_array($v) && is_array($v[0]) && is_array($v[0]['values'])) {
                $this->headerData[$k] = $v[0]['values'][0];
            }
       }
    }

    /**
     * @return bool
     */
    protected function _isIncorrectXMLFile()
    {
        $error = array();
        if (!isset($this->headerData['t3_formatVersion']) || $this->headerData['t3_formatVersion'] != L10NMGR_FILEVERSION) {
            $error[] = sprintf($this->getLanguageService()->getLL('import.manager.error.version.message'),
                $this->headerData['t3_formatVersion'], L10NMGR_FILEVERSION);
        }
        if (!isset($this->headerData['t3_workspaceId']) || $this->headerData['t3_workspaceId'] != $this->getBackendUser()->workspace) {
            $this->getBackendUser()->workspace = $this->headerData['t3_workspaceId'];
            $error[] = sprintf($this->getLanguageService()->getLL('import.manager.error.workspace.message'),
                $this->getBackendUser()->workspace,
                $this->headerData['t3_workspaceId']);
        }
        if (!isset($this->headerData['t3_sysLang']) || $this->headerData['t3_sysLang'] != $this->sysLang) {
            $error[] = sprintf($this->getLanguageService()->getLL('import.manager.error.language.message'),
                $this->sysLang,
                $this->headerData['t3_sysLang']);
        }
        if (count($error) > 0) {
            $this->_errorMsg = array_merge($this->_errorMsg, $error);
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function parseAndCheckXMLString()
    {
        $catXmlString = $this->xmlString;
        $this->xmlNodes = GeneralUtility::xml2tree(str_replace('&nbsp;', '&#160;', $catXmlString),
            3); // For some reason PHP chokes on incoming &nbsp; in XML!
        if (!is_array($this->xmlNodes)) {
            $this->_errorMsg[] = $this->getLanguageService()->getLL('import.manager.error.parsing.xml2tree.message') . $this->xmlNodes;
            return false;
        }
        $headerInformationNodes = $this->xmlNodes['TYPO3L10N'][0]['ch']['head'][0]['ch'];
        if (!is_array($headerInformationNodes)) {
            $this->_errorMsg[] = $this->getLanguageService()->getLL('import.manager.error.missing.head.message');
            return false;
        }
        $this->_setHeaderData($headerInformationNodes);
        if ($this->_isIncorrectXMLString()) {
            return false;
        }
        return true;
    }

    /**
     * @return bool
     */
    protected function _isIncorrectXMLString()
    {
        $error = array();
        if (!isset($this->headerData['t3_formatVersion']) || $this->headerData['t3_formatVersion'] != L10NMGR_FILEVERSION) {
            $error[] = sprintf($this->getLanguageService()->getLL('import.manager.error.version.message'),
                $this->headerData['t3_formatVersion'], L10NMGR_FILEVERSION);
        }
        if (!isset($this->headerData['t3_workspaceId']) || $this->headerData['t3_workspaceId'] != $this->getBackendUser()->workspace) {
            $error[] = sprintf($this->getLanguageService()->getLL('import.manager.error.workspace.message'),
                $this->getBackendUser()->workspace,
                $this->headerData['t3_workspaceId']);
        }
        if (!isset($this->headerData['t3_sysLang'])) {
            //if (!isset($this->headerData['t3_sysLang']) || $this->headerData['t3_sysLang'] != $this->sysLang) {
            $error[] = sprintf($this->getLanguageService()->getLL('import.manager.error.language.message'),
                $this->sysLang,
                $this->headerData['t3_sysLang']);
        }
        if (count($error) > 0) {
            $this->_errorMsg = array_merge($this->_errorMsg, $error);
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getErrorMessages()
    {
        return implode('<br />', $this->_errorMsg);
    }

    /**
     * @return array
     */
    public function &getXMLNodes()
    {
        return $this->xmlNodes;
    }

    /**
     * Get pageGrp IDs for preview link generation
     *
     * @param array $xmlNodes XML nodes from CATXML
     *
     * @return array Page IDs for preview
     */
    public function getPidsFromCATXMLNodes(&$xmlNodes)
    {
        $pids = array();
        if (is_array($xmlNodes['TYPO3L10N'][0]['ch']['pageGrp'])) {
            foreach ($xmlNodes['TYPO3L10N'][0]['ch']['pageGrp'] as $pageGrp) {
                $pids[] = $pageGrp['attrs']['id'];
            }
        }
        return $pids;
    }

    /**
     * Get uids for which localizations shall be removed on 2nd import if option checked
     *
     * @param array $xmlNodes XML nodes from CATXML
     *
     * @return array Uids for which localizations shall be removed
     */
    public function getDelL10NDataFromCATXMLNodes(&$xmlNodes)
    {
        //get L10Ns to be deleted before import
        $delL10NUids = array();
        if (is_array($xmlNodes['TYPO3L10N'][0]['ch']['pageGrp'])) {
            foreach ($xmlNodes['TYPO3L10N'][0]['ch']['pageGrp'] as $pageGrp) {
                if (is_array($pageGrp['ch']['data'])) {
                    foreach ($pageGrp['ch']['data'] as $row) {
                        if (preg_match('/NEW/', $row['attrs']['key'])) {
                            $delL10NUids[] = $row['attrs']['table'] . ':' . $row['attrs']['elementUid'];
                        }
                    }
                }
            }
        }
        return array_unique($delL10NUids);
    }

    /**
     * Delete previous localisations
     *
     * @param array $delL10NData table:id combinations to be deleted
     *
     * @return int Number of deleted elements
     */
    public function delL10N($delL10NData)
    {
        //delete previous L10Ns
        $cmdCount = 0;
        /** @var Datahandler $dataHandler */
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start(array(), array());
        foreach ($delL10NData as $element) {
            list($table, $elementUid) = explode(':', $element);
            if ($table == 'pages') {
                $table = 'pages_language_overlay';
                $where = 'pid = ' . (int)$elementUid . ' AND sys_language_uid = ' . (int)$this->headerData['t3_sysLang'] . ' AND t3ver_wsid = ' . (int)$this->headerData['t3_workspaceId'];
            } else {
                $languageField = $GLOBALS['TCA'][$table]['ctrl']['languageField'];
                $l18nPointerField = $GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField'];
                $where = $l18nPointerField . " = $elementUid AND " . $languageField . " = " . $this->headerData['t3_sysLang'] . " AND t3ver_wsid = " . $this->headerData['t3_workspaceId'];
            }
            $delDataQuery = $this->getDatabaseConnection()->exec_SELECTgetRows('uid', $table, $where, '', '', '',
                'uid');
            if (!empty($delDataQuery)) {
                foreach ($delDataQuery as $uid => $item) {
                    $dataHandler->deleteAction($table, $uid);
                }
            }
            $cmdCount++;
        }
        return $cmdCount;
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