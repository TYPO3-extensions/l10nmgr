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

require_once t3lib_extMgm::extPath('l10nmgr') . 'interface/interface.tx_l10nmgr_interface_stateImportable.php';

/**
 * Collection of fields to translate
 *
 * class.tx_l10nmgr_domain_translation_fieldCollection.php
 *
 * @author Michael Klapper <klapper@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id$
 * @date $Date$
 * @since 24.04.2009 - 14:16:49
 * @package TYPO3
 * @subpackage tx_l10nmgr
 * @access public
 */
class tx_l10nmgr_domain_translation_fieldCollection extends ArrayObject implements tx_l10nmgr_interface_stateImportable {

	/**
	 * Indicate that the current entity was already processed for the import
	 *
	 * @var boolean
	 */
	protected $isImported = false;

	/**
	 * Mark entity as processed for the import
	 *
	 * @access public
	 * @return void
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function markImported() {
//!TODO refactor this, the object should not allowed to set his own isImported state to true
//		$this->isImported = true;
	}

	/**
	 * Retrieve the import state
	 *
	 * If the FieldCollection contains no field, the import state
	 * is indicated as true while nothing more is to processed.
	 *
	 * @access public
	 * @return boolean
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function isImported() {

		foreach ( $this as $Field ) { /* @var $Field tx_l10nmgr_domain_translation_field */
			if ( ($Field->isImported() === false) && ($Field->isSkipped() === false) ) {
				$this->isImported = false;
				break;
			}
			$this->isImported = true;
		}

			// if the fieldCollection contains no fields
		if ($this->isImported === false && $this->count() === 0) {
			$this->isImported = true;
		}

		return $this->isImported;
	}

	/**
	 *
	 * @param string $index Index key for example "pages_language_overlay:NEW/1/1111:title"
	 *
	 * @throws tx_mvc_exception_argumentOutOfRange
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 *
	 * @return tx_l10nmgr_domain_translation_field
	 */
	public function offsetGet($index) {
		$key   = $index;
		$index =  $this->extractFieldIdentifier($index);

		if (! parent::offsetExists($index)) {
			throw new tx_mvc_exception_argumentOutOfRange('Index "' . var_export($index . ' (' . $key . ')', true) . '" for tx_l10nmgr_domain_translation_field are not available');
		}
		return parent::offsetGet($index);
	}

	/**
	 * Extract the field name inlcuding the flexform path from the given unique id.
	 *
	 * @param string $key For example "pages_language_overlay:NEW/1/1111:title"
	 *
	 * @access protected
	 * @return string Like "title" or "tx_templavoiloa_flex:..."
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	protected function extractFieldIdentifier($key) {

		$field = $flexPointer = '';
		list($first, $second, $field, $flexPointer) = explode(':', $key);

		return $field . ((! is_null($flexPointer)) ? ':' . $flexPointer : '');
	}

	/**
	 *
	 * @param string $index Field path like "pages_language_overlay:NEW/1/1111:title"
	 * @param tx_l10nmgr_domain_translation_field $Field
	 *
	 * @throws InvalidArgumentException
	 *
	 * @access public
	 * @return void
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function offsetSet($index, $Field) {

		if (! $Field instanceof tx_l10nmgr_domain_translation_field ) {
			throw new InvalidArgumentException('Wrong parameter type given, "tx_l10nmgr_domain_translation_field" expected!');
		}
		$index =  $this->extractFieldIdentifier($index);

		parent::offsetSet($index, $Field);
	}

	/**
	 * @deprecated This method can not be used any more, because we need the key build from the identity_key (cmd string)
	 *
	 * @param tx_l10nmgr_domain_translation_field $Field
	 *
	 * @throws Exception
	 * @throws InvalidArgumentException
	 *
	 * @access public
	 * @return void
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function append($Field) {

		throw new Exception('Not supported: Please use offsetSet!');

		if (! $Field instanceof tx_l10nmgr_domain_translation_field ) {
			throw new InvalidArgumentException('Wrong parameter type given, "tx_l10nmgr_domain_translation_field" expected!');
		}

		parent::append($Field);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/domain/translation/class.tx_l10nmgr_domain_translation_fieldCollection.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/domain/translation/class.tx_l10nmgr_domain_translation_fieldCollection.php']);
}

?>