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

require_once(t3lib_extMgm::extPath('l10nmgr').'models/class.tx_l10nmgr_l10nConfiguration.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/class.tx_l10nmgr_l10nBaseService.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/class.tx_l10nmgr_translationData.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/class.tx_l10nmgr_translationDataFactory.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/class.tx_l10nmgr_l10nBaseService.php');



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
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return	void
	 */
	function menuConfig()	{
		global $LANG;
		$this->MOD_MENU = Array (
			'action' => array(
				'' => '==Select Action==',
				'link' => 'Overview with links',
				'inlineEdit' => 'Inline Edit',
				'export_excel' => 'ImpExp: Excel',
				'export_xml' => 'ImpExp: XML',
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
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

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
		
		if ($l10ncfgObj->isLoaded())	{

				// Setting page id
			$this->id = $l10ncfgObj->getData('pid');
			$this->perms_clause = $GLOBALS['BE_USER']->getPagePermsClause(1);
			$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
			$access = is_array($this->pageinfo) ? 1 : 0;
			if ($this->id && $access)	{

					// Header:
				$this->content.=$this->doc->startPage($LANG->getLL('title'));
				$this->content.=$this->doc->header($LANG->getLL('title'));
				
				//create and render view to show details for the current l10nmgrcfg
				$l10nmgrconfigurationViewClassName=t3lib_div::makeInstanceClassName('tx_l10nmgr_l10ncfgDetailView');
				$l10nmgrconfigurationView= new $l10nmgrconfigurationViewClassName($l10ncfgObj);
				$this->content.=$this->doc->section('',$l10nmgrconfigurationView->render());
				
				
				$this->content.=$this->doc->divider(5);
				$this->content.=$this->doc->section('',
						t3lib_BEfunc::getFuncMenu($l10ncfgObj->getId(),"SET[lang]",$this->MOD_SETTINGS["lang"],$this->MOD_MENU["lang"],'','&srcPID='.rawurlencode(t3lib_div::_GET('srcPID'))).
						t3lib_BEfunc::getFuncMenu($l10ncfgObj->getId(),"SET[action]",$this->MOD_SETTINGS["action"],$this->MOD_MENU["action"],'','&srcPID='.rawurlencode(t3lib_div::_GET('srcPID'))).
						t3lib_BEfunc::getFuncCheck($l10ncfgObj->getId(),"SET[onlyChangedContent]",$this->MOD_SETTINGS["onlyChangedContent"],'','&srcPID='.rawurlencode(t3lib_div::_GET('srcPID'))).' New/Changed content only</br>'
					);

					// Render content:
				if (!count($this->MOD_MENU['lang']))	{
					$this->content.= $this->doc->section('ERROR','User has no access to edit any translations');
				} else {
					$this->moduleContent($l10ncfgObj);
				}

				// ShortCut
				if ($BE_USER->mayMakeShortcut())	{
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
	function printContent()	{

		$this->content.=$this->doc->endPage();
		echo $this->content;
	}
	
	function inlineEditAction($l10ncfgObj) {
		$sysLang = $this->MOD_SETTINGS["lang"];
		$service=t3lib_div::makeInstance('tx_l10nmgr_l10nBaseService');
		$info='';
		// Buttons:
		$info.= '<input type="submit" value="Save" name="saveInline" onclick="return confirm(\'You are about to create/update ALL localizations in this form? Continue?\');" />';
		$info.= '<input type="submit" value="Cancel" name="_" onclick="return confirm(\'You are about to discard any changes you made. Continue?\');" />';
		
		//simple init of translation object:
		$translationData=t3lib_div::makeInstance('tx_l10nmgr_translationData');		
		$translationData->setTranslationData(t3lib_div::_POST('translation'));
		$translationData->setLanguage($sysLang);
					
			// See, if incoming translation is available, if so, submit it
		if (t3lib_div::_POST('saveInline')) {
			$service->saveTranslation($l10ncfgObj,$translationData);						
				// reloading if submitting stuff...
			//$accum = $this->getAccumulated($tree, $l10ncfg, $sysLang);	
		}
		return $info;
	}
	function catXMLExportImportAction($l10ncfgObj) {		
		$sysLang = $this->MOD_SETTINGS["lang"];
		$service=t3lib_div::makeInstance('tx_l10nmgr_l10nBaseService');
			// Buttons:
			$info.= '<input type="submit" value="Refresh" name="_" />';
			$info.= '<input type="submit" value="Export" name="export_xml" />';
			$info.= '<input type="submit" value="Import" name="import_xml" /><input type="file" size="60" name="uploaded_import_file" />';

				// Read uploaded file:
				if (t3lib_div::_POST('import_xml') && $_FILES['uploaded_import_file']['tmp_name'] && is_uploaded_file($_FILES['uploaded_import_file']['tmp_name']))	{
					$uploadedTempFile = t3lib_div::upload_to_tempfile($_FILES['uploaded_import_file']['tmp_name']);
					
					$factory=t3lib_div::makeInstance('tx_l10nmgr_translationDataFactory');
					//TODO: catch exeption
					$translationData=$factory->getTranslationDataFromCATXMLFile($uploadedTempFile);
					$translationData->setLanguage($sysLang);
						
					t3lib_div::unlink_tempfile($uploadedTempFile);
						
						
					$service->saveTranslation($l10ncfgObj,$translationData);
					$info.='<br/><br/>'.$this->doc->icons(1).'Import done<br/><br/>';
				}	
				// If export of XML is asked for, do that (this will exit and push a file for download)
				if (t3lib_div::_POST('export_xml'))	{
					// Render the XML
					$viewClassName=t3lib_div::makeInstanceClassName('tx_l10nmgr_CATXMLView');
					$viewClass=new $viewClassName($l10ncfgObj,$sysLang);
					if ($this->MOD_SETTINGS["onlyChangedContent"]) {
						$viewClass->setModeOnlyChanged();
					}
					$this->_downloadXML($viewClass);
				}
		return $info;
	}
	
	function excelExportImportAction($l10ncfgObj) {
		$sysLang = $this->MOD_SETTINGS["lang"];
		$service=t3lib_div::makeInstance('tx_l10nmgr_l10nBaseService');
		// Buttons:
		$info.= '<input type="submit" value="Refresh" name="_" />';
		$info.= '<input type="submit" value="Export" name="export_excel" />';
		$info.= '<input type="submit" value="Import" name="import_excel" /><input type="file" size="60" name="uploaded_import_file" />';

			// Read uploaded file:
		if (t3lib_div::_POST('import_excel') && $_FILES['uploaded_import_file']['tmp_name'] && is_uploaded_file($_FILES['uploaded_import_file']['tmp_name']))	{
			$uploadedTempFile = t3lib_div::upload_to_tempfile($_FILES['uploaded_import_file']['tmp_name']);
			
			$factory=t3lib_div::makeInstance('tx_l10nmgr_translationDataFactory');
			//TODO: catch exeption
			$translationData=$factory->getTranslationDataFromExcelXMLFile($uploadedTempFile);
			$translationData->setLanguage($sysLang);
			
			t3lib_div::unlink_tempfile($uploadedTempFile);
			
			$service->saveTranslation($l10ncfgObj,$translationData);
			
			$info.='<br/><br/>'.$this->doc->icons(1).'Import done<br/><br/>';
			
		}

			// If export of XML is asked for, do that (this will exit and push a file for download)
		if (t3lib_div::_POST('export_excel'))	{
			// Render the XML
			$viewClassName=t3lib_div::makeInstanceClassName('tx_l10nmgr_excelXMLView');
			$viewClass=new $viewClassName($l10ncfgObj,$sysLang);
			$this->_downloadXML($viewClass);
		}
		return $info;
	}

	/**
	 * Creating module content
	 *
	 * @param	array		Localization Configuration record
	 * @return	void
	 */
	function moduleContent($l10ncfgObj)	{
		global $TCA,$LANG;

			// Get language to export here:
		$sysLang = $this->MOD_SETTINGS["lang"];

		
		

		switch ($this->MOD_SETTINGS["action"]) {
				case 'inlineEdit': case 'link':
					$htmlListViewClassName=t3lib_div::makeInstanceClassName('tx_l10nmgr_l10nHTMLListView');
					$htmlListView=new $htmlListViewClassName($l10ncfgObj,$sysLang);
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
					$subcontent.=$htmlListView->renderOverview();			
				break;
				case 'export_excel':
					$subheader=$LANG->getLL('export_excel');
					$subcontent=$this->excelExportImportAction($l10ncfgObj);	
				
				break;
				case 'export_xml':		// XML import/export
					$subheader=$LANG->getLL('export_xml');
					$subcontent=$this->catXMLExportImportAction($l10ncfgObj);					
				break;
				
				DEFAULT:	// Default display:
					$subcontent= '<input type="submit" value="Refresh" name="_" />';					
				break;
		} //switch block
		
		
		
		$this->content.=$this->doc->section($subheader,$subcontent);
	}
	
	

	/**
	* function sends downloadheader and calls render method of the view.
	*  it is used for excelXML and CATXML
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
	function diffCMP($old, $new)	{
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
