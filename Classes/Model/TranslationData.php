<?php
namespace Localizationteam\L10nmgr\Model;

/***************************************************************
 * Copyright notice
 * (c) 2006 Kasper Skårhøj <kasperYYYY@typo3.com>
 * All rights reserved
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
/**
 * translationData: encapsulates the data which are needed for saving a new translation.
 *
 * @authorDaniel Poetzinger <development@aoemedia.de>
 * @packageTYPO3
 * @subpackage tx_l10nmgr
 */
class TranslationData
{
    /**
     * @var array
     */
    protected $data = array();
    /**
     * @var int
     */
    protected $sysLang;
    /**
     * @var int
     */
    protected $previewLanguage;

    /**
     * @param array $data
     */
    public function setTranslationData($data)
    {
        $this->data = $data;
    }

    /**
     * @param int $sysLang
     */
    public function setLanguage($sysLang)
    {
        $this->sysLang = $sysLang;
    }

    /**
     * @return array
     */
    public function &getTranslationData()
    {
        return $this->data;
    }

    /**
     * @return int
     */
    public function getLanguage()
    {
        return $this->sysLang;
    }

    /**
     * @return int
     */
    public function getPreviewLanguage()
    {
        return $this->previewLanguage;
    }

    /**
     * @param int $previewLanguage
     */
    public function setPreviewLanguage($previewLanguage)
    {
        $this->previewLanguage = $previewLanguage;
    }
}