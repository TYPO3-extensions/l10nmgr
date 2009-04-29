<?php

/***************************************************************
 *  Copyright notice
 *
 *  Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
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
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * A pageGroup is a logical group for elements of one page.
 * It provides the abillity to count all fields and words in the pageGroup.
 *
 * class.tx_l10nmgr_models_translateable_PageGroup.php
 *
 * @author	 Timo Schmidt <schmidt@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.class_name.php $
 * @date 03.04.2009 - 10:06:51
 * @package	TYPO3
 * @subpackage	l10nmgr
 * @access public
 */
class tx_l10nmgr_models_translateable_pageGroup implements tx_l10nmgr_interface_wordsCountable{

	/**
	 * Holds the assigned translateableElements
	 * @var ArrayObject
	 */
	protected $translateableElements;

	/**
	 * Hold the page_row of the pageGroup
	 *
	 * @var array
	 */
	protected $page_row;

	/**
	 * Number of fields of all translateableElements
	 *
	 * @var int
	 */
	protected $countedFields;

	/**
	 * @var int
	 */
	protected $countedWords;


	/**
	 * Constructor
	 *
	 * @param void
	 */
	public function __construct(){
		$this->translateableElements = new ArrayObject();
		$this->countedFields = 0;
		$this->countedWords = 0;
	}

	/**
	 * Method to initialize the pageGroup from a page row of the database.
	 *
	 * @param array $row
	 */
	public function setPageRow($row){
		$this->page_row = $row;
	}


	/**
	 * Returns the uid of the page, which the pageGroup is based on.
	 * 
	 * @return int
	 */
	public function getUid(){
		return $this->page_row['uid'];
	}

	/**
 	 * Returns the title of the page where the pageGroup is based on
 	 *
 	 * @return string
	 */
	public function getPageTitle(){
		return $this->page_row['title'];
	}


	/**
	 * Method to add a translateableElement to the PageGroup.
	 *
	 * @param tx_l10nmgr_models_translateable_translateableElement
	 */
	public function addTranslateableElement(tx_l10nmgr_models_translateable_translateableElement $translateableElement){
		$this->translateableElements->append($translateableElement);
	}

	/**
	 * Returns the collection of translateableElements
	 *
	 * @return ArrayObject
	 */
	public function getTranslateableElements(){
		return $this->translateableElements;
	}

	/**
	 * Counts the number of fields of all translateableElements in the pageGroup
	 *
	 * @return int
	 */
	public function countFields(){
		if($this->countedFields == 0 && $this->translateableElements instanceof  ArrayObject ){
			foreach($this->translateableElements as $translateableElement){
				$this->countedFields  += $translateableElement->countFields();
			}
		}

		return $this->countedFields;
	}

	/**
	 * Counts all words within the pagegroup.
	 *
	 * @return int
	 */
	public function countWords(){
		if($this->countedWords == 0 && $this->translateableElements instanceof  ArrayObject ){
			foreach($this->translateableElements as $translateableElement){
				$this->countedWords += $translateableElement->countWords();
			}
		}

		return $this->countedWords;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/models/translateable/class.tx_l10nmgr_models_translateable_pageGroup.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/models/translateable/class.tx_l10nmgr_models_translateable_pageGroup.php']);
}
?>