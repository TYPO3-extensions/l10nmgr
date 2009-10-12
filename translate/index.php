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
 * l10nmgr module translate
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
 *   68: class tx_l10nmgr_translate extends t3lib_SCbase
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
$LANG->includeLLFile('EXT:l10nmgr/translate/locallang.xml');
require_once (PATH_t3lib.'class.t3lib_scbase.php');

//$BE_USER->modAccess($MCONF,1);

	// autoload the mvc 
if (t3lib_extMgm::isLoaded('mvc')) {
	require_once(t3lib_extMgm::extPath('mvc').'common/class.tx_mvc_common_classloader.php');
	tx_mvc_common_classloader::loadAll();
} else {
	exit('Framework "mvc" not loaded!');
}


require_once(t3lib_extMgm::extPath('l10nmgr').'views/class.tx_l10nmgr_l10ncfgDetailView.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'views/class.tx_l10nmgr_l10nHTMLListView.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'views/class.tx_l10nmgr_abstractExportView.php');

require_once(t3lib_extMgm::extPath('l10nmgr').'models/configuration/class.tx_l10nmgr_models_configuration_configuration.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/configuration/class.tx_l10nmgr_models_configuration_configurationRepository.php');

require_once(t3lib_extMgm::extPath('l10nmgr').'models/class.tx_l10nmgr_l10nBaseService.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/class.tx_l10nmgr_translationData.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/class.tx_l10nmgr_translationDataFactory.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/class.tx_l10nmgr_l10nBaseService.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/class.tx_l10nmgr_mkPreviewLinkService.php');

require_once(PATH_t3lib.'class.t3lib_parsehtml_proc.php');

require_once(t3lib_extMgm::extPath('l10nmgr').'models/language/class.tx_l10nmgr_models_language_language.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/language/class.tx_l10nmgr_models_language_languageRepository.php');

require_once(t3lib_extMgm::extPath('l10nmgr').'interface/interface.tx_l10nmgr_interface_wordsCountable.php');
		
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
//class tx_l10nmgr_module4 extends t3lib_SCbase {
class tx_l10nmgr_translate extends t3lib_SCbase {

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
				'inlineEdit'   => $LANG->getLL('general.action.edit.inline.title'),
				'link'         => $LANG->getLL('general.action.edit.link.title'),
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
				function toggle_visibility(id) {
       				var e = document.getElementById(id);
       				if(e.style.display == \'block\')
          				e.style.display = \'none\';
			       	else
			          e.style.display = \'block\';
			    }				
			</script>
			<script language="javascript" type="text/javascript" src="' . t3lib_div::resolveBackPath($BACK_PATH . t3lib_extMgm::extRelPath('l10nmgr') . 'res/contrib/tabs.js') . '"></script>
			<link rel="stylesheet" type="text/css" href="' . t3lib_div::resolveBackPath($BACK_PATH . t3lib_extMgm::extRelPath('l10nmgr') . 'res/contrib/tabs.css') . '" />';

		$params 	= t3lib_div::_GP('l10nmgr');
		$cfgId = intval($params['configurationId']);
		
			
			// Find l10n configuration record:

		$l10nmgrCfgRepository = t3lib_div::makeInstance( 'tx_l10nmgr_models_configuration_configurationRepository' );
		$l10ncfgObj = $l10nmgrCfgRepository->findById($cfgId);
		
		if ($l10ncfgObj instanceof tx_l10nmgr_models_configuration_configuration) {
			/* @var $l10ncfgObj tx_l10nmgr_models_configuration_configuration */
			$this->id = (intval(t3lib_div::_GP('id')) != 0) ? intval(t3lib_div::_GP('id'))  : $l10ncfgObj->getPid();
			
			// Setting page id
			$this->perms_clause = $GLOBALS['BE_USER']->getPagePermsClause(1);
			$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
			$access = is_array($this->pageinfo) ? 1 : 0;
			
			if ($this->id && $access)	{

					// Header:
				$this->content.= $this->doc->startPage($LANG->getLL('general.title'));
				$this->content.= $this->doc->header($LANG->getLL('general.title'));

				//$this->content.=$this->doc->divider(15);
				$this->content.= $this->doc->section($LANG->getLL('general.action.edit.inline.title'), 
						'<table><tr><td><strong>'.$LANG->getLL('general.action.select.title').'</strong></td><td><strong>'.$LANG->getLL('general.action.language.select.title').'</strong></td><td><strong>'.$LANG->getLL('general.action.options.title').'</strong></td></tr>'.
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

				$this->content.= $this->doc->spacer(10);
				//create and render view to show details for the current l10nmgrcfg
				$l10nmgrconfigurationViewClassName=t3lib_div::makeInstanceClassName('tx_l10nmgr_l10ncfgDetailView');
				$l10nmgrconfigurationView= new $l10nmgrconfigurationViewClassName($l10ncfgObj, $this->doc);

				$this->content.=$this->doc->section('',$l10nmgrconfigurationView->render());
				
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
		$info.= '<input type="submit" value="'.$LANG->getLL('general.action.save.button.title').'" name="saveInline" onclick="return confirm(\''.$LANG->getLL('inlineedit.save.alert.title').'\');" /> ';
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

	/**
	 * Creating module content
	 *
	 * @param	array		Localization Configuration record
	 * @return	void
	 */
	function moduleContent($l10ncfgObj) {
		global $TCA,$LANG,$BE_USER;

		switch ($this->MOD_SETTINGS["action"]) {
			case 'inlineEdit': case 'link':
				$htmlListViewClassName=t3lib_div::makeInstanceClassName('tx_l10nmgr_l10nHTMLListView');
				$htmlListView=new $htmlListViewClassName();
				$htmlListView->setL10NConfiguration($l10ncfgObj);
				$htmlListView->setTargetLanguageId($this->sysLanguage);
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
				if ($this->MOD_SETTINGS["noHidden"]) {
					$htmlListView->setModeNoHidden();
				}
				if ($this->MOD_SETTINGS["action"]=='link') {
					$htmlListView->setModeShowEditLinks();
				}
				$subcontent .= $htmlListView->renderOverview();
			break;

			DEFAULT:	// Default display:
				$subcontent = '<input type="submit" value="'.$LANG->getLL('general.action.refresh.button.title').'" name="_" />';
			break;
		} //switch block

		$this->content .= $this->doc->section($subheader,$subcontent);
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


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/translate/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/translate/index.php']);
}


// Make instance:
$SOBE = t3lib_div::makeInstance('tx_l10nmgr_translate');
//$SOBE = t3lib_div::makeInstance('tx_l10nmgr_module4');
$SOBE->init();

$SOBE->main();
$SOBE->printContent();
?>
