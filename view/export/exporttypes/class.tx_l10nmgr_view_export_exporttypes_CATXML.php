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
require_once(t3lib_extMgm::extPath('l10nmgr').'view/export/class.tx_l10nmgr_view_export_abstractExportView.php');

/**
 * CATXMLView: Renders the XML for the use for translation agencies
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @author	Daniel Poetzinger <development@aoemedia.de>
 * @author	Daniel Zielinski <d.zielinski@L10Ntech.de>
 * @author	Fabian Seltmann <fs@marketing-factory.de>
 * @author	Andreas Otto <andreas.otto@dkd.de>
 * @author	Timo Schmidt <timo.schmidt@aoemedia.de>
 * @package TYPO3
 * @subpackage tx_l10nmgr
 */
class tx_l10nmgr_view_export_exporttypes_CATXML extends tx_l10nmgr_view_export_abstractExportView {

	protected $defaultTemplate = 'EXT:l10nmgr/templates/catxml/catxml.php';

	/**
	 * @var	array		$internalMessges		Part of XML with fail logging information content elements
	 */
	protected $internalMessages = array();

	/**
	 * @var string Holds an string identifier for the export type.
	 */
	protected $export_type = 'xml';

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
		foreach ($this->getTranslateableInformation()->getPageGroups() as $pageGroup) { /* @var $pageGroup tx_l10nmgr_domain_translateable_pageGroup */
			$pageStartTag = "\t".sprintf('<pageGrp id="%d">', $pageGroup->getUid())."\n";
			$xml .= $pageStartTag;

			foreach ($pageGroup->getTranslateableElements() as $translateableElement) { /* @var $translateableElement tx_l10nmgr_domain_translateable_translateableElement */
				 foreach ($translateableElement->getTranslateableFields() as $translateableField) { /* @var $translateableField tx_l10nmgr_domain_translateable_translateableField */

				 	if (!$this->modeOnlyChanged || $translateableField->isChanged()) {

						try {
							$table 		= $translateableElement->getTableName();
							$uid 		= $translateableElement->getUid();
							$cType 		= $translateableElement->getCType();
							$fieldType  = $translateableField->getFieldType();
							$key 		= $translateableField->getIdentityKey();
							$data		= $this->getTransformedTranslationDataFromTranslateableField($this->getSkipXMLCheck(), $this->getUseUTF8Mode(),$translateableField,$this->forcedSourceLanguage);
							$needsTrafo = $translateableField->needsTransformation();

								//TODO switch to new format
								// changed in version 1.2 of the XML format
								//$transformationAttribute = $needsTrafo ? ' transformations="1"' : '';
							$transformationType = $translateableField->getTransformationType();
							$dataTag 	= "\t\t".sprintf('<data table="%s" elementUid="%d" cType="%s" fieldType="%s" transformationType="%s" key="%s">%s</data> ',$table,$uid,$cType,$fieldType,$transformationType,$key,$data)."\n";
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

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/views/CATXML/class.tx_l10nmgr_view_export_exporttypes_CATXML.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/views/CATXML/class.tx_l10nmgr_view_export_exporttypes_CATXML.php']);
}

?>