<?php
namespace Localizationteam\L10nmgr\Cli;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008 Daniel Zielinski (d.zielinski@l10ntech.de)
 *  (c) 2011 Francois Suter (typo3@cobweb.ch)
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

if (!defined('TYPO3_cliMode')) {
    die('You cannot run this script directly!');
}

use TYPO3\CMS\Core\Controller\CommandLineController;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Localizationteam\L10nmgr\Model\L10nConfiguration;
use Localizationteam\L10nmgr\Model\L10nBaseService;
use Localizationteam\L10nmgr\Model\TranslationData;
use Localizationteam\L10nmgr\Model\TranslationDataFactory;
use Localizationteam\L10nmgr\Model\CatXmlImportManager;
use Localizationteam\L10nmgr\Model\MkPreviewLinkService;
use Localizationteam\L10nmgr\Zip;
use TYPO3\CMS\Lang\LanguageService;

// Load language support
/* @var $lang LanguageService*/
$lang = GeneralUtility::makeInstance(LanguageService::class);
$fileRef = 'EXT:l10nmgr/Resources/Private/Language/Cli/locallang.xml';
$lang->includeLLFile($fileRef);

/**
 * Class for handling import of translations from the command-line
 *
 * @author  Daniel Zielinski <d.zielinski@l10ntech.de>
 * @author  Francois Suter <typo3@cobweb.ch>
 * @package TYPO3
 * @subpackage tx_l10nmgr
 */
class Import extends CommandLineController
{

    /**
     * @var array Extension's configuration as from the EM
     */
    protected $extensionConfiguration = array();

    /**
     * @var array List of command-line arguments
     */
    protected $callParameters = array();

    /**
     * @var integer ID of the language being handled
     */
    protected $sysLanguage;

    /**
     * @var string Path to temporary de-archiving directory, to be removed after import
     */
    protected $directoryToCleanUp;

    /**
     * @var array List of files that were imported, with additional information, used for reporting after import
     */
    protected $filesImported = array();

    /**
     * @var array List of error messages
     */
    protected $errors = array();

    /**
     * Constructor
     */
    function Import()
    {

        // Running parent class constructor
        parent::__construct();

        // Load the extension's configuration
        $this->extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['l10nmgr']);

        // Adding specific CLI options
        $this->cli_options[] = array(
            '--task',
            'The task to execute',
            "The values can be:\n  importString = Import a XML string\n  importFile = Import a XML file\n  preview = Generate a preview of the source from a XML string\n"
        );
        $this->cli_options[] = array(
            '--preview',
            'Preview flag',
            "Set to 1 in case of preview, 0 otherwise. Defaults to 0.\n"
        );
        $this->cli_options[] = array('--string', 'XML string', "XML string to import.\n");
        $this->cli_options[] = array(
            '--file',
            'Import file',
            "Path to the file to import. Can be XML or ZIP archive. If both XML string and import file are not defined, will import from FTP server (if defined).\n"
        );
        $this->cli_options[] = array('--server', 'Server link', "Server link for the preview URL.\n");
        $this->cli_options[] = array(
            '--importAsDefaultLanguage',
            'Import as default language',
            "If set this setting will overwrite the default language during the import.\nThe values can be: \n TRUE = Content will be imported as default language.\n FALSE = Content will be imported as translation (default).\n"
        );

        // Setting help texts
        $this->cli_help['name'] = 'Localization Manager importer';
        $this->cli_help['synopsis'] = '###OPTIONS###';
        $this->cli_help['description'] = 'Class with import functionality for l10nmgr';
        $this->cli_help['examples'] = "/.../cli_dispatch.phpsh l10nmgr_import --task=importFile --file=foo/bar/translation.xml\nOld syntax was preserved for backwards compatibility:\n/.../cli_dispatch.phpsh l10nmgr_import import|importPreview|preview CATXML serverlink";
        $this->cli_help['author'] = "Daniel Zielinski - L10Ntech.de, (c) 2008\nFrancois Suter - Cobweb, (c) 2011";
    }

    /**
     * Main method called during the CLI run
     *
     * @param array $argv Command line arguments
     * @return string
     */
    public function cli_main($argv)
    {
        global $LANG;
        // Parse the command-line arguments
        $this->initializeCallParameters();

        // Performance measurement
        $start = microtime(true);

        // Exit early if no task is defined
        if (empty($this->callParameters['task'])) {
            $this->cli_help();
            exit;
        }

        // Force user to admin state
        $formerAdminState = $GLOBALS['BE_USER']->user['admin'];
        $GLOBALS['BE_USER']->user['admin'] = 1;

        // Handle the task
        $msg = '';
        switch ($this->callParameters['task']) {
            case 'importString':
            case 'preview':
                // Get workspace id from CATXML
                // Continue if found, else exit script execution
                try {
                    $wsId = $this->getWsIdFromCATXML($this->callParameters['string']);

                    // Set workspace to the required workspace ID from CATXML:
                    $GLOBALS['BE_USER']->setWorkspace($wsId);

                    if ($this->callParameters['task'] == 'importString') {
                        $msg .= $this->importCATXML();
                    } else {
                        $msg .= $this->previewSource();
                    }
                } catch (Exception $e) {
                    $this->cli_echo("No workspace ID from CATXML\n");
                    exit;
                }
                break;
            case 'importFile':
                $msg .= $this->importXMLFile();
                break;
        }

        // Calculate duration and output result message
        $end = microtime(true);
        $time = $end - $start;
        $this->cli_echo($msg . LF);
        $this->cli_echo(sprintf($LANG->getLL('import.process.duration.message'), $time) . LF);

        // Send reporting mail
        $this->sendMailNotification();

        // Restore user's former admin state
        // May not be absolutely necessary, but cleaner in case anything gets executed after this script
        $GLOBALS['BE_USER']->user['admin'] = $formerAdminState;
    }

    /**
     * This method reads the command-line arguments and prepares a list of call parameters
     * It takes care of backwards-compatibility with the old way of calling the import script
     *
     * @return void
     */
    protected function initializeCallParameters()
    {
        $task = '';
        // Get the task parameter from either the new or the old input style
        // Sanitize value and make it empty if not properly defined
        if (isset($this->cli_args['--task'])) {
            $task = $this->cli_args['--task'][0];
            if ($task != 'importString' && $task != 'importFile' && $task != 'preview') {
                $task = '';
            }
        } elseif (isset($this->cli_args['_DEFAULT'][1])) {
            $input = (int)$this->cli_args['_DEFAULT'][1];
            switch ($input) {
                case 1:
                    $task = 'importString';
                    break;
                case 2:
                    $task = 'importFile';
                    break;
                case 3:
                    $task = 'preview';
                    break;
                default:
                    $task = '';
            }
        }
        $this->callParameters['task'] = $task;
        // Get the preview flag
        $preview = false;
        if (isset($this->cli_args['--preview'])) {
            $preview = (boolean)$this->cli_args['--preview'][0];
        } elseif (isset($this->cli_args['_DEFAULT'][2])) {
            $preview = (boolean)$this->cli_args['_DEFAULT'][2];
        }
        $this->callParameters['preview'] = $preview;
        // Get the XML string
        $string = '';
        if (isset($this->cli_args['--string'])) {
            $string = (string)$this->cli_args['--string'][0];
        } elseif (isset($this->cli_args['_DEFAULT'][3])) {
            $string = (string)$this->cli_args['_DEFAULT'][3];
        }
        $this->callParameters['string'] = stripslashes($string);
        // Get the path to XML or ZIP file
        $file = '';
        if (isset($this->cli_args['--file'])) {
            $file = (string)$this->cli_args['--file'][0];
        } elseif (isset($this->cli_args['_DEFAULT'][4])) {
            $file = (string)$this->cli_args['_DEFAULT'][4];
        }
        $this->callParameters['file'] = $file;
        // Get the server link for preview
        $server = '';
        if (isset($this->cli_args['--server'])) {
            $server = (string)$this->cli_args['--server'][0];
        } elseif (isset($this->cli_args['_DEFAULT'][5])) {
            $server = (string)$this->cli_args['_DEFAULT'][5];
        }
        $this->callParameters['server'] = $server;
        // Import as default language
        $importAsDefaultLanguage = false;
        if (isset($this->cli_args['--importAsDefaultLanguage'])) {
            $importAsDefaultLanguage = (bool)$this->cli_args['--importAsDefaultLanguage'][0];
        } elseif (isset($this->cli_args['_DEFAULT'][6])) {
            $importAsDefaultLanguage = (bool)$this->cli_args['_DEFAULT'][6];
        }
        $this->callParameters['importAsDefaultLanguage'] = $importAsDefaultLanguage;
    }

	/**
	 * Get workspace ID from XML (quick & dirty)
	 * @param string $xml XML string to parse
	 * @return int ID of the workspace to import to
	 * @throws \TYPO3\CMS\Core\Exception
	 */
    protected function getWsIdFromCATXML($xml)
    {
        preg_match('/<t3_workspaceId>([^<]+)/', $xml, $matches);
        if (!empty($matches)) {
            return $matches[1];
        } else {
            throw new Exception('No workspace id found', 1322475562);
        }
    }

    /**
     * Imports a CATXML string
     *
     * @return string Output
     */
    protected function importCATXML()
    {

        global $LANG;
        $out = '';
        $error = '';

        /** @var $service L10nBaseService */
        $service = GeneralUtility::makeInstance(L10nBaseService::class);
        if ($this->callParameters['importAsDefaultLanguage']) {
            $service->setImportAsDefaultLanguage(true);
        }
        /** @var $factory TranslationDataFactory */
        $factory = GeneralUtility::makeInstance(TranslationDataFactory::class);

        /** @var $importManager CatXmlImportManager */
        $importManager = GeneralUtility::makeInstance(CatXmlImportManager::class, '',
            $this->sysLanguage,
            $this->callParameters['string']);

        // Parse and check XML, load header data
        if ($importManager->parseAndCheckXMLString() === false) {
            $tmp = var_export($importManager->headerData, true);
            $tmp = str_replace("\n", '', $tmp);
            $error .= $tmp;
            $error .= $LANG->getLL('import.manager.error.parsing.xmlstring.message');
            $this->cli_echo($error);
            exit;
        } else {

            // Find l10n configuration record
            /** @var $l10ncfgObj L10nConfiguration */
            $l10ncfgObj = GeneralUtility::makeInstance(L10nConfiguration::class);
            $l10ncfgObj->load($importManager->headerData['t3_l10ncfg']);
            $status = $l10ncfgObj->isLoaded();
            if ($status === false) {
                $this->cli_echo("l10ncfg not loaded! Exiting...\n");
                exit;
            }

            //Do import...
            $this->sysLanguage = $importManager->headerData['t3_sysLang']; //set import language to t3_sysLang from XML

            //Delete previous translations
            $importManager->delL10N($importManager->getDelL10NDataFromCATXMLNodes($importManager->xmlNodes));

            //Make preview links
            if ($this->callParameters['preview']) {
                $pageIds = array();
                if (empty($importManager->headerData['t3_previewId'])) {
                    $pageIds = $importManager->getPidsFromCATXMLNodes($importManager->xmlNodes);
                } else {
                    $pageIds[0] = $importManager->headerData['t3_previewId'];
                }
                /** @var $mkPreviewLinks MkPreviewLinkService */
                $mkPreviewLinks = GeneralUtility::makeInstance(MkPreviewLinkService::class,
                    $importManager->headerData['t3_workspaceId'], $importManager->headerData['t3_sysLang'], $pageIds);
                $previewLink = $mkPreviewLinks->mkSinglePreviewLink($importManager->headerData['t3_baseURL'],
                    $this->callParameters['server']);
                $out .= $previewLink;
            }

            /** @var $translationData TranslationData */
            $translationData = $factory->getTranslationDataFromCATXMLNodes($importManager->getXMLNodes());
            $translationData->setLanguage($this->sysLanguage);
            unset($importManager);

            $service->saveTranslation($l10ncfgObj, $translationData);
            if (empty($out)) {
                $out = 1;
            } //Means OK if preview = 0
            return ($out);
        }
    }

    /**
     * Previews the source to import
     *
     * @return string Result output
     */
    protected function previewSource()
    {

        global $LANG;
        $out = '';
        $error = '';

        /** @var $importManager CatXmlImportManager */
        $importManager = GeneralUtility::makeInstance(CatXmlImportManager::class, '',
            $this->sysLanguage,
            $this->callParameters['string']);

        // Parse and check XML, load header data
        if ($importManager->parseAndCheckXMLString() === false) {
            $tmp = var_export($importManager->headerData, true);
            $tmp = str_replace("\n", '', $tmp);
            $error .= $tmp;
            $error .= $LANG->getLL('import.manager.error.parsing.xmlstring.message');
            $this->cli_echo($error);
        } else {
            $pageIds = $importManager->getPidsFromCATXMLNodes($importManager->xmlNodes);
            /** @var $mkPreviewLinks MkPreviewLinkService */
            $mkPreviewLinks = GeneralUtility::makeInstance(MkPreviewLinkService::class,
                $importManager->headerData['t3_workspaceId'], $importManager->headerData['t3_sysLang'], $pageIds);
            //Only valid if source language = default language (id=0)
            $previewLink = $mkPreviewLinks->mkSingleSrcPreviewLink($importManager->headerData['t3_baseURL'], 0);
            $out .= $previewLink;
        }

        // Output
        return ($out);
    }

    /**
     * Imports data from one or more XML files
     * Several files may be contained in a ZIP archive
     *
     * @return string Result output
     */
    protected function importXMLFile()
    {
        $out = '';
        $xmlFilesArr = array();
        try {
            $xmlFilesArr = $this->gatherAllFiles();
        } catch (Exception $e) {
            $out .= "\n\nAn error occurred trying to retrieve the files (" . $e->getMessage() . ')';
        }

        if (count($xmlFilesArr) > 0) {
            foreach ($xmlFilesArr as $xmlFile) {
                try {
                    $xmlFileHead = $this->getXMLFileHead($xmlFile);
                    // Set workspace to the required workspace ID from CATXML:
                    $GLOBALS['BE_USER']->setWorkspace($xmlFileHead['t3_workspaceId'][0]['XMLvalue']);
                    // Set import language to t3_sysLang from XML
                    $this->sysLanguage = $xmlFileHead['t3_sysLang'][0]['XMLvalue'];

                    /** @var $service L10nBaseService */
                    $service = GeneralUtility::makeInstance(L10nBaseService::class);
                    if ($this->callParameters['importAsDefaultLanguage']) {
                        $service->setImportAsDefaultLanguage(true);
                    }
                    /** @var $factory TranslationDataFactory */
                    $factory = GeneralUtility::makeInstance(TranslationDataFactory::class);

                    // Relevant processing of XML Import with the help of the Importmanager
                    /** @var $importManager CatXmlImportManager */
                    $importManager = GeneralUtility::makeInstance(CatXmlImportManager::class,
                        $xmlFile,
                        $this->sysLanguage, '');
                    if ($importManager->parseAndCheckXMLFile() === false) {
                        $out .= "\n\n" . $importManager->getErrorMessages();
                    } else {
                        // Find l10n configuration record
                        /** @var $l10ncfgObj L10nConfiguration */
                        $l10ncfgObj = GeneralUtility::makeInstance(L10nConfiguration::class);
                        $l10ncfgObj->load($importManager->headerData['t3_l10ncfg']);
                        $status = $l10ncfgObj->isLoaded();
                        if ($status === false) {
                            $this->cli_echo("l10ncfg not loaded! Exiting...\n");
                            exit;
                        }
                        // Delete previous translations
                        $importManager->delL10N($importManager->getDelL10NDataFromCATXMLNodes($importManager->xmlNodes));

                        // Make preview links
                        if ($this->callParameters['preview']) {
                            $pageIds = array();
                            if (empty($importManager->headerData['t3_previewId'])) {
                                $pageIds = $importManager->getPidsFromCATXMLNodes($importManager->xmlNodes);
                            } else {
                                $pageIds[0] = $importManager->headerData['t3_previewId'];
                            }
                            /** @var $mkPreviewLinks MkPreviewLinkService */
                            $mkPreviewLinks = GeneralUtility::makeInstance(MkPreviewLinkService::class,
                                $importManager->headerData['t3_workspaceId'], $importManager->headerData['t3_sysLang'],
                                $pageIds);
                            $previewLink = $mkPreviewLinks->mkSinglePreviewLink($importManager->headerData['t3_baseURL'],
                                $this->callParameters['server']);
                            $out .= $previewLink;
                        }

                        /** @var $translationData TranslationData */
                        $translationData = $factory->getTranslationDataFromCATXMLNodes($importManager->getXMLNodes());
                        $translationData->setLanguage($this->sysLanguage);
                        unset($importManager);
                        $service->saveTranslation($l10ncfgObj, $translationData);

                        // Store some information about the imported file
                        // This is used later for reporting by mail
                        $this->filesImported[$xmlFile] = array(
                            'workspace' => $xmlFileHead['t3_workspaceId'][0]['XMLvalue'],
                            'language' => $xmlFileHead['t3_targetLang'][0]['XMLvalue'],
                            'configuration' => $xmlFileHead['t3_l10ncfg'][0]['XMLvalue']
                        );
                    }
                } catch (Exception $e) {
                    if ($e->getCode() == 1390394945) {
                        $errorMessage = $e->getMessage();
                    } else {
                        $errorMessage = 'Badly formatted file (' . $e->getMessage() . ')';
                    }
                    $out .= "\n\n" . $xmlFile . ': ' . $errorMessage;
                    // Store the error message for later reporting by mail
                    $this->filesImported[$xmlFile] = array(
                        'error' => $errorMessage
                    );
                }
            }
        } else {
            $out .= "\n\nNo files to import! Either point to a file using the --file option or define a FTP server to get the files from";
        }
        // Clean up after import
        $this->importCleanUp();

        // Report non-fatal errors that happened
        if (count($this->errors) > 0) {
            $out .= "\n\n" . $GLOBALS['LANG']->getLL('import.nonfatal.errors') . "\n";
            foreach ($this->errors as $error) {
                $out .= "\t" . $error . "\n";
            }
        }

        // Means OK
        if (empty($out)) {
            $out = "\n\nImport was successful.\n";
        }

        // Output
        return $out;
    }

    /**
     * Gather all the files to be imported, depending on the call parameters
     *
     * @return array List of files to import
     */
    protected function gatherAllFiles()
    {
        $files = array();
        // If no file path was given, try to gather files from FTP
        if (empty($this->callParameters['file'])) {
            if (!empty($this->extensionConfiguration['ftp_server'])) {
                $files = $this->getFilesFromFtp();
            }
            // Get list of files to import from given command-line parameter
        } else {
            $fileInformation = pathinfo($this->callParameters['file']);
            // Unzip file if *.zip
            if ($fileInformation['extension'] == 'zip') {
                /** @var $unzip Zip */
                $unzip = GeneralUtility::makeInstance(Zip::class);
                $unzipResource = $unzip->extractFile($this->callParameters['file']);

                // Process extracted files if file type = xml => IMPORT
                $files = $this->checkFileType($unzipResource['fileArr'], 'xml');
                // Store the temporary directory's path for later clean up
                $this->directoryToCleanUp = $unzipResource['tempDir'];
            } elseif ($fileInformation['extension'] == 'xml') {
                $files[] = $this->callParameters['file'];
            }
        }

        return $files;
    }

    /**
     * Gets all available XML or ZIP files from the FTP server
     *
     * @throws Exception
     * @return array List of files, as local paths
     */
    protected function getFilesFromFtp()
    {
        $files = array();
        // First try connecting and logging in
        $connection = ftp_connect($this->extensionConfiguration['ftp_server']);
        if ($connection === false) {
            throw new Exception('Could not connect to FTP server', 1322489458);
        } else {
            if (@ftp_login($connection, $this->extensionConfiguration['ftp_server_username'],
                $this->extensionConfiguration['ftp_server_password'])
            ) {
                ftp_pasv($connection, true);
                // If a path was defined, change directory to this path
                if (!empty($this->extensionConfiguration['ftp_server_downpath'])) {
                    $result = ftp_chdir($connection, $this->extensionConfiguration['ftp_server_downpath']);
                    if ($result === false) {
                        throw new Exception('Could not change to directory: ' . $this->extensionConfiguration['ftp_server_downpath'],
                            1322489723);
                    }
                }
                // Get list of files to download from current directory
                $filesToDownload = ftp_nlist($connection, '');
                // If there are any files, loop on them
                if ($filesToDownload != false) {
                    // Check that download directory exists
                    $downloadFolder = 'uploads/tx_l10nmgr/jobs/in/';
                    $downloadPath = PATH_site . $downloadFolder;
                    if (!is_dir(GeneralUtility::getFileAbsFileName($downloadPath))) {
                        GeneralUtility::mkdir_deep(PATH_site, $downloadFolder);
                    }
                    foreach ($filesToDownload as $aFile) {
                        // Ignore current directory and reference to upper level
                        if ($aFile != '.' && $aFile != '..') {
                            $fileInformation = pathinfo($aFile);
                            // Download only XML or ZIP files
                            if ($fileInformation['extension'] == 'xml' || $fileInformation['extension'] == 'zip') {
                                $savePath = $downloadPath . $aFile;
                                // Get each file and save them to temporary directory
                                $result = ftp_get($connection, $savePath, $aFile, FTP_BINARY);
                                if ($result) {
                                    // If the file is XML, list it for usage as is
                                    if ($fileInformation['extension'] == 'xml') {
                                        $files[] = $savePath;
                                    } else {
                                        /** @var $unzip Zip */
                                        $unzip = GeneralUtility::makeInstance(Zip::class);
                                        $unzipResource = $unzip->extractFile($savePath);

                                        // Process extracted files if file type = xml => IMPORT
                                        $archiveFiles = $this->checkFileType($unzipResource['fileArr'], 'xml');
                                        $files = array_merge($files, $archiveFiles);
                                        // Store the temporary directory's path for later clean up
                                        $this->directoryToCleanUp = $unzipResource['tempDir'];
                                    }
                                    // Remove the file from the FTP server
                                    $result = ftp_delete($connection, $aFile);
                                    // If deleting failed, register error message
                                    // (don't throw exception as this does not need to interrupt the process)
                                    if (!$result) {
                                        $this->errors[] = 'Could not remove file ' . $aFile . 'from FTP server';
                                    }
                                    // If getting the file failed, register error message
                                    // (don't throw exception as this does not need to interrupt the process)
                                } else {
                                    $this->errors[] = 'Problem getting file ' . $aFile . 'from server or saving it locally';
                                }
                            }
                        }
                    }
                }
            } else {
                ftp_close($connection);
                throw new Exception('Could not log into to FTP server', 1322489527);
            }
        }

        return $files;
    }

    /**
     * Check file types from a list of files
     *
     * @param array $files Array of files to be checked
     * @param string $ext File extension to be tested for
     * @return array Files that passed test
     */
    protected function checkFileType($files, $ext)
    {
        $passed = array();
        foreach ($files as $file) {
            if (preg_match('/' . $ext . '$/', $file)) {
                $passed[] = $file;
            }
        }

        return $passed;
    }

	/**
	 * Extracts the header of a CATXML file
	 * @param string $filepath Path to the file
	 * @return bool
	 * @throws \TYPO3\CMS\Core\Exception
	 */
    protected function getXMLFileHead($filepath)
    {
        $getURLReport = array();
        $fileContent = GeneralUtility::getUrl($filepath, 0, false, $getURLReport);
        if ($getURLReport['error']) {
            throw new Exception(
                "File or URL cannot be read.\n  \\TYPO3\\CMS\\Core\\Utility\\GeneralUtility::getURL() error code: " . $getURLReport['error'] . "\n  \\TYPO3\\CMS\\Core\\Utility\\GeneralUtility::getURL() message: “" . $getURLReport['message'] . '”',
                1390394945
            );
        }

        // For some reason PHP chokes on incoming &nbsp; in XML!
        $xmlNodes = GeneralUtility::xml2tree(str_replace('&nbsp;', '&#160;', $fileContent), 3);

        if (!is_array($xmlNodes)) {
            throw new Exception($GLOBALS['LANG']->getLL('import.manager.error.parsing.xml2tree.message') . $xmlNodes,
                1322480030);
        }

        $headerInformationNodes = $xmlNodes['TYPO3L10N'][0]['ch']['head'][0]['ch'];
        if (!is_array($headerInformationNodes)) {
            throw new Exception($GLOBALS['LANG']->getLL('import.manager.error.missing.head.message'), 1322480056);
        }

        return $headerInformationNodes;
    }

    /**
     * Cleans up after the import process, as needed
     *
     * @return void
     */
    protected function importCleanUp()
    {
        // Clean up directory into which ZIP archives were uncompressed, if any
        if (!empty($this->directoryToCleanUp)) {
            /** @var $unzip Zip */
            $unzip = GeneralUtility::makeInstance(Zip::class);
            $unzip->removeDir($this->directoryToCleanUp);
        }
    }

    /**
     * Sends reporting mail about which files were imported
     *
     * @return void
     */
    protected function sendMailNotification()
    {
        // Send mail only if notifications are active and at least one file was imported
        if ($this->extensionConfiguration['enable_notification'] && count($this->filesImported) > 0) {
            // If at least a recipient is indeed defined, proceed with sending the mail
            $recipients = GeneralUtility::trimExplode(',',
                $this->extensionConfiguration['email_recipient_import']);
            if (count($recipients) > 0) {
                // First of all get a list of all workspaces and all l10nmgr configurations to use in the reporting
                $workspaces = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid,title', 'sys_workspace', '', '', '', '',
                    'uid');
                $l10nConfigurations = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid,title', 'tx_l10nmgr_cfg', '', '',
                    '', '', 'uid');

                // Start assembling the mail message
                $message = sprintf($GLOBALS['LANG']->getLL('import.mail.intro'),
                        date('d.m.Y H:i:s', $GLOBALS['EXEC_TIME']),
                        $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']) . "\n\n";
                foreach ($this->filesImported as $file => $fileInformation) {
                    if (isset($fileInformation['error'])) {
                        $status = $GLOBALS['LANG']->getLL('import.mail.error');
                        $message .= '[' . $status . '] ' . sprintf($GLOBALS['LANG']->getLL('import.mail.file'),
                                $file) . "\n";
                        $message .= "\t" . sprintf($GLOBALS['LANG']->getLL('import.mail.import.failed'),
                                $fileInformation['error']) . "\n";
                    } else {
                        $status = $GLOBALS['LANG']->getLL('import.mail.ok');
                        $message .= '[' . $status . '] ' . sprintf($GLOBALS['LANG']->getLL('import.mail.file'),
                                $file) . "\n";
                        // Get the workspace's name and add workspace information
                        if ($fileInformation['workspace'] == 0) {
                            $workspaceName = 'LIVE';
                        } else {
                            if (isset($workspaces[$fileInformation['workspace']])) {
                                $workspaceName = $workspaces[$fileInformation['workspace']]['title'];
                            } else {
                                $workspaceName = $GLOBALS['LANG']->getLL('import.mail.workspace.unknown');
                            }
                        }
                        $message .= "\t" . sprintf($GLOBALS['LANG']->getLL('import.mail.workspace'), $workspaceName,
                                $fileInformation['workspace']) . "\n";
                        // Add language information
                        $message .= "\t" . sprintf($GLOBALS['LANG']->getLL('import.mail.language'),
                                $fileInformation['language']) . "\n";
                        // Get configuration's name and add configuration information
                        if (isset($l10nConfigurations[$fileInformation['configuration']])) {
                            $configurationName = $l10nConfigurations[$fileInformation['configuration']]['title'];
                        } else {
                            $configurationName = $GLOBALS['LANG']->getLL('import.mail.l10nconfig.unknown');
                        }
                        $message .= "\t" . sprintf($GLOBALS['LANG']->getLL('import.mail.l10nconfig'),
                                $configurationName, $fileInformation['configuration']) . "\n";
                    }
                }

                // Report non-fatal errors that happened
                if (count($this->errors) > 0) {
                    $message .= "\n\n----------------------------------------\n";
                    $message .= $GLOBALS['LANG']->getLL('import.nonfatal.errors') . "\n";
                    foreach ($this->errors as $error) {
                        $message .= "\t" . $error . "\n";
                    }
                    $message .= "----------------------------------------\n";
                }

                // Add signature
                $message .= "\n\n" . $GLOBALS['LANG']->getLL('email.goodbye.msg');
                $message .= "\n" . $this->extensionConfiguration['email_sender_name'];
                $subject = sprintf($GLOBALS['LANG']->getLL('import.mail.subject'),
                    $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']);

                // Instantiate the mail object, set all necessary properties and send the mail
                /** @var $mailObject MailMessage */
                $mailObject = GeneralUtility::makeInstance('\TYPO3\CMS\Core\Mail\MailMessage');
                $mailObject->setFrom(array($this->extensionConfiguration['email_sender'] => $this->extensionConfiguration['email_sender_name']));
                $mailObject->setTo($recipients);
                $mailObject->setSubject($subject);
                $mailObject->setFormat('text/plain');
                $mailObject->setBody($message);
                $mailObject->send();
            }
        }
    }
}

// Call the functionality
/** @var $importObject Import */
$importObject = GeneralUtility::makeInstance(Import::class);
$importObject->cli_main($_SERVER['argv']);