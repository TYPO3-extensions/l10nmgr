<?php

class tx_l10nmgr_xmltools {
	function isValidXML($xml) {
		$parser = xml_parser_create();
		$vals = array();
		$index = array();

		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 0);
		xml_parse_into_struct($parser, $xml, $vals, $index);
		if (xml_get_error_code($parser))
			return false;
		else
			return true;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/models/tools/class.tx_l10nmgr_xmltools.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/models/tools/class.tx_l10nmgr_xmltools.php']);
}
?>
