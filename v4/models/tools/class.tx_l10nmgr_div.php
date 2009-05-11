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
 * Collection of static methods
 *
 * @author	Fabrizio Branca <fabrizio.branca@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: $
 * @since 2009-05-11
 * @package TYPO3
 * @subpackage l10nmgr
 */
class tx_l10nmgr_div {

	/**
	 * Do a translation from a file using a callback function
	 *
	 * @param string inputfile
	 * @param string outputfile
	 * @param callback callback function that does the "dummy translation"
	 * @return void
	 * @author Fabrizio Branca <fabrizio.branca@aoemedia.de>
	 */
	public static function translate($inputFile, $outputFile, $callback = array('tx_l10nmgr_div', 'dummyTranslate')) {
		tx_mvc_validator_factory::getFileValidator()->isValid($inputFile, true);

		$inputFileContent = file_get_contents($inputFile);

		$outputFileContent = preg_replace_callback(
			/* pattern */ '/<data(.*)>(.*)<\/data>/U',
			/* callback */ $callback,
			/* subject */ $inputFileContent
		);

		file_put_contents($outputFile, $outputFileContent);
	}

	/**
	 * Callback function that "translates" the content by adding "translated" to the end of the content
	 *
	 * @param array matched content
	 * @return string content to replace
	 * @author Fabrizio Branca <fabrizio.branca@aoemedia.de>
	 */
	public static function dummyTranslate($input) {

		$content = $input[2];

		if (strpos($content, ']]>') !== false) {
			$content = str_replace(']]>', ' translated]]>', $content);
		} elseif ($content == strip_tags($content)) {
				$content .= ' translated';
		} else {
			$content .= '<p>translated</p>';
		}

		return sprintf('<data%s>%s</data>', $input[1], $content);
	}

}

?>