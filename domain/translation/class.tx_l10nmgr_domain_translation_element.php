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
 * Representation of an single field of an record
 *
 * class.tx_l10nmgr_domain_translation_element.php
 *
 * @author Michael Klapper <klapper@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id$
 * @date $Date$
 * @since 24.04.2009 - 14:12:39
 * @package TYPO3
 * @subpackage tx_l10nmgr
 * @access public
 */

class tx_l10nmgr_domain_translation_element implements tx_l10nmgr_interface_stateImportable {

	/**
	 * Indicate that the current entity was already processed for the import
	 *
	 * @var boolean
	 */
	protected $isImported = false;

	/**
	 * Name of the current record table
	 *
	 * @var string
	 */
	protected $tableName = '';

	/**
	 * Uid of the current record
	 *
	 * @var integer
	 */
	protected $uid = 0;

	/**
	 * Contains a collection of the basic field
	 *
	 * @var tx_l10nmgr_domain_translation_fieldCollection
	 */
	protected $FieldCollection = null;

	/**
	 * Mark entity as processed for the import
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function markImported() {
//!TODO refactor this, the object should not allowed to set his own isImported state to true
//		$this->isImported = true;
	}

	/**
	 * Retrieve the import state
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return boolean
	 */
	public function isImported() {

		if ($this->isImported !== true) {

			if ( ($this->FieldCollection instanceof tx_l10nmgr_domain_translation_fieldCollection) &&  $this->FieldCollection->isImported()) {
				$this->isImported = true;
			}
		}

		return $this->isImported;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return tx_l10nmgr_domain_translation_fieldCollection
	 */
	public function getFieldCollection() {
		return $this->FieldCollection;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return string
	 */
	public function getTableName() {
		return $this->tableName;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return integer
	 */
	public function getUid() {
		return $this->uid;
	}

	/**
	 * If a new fieldCollection is set to the Element, the isImported state is automaticliy set to false.
	 *
	 * @param tx_l10nmgr_domain_translation_fieldCollection $FieldCollection
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function setFieldCollection(tx_l10nmgr_domain_translation_fieldCollection $FieldCollection) {
		$this->FieldCollection = $FieldCollection;
		$this->isImported      = false;
	}

	/**
	 * @param string $tableName
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function setTableName($tableName) {
		$this->tableName  = $tableName;
	}

	/**
	 * @param integer $uid
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function setUid($uid) {
		$this->uid        = $uid;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/domain/translation/class.tx_l10nmgr_domain_translation_element.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/domain/translation/class.tx_l10nmgr_domain_translation_element.php']);
}

?>