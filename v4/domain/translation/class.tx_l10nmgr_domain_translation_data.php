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

require_once t3lib_extMgm::extPath('l10nmgr') . 'domain/translation/class.tx_l10nmgr_domain_translation_pageCollection.php';

/**
 * Translation data object which holds the metadata of an XML file
 *
 * class.tx_l10nmgr_domain_translation_data.php
 *
 * @author Michael Klapper <klapper@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id$
 * @date $Date$
 * @since 24.04.2009 - 11:44:48
 * @package TYPO3
 * @subpackage tx_l10nmgr
 * @access public
 */
class tx_l10nmgr_domain_translation_data {

	/**
	 * Translation configuration record uid of table "tx_l10nmgr_cfg"
	 *
	 * @var integer
	 */
	protected $l10ncfgUid = 0;

	/**
	 * Uid of the sys_language from the Exported language
	 *
	 * @var integer
	 */
	protected $sysLanguageUid = 0;

	/**
	 * Column "lg_iso_2" of the "static_language" database table
	 * Needed for the flexform translation handling
	 *
	 * @see $this->sysLanguage
	 * @var string
	 */
	protected $sourceLanguageISOcode = '';

	/**
	 * Uid of the sys_language where the translation should be imported into
	 *
	 * @var integer
	 */
	protected $targetLanguageUid = 0;

	/**
	 * Base url from the exported system
	 *
	 * @var string
	 */
	protected $baseUrl = '';

	/**
	 * Uid of the sys_workspace record where the export was made from
	 *
	 * @var integer
	 */
	protected $workspaceId = 0;

	/**
	 * Count of the available fields from the XML export file
	 *
	 * @var integer
	 */
	protected $fieldCount = 0;

	/**
	 * Count of the words stored in the XML file
	 *
	 * @var integer
	 */
	protected $wordCount = 0;

	/**
	 * System messages produced by the exporter
	 *
	 * Possible messages are:
	 * - Error message
	 * - Warning
	 * - Notice
	 *
	 * @var ArrayObject
	 */
	protected $messages = null;

	/**
	 * Version of the XML struct defined by constant "L10NMGR_FILEVERSION"
	 *
	 * @see EXT:l10nmgr/ext_localconf.php
	 * @var float
	 */
	protected $formatVersion = 0;

	/**
	 * All available pages from the XML export file
	 *
	 * @var tx_l10nmgr_domain_translation_pageCollection
	 */
	protected $PagesCollection = null;

	/**
	 * Find fieldCollection for current parameter
	 *
	 * Note:
	 * An tx_mvc_exception_argumentOutOfRange Exception is thrown if an index not available.
	 *
	 * @param integer $pageUid
	 * @param string $tableName
	 * @param integer $elementUid
	 * @param string $uniqueKey EXAMPLE "pages_language_overlay:NEW/1/1111:title"
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return tx_l10nmgr_domain_translation_fieldCollection
	 */
	public function findByTableUidAndKey($pageUid, $tableName, $elementUid, $uniqueKey) {

		return $this->getPagesCollection()->offsetGet($pageUid)->getElementCollection()->offsetGet($tableName . ':' . $elementUid)->getFieldCollection()->offsetGet($uniqueKey);
	}

	/**
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return string
	 */
	public function getBaseUrl() {
		return $this->baseUrl;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return integer
	 */
	public function getFieldCount() {
		return $this->fieldCount;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return float
	 */
	public function getFormatVersion() {
		return $this->formatVersion;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return integer
	 */
	public function getL10ncfgUid() {
		return $this->l10ncfgUid;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return ArrayObject
	 */
	public function getMessages() {
		return $this->messages;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return tx_l10nmgr_domain_translation_pageCollection
	 */
	public function getPagesCollection() {
		return $this->PagesCollection;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return integer
	 */
	public function getSysLanguageUid() {
		return $this->sysLanguageUid;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return string
	 */
	public function getSourceLanguageISOcode() {
		return $this->sourceLanguageISOcode;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @param string $sourceLanguageISOcode
	 */
	public function setSourceLanguageISOcode($sourceLanguageISOcode) {
		$this->sourceLanguageISOcode = $sourceLanguageISOcode;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return integer
	 */
	public function getTargetLanguageUid() {
		return $this->targetLanguageUid;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return integer
	 */
	public function getWordCount() {
		return $this->wordCount;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return integer
	 */
	public function getWorkspaceId() {
		return $this->workspaceId;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @param string $baseUrl
	 */
	public function setBaseUrl($baseUrl) {
		$this->baseUrl = $baseUrl;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @param integer $fieldCount
	 */
	public function setFieldCount($fieldCount) {
		$this->fieldCount = $fieldCount;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @param float $formatVersion
	 */
	public function setFormatVersion($formatVersion) {
		$this->formatVersion = $formatVersion;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @param integer $l10ncfgUid
	 */
	public function setL10ncfgUid($l10ncfgUid) {
		$this->l10ncfgUid = $l10ncfgUid;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @param ArrayObject $messages
	 */
	public function setMessages($messages) {
		$this->messages = $messages;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @param tx_l10nmgr_domain_translation_pageCollection $PagesCollection
	 */
	public function setPagesCollection(tx_l10nmgr_domain_translation_pageCollection $PagesCollection) {
		$this->PagesCollection = $PagesCollection;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @param integer $sysLanguageUid
	 */
	public function setSysLanguageUid($sysLanguageUid) {
		$this->sysLanguageUid = $sysLanguageUid;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @param integer $targetLanguageUid
	 */
	public function setTargetLanguageUid($targetLanguageUid) {
		$this->targetLanguageUid = $targetLanguageUid;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @param integer $wordCount
	 */
	public function setWordCount($wordCount) {
		$this->wordCount = $wordCount;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @param integer $workspaceId
	 */
	public function setWorkspaceId($workspaceId) {
		$this->workspaceId = $workspaceId;
	}

	/**
	 * Returns a collection of page ids which are relevant for this translation.
	 *
	 * @param void
	 * @return ArrayObject
	 * @author Timo Schmidt
	 */
	public function getPageIdCollection(){
		$pageIdCollection = new ArrayObject();

		for ( $it = $this->PagesCollection->getIterator(); $it->valid(); $it->next() ) {
			$currentPage = $it->current();
			$pageIdCollection->append($currentPage->getUid());
		}

		return $pageIdCollection;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/domain/translation/class.tx_l10nmgr_domain_translation_data.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/domain/translation/class.tx_l10nmgr_domain_translation_data.php']);
}

?>