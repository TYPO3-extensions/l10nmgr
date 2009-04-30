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
 * class.tx_l10nmgr_models_importer_importFileCollection.php
 *
 * @subject tx_l10nmgr_models_importer_importFile
 * @author Timo Schmidt <schmidt@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.tx_l10nmgr_models_importer_importFileCollection.php $
 * @date 29.04.2009 18:53:42
 * @see ArrayObject
 * @category database
 * @package TYPO3
 * @subpackage l10nmgr
 * @access public
 */
class tx_l10nmgr_models_importer_importFileCollection extends ArrayObject {

	/**
	* Method to retrieve an element from the collection.
	* @access public
 	* @throws tx_mvc_exception_argumentOutOfRange
	* @return tx_l10nmgr_models_importer_importFile
	*/
	public function offsetGet($index) {
		if (! parent::offsetExists($index)) {
			throw new tx_mvc_exception_argumentOutOfRange('Index "' . var_export($index, true) . '" for tx_l10nmgr_models_importer_importFile are not available');
		}
		return parent::offsetGet($index);
	}

	/**
	* Mehtod to add an element to the collection-
	*
	* @param mixed $index
	* @param tx_l10nmgr_models_importer_importFile $subject
	* @throws InvalidArgumentException
	* @return void
	*/
	public function offsetSet($index, $subject) {
		if (! $subject instanceof tx_l10nmgr_models_importer_importFile ) {
			throw new InvalidArgumentException('Wrong parameter type given, "tx_l10nmgr_models_importer_importFile" expected!');
		}
		
		parent::offsetSet($index, $subject);
	}

	/**
	* Method to append an element to the collection
	* @param tx_l10nmgr_models_importer_importFile $subject
	* @throws InvalidArgumentException
	* @return void
	*/
	public function append($subject) {
		if (! $subject instanceof tx_l10nmgr_models_importer_importFile ) {
			throw new InvalidArgumentException('Wrong parameter type given, "tx_l10nmgr_models_importer_importFile" expected!');
		}
		
		parent::append($subject);
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/models/importer/class.tx_l10nmgr_models_importer_importFileCollection.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/models/importer/class.tx_l10nmgr_models_importer_importFileCollection.php']);
}
?>