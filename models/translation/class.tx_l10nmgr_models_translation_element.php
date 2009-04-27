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

require_once t3lib_extMgm::extPath('l10nmgr') . 'models/translation/class.tx_l10nmgr_models_translation_fieldCollection.php';

/**
 * Representation of an single field of an record
 *
 * class.tx_l10nmgr_models_translation_element.php
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
class tx_l10nmgr_models_translation_element {

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
	 * @var tx_l10nmgr_models_translation_fieldCollection
	 */
	protected $FieldCollection = null;

	/**
	 * @return tx_l10nmgr_models_translation_fieldCollection
	 */
	public function getFieldCollection() {

		return $this->FieldCollection;
	}

	/**
	 * @return string
	 */
	public function getTableName() {

		return $this->tableName;
	}

	/**
	 * @return integer
	 */
	public function getUid() {

		return $this->uid;
	}

	/**
	 * @param tx_l10nmgr_models_translation_fieldCollection $FieldCollection
	 */
	public function setFieldCollection($FieldCollection) {

		$this->FieldCollection = $FieldCollection;
	}

	/**
	 * @param string $tableName
	 */
	public function setTableName($tableName) {

		$this->tableName = $tableName;
	}

	/**
	 * @param integer $uid
	 */
	public function setUid($uid) {

		$this->uid = $uid;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/models/translation/class.tx_l10nmgr_models_translation_element.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/models/translation/class.tx_l10nmgr_models_translation_element.php']);
}

?>