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
 * Language class
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

require_once t3lib_extMgm::extPath('l10nmgr').'models/language/class.tx_l10nmgr_models_language_staticLanguageRepository.php';

class tx_l10nmgr_models_language_language extends tx_mvc_ddd_typo3_abstractTCAObject {

	/**
	 * Initialize the database object with
	 * the table name of current object
	 *
	 * @access public
	 * @return string
	 */
	public static function getTableName() {
		return 'sys_language';
	}

	/**
	 * Returns the static language from the static_info_tables
	 *
	 * @return tx_l10nmgr_models_language_staticLanguage
	 */
	public function getStaticLanguage(){
		if (!empty($this->row['static_lang_isocode'])) {
			if (empty($this->row['static_lang_isocode_object'])) {
				$staticLanguageRepository = new tx_l10nmgr_models_language_staticLanguageRepository();
				$this->row['static_lang_isocode_object'] = $staticLanguageRepository->findById($this->row['static_lang_isocode']);
				if (!$this->row['static_lang_isocode_object'] instanceof tx_l10nmgr_models_language_staticLanguage) {
					throw new Exception('Object is not an instance of "tx_l10nmgr_models_language_staticLanguage"');
				}
			}
			return $this->row['static_lang_isocode_object'];
		}
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext//l10nmgr/models/class.tx_l10nmgr_l10nLanguage.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext//l10nmgr/models/class.tx_l10nmgr_l10nLanguage.php']);
}
?>