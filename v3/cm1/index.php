<?php
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
 * l10nmgr module cm1
 *
 * @author	Kasper Skårhøj <kasperYYYY@typo3.com>
 * @author	Daniel Zielinski <d.zielinski@l10ntech.de>
 * @author	Daniel Pötzinger <poetzinger@aoemedia.de>
 * @author	Fabian Seltmann <fs@marketing-factory.de>
 * @author	Andreas Otto <andreas.otto@dkd.de>
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


	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require ('conf.php');
require ($BACK_PATH.'init.php');
require ($BACK_PATH.'template.php');
$LANG->includeLLFile('EXT:l10nmgr/cm1/locallang.xml');
require_once (PATH_t3lib.'class.t3lib_scbase.php');

require_once(t3lib_extMgm::extPath('l10nmgr').'views/class.tx_l10nmgr_l10ncfgDetailView.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'views/class.tx_l10nmgr_l10nHTMLListView.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'views/excelXML/class.tx_l10nmgr_excelXMLView.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'views/CATXML/class.tx_l10nmgr_CATXMLView.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'views/class.tx_l10nmgr_abstractExportView.php');

require_once(t3lib_extMgm::extPath('l10nmgr').'models/class.tx_l10nmgr_l10nConfiguration.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/class.tx_l10nmgr_l10nBaseService.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/class.tx_l10nmgr_translationData.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/class.tx_l10nmgr_translationDataFactory.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/class.tx_l10nmgr_l10nBaseService.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/class.tx_l10nmgr_CATXMLImportManager.php');

require_once(PATH_t3lib.'class.t3lib_parsehtml_proc.php');

/**
 * Translation management tool
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage tx_l10nmgr
 */
class tx_l10nmgr_cm1 extends t3lib_SCbase {

	var $flexFormDiffArray = array();	// Internal

	/**
	 * @var	integer		Default language to export
	 */
	var $sysLanguage = '0';

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return	void
	 */
	function menuConfig() {
		global $LANG;

		$this->MOD_MENU = Array (
			'action' => array(
				''             => $LANG->getLL('general.action.blank.title'),
				'link'         => $LANG->getLL('general.action.edit.link.title'),
				'inlineEdit'   => $LANG->getLL('general.action.edit.inline.title'),
				'export_excel' => $LANG->getLL('general.action.export.excel.title'),
				'export_xml'   => $LANG->getLL('general.action.export.xml.title'),
			),
			'lang' => array(),
			'onlyChangedContent' => ''
		);

			// Load system languages into menu:
		$t8Tools = t3lib_div::makeInstance('t3lib_transl8tools');
		$sysL = $t8Tools->getSystemLanguages();
		foreach($sysL as $sL)	{
			if ($sL['uid']>0 && $GLOBALS['BE_USER']->checkLanguageAccess($sL['uid']))	{
				$this->MOD_MENU['lang'][$sL['uid']] = $sL['title'];
			}
		}

		parent::menuConfig();
	}

	/**
	 * Main function of the module. Write the content to
	 *
	 * @return	void
	 */
	function main() {
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

			// Get language to export/import
		$this->sysLanguage = $this->MOD_SETTINGS["lang"];

			// Draw the header.
		$this->doc = t3lib_div::makeInstance('noDoc');
		$this->doc->backPath = $BACK_PATH;
		$this->doc->form='<form action="" method="post" enctype="'.$TYPO3_CONF_VARS['SYS']['form_enctype'].'">';

			// JavaScript
		$this->doc->JScode = '
			<script language="javascript" type="text/javascript">
				script_ended = 0;
				function jumpToUrl(URL)	{
					document.location = URL;
				}
			</script>
		';


			// Find l10n configuration record:
		$l10ncfgObj=t3lib_div::makeInstance('tx_l10nmgr_l10nConfiguration');
		$l10ncfgObj->load($this->id);

		if ($l10ncfgObj->isLoaded()) {

				// Setting page id
			$this->id = $l10ncfgObj->getData('pid');
			$this->perms_clause = $GLOBALS['BE_USER']->getPagePermsClause(1);
			$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
			$access = is_array($this->pageinfo) ? 1 : 0;
			if ($this->id && $access)	{

					// Header:
				$this->content.=$this->doc->startPage($LANG->getLL('general.title'));
				$this->content.=$this->doc->header($LANG->getLL('general.title'));
				
				//create and render view to show details for the current l10nmgrcfg
				$l10nmgrconfigurationViewClassName=t3lib_div::makeInstanceClassName('tx_l10nmgr_l10ncfgDetailView');
				$l10nmgrconfigurationView= new $l10nmgrconfigurationViewClassName($l10ncfgObj, $this->doc);
				$this->content.=$this->doc->section('',$l10nmgrconfigurationView->render());

				$this->content.=$this->doc->divider(15);
				$this->content.=$this->doc->section($LANG->getLL('general.export.choose.action.title'),
						t3lib_BEfunc::getFuncMenu($l10ncfgObj->getId(),"SET[lang]",$this->sysLanguage,$this->MOD_MENU["lang"],'','&srcPID='.rawurlencode(t3lib_div::_GET('srcPID'))).
						t3lib_BEfunc::getFuncMenu($l10ncfgObj->getId(),"SET[action]",$this->MOD_SETTINGS["action"],$this->MOD_MENU["action"],'','&srcPID='.rawurlencode(t3lib_div::_GET('srcPID'))).
						t3lib_BEfunc::getFuncCheck($l10ncfgObj->getId(),"SET[onlyChangedContent]",$this->MOD_SETTINGS["onlyChangedContent"],'','&srcPID='.rawurlencode(t3lib_div::_GET('srcPID'))) . ' ' . $LANG->getLL('export.xml.new.title') . '</br>'
					);

					// Render content:
				if (!count($this->MOD_MENU['lang'])) {
					$this->content.= $this->doc->section('ERROR',$LANG->getLL('general.access.error.title'));
				} else {
					$this->moduleContent($l10ncfgObj);
				}

				// ShortCut
				if ($BE_USER->mayMakeShortcut()) {
					$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
				}
			}
		}

		$this->content.=$this->doc->spacer(10);
	}

	/**
	 * Printing output content
	 *
	 * @return	void
	 */
	function printContent() {

		$this->content.=$this->doc->endPage();
		echo $this->content;
	}

	function inlineEditAction($l10ncfgObj) {
		global $LANG, $BACK_PATH;

		$service=t3lib_div::makeInstance('tx_l10nmgr_l10nBaseService');
		$info='';
		// Buttons:
		$info.= '<input type="submit" value="'.$LANG->getLL('general.action.save.button.title').'" name="saveInline" onclick="return confirm(\''.$LANG->getLL('inlineedit.save.alert.title').'\');" />';
		$info.= '<input type="submit" value="'.$LANG->getLL('general.action.cancel.button.title').'" name="_" onclick="return confirm(\''.$LANG->getLL('inlineedit.cancel.alert.title').'\');" />';

		//simple init of translation object:
		$translationData=t3lib_div::makeInstance('tx_l10nmgr_translationData');
		$translationData->setTranslationData(t3lib_div::_POST('translation'));
		$translationData->setLanguage($this->sysLanguage);

			// See, if incoming translation is available, if so, submit it
		if (t3lib_div::_POST('saveInline')) {
			$service->saveTranslation($l10ncfgObj,$translationData);
		}
		return $info;
	}


	function _getSelectField($elementName,$currentValue,$menuItems) {

		foreach($menuItems as $value => $label)	{
			$options[] = '<option value="'.htmlspecialchars($value).'"'.(!strcmp($currentValue,$value)?' selected="selected"':'').'>'.
						t3lib_div::deHSCentities(htmlspecialchars($label)).
						'</option>';
		}

		if (count($options)) {
			return '
				<select name="'.$elementName.'" >
					'.implode('
					',$options).'
				</select>
						';
		}
	}

	function catXMLExportImportAction($l10ncfgObj) {
		global $LANG, $BACK_PATH, $BE_USER;
		$allowedSettingFiles = array(
			'across'     => 'acrossL10nmgrConfig.dst',
			'dejaVu'     => 'dejaVuL10nmgrConfig.dvflt',
			'sdltrados'  => 'SDLTradosTagEditor.ini',
			'sdlpassolo' => 'SDLPassolo.xfg',
		);

		$service=t3lib_div::makeInstance('tx_l10nmgr_l10nBaseService');

		// Buttons:
		// Temporary links to settings files. Should be changed when download of L10N packages is available.
		$info .= '<br/>';
		$info .= '<input type="submit" value="'.$LANG->getLL('general.action.refresh.button.title').'" name="_" /><br />';
		$info .= '<br />'.$this->doc->header($LANG->getLL('file.settings.downloads.title'));
		$info .= $this->doc->icons(1) . 
			   $LANG->getLL('file.settings.available.title');

		for( reset($allowedSettingFiles); list($settingId, $settingFileName) = each($allowedSettingFiles); ) {

			$currentFile = t3lib_div::resolveBackPath($BACK_PATH . t3lib_extMgm::extRelPath('l10nmgr') . 'settings/' . $settingFileName);

			if ( is_file($currentFile) && is_readable($currentFile) ) {

				$size = t3lib_div::formatSize((int)filesize($currentFile), ' Bytes| KB| MB| GB');
				$info .= ' <a href="' . t3lib_div::rawUrlEncodeFP($currentFile) . '" title="' . $LANG->getLL('file.settings.download.title') . '" target="_blank">' . $LANG->getLL('file.settings.' . $settingId . '.title') . ' (' . $size . ')' . '</a> | ';
			}
		}

		$info .= '<br /><br />'.$this->doc->header($LANG->getLL('export.xml.headline.title'));
		$_selectOptions=array('0'=>'-default-');
		$_selectOptions=$_selectOptions+$this->MOD_MENU["lang"];
		$info .= '<input type="checkbox" value="1" name="check_exports" /> ' . $LANG->getLL('export.xml.check_exports.title') . '<br />';
		$info .= '<input type="checkbox" value="1" name="check_utf8" /> ' . $LANG->getLL('export.xml.checkUtf8.title') . '<br />';
		$info .= $LANG->getLL('export.xml.source-language.title') . $this->_getSelectField("export_xml_forcepreviewlanguage",'0',$_selectOptions);
		$info .= '<br />';
		$info .= '<input type="submit" value="Export" name="export_xml" /><br /><br /><br/>';
		$info .= $this->doc->header($LANG->getLL('import.xml.headline.title'));
		$info .= '<input type="checkbox" value="1" name="import_oldformat" /> ' . $LANG->getLL('import.xml.old-format.title') . '<br />';
		$info .= '<input type="file" size="60" name="uploaded_import_file" /><br /><input type="submit" value="Import" name="import_xml" /><br /><br /> ';
		$info .= $this->doc->header($LANG->getLL('misc.messages.title'));

		// Read uploaded file:
		if (t3lib_div::_POST('import_xml') && $_FILES['uploaded_import_file']['tmp_name'] && is_uploaded_file($_FILES['uploaded_import_file']['tmp_name']))	{
			$uploadedTempFile = t3lib_div::upload_to_tempfile($_FILES['uploaded_import_file']['tmp_name']);
			$factory=t3lib_div::makeInstance('tx_l10nmgr_translationDataFactory');

			if (t3lib_div::_POST('import_oldformat')=='1') {
				//Support for the old Format of XML Import (without pagegrp element)
				$info.='Import uses the old Format without pagegrp element and checks!';
				$translationData=$factory->getTranslationDataFromOldFormatCATXMLFile($uploadedTempFile);
				$translationData->setLanguage($sysLang);
				$service->saveTranslation($l10ncfgObj,$translationData);
				$info.='<br/><br/>'.$this->doc->icons(1).'Import done<br/><br/>(command-count:'.$service->lastTCEMAINCommandsCount.')';
			}
			else {
				// Relevant processing of XML Import with the help of the Importmanager
				$importManagerClass=t3lib_div::makeInstanceClassName('tx_l10nmgr_CATXMLImportManager');
				$importManager=new $importManagerClass($uploadedTempFile,$this->sysLanguage);
				if ($importManager->parseAndCheckXMLFile()===false) {
					$info.='<br/><br/>' . $this->doc->header($LANG->getLL('import.error.title')) .$importManager->getErrorMessages();
				}
				else {
					$translationData=$factory->getTranslationDataFromCATXMLNodes($importManager->getXMLNodes());
					$translationData->setLanguage($this->sysLanguage);
					unset($importManager);
					$service->saveTranslation($l10ncfgObj,$translationData);
					$info.='<br/><br/>'.$this->doc->icons(1).'Import done<br/><br/>(command-count:'.$service->lastTCEMAINCommandsCount.')';
				}
			}
			t3lib_div::unlink_tempfile($uploadedTempFile);
		}
		// If export of XML is asked for, do that (this will exit and push a file for download)
		if (t3lib_div::_POST('export_xml')) {
			// Save user prefs
			$BE_USER->pushModuleData('l10nmgr/cm1/checkUTF8',t3lib_div::_POST('check_utf8'));
					
			// Render the XML
			$viewClassName=t3lib_div::makeInstanceClassName('tx_l10nmgr_CATXMLView');
			$viewClass=new $viewClassName($l10ncfgObj,$this->sysLanguage);
			$export_xml_forcepreviewlanguage=intval(t3lib_div::_POST('export_xml_forcepreviewlanguage'));
			if ($export_xml_forcepreviewlanguage > 0) {
				$viewClass->setForcedSourceLanguage($export_xml_forcepreviewlanguage);
			}
			if ($this->MOD_SETTINGS["onlyChangedContent"]) {
				$viewClass->setModeOnlyChanged();
			}
			//Check the export
			if ((t3lib_div::_POST('check_exports')=='1') && ($viewClass->checkExports() == FALSE)) {
				echo($LANG->getLL('export.process.duplicate.message'));
				exit;	
			} else {
				$viewClass->saveExportInformation();
				$this->_downloadXML($viewClass);
			}
		}
				
		return $info;
	}

	function excelExportImportAction($l10ncfgObj) {
		global $LANG, $BACK_PATH;

		$service=t3lib_div::makeInstance('tx_l10nmgr_l10nBaseService');
		// Buttons:
		$info.= '<input type="submit" value="'.$LANG->getLL('general.action.refresh.button.title').'" name="_" />';
		$info.= '<input type="submit" value="'.$LANG->getLL('general.action.export.xml.button.title').'" name="export_excel" />';
		$info.= '<input type="submit" value="'.$LANG->getLL('general.action.import.xml.button.title').'" name="import_excel" /><input type="file" size="60" name="uploaded_import_file" />';
		$info .= '<br /><br /><input type="checkbox" value="1" name="check_exports" /> ' . $LANG->getLL('export.xml.check_exports.title') . '<br />';

			// Read uploaded file:
		if (t3lib_div::_POST('import_excel') && $_FILES['uploaded_import_file']['tmp_name'] && is_uploaded_file($_FILES['uploaded_import_file']['tmp_name'])) {
			$uploadedTempFile = t3lib_div::upload_to_tempfile($_FILES['uploaded_import_file']['tmp_name']);

			$factory=t3lib_div::makeInstance('tx_l10nmgr_translationDataFactory');
			//TODO: catch exeption
			$translationData=$factory->getTranslationDataFromExcelXMLFile($uploadedTempFile);
			$translationData->setLanguage($this->sysLanguage);

			t3lib_div::unlink_tempfile($uploadedTempFile);

			$service->saveTranslation($l10ncfgObj,$translationData);

			$info.='<br/><br/>'.$this->doc->icons(1).$LANG->getLL('import.success.message').'<br/><br/>';
		}

			// If export of XML is asked for, do that (this will exit and push a file for download)
		if (t3lib_div::_POST('export_excel')) {
			
			// Render the XML
			$viewClassName=t3lib_div::makeInstanceClassName('tx_l10nmgr_excelXMLView');
			$viewClass=new $viewClassName($l10ncfgObj,$this->sysLanguage);
			
			//Check the export
			if ((t3lib_div::_POST('check_exports')=='1') && ($viewClass->checkExports() == FALSE)) {
				echo($LANG->getLL('export.process.duplicate.message'));
				exit;	
			}else{
				$viewClass->saveExportInformation();
				$this->_downloadXML($viewClass);	
			}
		}

		return $info;
	}

	/**
	 * Creating module content
	 *
	 * @param	array		Localization Configuration record
	 * @return	void
	 */
	function moduleContent($l10ncfgObj) {
		global $TCA,$LANG;

		switch ($this->MOD_SETTINGS["action"]) {
			case 'inlineEdit': case 'link':
				$htmlListViewClassName=t3lib_div::makeInstanceClassName('tx_l10nmgr_l10nHTMLListView');
				$htmlListView=new $htmlListViewClassName($l10ncfgObj,$this->sysLanguage);
				$subheader=$LANG->getLL('inlineEdit');

				if ($this->MOD_SETTINGS["action"]=='inlineEdit') {
					$subheader=$LANG->getLL('link');
					$subcontent=$this->inlineEditAction($l10ncfgObj);
					$htmlListView->setModeWithInlineEdit();
				}
				// Render the module content (for all modes):
				//*******************************************

				if ($this->MOD_SETTINGS["onlyChangedContent"]) {
					$htmlListView->setModeOnlyChanged();
				}
				if ($this->MOD_SETTINGS["action"]=='link') {
					$htmlListView->setModeShowEditLinks();
				}
				$subcontent .= $htmlListView->renderOverview();
			break;

			case 'export_excel':
				$subheader  = $LANG->getLL('export_excel');
				$subcontent = $this->excelExportImportAction($l10ncfgObj);
			break;

			case 'export_xml':		// XML import/export
				$subheader  = $LANG->getLL('export_xml');
				$subcontent = $this->catXMLExportImportAction($l10ncfgObj);
			break;

			DEFAULT:	// Default display:
				$subcontent = '<input type="submit" value="'.$LANG->getLL('general.action.refresh.button.title').'" name="_" />';
			break;
		} //switch block

		$this->content .= $this->doc->section($subheader,$subcontent);
	}

	/**
	 * function sends downloadheader and calls render method of the view.
	 * it is used for excelXML and CATXML
	 **/
	function _downloadXML($xmlView) {
		// Setting filename:
		$filename = $xmlView->getFileName();
		$mimeType = 'text/xml';
		$this->_sendDownloadHeader($mimeType,$filename);
		echo $xmlView->render();
		exit;
	}

	function _sendDownloadHeader($mimeType,$filename) {

			// Creating output header:
		Header('Charset: utf-8');
		Header('Content-Type: '.$mimeType);
		Header('Content-Disposition: attachment; filename='.$filename);
	}

	/**
	 * Diff-compare markup
	 *
	 * @param	string		Old content
	 * @param	string		New content
	 * @return	string		Marked up string.
	 */
	function diffCMP($old, $new) {
			// Create diff-result:
		$t3lib_diff_Obj = t3lib_div::makeInstance('t3lib_diff');
		return $t3lib_diff_Obj->makeDiffDisplay($old,$new);
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/cm1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/cm1/index.php']);
}


// Make instance:
$SOBE = t3lib_div::makeInstance('tx_l10nmgr_cm1');
$SOBE->init();

$SOBE->main();
$SOBE->printContent();
?>
