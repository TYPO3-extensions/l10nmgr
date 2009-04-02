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
 * @date 01.04.2009 - 11:44:31
 * @package	TYPO3
 * @subpackage	l10nmgr
 * @access public
 */
class tx_l10nmgr_l10nLanguage implements ArrayAccess {

	protected $row;
	
	/**
	* loads internal array with sys_language record
	* @param int	$id		Id of the cfg record
	* @return void
	**/
	function load($id) {
		$this->row = t3lib_BEfunc::getRecord('sys_language', $id);
	}

	public function offsetExists($key){
		return array_key_exists($this->row,$key);
	}
	
	public function offsetGet($key){
		return $this->row[$key];
	}
	
	public function offsetSet($key, $value){
		$this->row[$key] = $value;
	}
	
	public function offsetUnset($key){
		unset($this->row[$key]);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext//l10nmgr/models/class.tx_l10nmgr_l10nLanguage.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext//l10nmgr/models/class.tx_l10nmgr_l10nLanguage.php']);
}
?>