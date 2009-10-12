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
 * This repository is used to find importFiles. 
 *
 * {@inheritdoc}
 *
 * class.tx_l10nmgr_domain_importer_importFileRepository.php
 *
 * @author Timo Schmidt <schmidt@aoemedia.de>
 * @subject tx_l10nmgr_domain_importer_importFile
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.tx_l10nmgr_domain_importer_importFileRepository.php $
 * @date 04.05.2009 10:09:31
 * @see tx_mvc_ddd_abstractRepository
 * @category database
 * @package TYPO3
 * @subpackage l10nmgr
 * @access public
 */
class tx_l10nmgr_domain_importer_importFileRepository extends tx_mvc_ddd_abstractRepository {
	/**
	* Must be set!
	* The name of the objectclass for that this repository s responsible
	*
	* @var string
	*/
	protected $objectClassName = 'tx_l10nmgr_domain_importer_importFile';
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/domain/importer/class.tx_l10nmgr_domain_importer_importFileRepository.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/domain/importer/class.tx_l10nmgr_domain_importer_importFileRepository.php']);
}
?>