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
 * documenation
 *
 * {@inheritdoc}
 *
 * class.tx_l10nmgr_view_importer_detail.php
 *
 * @author Timo Schmidt <schmidt@aoemedia.de>
 * @controller import
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.tx_l10nmgr_view_importer_detail.php $
 * @date 04.05.2009 15:08:52
 * @see tx_mvc_view_widget_phpTemplateListView
 * @category database
 * @package TYPO3
 * @subpackage l10nmgr
 * @access public
 */
class tx_l10nmgr_view_import_detail extends tx_mvc_view_widget_phpTemplateListView {

	/**
	 * The default template is used if o template is set
	 *
	 * @var        string
	 */
	protected $defaultTemplate = 'EXT:l10nmgr/templates/import/detail.php';

	/**
	 * Hold the importData to display
	 * 
	 * @var tx_l10nmgr_domain_importer_importData
	 */
	protected $importData;
	
	/**
	 * This method is used to set the importData where the informations should be displayed from
	 * 
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 * @param tx_l10nmgr_domain_importer_importData
	 */	
	public function setImportData($importData){
		$this->importData = $importData;
	}
	
	/**
	 * This method can be used from the template to get the configured
	 * importData.
	 * 
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 * @return tx_l10nmgr_domain_importer_importData
	 */
	protected function getImportData(){
		return $this->importData;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/view/import/class.tx_l10nmgr_view_importer_detail.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/view/import/class.tx_l10nmgr_view_importer_detail.php']);
}
?>