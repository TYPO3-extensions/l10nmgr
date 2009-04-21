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
 * {@inheritdoc }
 *
 * class.tx_l10nmgr_view_export_showXMLExportFormAction.php
 *
 * @author	 Timo Schmidt <schmidt@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.tx_l10nmgr_view_export_showXMLExportFormAction.php $
 * @date 16.04.2009 - 15:42:37
 * @see tx_mvc_view_phpTemplate
 * @category view
 * @package	TYPO3
 * @subpackage	l10nmgr
 * @access public
 */
class tx_l10nmgr_view_export_showExportForm extends tx_mvc_view_backendModule {

	/**
	 * The default template is used if o template is set
	 *
	 * @var        string
	 */
	protected $defaultTemplate = 'EXT:l10nmgr/templates/exportform.php';
	
	
	/**
	 * Holds an array of available source languages
	 *
	 * @var array
	 */
	protected $source_languages;
	
	/**
	 * Holds an array of available target Languages
	 * 
	 * @var array
	 */
	protected $target_languages;
	
	
	/**
	 * Action that should be used to render the view
	 *
	 * @var string
	 */
	protected $render_action;
	
	/**
	 * Holds an internal array with all available formats that can be exported
	 *
	 * @var array
	 */
	protected $available_export_formats;
	
	/**
	 * Holds the currently selected export format.
	 */
	protected $selected_export_format;
	


	/**
	 * Holds the internal id of the l10nConfiguration used for this export.
	 *
	 * @var int
	 */
	protected $configuration_id;
	
	/**
	 * Method to set that languages that should be shown in the exportform as 
	 * target languages
	 * 	 *
	 * @param array $languages
	 */
	public function setAvailableTargetLanguages(array $languages){
		$this->target_languages = $languages;
	}
	
	/**
	 * This method is used to set a set of languages as available source languages
	 *
	 * @param array $languages
	 */
	public function setAvailableSourceLanguages(array $languages){
		$this->source_languages = $languages;
	}
	
	
	/**
	 * Returns the configured languages for this form view
	 *
	 * @return array
	 */
	protected function getAvailableSourceLanguages(){
		return $this->source_languages;
	}
	
	/**
	 * Returns the configured languages that can act as target language
	 * 
	 * @return array
	 */
	protected function getAvailableTargetLanguages(){
		return $this->target_languages;
	}
	
	/**
	 * Method to set an action that should be called when the form was submitted
	 *
	 * @param string $renderString
	 */
	public function setRenderAction($renderString){
		$this->render_action = $renderString;
	}
	
	/**
	 * Returns the configured renderAction
	 *
	 * @return string
	 */
	protected function getRenderAction(){
		return $this->render_action;
	}
	
	/**
	 * Method to set an array of available export formats
	 */
	public function setAvailableExportFormats(array $exportFormats){
		$this->available_export_formats = $exportFormats;
	}
	
	/**
	 * Returns the list of available exportformats.
	 *
	 * @return array
	 */
	protected function getAvailableExportFormats(){
		return $this->available_export_formats;
	}

	/**
	 * Returns the Id of the configuration where the export should be generated form.
	 *
	 * @return int uid of the l10nConfiguration Record
	 * @todo
	 */
	protected function getConfigurationId(){
		return $this->configuration_id;
	}
	
	
	/**
	 * Method to set the id of the l10nConfiguration, that should be used for the export.
	 * Used to pass it to the export process.
	 *
	 * @param int $configurationId
	 */
	public function setConfigurationId($configurationId){
		$this->configuration_id = $configurationId;
	}
	
	/**
	 * Returns the current selected export format.
	 * @return string
	 */
	protected  function getSelectedExportFormat() {
		return $this->selected_export_format;
	}
	
	/**
	 * Method to configure the curret selected export format.
	 * 
	 * @param string $selected_export_format
	 */
	public function setSelectedExportFormat($selected_export_format) {
		$this->selected_export_format = $selected_export_format;
	}	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/l10nmgr/view/export/class.tx_l10nmgr_view_export_showXMLExportForm.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/l10nmgr/view/export/class.tx_l10nmgr_view_export_showXMLExportForm.php']);
}
?>