<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Kasper Skårhøj <kasperYYYY@typo3.com>
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

require_once t3lib_extMgm::extPath('l10nmgr') . 'service/class.tx_l10nmgr_service_textConverter.php';
require_once(t3lib_extMgm::extPath('l10nmgr').'views/class.tx_l10nmgr_abstractExportView.php');

/**
 * CATXMLView: Renders the XML for the use for translation agencies
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @author	Daniel Poetzinger <development@aoemedia.de>
 * @author	Daniel Zielinski <d.zielinski@L10Ntech.de>
 * @author	Fabian Seltmann <fs@marketing-factory.de>
 * @author	Andreas Otto <andreas.otto@dkd.de>
 * @package TYPO3
 * @subpackage tx_l10nmgr
 */
class tx_l10nmgr_CATXMLView extends tx_l10nmgr_abstractExportView {

	protected $defaultTemplate = 'EXT:l10nmgr/templates/catxml/catxml.php';

	/**
	 * @var	array		$internalMessges		Part of XML with fail logging information content elements
	 */
	protected $internalMessages = array();

	protected $export_type = 'xml';

	/**
	 * @var boolean
	 */
	protected $skipXMLCheck;

	/**
	 * @var boolean
	 */
	protected $useUTF8Mode;

	/**
	 * @var tx_l10nmgr_service_textConverter
	 */
	protected $xmlTool;

	/**
	 * @return boolean
	 */
	protected function getSkipXMLCheck() {
		return $this->skipXMLCheck;
	}

	/**
	 * @return boolean
	 */
	protected function getUseUTF8Mode() {
		return $this->useUTF8Mode;
	}

	/**
	 * @param boolean $skipXMLCheck
	 */
	public function setSkipXMLCheck($skipXMLCheck) {
		$this->skipXMLCheck = $skipXMLCheck;
	}

	/**
	 * @param boolean $useUTF8Mode
	 */
	public function setUseUTF8Mode($useUTF8Mode) {
		$this->useUTF8Mode = $useUTF8Mode;
	}

	/**
	 * Implementation of abstract method to deliver a filename prefix
	 *
	 * @return string
	 */
	public function getExporttypePrefix() {
		return 'catxml_export';
	}

	/**
	 * Build single item of "internal" message
	 *
	 * @param	string		$message
	 * @param	string		$key
	 * @access	private
	 * @return	void
	 */
	function setInternalMessage($message, $key) {
		$this->internalMessages[] = "\t\t" . '<t3_skippedItem>' . "\n\t\t\t\t" . '<t3_description>' . $message . '</t3_description>' . "\n\t\t\t\t" . '<t3_key>' . $key . '</t3_key>' . "\n\t\t\t" . '</t3_skippedItem>' . "\r";
	}

	/**
	 * Internal method the get the registered messages of the export.
	 *
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 * @param void
	 * @return string
	 */
	protected function getInternalMessagesXML() {
		$res = '';
		if(is_array($this->internalMessages)){
			$res = implode("\n\t",$this->internalMessages);
		}

		return $res;
	}

	/**
	 * Internal method to build the pageGroupXML structure.
	 *
	 * @param void
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	protected function renderPageGroups() {
		foreach ($this->getTranslateableInformation()->getPageGroups() as $pageGroup) { /* @var $pageGroup tx_l10nmgr_models_translateable_pageGroup */
			$pageStartTag = "\t".sprintf('<pageGrp id="%d">', $pageGroup->getUid())."\n";
			$xml .= $pageStartTag;

			foreach ($pageGroup->getTranslateableElements() as $translateableElement) { /* @var $translateableElement tx_l10nmgr_models_translateable_translateableElement */
				 foreach ($translateableElement->getTranslateableFields() as $translateableField) { /* @var $translateableField tx_l10nmgr_models_translateable_translateableField */

				 	if (!$this->modeOnlyChanged || $translateableField->isChanged()) {

						try {
							$table 		= $translateableElement->getTableName();
							$uid 		= $translateableElement->getUid();
							$cType 		= $translateableElement->getCType();
							$fieldType  = $translateableField->getFieldType();
							$key 		= $translateableField->getIdentityKey();
							$data		= $this->getTransformedTranslationDataFromTranslateableField($this->getSkipXMLCheck(), $this->getUseUTF8Mode(),$translateableField,$this->forcedSourceLanguage);
							$needsTrafo = $translateableField->needsTransformation();
							$transformationAttribute = $needsTrafo ? ' transformations="1"' : '';

							$dataTag 	= "\t\t".sprintf('<data table="%s" elementUid="%d" cType="%s" fieldType="%s" key="%s"%s>%s</data> ',$table,$uid,$cType,$fieldType,$key,$transformationAttribute,$data)."\n";
							$xml .= $dataTag;

						} catch(tx_mvc_exception_invalidContent $e) {
							$this->setInternalMessage($e->getMessage(), $uid . '/' . $table . '/' . $key);
						} catch(Exception $e) {
							tx_mvc_common_debug::logException($e);
						}
					}else{
						$this->setInternalMessage('Content not exported: Element is unchanged and option modeOnylChanged is active ', $uid . '/' . $table . '/' . $key);
					}
				}
			}
			$pageEndTag = "\t".'</pageGrp>'."\n";
			$xml .= $pageEndTag;
		}

		$this->setRenderedPageGroups($xml);
	}

	/**
	 * Searches the internal XML Tool Singleton
	 *
	 * @return tx_l10nmgr_service_textConverter
	 */
	protected function TextConverter() {
		if (! ($this->xmlTool instanceof tx_l10nmgr_service_textConverter) ) {
			$this->xmlTool= t3lib_div::makeInstance('tx_l10nmgr_service_textConverter');
		}

		return $this->xmlTool;
	}

	/**
	 * This method is used to transform the data of the export for the correct presentation.
	 *
	 * @var boolean
	 * @var boolean
	 * @todo this should be the new way
	 * @throws tx_mvc_exception_invalidContent
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return string
	 */
	protected function getTransformedTranslationDataFromTranslateableField($skipXMLCheck, $useUTF8mode, $translateableField, $forcedSourceLanguage) {
		$dataForTranslation = $translateableField->getDataForTranslation($forcedSourceLanguage);
		$result = '';

		try {
			if ($translateableField->needsTransformation()) {
				$result = $this->TextConverter()->toXML($dataForTranslation);
			} else {
				$result = $this->TextConverter()->toRaw($dataForTranslation, (bool)$useUTF8mode, true, false);
			}
		} catch (tx_mvc_exception_converter $e) {
			
			try {
				$result = $this->TextConverter()->toRaw($dataForTranslation, (bool)$useUTF8mode, true, true);
				
			} catch(tx_mvc_exception_converter $e) {
				if ($skipXMLCheck) {
					$result = '<![CDATA[' . $this->TextConverter()->toRaw($dataForTranslation, (bool)$useUTF8mode, false) . ']]>';
				} else {
					throw new tx_mvc_exception_invalidContent('Content not exported: No valid XML and option skipXMLCheck not active.');
				}
			}
		}

		return $result;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/views/CATXML/class.tx_l10nmgr_CATXMLView.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/views/CATXML/class.tx_l10nmgr_CATXMLView.php']);
}

?>