<?php
namespace Localizationteam\L10nmgr\Utility;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Localizationteam\L10nmgr\Constants;
use Localizationteam\L10nmgr\LanguageRestriction\LanguageRestrictionRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * L10nmgr Extension Management functions
 */
class L10nmgrExtensionManagementUtility
{

    /**
     * Makes translations of a table restrictable by adding value of restricted languages into the registry.
     * FOR USE IN ext_localconf.php FILES or files in Configuration/TCA/Overrides/*.php Use the latter to benefit from TCA caching!
     *
     * @param string $extensionKey Extension key to be used
     * @param string $tableName Name of the table to be categorized
     * @param string $fieldName Name of the field to be used to store restricted languages
     * @param array $options Additional configuration options
     * @param bool $override If FALSE, any translation restriction configuration for the same table / field is kept as is even though the new configuration is added
     * @see addTCAcolumns
     * @see addToAllTCAtypes
     */
    public static function makeTranslationsRestrictable($extensionKey, $tableName, $fieldName = Constants::L10NMGR_LANGUAGE_RESTRICTION_FIELDNAME, array $options = [], $override = true)
    {
        // Update the category registry
        $result = LanguageRestrictionRegistry::getInstance()->add($extensionKey, $tableName, $fieldName, $options, $override);
        if ($result === false) {
            $message = LanguageRestrictionRegistry::class . ': no category registered for table "%s". Key was already registered.';
            /** @var $logger \TYPO3\CMS\Core\Log\Logger */
            $logger = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Log\LogManager::class)->getLogger(__CLASS__);
            $logger->warning(
                sprintf($message, $tableName)
            );
        }
    }
}
