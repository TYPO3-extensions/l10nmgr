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

require_once t3lib_extMgm::extPath('l10nmgr').'interface/interface.tx_l10nmgr_interface_stateImportable.php';

/**
 * Business object of an page which contains tx_l10nmgr_domain_tranlation_elementCollection
 *
 * class.tx_l10nmgr_domain_tranlation_page.php
 *
 * @author Michael Klapper <klapper@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id$
 * @date $Date$
 * @since 24.04.2009 - 14:04:05
 * @package TYPO3
 * @subpackage tx_l10nmgr
 * @access public
 */
class tx_l10nmgr_domain_translation_page implements tx_l10nmgr_interface_stateImportable {

	/**
	 * Indicate that the current entity was already processed for the import
	 *
	 * @var boolean
	 */
	protected $isImported = false;

	/**
	 * Uid of the entity page eq database table record
	 *
	 * @var integer
	 */
	protected $uid = 0;

	/**
	 * Holds all related items of the current page
	 *
	 * @var tx_l10nmgr_domain_tranlation_elementCollection
	 */
	protected $ElementCollection = null;

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

		if ( ($this->ElementCollection instanceof tx_l10nmgr_domain_translation_elementCollection) && $this->ElementCollection->isImported() ) {
			$this->isImported = true;
		}

		return $this->isImported;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return tx_l10nmgr_domain_tranlation_elementCollection
	 */
	public function getElementCollection() {
		return $this->ElementCollection;
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
	 * @param tx_l10nmgr_domain_translation_elementCollection $ElementCollection
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function setElementCollection(tx_l10nmgr_domain_translation_elementCollection $ElementCollection) {
		$this->ElementCollection = $ElementCollection;
	}

	/**
	 * @param integer $uid
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function setUid($uid) {
		$this->uid = $uid;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/domain/translation/class.tx_l10nmgr_domain_translation_page.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/domain/translation/class.tx_l10nmgr_domain_translation_page.php']);
}

?>