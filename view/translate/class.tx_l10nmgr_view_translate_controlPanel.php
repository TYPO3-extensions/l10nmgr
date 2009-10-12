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
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

require_once t3lib_extMgm::extPath('mvc') . 'mvc/presentation/form/class.tx_mvc_presentation_form_selectElement.php';
require_once t3lib_extMgm::extPath('mvc') . 'mvc/presentation/form/class.tx_mvc_presentation_form_simpleForm.php';

/**
 * description
 *
 * {@inheritdoc}
 *
 * class.tx_l10nmgr_view_translate_controllPanel.php
 *
 * @author Timo Schmidt <schmidt@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id$
 * @date $Date$
 * @since 06.10.2009 - 11:43:50
 * @see tx_mvc_view_phpTemplate
 * @category view
 * @package TYPO3
 * @subpackage l10nmgr
 * @access public
 */
class tx_l10nmgr_view_translate_controlPanel extends tx_mvc_view_phpTemplate {

	/**
	 * The default template is used if o template is set
	 *
	 * @var string
	 */
	protected $defaultTemplate = 'EXT:l10nmgr/templates/translate/controlPanel.php';
	
	
	protected $availableLanguages;
	
	/**
	 * @var tx_l10nmgr_domain_language_language
	 */
	protected $selectedLanguage;
	
	/**
	 * @var boolean
	 */
	protected $newChangedOnly;
	
	
	/**
	 * @var boolean
	 */
	protected $noHidden;

	/**
	 * @var unknown_type
	 */
	protected $L10NConfiguration;
	
	/**
	 * @param $L10NConfiguration the $L10NConfiguration to set
	 */
	public function setL10NConfiguration($L10NConfiguration) {
		$this->L10NConfiguration = $L10NConfiguration;
	}

	/**
	 * @return the $L10NConfiguration
	 */
	public function getL10NConfiguration() {
		return $this->L10NConfiguration;
	}
		
	/**
	 * @param $newChangedOnly the $newChangedOnly to set
	 */
	public function setNewChangedOnly($newChangedOnly) {
		$this->newChangedOnly = $newChangedOnly;
	}
	
	/**
	 * @return the $newChangedOnly
	 */
	public function getNewChangedOnly() {
		return $this->newChangedOnly;
	}	
		
	/**
	 * @param $noHidden the $noHidden to set
	 */
	public function setNoHidden($noHidden) {
		$this->noHidden = $noHidden;
	}

	/**
	 * @return the $noHidden
	 */
	public function getNoHidden() {
		return $this->noHidden;
	}

	/**
	 * 
	 *
	 **/
	public function setAvailableLanguages($availableLanguages){
		$this->availableLanguages = $availableLanguages;
	}

	/**
	 * 
	 *
	 */
	protected function getAvailableLanguages(){
		return $this->availableLanguages;
	}
	
	/**
	 * 
	 *
	 */
	public function setSelectedLanguage(tx_l10nmgr_domain_language_language $selectedLanguage){
		$this->selectedLanguage = $selectedLanguage;
	}
	
	/**
	 * 
	 *
	 */
	protected function getSelectedLanguage(){
		return $this->selectedLanguage;
	}
	
	/**
	 * This method creates the language form element as select box.
	 * 
	 * @param void
	 * @return tx_mvc_presentation_form_selectElement
	 */
	private function getLanguageFormElement(){
		foreach($this->getAvailableLanguages() as $avbLang){
			$multidata[$avbLang->getUid()] = $avbLang->getTitle();
		}

		$languageFormElement = new tx_mvc_presentation_form_selectElement('target_language',$this->getSelectedLanguage()->getUid(),'select',null,$multidata);
		return $languageFormElement;
	}
	
	/**
	 *
	 *
	 * @return tx_mvc_presentation_form_element
	 */
	private function getNewChangedCheckbox(){
		$newChangedCheckbox = new tx_mvc_presentation_form_element('new_changed_only',$this->getNewChangedOnly(),'checkbox');
		return $newChangedCheckbox;
	}
	
	/**
	 * 
	 * @return tx_mvc_presentation_form_element
	 */
	private function getNoHiddenCheckbox(){
		$noHiddenCheckbox = new tx_mvc_presentation_form_element('no_hidden',$this->getNoHidden(),'checkbox');
		return $noHiddenCheckbox;
	}
	
	/**
	 * 
	 */
	private function getL10NConfigurationHiddenField(){
		$configurationField = new tx_mvc_presentation_form_element('configurationId',$this->getL10NConfiguration()->getUid(),'hidden');
		return $configurationField;
	}
	
	/**
	 * 
	 * @return  tx_mvc_presentation_form_simpleForm
	 */
	protected function getForm(){
		
		$form = new tx_mvc_presentation_form_simpleForm();	
		$form->addElement($this->getLanguageFormElement());
		$form->addElement($this->getNewChangedCheckbox());
		$form->addElement($this->getNoHiddenCheckbox());

		$form->addElement($this->getL10NConfigurationHiddenField());
		
		return $form;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['class.tx_l10nmgr_view_translate_controllPanel.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['class.tx_l10nmgr_view_translate_controllPanel.php']);
}
?>