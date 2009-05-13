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

require_once t3lib_extMgm::extPath('l10nmgr') . 'models/tools/class.tx_l10nmgr_utf8tools.php';
require_once PATH_t3lib . 'class.t3lib_cs.php';

/**
 * Converter for content.
 *
 * Futher documentation about the transformations can be found here:
 * - http://typo3.org/documentation/document-library/core-documentation/doc_core_api/4.2.0/view/5/2/
 * - http://typo3.org/documentation/document-library/references/doc_core_tsref/4.2.0/view/1/5/#id4198758
 *
 * class.tx_l10nmgr_service_textConverter.php
 *
 * @author Michael Klapper <klapper@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id$
 * @date $Date$
 * @since 05.05.2009 - 18:41:42
 * @package TYPO3
 * @subpackage tx_l10nmgr
 * @access public
 */
class tx_l10nmgr_service_textConverter extends t3lib_cs {

	/**
	 * @var t3lib_parseHTML_proc
	 */
	protected $HTMLparser = null;

	/**
	 * @access public
	 * @return void
	 */
	public function __construct() {
		$this->HTMLparser = t3lib_div::makeInstance("t3lib_parseHTML_proc");
	}

	/**
	 * Transforms a RTE Field to valid XML.
	 *
	 * @param string $content RTE styled string to convert into valid XML
	 * @param boolean $stripBadUTF8 DEFAULT is false
	 * @access public
	 * @uses txl10nmgr_service_textConverter::toRaw
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @throws tx_mvc_exception_converter
	 * @return string XML valid structure
	 */
	public function toXML($content, $removeBrockenUTF8 = false) {

		$content = $this->toRaw($content, $removeBrockenUTF8, false);

			// convert any &amp; you'll find
		$this->HTMLparser->procOptions['dontConvAmpInNBSP_rte'] = false;
		$this->HTMLparser->procOptions['allowTagsOutside ']     = 'img,hr,div';
		$this->HTMLparser->procOptions['preserveDIVSections']   = true;

			//!TODO switch to use the RTE configuration from pageTSconfig - $this->HTMLparser->RTE_transform();
			// Transform the content into valid XHTML style
		$content = $this->HTMLparser->TS_transform_rte (
			$this->HTMLparser->TS_links_rte (
				$this->HTMLparser->TS_images_rte($content)
			),
			true
		);

		//!TODO configure the parser that xhtml_cleaning is used

			//!FIXME replace this with a core "t3lib_parseHTML_proc" configuration
			// this is needed while the "t3lib_parseHTML_proc" replaces all "&" to the entity "&amp;" he can find.
		$content = str_replace (
			array('&amp;lt;', '&amp;gt;', '&amp;quot;'),
			array('&lt;',     '&gt;',     '&quot;'),
			$content
		);

		try {
			$this->isValidXML($content);
		} catch (tx_mvc_exception_invalidContent $e) {
			throw new tx_mvc_exception_converter($e->getMessage());
		}

		return $content;
	}

	/**
	 * Convert plain text into XML valid form.
	 *
	 * Entities are converted into UTF-8 charakter (see "entities_to_utf8()").
	 * And optional bad UTF-8 charakter are also remvoed.
	 *
	 * @param string $content Plain input to convert into valid XML
	 * @param boolean $removeBrockenUTF8Charakter DEFAULT is false
	 * @access public
	 * @uses txl10nmgr_service_textConverter::entities_to_utf8
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @throws tx_mvc_exception_converter
	 * @return string
	 */
	public function toRaw($content, $removeBrockenUTF8Charakter = false, $validateToXML = true, $convertAllEntitiesToUTF8 = false) {

		$content = $this->entities_to_utf8($content, true);

		if ($removeBrockenUTF8Charakter === true) {
			$content = tx_l10nmgr_utf8tools::utf8_bad_strip($content);
		}

		if ($validateToXML === true) {

			try {
				$this->isValidXML($content);
			} catch (tx_mvc_exception_invalidContent $e) {
				throw new tx_mvc_exception_converter($e->getMessage());
			}
		}

		return $content;
	}

	/**
	 * This method is used to convert thext form an import XML file into a valid database shema.
	 *
	 * The given text which should stored in the database needs to be converted first into a valid TYPO3 formated text style.
	 * That means for example that:
	 * - "<a>" tags should be converted to "<link>" tags.
	 *
	 * Reverse function of toXML() or toRaw()
	 *
	 * @param string $content XML string to convert
	 * @param boolean $importFlexFieldValue OPTIONAL if an value for flexforms should be imported use this option to escape the htmlspecialchars
	 *                                       This option is current not in use - while the TCEmain convert the htmlspecialchars self way.
	 * @access public
	 * @throws tx_mvc_exception_converter
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return string
	 */
	public function toText($content, $importFlexFieldValue = false) {
		//!TODO switch to use the RTE configuration from pageTSconfig
		//$this->HTMLparser->RTE_transform();

		$this->HTMLparser->procOptions['typolist']              = false;
		$this->HTMLparser->procOptions['typohead']              = false;
		$this->HTMLparser->procOptions['keepPDIVattribs']       = true;
		$this->HTMLparser->procOptions['dontConvBRtoParagraph'] = true;
			//trick to preserve strong tags
		$this->HTMLparser->procOptions['denyTags']                 = 'strong';
		$this->HTMLparser->procOptions['preserveTables']           = true;
		$this->HTMLparser->procOptions['dontRemoveUnknownTags_db'] = true;

		if (!is_array($this->HTMLparser->procOptions['HTMLparser_db.'])) {
			$this->HTMLparser->procOptions['HTMLparser_db.']    = array();
		}

		$this->HTMLparser->procOptions['HTMLparser_db.']['xhtml_cleaning'] = true;

		$content = $this->HTMLparser->TS_links_db (
			$this->HTMLparser->TS_images_db (
				$this->HTMLparser->TS_transform_db($content, 0) // removes links from content if not called first!
			)
		);

			/* @internal We need to escape the content for the XML flexform structure and reconvert the "&nbsp;" */
		if ($importFlexFieldValue === true) {
			$content = str_replace (
				'&amp;nbsp;',
				'&nbsp;',
				htmlspecialchars($content)
			);
		} else {
				// we need this while the HTMLparser won't convert entities to their character if the value comes without "<p>"-Tags
				// => check this option "$this->HTMLparser->procOptions['HTMLparser_db.']['htmlSpecialChars'] = -1;"
			$content = htmlspecialchars_decode($content);
		}

		return $content;
	}

	/**
	 * Verify that the given string contains a valid XML structure.
	 *
	 * A "dummy" doctype is used with an declared entity "&nbsp;".
	 *
	 * @param string $content
	 * @throws tx_mvc_exception_invalidContent
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function isValidXML($content) {
		$parser  = xml_parser_create();
		$vals    = array();
		$index   = array();

		$content = '<!DOCTYPE dummy [ <!ENTITY nbsp " "> ]><dummy>' . $content . '</dummy>';

		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 0);
		xml_parse_into_struct($parser, $content, $vals, $index);

		$xmlParserErrorCode = xml_get_error_code($parser);

		if ($xmlParserErrorCode !== 0)
			throw new tx_mvc_exception_invalidContent('Given XML string is not valid. [' . xml_error_string($xmlParserErrorCode) . ']');
	}

	/**
	 * Method to map the "get_html_translation_table" and remove
	 * the htmlspecialchars from the list.
	 *
	 * The "%amp;" will not be removed from the list as default.
	 *
	 * @param boolean $excludeAmp OPTIONAL default is "false" If set
	 *                                      it to "true" it will be also removed from the result of "get_html_translation_table"
	 * @access private
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return array
	 */
	private function getTranslationTable($excludeAmp = false) {
		$translationHTMLEnties = get_html_translation_table(HTML_ENTITIES);
		$mappedArray = $translationHTMLEnties;

			// remove entities from the array
		foreach ($mappedArray as $index => $entity) {
			if (
					($entity === '&nbsp;')
				||
					($entity === '&lt;')
				||
					($entity === '&gt;')
				||
					($entity === '&quot;')
			 	||
					(($excludeAmp === true) && ($entity === '&amp;'))
			 ) {
				unset($translationHTMLEnties[$index]);
			}
		}

		return array_flip($translationHTMLEnties);
	}

	/**
	 * Converts numeric entities (UNICODE, eg. decimal (&#1234;) or hexadecimal (&#x1b;)) to UTF-8 multibyte chars
	 *
	 * We want to keep the original of the &nbsp; entity.
	 *
	 * @param	string		Input string, UTF-8
	 * @param	boolean		If set, then all string-HTML entities (like &amp; or &pound; will be converted as well)
	 * @uses tx_l10nmgr_service_textConverter::getTranslationTable()
	 * @return	string		Output string
	 */
	function entities_to_utf8($str, $alsoStdHtmlEnt=0) {

		if ($alsoStdHtmlEnt) {
			$trans_tbl = $this->getTranslationTable(); // Getting them in iso-8859-1 - but thats ok since this is observed below.
		}

		$token = md5(microtime());
		$parts = explode($token,ereg_replace('(&([#[:alnum:]]*);)',$token.'\2'.$token,$str));
		foreach($parts as $k => $v)	{
			if ($k%2)	{
				if (substr($v,0,1)=='#')	{	// Dec or hex entities:
					if (substr($v,1,1)=='x')	{
						$parts[$k] = $this->UnumberToChar(hexdec(substr($v,2)));
					} else {
						$parts[$k] = $this->UnumberToChar(substr($v,1));
					}
				} elseif ($alsoStdHtmlEnt && $trans_tbl['&'.$v.';']) {	// Other entities:
					$parts[$k] = $this->utf8_encode($trans_tbl['&'.$v.';'],'iso-8859-1');
				} else {	// No conversion:
					$parts[$k] ='&'.$v.';';
				}
			}
		}

		return implode('',$parts);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/service/class.tx_l10nmgr_service_textConverter.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/service/class.tx_l10nmgr_service_textConverter.php']);
}

?>