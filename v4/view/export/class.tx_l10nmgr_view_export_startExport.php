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
 * class.class_name.php
 *
 * @author	 Timo Schmidt <schmidt@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.class_name.php $
 * @date 20.04.2009 - 16:47:15
 * @see tx_mvc_view_phpTemplate
 * @category view
 * @package	TYPO3
 * @subpackage	extensionkey
 * @access public
 */
class tx_l10nmgr_view_export_startExport extends tx_mvc_view_backendModule { 
	/**
	 * The default template is used if o template is set
	 *
	 * @var        string
	 */
	protected $defaultTemplate = 'EXT:l10nmgr/templates/startExport.php';
	
	/**
	 * This method is used to add the progressView to the exportView
	 *
	 * @param tx_l10nmgr_view_export_progress $progressView
	 */
	public function setProgressView(tx_l10nmgr_view_export_progress $progressView){
		$this->progressView = $progressView;
	}
	
	/**
	 * This method is used to set an exportData object which represents the current export.
	 *
	 * @param tx_l10nmgr_models_export_exportData
	 */
	public function setExportData($exportData){
		$this->exportData = $exportData;
	}
	
	/**
	 * Returns the current exportData object to display it in the view
	 *
	 * @return tx_l10nmgr_models_export_exportDtaa
	 */
	protected function getExportData(){
		return $this->exportData;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/view/export/class.tx_l10nmgr_view_export_startExport.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/view/export/class.tx_l10nmgr_view_export_startExport.php']);
}
?>