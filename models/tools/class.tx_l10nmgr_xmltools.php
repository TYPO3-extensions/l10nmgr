<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Kasper Sk�rh�j <kasperYYYY@typo3.com>
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
 * Contains xml tools
 *
 * $Id$
 *
 * @author	Daniel P�tzinger <development@aoemedia.de>
 */
require_once(t3lib_extMgm::extPath('l10nmgr').'models/tools/class.tx_l10nmgr_utf8tools.php');
require_once PATH_t3lib . 'class.t3lib_cs.php';

class tx_l10nmgr_xmltools {

	/**
	 * @var t3lib_parseHTML_proc
	 */
	protected $HTMLparser = null;

	public function __construct() {
		throw new Exception('Obsolete');
		$this->HTMLparser = t3lib_div::makeInstance("t3lib_parseHTML_proc");
	}

	/**
	 * Verify that the given string is based on a valid XML structure.
	 *
	 * @param string $content
	 * @access public
	 * @uses tx_l10nmgr_xmltools::isValidXML
	 * @return boolean
	 */
	function isValidXMLString($content) {
		return $this->isValidXML (
			'<!DOCTYPE dummy [ <!ENTITY nbsp " "> ]><dummy>' . $content . '</dummy>'
		);
	}

	/**
	 * Verify that the given string is formed as valid XML structure.
	 *
	 * @param string $content
	 * @access public
	 * @return boolean
	 */
	public function isValidXML($content) {
		$parser  = xml_parser_create();
		$vals    = array();
		$index   = array();
		$isValid = false;

		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 0);
		xml_parse_into_struct($parser, $content, $vals, $index);

		if (xml_get_error_code($parser) === 0)
			$isValid = true;

		return $isValid;
	}

	/**
	 * Transforms a RTE Field to valid XML
	 *
	 * @param	string		HTML String which should be transformed
	 * @return	mixed		false if transformation failed, string with XML if all fine
	 */
	function RTE2XML($content, $withStripBadUTF8 = 0) {

		$content = $this->HTMLparser->TS_images_rte($content);
		$content = $this->HTMLparser->TS_links_rte($content);
		$content = $this->HTMLparser->TS_transform_rte($content, 1);

			//substitute & with &amp;
		$content=str_replace('&','&amp;',$content);
		$content=t3lib_div::deHSCentities($content);

		if ($withStripBadUTF8 == 1) {
			$content = tx_l10nmgr_utf8tools::utf8_bad_strip($content);
		}
		if ($this->isValidXMLString($content)) {
			return $content;
		}
		else {
			return false;
		}
	}
	/**
	 * Transforms a XML back to RTE / reverse function of RTE2XML
	 *
	 * @param	string		XMLString which should be transformed
	 * @return	string		string with HTML
	 */
	function XML2RTE($xmlstring) {
		//!TODO fixed setting of Parser (TO-DO set it via typoscript)

			//Added because import failed
		$xmlstring=str_replace('<br/>','<br>',$xmlstring);
		$xmlstring=str_replace('<br />','<br>',$xmlstring);

		$this->HTMLparser->procOptions['typolist']=FALSE;
		$this->HTMLparser->procOptions['typohead']=FALSE;
		$this->HTMLparser->procOptions['keepPDIVattribs']=TRUE;
		$this->HTMLparser->procOptions['dontConvBRtoParagraph']=TRUE;

		if (!is_array($this->HTMLparser->procOptions['HTMLparser_db.'])) {
			$this->parseHTML->procOptions['HTMLparser_db.']=array();
		}

		$this->HTMLparser->procOptions['HTMLparser_db.']['xhtml_cleaning']=TRUE;
			//trick to preserve strong tags
		$this->HTMLparser->procOptions['denyTags']='strong';
		$this->HTMLparser->procOptions['preserveTables']=TRUE;
		$this->HTMLparser->procOptions['dontRemoveUnknownTags_db']=TRUE;

		$content = $this->HTMLparser->TS_transform_db($xmlstring, 0); // removes links from content if not called first!
		$content = $this->HTMLparser->TS_images_db($content);
		$content = $this->HTMLparser->TS_links_db($content);

		return $content;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/models/tools/class.tx_l10nmgr_xmltools.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/models/tools/class.tx_l10nmgr_xmltools.php']);
}

?>