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
 * class.tx_l10nmgr_models_translateable_translateableField.php
 *
 * @author	 Timo Schmidt <schmidt@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.tx_l10nmgr_models_translateable_translateableField.php $
 * @date 03.04.2009 - 10:15:34
 * @package	TYPO3
 * @subpackage	l10nmgr
 * @access public
 */
class tx_l10nmgr_models_translateable_translateableField implements tx_l10nmgr_interfaces_wordsCountable{
	
	protected $translationDetail;
	
	/**
	 * @var string
	 */
	protected $identity_key;

	/**
	 * @var string
	 */
	protected $default_value;
	
	/**
	 * @var string
	 */
	protected $translation_value;
	
	
	/**
	 * @var string
	 */
	protected $diffDefault_value;
	
	/**
	 * @var array
	 */
	protected $previewLanguage_values;

	/**
	 * @var string
	 */
	protected $message;
	
	/**
	 * @var boolean
	 */
	protected $readOnly;
	
	/**
	 * @var string
	 */
	protected $fieldType;
	
	/**
	 * @var boolean
	 */
	protected $isRTE;

	/**
	 * @param string $identity_key
	 */
	public function setIdentityKey($identity_key) {
		$this->identity_key = $identity_key;
	}

	/**
	 * @return string identity string
	 */
	public function getIdentityKey(){
		return $this->identity_key;
	}
	
	/**
	 * @param string $default_value
	 */
	public function setDefaultValue($default_value) {
		$this->default_value = $default_value;
	}
	
	/**
	* Returns the value of the default record. This is the base of all translation.
	 * 	 *
	 * @return string
	 */
	public function getDefaultValue(){
		return $this->default_value;
	}
	
	/**
	 * @param string $diffDefault_value
	 */
	public function setDiffDefaultValue($diffDefault_value) {
		$this->diffDefault_value = $diffDefault_value;
	}
	
	/**
	 * Returns the original value of the default record (which was used to localize this element)
	 *
	 * @return string $diffDefault_value
	 */
	public function getDiffDefaultValue() {
		return $this->diffDefault_value;
	}
	
	/**
	 * @param string $fieldType
	 */
	public function setFieldType($fieldType) {
		$this->fieldType = $fieldType;
	}
	
	/**
	 * @param boolean $isRTE
	 */
	public function setIsRTE($isRTE) {
		$this->isRTE = $isRTE;
	}
	
	/**
	 * @param string $message
	 */
	public function setMessage($message) {
		$this->message = $message;
	}
	
	/**
	 * @param array $previewLanguage_values
	 */
	public function setPreviewLanguageValues($previewLanguage_values) {
		$this->previewLanguage_values = $previewLanguage_values;
	}
	
	/**
	 * @param boolean $readOnly
	 */
	public function setReadOnly($readOnly) {
		$this->readOnly = $readOnly;
	}
	
	/**
	 * @param string $translation_value
	 */
	public function setTranslationValue($translation_value) {
		$this->translation_value = $translation_value;
	}
	
	/**
	 * Returns the translated value of this translateable field.
	 * @return string translated value
	 */
	public function getTranslationValue(){
		return $this->translation_value;
	}
	
	/**
	 * @param unknown_type $translationDetail
	 */
	public function setTranslationDetail($translationDetail) {
		$this->translationDetail = $translationDetail;
	}	
	
	/**
	 * Returns the number of words of the default value
	 *
	 * @return int
	 */
	public function countWords(){
		return str_word_count(trim($this->default_value));
	}
	
	/**
	 * This method returns the base data for the translation. In normal cases, this is 
	 * the content of the record in the default language. 
	 *
	 * @param tx_l10nmgr_models_language_language$forcedSourceLanguageId
	 */
	public function getDataForTranslation(tx_l10nmgr_models_language_language $forcedSourceLanguage = null){
		//dtermine ssourcefield depending in sourceLanguage
		$dataForTranslation = $this->determinFieldContentByLanguage($forcedSourceLanguage);
		
		return $dataForTranslation;
	}
	

	
	/**
	 * delivers the data for the translation depending on the sourceLanguage
	 * 
	 * @param tx_l10nmgr_models_language_language $forcedSourceLanguage
	 * @return string
	 */
	protected function determinFieldContentByLanguage(tx_l10nmgr_models_language_language $forcedSourceLanguage = null){
		if ($forcedSourceLanguage) {
			$dataForTranslation =	$this->previewLanguage_values[$forcedSourceLanguage->getUid()];
		}
		else {
			$dataForTranslation	=	$this->default_value;
		}
		
		return $dataForTranslation;
	}
	

	
	
	/**
	 * This method can be used to determin if there is a difference between the diffDefaultValue and the defaultValue
	 *
	 * @return boolean
	 */
	public function isChanged(){
		return (strcmp(trim($this->diffDefault_value),trim($this->default_value)) != 0);
	}
	
	/**
	 * A field with the type text from the RTE needs a Transformation
	 *
	 * @return boolean
	 */
	public function needsTransformation(){
		return (($this->fieldType =='text') &&  $this->isRTE);
	}
}

?>