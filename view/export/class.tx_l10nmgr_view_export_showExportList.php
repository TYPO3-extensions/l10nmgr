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
 * class.tx_l10nmgr_view_export_show.php
 *
 * @author	 Timo Schmidt <schmidt@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.tx_l10nmgr_view_export_show.php $
 * @date 22.04.2009 - 13:01:57
 * @see tx_mvc_view_phpTemplate
 * @category view
 * @package	TYPO3
 * @subpackage	extensionkey
 * @access public
 */
class tx_l10nmgr_view_export_showExportList extends tx_mvc_view_backendModule{

	/**
	 * The default template is used if o template is set
	 *
	 * @var        string
	 */
	protected $defaultTemplate = 'EXT:l10nmgr/templates/exportList.php';

	protected $title = 'List of incomplete exports';
	
	/**
	 * @var ArrayObject exportData collection
	 */
	protected $exportDataCollection;
	
	
	/**
	 * This method is used to set a collection of exportData objects.
	 *
	 * @param ArrayObject $exportDataCollection
	 */
	public function setExportDataCollection(ArrayObject $exportDataCollection){
		$this->exportDataCollection = $exportDataCollection;
	}
	
	/**
	 * Returns a collection of exportData objects.
	 *
	 * @return ArrayObject
	 */
	protected function getExportDataCollection(){
		return $this->exportDataCollection;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extensionkey/l10nmgr/view/export/class.tx_l10nmgr_view_export_showNotReimportedExports.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extensionkey/l10nmgr/view/export/class.tx_l10nmgr_view_export_showNotReimportedExports.php']);
}
?>