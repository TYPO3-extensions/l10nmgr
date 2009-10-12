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

require_once(t3lib_extMgm::extPath('mvc').'mvc/controller/class.tx_mvc_controller_cli.php');

/**
 * Export controller for cli
 *
 * class.tx_l10nmgr_controller_exportCli.php
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
class tx_l10nmgr_controller_exportCli extends tx_mvc_controller_cli {

	protected $cli_help = array(
		'name' => 'Localization Manager Exporter',
		'synopsis' => '###OPTIONS###',
		'options' => '',
		'description' => 'Exports configurations to an export format',
		'examples' => './cli_dispatch.phpsh l10nmgr_export --format=CATXML --config=l10ncfg --target=tlangs --workspace=wsid --hidden=TRUE --updated=FALSE',
		'author' => 'Daniel Zielinski - L10Ntech.de, AOE media GmbH (c) 2009',
		'license' => 'GNU GPL - free software!',
	);

	protected $cli_options = array(
		array(
			'--format',
				'Format for export of tranlatable data',
				"The value of level can be:\n    'CATXML' = XML for translation tools (default)\n    'EXCEL' = Microsoft XML format \n"
		),
		array(
			'--config',
				'Localization Manager configurations',
				"UIDs of the localization manager configurations to be used for export. Comma seperated values, no spaces.\nDefault is EXTCONF which means values are taken from extension configuration.\n"
		),
		array(
			'--target',
				'Target languages',
				"UIDs for the target languages used during export. Comma seperated values, no spaces. Default is 0. In that case UIDs are taken from extension configuration.\n"
		),
		array(
			'--workspace',
				'Workspace ID',
				"UID of the workspace used during export. Default = 0\n"
		),
		array(
			'--hidden',
				'Do not export hidden contents',
				"The values can be: \n    'TRUE' = Hidden content is skipped\n    'FALSE' = Hidden content is exported. Default is FALSE.\n"
		),
		array(
			'--updated',
				'Export only new/updated contents',
				"The values can be: \n    'TRUE' = Only new/updated content is exported\n    'FALSE' = All content is exported (default)\n"
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

		// Set workspace to the required workspace ID from CATXML:
		$GLOBALS['BE_USER']->setWorkspace($this->getWorkspaceId());
	}

	/**
	 * Get workspace id
	 *
	 * @param void
	 * @return int workspace id
	 */
	protected function getWorkspaceId() {
		$workspaceId = isset($this->arguments['--workspace'] ) ? $this->arguments['--workspace'] [0] : '0';
		tx_mvc_validator_factory::getIntGreaterThanValidator()->setMin(-1)->isValid($workspaceId, true);
		return $workspaceId;
	}

	/**
	 * Get format
	 *
	 * @param void
	 * @return string format
	 */
	protected function getFormat() {
		return isset($this->arguments['--format']) ? $this->arguments['--format'][0] : 'CATXML';
	}

	/**
	 * Get l10n configurations
	 *
	 * @param void
	 * @return array of uids of configuration records
	 */
	protected function getL10nConfigurationIds() {
		$l10ncfg = isset($this->arguments['--config']) ? $this->arguments['--config'][0] : 'EXTCONF';

		if ($l10ncfg !== 'EXTCONF' && !empty($l10ncfg)) {
			$l10ncfgArray = t3lib_div::trimExplode(',', $l10ncfg);
		} elseif ($this->configuration->get('l10nmgr_cfg')) {
			$l10ncfgArray = t3lib_div::trimExplode(',', $this->configuration->get('l10nmgr_cfg'));
		} else {
			throw new tx_mvc_exception_invalidArgument('No configuration id found in arguments or extension manager configuration!');
		}

		return $l10ncfgArray;
	}

	/**
	 * Get target language ids
	 *
	 * @param void
	 * @return array of uids of sys_language records
	 */
	protected function getTargetLanguages() {
		$tlang = isset ( $this->arguments['--target'] ) ? $this->arguments['--target'] [0] : '0';
		if ($tlang !== "0") {
			$tlangArray = t3lib_div::trimExplode(',', $tlang );
		} elseif ($this->configuration->get('l10nmgr_tlangs')) {
			$tlangArray = t3lib_div::trimExplode(',', $this->configuration->get('l10nmgr_tlangs'));
		} else {
			throw new tx_mvc_exception_invalidArgument('No target languages found in arguments or extension manager configuration!');
		}
		return $tlangArray;
	}

	/**
	 * Default action
	 *
	 * @param void
	 * @return void;
	 */
	public function defaultAction() {

		if (isset($this->arguments['--help']) || isset($this->arguments['-h'])) {

			return $this->routeToAction('helpAction');

		} else {

			$format = $this->getFormat();

			switch (strtolower($format)) {

				case 'catxml' : {
					return $this->routeToAction('catxmlExportAction');
				} break;

				case 'excel' : {
					throw new tx_mvc_exception_notImplemented('Excel export vi cli is not implemented!');
				} break;

				default: {
					throw new tx_mvc_exception_invalidArgument(sprintf('Format "%s" is not supported!', $format));
				}
			}

		}
	}

	/**
	 * Export to xml
	 *
	 * @param void
	 * @return void
	 */
	public function catxmlExportAction() {
		foreach ($this->getL10nConfigurationIds() as $l10nConfigurationId) {
			foreach ($this->getTargetLanguages() as $targetLanguageId) {
				tx_mvc_validator_factory::getIntGreaterThanValidator()->setMessage('Invalid l10n configuration id')->isValid($l10nConfigurationId, true);
				tx_mvc_validator_factory::getIntGreaterThanValidator()->setMessage('Invalid target language id')->isValid($targetLanguageId, true);

				$this->cli_echo(sprintf('Creating export data record for configuration "%s" and target language "%s"', $l10nConfigurationId, $targetLanguageId) . "\n");

				$configurationRepository = new tx_l10nmgr_domain_configuration_configurationRepository();
				$configuration = $configurationRepository->findById($l10nConfigurationId);

				$exportData = new tx_l10nmgr_domain_exporter_exportData();
				$exportData['pid'] = $configuration->getPid();
				$exportData['l10ncfg_id'] = $configuration->getUid();
				$exportData['title'] = 'Generated by cli'; // TODO make configurable
				$exportData['source_lang'] = 0; // default language
				$exportData['translation_lang'] = $targetLanguageId;
				$exportData['export_type'] = 'xml';
					$onlyChanged = isset($this->arguments['--updated']) ? $this->arguments['--updated'] [0] : 'FALSE';
				$exportData['onlychangedcontent'] = (strtolower($onlyChanged) == 'true');
					$hidden = isset($this->arguments['--hidden'] ) ? $this->arguments['--hidden'][0] : 'FALSE';
				$exportData['nohidden'] = (strtolower($hidden) == 'true');
				// TODO: make configurable
				$exportData['checkforexistingexports'] = 0;
				$exportData['noxmlcheck'] = 0;
				$exportData['checkutf8'] = 0;

				$exportDataRepository = new tx_l10nmgr_domain_exporter_exportDataRepository();
				$exportDataRepository->add($exportData);

				//TODO incomplete ...
//				$exporter = new tx_l10nmgr_domain_exporter_exporter($exportData);

				do {
					$this->cli_echo(sprintf('%s%% finished'."\n", round($exportData->getProgressPercentage())));
				} while (!tx_l10nmgr_domain_exporter_exporter::performFileExportRun($exportData,$this->configuration->get('pagesPerChunk')));
				$this->cli_echo(sprintf('%s%% finished'."\n", round($exportData->getProgressPercentage())));

			}
		}
	}

}

?>