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
 * description
 *
 * class.tx_l10nmgr_models_translateable_translateableElement.php
 *
 * @author	 Timo Schmidt <schmidt@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.tx_l10nmgr_models_translateable_translateableElement.php $
 * @date 03.04.2009 - 10:19:15
 * @package	TYPO3
 * @subpackage	l10nmgr
 * @access public
 */
class tx_l10nmgr_models_translateable_translateableElement  implements tx_l10nmgr_interface_wordsCountable {


	/**
	 * Holds the translateableFields of the translateableElement
	 * @var ArrayObject
	 */
	protected $translateableFields;

	/**
	 * @var int
	 */
	protected $uid;

	/**
	 * @var string
	 */
	protected $table;


	/**
	 * @var ArrayObject
	 */
	protected $logs;

	/**
	 * @var int
	 */
	protected $sys_language_uid;

	/**
	 * @var string
	 */
	protected $translation_table;

	/**
	 * @var array
	 */
	protected $translations;

	/**
	 * @var array
	 */
	protected $excessive_translations;

	/**
	 * @var int
	 */
	protected $countedWords;

	/**
	 * @var int
	 */
	protected $countedFields;

	/**
	 * Constructor
	 *
	 */
	public function __construct(){
		$this->translateableFields = new ArrayObject();
	}

	/**
	 * Method to add a translateableField to the translateableElement
	 *
	 * @param tx_l10nmgr_models_translateable_translateableField $translateableField
	 */
	public function addTranslateableField(tx_l10nmgr_models_translateable_translateableField $translateableField){
		unset($this->countedWords);
		$this->translateableFields->append($translateableField);
	}

	/**
	 * Returns the collection of translateableFields of the translateabeElement
	 *
	 * @return ArrayObject
	 */
	public function getTranslateableFields(){
		return $this->translateableFields;
	}

	/**
	 * Method to set the tablename of the translateableElement
	 *
	 * @param string $table
	 */
	public function setTableName($table){
		$this->table = $table;
	}

	/**
	 * Returns the name of the table
	 *
	 * @return string
	 */
	public function getTableName(){
		return $this->table;
	}

	/**
	 * Method to set a uid of the translateableElement
	 *
	 * @param int $uid
	 */
	public function setUid($uid){
		$this->uid = $uid;
	}

	/**
	 * Returns the uid of the element
	 *
	 * @return int
	 */
	public function getUid(){
		return $this->uid;
	}

	/**
	 * Method to attach an array with logmessages to the translateableElement
	 *
	 * @param array $logs
	 */
	public function setLogs($logs){

		if(is_array($logs)){
			$this->logs = new ArrayObject($logs);
		}
	}

	/**
	 * Method to configure a language uid.
	 *
	 * @param int $id
	 */
	public function setSysLanguageUid($id){
		$this->sys_language_uid = $id;
	}


	/**
	 * @param string $translation_table
	 */
	public function setTranslationTable($translation_table) {
		$this->translation_table = $translation_table;
	}

	/**
	 * @param array $excessive_translations
	 */
	public function setExcessiveTranslations($excessive_translations) {
		$this->excessive_translations = $excessive_translations;
	}

	/**
	 * @param array $translations
	 */
	public function setTranslations($translations) {
		$this->translations = $translations;
	}

	/**
	 * Returns the numberof words of the translateableElement
	 *
	 * @param void
	 * @return int
	 */
	public function countWords(){
		//the words should only be counted once
		if(!isset($this->countedWords)){
			$this->countedWords = 0;
			//the word count of the elements is the sum of the wordcount of all fields
			for($it = $this->translateableFields->getIterator(); $it->valid(); $it->next()){
				$this->countedWords +=  (int)$it->current()->countWords();
			}
		}

		return $this->countedWords;
	}

	/**
	 * Returns the number of translateableFields
	 *
	 * @return int
	 */
	public function countFields(){
		if($this->countedFields == 0 && $this->getTranslateableFields() instanceof ArrayObject ){
			$this->countedFields = $this->getTranslateableFields()->count();
		}else{
			$this->countedFields = 0;
		}

		return $this->countedFields;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/models/translateable/class.tx_l10nmgr_models_translateable_translateableElement.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/models/translateable/class.tx_l10nmgr_models_translateable_translateableElement.php']);
}
?>