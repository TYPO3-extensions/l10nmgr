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

/**
 * This view is used to display the details of an exportData record.
 * An exportData record represents one export run.
 *
 *
 * {@inheritdoc}
 *
 * class.tx_l10nmgr_view_export_detail.php
 *
 * @author Timo Schmidt <schmidt@aoemedia.de>
 * @controller export
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.tx_l10nmgr_view_export_detail.php $
 * @date 04.05.2009 15:01:37
 * @see tx_mvc_view_widget_phpTemplateListView
 * @category database
 * @package TYPO3
 * @subpackage l10nmgr
 * @access public
 */
class tx_l10nmgr_view_export_showExportDetail extends tx_mvc_view_backendModule {

	/**
	 * The default template is used if o template is set
	 *
	 * @var        string
	 */
	protected $defaultTemplate = 'EXT:l10nmgr/templates/export/detail.php';

	/**
	 * Holds the exportData record where the details should be displayed from
	 *
	 * @var tx_l10nmgr_domain_exporter_exportData
	 */
	protected $exportData;

	/**
	 * Holds a flag if files should be shown or not
	 *
	 * @var boolean
	 */
	protected $showFiles;

	/**
	 * Holds the state to display the link to the list or not
	 *
	 * @var boolean
	 */
	protected $showListLink;

	/**
	 * Holds the link to the listView
	 *
	 * @var string
	 */
	protected $listLink;

	/**
	 * Method to set the exportData record that should be used to display informations about.
	 *
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 * @param tx_l10nmgr_domain_exporter_exportData exportData
	 */
	public function setExportData($exportData){
		$this->exportData = $exportData;
	}

	/**
	 * Retrieves the configured exportData
	 *
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 * @return tx_l10nmgr_domain_exporter_exportData
	 */
	protected function getExportData(){
		return $this->exportData;
	}


	/**
	 * Method to configure the view to show files.
	 *
	 * @param void
	 * @return void
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 */
	public function showFiles(){
		$this->showFiles = true;
	}

	/**
	 * Configure the view to hide files
	 *
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 * @param void
	 * @return void
	 */
	public function hideFiles(){
		$this->showFiles  = false;
	}

	/**
	 * Returns the state if files should be displayed or not.
	 *
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 * @return boolean
	 */
	protected function getShowFiles(){
		return $this->showFiles;
	}

	/**
	 * Method to enable the link to the list view. This
	 * is usefull when the detail view is shown at the end of an export.
	 *
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 * @param void
	 * @return void
	 */
	public function showListLink(){
		$this->showListLink = true;
	}

	/**
	 * Mehtod to read the state if the list link should be shown or not
	 *
	 * @return boolean
	 */
	protected function getShowListLink(){
		return $this->showListLink;
	}

	/**
	 * Method to set a link to the list view.
	 *
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 * @param string
	 */
	public function setListLink($link){
		$this->listLink = $link;
	}

	/**
	 * Returns the link to the list view.
	 *
	 * @return string
	 */
	protected function getListLink(){
		return $this->listLink;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/view/export/class.tx_l10nmgr_view_export_detail.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/view/export/class.tx_l10nmgr_view_export_detail.php']);
}
?>