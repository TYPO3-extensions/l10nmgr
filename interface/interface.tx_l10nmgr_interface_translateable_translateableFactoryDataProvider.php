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
 * Model that provides a structured array by a XML translation file
 *
 * interface.tx_l10nmgr_interface_translateable_translateableFactoryDataProvider.php
 *
 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id$
 * @date $Date$
 * @since 24.04.2009 - 10:20:37
 * @package TYPO3
 * @subpackage tx_l10nmgr
 * @access public
 */
interface tx_l10nmgr_interface_translateable_translateableFactoryDataProvider{

	/**
	 * The implementation of the method should return an ArrayObject
	 * with all relevant tablenames.
	 *
	 * @access public
	 * @return ArrayObject
	 */
	public function getRelevantTables();

	/**
	 * Sould return an ArrayObject with all relevant pageIds.
	 *
	 * @access public
	 * @return ArrayObject
	 */
	public function getRelevantPageIds();

	/**
	 * Should return an ArrayObject with all relevant elementIds
	 *
	 * @param string $tableName
	 * @param integer $pageId
	 * @access public
	 * @return ArrayObject collection with element
	 */
	public function getRelevantElementIdsByTablenameAndPageId($tableName,$pageId);

	/**
	 * Should return an ArrayObject with all relevant TranslationDetails
	 *
	 * The translationDetail result should have the following structure:
	 *
	 * @param string $tableName
	 * @param integer $elementId
	 * @access public
	 * @return array
	 */
	public function getTranslationDetailsByTablenameAndElementId($tableName,$elementId);
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/interfaces/interface.tx_l10nmgr_interface_translateable_translateableFactoryDataProvider.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/interfaces/interface.tx_l10nmgr_interface_translateable_translateableFactoryDataProvider.php']);
}

?>