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
 * class.tx_l10nmgr_models_translateable_PageGroup.php
 *
 * @author	 Timo Schmidt <schmidt@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.class_name.php $
 * @date 03.04.2009 - 10:06:51
 * @package	TYPO3
 * @subpackage	l10nmgr
 * @access public
 */
class tx_l10nmgr_models_translateable_PageGroup {
	
	/**
	 * Holds the assigned translateableElements
	 * @var ArrayObject
	 */
	protected $translateableElements;
	
	/**
	 * Hold the page_row of the pageGroup
	 *
	 * @var array
	 */
	protected $page_row;
	
	

	
	/**
	 * Constructor 
	 * 
	 * @param void
	 */
	public function __construct(){
		$this->translateableElements = new ArrayObject();
	
	}

	
	/**
	 * Method to initialize the pageGroup from a page row of the database.
	 *
	 * @param array $row
	 */
	public function setPageRow($row){
		$this->page_row = $row;
	}
	
	public function getPageId(){
		return $this->page_row['uid'];
	}
	
	
	/**
	 * Method to add a translateableElement to the PageGroup.
	 * 
	 * @param tx_l10nmgr_models_translateable_translateableElement
	 */
	public function addTranslateableElement(tx_l10nmgr_models_translateable_translateableElement $translateableElement){
		$this->translateableElements->append($translateableElement);
	}
}

?>