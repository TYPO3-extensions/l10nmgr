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

require_once(t3lib_extMgm::extPath('l10nmgr').'models/class.tx_l10nmgr_l10nAccumulatedInformations.php');


/**
 * l10nConfiguration
 *  Capsulate a 10ncfg record.
 *	Has factory method to get a relevant AccumulatedInformationsObject
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @author	Daniel Pötzinger <ext@aoemedia.de>
 * @author  Timo Schmidt <schmidt@aoemedia.de>
 * 
 * @package TYPO3
 * @subpackage tx_l10nmgr
 */
class tx_l10nmgr_l10nConfiguration {


	/**
	 * @todo refactorm needs to be private
	 * @var unknown_type
	 */
	public 	$l10ncfg=array();
	private $tree;
	private $exportPageIdCollection;

	/**
	* loads internal array with l10nmgrcfg record
	* @param int	$id		Id of the cfg record
	* @return void
	**/
	public function load($id) {
		$this->l10ncfg = t3lib_BEfunc::getRecord('tx_l10nmgr_cfg', $id);
	}

	/**
	* checks if configuration is valid
	*
	* @return boolean
	**/
	public function isLoaded() {
		// array must have values also!
		if (is_array($this->l10ncfg) && (!empty($this->l10ncfg))) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	* get a field of the current cfgr record
	* @param string	$key		Key of the field. E.g. title,uid...
	* @return string	Value of the field
	**/
	function getData($key) {
		return $this->l10ncfg[$key];
	}

	/**
	* get uid field
	*
	* @return Int
	**/
	public function getId() {
		return $this->getData('uid');
	}
	
	/**
	 * Returns the Flexformdiff stored in the configuration record
	 *
	 * @return string
	 */
	public function getFlexFormDiff(){
		return $this->getData('flexformdiff');
	}
	
	/**
	 * Returns the configurationoption to include FCEs with defaultLanguage or not
	 *
	 * @return boolean
	 */
	public function getIncludeFCEWithDefaultLanguage(){
		return $this->getData('incfcewithdefaultlanguage'); 
	}
	
	/**
	 * Returns a list of relavant tables for this export
	 *
	 * @return string commaseperated list of tables
	 */
	public function getTableList(){
		return $this->getData('tablelist');
	}

	/**
	 * Returns the list of configured tables as array
	 *
	 * @return array of tables
	 */
	public function getTableArray(){
		return explode(',',$this->getTableList());
	}
	
	/**
	 * Each l10nconfig can define an include and exclude list. This method returns the excludeList as arrayM
	 *
	 * @return array 
	 */
	public function getExcludeArray(){
		$excludeArray = array_flip(array_unique(t3lib_div::trimExplode(',',$this->getData('exclude'),1)));
		
		return $excludeArray;
	}
	
	/**
	 * Each l10nconfig can define an includeList this method returns the includeList as array.
	 *
	 * @return array
	 */
	public function getIncludeArray(){
		$includeArray = array_flip(array_unique(t3lib_div::trimExplode(',',$this->getData('include'),1)));		
		return $includeArray;
	}
	
	/**
	* Factory method to create AccumulatedInformations Object (e.g. build tree etc...) (Factorys should have all dependencies passed as parameter)
	*
	* @param int	$overrideStartingPoint		optional override startingpoint  TODO!
	* @return tx_l10nmgr_l10nAccumulatedInformations
	* @deprecated 
	**/
	public function getL10nAccumulatedInformationsObjectForLanguage($sysLang,$overrideStartingPoint='') {

		$l10ncfg=$this->l10ncfg;
		// Showing the tree:
		// Initialize starting point of page tree:
		$treeStartingPoint = intval($l10ncfg['depth']==-1 ? t3lib_div::_GET('srcPID') : $l10ncfg['pid']);
		$treeStartingRecord = t3lib_BEfunc::getRecordWSOL('pages', $treeStartingPoint);
		$depth = $l10ncfg['depth'];

		// Initialize tree object:
		$tree = t3lib_div::makeInstance('t3lib_pageTree');
		$tree->init('AND '.$GLOBALS['BE_USER']->getPagePermsClause(1));
		$tree->addField('l18n_cfg');

		// Creating top icon; the current page
		$HTML = t3lib_iconWorks::getIconImage('pages', $treeStartingRecord, $GLOBALS['BACK_PATH'],'align="top"');
		$tree->tree[] = array(
			'row' => $treeStartingRecord,
			'HTML'=> $HTML
		);
		// Create the tree from starting point:
		if ($depth>0)	$tree->getTree($treeStartingPoint, $depth, '');

		//now create and init accum Info object:
		$accumObjName=t3lib_div::makeInstanceClassName('tx_l10nmgr_l10nAccumulatedInformations');
		$accumObj=new $accumObjName($tree,$l10ncfg,$sysLang);

		return $accumObj;
	}
	
	/**
	 * Returns a collection of pageids which need to be exported
	 *
	 * @return ArrayObject
	 */
	public function getExportPageIdCollection(){
		
		$this->exportPageIdCollection = new ArrayObject();
		$tree = $this->getExportTree();
		
		//$tree->tree contains pages of the tree
		foreach($tree->tree as $treeitem){
			$treerow = $treeitem['row'];
			$this->exportPageIdCollection->append(intval($treerow['uid']));
		}
		
		return $this->exportPageIdCollection;
	}

	/**
	 * An l10nConfiguration consists of a startingpoint an a depth. Internally the pagetree is used to determine
	 * a set of pages wich should be exported.
	 * 
	 * @param void 
	 *
	 */
	protected function getExportTree(){
		$this->buildExportTree();
		return $this->tree;
	}
	
	/**
	 * Internal function to build the pagetree, if it has note been builded yet.
	 * 
	 * @param void
	 * @return void
	 *
	 */
	protected function buildExportTree(){
		if(!isset($this->tree)){
			//ensure tree is empty
			unset($this->tree);
			
			$depth 	= $this->l10ncfg['depth'];
			$pid	= $this->l10ncfg['pid'];

			// Initialize starting point of page tree:
			if(!isset($pid)){
				throw new Exception('no export start page configured.');
			}
	
			//@todo is t3lib_div::_GET('srcPID') needed anymore?
			//$treeStartingPoint = intval($depth==-1 ? t3lib_div::_GET('srcPID') : $pid);
			
			$treeStartingPoint 	= intval($pid);
			$treeStartingRecord = t3lib_BEfunc::getRecordWSOL('pages', $treeStartingPoint);
	
			// Initialize tree object:
			$this->tree = t3lib_div::makeInstance('t3lib_pageTree');
			$this->tree->init('AND '.$GLOBALS['BE_USER']->getPagePermsClause(1));
			$this->tree->addField('l18n_cfg');
	
			// Creating top icon; the current page
			// @todo why icon?
			$HTML = t3lib_iconWorks::getIconImage('pages', $treeStartingRecord, $GLOBALS['BACK_PATH'],'align="top"');
			$this->tree->tree[] = array(
				'row' => $treeStartingRecord,
				'HTML'=> $HTML
			);
	
			// Create the tree from starting point:
			if ($depth>0)	$this->tree->getTree($treeStartingPoint, $depth, '');
		}
	}	
	
	/**
	 * 
	 *
	 * @deprecated 
	 */
	public function updateFlexFormDiff($sysLang,$flexFormDiffArray)	{
		$l10ncfg=$this->l10ncfg;
			// Updating diff-data:
			// First, unserialize/initialize:
		$flexFormDiffForAllLanguages = unserialize($l10ncfg['flexformdiff']);
		if (!is_array($flexFormDiffForAllLanguages))	{
			$flexFormDiffForAllLanguages = array();
		}

			// Set the data (
		$flexFormDiffForAllLanguages[$sysLang] = array_merge((array)$flexFormDiffForAllLanguages[$sysLang],$flexFormDiffArray);

			// Serialize back and save it to record:
		$l10ncfg['flexformdiff'] = serialize($flexFormDiffForAllLanguages);
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_l10nmgr_cfg','uid='.intval($l10ncfg['uid']),array('flexformdiff' => $l10ncfg['flexformdiff']));
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/models/class.tx_l10nmgr_l10nConfiguration.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/models/class.tx_l10nmgr_l10nConfiguration.php']);
}


?>
