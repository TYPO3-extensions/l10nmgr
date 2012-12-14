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
	public function toXML($content, $removeBrockenUTF8 = false, $pageUid = 0, $table = 'tt_content', $fieldType = 'text', $fieldPath = 'tt_content:0:bodytext') {

		$content = $this->toRaw($content, $removeBrockenUTF8, false);

			//use the RTE configuration from pageTSconfig
			/* @var $beUser t3lib_beUserAuth */
		$beUser = $GLOBALS['BE_USER'];
		$RTEsetup = $beUser->getTSConfig('RTE', t3lib_BEfunc::getPagesTSconfig($pageUid));
		$fieldPathSegments = t3lib_div::trimExplode(':', $fieldPath, false, 4);
		$thisConfig = t3lib_BEfunc::RTEsetup($RTEsetup['properties'], $table, $fieldPathSegments[2], $fieldType);

		$thisConfig['proc.']['dontHSC_rte'] = 0;
			// TODO: switch to use the RTE parameters from field configuration
			// NOTE: this will pass for practically all RTE fields
		$specConf = array(
				'richtext' => 1,
				'rte_transform' => array (
						'parameters' => array ('flag=rte_enabled', 'mode=ts')
				)
		);
		$this->HTMLparser->init($table . ':' . $fieldPathSegments[2], $pageUid);
		$this->HTMLparser->setRelPath('');

			// convert any &amp; you'll find
		$this->HTMLparser->procOptions['dontConvAmpInNBSP_rte'] = true;

		$content = $this->HTMLparser->RTE_transform($content, $specConf, 'rte', $thisConfig);

			// this is needed while the "t3lib_parseHTML_proc" replaces all "&" to the entity "&amp;" he can find.
		$content = t3lib_div::deHSCentities($content);

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
	 * @param boolean $validateToXML
	 * @param boolean $convertEntities
	 * @access public
	 * @uses txl10nmgr_service_textConverter::entities_to_utf8
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @throws tx_mvc_exception_converter
	 * @return string
	 */
	public function toRaw($content, $removeBrockenUTF8Charakter = false, $validateToXML = true, $convertEntities = false) {

		$content = $this->entities_to_utf8($content, true);

			// Final clean up of linebreaks:
			// Make sure no \r\n sequences has entered in the meantime...
		$content = str_replace (
			array(chr(13).chr(10), chr(10).chr(13)),
			array(chr(10),         chr(10)),
			$content
		);

		if ($removeBrockenUTF8Charakter === true) {
			$content = self::utf8_bad_strip($content);
		}

		if($convertEntities) {
			$content = htmlspecialchars($content);
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
	* Strips out any bad bytes from a UTF-8 string and returns the rest
	* PCRE Pattern to locate bad bytes in a UTF-8 string
	* Comes from W3 FAQ: Multilingual Forms
	* Note: modified to include full ASCII range including control chars
	* @see http://www.w3.org/International/questions/qa-forms-utf-8
	* @param string
	* @return string
	* @package utf8
	* @subpackage bad
	*/
	protected static function utf8_bad_strip($str) {
	    $UTF8_BAD =
	    '([\x00-\x7F]'.                          # ASCII (including control chars)
	    '|[\xC2-\xDF][\x80-\xBF]'.               # non-overlong 2-byte
	    '|\xE0[\xA0-\xBF][\x80-\xBF]'.           # excluding overlongs
	    '|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}'.    # straight 3-byte
	    '|\xED[\x80-\x9F][\x80-\xBF]'.           # excluding surrogates
	    '|\xF0[\x90-\xBF][\x80-\xBF]{2}'.        # planes 1-3
	    '|[\xF1-\xF3][\x80-\xBF]{3}'.            # planes 4-15
	    '|\xF4[\x80-\x8F][\x80-\xBF]{2}'.        # plane 16
	    '|(.{1}))';                              # invalid byte
	    ob_start();
	    while (preg_match('/'.$UTF8_BAD.'/S', $str, $matches)) {
	        if ( !isset($matches[2])) {
	            echo $matches[0];
	        }
	        $str = substr($str,strlen($matches[0]));
	    }
	    $result = ob_get_contents();
	    ob_end_clean();
	    return $result;
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
	 * @param boolean $forceCleaningForRTE DEFAULT true If set to true the HTML-tags will be removed before the content is stored into the database.
	 * @access public
	 * @throws tx_mvc_exception_converter
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return string
	 */
	public function toText($content, $importFlexFieldValue = false, $forceCleaningForRTE = true, $pageUid = 0, $table = 'tt_content', $fieldType = 'text', $fieldPath = 'tt_content:0:bodytext') {
			// cleaning up text
		$content = $this->toHtml($content);

			//use the RTE configuration from pageTSconfig
			/* @var $beUser t3lib_beUserAuth */
		$beUser = $GLOBALS['BE_USER'];
		$RTEsetup = $beUser->getTSConfig('RTE', t3lib_BEfunc::getPagesTSconfig($pageUid));
		$fieldPathSegments = t3lib_div::trimExplode(':', $fieldPath, false, 4);
		$thisConfig = t3lib_BEfunc::RTEsetup($RTEsetup['properties'], $table, $fieldPathSegments[2], $fieldType);
			// TODO: switch to use the RTE parameters from field configuration
			// NOTE: this will pass for practically all RTE fields
		$specConf = array(
			'richtext' => 1,
			'rte_transform' => array (
				'parameters' => array ('flag=rte_enabled', 'mode=ts')
			)
		);
		$this->HTMLparser->init($table . ':' . $fieldPathSegments[2], $pageUid);
		$this->HTMLparser->setRelPath('');
		$this->HTMLparser->procOptions['HTMLparser_db.']['xhtml_cleaning'] = true;

		$content = $this->HTMLparser->RTE_transform($content, $specConf, 'db', $thisConfig);

			/* @internal We need to escape the content for the XML flexform structure and reconvert the "&nbsp;" */
		if ($importFlexFieldValue === true) {

			$input = htmlspecialchars($content);
			while(preg_match('/&amp;(\S+);/',$input)) {
				$input = preg_replace('/&amp;(\S+);/','&\1;',$input, 1);
			}
			$content = $input;
		} else {
				// we need this while the HTMLparser won't convert entities to their character if the value comes without "<p>"-Tags
				// => check this option "$this->HTMLparser->procOptions['HTMLparser_db.']['htmlSpecialChars'] = -1;"
			$content = htmlspecialchars_decode($content);
		}

		return $content;
	}

	/**
	 * This method is used to convert thext form an import XML file into a valid database shema.
	 *
	 * @param string $content XML string to convert
	 * @access public
	 * @throws tx_mvc_exception_converter
	 * @author Nikola Stojiljkovic
	 * @return string
	 */
	public function toHtml($content) {
		$tagsWhichNeedClosing = array(
				'a', 'abbr', 'acronym', 'address', 'applet', 'b', 'bdo', 'big', 'blockquote', 'body',
				'button', 'caption', 'center', 'cite', 'code', 'colgroup', 'dd', 'del', 'dfn', 'dir', 'div',
				'dl', 'dt', 'em', 'fieldset', 'font', 'form', 'frameset', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
				'head', 'html', 'i', 'iframe', 'ins', 'kbd', 'label', 'legend', 'li', 'map', 'menu', 'noframes',
				'noscript', 'object', 'ol', 'optgroup', 'option', 'p', 'pre', 'q', 's', 'samp', 'script', 'select',
				'small', 'span', 'strike', 'strong', 'style', 'sub', 'sup', 'table', 'tbody', 'td', 'textarea',
				'tfoot', 'th', 'thead', 'title', 'tr', 'tt', 'u', 'ul', 'var', 'xmp');
			// tags which need closing, SimpleXML is not aware of this
		foreach ($tagsWhichNeedClosing as $tag) {
			$content = preg_replace('|<'.$tag.' ([^>]*)\/>|msU', '<'.$tag.' $1></'.$tag.'>', $content);
			$content = preg_replace('|<'.$tag.'\/>|msU', '<'.$tag.'></'.$tag.'>', $content);
		}
			// this is needed because the "t3lib_parseHTML_proc" dosn't recognise <br/> whitin <li> tags
		$tagsWhichHaveToClose = array (
				'area', 'base', 'basefont', 'br', 'col', 'frame', 'hr', 'img', 'input', 'link', 'meta', 'param');
		foreach ($tagsWhichHaveToClose as $tag) {
			$content = preg_replace('|<'.$tag.'([^>]*)>|msU', '<'.$tag.'$1 />', $content);
			$content = preg_replace('|<'.$tag.'([^>]*)\/ \/>|msU', '<'.$tag.'$1 />', $content);
			$content = preg_replace('|<'.$tag.'([^>]*)\/\/>|msU', '<'.$tag.'$1 />', $content);
		}

			/* @var $parsehtml t3lib_parsehtml */
		$parsehtml = t3lib_div::makeInstance('t3lib_parsehtml');
		$content = $parsehtml->XHTML_clean($content);

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

		$content = '<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE dummy [ <!ENTITY nbsp "&#160;"> ]><dummy>' . $content . '</dummy>';

		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 0);
		xml_parse_into_struct($parser, $content, $vals, $index);

		$xmlParserErrorCode = xml_get_error_code($parser);

		if ($xmlParserErrorCode !== 0) {
			throw new tx_mvc_exception_invalidContent('Given XML string is not valid. [' . xml_error_string($xmlParserErrorCode) . ']');
		}
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
			$trans_tbl = $this->getTranslationTable(true); // Getting them in iso-8859-1 - but thats ok since this is observed below.
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

	/**
	 * Get the contents of a SimpleXMLElement as string (XML)
	 *
	 * @param SimpleXMLElement $field
	 * @return the content of the SimpleXMLElement
	 */
	public function getXMLContent( SimpleXMLElement $field ) {
		if(count($field->children())>0) {

			/**
			 * Here we need to convert the xml structure to it's string representation.
			 * Usually we can simply use $field->asXML() but there is a problem
			 * with empty tags. Empty tags will automaticlly converted to selfclosing tags. This
			 * behavious is usefull for inline tags, but not for block level tags like <div>
			 * Therefore we traverse the DOM and append an empty text node to all blocklevel nodes
			 * to prevent the conversion into an self closing tag.
			 *
			 */
			$domElement = dom_import_simplexml($field);

			/* @var $dom DOMDocument */
			$dom 		= $domElement->ownerDocument;
			$dom->preserveWhiteSpace = true;

			$this->appendEmptyStringToAllBlockLevelTags($dom->documentElement);

			$fieldXML = $dom->saveXML($domElement);
			$fieldXML = str_replace("<?xml version=\"1.0\"?>\n",'',$fieldXML);

			if(preg_match('/^<!DOCTYPE/',$fieldXML)) {
				$fieldXML = substr($fieldXML,strpos($fieldXML,"]>")+3);
			}

			$content = substr($fieldXML,strpos($fieldXML,'>')+1,strrpos($fieldXML,'<')-strpos($fieldXML,'>')-1);
			if(substr($content,0,9)=='<![CDATA[') $content = substr($content,9,-3);

		} else {
			$content = (string)$field;
		}

		return $content;
	}

	/**
	 * Recursive function which visits all childnodes and appends an empty textNode to all
	 * blocklevel tags to prevent a conversion to self closing tags.
	 *
	 * @param DOMNode $parent
	 * @param $maxRecursion
	 */
	protected function appendEmptyStringToAllBlockLevelTags(DOMNode $parent, $maxRecursion = 0){
		if($maxRecursion > 30){ return 0; }

		if($parent->hasChildNodes()){
			foreach($parent->childNodes as $childNode){
				$maxRecursion++;
				$this->appendEmptyStringToAllBlockLevelTags($childNode,$maxRecursion);
			}
		}else{
			//append empty text node to blocklevel tags
			if(!in_array($parent->nodeName,array('img','br','a','hr','area','link'))){
				$parent->appendChild(new DOMText(''));
			}
		}
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/service/class.tx_l10nmgr_service_textConverter.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/service/class.tx_l10nmgr_service_textConverter.php']);
}

?>