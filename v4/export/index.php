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
 * l10nmgr module export
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
 *   68: class tx_l10nmgr_export extends t3lib_SCbase
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
$LANG->includeLLFile('EXT:l10nmgr/export/locallang.xml');
require_once (PATH_t3lib.'class.t3lib_scbase.php');

	// autoload the mvc 
if (t3lib_extMgm::isLoaded('mvc')) {
	require_once(t3lib_extMgm::extPath('mvc').'common/class.tx_mvc_common_classloader.php');
	tx_mvc_common_classloader::loadAll();
} else {
	exit('Framework "mvc" not loaded!');
}


require_once(t3lib_extMgm::extPath('l10nmgr').'views/class.tx_l10nmgr_l10ncfgDetailView.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'views/excelXML/class.tx_l10nmgr_excelXMLView.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'views/CATXML/class.tx_l10nmgr_CATXMLView.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'views/class.tx_l10nmgr_abstractExportView.php');

require_once(t3lib_extMgm::extPath('l10nmgr').'models/configuration/class.tx_l10nmgr_models_configuration_configuration.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/configuration/class.tx_l10nmgr_models_configuration_configurationRepository.php');

require_once(t3lib_extMgm::extPath('l10nmgr').'models/class.tx_l10nmgr_l10nBaseService.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/class.tx_l10nmgr_translationData.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/class.tx_l10nmgr_translationDataFactory.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/class.tx_l10nmgr_l10nBaseService.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/class.tx_l10nmgr_CATXMLImportManager.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/class.tx_l10nmgr_mkPreviewLinkService.php');

require_once(PATH_t3lib.'class.t3lib_parsehtml_proc.php');

require_once(t3lib_extMgm::extPath('l10nmgr').'models/language/class.tx_l10nmgr_models_language_language.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/language/class.tx_l10nmgr_models_language_languageRepository.php');

require_once(t3lib_extMgm::extPath('l10nmgr').'interfaces/interface.tx_l10nmgr_interfaces_wordsCountable.php');
		
require_once(t3lib_extMgm::extPath('l10nmgr').'models/translateable/class.tx_l10nmgr_models_translateable_pageGroup.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/translateable/class.tx_l10nmgr_models_translateable_translateableElement.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/translateable/class.tx_l10nmgr_models_translateable_translateableField.php');

require_once(t3lib_extMgm::extPath('l10nmgr').'models/translateable/class.tx_l10nmgr_models_translateable_translateableInformation.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/translateable/class.tx_l10nmgr_models_translateable_translateableInformationFactory.php');


/**
 * Translation management tool
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage tx_l10nmgr
 */
class tx_l10nmgr_export extends t3lib_SCbase {

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

		$this->loadExtConf();
		$this->MOD_MENU = Array (
			'action' => array(
				''             => $LANG->getLL('general.action.blank.title'),
				'xls' => $LANG->getLL('general.action.export.xls.title'),
				'xml'   => $LANG->getLL('general.action.export.xml.title'),
			),
			'lang' => array(),
			'onlyChangedContent' => '',
			'noHidden' => ''
		);

			// Load system languages into menu:
		$t8Tools = t3lib_div::makeInstance('t3lib_transl8tools');
		$sysL = $t8Tools->getSystemLanguages();

		foreach($sysL as $sL)	{
			if ($sL['uid']>0 && $GLOBALS['BE_USER']->checkLanguageAccess($sL['uid']))	{
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
			<script language="javascript" type="text/javascript" src="' . t3lib_div::resolveBackPath($BACK_PATH . t3lib_extMgm::extRelPath('l10nmgr') . 'res/contrib/tabs.js') . '"></script>
			<link rel="stylesheet" type="text/css" href="' . t3lib_div::resolveBackPath($BACK_PATH . t3lib_extMgm::extRelPath('l10nmgr') . 'res/contrib/tabs.css') . '" />';


			// Find l10n configuration record:

		$l10nmgrCfgRepository = t3lib_div::makeInstance( 'tx_l10nmgr_models_configuration_configurationRepository' );
		$l10ncfgObj = $l10nmgrCfgRepository->findById($this->id);
		if ($l10ncfgObj instanceof tx_l10nmgr_models_configuration_configuration) {

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
				//$this->content.=$this->doc->section('',$l10nmgrconfigurationView->render());

				$this->content.=$this->doc->section($LANG->getLL('general.export.title'),
						'<table><tr><td><strong>'.$LANG->getLL('general.action.select.format.title').'</strong></td><td><strong>'.$LANG->getLL('general.action.language.select.title').'</strong></td><td><strong>'.$LANG->getLL('general.action.options.title').'</strong></td></tr>'.
												'<tr><td>'.t3lib_BEfunc::getFuncMenu($l10ncfgObj->getId(),"SET[action]",$this->MOD_SETTINGS["action"],$this->MOD_MENU["action"],'','&srcPID='.rawurlencode(t3lib_div::_GET('srcPID'))).'</td>'.
												'<td>'.t3lib_BEfunc::getFuncMenu($l10ncfgObj->getId(),"SET[lang]",$this->sysLanguage,$this->MOD_MENU["lang"],'','&srcPID='.rawurlencode(t3lib_div::_GET('srcPID'))).'</td>'.
						'<td>'.t3lib_BEfunc::getFuncCheck($l10ncfgObj->getId(),"SET[onlyChangedContent]",$this->MOD_SETTINGS["onlyChangedContent"],'','&srcPID='.rawurlencode(t3lib_div::_GET('srcPID'))) . ' ' . $LANG->getLL('export.xml.new.title') .
						t3lib_BEfunc::getFuncCheck($l10ncfgObj->getId(),"SET[noHidden]",$this->MOD_SETTINGS["noHidden"],'','&srcPID='.rawurlencode(t3lib_div::_GET('srcPID'))) . ' ' . $LANG->getLL('export.xml.noHidden.title'). '</td>'.
						'</tr></table>'
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

	function catXMLExportAction($l10ncfgObj) {
		global $LANG, $BACK_PATH, $BE_USER;
		$allowedSettingFiles = array(
			'across'     => 'acrossL10nmgrConfig.dst',
			'dejaVu'     => 'dejaVuL10nmgrConfig.dvflt',
			'memoq'     => 'MemoQ.xml',
			'sdltrados'  => 'SDLTradosTagEditor.ini',
			'sdlpassolo' => 'SDLPassolo.xfg',
		);

		$service=t3lib_div::makeInstance('tx_l10nmgr_l10nBaseService');

		$info .= '<br/>';
		//$info .= '<input type="submit" value="'.$LANG->getLL('general.action.refresh.button.title').'" name="_" /><br /><br/>';
		
		$info .= '<h4>'.$LANG->getLL('export.xml.options.title'). '</h4>';
		$_selectOptions=array('0'=>'-default-');
		$_selectOptions=$_selectOptions+$this->MOD_MENU["lang"];
		$info .= '<input type="checkbox" value="1" name="check_exports" /> ' . $LANG->getLL('export.xml.check_exports.title') . '<br />';
		$info .= '<input type="checkbox" value="1" name="no_check_xml" /> ' . $LANG->getLL('export.xml.no_check_xml.title') . '<br />';
		$info .= '<input type="checkbox" value="1" name="check_utf8" /> ' . $LANG->getLL('export.xml.checkUtf8.title') . '<br />';
		$info .= $LANG->getLL('export.xml.source-language.title') . $this->_getSelectField("export_xml_forcepreviewlanguage",'0',$_selectOptions);
		$info .= '<br /><br/>';
		$info .= '<input type="submit" value="Export" name="export_xml" /><br /><br /><br/>';
		$info .= $this->doc->header($LANG->getLL('misc.messages.title'));


		// If export of XML is asked for, do that (this will exit and push a file for download)
		if (t3lib_div::_POST('export_xml')) {
			// Save user prefs
			$BE_USER->pushModuleData('l10nmgr/cm1/checkUTF8',t3lib_div::_POST('check_utf8'));

####			
			// Render the XML
			$languageRespository 	= new  tx_l10nmgr_models_language_languageRepository();
			$targetLanguage 		= $languageRespository->findById($this->sysLanguage);
						
			$export_xml_forcepreviewlanguage=intval(t3lib_div::_POST('export_xml_forcepreviewlanguage'));
			if ($export_xml_forcepreviewlanguage > 0) {			
				$previewLanguage =  $languageRespository->findById($export_xml_forcepreviewlanguage);
			}			
			
			$factory 				= new tx_l10nmgr_models_translateable_translateableInformationFactory();
			$tranlateableInformation = $factory->create($l10ncfgObj,$l10ncfgObj->getExportPageIdCollection(),$targetLanguage,$previewLanguage);	
			
			$viewClassName=t3lib_div::makeInstanceClassName('tx_l10nmgr_CATXMLView');
			$viewClass=new $viewClassName();
			$viewClass->setL10NConfiguration($l10ncfgObj);
			$viewClass->setTargetLanguageId($this->sysLanguage);
			$viewClass->setForcedSourceLanguage($previewLanguage);
			
			
			$config 	=	$BE_USER->getModuleData('l10nmgr/export/prefs', 'prefs');
			$viewClass->setSkipXMLCheck($config['noxmlcheck']);
			$viewClass->setUseUTF8Mode($config['utf8']);
			
####
			if ($this->MOD_SETTINGS["onlyChangedContent"]) {
				$viewClass->setModeOnlyChanged();
			}
			if ($this->MOD_SETTINGS["noHidden"]) {
				$viewClass->setModeNoHidden();
			}
			//Check the export
			if ((t3lib_div::_POST('check_exports')=='1') && ($viewClass->checkExports() == FALSE)) {
				$info .= '<br />'.$this->doc->icons(2).$LANG->getLL('export.process.duplicate.message');
				$info .= $viewClass->renderExports();
			} else {
				$viewClass->saveExportInformation();
				$this->_downloadXML($viewClass);
			}
		}
		$info .= '</div>';

		return $info;
	}
	

	function excelExportAction($l10ncfgObj) {
		global $LANG, $BACK_PATH;

		$service=t3lib_div::makeInstance('tx_l10nmgr_l10nBaseService');
		$info .= '<h4>'.$LANG->getLL('export.xls.options.title'). '</h4>';
		$info .= '<input type="checkbox" value="1" name="check_exports" /> ' . $LANG->getLL('export.xml.check_exports.title') . '<br /><br />';
		// Buttons:
		$info.= '<input type="submit" value="'.$LANG->getLL('general.action.refresh.button.title').'" name="_" />';
		$info.= '<input type="submit" value="'.$LANG->getLL('general.action.export.xml.button.title').'" name="export_excel" /><br />';

		if (t3lib_div::_POST('export_excel')) {
			// If export of XML is asked for, do that (this will exit and push a file for download)
			// Render the XML
			$viewClassName=t3lib_div::makeInstanceClassName('tx_l10nmgr_excelXMLView');
			$viewClass=new $viewClassName();
			$viewClass->setL10NConfiguration($l10ncfgObj);
			$viewClass->setTargetLanguageId($this->sysLanguage);

			//Check the export
			if ((t3lib_div::_POST('check_exports')=='1') && ($viewClass->checkExports() == FALSE)) {
				$info .= '<br />'.$this->doc->icons(2).$LANG->getLL('export.process.duplicate.message');
				$info .= $viewClass->renderExports();
			} else {
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
		global $TCA,$LANG,$BE_USER;

		switch ($this->MOD_SETTINGS["action"]) {
			case 'xls':
				$subheader  = $LANG->getLL('export_excel');
				$subcontent = $this->excelExportAction($l10ncfgObj);
			break;

			case 'xml':		// XML import/export
				$prefs['utf8']=t3lib_div::_POST('check_utf8');
				$prefs['noxmlcheck']=t3lib_div::_POST('no_check_xml');
				$BE_USER->pushModuleData('l10nmgr/export/prefs', $prefs);

				$subheader  = $LANG->getLL('export_xml');
				$subcontent = $this->catXMLExportAction($l10ncfgObj);
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

	/**
	 * The function loadExtConf loads the extension configuration.
	 * @return      void
	 *
	*/
	function loadExtConf() {
		// Load the configuration
		$this->lConf = unserialize( $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['l10nmgr'] );
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/export/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/export/index.php']);
}


// Make instance:
$SOBE = t3lib_div::makeInstance('tx_l10nmgr_export');
$SOBE->init();

$SOBE->main();
$SOBE->printContent();
?>
