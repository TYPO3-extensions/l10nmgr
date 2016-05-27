<?php
namespace Localizationteam\L10nmgr\Controller\Cm1;

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
use Localizationteam\L10nmgr\View\AbstractExportView;
use TYPO3\CMS\Backend\Module\BaseScriptClass;
use TYPO3\CMS\Backend\Configuration\TranslationConfigurationProvider;
use TYPO3\CMS\Backend\Template\DocumentTemplate;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\DiffUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Localizationteam\L10nmgr\Model\CatXmlImportManager;
use Localizationteam\L10nmgr\Model\L10nBaseService;
use Localizationteam\L10nmgr\Model\L10nConfiguration;
use Localizationteam\L10nmgr\Model\MkPreviewLinkService;
use Localizationteam\L10nmgr\Model\TranslationData;
use Localizationteam\L10nmgr\Model\TranslationDataFactory;
use Localizationteam\L10nmgr\View\CatXmlView;
use Localizationteam\L10nmgr\View\ExcelXmlView;
use Localizationteam\L10nmgr\View\L10nConfigurationDetailView;
use Localizationteam\L10nmgr\View\L10nHtmlListView;


/**
 * l10nmgr module cm1
 *
 * @author  Kasper Skårhøj <kasperYYYY@typo3.com>
 * @author  Daniel Zielinski <d.zielinski@l10ntech.de>
 * @author  Daniel Pötzinger <poetzinger@aoemedia.de>
 * @author  Fabian Seltmann <fs@marketing-factory.de>
 * @author  Andreas Otto <andreas.otto@dkd.de>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   68: class tx_l10nmgr_cm1 extends t3lib_SCbase
 *   75:     function menuConfig()
 *   89:     function main()
 *  101:     function jumpToUrl(URL)
 *  142:     function printContent()
 *  154:     function moduleContent($l10ncfg)
 *  203:     function render_HTMLOverview($accum)
 *  265:     function diffCMP($old, $new)
 *  278:     function submitContent($accum,$inputArray)
 *  376:     function getAccumulated($tree, $l10ncfg, $sysLang)
 *
 * TOTAL FUNCTIONS: 9
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

/**
 * Translation management tool
 *
 * @author  Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage tx_l10nmgr
 */

class Cm1 extends BaseScriptClass
{

    var $flexFormDiffArray = array();
    /**
     * @var  integer    Default language to export
     */
    var $sysLanguage = '0'; // Internal
    /**
     * @var array Extension configuration
     */
    protected $lConf = array();

	/**
	 * Initializes the Module
	 *
	 * @return  void
	 */
	public function init()
	{
		$this->MCONF['name'] = 'xMOD_txl10nmgrCM1';
		$GLOBALS['BE_USER']->modAccess($this->MCONF, 1);
		$GLOBALS['LANG']->includeLLFile("EXT:l10nmgr/Resources/Private/Language/Modules/Cm1/locallang.xlf");
		parent::init();
	}

	/**
     * Adds items to the ->MOD_MENU array. Used for the function menu selector.
     *
     * @return  void
     */
    function menuConfig()
    {
        $this->loadExtConf();
        $this->MOD_MENU = Array(
            'action' => array(
                '' => $GLOBALS['LANG']->getLL('general.action.blank.title'),
                'link' => $GLOBALS['LANG']->getLL('general.action.edit.link.title'),
                'inlineEdit' => $GLOBALS['LANG']->getLL('general.action.edit.inline.title'),
                'export_excel' => $GLOBALS['LANG']->getLL('general.action.export.excel.title'),
                'export_xml' => $GLOBALS['LANG']->getLL('general.action.export.xml.title'),
            ),
            'lang' => array(),
            'onlyChangedContent' => '',
            'noHidden' => ''
        );

        // Load system languages into menu:
        /** @var $t8Tools TranslationConfigurationProvider */
        $t8Tools = GeneralUtility::makeInstance(TranslationConfigurationProvider::class);
        $sysL = $t8Tools->getSystemLanguages();

        foreach ($sysL as $sL) {
            if ($sL['uid'] > 0 && $GLOBALS['BE_USER']->checkLanguageAccess($sL['uid'])) {
                if ($this->lConf['enable_hidden_languages'] == 1) {
                    $this->MOD_MENU['lang'][$sL['uid']] = $sL['title'];
                } elseif ($sL['hidden'] == 0) {
                    $this->MOD_MENU['lang'][$sL['uid']] = $sL['title'];
                }
            }
        }

        parent::menuConfig();
    }

    /**
     * The function loadExtConf loads the extension configuration.
     *
     * @return void
     *
     */
    function loadExtConf()
    {
        // Load the configuration
        $this->lConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['l10nmgr']);
    }

    /**
     * Main function of the module. Write the content to
     *
     * @return  void
     */
    public function main()
    {
        // Get language to export/import
        $this->sysLanguage = $this->MOD_SETTINGS["lang"];

        // Draw the header.
        $this->doc = GeneralUtility::makeInstance(DocumentTemplate::class);
        $this->doc->backPath = $GLOBALS['BACK_PATH'];
        $this->doc->setModuleTemplate('EXT:l10nmgr/Resources/Private/Templates/Cm1Template.html');
        $this->doc->form = '<form action="" method="post" enctype="' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['form_enctype'] . '">';

        // JavaScript
        $this->doc->JScode = '
			<script language="javascript" type="text/javascript">
				script_ended = 0;
				function jumpToUrl(URL)	{
					document.location = URL;
				}
			</script>
			<script language="javascript" type="text/javascript" src="' . GeneralUtility::resolveBackPath($GLOBALS['BACK_PATH'] . \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('l10nmgr') . 'Resources/Public/Contrib/tabs.js') . '"></script>
			<link rel="stylesheet" type="text/css" href="' . GeneralUtility::resolveBackPath($GLOBALS['BACK_PATH'] . \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('l10nmgr') . 'Resources/Public/Contrib/tabs.css') . '" />';

        // Find l10n configuration record
        /** @var $l10ncfgObj L10nConfiguration */
        $l10ncfgObj = GeneralUtility::makeInstance(L10nConfiguration::class);
        $l10ncfgObj->load($this->id);

        if ($l10ncfgObj->isLoaded()) {

            // Setting page id
            $this->id = $l10ncfgObj->getData('pid');
            $this->perms_clause = $GLOBALS['BE_USER']->getPagePermsClause(1);
            $this->pageinfo = BackendUtility::readPageAccess($this->id, $this->perms_clause);
            $access = is_array($this->pageinfo) ? 1 : 0;
            if ($this->id && $access) {

                // Header:
//				$this->content.=$this->doc->startPage($GLOBALS['LANG']->getLL('general.title'));
//				$this->content.=$this->doc->header($GLOBALS['LANG']->getLL('general.title'));

                // Create and render view to show details for the current l10nmgrcfg
                /** @var $l10nmgrconfigurationView L10nConfigurationDetailView */
                $l10nmgrconfigurationView = GeneralUtility::makeInstance(L10nConfigurationDetailView::class, $l10ncfgObj, $this->doc);
                $this->content .= $this->doc->section('', $l10nmgrconfigurationView->render());

                $this->content .= $this->doc->divider(15);
                $this->content .= $this->doc->section($GLOBALS['LANG']->getLL('general.export.choose.action.title'),
                    BackendUtility::getFuncMenu($l10ncfgObj->getId(), "SET[lang]", $this->sysLanguage,
                        $this->MOD_MENU["lang"], '',
                        '&srcPID=' . rawurlencode(GeneralUtility::_GET('srcPID'))) .
                    BackendUtility::getFuncMenu($l10ncfgObj->getId(), "SET[action]", $this->MOD_SETTINGS["action"],
                        $this->MOD_MENU["action"], '',
                        '&srcPID=' . rawurlencode(GeneralUtility::_GET('srcPID'))) .
                    BackendUtility::getFuncCheck($l10ncfgObj->getId(), "SET[onlyChangedContent]",
                        $this->MOD_SETTINGS["onlyChangedContent"], '',
                        '&srcPID=' . rawurlencode(GeneralUtility::_GET('srcPID'))) . ' ' . $GLOBALS['LANG']->getLL('export.xml.new.title') .
                    BackendUtility::getFuncCheck($l10ncfgObj->getId(), "SET[noHidden]", $this->MOD_SETTINGS["noHidden"],
                        '',
                        '&srcPID=' . rawurlencode(GeneralUtility::_GET('srcPID'))) . ' ' . $GLOBALS['LANG']->getLL('export.xml.noHidden.title') . '</br>'
                );

                // Render content:
                if (!count($this->MOD_MENU['lang'])) {
                    $this->content .= $this->doc->section('ERROR', $GLOBALS['LANG']->getLL('general.access.error.title'));
                } else {
                    $this->moduleContent($l10ncfgObj);
                }
            }
        }

        $this->content .= $this->doc->spacer(10);

        $markers['CONTENT'] = $this->content;

        // Build the <body> for the module
        $docHeaderButtons = $this->getButtons();
        $this->content = $this->doc->startPage($GLOBALS['LANG']->getLL('general.title'));
        $this->content .= $this->doc->moduleBody($this->pageinfo, $docHeaderButtons, $markers);
        $this->content .= $this->doc->endPage();
        $this->content = $this->doc->insertStylesAndJS($this->content);
    }

    /**
     * Creating module content
     *
     * @param   array    Localization Configuration record
     * @return  void
     */
    function moduleContent($l10ncfgObj)
    {
        global $LANG, $BE_USER;

        switch ($this->MOD_SETTINGS["action"]) {
            case 'inlineEdit':
            case 'link':
                /** @var $htmlListView L10nHTMLListView */
                $htmlListView = GeneralUtility::makeInstance(L10nHtmlListView::class,
                    $l10ncfgObj, $this->sysLanguage);
                $subheader = $GLOBALS['LANG']->getLL('inlineEdit');
                $subcontent = '';

                if ($this->MOD_SETTINGS["action"] == 'inlineEdit') {
                    $subheader = $GLOBALS['LANG']->getLL('link');
                    $subcontent = $this->inlineEditAction($l10ncfgObj);
                    $htmlListView->setModeWithInlineEdit();
                }
                // Render the module content (for all modes):
                //*******************************************

                if ($this->MOD_SETTINGS["onlyChangedContent"]) {
                    $htmlListView->setModeOnlyChanged();
                }
                if ($this->MOD_SETTINGS["noHidden"]) {
                    $htmlListView->setModeNoHidden();
                }
                if ($this->MOD_SETTINGS["action"] == 'link') {
                    $htmlListView->setModeShowEditLinks();
                }
                $subcontent .= $htmlListView->renderOverview();
                break;

            case 'export_excel':
                $subheader = $GLOBALS['LANG']->getLL('export_excel');
                $subcontent = $this->excelExportImportAction($l10ncfgObj);
                break;

            case 'export_xml': // XML import/export
                $prefs['utf8'] = GeneralUtility::_POST('check_utf8');
                $prefs['noxmlcheck'] = GeneralUtility::_POST('no_check_xml');
                $BE_USER->pushModuleData('l10nmgr/cm1/prefs', $prefs);

                $subheader = $GLOBALS['LANG']->getLL('export_xml');
                $subcontent = $this->catXMLExportImportAction($l10ncfgObj);
                break;

            DEFAULT: // Default display:
                $subcontent = '<input type="submit" value="' . $GLOBALS['LANG']->getLL('general.action.refresh.button.title') . '" name="_" />';
                break;
        } //switch block

        $this->content .= $this->doc->section($subheader, $subcontent);
    }

    function inlineEditAction($l10ncfgObj)
    {

        /** @var $service L10nBaseService */
        $service = GeneralUtility::makeInstance(L10nBaseService::class);
        $info = '';
        // Buttons:
        $info .= '<input type="submit" value="' . $GLOBALS['LANG']->getLL('general.action.save.button.title') . '" name="saveInline" onclick="return confirm(\'' . $GLOBALS['LANG']->getLL('inlineedit.save.alert.title') . '\');" />';
        $info .= '<input type="submit" value="' . $GLOBALS['LANG']->getLL('general.action.cancel.button.title') . '" name="_" onclick="return confirm(\'' . $GLOBALS['LANG']->getLL('inlineedit.cancel.alert.title') . '\');" />';

        //simple init of translation object:
        /** @var $translationData TranslationData */
        $translationData = GeneralUtility::makeInstance(TranslationData::class);
        $translationData->setTranslationData(GeneralUtility::_POST('translation'));
        $translationData->setLanguage($this->sysLanguage);

        // See, if incoming translation is available, if so, submit it
        if (GeneralUtility::_POST('saveInline')) {
            $service->saveTranslation($l10ncfgObj, $translationData);
        }

        return $info;
    }

    function excelExportImportAction($l10ncfgObj)
    {
        global $LANG, $BACK_PATH;

        /** @var $service L10nBaseService */
        $service = GeneralUtility::makeInstance(L10nBaseService::class);
        if (GeneralUtility::_POST('import_asdefaultlanguage') == '1') {
            $service->setImportAsDefaultLanguage(true);
        }
        // Buttons:
        $_selectOptions = array('0' => '-default-');
        $_selectOptions = $_selectOptions + $this->MOD_MENU["lang"];
        $info = '<br /><br />';
        $info .= '<input type="checkbox" value="1" checked="checked" name="check_exports" /> ' . $GLOBALS['LANG']->getLL('export.xml.check_exports.title') . '<br />';
        $info .= '<input type="checkbox" value="1" name="import_asdefaultlanguage" /> ' . $GLOBALS['LANG']->getLL('import.xml.asdefaultlanguage.title') . '<br />';
        $info .= $GLOBALS['LANG']->getLL('export.xml.source-language.title') . $this->_getSelectField("export_xml_forcepreviewlanguage",
                '0', $_selectOptions) . '<br /><br />';
        $info .= '<input type="file" size="60" name="uploaded_import_file" /><br /><br />';
        $info .= '<input type="submit" value="' . $GLOBALS['LANG']->getLL('general.action.refresh.button.title') . '" name="_" />';
        $info .= '<input type="submit" value="' . $GLOBALS['LANG']->getLL('general.action.export.xml.button.title') . '" name="export_excel" />';
        $info .= '<input type="submit" value="' . $GLOBALS['LANG']->getLL('general.action.import.xml.button.title') . '" name="import_excel" />';

        // Read uploaded file:
        if (GeneralUtility::_POST('import_excel') && $_FILES['uploaded_import_file']['tmp_name'] && is_uploaded_file($_FILES['uploaded_import_file']['tmp_name'])) {
            $uploadedTempFile = GeneralUtility::upload_to_tempfile($_FILES['uploaded_import_file']['tmp_name']);

            /** @var  $factory TranslationDataFactory */
            $factory = GeneralUtility::makeInstance(TranslationDataFactory::class);
            //TODO: catch exeption
            $translationData = $factory->getTranslationDataFromExcelXMLFile($uploadedTempFile);
            $translationData->setLanguage($this->sysLanguage);

            GeneralUtility::unlink_tempfile($uploadedTempFile);

            $service->saveTranslation($l10ncfgObj, $translationData);

            $info .= '<br/><br/>' . $this->doc->icons(1) . $GLOBALS['LANG']->getLL('import.success.message') . '<br/><br/>';
        }

        // If export of XML is asked for, do that (this will exit and push a file for download)
        if (GeneralUtility::_POST('export_excel')) {

            // Render the XML
            /** @var $viewClass ExcelXmlView */
            $viewClass = GeneralUtility::makeInstance(ExcelXmlView::class, $l10ncfgObj,
                $this->sysLanguage);
            $export_xml_forcepreviewlanguage = (int)GeneralUtility::_POST('export_xml_forcepreviewlanguage');
            if ($export_xml_forcepreviewlanguage > 0) {
                $viewClass->setForcedSourceLanguage($export_xml_forcepreviewlanguage);
            }
            if ($this->MOD_SETTINGS['onlyChangedContent']) {
                $viewClass->setModeOnlyChanged();
            }
            if ($this->MOD_SETTINGS['noHidden']) {
                $viewClass->setModeNoHidden();
            }

            //Check the export
            if ((GeneralUtility::_POST('check_exports') == '1') && ($viewClass->checkExports() == false)) {
                /** @var $flashMessage FlashMessage */
                $flashMessage = GeneralUtility::makeInstance(FlashMessage::class,
                    $GLOBALS['LANG']->getLL('export.process.duplicate.message'), $GLOBALS['LANG']->getLL('export.process.duplicate.title'),
                    FlashMessage::INFO);
                $info .= $flashMessage->render();
                $info .= $viewClass->renderExports();
            } else {
                try {
                    $filename = $this->downloadXML($viewClass);
                    // Prepare a success message for display
                    $link = sprintf('<a href="%s" target="_blank">%s</a>',
                        GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . $filename, $filename);
                    $title = $GLOBALS['LANG']->getLL('export.download.success');
                    $message = sprintf($GLOBALS['LANG']->getLL('export.download.success.detail'), $link);
                    $status = FlashMessage::OK;
                } catch (Exception $e) {
                    // Prepare an error message for display
                    $title = $GLOBALS['LANG']->getLL('export.download.error');
                    $message = $e->getMessage() . ' (' . $e->getCode() . ')';
                    $status = FlashMessage::ERROR;
                }
                /** @var $flashMessage FlashMessage */
                $flashMessage = GeneralUtility::makeInstance(FlashMessage::class, $message,
                    $title, $status);
                $info .= $flashMessage->render();
                $info .= $viewClass->renderInternalMessagesAsFlashMessage($status);

                $viewClass->saveExportInformation();
            }
        }

        return $info;
    }

    function _getSelectField($elementName, $currentValue, $menuItems)
    {
        $options = array();

        foreach ($menuItems as $value => $label) {
            $options[] = '<option value="' . htmlspecialchars($value) . '"' . (!strcmp($currentValue,
                    $value) ? ' selected="selected"' : '') . '>' .
                GeneralUtility::deHSCentities(htmlspecialchars($label)) .
                '</option>';
        }

        if (count($options) > 0) {
            return '
				<select name="' . $elementName . '" >
					' . implode('
					', $options) . '
				</select>
						';
        }
    }

    /**
     * Sends download header and calls render method of the view.
     * Used for excelXML and CATXML.
     *
     * @param AbstractExportView $xmlView Object for generating the XML export
     * @return string $filename
     */
    protected function downloadXML(AbstractExportView $xmlView)
    {
        // Save content to the disk and get the file name
        $filename = $xmlView->render();

        return $filename;
    }

    function catXMLExportImportAction($l10ncfgObj)
    {
        global $LANG, $BACK_PATH, $BE_USER;
        $allowedSettingFiles = array(
            'across' => 'acrossL10nmgrConfig.dst',
            'dejaVu' => 'dejaVuL10nmgrConfig.dvflt',
            'memoq' => 'memoQ.mqres',
            'memoq2013-2014' => 'XMLConverter_TYPO3_l10nmgr_v3.6.mqres',
            'transit' => 'StarTransit_XML_UTF_TYPO3.FFD',
            'sdltrados2007' => 'SDLTradosTagEditor.ini',
            'sdltrados2009' => 'TYPO3_l10nmgr.sdlfiletype',
            'sdltrados2011-2014' => 'TYPO3_LocalizationManager_v3.6.free.sdlftsettings',
            'sdlpassolo' => 'SDLPassolo.xfg',
        );

        /** @var $service L10nBaseService */
        $service = GeneralUtility::makeInstance(L10nBaseService::class);

        $info = '<br/>';
        $info .= '<input type="submit" value="' . $GLOBALS['LANG']->getLL('general.action.refresh.button.title') . '" name="_" /><br /><br/>';

        $info .= '<div id="ddtabs" class="basictab" style="border:0px solid gray;margin:0px;">
					<ul style="border:0px solid #999999; ">
					<li><a onClick="expandcontent(\'sc1\', this)" style="margin:0px;">' . $GLOBALS['LANG']->getLL('export.xml.headline.title') . '</a></li>
					<li><a onClick="expandcontent(\'sc2\', this)" style="margin:0px;">' . $GLOBALS['LANG']->getLL('import.xml.headline.title') . '</a></li>
					<li><a onClick="expandcontent(\'sc3\', this)" style="margin:0px;">' . $GLOBALS['LANG']->getLL('file.settings.downloads.title') . '</a></li>
					<li><a onClick="expandcontent(\'sc4\', this)" style="margin:0px;">' . $GLOBALS['LANG']->getLL('l10nmgr.documentation.title') . '</a></li>
				</ul></div>';

        $info .= '<div id="tabcontentcontainer" style="height:190px;border:1px solid gray;padding-right:5px;width:100%;">';

        $info .= '<div id="sc1" class="tabcontent">';
        //$info .= '<div id="sc1" class="tabcontent">';
        $_selectOptions = array('0' => '-default-');
        $_selectOptions = $_selectOptions + $this->MOD_MENU["lang"];
        $info .= '<input type="checkbox" value="1" name="check_exports" /> ' . $GLOBALS['LANG']->getLL('export.xml.check_exports.title') . '<br />';
        $info .= '<input type="checkbox" value="1" checked="checked" name="no_check_xml" /> ' . $GLOBALS['LANG']->getLL('export.xml.no_check_xml.title') . '<br />';
        $info .= '<input type="checkbox" value="1" name="check_utf8" /> ' . $GLOBALS['LANG']->getLL('export.xml.checkUtf8.title') . '<br />';
        $info .= $GLOBALS['LANG']->getLL('export.xml.source-language.title') . $this->_getSelectField("export_xml_forcepreviewlanguage",
                '0', $_selectOptions) . '<br />';
        // Add the option to send to FTP server, if FTP information is defined
        if (!empty($this->lConf['ftp_server']) && !empty($this->lConf['ftp_server_username']) && !empty($this->lConf['ftp_server_password'])) {
            $info .= '<input type="checkbox" value="1" name="ftp_upload" id="tx_l10nmgr_ftp_upload" /> <label for="tx_l10nmgr_ftp_upload">' . $GLOBALS['LANG']->getLL('export.xml.ftp.title') . '</label>';
        }
        $info .= '<br /><br/>';
        $info .= '<input type="submit" value="Export" name="export_xml" /><br /><br /><br/>';
        $info .= '</div>';
        $info .= '<div id="sc2" class="tabcontent">';
        $info .= '<input type="checkbox" value="1" name="make_preview_link" /> ' . $GLOBALS['LANG']->getLL('import.xml.make_preview_link.title') . '<br />';
        $info .= '<input type="checkbox" value="1" name="import_delL10N" /> ' . $GLOBALS['LANG']->getLL('import.xml.delL10N.title') . '<br />';
        $info .= '<input type="checkbox" value="1" name="import_asdefaultlanguage" /> ' . $GLOBALS['LANG']->getLL('import.xml.asdefaultlanguage.title') . '<br />';
        $info .= '<br />';
        $info .= '<input type="file" size="60" name="uploaded_import_file" /><br /><br /><input type="submit" value="Import" name="import_xml" /><br /><br /> ';
        $info .= '</div>';
        $info .= '<div id="sc3" class="tabcontent">';
        $info .= $this->doc->icons(1) .
            $GLOBALS['LANG']->getLL('file.settings.available.title');

        for (reset($allowedSettingFiles); list($settingId, $settingFileName) = each($allowedSettingFiles);) {
            $currentFile = GeneralUtility::resolveBackPath($BACK_PATH . \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('l10nmgr') . 'settings/' . $settingFileName);

            if (is_file($currentFile) && is_readable($currentFile)) {

                $size = GeneralUtility::formatSize((int)filesize($currentFile),
                    ' Bytes| KB| MB| GB');
                $info .= '<br/><a href="' . GeneralUtility::rawUrlEncodeFP($currentFile) . '" title="' . $GLOBALS['LANG']->getLL('file.settings.download.title') . '" target="_blank">' . $GLOBALS['LANG']->getLL('file.settings.' . $settingId . '.title') . ' (' . $size . ')' . '</a> ';
            }
        }
        $info .= '</div>';
        $info .= '<div id="sc4" class="tabcontent">';
        $info .= '<a href="/' . \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('l10nmgr') . 'doc/manual.sxw" target="_new">Download</a>';
        $info .= '</div>';
        $info .= '</div>';

        $actionInfo = '';
        // Read uploaded file:
        if (GeneralUtility::_POST('import_xml') && $_FILES['uploaded_import_file']['tmp_name'] && is_uploaded_file($_FILES['uploaded_import_file']['tmp_name'])) {
            $uploadedTempFile = GeneralUtility::upload_to_tempfile($_FILES['uploaded_import_file']['tmp_name']);
            /** @var $factory TranslationDataFactory */
            $factory = GeneralUtility::makeInstance(TranslationDataFactory::class);

            //print "<pre>";
            //var_dump($GLOBALS['BE_USER']->user);
            //print "</pre>";
            if (GeneralUtility::_POST('import_asdefaultlanguage') == '1') {
                $service->setImportAsDefaultLanguage(true);
            }
            if (GeneralUtility::_POST('import_oldformat') == '1') {
                //Support for the old Format of XML Import (without pageGrp element)
                $actionInfo .= $GLOBALS['LANG']->getLL('import.xml.old-format.message');
                $translationData = $factory->getTranslationDataFromOldFormatCATXMLFile($uploadedTempFile);
                $translationData->setLanguage($this->sysLanguage);
                $service->saveTranslation($l10ncfgObj, $translationData);
                $actionInfo .= '<br/><br/>' . $this->doc->icons(1) . 'Import done<br/><br/>(Command count:' . $service->lastTCEMAINCommandsCount . ')';
            } else {
                // Relevant processing of XML Import with the help of the Importmanager
                /** @var $importManager CatXmlImportManager */
                $importManager = GeneralUtility::makeInstance(CatXmlImportManager::class,
                    $uploadedTempFile,
                    $this->sysLanguage, $xmlString = "");
                if ($importManager->parseAndCheckXMLFile() === false) {
                    $actionInfo .= '<br/><br/>' . $this->doc->header($GLOBALS['LANG']->getLL('import.error.title')) . $importManager->getErrorMessages();
                } else {
                    if (GeneralUtility::_POST('import_delL10N') == '1') {
                        $actionInfo .= $GLOBALS['LANG']->getLL('import.xml.delL10N.message') . '<br/>';
                        $delCount = $importManager->delL10N($importManager->getDelL10NDataFromCATXMLNodes($importManager->xmlNodes));
                        $actionInfo .= sprintf($GLOBALS['LANG']->getLL('import.xml.delL10N.count.message'),
                                $delCount) . '<br/><br/>';
                    }
                    if (GeneralUtility::_POST('make_preview_link') == '1') {
                        $pageIds = $importManager->getPidsFromCATXMLNodes($importManager->xmlNodes);
                        $actionInfo .= '<b>' . $GLOBALS['LANG']->getLL('import.xml.preview_links.title') . '</b><br/>';
                        /** @var $mkPreviewLinks MkPreviewLinkService */
                        $mkPreviewLinks = GeneralUtility::makeInstance(MkPreviewLinkService::class,
                            $t3_workspaceId = $importManager->headerData['t3_workspaceId'],
                            $t3_sysLang = $importManager->headerData['t3_sysLang'], $pageIds);
                        $actionInfo .= $mkPreviewLinks->renderPreviewLinks($mkPreviewLinks->mkPreviewLinks());
                    }
                    $translationData = $factory->getTranslationDataFromCATXMLNodes($importManager->getXMLNodes());
                    $translationData->setLanguage($this->sysLanguage);
                    //$actionInfo.="<pre>".var_export($GLOBALS['BE_USER'],true)."</pre>";
                    unset($importManager);
                    $service->saveTranslation($l10ncfgObj, $translationData);
                    $actionInfo .= '<br/>' . $this->doc->icons(-1) . $GLOBALS['LANG']->getLL('import.xml.done.message') . '<br/><br/>(Command count:' . $service->lastTCEMAINCommandsCount . ')';
                }
            }
            GeneralUtility::unlink_tempfile($uploadedTempFile);
        }
        // If export of XML is asked for, do that (this will exit and push a file for download, or upload to FTP is option is checked)
        if (GeneralUtility::_POST('export_xml')) {
            // Save user prefs
            $BE_USER->pushModuleData('l10nmgr/cm1/checkUTF8',
                GeneralUtility::_POST('check_utf8'));

            // Render the XML
            /** @var $viewClass CatXmlView */
            $viewClass = GeneralUtility::makeInstance(CatXmlView::class, $l10ncfgObj,
                $this->sysLanguage);
            $export_xml_forcepreviewlanguage = (int)GeneralUtility::_POST('export_xml_forcepreviewlanguage');
            if ($export_xml_forcepreviewlanguage > 0) {
                $viewClass->setForcedSourceLanguage($export_xml_forcepreviewlanguage);
            }
            if ($this->MOD_SETTINGS['onlyChangedContent']) {
                $viewClass->setModeOnlyChanged();
            }
            if ($this->MOD_SETTINGS['noHidden']) {
                $viewClass->setModeNoHidden();
            }
            // Check the export
            if ((GeneralUtility::_POST('check_exports') == '1') && ($viewClass->checkExports() == false)) {
                /** @var $flashMessage FlashMessage */
                $flashMessage = GeneralUtility::makeInstance(FlashMessage::class,
                    $GLOBALS['LANG']->getLL('export.process.duplicate.message'), $GLOBALS['LANG']->getLL('export.process.duplicate.title'),
                    FlashMessage::INFO);
                $actionInfo .= $flashMessage->render();
                $actionInfo .= $viewClass->renderExports();
            } else {
                // Upload to FTP
                if (GeneralUtility::_POST('ftp_upload') == '1') {
                    try {
                        $filename = $this->uploadToFtp($viewClass);
                        // Send a mail notification
                        $this->emailNotification($filename, $l10ncfgObj, $this->sysLanguage);
                        // Prepare a success message for display
                        $title = $GLOBALS['LANG']->getLL('export.ftp.success');
                        $message = sprintf($GLOBALS['LANG']->getLL('export.ftp.success.detail'),
                            $this->lConf['ftp_server_path'] . $filename);
                        $status = FlashMessage::OK;
                    } catch (Exception $e) {
                        // Prepare an error message for display
                        $title = $GLOBALS['LANG']->getLL('export.ftp.error');
                        $message = $e->getMessage() . ' (' . $e->getCode() . ')';
                        $status = FlashMessage::ERROR;
                    }
                    /** @var $flashMessage FlashMessage */
                    $flashMessage = GeneralUtility::makeInstance(FlashMessage::class, $message,
                        $title, $status);
                    $actionInfo .= $flashMessage->render();
                    $actionInfo .= $viewClass->renderInternalMessagesAsFlashMessage($status);
                    // Download the XML file
                } else {
                    try {
                        $filename = $this->downloadXML($viewClass);
                        // Prepare a success message for display
                        $link = sprintf('<a href="%s" target="_blank">%s</a>',
                            GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . $filename,
                            $filename);
                        $title = $GLOBALS['LANG']->getLL('export.download.success');
                        $message = sprintf($GLOBALS['LANG']->getLL('export.download.success.detail'), $link);
                        $status = FlashMessage::OK;
                    } catch (Exception $e) {
                        // Prepare an error message for display
                        $title = $GLOBALS['LANG']->getLL('export.download.error');
                        $message = $e->getMessage() . ' (' . $e->getCode() . ')';
                        $status = FlashMessage::ERROR;
                    }

                    /** @var $flashMessage FlashMessage */
                    $flashMessage = GeneralUtility::makeInstance(FlashMessage::class, $message,
                        $title, $status);
                    $actionInfo .= $flashMessage->render();
                    $actionInfo .= $viewClass->renderInternalMessagesAsFlashMessage($status);
                }

                $viewClass->saveExportInformation();
            }
        }
        if (!empty($actionInfo)) {
            $info .= $this->doc->header($GLOBALS['LANG']->getLL('misc.messages.title'));
            $info .= $actionInfo;
        }

        $info .= '</div>';

        return $info;
    }

    /**
     * Uploads the XML export to the FTP server
     *
     * @param CatXmlView $xmlView Object for generating the XML export
     * @return string The file name, if successful
     * @throws Exception
     */
    protected function uploadToFtp(CatXmlView $xmlView)
    {
        // Save content to the disk and get the file name
        $filename = $xmlView->render();
        $xmlFileName = basename($filename);

        // Try connecting to FTP server and uploading the file
        // If any step fails, an exception is thrown
        $connection = ftp_connect($this->lConf['ftp_server']);
        if ($connection) {
            if (@ftp_login($connection, $this->lConf['ftp_server_username'], $this->lConf['ftp_server_password'])) {
                if (ftp_put($connection, $this->lConf['ftp_server_path'] . $xmlFileName, PATH_site . $filename,
                    FTP_BINARY)) {
                    ftp_close($connection);
                } else {
                    ftp_close($connection);
                    throw new Exception(sprintf($GLOBALS['LANG']->getLL('export.ftp.upload_failed'), $filename,
                        $this->lConf['ftp_server_path']), 1326906926);
                }
            } else {
                ftp_close($connection);
                throw new Exception(sprintf($GLOBALS['LANG']->getLL('export.ftp.login_failed'),
                    $this->lConf['ftp_server_username']), 1326906772);
            }
        } else {
            throw new Exception($GLOBALS['LANG']->getLL('export.ftp.connection_failed'), 1326906675);
        }

        // If everything went well, return the file's base name
        return $xmlFileName;
    }

    /**
     * The function emailNotification sends an email with a translation job to the recipient specified in the extension config.
     *
     * @param string $xmlFileName Name of the XML file
     * @param L10nConfiguration $l10nmgrCfgObj L10N Manager configuration object
     * @param integer $tlang ID of the language to translate to
     * @return void
     */
    protected function emailNotification($xmlFileName, $l10nmgrCfgObj, $tlang)
    {
        // If at least a recipient is indeed defined, proceed with sending the mail
        $recipients = GeneralUtility::trimExplode(',', $this->lConf['email_recipient']);
        if (count($recipients) > 0) {
            $fullFilename = PATH_site . 'uploads/tx_l10nmgr/jobs/out/' . $xmlFileName;

            // Get source & target language ISO codes
            $sourceStaticLangArr = BackendUtility::getRecord('static_languages',
                $l10nmgrCfgObj->l10ncfg['sourceLangStaticId'], 'lg_iso_2');
            $targetStaticLang = BackendUtility::getRecord('sys_language', $tlang, 'static_lang_isocode');
            $targetStaticLangArr = BackendUtility::getRecord('static_languages', $targetStaticLang['static_lang_isocode'],
                'lg_iso_2');
            $sourceLang = $sourceStaticLangArr['lg_iso_2'];
            $targetLang = $targetStaticLangArr['lg_iso_2'];
            // Collect mail data
            $fromMail = $this->lConf['email_sender'];
            $fromName = $this->lConf['email_sender_name'];
            $organisation = $this->lConf['email_sender_organisation'];
            $subject = sprintf($GLOBALS['LANG']->getLL('email.suject.msg'), $sourceLang, $targetLang,
                $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']);
            // Assemble message body
            $message = array(
                'msg1' => $GLOBALS['LANG']->getLL('email.greeting.msg'),
                'msg2' => '',
                'msg3' => sprintf($GLOBALS['LANG']->getLL('email.new_translation_job.msg'), $sourceLang, $targetLang,
                    $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']),
                'msg4' => $GLOBALS['LANG']->getLL('email.info.msg'),
                'msg5' => $GLOBALS['LANG']->getLL('email.info.import.msg'),
                'msg6' => '',
                'msg7' => $GLOBALS['LANG']->getLL('email.goodbye.msg'),
                'msg8' => $fromName,
                'msg9' => '--',
                'msg10' => $GLOBALS['LANG']->getLL('email.info.exportef_file.msg'),
                'msg11' => $xmlFileName,
            );
            if ($this->lConf['email_attachment']) {
                $message['msg3'] = sprintf($GLOBALS['LANG']->getLL('email.new_translation_job_attached.msg'),
                    $sourceLang, $targetLang, $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']);
            }
            $msg = implode(chr(10), $message);

            // Instantiate the mail object, set all necessary properties and send the mail
            /** @var $mailObject MailMessage */
            $mailObject = GeneralUtility::makeInstance(MailMessage::class);
            $mailObject->setFrom(array($fromMail => $fromName));
            $mailObject->setTo($recipients);
            $mailObject->setSubject($subject);
            $mailObject->setFormat('text/plain');
            $mailObject->setBody($msg);
            if ($this->lConf['email_attachment']) {
                $attachment = Swift_Attachment::fromPath($fullFilename, 'text/xml');
                $mailObject->attach($attachment);
            }
            $mailObject->send();
        }
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
     * Printing output content
     *
     * @return  void
     */
    public function printContent()
    {

//		$this->content .= $this->doc->endPage();
        echo $this->content;
    }

    /**
     * Diff-compare markup
     *
     * @param   string    Old content
     * @param   string    New content
     * @return  string    Marked up string.
     */
    function diffCMP($old, $new)
    {
        // Create diff-result:
        /** @var $DiffUtility DiffUtility */
        $DiffUtility = GeneralUtility::makeInstance(DiffUtility::class);

        return $DiffUtility->makeDiffDisplay($old, $new);
    }

    /**
     * @param string Mime type
     * @param string Filename
     * @return void
     */
    protected function sendDownloadHeader($mimeType, $filename)
    {
        // Creating output header:
        Header('Charset: utf-8');
        Header('Content-Type: ' . $mimeType);
        Header('Content-Disposition: attachment; filename=' . $filename);
    }
}

// Make instance:
/** @var $SOBE Cm1 */
$SOBE = GeneralUtility::makeInstance(Cm1::class);
$SOBE->init();

$SOBE->main();
$SOBE->printContent();
?>
