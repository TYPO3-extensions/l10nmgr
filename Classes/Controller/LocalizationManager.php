<?php
namespace Localizationteam\L10nmgr\Controller;

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
use Exception;
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
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swift_Attachment;
use TYPO3\CMS\Backend\Configuration\TranslationConfigurationProvider;
use TYPO3\CMS\Backend\Module\BaseScriptClass;
use TYPO3\CMS\Backend\Routing\Router;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\DocumentTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageRendererResolver;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * l10nmgr module Configuration Manager
 *
 * @author Kasper Skårhøj <kasperYYYY@typo3.com>
 * @author Daniel Zielinski <d.zielinski@l10ntech.de>
 * @author Daniel Pötzinger <poetzinger@aoemedia.de>
 * @author Fabian Seltmann <fs@marketing-factory.de>
 * @author Andreas Otto <andreas.otto@dkd.de>
 * @author Jo Hasenau <info@cybercraft.de>
 */

/**
 * Translation management tool
 *
 * @author Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage tx_l10nmgr
 */
class LocalizationManager extends BaseScriptClass
{
    /**
     * Document Template Object
     *
     * @var DocumentTemplate
     */
    public $doc;
    /**
     * @var array
     */
    protected $flexFormDiffArray = array(); // Internal
    /**
     * @var int Default language to export
     */
    protected $sysLanguage = '0'; // Internal
    /**
     * @var int Forced source language to export
     */
    protected $previewLanguage = '0';
    /**
     * @var array Extension configuration
     */
    protected $lConf = array();
    /**
     * ModuleTemplate Container
     *
     * @var ModuleTemplate
     */
    protected $moduleTemplate;
    /**
     * The name of the module
     *
     * @var string
     */
    protected $moduleName = 'ConfigurationManager_LocalizationManager';
    /**
     * @var IconFactory
     */
    protected $iconFactory;
    /**
     * @var array | bool
     */
    protected $pageinfo;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $this->moduleTemplate = GeneralUtility::makeInstance(ModuleTemplate::class);
        $this->getLanguageService()->includeLLFile('EXT:l10nmgr/Resources/Private/Language/Modules/LocalizationManager/locallang.xlf');
        $this->MCONF = array(
            'name' => $this->moduleName,
        );
    }

    /**
     * Injects the request object for the current request or subrequest
     * Then checks for module functions that have hooked in, and renders menu etc.
     *
     * @param ServerRequestInterface $request the current request
     * @param ResponseInterface $response
     * @return ResponseInterface the response with the content
     */
    public function mainAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $GLOBALS['SOBE'] = $this;
        $this->init();
        // Checking for first level external objects
        $this->checkExtObj();
        // Checking second level external objects
        $this->checkSubExtObj();
        $this->main();
        $this->moduleTemplate->setContent($this->content);
        $response->getBody()->write($this->moduleTemplate->renderContent());
        return $response;
    }

    /**
     * Initializes the Module
     *
     * @return void
     */
    public function init()
    {
        $this->getBackendUser()->modAccess($this->MCONF, 1);
        parent::init();
    }

    /**
     * Main function of the module. Write the content to
     *
     * @return void
     */
    protected function main()
    {
        // Get language to export/import
        $this->sysLanguage = $this->MOD_SETTINGS["lang"];
        // Draw the header.
        $this->moduleTemplate->addJavaScriptCode(
            'jumpToUrl',
            '
function jumpToUrl(URL) {
window.location.href = URL;
return false;
}
'
        );
        $this->moduleTemplate->setForm('<form action="" method="post" enctype="multipart/form-data">');
        // Find l10n configuration record
        /** @var L10nConfiguration $l10ncfgObj */
        $l10ncfgObj = GeneralUtility::makeInstance(L10nConfiguration::class);
        $l10ncfgObj->load((int)GeneralUtility::_GP('exportUID'));
        if ($l10ncfgObj->isLoaded()) {
            // Setting page id
            $this->id = $l10ncfgObj->getData('pid');
            $this->perms_clause = $this->getBackendUser()->getPagePermsClause(1);
            $this->pageinfo = BackendUtility::readPageAccess($this->id, $this->perms_clause);
            $access = is_array($this->pageinfo) ? 1 : 0;
            if ($this->id && $access) {
                // Header:
                //	$this->content.=$this->moduleTemplate->startPage($this->getLanguageService()->getLL('general.title'));
                $this->content .= $this->moduleTemplate->header($this->getLanguageService()->getLL('general.title'));
                // Create and render view to show details for the current l10nmgrcfg
                /** @var L10nConfigurationDetailView $l10nmgrconfigurationView */
                $l10nmgrconfigurationView = GeneralUtility::makeInstance(L10nConfigurationDetailView::class,
                    $l10ncfgObj, $this->moduleTemplate);
                $this->content .= '<div><h2 class="uppercase">' . $this->getLanguageService()->getLL('general.manager') . '</h2>' .
                    $l10nmgrconfigurationView->render() . '</div>';
                $this->content .= '<hr />';
                $title = $this->MOD_MENU["action"][$this->MOD_SETTINGS["action"]];
                $this->content .= '<div> 
<h2 class="uppercase">' . $title . '</h2>
<div class="col-md-6">
<div class="form-inline form-inline-spaced">
<div class="form-section">' .
                    $this->getFuncMenu($this->id,
                        "SET[action]", $this->MOD_SETTINGS["action"], $this->MOD_MENU["action"], '',
                        '&srcPID=' . rawurlencode(GeneralUtility::_GET('srcPID')) . '&exportUID=' . $l10ncfgObj->getId(),
                        $this->getLanguageService()->getLL('general.export.choose.action.title')) .
                    '<br />' .
                    $this->getFuncMenu($this->id,
                        "SET[lang]", $this->sysLanguage, $this->MOD_MENU["lang"], '',
                        '&srcPID=' . rawurlencode(GeneralUtility::_GET('srcPID')) . '&exportUID=' . $l10ncfgObj->getId(),
                        $this->getLanguageService()->getLL('export.overview.targetlanguage.label')) .
                    '<br /><br /></div><div class="form-section">' .
                    $this->getFuncCheck(
                        $this->id,
                        "SET[onlyChangedContent]",
                        $this->MOD_SETTINGS["onlyChangedContent"],
                        '',
                        '&srcPID=' . rawurlencode(GeneralUtility::_GET('srcPID')) . '&exportUID=' . $l10ncfgObj->getId(),
                        '',
                        $this->getLanguageService()->getLL('export.xml.new.title')
                    ) . '<br />' .
                    $this->getFuncCheck(
                        $this->id,
                        "SET[noHidden]",
                        $this->MOD_SETTINGS["noHidden"],
                        '',
                        '&srcPID=' . rawurlencode(GeneralUtility::_GET('srcPID')) . '&exportUID=' . $l10ncfgObj->getId(),
                        '',
                        $this->getLanguageService()->getLL('export.xml.noHidden.title')
                    ) .
                    '<br /><br ></div></div></div></div>';
                // Render content:
                if (!count($this->MOD_MENU['lang'])) {
                    $this->content .= '<div><h2>ERROR<h2>' . $this->getLanguageService()->getLL('general.access.error.title') . '</div>';
                } else {
                    $this->moduleContent($l10ncfgObj);
                }
            }
        }
    }

    /**
     * Returns a selector box "function menu" for a module
     * Requires the JS function jumpToUrl() to be available
     * See Inside TYPO3 for details about how to use / make Function menus
     *
     * @param mixed $mainParams The "&id=" parameter value to be sent to the module, but it can be also a parameter array which will be passed instead of the &id=...
     * @param string $elementName The form elements name, probably something like "SET[...]
     * @param string $currentValue The value to be selected currently.
     * @param array $menuItems An array with the menu items for the selector box
     * @param string $script The script to send the &id to, if empty it's automatically found
     * @param string $addParams Additional parameters to pass to the script.
     * @param string $label
     *
     * @return string HTML code for selector box
     */
    public static function getFuncMenu(
        $mainParams,
        $elementName,
        $currentValue,
        $menuItems,
        $script = '',
        $addParams = '',
        $label = ''
    ) {
        if (!is_array($menuItems)) {
            return '';
        }
        $scriptUrl = self::buildScriptUrl($mainParams, $addParams, $script);
        $options = array();
        foreach ($menuItems as $value => $text) {
            $options[] = '<option value="' . htmlspecialchars($value) . '"' . ((string)$currentValue === (string)$value ? ' selected="selected"' : '') . '>' . htmlspecialchars($text,
                    ENT_COMPAT, 'UTF-8', false) . '</option>';
        }
        $label = $label !== '' ?
            ('<label>' . htmlspecialchars($label) . '</label><br />') :
            '';
        if (!empty($options)) {
            $onChange = 'jumpToUrl(' . GeneralUtility::quoteJSvalue($scriptUrl . '&' . $elementName . '=') . '+this.options[this.selectedIndex].value,this);';
            return '
	<!-- Function Menu of module -->
<div class="form-group">' .
                $label .
                '<select class="form-control clear-both" name="' . $elementName . '" onchange="' . htmlspecialchars($onChange) . '">
	' . implode('
	', $options) . '
	</select>
	</div>
	';
        }
        return '';
    }

    /**
     * Builds the URL to the current script with given arguments
     *
     * @param mixed $mainParams $id is the "&id=" parameter value to be sent to the module, but it can be also a parameter array which will be passed instead of the &id=...
     * @param string $addParams Additional parameters to pass to the script.
     * @param string $script The script to send the &id to, if empty it's automatically found
     * @return string The completes script URL
     */
    protected static function buildScriptUrl($mainParams, $addParams, $script = '')
    {
        if (!is_array($mainParams)) {
            $mainParams = array('id' => $mainParams);
        }
        if (!$script) {
            $script = basename(PATH_thisScript);
        }
        if (GeneralUtility::_GP('route')) {
            /** @var Router $router */
            $router = GeneralUtility::makeInstance(Router::class);
            $route = $router->match(GeneralUtility::_GP('route'));
            /** @var UriBuilder $uriBuilder */
            $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
            $scriptUrl = (string)$uriBuilder->buildUriFromRoute($route->getOption('_identifier'));
            $scriptUrl .= $addParams;
        } elseif ($script === 'index.php' && GeneralUtility::_GET('M')) {
            $scriptUrl = BackendUtility::getModuleUrl(GeneralUtility::_GET('M'), $mainParams) . $addParams;
        } else {
            $scriptUrl = $script . '?' . GeneralUtility::implodeArrayForUrl('', $mainParams) . $addParams;
        }
        return $scriptUrl;
    }

    /**
     * Checkbox function menu.
     * Works like ->getFuncMenu() but takes no $menuItem array since this is a simple checkbox.
     *
     * @param mixed $mainParams $id is the "&id=" parameter value to be sent to the module, but it can be also a parameter array which will be passed instead of the &id=...
     * @param string $elementName The form elements name, probably something like "SET[...]
     * @param string $currentValue The value to be selected currently.
     * @param string $script The script to send the &id to, if empty it's automatically found
     * @param string $addParams Additional parameters to pass to the script.
     * @param string $tagParams Additional attributes for the checkbox input tag
     * @param string $label
     *
     * @return string HTML code for checkbox
     * @see getFuncMenu()
     */
    public static function getFuncCheck(
        $mainParams,
        $elementName,
        $currentValue,
        $script = '',
        $addParams = '',
        $tagParams = '',
        $label = ''
    ) {
        $scriptUrl = self::buildScriptUrl($mainParams, $addParams, $script);
        $onClick = 'jumpToUrl(' . GeneralUtility::quoteJSvalue($scriptUrl . '&' . $elementName . '=') . '+(this.checked?1:0),this);';
        return
            '<div class="form-group">' .
            '<div class="checkbox">
<label>
<input' .
            ' type="checkbox"' .
            ' name="' . $elementName . '"' .
            ($currentValue ? ' checked="checked"' : '') .
            ' onclick="' . htmlspecialchars($onClick) . '"' .
            ($tagParams ? ' ' . $tagParams : '') .
            ' value="1"' .
            ' />&nbsp;' .
            htmlspecialchars($label) .
            '</label>
</div>
</div>';
    }

    /**
     * Creating module content
     *
     * @paramL10nConfiguration $l10ncfgObj Localization Configuration record
     *
     * @return void
     */
    protected function moduleContent($l10ncfgObj)
    {
        $subheader = '';
        switch ($this->MOD_SETTINGS["action"]) {
            case 'inlineEdit':
            case 'link':
                /** @var L10nHTMLListView $htmlListView */
                $htmlListView = GeneralUtility::makeInstance(L10nHtmlListView::class, $l10ncfgObj, $this->sysLanguage);
                $subheader = $this->getLanguageService()->getLL('inlineEdit');
                $subcontent = '';
                if ($this->MOD_SETTINGS["action"] == 'inlineEdit') {
                    $subheader = $this->getLanguageService()->getLL('link');
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
                $subcontent .= '</div></div><div class="col-md-12">' . $htmlListView->renderOverview();
                break;
            case 'export_excel':
                $subheader = $this->getLanguageService()->getLL('export_excel');
                $subcontent = $this->excelExportImportAction($l10ncfgObj);
                break;
            case 'export_xml': // XML import/export
                $prefs['utf8'] = GeneralUtility::_POST('check_utf8');
                $prefs['noxmlcheck'] = GeneralUtility::_POST('no_check_xml');
                $this->getBackendUser()->pushModuleData('l10nmgr/cm1/prefs', $prefs);
                $subheader = $this->getLanguageService()->getLL('export_xml');
                $subcontent = $this->catXMLExportImportAction($l10ncfgObj);
                break;
            DEFAULT: // Default display:
                $subcontent = '<input class="btn btn-default" type="submit" value="' . $this->getLanguageService()->getLL('general.action.refresh.button.title') . '" name="_" />';
                break;
        } //switch block
        $this->content .= '<div><h3 class="uppercase">' . $subheader . '</h3>' .
            '<div class="col-md-6"><div class="form-inline form-inline-spaced">' . $subcontent . '</div></div></div>';
    }

    /**
     * @param L10nConfiguration $l10ncfgObj
     * @return string
     */
    protected function inlineEditAction($l10ncfgObj)
    {
        /** @var L10nBaseService $service */
        $service = GeneralUtility::makeInstance(L10nBaseService::class);
        $info = '';
        // Buttons:
        $info .= '<input class="btn btn-success" type="submit" value="' . $this->getLanguageService()->getLL('general.action.save.button.title') . '" name="saveInline" onclick="return confirm(\'' . $this->getLanguageService()->getLL('inlineedit.save.alert.title') . '\');" />&nbsp;';
        $info .= '<input class="btn btn-danger" type="submit" value="' . $this->getLanguageService()->getLL('general.action.cancel.button.title') . '" name="_" onclick="return confirm(\'' . $this->getLanguageService()->getLL('inlineedit.cancel.alert.title') . '\');" />';
        //simple init of translation object:
        /** @var TranslationData $translationData */
        $translationData = GeneralUtility::makeInstance(TranslationData::class);
        $translationData->setTranslationData(GeneralUtility::_POST('translation'));
        $translationData->setLanguage($this->sysLanguage);
        $translationData->setPreviewLanguage($this->previewLanguage);
        // See, if incoming translation is available, if so, submit it
        if (GeneralUtility::_POST('saveInline')) {
            $service->saveTranslation($l10ncfgObj, $translationData);
        }
        return $info;
    }

    /**
     * @param L10nConfiguration $l10ncfgObj
     * @return string
     */
    protected function excelExportImportAction($l10ncfgObj)
    {
        /** @var L10nBaseService $service */
        $service = GeneralUtility::makeInstance(L10nBaseService::class);
        if (GeneralUtility::_POST('import_asdefaultlanguage') == '1') {
            $service->setImportAsDefaultLanguage(true);
        }
        // Buttons:
        $_selectOptions = array('0' => '-default-');
        $_selectOptions = $_selectOptions + $this->MOD_MENU["lang"];
        $info = '<div class="form-section">' .
            $this->getFuncCheck(
                $this->id,
                'SET[check_exports]',
                $this->MOD_SETTINGS['check_exports'],
                '',
                '&srcPID=' . rawurlencode(GeneralUtility::_GET('srcPID')) . '&exportUid=' . $l10ncfgObj->getId(),
                '',
                $this->getLanguageService()->getLL('export.xml.check_exports.title')
            ) . '<br />' .
            '<div class="form-group"><div class="checkbox"><label>' .
            '<input type="checkbox" value="1" name="import_asdefaultlanguage" /> ' . $this->getLanguageService()->getLL('import.xml.asdefaultlanguage.title') .
            '</label></div></div><br /><br />' .
            '</div><div class="form-section"><div class="form-group">
<label>' . $this->getLanguageService()->getLL('export.xml.source-language.title') . '</label><br />' .
            $this->_getSelectField("export_xml_forcepreviewlanguage", '0', $_selectOptions) .
            '<br /><br /></div></div><div class="form-section">
<label>' . $this->getLanguageService()->getLL('general.action.import.upload.title') . '</label><br />' .
            '<input type="file" size="60" name="uploaded_import_file" />' .
            '<br /></div><div class="form-section">' .
            '<input class="btn btn-default btn-info" type="submit" value="' . $this->getLanguageService()->getLL('general.action.refresh.button.title') . '" name="_" /> ' .
            '<input class="btn btn-default btn-success" type="submit" value="' . $this->getLanguageService()->getLL('general.action.export.xml.button.title') . '" name="export_excel" /> ' .
            '<input class="btn btn-default btn-warning" type="submit" value="' . $this->getLanguageService()->getLL('general.action.import.xml.button.title') . '" name="import_excel" />
<br /><br /></div></div>';
        // Read uploaded file:
        if (GeneralUtility::_POST('import_excel') && $_FILES['uploaded_import_file']['tmp_name'] && is_uploaded_file($_FILES['uploaded_import_file']['tmp_name'])) {
            $uploadedTempFile = GeneralUtility::upload_to_tempfile($_FILES['uploaded_import_file']['tmp_name']);
            /** @var TranslationDataFactory $factory */
            $factory = GeneralUtility::makeInstance(TranslationDataFactory::class);
            //TODO: catch exeption
            $translationData = $factory->getTranslationDataFromExcelXMLFile($uploadedTempFile);
            $translationData->setLanguage($this->sysLanguage);
            $translationData->setPreviewLanguage($this->previewLanguage);
            GeneralUtility::unlink_tempfile($uploadedTempFile);
            $service->saveTranslation($l10ncfgObj, $translationData);
            $info .= '<br/><br/>' . $this->moduleTemplate->icons(1) . $this->getLanguageService()->getLL('import.success.message') . '<br/><br/>';
        }
        // If export of XML is asked for, do that (this will exit and push a file for download)
        if (GeneralUtility::_POST('export_excel')) {
            // Render the XML
            /** @var ExcelXmlView $viewClass */
            $viewClass = GeneralUtility::makeInstance(ExcelXmlView::class, $l10ncfgObj, $this->sysLanguage);
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
            if ($this->MOD_SETTINGS['check_exports'] && !$viewClass->checkExports()) {
                /** @var FlashMessage $flashMessage */
                $flashMessage = GeneralUtility::makeInstance(FlashMessage::class,
                    '###MESSAGE###',
                    $this->getLanguageService()->getLL('export.process.duplicate.title'), FlashMessage::INFO);
                $info .= str_replace(
                    '###MESSAGE###',
                    $this->getLanguageService()->getLL('export.process.duplicate.message'),
                    GeneralUtility::makeInstance(FlashMessageRendererResolver::class)
                        ->resolve()
                        ->render([$flashMessage])
                );
                $info .= $viewClass->renderExports();
            } else {
                try {
                    $filename = $this->downloadXML($viewClass);
                    // Prepare a success message for display
                    $link = sprintf('<a href="%s" target="_blank">%s</a>',
                        GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . $filename, $filename);
                    $title = $this->getLanguageService()->getLL('export.download.success');
                    $message = '###MESSAGE###';
                    $status = FlashMessage::OK;
                    /** @var FlashMessage $flashMessage */
                    $flashMessage = GeneralUtility::makeInstance(FlashMessage::class, $message, $title, $status);
                    $info .= str_replace(
                        '###MESSAGE###',
                        sprintf($this->getLanguageService()->getLL('export.download.success.detail'), $link),
                        GeneralUtility::makeInstance(FlashMessageRendererResolver::class)
                            ->resolve()
                            ->render([$flashMessage])
                    );
                } catch (Exception $e) {
                    // Prepare an error message for display
                    $title = $this->getLanguageService()->getLL('export.download.error');
                    $message = '###MESSAGE###';
                    $status = FlashMessage::ERROR;
                    /** @var FlashMessage $flashMessage */
                    $flashMessage = GeneralUtility::makeInstance(FlashMessage::class, $message, $title, $status);
                    $info .= str_replace(
                        '###MESSAGE###',
                        $e->getMessage() . ' (' . $e->getCode() . ')',
                        GeneralUtility::makeInstance(FlashMessageRendererResolver::class)
                            ->resolve()
                            ->render([$flashMessage])
                    );
                }
                /** @var $flashMessage FlashMessage */
                $info .= $viewClass->renderInternalMessagesAsFlashMessage($status);
                $viewClass->saveExportInformation();
            }
        }
        return $info;
    }

    /**
     * @param string $elementName
     * @param string $currentValue
     * @param array $menuItems
     * @return string
     */
    protected function _getSelectField($elementName, $currentValue, $menuItems)
    {
        $options = array();
        foreach ($menuItems as $value => $label) {
            $options[] = '<option value="' . htmlspecialchars($value) . '"' . (!strcmp($currentValue,
                    $value) ? ' selected="selected"' : '') . '>' . htmlspecialchars($label, ENT_COMPAT, 'UTF-8',
                    false) . '</option>';
        }
        if (count($options) > 0) {
            return '
	<select class="form-control" name="' . $elementName . '" >
	' . implode('
	', $options) . '
	</select>
	';
        } else {
            return '';
        }
    }

    /**
     * Sends download header and calls render method of the view.
     * Used for excelXML and CATXML.
     *
     * @param CatXmlView | ExcelXmlView $xmlView Object for generating the XML export
     *
     * @return string $filename
     */
    protected function downloadXML($xmlView)
    {
        // Save content to the disk and get the file name
        $filename = $xmlView->render();
        return $filename;
    }

    /**
     * @param L10nConfiguration $l10ncfgObj
     * @return string
     */
    protected function catXMLExportImportAction($l10ncfgObj)
    {
        /** @var L10nBaseService $service */
        $service = GeneralUtility::makeInstance(L10nBaseService::class);
        $menuItems = array(
            '0' => array(
                'label' => $this->getLanguageService()->getLL('export.xml.headline.title'),
                'content' => $this->getTabContentXmlExport()
            ),
            '1' => array(
                'label' => $this->getLanguageService()->getLL('import.xml.headline.title'),
                'content' => $this->getTabContentXmlImport()
            ),
            '2' => array(
                'label' => $this->getLanguageService()->getLL('file.settings.downloads.title'),
                'content' => $this->getTabContentXmlDownloads()
            ),
            '3' => array(
                'label' => $this->getLanguageService()->getLL('l10nmgr.documentation.title'),
                'content' => '<a class="btn btn-success" href="/' . ExtensionManagementUtility::siteRelPath('l10nmgr') . 'Documentation/manual.sxw" target="_new">Download</a>'
            )
        );
        $info = $this->moduleTemplate->getDynamicTabMenu($menuItems, 'ddtabs');
        $actionInfo = '';
        // Read uploaded file:
        if (GeneralUtility::_POST('import_xml') && $_FILES['uploaded_import_file']['tmp_name'] && is_uploaded_file($_FILES['uploaded_import_file']['tmp_name'])) {
            $uploadedTempFile = GeneralUtility::upload_to_tempfile($_FILES['uploaded_import_file']['tmp_name']);
            /** @var TranslationDataFactory $factory */
            $factory = GeneralUtility::makeInstance(TranslationDataFactory::class);
            //print "<pre>";
            //var_dump($this->getBackendUser()->user);
            //print "</pre>";
            if (GeneralUtility::_POST('import_asdefaultlanguage') == '1') {
                $service->setImportAsDefaultLanguage(true);
            }
            if (GeneralUtility::_POST('import_oldformat') == '1') {
                //Support for the old Format of XML Import (without pageGrp element)
                $actionInfo .= $this->getLanguageService()->getLL('import.xml.old-format.message');
                $translationData = $factory->getTranslationDataFromOldFormatCATXMLFile($uploadedTempFile);
                $translationData->setLanguage($this->sysLanguage);
                $translationData->setPreviewLanguage($this->previewLanguage);
                $service->saveTranslation($l10ncfgObj, $translationData);
                $actionInfo .= '<br/><br/>' . $this->moduleTemplate->icons(1) . 'Import done<br/><br/>(Command count:' . $service->lastTCEMAINCommandsCount . ')';
            } else {
                // Relevant processing of XML Import with the help of the Importmanager
                /** @var CatXmlImportManager $importManager */
                $importManager = GeneralUtility::makeInstance(CatXmlImportManager::class, $uploadedTempFile,
                    $this->sysLanguage, $xmlString = "");
                if ($importManager->parseAndCheckXMLFile() === false) {
                    $actionInfo .= '<br/><br/>' . $this->moduleTemplate->header($this->getLanguageService()->getLL('import.error.title')) . $importManager->getErrorMessages();
                } else {
                    if (GeneralUtility::_POST('import_delL10N') == '1') {
                        $actionInfo .= $this->getLanguageService()->getLL('import.xml.delL10N.message') . '<br/>';
                        $delCount = $importManager->delL10N($importManager->getDelL10NDataFromCATXMLNodes($importManager->getXMLNodes()));
                        $actionInfo .= sprintf($this->getLanguageService()->getLL('import.xml.delL10N.count.message'),
                                $delCount) . '<br/><br/>';
                    }
                    if (GeneralUtility::_POST('make_preview_link') == '1') {
                        $pageIds = $importManager->getPidsFromCATXMLNodes($importManager->getXmlNodes());
                        $actionInfo .= '<b>' . $this->getLanguageService()->getLL('import.xml.preview_links.title') . '</b><br/>';
                        /** @var MkPreviewLinkService $mkPreviewLinks */
                        $mkPreviewLinks = GeneralUtility::makeInstance(MkPreviewLinkService::class,
                            $t3_workspaceId = $importManager->headerData['t3_workspaceId'],
                            $t3_sysLang = $importManager->headerData['t3_sysLang'], $pageIds);
                        $actionInfo .= $mkPreviewLinks->renderPreviewLinks($mkPreviewLinks->mkPreviewLinks());
                    }
                    if ($importManager->headerData['t3_sourceLang'] === $importManager->headerData['t3_targetLang']) {
                        $this->previewLanguage = $this->sysLanguage;
                    }
                    $translationData = $factory->getTranslationDataFromCATXMLNodes($importManager->getXMLNodes());
                    $translationData->setLanguage($this->sysLanguage);
                    $translationData->setPreviewLanguage($this->previewLanguage);
                    //$actionInfo.="<pre>".var_export($GLOBALS['BE_USER'],true)."</pre>";
                    unset($importManager);
                    $service->saveTranslation($l10ncfgObj, $translationData);
                    $actionInfo .= '<br/>' . $this->moduleTemplate->icons(-1) . $this->getLanguageService()->getLL('import.xml.done.message') . '<br/><br/>(Command count:' . $service->lastTCEMAINCommandsCount . ')';
                }
            }
            GeneralUtility::unlink_tempfile($uploadedTempFile);
        }
        // If export of XML is asked for, do that (this will exit and push a file for download, or upload to FTP is option is checked)
        if (GeneralUtility::_POST('export_xml')) {
            // Save user prefs
            $this->getBackendUser()->pushModuleData('l10nmgr/cm1/checkUTF8', GeneralUtility::_POST('check_utf8'));
            // Render the XML
            /** @var CatXmlView $viewClass */
            $viewClass = GeneralUtility::makeInstance(CatXmlView::class, $l10ncfgObj, $this->sysLanguage);
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
            if ($this->MOD_SETTINGS['check_exports'] && !$viewClass->checkExports()) {
                /** @var FlashMessage $flashMessage */
                $flashMessage = GeneralUtility::makeInstance(FlashMessage::class,
                    '###MESSAGE###',
                    $this->getLanguageService()->getLL('export.process.duplicate.title'), FlashMessage::INFO);
                $actionInfo .= str_replace(
                    '###MESSAGE###',
                    $this->getLanguageService()->getLL('export.process.duplicate.message'),
                    GeneralUtility::makeInstance(FlashMessageRendererResolver::class)
                        ->resolve()
                        ->render([$flashMessage])
                );
                $actionInfo .= $viewClass->renderExports();
            } else {
                // Upload to FTP
                if (GeneralUtility::_POST('ftp_upload') == '1') {
                    try {
                        $filename = $this->uploadToFtp($viewClass);
                        // Send a mail notification
                        $this->emailNotification($filename, $l10ncfgObj, $this->sysLanguage);
                        // Prepare a success message for display
                        $title = $this->getLanguageService()->getLL('export.ftp.success');
                        $message = '###MESSAGE###';
                        $status = FlashMessage::OK;
                        /** @var FlashMessage $flashMessage */
                        $flashMessage = GeneralUtility::makeInstance(FlashMessage::class, $message, $title, $status);
                        $actionInfo .= str_replace(
                            '###MESSAGE###',
                            sprintf($this->getLanguageService()->getLL('export.ftp.success.detail'),
                                $this->lConf['ftp_server_path'] . $filename),
                            GeneralUtility::makeInstance(FlashMessageRendererResolver::class)
                                ->resolve()
                                ->render([$flashMessage])
                        );
                    } catch (Exception $e) {
                        // Prepare an error message for display
                        $title = $this->getLanguageService()->getLL('export.ftp.error');
                        $message = '###MESSAGE###';
                        $status = FlashMessage::ERROR;
                        /** @var FlashMessage $flashMessage */
                        $flashMessage = GeneralUtility::makeInstance(FlashMessage::class, $message, $title, $status);
                        $actionInfo .= str_replace(
                            '###MESSAGE###',
                            $e->getMessage() . ' (' . $e->getCode() . ')',
                            GeneralUtility::makeInstance(FlashMessageRendererResolver::class)
                                ->resolve()
                                ->render([$flashMessage])
                        );
                    }
                    /** @var $flashMessage FlashMessage */
                    $actionInfo .= $viewClass->renderInternalMessagesAsFlashMessage($status);
                    // Download the XML file
                } else {
                    try {
                        $filename = $this->downloadXML($viewClass);
                        // Prepare a success message for display
                        $link = sprintf('<a href="%s" target="_blank">%s</a>',
                            GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . $filename, $filename);
                        $title = $this->getLanguageService()->getLL('export.download.success');
                        $message = '###MESSAGE###';
                        $status = FlashMessage::OK;
                        /** @var FlashMessage $flashMessage */
                        $flashMessage = GeneralUtility::makeInstance(FlashMessage::class, $message, $title, $status);
                        $actionInfo .= str_replace(
                            '###MESSAGE###',
                            sprintf($this->getLanguageService()->getLL('export.download.success.detail'), $link),
                            GeneralUtility::makeInstance(FlashMessageRendererResolver::class)
                                ->resolve()
                                ->render([$flashMessage])
                        );
                    } catch (Exception $e) {
                        // Prepare an error message for display
                        $title = $this->getLanguageService()->getLL('export.download.error');
                        $message = '###MESSAGE###';
                        $status = FlashMessage::ERROR;
                        /** @var FlashMessage $flashMessage */
                        $flashMessage = GeneralUtility::makeInstance(FlashMessage::class, $message, $title, $status);
                        $actionInfo .= str_replace(
                            '###MESSAGE###',
                            $e->getMessage() . ' (' . $e->getCode() . ')',
                            GeneralUtility::makeInstance(FlashMessageRendererResolver::class)
                                ->resolve()
                                ->render([$flashMessage])
                        );
                    }
                    /** @var $flashMessage FlashMessage */
                    $actionInfo .= $viewClass->renderInternalMessagesAsFlashMessage($status);
                }
                $viewClass->saveExportInformation();
            }
        }
        if (!empty($actionInfo)) {
            $info .= $this->moduleTemplate->header($this->getLanguageService()->getLL('misc.messages.title'));
            $info .= $actionInfo;
        }
        // $info .= '</div>';
        return $info;
    }

    /**
     * @return string
     */
    protected function getTabContentXmlExport()
    {
        $_selectOptions = array('0' => '-default-');
        $_selectOptions = $_selectOptions + $this->MOD_MENU["lang"];
        $tabContentXmlExport = '<div class="form-section">' .
            '<div class="form-group"><div class="checkbox"><label>' .
            '<input type="checkbox" value="1" name="check_exports" /> ' . $this->getLanguageService()->getLL('export.xml.check_exports.title') .
            '</label></div></div><br />' .
            '<div class="form-group"><div class="checkbox"><label>' .
            '<input type="checkbox" value="1" checked="checked" name="no_check_xml" /> ' . $this->getLanguageService()->getLL('export.xml.no_check_xml.title') .
            '</label></div></div><br />' .
            '<div class="form-group"><div class="checkbox"><label>' .
            '<input type="checkbox" value="1" name="check_utf8" /> ' . $this->getLanguageService()->getLL('export.xml.checkUtf8.title') .
            '</label></div></div><br /><br />' .
            '</div><div class="form-section">' .
            '<div class="form-group">' .
            '<label>' . $this->getLanguageService()->getLL('export.xml.source-language.title') . '</label><br />' .
            $this->_getSelectField("export_xml_forcepreviewlanguage", '0', $_selectOptions) .
            '<br /><br /></div></div>';
        // Add the option to send to FTP server, if FTP information is defined
        if (!empty($this->lConf['ftp_server']) && !empty($this->lConf['ftp_server_username']) && !empty($this->lConf['ftp_server_password'])) {
            $tabContentXmlExport .= '<input type="checkbox" value="1" name="ftp_upload" id="tx_l10nmgr_ftp_upload" />
<label for="tx_l10nmgr_ftp_upload">' . $this->getLanguageService()->getLL('export.xml.ftp.title') . '</label>';
        }
        $tabContentXmlExport .= '<div class="form-section"><input class="btn btn-default btn-info" type="submit" value="' . $this->getLanguageService()->getLL('general.action.refresh.button.title') . '" name="_" /> ' .
            '<input class="btn btn-default btn-success" type="submit" value="Export" name="export_xml" /><br class="clearfix">&nbsp;</div>';
        return $tabContentXmlExport;
    }

    /**
     * @return string
     */
    protected function getTabContentXmlImport()
    {
        $tabContentXmlImport = '<div class="form-section">' .
            '<div class="form-group"><div class="checkbox"><label>' .
            '<input type="checkbox" value="1" name="make_preview_link" /> ' . $this->getLanguageService()->getLL('import.xml.make_preview_link.title') .
            '</label></div></div><br />' .
            '<div class="form-group"><div class="checkbox"><label>' .
            '<input type="checkbox" value="1" name="import_delL10N" /> ' . $this->getLanguageService()->getLL('import.xml.delL10N.title') .
            '</label></div></div><br />' .
            '<div class="form-group"><div class="checkbox"><label>' .
            '<input type="checkbox" value="1" name="import_asdefaultlanguage" /> ' . $this->getLanguageService()->getLL('import.xml.asdefaultlanguage.title') .
            '</label></div></div><br /><br /></div>' .
            '<div class="form-section">' .
            '<input type="file" size="60" name="uploaded_import_file" /><br />' .
            '</div>' .
            '<div class="form-section">' .
            '<input class="btn btn-info" type="submit" value="' . $this->getLanguageService()->getLL('general.action.refresh.button.title') . '" name="_" /> ' .
            '<input class="btn btn-warning" type="submit" value="Import" name="import_xml" />' .
            '<br class="clearfix">&nbsp;</div>';
        return $tabContentXmlImport;
    }

    /**
     * @return string
     */
    protected function getTabContentXmlDownloads()
    {
        global $BACK_PATH;
        $allowedSettingFiles = array(
            'across' => 'acrossL10nmgrConfig.dst',
            'dejaVu' => 'dejaVuL10nmgrConfig.dvflt',
            'memoq' => 'memoQ.mqres',
            'memoq2013-2014' => 'XMLConverter_TYPO3_l10nmgr_v3.6.mqres',
            'transit' => 'StarTransit_XML_UTF_TYPO3.FFD',
            'sdltrados2007' => 'SDLTradosTagEditor.ini',
            'sdltrados2009' => 'TYPO3_l10nmgr.sdlfiletype',
            'sdltrados2011-2014' => 'TYPO3_ConfigurationManager_v3.6.free.sdlftsettings',
            'sdlpassolo' => 'SDLPassolo.xfg',
        );
        $tabContentXmlDownloads = '<h4>' . $this->getLanguageService()->getLL('file.settings.available.title') . '</h4><ul>';
        foreach ($allowedSettingFiles as $settingId => $settingFileName) {
            $absoluteFileName = GeneralUtility::getFileAbsFileName('EXT:l10nmgr/Configuration/Settings/' . $settingFileName);
            $currentFile = GeneralUtility::resolveBackPath($BACK_PATH . ExtensionManagementUtility::siteRelPath('l10nmgr') . 'Configuration/Settings/' . $settingFileName);
            if (is_file($absoluteFileName) && is_readable($absoluteFileName)) {
                $size = GeneralUtility::formatSize((int)filesize($absoluteFileName), ' Bytes| KB| MB| GB');
                $tabContentXmlDownloads .= '<li><a class="t3-link" href="' . str_replace('%2F', '/',
                        rawurlencode($currentFile)) . '" title="' . $this->getLanguageService()->getLL('file.settings.download.title') . '" target="_blank">' . $this->getLanguageService()->getLL('file.settings.' . $settingId . '.title') . ' (' . $size . ')' . '</a></li>';
            }
        }
        $tabContentXmlDownloads .= '</ul>';
        return $tabContentXmlDownloads;
    }

    /**
     * Uploads the XML export to the FTP server
     *
     * @param CatXmlView $xmlView Object for generating the XML export
     *
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
                    throw new Exception(sprintf($this->getLanguageService()->getLL('export.ftp.upload_failed'),
                        $filename,
                        $this->lConf['ftp_server_path']), 1326906926);
                }
            } else {
                ftp_close($connection);
                throw new Exception(sprintf($this->getLanguageService()->getLL('export.ftp.login_failed'),
                    $this->lConf['ftp_server_username']), 1326906772);
            }
        } else {
            throw new Exception($this->getLanguageService()->getLL('export.ftp.connection_failed'), 1326906675);
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
     *
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
            $targetStaticLangArr = BackendUtility::getRecord('static_languages',
                $targetStaticLang['static_lang_isocode'], 'lg_iso_2');
            $sourceLang = $sourceStaticLangArr['lg_iso_2'];
            $targetLang = $targetStaticLangArr['lg_iso_2'];
            // Collect mail data
            $fromMail = $this->lConf['email_sender'];
            $fromName = $this->lConf['email_sender_name'];
            $subject = sprintf($this->getLanguageService()->getLL('email.suject.msg'), $sourceLang, $targetLang,
                $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']);
            // Assemble message body
            $message = array(
                'msg1' => $this->getLanguageService()->getLL('email.greeting.msg'),
                'msg2' => '',
                'msg3' => sprintf($this->getLanguageService()->getLL('email.new_translation_job.msg'), $sourceLang,
                    $targetLang,
                    $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']),
                'msg4' => $this->getLanguageService()->getLL('email.info.msg'),
                'msg5' => $this->getLanguageService()->getLL('email.info.import.msg'),
                'msg6' => '',
                'msg7' => $this->getLanguageService()->getLL('email.goodbye.msg'),
                'msg8' => $fromName,
                'msg9' => '--',
                'msg10' => $this->getLanguageService()->getLL('email.info.exportef_file.msg'),
                'msg11' => $xmlFileName,
            );
            if ($this->lConf['email_attachment']) {
                $message['msg3'] = sprintf($this->getLanguageService()->getLL('email.new_translation_job_attached.msg'),
                    $sourceLang, $targetLang, $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']);
            }
            $msg = implode(chr(10), $message);
            // Instantiate the mail object, set all necessary properties and send the mail
            /** @var MailMessage $mailObject */
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
     * Adds items to the ->MOD_MENU array. Used for the function menu selector.
     *
     * @return void
     */
    public function menuConfig()
    {
        $this->loadExtConf();
        $this->MOD_MENU = Array(
            'action' => array(
                '' => $this->getLanguageService()->getLL('general.action.blank.title'),
                'link' => $this->getLanguageService()->getLL('general.action.edit.link.title'),
                'inlineEdit' => $this->getLanguageService()->getLL('general.action.edit.inline.title'),
                'export_excel' => $this->getLanguageService()->getLL('general.action.export.excel.title'),
                'export_xml' => $this->getLanguageService()->getLL('general.action.export.xml.title'),
            ),
            'lang' => array(),
            'onlyChangedContent' => '',
            'check_exports' => 1,
            'noHidden' => ''
        );
        // Load system languages into menu:
        /** @var TranslationConfigurationProvider $t8Tools */
        $t8Tools = GeneralUtility::makeInstance(TranslationConfigurationProvider::class);
        $sysL = $t8Tools->getSystemLanguages();
        foreach ($sysL as $sL) {
            if ($sL['uid'] > 0 && $this->getBackendUser()->checkLanguageAccess($sL['uid'])) {
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
     */
    protected function loadExtConf()
    {
        // Load the configuration
        $this->lConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['l10nmgr']);
    }
}