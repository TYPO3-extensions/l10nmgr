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

class tx_l10nmgr_models_hooks_emailNotifier {

	public function notify(array $params, tx_l10nmgr_models_exporter_exporter $exporter) {

		$emConf = tx_mvc_common_typo3::getExtensionManagerConfiguration('l10nmgr');

		if ($emConf['enable_notification'] && defined('TYPO3_cliMode')) {

			tx_mvc_validator_factory::getNotEmptyStringValidator()->isValid($emConf['email_recipient'], true);

			// require_once (t3lib_extMgm::extPath ( 'lang', 'lang.php' ));
			// $GLOBALS['LANG'] = t3lib_div::makeInstance ( 'language' );
			if (! $GLOBALS['LANG']->csConvObj instanceof t3lib_cs) {
				$GLOBALS['LANG']->csConvObj = t3lib_div::makeInstance('t3lib_cs');
			}

			$GLOBALS['LANG']->includeLLFile('EXT:l10nmgr/cli/locallang.xml');

			// Get source & target language ISO codes
			$sourceLang = $exporter->getExportData()->getSourceIsoCode();
			$targetLang = $exporter->getExportData()->getTranslationIsoCode();

			// Construct email message
			$email = t3lib_div::makeInstance('t3lib_htmlmail');
			$email->start();
			$email->useQuotedPrintable();

			$email->subject = sprintf($GLOBALS['LANG']->getLL('email.suject.msg'), $sourceLang, $targetLang, $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']);
			if (empty($GLOBALS['BE_USER']->user['email']) || empty($GLOBALS['BE_USER']->user['realName'])) {
				$email->from_email = $emConf['email_sender'];
				$email->from_name = $emConf['email_sender_name'];
				$email->replyto_email = $emConf['email_sender'];
				$email->replyto_name = $emConf['email_sender_name'];
			} else {
				$email->from_email = $GLOBALS['BE_USER']->user['email'];
				$email->from_name = $GLOBALS['BE_USER']->user['realName'];
				$email->replyto_email = $GLOBALS['BE_USER']->user['email'];
				$email->replyto_name = $GLOBALS['BE_USER']->user['realName'];
			}
			$email->organisation = $emConf['email_sender_organisation'];

			$message = array (
				'msg1' => $GLOBALS['LANG']->getLL('email.greeting.msg'),
				'msg2' => '',
				'msg3' => sprintf($GLOBALS['LANG']->getLL('email.new_translation_job.msg'), $sourceLang, $targetLang, $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']),
				'msg4' => $GLOBALS['LANG']->getLL('email.info.msg'),
				'msg5' => $GLOBALS['LANG']->getLL('email.info.import.msg'),
				'msg6' => '',
				'msg7' => $GLOBALS['LANG']->getLL('email.goodbye.msg'),
				'msg8' => $email->from_name,
				'msg9' => '--',
				'msg10' => $GLOBALS['LANG']->getLL('email.info.exportef_file.msg'), 'msg11' => $xmlFileName
			);
			if ($emConf['email_attachment']) {
				$message['msg3'] = sprintf($GLOBALS['LANG']->getLL('email.new_translation_job_attached.msg'), $sourceLang, $targetLang, $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']);
			}

			$email->addPlain(implode(chr(10), $message));

			if ($emConf['email_attachment']) {
				$fileName = t3lib_div::getFileAbsFileName(tx_mvc_common_typo3::getTCAConfigValue('uploadfolder', tx_l10nmgr_models_exporter_exportData::getTableName(), 'filename')) . '/' . $exporter->getExportData()->getFilename();
				echo $fileName;
				$email->addAttachment($fileName);
			}
			$email->send($emConf['email_recipient']);

		}
	}

}

?>