<?php
namespace Localizationteam\L10nmgr\Cli;

/***************************************************************
 *  Copyright notice
 *  (c) 2009 Daniel Zielinski (d.zielinski
 *
 * @l10ntech.de)
 *  All rights reserved
 *  [...]

 */

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Controller\CommandLineController;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Lang\LanguageService;
use Localizationteam\L10nmgr\View\ExcelXmlView;
use Localizationteam\L10nmgr\View\CatXmlView;
use Localizationteam\L10nmgr\Model\L10nConfiguration;

if (!defined('TYPO3_cliMode')) {
    die('You cannot run this script directly!');
}

// Load language support
/* @var $lang LanguageService*/
$lang = GeneralUtility::makeInstance(LanguageService::class);
$fileRef = 'EXT:l10nmgr/Resources/Private/Language/Cli/locallang.xml';
$lang->includeLLFile($fileRef);

class Export extends CommandLineController
{

    /**
     * @var array $lConf Extension configuration
     */
    var $lConf;

    /**
     * Constructor
     */
    function Export()
    {

        // Running parent class constructor
        parent::__construct();

        // Adding options to help archive:
        $this->cli_options[] = array(
            '--format',
            'Format for export of translatable data',
            "The value can be:\n  CATXML = XML for translation tools (default)\n  EXCEL = Microsoft XML format \n"
        );
        $this->cli_options[] = array(
            '--config',
            'Localization Manager configurations',
            "UIDs of the localization manager configurations to be used for export. Comma seperated values, no spaces.\nDefault is EXTCONF which means values are taken from extension configuration.\n"
        );
        $this->cli_options[] = array(
            '--target',
            'Target languages',
            "UIDs for the target languages used during export. Comma seperated values, no spaces. Default is 0. In that case UIDs are taken from extension configuration.\n"
        );
        $this->cli_options[] = array(
            '--workspace',
            'Workspace ID',
            "UID of the workspace used during export. Default = 0\n"
        );
        $this->cli_options[] = array(
            '--hidden',
            'Do not export hidden contents',
            "The values can be: \n TRUE = Hidden content is skipped\n FALSE = Hidden content is exported. Default is FALSE.\n"
        );
        $this->cli_options[] = array(
            '--updated',
            'Export only new/updated contents',
            "The values can be: \n TRUE = Only new/updated content is exported\n FALSE = All content is exported (default)\n"
        );
        $this->cli_options[] = array(
            '--check-exports',
            'Check for already exported content',
            "The values can be: \n TRUE = Check if content has already been exported\n FALSE = Don't check, just create a new export (default)\n"
        );
        $this->cli_options[] = array('--help', 'Show help', "");
        $this->cli_options[] = array('-h', 'Same as --help', "");

        // Setting help texts:
        $this->cli_help['name'] = 'Localization Manager exporter';
        $this->cli_help['synopsis'] = '###OPTIONS###';
        $this->cli_help['description'] = 'Class with export functionality for l10nmgr';
        $this->cli_help['examples'] = '/.../cli_dispatch.phpsh l10nmgr_export --format=CATXML --config=l10ncfg --target=tlangs --workspace=wsid --hidden=TRUE --updated=FALSE';
        $this->cli_help['author'] = 'Daniel Zielinski - L10Ntech.de, (c) 2009';
    }

    /**
     * CLI engine
     *
     * @param array $argv Command line arguments
     * @return string
     */
    public function cli_main($argv)
    {

        global $lang;

        // Performance measuring
        $time_start = microtime(true);

        // Load the configuration
        $this->loadExtConf();

        if (isset($this->cli_args['--help']) || isset($this->cli_args['-h'])) {
            $this->cli_validateArgs();
            $this->cli_help();
            exit;
        }

        // get format (CATXML,EXCEL)
        //$format = (string)$this->cli_args['_DEFAULT'][1];
        $format = isset($this->cli_args['--format']) ? $this->cli_args['--format'][0] : 'CATXML';

        // get l10ncfg command line takes precedence over extConf
        //$l10ncfg = (string)$this->cli_args['_DEFAULT'][2];
        $l10ncfg = isset($this->cli_args['--config']) ? $this->cli_args['--config'][0] : 'EXTCONF';
        if ($l10ncfg !== 'EXTCONF' && !empty($l10ncfg)) {
            //export single
            $l10ncfgs = explode(',', $l10ncfg);
        } elseif (!empty($this->lConf['l10nmgr_cfg'])) {
            //export multiple
            $l10ncfgs = explode(',', $this->lConf['l10nmgr_cfg']);
        } else {
            $this->cli_echo($lang->getLL('error.no_l10ncfg.msg') . "\n");
            exit;
        }

        // get target languages
        //$tlang = (string)$this->cli_args['_DEFAULT'][3]; //extend to list of target languages!
        $tlang = isset($this->cli_args['--target']) ? $this->cli_args['--target'][0] : '0';
        if ($tlang !== "0") {
            //export single
            $tlangs = explode(',', $tlang);
        } elseif (!empty($this->lConf['l10nmgr_tlangs'])) {
            //export multiple
            $tlangs = explode(',', $this->lConf['l10nmgr_tlangs']);
        } else {
            $this->cli_echo($lang->getLL('error.target_language_id.msg') . "\n");
            exit;
        }

        // get workspace ID
        //$wsId = (string)$this->cli_args['_DEFAULT'][4];
        $wsId = isset($this->cli_args['--workspace']) ? $this->cli_args['--workspace'][0] : '0';
        if (MathUtility::canBeInterpretedAsInteger($wsId) === false) {
            $this->cli_echo($lang->getLL('error.workspace_id_int.msg') . "\n");
            exit;
        }
        $msg = '';

        // Force user to admin state
        $GLOBALS['BE_USER']->user['admin'] = 1;

        // Set workspace to the required workspace ID from CATXML:
        $GLOBALS['BE_USER']->setWorkspace($wsId);

        if ($format == 'CATXML') {
            foreach ($l10ncfgs as $l10ncfg) {
                if (MathUtility::canBeInterpretedAsInteger($l10ncfg) === false) {
                    $this->cli_echo($lang->getLL('error.l10ncfg_id_int.msg') . "\n");
                    exit;
                }
                foreach ($tlangs as $tlang) {
                    if (MathUtility::canBeInterpretedAsInteger($tlang) === false) {
                        $this->cli_echo($lang->getLL('error.target_language_id_integer.msg') . "\n");
                        exit;
                    }
                    $msg .= $this->exportCATXML($l10ncfg, $tlang);
                }
            }
        } elseif ($format == 'EXCEL') {
            foreach ($l10ncfgs as $l10ncfg) {
                if (MathUtility::canBeInterpretedAsInteger($l10ncfg) === false) {
                    $this->cli_echo($lang->getLL('error.l10ncfg_id_int.msg') . "\n");
                    exit;
                }
                foreach ($tlangs as $tlang) {
                    if (MathUtility::canBeInterpretedAsInteger($tlang) === false) {
                        $this->cli_echo($lang->getLL('error.target_language_id_integer.msg') . "\n");
                        exit;
                    }
                    $msg .= $this->exportEXCELXML($l10ncfg, $tlang);
                }
            }
        }
        // Send email notification if set

        $time_end = microtime(true);
        $time = $time_end - $time_start;
        $this->cli_echo($msg . LF);
        $this->cli_echo(sprintf($lang->getLL('export.process.duration.message'), $time) . LF);
    }

    /**
     * The function loadExtConf loads the extension configuration.
     *
     * @return void
     */
    function loadExtConf()
    {
        // Load the configuration
        $this->lConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['l10nmgr']);
    }

    /**
     * exportCATXML which is called over cli
     *
     * @param integer $l10ncfg ID of the configuration to load
     * @param integer $tlang ID of the language to translate to
     * @return string An error message in case of failure
     */
    function exportCATXML($l10ncfg, $tlang)
    {

        global $lang;

        $error = '';

        // Load the configuration
        $this->loadExtConf();

        /** @var $l10nmgrCfgObj L10nConfiguration */
        $l10nmgrCfgObj = GeneralUtility::makeInstance(L10nConfiguration::class);
        $l10nmgrCfgObj->load($l10ncfg);
        if ($l10nmgrCfgObj->isLoaded()) {

            /** @var $l10nmgrGetXML CatXmlView */
            $l10nmgrGetXML = GeneralUtility::makeInstance(CatXmlView::class,
                $l10nmgrCfgObj, $tlang);

            // Check if sourceLangStaticId is set in configuration and set setForcedSourceLanguage to this value
            if ($l10nmgrCfgObj->getData('sourceLangStaticId') && ExtensionManagementUtility::isLoaded('static_info_tables')) {
                $staticLangArr = BackendUtility::getRecordRaw('sys_language',
                    'static_lang_isocode = ' . $l10nmgrCfgObj->getData('sourceLangStaticId'), 'uid');
                if (is_array($staticLangArr) && ($staticLangArr['uid'] > 0)) {
                    $forceLanguage = $staticLangArr['uid'];
                    $l10nmgrGetXML->setForcedSourceLanguage($forceLanguage);
                }
            }

            $onlyChanged = isset($this->cli_args['--updated']) ? $this->cli_args['--updated'][0] : 'FALSE';
            if ($onlyChanged === 'TRUE') {
                $l10nmgrGetXML->setModeOnlyChanged();
            }
            $hidden = isset($this->cli_args['--hidden']) ? $this->cli_args['--hidden'][0] : 'FALSE';
            if ($hidden === 'TRUE') {
                $GLOBALS['BE_USER']->uc['moduleData']['xMOD_tx_l10nmgr_cm1']['noHidden'] = true;
                $l10nmgrGetXML->setModeNoHidden();
            }

            // If the check for already exported content is enabled, run the ckeck.
            $checkExportsCli = isset($this->cli_args['--check-exports']) ? (bool)$this->cli_args['--check-exports'][0] : false;
            $checkExports = $l10nmgrGetXML->checkExports();
            if (($checkExportsCli === true) && ($checkExports === false)) {
                $this->cli_echo($lang->getLL('export.process.duplicate.title') . ' ' . $lang->getLL('export.process.duplicate.message') . LF);
                $this->cli_echo($l10nmgrGetXML->renderExportsCli() . LF);
            } else {
                // Save export to XML file
                $xmlFileName = PATH_site . $l10nmgrGetXML->render();
                $l10nmgrGetXML->saveExportInformation();

                // If email notification is set send export files to responsible translator
                if ($this->lConf['enable_notification'] == 1) {
                    if (empty($this->lConf['email_recipient'])) {
                        $this->cli_echo($lang->getLL('error.email.repient_missing.msg') . "\n");
                    } else {
	                    // ToDo: make email configuration run again
                        // $this->emailNotification($xmlFileName, $l10nmgrCfgObj, $tlang);
                    }
                } else {
                    $this->cli_echo($lang->getLL('error.email.notification_disabled.msg') . "\n");
                }

                // If FTP option is set upload files to remote server
                if ($this->lConf['enable_ftp'] == 1) {
                    if (file_exists($xmlFileName)) {
                        $error .= $this->ftpUpload($xmlFileName, $l10nmgrGetXML->getFileName());
                    } else {
                        $this->cli_echo($lang->getLL('error.ftp.file_not_found.msg') . "\n");
                    }
                } else {
                    $this->cli_echo($lang->getLL('error.ftp.disabled.msg') . "\n");
                }

                if ($this->lConf['enable_notification'] == 0 && $this->lConf['enable_ftp'] == 0) {
                    $this->cli_echo(sprintf($lang->getLL('export.file_saved.msg'), $xmlFileName) . "\n");
                }
            }
        } else {
            $error .= $lang->getLL('error.l10nmgr.object_not_loaded.msg') . "\n";
        }

        return ($error);
    }

    /**
     * The function emailNotification sends an email with a translation job to the recipient specified in the extension config.
     *
     * @param string $xmlFileName Name of the XML file
     * @param L10nConfiguration $l10nmgrCfgObj L10N Manager configuration object
     * @param integer $tlang ID of the language to translate to
     * @return string Error message
     */
    function emailNotification($xmlFileName, $l10nmgrCfgObj, $tlang)
    {
        global $lang;

        // Get source & target language ISO codes
        $sourceStaticLangArr = BackendUtility::getRecord('static_languages',
            $l10nmgrCfgObj->l10ncfg['sourceLangStaticId'], 'lg_iso_2');
        $targetStaticLang = BackendUtility::getRecord('sys_language', $tlang, 'static_lang_isocode');
        $targetStaticLangArr = BackendUtility::getRecord('static_languages', $targetStaticLang['static_lang_isocode'],
            'lg_iso_2');
        $sourceLang = $sourceStaticLangArr['lg_iso_2'];
        $targetLang = $targetStaticLangArr['lg_iso_2'];

        // Construct email message
        /** @var $email t3lib_htmlmail */
        $email = GeneralUtility::makeInstance('t3lib_htmlmail');
        $email->start();
        $email->useQuotedPrintable();

        $email->subject = sprintf($lang->getLL('email.suject.msg'), $sourceLang, $targetLang,
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']);
        if (empty($GLOBALS['BE_USER']->user['email']) || empty($GLOBALS['BE_USER']->user['realName'])) {
            $email->from_email = $this->lConf['email_sender'];
            $email->from_name = $this->lConf['email_sender_name'];
            $email->replyto_email = $this->lConf['email_sender'];
            $email->replyto_name = $this->lConf['email_sender_name'];
        } else {
            $email->from_email = $GLOBALS['BE_USER']->user['email'];
            $email->from_name = $GLOBALS['BE_USER']->user['realName'];
            $email->replyto_email = $GLOBALS['BE_USER']->user['email'];
            $email->replyto_name = $GLOBALS['BE_USER']->user['realName'];
        }
        $email->organisation = $this->lConf['email_sender_organisation'];

        $message = array(
            'msg1' => $lang->getLL('email.greeting.msg'),
            'msg2' => '',
            'msg3' => sprintf($lang->getLL('email.new_translation_job.msg'), $sourceLang, $targetLang,
                $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']),
            'msg4' => $lang->getLL('email.info.msg'),
            'msg5' => $lang->getLL('email.info.import.msg'),
            'msg6' => '',
            'msg7' => $lang->getLL('email.goodbye.msg'),
            'msg8' => $email->from_name,
            'msg9' => '--',
            'msg10' => $lang->getLL('email.info.exportef_file.msg'),
            'msg11' => $xmlFileName,
        );
        if ($this->lConf['email_attachment']) {
            $message['msg3'] = sprintf($lang->getLL('email.new_translation_job_attached.msg'), $sourceLang, $targetLang,
                $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']);
        }
        $msg = implode(chr(10), $message);

        $email->addPlain($msg);
        if ($this->lConf['email_attachment']) {
            $email->addAttachment($xmlFileName);
        }
        $email->send($this->lConf['email_recipient']);
    }

    /**
     * The function ftpUpload puts an export on a remote FTP server for further processing
     *
     * @param string $xmlFileName Path to the file to upload
     * @param string $filename Name of the file to upload to
     * @return string Error message
     */
    function ftpUpload($xmlFileName, $filename)
    {

        global $lang;
        $error = '';

        $connection = ftp_connect($this->lConf['ftp_server']) or die("Connection failed");
        if ($connection) {
            if (@ftp_login($connection, $this->lConf['ftp_server_username'], $this->lConf['ftp_server_password'])) {
                if (ftp_put($connection, $this->lConf['ftp_server_path'] . $filename, $xmlFileName, FTP_BINARY)) {
                    ftp_close($connection) or die("Couldn't close connection");
                } else {
                    $error .= sprintf($lang->getLL('error.ftp.connection.msg'), $this->lConf['ftp_server_path'],
                            $filename) . "\n";
                }
            } else {
                $error .= sprintf($lang->getLL('error.ftp.connection_user.msg'),
                        $this->lConf['ftp_server_username']) . "\n";
                ftp_close($connection) or die("Couldn't close connection");
            }
        } else {
            $error .= $lang->getLL('error.ftp.connection_failed.msg');
        }

        return $error;
    }

    /**
     * exportEXCELXML which is called over cli
     *
     * @param integer $l10ncfg ID of the configuration to load
     * @param integer $tlang ID of the language to translate to
     * @return string An error message in case of failure
     */
    function exportEXCELXML($l10ncfg, $tlang)
    {

        global $lang;

        $error = '';

        // Load the configuration
        $this->loadExtConf();

        /** @var $l10nmgrCfgObj L10nConfiguration */
        $l10nmgrCfgObj = GeneralUtility::makeInstance(L10nConfiguration::class);
        $l10nmgrCfgObj->load($l10ncfg);
        if ($l10nmgrCfgObj->isLoaded()) {

            /** @var $l10nmgrGetXML ExcelXmlView */
            $l10nmgrGetXML = GeneralUtility::makeInstance(ExcelXmlView::class,
                $l10nmgrCfgObj, $tlang);

            // Check if sourceLangStaticId is set in configuration and set setForcedSourceLanguage to this value
            if ($l10nmgrCfgObj->getData('sourceLangStaticId') && ExtensionManagementUtility::isLoaded('static_info_tables')) {
                $staticLangArr = BackendUtility::getRecordRaw('sys_language',
                    'static_lang_isocode = ' . $l10nmgrCfgObj->getData('sourceLangStaticId'), 'uid');
                if (is_array($staticLangArr) && ($staticLangArr['uid'] > 0)) {
                    $forceLanguage = $staticLangArr['uid'];
                    $l10nmgrGetXML->setForcedSourceLanguage($forceLanguage);
                }
            }

            $onlyChanged = isset($this->cli_args['--updated']) ? $this->cli_args['--updated'][0] : 'FALSE';
            if ($onlyChanged === 'TRUE') {
                $l10nmgrGetXML->setModeOnlyChanged();
            }
            $hidden = isset($this->cli_args['--hidden']) ? $this->cli_args['--hidden'][0] : 'FALSE';
            if ($hidden === 'TRUE') {
                $GLOBALS['BE_USER']->uc['moduleData']['xMOD_tx_l10nmgr_cm1']['noHidden'] = true;
                $l10nmgrGetXML->setModeNoHidden();
            }

            // If the check for already exported content is enabled, run the ckeck.
            $checkExportsCli = isset($this->cli_args['--check-exports']) ? (bool)$this->cli_args['--check-exports'][0] : false;
            $checkExports = $l10nmgrGetXML->checkExports();
            if (($checkExportsCli === true) && ($checkExports == false)) {
                $this->cli_echo($lang->getLL('export.process.duplicate.title') . ' ' . $lang->getLL('export.process.duplicate.message') . LF);
                $this->cli_echo($l10nmgrGetXML->renderExportsCli() . LF);
            } else {
                // Save export to XML file
                $xmlFileName = $l10nmgrGetXML->render();
                $l10nmgrGetXML->saveExportInformation();

                // If email notification is set send export files to responsible translator
                if ($this->lConf['enable_notification'] == 1) {
                    if (empty($this->lConf['email_recipient'])) {
                        $this->cli_echo($lang->getLL('error.email.repient_missing.msg') . "\n");
                    } else {
                        $this->emailNotification($xmlFileName, $l10nmgrCfgObj, $tlang);
                    }
                } else {
                    $this->cli_echo($lang->getLL('error.email.notification_disabled.msg') . "\n");
                }

                // If FTP option is set upload files to remote server
                if ($this->lConf['enable_ftp'] == 1) {
                    if (file_exists($xmlFileName)) {
                        $error .= $this->ftpUpload($xmlFileName, $l10nmgrGetXML->getFileName());
                    } else {
                        $this->cli_echo($lang->getLL('error.ftp.file_not_found.msg') . "\n");
                    }
                } else {
                    $this->cli_echo($lang->getLL('error.ftp.disabled.msg') . "\n");
                }

                if ($this->lConf['enable_notification'] == 0 && $this->lConf['enable_ftp'] == 0) {
                    $this->cli_echo(sprintf($lang->getLL('export.file_saved.msg'), $xmlFileName) . "\n");
                }
            }
        } else {
            $error .= $lang->getLL('error.l10nmgr.object_not_loaded.msg') . "\n";
        }

        return ($error);
    }
}

// Call the functionality
/** @var $cleanerObj Export */
$cleanerObj = GeneralUtility::makeInstance(Export::class);
$cleanerObj->cli_main($_SERVER['argv']);