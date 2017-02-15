<?php
namespace Localizationteam\L10nmgr\View;

/***************************************************************
 * Copyright notice
 * (c) 2006 Kasper Skårhøj <kasperYYYY@typo3.com>
 *
 * @author Fabian Seltmann <fs@marketing-factory.de>
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
use Localizationteam\L10nmgr\Model\L10nConfiguration;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageRendererResolver;
use TYPO3\CMS\Core\Utility\DiffUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;

/**
 * Abstract class for all export views
 *
 * @author Fabian Seltmann <fs@marketing-factory.de>
 * @package TYPO3
 * @subpackage tx_l10nmgr
 **/
abstract class AbstractExportView
{
    /**
     * @var string
     */
    var $filename = '';
    /**
     * @var L10nConfiguration The language configuration object
     */
    protected $l10ncfgObj;
    /**
     *flags for controlling the fields which should render in the output:
     */
    /**
     * @var integer The sys_language_uid of language to export
     */
    protected $sysLang;
    /**
     * @var bool
     */
    protected $modeOnlyChanged = false;
    /**
     * @var bool
     */
    protected $modeNoHidden = false;
    /**
     * @var bool
     */
    protected $modeOnlyNew = false;
    /**
     * @var int
     */
    protected $exportType;
    /**
     * @var LanguageService
     */
    protected $languageService;
    /**
     * @var array List of messages issued during rendering
     */
    protected $internalMessages = array();
    /**
     * @var int
     */
    protected $forcedSourceLanguage;

    /**
     * AbstractExportView constructor.
     * @param L10nConfiguration $l10ncfgObj
     * @param int $sysLang
     */
    public function __construct($l10ncfgObj, $sysLang)
    {
        $this->sysLang = $sysLang;
        $this->l10ncfgObj = $l10ncfgObj;
    }

    /**
     * @return int
     */
    public function getExportType()
    {
        return $this->exportType;
    }

    /**
     * @return void
     */
    public function setModeNoHidden()
    {
        $this->modeNoHidden = true;
    }

    /**
     * @return void
     */
    public function setModeOnlyChanged()
    {
        $this->modeOnlyChanged = true;
    }

    /**
     * @return void
     */
    public function setModeOnlyNew()
    {
        $this->modeOnlyNew = true;
    }

    /**
     * Saves the information of the export in the database table 'tx_l10nmgr_sava_data'
     *
     * @return bool|\mysqli_result|object resource Handle to the database query
     */
    public function saveExportInformation()
    {
        // get current date
        $date = time();
        // query to insert the data in the database
        $field_values = array(
            'source_lang' => (int)$this->forcedSourceLanguage ? (int)$this->forcedSourceLanguage : 0,
            'translation_lang' => (int)$this->sysLang,
            'crdate' => $date,
            'tstamp' => $date,
            'l10ncfg_id' => (int)$this->l10ncfgObj->getData('uid'),
            'pid' => (int)$this->l10ncfgObj->getData('pid'),
            'tablelist' => (string)$this->l10ncfgObj->getData('tablelist'),
            'title' => (string)$this->l10ncfgObj->getData('title'),
            'cruser_id' => (int)$this->l10ncfgObj->getData('cruser_id'),
            'filename' => (string)$this->getFilename(),
            'exportType' => (int)$this->exportType
        );
        $res = $this->getDatabaseConnection()->exec_INSERTquery(
            'tx_l10nmgr_exportdata',
            $field_values,
            array('source_lang', 'translation_lang', 'crdate', 'tstamp', 'l10ncfg_id', 'pid', 'cruser_id', 'exportType')
        );
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['l10nmgr']['exportView'])) {
            $params = array(
                'uid' => $this->getDatabaseConnection()->sql_insert_id(),
                'data' => $field_values
            );
            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['l10nmgr']['exportView'] as $classData) {
                $postSaveProcessor = GeneralUtility::getUserObj($classData);
                if ($postSaveProcessor instanceof PostSaveInterface) {
                    $postSaveProcessor->postExportAction($params);
                }
            }
        }
        return $res;
    }

    /**
     * Get filename
     *
     * @return string File name
     */
    public function getFilename()
    {
        if (empty($this->filename)) {
            $this->setFilename();
        }
        return $this->filename;
    }

    /**
     * Set filename
     *
     * @return void
     */
    public function setFilename()
    {
        $sourceLang = '';
        $targetLang = '';
        if ($this->exportType == '0') {
            $fileType = 'excel';
        } else {
            $fileType = 'catxml';
        }
        if ($this->l10ncfgObj->getData('sourceLangStaticId') && ExtensionManagementUtility::isLoaded('static_info_tables')) {
            $staticLangArr = BackendUtility::getRecord('static_languages',
                $this->l10ncfgObj->getData('sourceLangStaticId'), 'lg_iso_2');
        }
        if ($this->sysLang && ExtensionManagementUtility::isLoaded('static_info_tables')) {
            $targetLangSysLangArr = BackendUtility::getRecord('sys_language', $this->sysLang);
            $targetLangArr = BackendUtility::getRecord('static_languages',
                $targetLangSysLangArr['static_lang_isocode']);
        }
        // Set sourceLang for filename
        if (isset($staticLangArr['lg_iso_2']) && !empty($staticLangArr['lg_iso_2'])) {
            $sourceLang = $staticLangArr['lg_iso_2'];
        }
        // Use locale for targetLang in filename if available
        if (isset($targetLangArr['lg_collate_locale']) && !empty($targetLangArr['lg_collate_locale'])) {
            $targetLang = $targetLangArr['lg_collate_locale'];
            // Use two letter ISO code if locale is not available
        } elseif (isset($targetLangArr['lg_iso_2']) && !empty($targetLangArr['lg_iso_2'])) {
            $targetLang = $targetLangArr['lg_iso_2'];
        }
        $fileNamePrefix = (trim($this->l10ncfgObj->getData('filenameprefix'))) ? $this->l10ncfgObj->getData('filenameprefix') . '_' . $fileType : $fileType;
        // Setting filename:
        $filename = $fileNamePrefix . '_' . $sourceLang . '_to_' . $targetLang . '_' . date('dmy-His') . '.xml';
        $this->filename = $filename;
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
     * Checks if an export exists
     *
     * @return boolean
     */
    public function checkExports()
    {
        $res = $this->getDatabaseConnection()->exec_SELECTquery('l10ncfg_id,exportType,translation_lang',
            'tx_l10nmgr_exportdata',
            'l10ncfg_id =' . (int)$this->l10ncfgObj->getData('uid') . ' AND exportType = ' . $this->exportType . ' AND translation_lang = ' . $this->sysLang);
        if (!$this->getDatabaseConnection()->sql_error()) {
            $numRows = $this->getDatabaseConnection()->sql_num_rows($res);
        } else {
            $numRows = 0;
        }
        if ($numRows > 0) {
            $ret = false;
        } else {
            $ret = true;
        }
        return $ret;
    }

    /**
     * Renders a list of saved exports as HTML table.
     *
     * @return string HTML table
     */
    public function renderExports()
    {
        $content = array();
        $exports = $this->fetchExports();
        foreach ($exports AS $export => $exportData) {
            $content[$export] = sprintf('
<tr class="db_list_normal">
	<td>%s</td>
	<td>%s</td>
	<td>%s</td>
	<td>%s</td>
	<td>%s</td>
</tr>', BackendUtility::datetime($exportData['crdate']), $exportData['l10ncfg_id'], $exportData['exportType'],
                $exportData['translation_lang'], sprintf('<a href="%suploads/tx_l10nmgr/jobs/out/%s">%s</a>',
                    GeneralUtility::getIndpEnv('TYPO3_SITE_URL'), $exportData['filename'], $exportData['filename']));
        }
        $out = sprintf('
<table class="table table-striped table-hover">
	<thead>
	<tr class="t3-row-header">
	<th>%s</th>
	<th>%s</th>
	<th>%s</th>
	<th>%s</th>
	<th>%s</th>
	</tr>
	</thead>
	<tbody>
%s
	</tbody>
</table>', $this->getLanguageService()->getLL('export.overview.date.label'),
            $this->getLanguageService()->getLL('export.overview.configuration.label'),
            $this->getLanguageService()->getLL('export.overview.type.label'),
            $this->getLanguageService()->getLL('export.overview.targetlanguage.label'),
            $this->getLanguageService()->getLL('export.overview.filename.label'), implode(chr(10), $content));
        return $out;
    }

    /**
     * Fetches saved exports based on configuration, export format and target language.
     *
     * @author Andreas Otto <andreas.otto@dkd.de>
     * @return array Information about exports.
     */
    protected function fetchExports()
    {
        $exports = array();
        $res = $this->getDatabaseConnection()->exec_SELECTgetRows('crdate,l10ncfg_id,exportType,translation_lang,filename',
            'tx_l10nmgr_exportdata',
            'l10ncfg_id = ' . (int)$this->l10ncfgObj->getData('uid') . ' AND exportType = ' . $this->exportType . ' AND translation_lang = ' . $this->sysLang,
            '', 'crdate DESC');
        if (is_array($res)) {
            $exports = $res;
        }
        return $exports;
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
     * Renders a list of saved exports as text.
     *
     * @return string text
     */
    public function renderExportsCli()
    {
        $content = array();
        $exports = $this->fetchExports();
        foreach ($exports AS $export => $exportData) {
            $content[$export] = sprintf('%-15s%-15s%-15s%-15s%s', BackendUtility::datetime($exportData['crdate']),
                $exportData['l10ncfg_id'], $exportData['exportType'], $exportData['translation_lang'],
                sprintf('%suploads/tx_l10nmgr/jobs/out/%s', PATH_site, $exportData['filename']));
        }
        $out = sprintf('%-15s%-15s%-15s%-15s%s%s%s', $this->getLanguageService()->getLL('export.overview.date.label'),
            $this->getLanguageService()->getLL('export.overview.configuration.label'),
            $this->getLanguageService()->getLL('export.overview.type.label'),
            $this->getLanguageService()->getLL('export.overview.targetlanguage.label'),
            $this->getLanguageService()->getLL('export.overview.filename.label'), LF,
            implode(LF, $content));
        return $out;
    }

    /**
     * Saves the exported files to the folder /uploads/tx_l10nmgr/jobs/out/
     *
     * @param string $fileContent The content to save to file
     *
     * @return string $fileExportName The complete filename
     */
    public function saveExportFile($fileContent)
    {
        $fileExportName = 'uploads/tx_l10nmgr/jobs/out/' . $this->getFilename();
        GeneralUtility::writeFile(PATH_site . $fileExportName, $fileContent);
        return $fileExportName;
    }

    /**
     * Diff-compare markup
     *
     * @param string $old Old content
     * @param string $new New content
     *
     * @return string Marked up string.
     */
    public function diffCMP($old, $new)
    {
        // Creates diff-result
        /** @var DiffUtility $t3lib_diff_Obj */
        $t3lib_diff_Obj = GeneralUtility::makeInstance(DiffUtility::class);
        return $t3lib_diff_Obj->makeDiffDisplay($old, $new);
    }

    /**
     * Renders internal messages as flash message.
     * If the export was successful, check if there were any internal warnings.
     * If yes, display them below the success message.
     *
     * @param string $status Flag which indicates if the export was successful.
     *
     * @return string Rendered flash message or empty string if there are no messages.
     */
    public function renderInternalMessagesAsFlashMessage($status)
    {
        $ret = '';
        if ($status == FlashMessage::OK) {
            $internalMessages = $this->getMessages();
            if (count($internalMessages) > 0) {
                $messageBody = '';
                foreach ($internalMessages as $messageInformation) {
                    $messageBody .= $messageInformation['message'] . ' (' . $messageInformation['key'] . ')<br />';
                }
                /** @var FlashMessage $flashMessage */
                $flashMessage = GeneralUtility::makeInstance(FlashMessage::class, $messageBody,
                    $this->getLanguageService()->getLL('export.ftp.warnings'), FlashMessage::WARNING);
                $ret .= GeneralUtility::makeInstance(FlashMessageRendererResolver::class)
                    ->resolve()
                    ->render([$flashMessage]);
            }
        }
        return $ret;
    }

    /**
     * Returns the list of internal messages
     *
     * @return array List of messages
     */
    public function getMessages()
    {
        return $this->internalMessages;
    }

    /**
     * Store a message in the internal queue
     * Note: this method is protected. Messages should not be set from the outside.
     *
     * @param string $message Text of the message
     * @param string $key Key identifying the element where the problem happened
     *
     * @return void
     */
    protected function setInternalMessage($message, $key)
    {
        $this->internalMessages[] = array(
            'message' => $message,
            'key' => $key
        );
    }
}