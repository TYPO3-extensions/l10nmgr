<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 Fabrizio Branca (fabrizio.branca@aoemedia.de)
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

class tx_l10nmgr_domain_language_languageRepository extends tx_mvc_ddd_typo3_abstractTCAObjectRepository {

	/**
	 * @var string The name of the objectclass for that this repository s responsible
	 */
	protected $objectClassName = 'tx_l10nmgr_domain_language_language';
	
	/**
	 * (non-PHPdoc)
	 * @see ddd/typo3/tx_mvc_ddd_typo3_abstractTCAObjectRepository#findById($uid, $add_enable_fields)
	 */
	public function findById($id, $add_enable_fields = true){
		if($id == 0){
			$language = new tx_l10nmgr_domain_language_language(array('uid' => 0, 'title' => 'Default'));

		}else{
			$language = parent::findById($id,$add_enable_fields);
		}
		
		return $language;
	}
}
?>