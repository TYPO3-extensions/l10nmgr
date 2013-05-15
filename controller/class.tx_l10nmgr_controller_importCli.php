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

require_once(t3lib_extMgm::extPath('mvc') . 'mvc/controller/class.tx_mvc_controller_cli.php');
require_once(t3lib_extMgm::extPath('l10nmgr') . 'domain/tools/class.tx_l10nmgr_tools.php');


/**
 * Import controller for cli
 *
 * class.tx_l10nmgr_controller_importCli.php
 *
 * @author Fabrizio Branca <fabrizio.branca@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @version $Id: class.tx_l10nmgr_controller_xmlexport.php $
 * @date 16.04.2009 - 12:28:56
 * @see tx_mvc_controller_action
 * @category controller
 * @package	TYPO3
 * @subpackage	l10nmgr
 * @access public
 */
class tx_l10nmgr_controller_importCli extends tx_mvc_controller_cli {

	protected $cli_help = array(
		'name' => 'Localization Manager Importer',
		'synopsis' => '###OPTIONS###',
		'options' => '',
		'description' => 'Imports previously exported and translated data',
		'examples' => './cli_dispatch.phpsh --file <pathToTheImportFile>',
		'author' => 'Daniel Zielinski - L10Ntech.de, AOE media GmbH (c) 2009',
		'license' => 'GNU GPL - free software!',
	);

	protected $cli_options = array(
		array(
			'--file',
				'Path to the file that should be imported',
		),
		array(
			'--workspace',
			'Workspace ID',
			'Force workspace ID for import (overrides value in translation file)'
		),
		array(
			'--help',
			'Show help',
		),
		array(
			'-h',
			'Same as --help'
		),
		array(
			'--silent',
			'Silent operation, will only output errors and important messages.'
		),
		array(
			'-s',
			'Same as --silent'
		),
		array(
			'-ss',
			'Super silent, will not even output errors or important messages.'
		),
	);

	/**
	 * Inizialize the backend user
	 *
	 * @param void
	 * @return void
	 */
	public function initializeController() {

			// Force user to admin state
		$GLOBALS['BE_USER']->user['admin'] = 1;
	}

	/**
	 * Default action
	 *
	 * @throws tx_mvc_exception_invalidArgument
	 * @return void;
	 */
	public function defaultAction() {

		if (isset($this->arguments['--help']) || isset($this->arguments['-h']) || !isset($this->arguments['--file']) ) {
			return $this->routeToAction('helpAction');
		}

				// Force target workspace for import
			if (isset($this->arguments['--workspace']) && t3lib_utility_Math::canBeInterpretedAsInteger($this->arguments['--workspace'][0])) {
				$GLOBALS['BE_USER']->setWorkspace((int) $this->arguments['--workspace'][0]);
			}

		$destinationFolder = t3lib_div::getFileAbsFileName(tx_mvc_common_typo3::getTCAConfigValue('uploadfolder', tx_l10nmgr_domain_importer_importFile::getTableName(), 'filename'));

		foreach ($this->arguments['--file'] as $file) {

			tx_mvc_validator_factory::getFileValidator()->isValid($file, TRUE);

				// create importData record
			$importData = new tx_l10nmgr_domain_importer_importData();
			$importData['exportdata_id'] = 0; // will be updated later
			$importData['configuration_id'] = 0; // will be updated later
			$importData['import_type'] = 'xml';

			$importDataRepository = new tx_l10nmgr_domain_importer_importDataRepository();
			$importDataRepository->add($importData);

			$pathInfo = pathinfo($file);

				// prepend the importdata uid to the new file name
			$destinationFileName = $importData->getUid() . '_' . $pathInfo['basename'];

			$destinationFile = $destinationFolder . DIRECTORY_SEPARATOR . $destinationFileName;

				// move to upload folder
			if (!copy($file, $destinationFile)) {
				throw new tx_mvc_exception_invalidArgument('Can\'t copy ' . $file . ' to ' . $destinationFile);
			}

			$this->cli_echo(sprintf('Copying file "%s" to "%s"' . chr(10), $file, $destinationFile));

			t3lib_div::fixPermissions($destinationFile);

			$importFile = new tx_l10nmgr_domain_importer_importFile();
			$importFile['importdata_id'] = $importData->getUid();
			$importFile['filename'] = $destinationFileName;

			$importFileRepository = new tx_l10nmgr_domain_importer_importFileRepository();
			$importFileRepository->add($importFile);

			$this->cli_echo('Extracting zip content...');
			$importData->extractAllZipContent();
			$this->cli_echo(" done!\n");

				// Enable detailed progress output
			tx_l10nmgr_tools::setCliMode(TRUE);

			do {
				$this->cli_echo(sprintf('%s%% finished' . "\n", round($importData->getProgressPercentage())));

					// Clear progress counters
				tx_l10nmgr_tools::resetProgressCounters();
			} while (!tx_l10nmgr_domain_importer_importer::performImportRun($importData));
			$this->cli_echo(sprintf('%s%% finished' . "\n", round($importData->getProgressPercentage())));
		}
	}
}
