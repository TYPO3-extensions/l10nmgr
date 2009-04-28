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
 * Translation base
 *
 * class.tx_l10nmgr_service_translationBase.php
 *
 * @author Michael Klapper <klapper@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id$
 * @date $Date$
 * @since 28.04.2009 - 14:43:36
 * @package TYPO3
 * @subpackage ex_l10nmgr
 * @access public
 */
class tx_l10nmgr_service_translationBase {

	/**
	 * Save the incoming translationData object into the database
	 * if the available translatableObject are match the configuration.
	 *
	 * @param tx_l10nmgr_models_translateable_translateableInformation $TranslatableInformation
	 * @param tx_l10nmgr_domain_translation_data $TranslationData
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function save(tx_l10nmgr_models_translateable_translateableInformation $TranslatableInformation, tx_l10nmgr_domain_translation_data $TranslationData) {

	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/service/class.tx_l10nmgr_service_translationBase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/service/class.tx_l10nmgr_service_translationBase.php']);
}

?>