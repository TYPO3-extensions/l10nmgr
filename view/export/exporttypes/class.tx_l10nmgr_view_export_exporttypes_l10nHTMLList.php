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


require_once(t3lib_extMgm::extPath('l10nmgr').'view/export/class.tx_l10nmgr_view_export_abstractExportView.php');

/**
 * l10nHTMLListView:
 * 	renders accumulated informations for the browser:
 *	- Table with inline editing / links  etc...
 *
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @author	Daniel Pötzinger <development@aoemedia.de>
 *
 * @package TYPO3
 * @subpackage tx_l10nmgr
 */
class tx_l10nmgr_view_export_exporttypes_l10nHTMLList extends tx_l10nmgr_view_export_abstractExportView {

	
	/**
	 * The default template of the view
	 * @var sting
	 */
	protected $defaultTemplate = 'EXT:l10nmgr/templates/html/html.php';

	/**
	 * @var string
	 */
	protected $export_type = 'html';

	//internal flags:
	protected $modeWithInlineEdit=FALSE;
	
	protected $modeShowEditLinks=FALSE;

	public function __construct() {
		global $BACK_PATH;
		$this->doc = t3lib_div::makeInstance('noDoc');
		$this->doc->backPath = $BACK_PATH;
	}

	/**
	 * (non-PHPdoc)
	 * @see view/export/tx_l10nmgr_view_export_abstractExportView#getExporttypePrefix()
	 */
	protected function getExporttypePrefix(){
		return 'html';
	}
	
	/**
	 * 
	 */
	public function setModeWithInlineEdit() {
		$this->modeWithInlineEdit=TRUE;
	}
	
	/**
	 * 
	 **/
	public function setModeShowEditLinks() {
		$this->modeShowEditLinks=TRUE;
	}
	
	/**
	 * 
	 *
	 */
	public function setSelectedItem($table,$uid){
		$this->selectedItem = $table.':'.$uid;
	}
	
	/**
	 * Method to determine if an item is selected or not. 
	 *
	 * @param string tablename
	 * @param int record uid
	 */
	public function isSelectedItem($table,$uid){
		return ($this->selectedItem == $table.':'.$uid);
	}
	
	/**
	 * 
	 */
	protected function getEditLink($translateableElement){
		$table 		= $translateableElement->getTableName();
		$elementUid = $translateableElement->getUid();
						
		if ($this->modeShowEditLinks && $translateableElement->getTranslateableFields()->count() > 0)	{
			$uidString = $translateableElement->getTranslateableFields()->offsetGet(0)->getUidValue();
			
			if (substr($uidString,0,3)!=='NEW')	{
				$translationUid = $translateableElement->getTranslationUid($this->getTargetLanguageId());
				$editLink = ' - <a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick('&edit['.$table.']['.$translationUid.']=edit',$this->doc->backPath)).'"><em>['.$this->labels->get('render_overview.clickedit.message').']</em></a>';
			} else {
				$editLink = ' - <a href="'.htmlspecialchars($this->doc->issueCommand('&cmd['.$table.']['.$elementUid.'][localize]='.$this->getTargetLanguageId())).'"><em>['.$this->labels->get('render_overview.clicklocalize.message').']</em></a>';
			}	
		}else{
			$editLink = '';
		}
		
		return $editLink;
	}
	
	/**
	 * Determines an returns a diff string for a translateable field. 
	 *
	 */
	protected function getDiffString($translateableField){
		if ($translateableField->getUidValue()==='NEW')	{
			$diff = $this->labels->get('render_overview.new.message');
		} elseif ($translateableField->getDiffDefaultValue() == '') {
			$diff = $this->labels->get('render_overview.nodiff.message');
		} elseif (!$translateableField->isChanged())	{
			$diff = $this->labels->get('render_overview.nochange.message');
		} else {
			$diff = $this->diffCMP($translateableField->getDiffDefaultValue(),$translateableField->getDefaultValue());
		}		
		
		return $diff;
	}
	
	/**
	 * This method is used to determine the flags of new unknown and elements without changes. 
	 *
	 *
	 */
	protected function getFlagsForElement($translateableElement){
		$flags = array();
		
		foreach($translateableElement->getTranslateableFields() as $translateableField){
			if ($translateableField->getUidValue()==='NEW')	{
				$flags['new']++;
			} elseif ($translateableField->getDiffDefaultValue() == '') {
				$flags['unknown']++;
			} elseif (!$translateableField->isChanged())	{
				$flags['noChange']++;
			} else {
				$flags['update']++;
			}	
		}
		
		return $flags;
	}
	
	/**
	 * 
	 * (non-PHPdoc)
	 * @see view/export/tx_l10nmgr_view_export_abstractExportView#renderPageGroups()
	 */
	protected function renderPageGroups(){}
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/views/class.tx_l10nmgr_view_export_exporttypes_l10nHTMLList.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/views/class.tx_l10nmgr_view_export_exporttypes_l10nHTMLList.php']);
}

?>