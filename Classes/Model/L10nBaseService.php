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
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Database\RelationHandler;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * baseService class for offering common services like saving translation etc...
 *
 * @authorKasper Skaarhoj <kasperYYYY@typo3.com>
 * @authorDaniel Pötzinger <development@aoemedia.de>
 * @packageTYPO3
 * @subpackage tx_l10nmgr
 */
class L10nBaseService
{
    protected static $targetLanguageID = null;
    public $lastTCEMAINCommandsCount;
    /**
     * @var bool Translate even if empty.
     */
    protected $createTranslationAlsoIfEmpty = false;
    /**
     * @var bool Import as default language.
     */
    protected $importAsDefaultLanguage = false;
    /**
     * @var array Extension's configuration as from the EM
     */
    protected $extensionConfiguration = array();
    /**
     * @var array
     */
    protected $TCEmain_cmd = array();
    /**
     * @var array
     */
    protected $checkedParentRecords = array();
    /**
     * @var int
     */
    protected $depthCounter = 0;
    /**
     * @var array
     */
    protected $flexFormDiffArray;

    public function __construct()
    {
        // Load the extension's configuration
        $this->extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['l10nmgr']);
    }

    /**
     * @return integer|NULL
     */
    public static function getTargetLanguageID()
    {
        return self::$targetLanguageID;
    }

    /**
     * Save the translation
     *
     * @param L10nConfiguration $l10ncfgObj
     * @param TranslationData $translationObj
     */
    public function saveTranslation(L10nConfiguration $l10ncfgObj, TranslationData $translationObj)
    {
        // Provide a hook for specific manipulations before saving
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['l10nmgr']['savePreProcess'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['l10nmgr']['savePreProcess'] as $classReference) {
                $processingObject = GeneralUtility::getUserObj($classReference);
                $processingObject->processBeforeSaving($l10ncfgObj, $translationObj, $this);
            }
        }
        // make sure to translate all pages and content elements that are available on these pages
        $this->preTranslateAllContent($l10ncfgObj, $translationObj);
        $this->remapInputDataForExistingTranslations($l10ncfgObj, $translationObj);
        $sysLang = $translationObj->getLanguage();
        $previewLanguage = $translationObj->getPreviewLanguage();
        $accumObj = $l10ncfgObj->getL10nAccumulatedInformationsObjectForLanguage($sysLang);
        $accumObj->setForcedPreviewLanguage($previewLanguage);
        $flexFormDiffArray = $this->_submitContentAndGetFlexFormDiff($accumObj->getInfoArray(),
            $translationObj->getTranslationData());
        if ($flexFormDiffArray !== false) {
            $l10ncfgObj->updateFlexFormDiff($sysLang, $flexFormDiffArray);
        }
        // Provide a hook for specific manipulations after saving
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['l10nmgr']['savePostProcess'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['l10nmgr']['savePostProcess'] as $classReference) {
                $processingObject = GeneralUtility::getUserObj($classReference);
                $processingObject->processAfterSaving($l10ncfgObj, $translationObj, $flexFormDiffArray, $this);
            }
        }
    }

    /**
     * Function that iterates over all page records that are given within the import data
     * and translate all pages and content elements
     * beforehand so ordering and container elements work just as expected.
     *
     * Goes hand in hand with the remapInputDataForExistingTranslations() functionality, which then replaces the elements
     * which would be expected to be new)
     *
     * @param L10nConfiguration $configurationObject
     * @param TranslationData $translationData
     */
    protected function preTranslateAllContent(L10nConfiguration $configurationObject, TranslationData $translationData)
    {
        // feature is not enabled
        if (!$configurationObject->getData('pretranslatecontent')) {
            return;
        }
        $inputArray = $translationData->getTranslationData();
        $pageUids = array_keys($inputArray['pages']);
        foreach ($pageUids as $pageUid) {
            $this->translateContentOnPage($pageUid, (int)$translationData->getLanguage());
        }
    }

    /**
     * Translates all non-translated content elements on a certain page (and the page itself)
     *
     * @param int $pageUid
     * @param int $targetLanguageUid
     */
    protected function translateContentOnPage($pageUid, $targetLanguageUid)
    {
        // Check if the page itself was translated already, if not, translate it
        $translatedPageRecords = BackendUtility::getRecordLocalization('pages', $pageUid, $targetLanguageUid);
        if ($translatedPageRecords === false) {
            // translate the page first
            $commands = array(
                'pages' => array(
                    $pageUid => array(
                        'localize' => $targetLanguageUid
                    )
                )
            );
            $dataHandler = $this->getDataHandlerInstance();
            $dataHandler->start(array(), $commands);
            $dataHandler->process_cmdmap();
        }
        $commands = array();
        $gridElementsInstalled = ExtensionManagementUtility::isLoaded('gridelements');
        if ($gridElementsInstalled) {
            // find all tt_content elements in the default language of this page that are NOT inside a grid element
            $recordsInOriginalLanguage = BackendUtility::getRecordsByField('tt_content', 'pid', $pageUid,
                'AND sys_language_uid=0 AND tx_gridelements_container=0', '', 'colPos, sorting');
            foreach ($recordsInOriginalLanguage as $recordInOriginalLanguage) {
                $translatedContentElements = BackendUtility::getRecordLocalization('tt_content',
                    $recordInOriginalLanguage['uid'], $targetLanguageUid);
                if (empty($translatedContentElements)) {
                    $commands['tt_content'][$recordInOriginalLanguage['uid']]['localize'] = $targetLanguageUid;
                }
            }
            // find all tt_content elements in the default language of this page that ARE inside a grid element
            $recordsInOriginalLanguage = BackendUtility::getRecordsByField('tt_content', 'pid', $pageUid,
                'AND sys_language_uid=0 AND tx_gridelements_container!=0', '', 'colPos, sorting');
            foreach ($recordsInOriginalLanguage as $recordInOriginalLanguage) {
                $translatedContentElements = BackendUtility::getRecordLocalization('tt_content',
                    $recordInOriginalLanguage['uid'], $targetLanguageUid);
                if (empty($translatedContentElements)) {
                    $commands['tt_content'][$recordInOriginalLanguage['uid']]['localize'] = $targetLanguageUid;
                }
            }
        } else {
            // find all tt_content elements in the default language of this page
            $recordsInOriginalLanguage = BackendUtility::getRecordsByField('tt_content', 'pid', $pageUid,
                'AND sys_language_uid=0', '', 'colPos, sorting');
            foreach ($recordsInOriginalLanguage as $recordInOriginalLanguage) {
                $translatedContentElements = BackendUtility::getRecordLocalization('tt_content',
                    $recordInOriginalLanguage['uid'], $targetLanguageUid);
                if (empty($translatedContentElements)) {
                    $commands['tt_content'][$recordInOriginalLanguage['uid']]['localize'] = $targetLanguageUid;
                }
            }
        }
        if (count($commands)) {
            // don't do the "prependAtCopy"
            $GLOBALS['TCA']['tt_content']['ctrl']['prependAtCopy'] = false;
            $dataHandler = $this->getDataHandlerInstance();
            $dataHandler->start(array(), $commands);
            $dataHandler->process_cmdmap();
        }
    }

    /**
     * @return DataHandler
     */
    protected function getDataHandlerInstance()
    {
        /** @var DataHandler $dataHandler */
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        if ($this->extensionConfiguration['enable_neverHideAtCopy'] == 1) {
            $dataHandler->neverHideAtCopy = true;
        }
        $dataHandler->dontProcessTransformations = true;
        $dataHandler->isImporting = true;
        return $dataHandler;
    }

    /**
     * If you want to reimport the same file over and over again, by default this can only be done once because the input array
     * contains "NEW" all over the place in th XML file.
     * This feature (enabled per configuration record) maps the data of the existing record in the target language
     * to re-import the data again and again.
     *
     * This also allows to import data of records that have been added in TYPO3 in the meantime.
     *
     * @param L10nConfiguration $configurationObject
     * @param TranslationData $translationData
     */
    protected function remapInputDataForExistingTranslations(
        L10nConfiguration $configurationObject,
        TranslationData $translationData
    ) {
        // feature is not enabled
        if (!$configurationObject->getData('overrideexistingtranslations')) {
            return;
        }
        $inputArray = $translationData->getTranslationData();
        // clean up input array and replace the "NEW" fields with actual values if they have been translated already
        $cleanedInputArray = array();
        foreach ($inputArray as $table => $elementsInTable) {
            foreach ($elementsInTable as $elementUid => $fields) {
                foreach ($fields as $fieldKey => $translatedValue) {
                    // check if the record was marked as "new" but was translated already
                    list($Ttable, $TuidString, $Tfield, $Tpath) = explode(':', $fieldKey);
                    list($Tuid, $Tlang, $TdefRecord) = explode('/', $TuidString);
                    if ($Tuid === 'NEW') {
                        $translatedRecord = BackendUtility::getRecordLocalization($Ttable, $TdefRecord, $Tlang);
                        if (!empty($translatedRecord)) {
                            $translatedRecord = reset($translatedRecord);
                            if ($translatedRecord['uid'] > 0) {
                                $fieldKey = $Ttable . ':' . $translatedRecord['uid'] . ':' . $Tfield;
                                if ($Tpath) {
                                    $fieldKey .= ':' . $Tpath;
                                }
                            }
                        }
                    }
                    $cleanedInputArray[$table][$elementUid][$fieldKey] = $translatedValue;
                }
            }
        }
        $translationData->setTranslationData($cleanedInputArray);
    }

    /**
     * Submit incoming content to database. Must match what is available in $accum.
     *
     * @param array $accum Translation configuration
     * @param array $inputArray Array with incoming translation. Must match what is found in $accum
     *
     * @return mixed False if error - else flexFormDiffArray (if $inputArray was an array and processing was performed.)
     */
    protected function _submitContentAndGetFlexFormDiff($accum, $inputArray)
    {
        if ($this->getImportAsDefaultLanguage()) {
            return $this->_submitContentAsDefaultLanguageAndGetFlexFormDiff($accum, $inputArray);
        } else {
            return $this->_submitContentAsTranslatedLanguageAndGetFlexFormDiff($accum, $inputArray);
        }
    }

    /**
     * Getter for $importAsDefaultLanguage
     *
     * @return boolean
     */
    public function getImportAsDefaultLanguage()
    {
        return $this->importAsDefaultLanguage;
    }

    /**
     * Setter for $importAsDefaultLanguage
     *
     * @param boolean $importAsDefaultLanguage
     *
     * @return void
     */
    public function setImportAsDefaultLanguage($importAsDefaultLanguage)
    {
        $this->importAsDefaultLanguage = $importAsDefaultLanguage;
    }

    /**
     * Submit incoming content as default language to database. Must match what is available in $accum.
     *
     * @param array $accum Translation configuration
     * @param array $inputArray Array with incoming translation. Must match what is found in $accum
     *
     * @return mixed False if error - else flexFormDiffArray (if $inputArray was an array and processing was performed.)
     */
    protected function _submitContentAsDefaultLanguageAndGetFlexFormDiff($accum, $inputArray)
    {
        if (is_array($inputArray)) {
            // Initialize:
            /** @var FlexFormTools $flexToolObj */
            $flexToolObj = GeneralUtility::makeInstance(FlexFormTools::class);
            $TCEmain_data = array();
            $_flexFormDiffArray = array();
            // Traverse:
            foreach ($accum as $pId => $page) {
                foreach ($accum[$pId]['items'] as $table => $elements) {
                    foreach ($elements as $elementUid => $data) {
                        $hooks = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['l10nmgr']['beforeDataFieldsDefault'];
                        if (is_array($hooks)) {
                            foreach ($hooks as $hookObj) {
                                $parameters = array(
                                    'data' => $data
                                );
                                $data = GeneralUtility::callUserFunction($hookObj, $parameters, $this);
                            }
                        }
                        if (is_array($data['fields'])) {
                            foreach ($data['fields'] as $key => $tData) {
                                if (is_array($tData) && isset($inputArray[$table][$elementUid][$key])) {
                                    list($Ttable, $TuidString, $Tfield, $Tpath) = explode(':', $key);
                                    list($Tuid, $Tlang, $TdefRecord) = explode('/', $TuidString);
                                    if (!$this->createTranslationAlsoIfEmpty && $inputArray[$table][$elementUid][$key] == '' && $Tuid == 'NEW') {
                                        //if data is empty do not save it
                                        unset($inputArray[$table][$elementUid][$key]);
                                        continue;
                                    }
                                    // If FlexForm, we set value in special way:
                                    if ($Tpath) {
                                        if (!is_array($TCEmain_data[$Ttable][$elementUid][$Tfield])) {
                                            $TCEmain_data[$Ttable][$elementUid][$Tfield] = array();
                                        }
                                        //TCEMAINDATA is passed as reference here:
                                        $flexToolObj->setArrayValueByPath($Tpath,
                                            $TCEmain_data[$Ttable][$elementUid][$Tfield],
                                            $inputArray[$table][$elementUid][$key]);
                                        $_flexFormDiffArray[$key] = array(
                                            'translated' => $inputArray[$table][$elementUid][$key],
                                            'default' => $tData['defaultValue']
                                        );
                                    } else {
                                        $TCEmain_data[$Ttable][$elementUid][$Tfield] = $inputArray[$table][$elementUid][$key];
                                    }
                                    unset($inputArray[$table][$elementUid][$key]); // Unsetting so in the end we can see if $inputArray was fully processed.
                                } else {
                                    //debug($tData,'fields not set for: '.$elementUid.'-'.$key);
                                    //debug($inputArray[$table],'inputarray');
                                }
                            }
                            if (is_array($inputArray[$table][$elementUid]) && !count($inputArray[$table][$elementUid])) {
                                unset($inputArray[$table][$elementUid]); // Unsetting so in the end we can see if $inputArray was fully processed.
                            }
                        }
                        $hooks = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['l10nmgr']['afterDataFieldsDefault'];
                        if (is_array($hooks)) {
                            foreach ($hooks as $hookObj) {
                                $parameters = array(
                                    'TCEmain_data' => $TCEmain_data,
                                );
                                $TCEmain_data = GeneralUtility::callUserFunction($hookObj, $parameters, $this);
                            }
                        }
                    }
                    if (is_array($inputArray[$table]) && !count($inputArray[$table])) {
                        unset($inputArray[$table]); // Unsetting so in the end we can see if $inputArray was fully processed.
                    }
                }
            }
            if ($TCEmain_data['pages_language_overlay']) {
                $TCEmain_data['pages'] = $TCEmain_data['pages_language_overlay'];
                unset($TCEmain_data['pages_language_overlay']);
            }
            $this->lastTCEMAINCommandsCount = 0;
            // Now, submitting translation data:
            /** @var DataHandler $tce */
            $tce = GeneralUtility::makeInstance(DataHandler::class);
            $tce->dontProcessTransformations = true;
            $tce->isImporting = true;
            foreach (array_chunk($TCEmain_data, 100, true) as $dataPart) {
                $tce->start($dataPart,
                    array()); // check has been done previously that there is a backend user which is Admin and also in live workspace
                $tce->process_datamap();
            }
            if (count($tce->errorLog)) {
                GeneralUtility::sysLog(__FILE__ . ': ' . __LINE__ . ': TCEmain update errors: ' . GeneralUtility::arrayToLogString($tce->errorLog),
                    'l10nmgr');
            }
            if (count($tce->autoVersionIdMap) && count($_flexFormDiffArray)) {
                foreach ($_flexFormDiffArray as $key => $value) {
                    list($Ttable, $Tuid, $Trest) = explode(':', $key, 3);
                    if ($tce->autoVersionIdMap[$Ttable][$Tuid]) {
                        $_flexFormDiffArray[$Ttable . ':' . $tce->autoVersionIdMap[$Ttable][$Tuid] . ':' . $Trest] = $_flexFormDiffArray[$key];
                        unset($_flexFormDiffArray[$key]);
                    }
                }
            }
            // Should be empty now - or there were more information in the incoming array than there should be!
            if (count($inputArray)) {
                debug($inputArray, 'These fields were ignored since they were not in the configuration 1:');
            }
            return $_flexFormDiffArray;
        } else {
            return false;
        }
    }

    /**
     * Submit incoming content as translated language to database. Must match what is available in $accum.
     *
     * @param array $accum Translation configuration
     * @param array $inputArray Array with incoming translation. Must match what is found in $accum
     *
     * @return mixed False if error - else flexFormDiffArray (if $inputArray was an array and processing was performed.)
     */
    protected function _submitContentAsTranslatedLanguageAndGetFlexFormDiff($accum, $inputArray)
    {
        global $TCA;
        if (is_array($inputArray)) {
            // Initialize:
            /** @var FlexFormTools $flexToolObj */
            $flexToolObj = GeneralUtility::makeInstance(FlexFormTools::class);
            $gridElementsInstalled = ExtensionManagementUtility::isLoaded('gridelements');
            $fluxInstalled = ExtensionManagementUtility::isLoaded('flux');
            $element = array();
            $TCEmain_data = array();
            $this->TCEmain_cmd = array();
            $Tlang = '';
            $_flexFormDiffArray = array();
            // Traverse:
            foreach ($accum as $pId => $page) {
                foreach ($accum[$pId]['items'] as $table => $elements) {
                    foreach ($elements as $elementUid => $data) {
                        $hooks = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['l10nmgr']['beforeDataFieldsTranslated'];
                        if (is_array($hooks)) {
                            foreach ($hooks as $hookObj) {
                                $parameters = array(
                                    'data' => $data
                                );
                                $data = GeneralUtility::callUserFunction($hookObj, $parameters, $this);
                            }
                        }
                        if (is_array($data['fields'])) {
                            foreach ($data['fields'] as $key => $tData) {
                                if (is_array($tData) && array_key_exists($key, $inputArray[$table][$elementUid])) {
                                    list($Ttable, $TuidString, $Tfield, $Tpath) = explode(':', $key);
                                    list($Tuid, $Tlang, $TdefRecord) = explode('/', $TuidString);
                                    if (!$this->createTranslationAlsoIfEmpty && $inputArray[$table][$elementUid][$key] == '' && $Tuid == 'NEW') {
                                        //if data is empty do not save it
                                        unset($inputArray[$table][$elementUid][$key]);
                                        continue;
                                    }
                                    // If new element is required, we prepare for localization
                                    if ($Tuid === 'NEW') {
                                        if ($table === 'tt_content' && ($gridElementsInstalled === true || $fluxInstalled === true)) {
                                            $element = BackendUtility::getRecordRaw($table,
                                                'uid = ' . (int)$elementUid . ' AND deleted = 0');
                                            if (isset($this->TCEmain_cmd['tt_content'][$elementUid])) {
                                                unset($this->TCEmain_cmd['tt_content'][$elementUid]);
                                            }
                                            if ((int)$element['colPos'] > -1 && (int)$element['colPos'] !== 18181) {
                                                $this->TCEmain_cmd['tt_content'][$elementUid]['localize'] = $Tlang;
                                            } else {
                                                if ($element['tx_gridelements_container'] > 0) {
                                                    $this->depthCounter = 0;
                                                    $this->recursivelyCheckForRelationParents($element, $Tlang,
                                                        'tx_gridelements_container', 'tx_gridelements_children');
                                                }
                                                if ($element['tx_flux_parent'] > 0) {
                                                    $this->depthCounter = 0;
                                                    $this->recursivelyCheckForRelationParents($element, $Tlang,
                                                        'tx_flux_parent', 'tx_flux_children');
                                                }
                                            }
                                        } elseif ($table === 'sys_file_reference') {
                                            $element = BackendUtility::getRecordRaw($table,
                                                'uid = ' . (int)$elementUid . ' AND deleted = 0');
                                            if ($element['uid_foreign'] && $element['tablenames'] && $element['fieldname']) {
                                                if ($element['tablenames'] === 'pages') {
                                                    if (isset($this->TCEmain_cmd[$table][$elementUid])) {
                                                        unset($this->TCEmain_cmd[$table][$elementUid]);
                                                    }
                                                    $this->TCEmain_cmd[$table][$elementUid]['localize'] = $Tlang;
                                                    $TCEmain_data[$Ttable][$TuidString]['tablenames'] = 'pages';
                                                } else {
                                                    $parent = BackendUtility::getRecordRaw($element['tablenames'],
                                                        $TCA[$element['tablenames']]['ctrl']['transOrigPointerField'] . ' = ' . (int)$element['uid_foreign'] .
                                                        ' AND deleted = 0 AND sys_language_uid = ' . (int)$Tlang);
                                                    if ($parent['uid'] > 0) {
                                                        if (isset($this->TCEmain_cmd[$element['tablenames']][$element['uid_foreign']])) {
                                                            unset($this->TCEmain_cmd[$element['tablenames']][$element['uid_foreign']]);
                                                        }
                                                        $this->TCEmain_cmd[$element['tablenames']][$element['uid_foreign']]['inlineLocalizeSynchronize'] = $element['fieldname'] . ',localize';
                                                    }
                                                }
                                            }
                                        } else {
                                            //print "\nNEW\n";
                                            if (isset($this->TCEmain_cmd[$table][$elementUid])) {
                                                unset($this->TCEmain_cmd[$table][$elementUid]);
                                            }
                                            $this->TCEmain_cmd[$table][$elementUid]['localize'] = $Tlang;
                                        }
                                        $hooks = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['l10nmgr']['importNewTceMainCmd'];
                                        if (is_array($hooks)) {
                                            foreach ($hooks as $hookObj) {
                                                $parameters = array(
                                                    'data' => $data,
                                                    'TCEmain_cmd' => $this->TCEmain_cmd
                                                );
                                                $this->TCEmain_cmd = GeneralUtility::callUserFunction($hookObj,
                                                    $parameters, $this);
                                            }
                                        }
                                    }
                                    // If FlexForm, we set value in special way:
                                    if ($Tpath) {
                                        if (!is_array($TCEmain_data[$Ttable][$TuidString][$Tfield])) {
                                            $TCEmain_data[$Ttable][$TuidString][$Tfield] = array();
                                        }
                                        //TCEMAINDATA is passed as reference here:
                                        $flexToolObj->setArrayValueByPath($Tpath,
                                            $TCEmain_data[$Ttable][$TuidString][$Tfield],
                                            $inputArray[$table][$elementUid][$key]);
                                        $_flexFormDiffArray[$key] = array(
                                            'translated' => $inputArray[$table][$elementUid][$key],
                                            'default' => $tData['defaultValue']
                                        );
                                    } else {
                                        $TCEmain_data[$Ttable][$TuidString][$Tfield] = $inputArray[$table][$elementUid][$key];
                                    }
                                    unset($inputArray[$table][$elementUid][$key]); // Unsetting so in the end we can see if $inputArray was fully processed.
                                } else {
                                    //debug($tData,'fields not set for: '.$elementUid.'-'.$key);
                                    //debug($inputArray[$table],'inputarray');
                                }
                            }
                            if (is_array($inputArray[$table][$elementUid]) && !count($inputArray[$table][$elementUid])) {
                                unset($inputArray[$table][$elementUid]); // Unsetting so in the end we can see if $inputArray was fully processed.
                            }
                        }

                        /** @var $relationHandler RelationHandler */
                        // integrators have to make sure to configure fields of parent elements properly
                        // so they will do translations of their children automatically when translated
                        if (!empty($TCA[$table]['columns'])) {
                            foreach ($TCA[$table]['columns'] as $column => $setup) {
                                $configuration = $setup['config'];
                                if ($configuration['foreign_table']) {
                                    $relationHandler = GeneralUtility::makeInstance(RelationHandler::class);
                                    $relationHandler->start($element[$column], $configuration['foreign_table'],
                                        $configuration['MM'], $elementUid, $table, $configuration);
                                    $relationHandler->processDeletePlaceholder();
                                    $referenceUids = $relationHandler->tableArray[$configuration['foreign_table']];
                                    if (!empty($referenceUids)) {
                                        foreach ($referenceUids as $referenceUid) {
                                            unset($this->TCEmain_cmd[$configuration['foreign_table']][$referenceUid]);
                                        }
                                    }
                                }
                            }
                        }

                        $hooks = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['l10nmgr']['afterDataFieldsTranslated'];
                        if (is_array($hooks)) {
                            foreach ($hooks as $hookObj) {
                                $parameters = array(
                                    'TCEmain_data' => $TCEmain_data,
                                    'TCEmain_cmd' => $this->TCEmain_cmd
                                );
                                $this->TCEmain_cmd = GeneralUtility::callUserFunction($hookObj, $parameters, $this);
                            }
                        }
                    }
                    if (is_array($inputArray[$table]) && !count($inputArray[$table])) {
                        unset($inputArray[$table]); // Unsetting so in the end we can see if $inputArray was fully processed.
                    }
                }
            }
            self::$targetLanguageID = $Tlang;
            // Execute CMD array: Localizing records:
            /** @var DataHandler $tce */
            $tce = GeneralUtility::makeInstance(DataHandler::class);
            if ($this->extensionConfiguration['enable_neverHideAtCopy'] == 1) {
                $tce->neverHideAtCopy = true;
            }
            $tce->isImporting = true;
            if (count($this->TCEmain_cmd)) {
                $tce->start(array(), $this->TCEmain_cmd);
                $tce->process_cmdmap();
                if (count($tce->errorLog)) {
                    debug($tce->errorLog, 'TCEmain localization errors:');
                }
            }
            // Before remapping
            if (TYPO3_DLOG) {
                GeneralUtility::sysLog(__FILE__ . ': ' . __LINE__ . ': TCEmain_data before remapping: ' . GeneralUtility::arrayToLogString($TCEmain_data),
                    'l10nmgr');
            }
            // Remapping those elements which are new:
            $this->lastTCEMAINCommandsCount = 0;
            foreach ($TCEmain_data as $table => $items) {
                foreach ($TCEmain_data[$table] as $TuidString => $fields) {
                    if ($table === 'sys_file_reference' && $fields['tablenames'] === 'pages') {
                        $parent = BackendUtility::getRecordRaw('pages_language_overlay',
                            'pid = ' . (int)$element['uid_foreign'] . ' AND deleted = 0 AND sys_language_uid = ' . (int)$Tlang);
                        if ($parent['uid']) {
                            $fields['tablenames'] = 'pages_language_overlay';
                            $fields['uid_foreign'] = $parent['uid'];
                        }
                    }
                    list($Tuid, $Tlang, $TdefRecord) = explode('/', $TuidString);
                    $this->lastTCEMAINCommandsCount++;
                    if ($Tuid === 'NEW') {
                        if ($tce->copyMappingArray_merged[$table][$TdefRecord]) {
                            $TCEmain_data[$table][BackendUtility::wsMapId($table,
                                $tce->copyMappingArray_merged[$table][$TdefRecord])] = $fields;
                        } else {
                            GeneralUtility::sysLog(__FILE__ . ': ' . __LINE__ . ': Record "' . $table . ':' . $TdefRecord . '" was NOT localized as it should have been!',
                                'l10nmgr');
                        }
                        unset($TCEmain_data[$table][$TuidString]);
                    }
                }
            }
            // After remapping
            if (TYPO3_DLOG) {
                GeneralUtility::sysLog(__FILE__ . ': ' . __LINE__ . ': TCEmain_data after remapping: ' . GeneralUtility::arrayToLogString($TCEmain_data),
                    'l10nmgr');
            }
            // Now, submitting translation data:
            /** @var DataHandler $tce */
            $tce = GeneralUtility::makeInstance(DataHandler::class);
            if ($this->extensionConfiguration['enable_neverHideAtCopy'] == 1) {
                $tce->neverHideAtCopy = true;
            }
            $tce->dontProcessTransformations = true;
            $tce->isImporting = true;
            foreach (array_chunk($TCEmain_data, 100, true) as $dataPart) {
                $tce->start($dataPart,
                    array()); // check has been done previously that there is a backend user which is Admin and also in live workspace
                $tce->process_datamap();
            }
            self::$targetLanguageID = null;
            if (count($tce->errorLog)) {
                GeneralUtility::sysLog(__FILE__ . ': ' . __LINE__ . ': TCEmain update errors: ' . GeneralUtility::arrayToLogString($tce->errorLog),
                    'l10nmgr');
            }
            if (count($tce->autoVersionIdMap) && count($_flexFormDiffArray)) {
                if (TYPO3_DLOG) {
                    GeneralUtility::sysLog(__FILE__ . ': ' . __LINE__ . ': flexFormDiffArry: ' . GeneralUtility::arrayToLogString($this->flexFormDiffArray),
                        'l10nmgr');
                }
                foreach ($_flexFormDiffArray as $key => $value) {
                    list($Ttable, $Tuid, $Trest) = explode(':', $key, 3);
                    if ($tce->autoVersionIdMap[$Ttable][$Tuid]) {
                        $_flexFormDiffArray[$Ttable . ':' . $tce->autoVersionIdMap[$Ttable][$Tuid] . ':' . $Trest] = $_flexFormDiffArray[$key];
                        unset($_flexFormDiffArray[$key]);
                    }
                }
                if (TYPO3_DLOG) {
                    GeneralUtility::sysLog(__FILE__ . ': ' . __LINE__ . ': autoVersionIdMap: ' . $tce->autoVersionIdMap,
                        'l10nmgr');
                    GeneralUtility::sysLog(__FILE__ . ': ' . __LINE__ . ': _flexFormDiffArray: ' . GeneralUtility::arrayToLogString($_flexFormDiffArray),
                        'l10nmgr');
                }
            }
            // Should be empty now - or there were more information in the incoming array than there should be!
            if (count($inputArray)) {
                debug($inputArray, 'These fields were ignored since they were not in the configuration 2:');
            }
            return $_flexFormDiffArray;
        } else {
            return false;
        }
    }

    /**
     * @param $element
     * @param $Tlang
     * @param $parentField
     * @param $childrenField
     */
    protected function recursivelyCheckForRelationParents($element, $Tlang, $parentField, $childrenField)
    {
        global $TCA;
        $this->depthCounter++;
        if ($this->depthCounter < 100 && !isset($this->checkedParentRecords[$parentField][$element['uid']])) {
            $this->checkedParentRecords[$parentField][$element['uid']] = true;
            $translatedParent = array();
            if ($element[$parentField] > 0) {
                $translatedParent = BackendUtility::getRecordRaw('tt_content',
                    $TCA['tt_content']['ctrl']['transOrigPointerField'] . ' = ' . (int)$element[$parentField] .
                    ' AND deleted = 0 AND sys_language_uid = ' . (int)$Tlang
                );
            }
            if ($translatedParent['uid'] > 0) {
                $this->TCEmain_cmd['tt_content'][$translatedParent['uid']]['inlineLocalizeSynchronize'] = $childrenField . ',localize';
            } else {
                if ($element[$parentField] > 0) {
                    $parent = BackendUtility::getRecordRaw('tt_content',
                        'uid = ' . (int)$element[$parentField] . ' AND deleted = 0');
                    $this->recursivelyCheckForRelationParents($parent, $Tlang, $parentField, $childrenField);
                } else {
                    $this->TCEmain_cmd['tt_content'][$element['uid']]['localize'] = $Tlang;
                }
            }
        }
    }
}