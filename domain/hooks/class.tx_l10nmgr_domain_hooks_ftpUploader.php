<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 Fabrizio Branca (fabrizio.branca@aoemedia.de)
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

class tx_l10nmgr_domain_hooks_ftpUploader {

	public function upload(array $params, tx_l10nmgr_domain_exporter_exporter $exporter) {

		$emConf = tx_mvc_common_typo3::getExtensionManagerConfiguration('l10nmgr');

		if ($emConf['enable_ftp'] == 1 && defined('TYPO3_cliMode')) {

			$fileName = t3lib_div::getFileAbsFileName(tx_mvc_common_typo3::getTCAConfigValue('uploadfolder', tx_l10nmgr_domain_exporter_exportData::getTableName(), 'filename')) . '/' . $exporter->getExportData(TRUE)->getFilename();

			$connection = ftp_connect($emConf['ftp_server']);
				if ($connection === false) throw new Exception('Connection failed!');
			$res = ftp_login($connection, $emConf['ftp_server_username'], $emConf['ftp_server_password']);
				if ($res === false) throw new Exception('Could not login!');
			if($emConf['ftp_passive_mode']) {
				$res = ftp_pasv($connection, TRUE);
				if ($res === false) throw new Exception('Could not switch to passive mode.');
			}
			$res = ftp_put($connection, $emConf['ftp_server_path'] . $exporter->getExportData(TRUE)->getFilename(), $fileName, FTP_BINARY);
				if ($res === false) {
					$msg = 'Transfer failed!';
					if(!$emConf['ftp_passive_mode']) {
						$msg .= ' See if enabling ftp_passive_mode in the extension settings helps.';
					}
					throw new Exception($msg);
				}
			$res = ftp_close($connection);
				if ($res === false) throw new Exception('Could not close the connection');

		}
	}

}

?>