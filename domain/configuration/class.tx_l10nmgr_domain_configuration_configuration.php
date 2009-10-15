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
class tx_l10nmgr_domain_configuration_configuration extends tx_mvc_ddd_typo3_abstractTCAObject {
	
	/**
	 * @var t3lib_pageTree
	 */
	protected $tree;
	
	/**
	 * @var ArrayObject
	 */
	protected $exportPageIdCollection;

	/**
	 * Initialize the database object with
	 * the table name of current object
	 *
	 * @access public
	 * @return string
	 */
	public static function getTableName() {
		return 'tx_l10nmgr_cfg';
	}

	/**
	* get a field of the current cfgr record
	* @param string	$key		Key of the field. E.g. title,uid...
	* @return string	Value of the field
	**/
	protected function getData($key) {
		return $this->row[$key];
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
		$allExcludes = array();
		
		foreach($excludeArray as $excludeElement => $value){
			//is value a page?
			if(substr($excludeElement,0,6) == 'pages:'){
				$recursiveExcludes = $this->expandExcludeString(substr($excludeElement,6));
				$allExcludes = array_merge($allExcludes,array_flip($recursiveExcludes));				
			}else{
				$allExcludes[$excludeElement] = $value;
			}
		}
				
		return $allExcludes;
	}

	/**
	 * Expands an exclude string (eg pages:4711+) to all pages in the tree recursiv.
	 * 
	 * @param string
	 * @return array
	 */
	public function expandExcludeString($excludeString) {
		// internal static caches;
		$pidList = array();

		/* @var $tree t3lib_pageTree */
		$tree = t3lib_div::makeInstance('t3lib_pageTree');
		$perms_clause = $GLOBALS['BE_USER']->getPagePermsClause(1);
		$tree->init('AND ' . $perms_clause);

		list($pid, $depth) = t3lib_div::trimExplode('+', $excludeString);

		// default is "page only" = "depth=0"
		if (empty($depth)) {
			$depth = ( stristr($excludeString,'+')) ? 99 : 0;
		}

		$pidList[] = 'pages:'.$pid;

		if ($depth > 0) {			
			$tree->getTree($pid, $depth);
			foreach ($tree->tree as $data) {
				$pidList[] = 'pages:'.$data['row']['uid'];
			}
		}
		
		return array_unique($pidList);
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
	 * Returns a collection of pageids which need to be exported
	 *
	 * @return ArrayObject
	 */
	public function getExportPageIdCollection() {
		$this->exportPageIdCollection = new ArrayObject();
		$tree 			= $this->getExportTree();
		$excludeArray	= $this->getExcludeArray();		

		//$tree->tree contains pages of the tree
		foreach($tree->tree as $treeitem){
			$treerow = $treeitem['row'];
			if(is_array($excludeArray) && !array_key_exists('pages:'.$treerow['uid'],$excludeArray)){
				$this->exportPageIdCollection->append(intval($treerow['uid']));
			}
		}

		return $this->exportPageIdCollection;
	}

	/**
	 * An l10nConfiguration consists of a startingpoint an a depth. Internally the pagetree is used to determine
	 * a set of pages wich should be exported.
	 *
	 * @param void
	 * @return t3lib_pageTree
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
	 */
	protected function buildExportTree(){
		if(!isset($this->tree)){
			//ensure tree is empty
			unset($this->tree);

			$depth 	= $this->getData('depth');
			$pid	= $this->getData('pid');

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
	 * Method to determine if all exports from the configuration are allready finished or not
	 *
	 * @return boolean
	 */
	public function hasIncompleteExports(){
		//@todo determine all exports exports that are currently not finished
		return false;
	}

	/**
	 * Returns the static language object
	 *
	 * @return tx_l10nmgr_domain_language_staticLanguage
	 */
	public function getStaticSourceLanguage(){
		if ($this->getData('sourceLangStaticId') != 0) {
			if (empty($this->row['sourceLangStaticObject'])) {
				$languageRepository = new tx_l10nmgr_domain_language_staticLanguageRepository();
				$this->row['sourceLangStaticObject'] = $languageRepository->findById($this->getData('sourceLangStaticId'));

				if (!$this->row['sourceLangStaticObject'] instanceof tx_l10nmgr_domain_language_staticLanguage) {
					throw new Exception('Object is not an instance of "tx_l10nmgr_domain_language_staticLanguage"');
				}
			}
			return $this->row['sourceLangStaticObject'];
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/domain/configuration/class.tx_l10nmgr_domain_configuration_configuration.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/domain/configuration/class.tx_l10nmgr_domain_configuration_configuration.php']);
}
?>