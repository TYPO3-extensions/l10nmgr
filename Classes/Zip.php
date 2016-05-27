<?php
namespace Localizationteam\L10nmgr;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Zip file creation class.
 * Makes zip files.
 *
 * Based on :
 *
 *  http://www.zend.com/codex.php?id=535&single=1
 *  By Eric Mueller <eric@themepark.com>
 *
 *  http://www.zend.com/codex.php?id=470&single=1
 *  by Denis125 <webmaster@atlant.ru>
 *
 *  a patch from Peter Listiak <mlady@users.sourceforge.net> for last modified
 *  date and time of the compressed file
 *
 * Official ZIP file format: http://www.pkware.com/appnote.txt
 *
 * @access  public
 */
class Zip
{

    /**
     * Array to store compressed data
     *
     * @var  array $datasec
     */
    var $datasec = array();

    /**
     * Central directory
     *
     * @var  array $ctrl_dir
     */
    var $ctrl_dir = array();

    /**
     * End of central directory record
     *
     * @var  string $eof_ctrl_dir
     */
    var $eof_ctrl_dir = "\x50\x4b\x05\x06\x00\x00\x00\x00";

    /**
     * Last offset position
     *
     * @var  integer $old_offset
     */
    var $old_offset = 0;

    /**
     * Unzip Application
     *
     * @var  string $unzipAppCmd
     */

    var $unzipAppCmd = '/usr/bin/unzip -qq ###ARCHIVENAME### -d ###DIRECTORY###'; // Unzip Application (don't set to blank!) ** MODIFIED RL, 15.08.03
    //TODO: Take global var for unzip program...

    // Example for WinRAR:
    // var $unzipAppCmd ='c:\Programme\WinRAR\winrar.exe x -afzip -ibck -inul -o+ ###ARCHIVENAME### ###DIRECTORY###';

    /**
     * Adds "file" to archive
     *
     * @param  string   file contents
     * @param  string   name of the file in the archive (may contains the path)
     * @param  integer  the current timestamp
     *
     * @access public
     */
    function addFile($data, $name, $time = 0)
    {
        $name = str_replace('\\', '/', $name);

        $dtime = dechex($this->unix2DosTime($time));
        $hexdtime = '\x' . $dtime[6] . $dtime[7]
            . '\x' . $dtime[4] . $dtime[5]
            . '\x' . $dtime[2] . $dtime[3]
            . '\x' . $dtime[0] . $dtime[1];
        eval('$hexdtime = "' . $hexdtime . '";');

        $fr = "\x50\x4b\x03\x04";
        $fr .= "\x14\x00"; // ver needed to extract
        $fr .= "\x00\x00"; // gen purpose bit flag
        $fr .= "\x08\x00"; // compression method
        $fr .= $hexdtime; // last mod time and date

        // "local file header" segment
        $unc_len = strlen($data);
        $crc = crc32($data);
        $zdata = gzcompress($data);
        $zdata = substr(substr($zdata, 0, strlen($zdata) - 4), 2); // fix crc bug
        $c_len = strlen($zdata);
        $fr .= pack('V', $crc); // crc32
        $fr .= pack('V', $c_len); // compressed filesize
        $fr .= pack('V', $unc_len); // uncompressed filesize
        $fr .= pack('v', strlen($name)); // length of filename
        $fr .= pack('v', 0); // extra field length
        $fr .= $name;

        // "file data" segment
        $fr .= $zdata;

        // "data descriptor" segment (optional but necessary if archive is not
        // served as file)
        $fr .= pack('V', $crc); // crc32
        $fr .= pack('V', $c_len); // compressed filesize
        $fr .= pack('V', $unc_len); // uncompressed filesize

        // add this entry to array
        $this->datasec[] = $fr;

        // now add to central directory record
        $cdrec = "\x50\x4b\x01\x02";
        $cdrec .= "\x00\x00"; // version made by
        $cdrec .= "\x14\x00"; // version needed to extract
        $cdrec .= "\x00\x00"; // gen purpose bit flag
        $cdrec .= "\x08\x00"; // compression method
        $cdrec .= $hexdtime; // last mod time & date
        $cdrec .= pack('V', $crc); // crc32
        $cdrec .= pack('V', $c_len); // compressed filesize
        $cdrec .= pack('V', $unc_len); // uncompressed filesize
        $cdrec .= pack('v', strlen($name)); // length of filename
        $cdrec .= pack('v', 0); // extra field length
        $cdrec .= pack('v', 0); // file comment length
        $cdrec .= pack('v', 0); // disk number start
        $cdrec .= pack('v', 0); // internal file attributes
        $cdrec .= pack('V', 32); // external file attributes - 'archive' bit set

        $cdrec .= pack('V', $this->old_offset); // relative offset of local header
        $this->old_offset += strlen($fr);

        $cdrec .= $name;

        // optional extra field, file comment goes here
        // save to central directory
        $this->ctrl_dir[] = $cdrec;
    } // end of the 'unix2DosTime()' method

    /**
     * Converts an Unix timestamp to a four byte DOS date and time format (date
     * in high two bytes, time in low two bytes allowing magnitude comparison).
     *
     * @param  integer  the current Unix timestamp
     *
     * @return integer  the current date in a four byte DOS format
     *
     * @access private
     */
    function unix2DosTime($unixtime = 0)
    {
        $timearray = ($unixtime == 0) ? getdate() : getdate($unixtime);

        if ($timearray['year'] < 1980) {
            $timearray['year'] = 1980;
            $timearray['mon'] = 1;
            $timearray['mday'] = 1;
            $timearray['hours'] = 0;
            $timearray['minutes'] = 0;
            $timearray['seconds'] = 0;
        } // end if

        return (($timearray['year'] - 1980) << 25) | ($timearray['mon'] << 21) | ($timearray['mday'] << 16) |
        ($timearray['hours'] << 11) | ($timearray['minutes'] << 5) | ($timearray['seconds'] >> 1);
    } // end of the 'addFile()' method

    /**
     * Dumps out file
     *
     * @return  string  the zipped file
     *
     * @access public
     */
    function file()
    {
        $data = implode('', $this->datasec);
        $ctrldir = implode('', $this->ctrl_dir);

        return
            $data .
            $ctrldir .
            $this->eof_ctrl_dir .
            pack('v', sizeof($this->ctrl_dir)) . // total # of entries "on this disk"
            pack('v', sizeof($this->ctrl_dir)) . // total # of entries overall
            pack('V', strlen($ctrldir)) . // size of central dir
            pack('V', strlen($data)) . // offset to start of central dir
            "\x00\x00"; // .zip file comment length
    } // end of the 'file()' method

    /**
     * This takes the ZIP file, unzips it, reads all documents, store them in database for next retrieval.
     * The file is libunzipped in PATH_site.'typo3temp/' + a randomly named folder.
     *
     * @param  $file
     * @return mixed
     * @access private
     */
    function extractFile($file)
    {
        if (is_file($file)) {
            $tempDir = PATH_site . 'typo3temp/' . md5(microtime()) . '/';
            GeneralUtility::mkdir($tempDir);
            if (is_dir($tempDir)) {
                // This is if I want to check the content:
                #       $cmd = $this->unzipAppPath.' -t '.$this->file;
                #       exec($cmd,$dat);
                #       debug($dat);

                // Unzip the files inside: **MODIFIED RL, 15.08.03
                $cmd = $this->unzipAppCmd;
                $cmd = str_replace('###ARCHIVENAME###', $file, $cmd);
                $cmd = str_replace('###DIRECTORY###', $tempDir, $cmd);
                exec($cmd);

                $out['fileArr'] = $this->getAllFilesAndFoldersInPath(array(), $tempDir);
                $out['tempDir'] = $tempDir;

                return $out;
            } else {
                return 'No dir: ' . $tempDir;
            }
        } else {
            return 'No file: ' . $file;
        }
    }

    /**
     * Returns an array with all files and folders in $extPath
     *
     * @param       array $fileArr
     * @param       string $extPath
     * @return      array          Array with files and folders
     * @access      private
     */
    function getAllFilesAndFoldersInPath($fileArr, $extPath)
    {
        $extList = '';
        $fileArr[] = $extPath;
        $fileArr = array_merge($fileArr,
            GeneralUtility::getFilesInDir($extPath, $extList, 1, 1));

        $dirs = GeneralUtility::get_dirs($extPath);
        if (is_array($dirs)) {
            reset($dirs);
            while (list(, $subdirs) = each($dirs)) {
                if ($subdirs) {
                    $fileArr = $this->getAllFilesAndFoldersInPath($fileArr, $extPath . $subdirs . '/');
                }
            }
        }

        return $fileArr;
    }

    /**
     * Removes directory with all files from the path $tempDir.
     * $tempDir must be a subfolder to typo3temp/
     *
     * @param  string $tempDir
     * @return void
     * @access private
     */
    function removeDir($tempDir)
    {
        $testDir = PATH_site . 'typo3temp/';
        if (!GeneralUtility::isFirstPartOfStr($tempDir, $testDir)) {
            die($tempDir . ' was not within ' . $testDir);
        }

        // Go through dirs:
        $dirs = GeneralUtility::get_dirs($tempDir);
        if (is_array($dirs)) {
            reset($dirs);
            while (list(, $subdirs) = each($dirs)) {
                if ($subdirs) {
                    $this->removeDir($tempDir . $subdirs . '/');
                }
            }
        }
        // Then files in this dir:
        $fileArr = GeneralUtility::getFilesInDir($tempDir, '', 1);
        if (is_array($fileArr)) {
            reset($fileArr);
            while (list(, $file) = each($fileArr)) {
                if (!GeneralUtility::isFirstPartOfStr($file, $testDir)) {
                    die($file . ' was not within ' . $testDir);
                } // PARAnoid...
                unlink($file);
            }
        }
        // Remove this dir:
        rmdir($tempDir);
    }
}