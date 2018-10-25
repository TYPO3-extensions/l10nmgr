<?php
/**
 * Created by PhpStorm.
 * User: info
 * Date: 25.10.2018
 * Time: 11:34
 */

namespace Localizationteam\L10nmgr\LanguageRestriction;

use Localizationteam\L10nmgr\Constants;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;

class LanguageRestrictionRegistry implements SingletonInterface
{
    /**
     * @var array
     */
    protected $registry = [];

    /**
     * @var array
     */
    protected $extensions = [];

    /**
     * @var string
     */
    protected $template = '';

    /**
     * Creates this object.
     */
    public function __construct()
    {
        $this->template = str_repeat(PHP_EOL, 3) . 'CREATE TABLE %s (' . PHP_EOL
            . '  %s int(11) DEFAULT \'0\' NOT NULL' . PHP_EOL . ');' . str_repeat(PHP_EOL, 3);
    }

    /**
     * Returns a class instance
     *
     * @return object|LanguageRestrictionRegistry
     */
    public static function getInstance()
    {
        return GeneralUtility::makeInstance(__CLASS__);
    }

    /**
     * Gets all language restrictable tables
     *
     * @return array
     */
    public function getLanguageRestrictableTables()
    {
        return array_keys($this->registry);
    }

    /**
     * Apply TCA to all registered tables
     *
     * @internal
     */
    public function applyTcaForPreRegisteredTables()
    {
        $this->registerDefaultTranslationRestrictableTables();
        foreach ($this->registry as $tableName => $fields) {
            foreach ($fields as $fieldName => $_) {
                $this->applyTcaForTableAndField($tableName, $fieldName);
            }
        }
    }

    /**
     * Add default translation restrictable tables to the registry
     */
    protected function registerDefaultTranslationRestrictableTables()
    {
        $defaultTranslationRestrictableTables = GeneralUtility::trimExplode(
            ',',
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['defaultTranslationRestrictableTables'],
            true
        );
        foreach ($defaultTranslationRestrictableTables as $defaultTranslationRestrictedTable) {
            if (!$this->isRegistered($defaultTranslationRestrictedTable)) {
                $this->add('core', $defaultTranslationRestrictedTable,
                    Constants::L10NMGR_LANGUAGE_RESTRICTION_FIELDNAME);
            }
        }
    }

    /**
     * Adds a new language restriction configuration to this registry.
     * TCA changes are directly applied
     *
     * @param string $extensionKey Extension key to be used
     * @param string $tableName Name of the table to be registered
     * @param string $fieldName Name of the field to be registered
     * @param array $options Additional configuration options
     *              + fieldList: field configuration to be added to showitems
     *              + typesList: list of types that shall visualize the language restriction field
     *              + position: insert position of the language restriction field
     *              + label: backend label of the language restriction field
     *              + fieldConfiguration: TCA field config array to override defaults
     * @param bool $override If FALSE, any language restriction configuration for the same table / field is kept as is even though the new configuration is added
     * @return bool
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function add(
        $extensionKey,
        $tableName,
        $fieldName = Constants::L10NMGR_LANGUAGE_RESTRICTION_FIELDNAME,
        array $options = [],
        $override = true
    ) {
        $didRegister = false;
        if (empty($tableName) || !is_string($tableName)) {
            throw new \InvalidArgumentException('No or invalid table name "' . $tableName . '" given.', 1540460445);
        }
        if (empty($extensionKey) || !is_string($extensionKey)) {
            throw new \InvalidArgumentException('No or invalid extension key "' . $extensionKey . '" given.',
                1540460446);
        }

        if ($override) {
            $this->remove($tableName, $fieldName);
        }

        if (!$this->isRegistered($tableName, $fieldName)) {
            $this->registry[$tableName][$fieldName] = $options;
            $this->extensions[$extensionKey][$tableName][$fieldName] = $fieldName;

            if (isset($GLOBALS['TCA'][$tableName]['columns'])) {
                $this->applyTcaForTableAndField($tableName, $fieldName);
                $didRegister = true;
            }
        }

        return $didRegister;
    }

    /**
     * Removes the given field in the given table from the registry if it is found.
     *
     * @param string $tableName The name of the table for which the registration should be removed.
     * @param string $fieldName The name of the field for which the registration should be removed.
     */
    protected function remove($tableName, $fieldName)
    {
        if (!$this->isRegistered($tableName, $fieldName)) {
            return;
        }

        unset($this->registry[$tableName][$fieldName]);

        foreach ($this->extensions as $extensionKey => $tableFieldConfig) {
            foreach ($tableFieldConfig as $extTableName => $fieldNameArray) {
                if ($extTableName === $tableName && isset($fieldNameArray[$fieldName])) {
                    unset($this->extensions[$extensionKey][$tableName][$fieldName]);
                    break;
                }
            }
        }

    }

    /**
     * Tells whether a table has a language restriction configuration in the registry.
     *
     * @param string $tableName Name of the table to be looked up
     * @param string $fieldName Name of the field to be looked up
     * @return bool
     */
    public function isRegistered($tableName, $fieldName = Constants::L10NMGR_LANGUAGE_RESTRICTION_FIELDNAME)
    {
        return isset($this->registry[$tableName][$fieldName]);
    }

    /**
     * Applies the additions directly to the TCA
     *
     * @param string $tableName
     * @param string $fieldName
     */
    protected function applyTcaForTableAndField($tableName, $fieldName)
    {
        $this->addTcaColumn($tableName, $fieldName, $this->registry[$tableName][$fieldName]);
        $this->addToAllTCAtypes($tableName, $this->registry[$tableName][$fieldName]);
    }

    /**
     * Add a new TCA Column
     *
     * @param string $tableName Name of the table to be language restrictable
     * @param string $fieldName Name of the field to be used to store language restrictions
     * @param array $options Additional configuration options
     *              + fieldConfiguration: TCA field config array to override defaults
     *              + label: backend label of the language restriction field
     *              + interface: boolean if the language restriction should be included in the "interface" section of the TCA table
     *              + l10n_mode
     *              + l10n_display
     */
    protected function addTcaColumn($tableName, $fieldName, array $options)
    {
        // Makes sure to add more TCA to an existing structure
        if (isset($GLOBALS['TCA'][$tableName]['columns'])) {
            // Take specific label into account
            $label = 'LLL:EXT:l10nmgr/Resources/Private/Language/locallang_db.xlf:sys_language.restrictions';
            if (!empty($options['label'])) {
                $label = $options['label'];
            }

            // Take specific value of exclude flag into account
            $exclude = true;
            if (isset($options['exclude'])) {
                $exclude = (bool)$options['exclude'];
            }

            $fieldConfiguration = empty($options['fieldConfiguration']) ? [] : $options['fieldConfiguration'];

            $columns = [
                $fieldName => [
                    'exclude' => $exclude,
                    'label'   => $label,
                    'config'  => static::getTcaFieldConfiguration($tableName, $fieldName, $fieldConfiguration),
                ],
            ];

            if (isset($options['l10n_mode'])) {
                $columns[$fieldName]['l10n_mode'] = $options['l10n_mode'];
            }
            if (isset($options['l10n_display'])) {
                $columns[$fieldName]['l10n_display'] = $options['l10n_display'];
            }
            if (isset($options['displayCond'])) {
                $columns[$fieldName]['displayCond'] = $options['displayCond'];
            }

            // Add field to interface list per default (unless the 'interface' property is FALSE)
            if (
                (!isset($options['interface']) || $options['interface'])
                && !empty($GLOBALS['TCA'][$tableName]['interface']['showRecordFieldList'])
                && !GeneralUtility::inList($GLOBALS['TCA'][$tableName]['interface']['showRecordFieldList'], $fieldName)
            ) {
                $GLOBALS['TCA'][$tableName]['interface']['showRecordFieldList'] .= ',' . $fieldName;
            }

            // Adding fields to an existing table definition
            ExtensionManagementUtility::addTCAcolumns($tableName, $columns);
        }
    }

    /**
     * Get the config array for given table and field.
     * This method does NOT take care of adding sql fields, adding the field to TCA types
     * nor does it set the MM_oppositeUsage in the sys_language TCA. This has to be taken care of manually!
     *
     * @param string $tableName The table name
     * @param string $fieldName The field name (default l10nmgr_language_restriction)
     * @param array $fieldConfigurationOverride Changes to the default configuration
     * @return array
     * @api
     */
    public static function getTcaFieldConfiguration(
        $tableName,
        $fieldName = Constants::L10NMGR_LANGUAGE_RESTRICTION_FIELDNAME,
        array $fieldConfigurationOverride = []
    ) {
        // Forges a new field, default name is "l10nmgr_language_restriction"
        $fieldConfiguration = [
            'type'                => 'select',
            'renderType'          => 'selectMultipleSideBySide',
            'foreign_table'       => Constants::L10NMGR_LANGUAGE_RESTRICTION_FOREIGN_TABLENAME,
            'foreign_table_where' => ' ORDER BY sys_language.sorting ASC',
            'MM'                  => Constants::L10NMGR_LANGUAGE_RESTRICTION_MM_TABLENAME,
            'MM_opposite_field'   => 'items',
            'MM_match_fields'     => [
                'tablenames' => $tableName,
                'fieldname'  => $fieldName,
            ],
            'size'                => 10,
            'maxitems'            => 9999
        ];

        // Merge changes to TCA configuration
        if (!empty($fieldConfigurationOverride)) {
            ArrayUtility::mergeRecursiveWithOverrule(
                $fieldConfiguration,
                $fieldConfigurationOverride
            );
        }

        return $fieldConfiguration;
    }

    /**
     * Add a new field into the TCA types -> showitem
     *
     * @param string $tableName Name of the table to be language restrictable
     * @param array $options Additional configuration options
     *              + fieldList: field configuration to be added to showitems
     *              + typesList: list of types that shall visualize the language restriction field
     *              + position: insert position of the language restriction field
     */
    protected function addToAllTCAtypes($tableName, array $options)
    {

        // Makes sure to add more TCA to an existing structure
        if (isset($GLOBALS['TCA'][$tableName]['columns'])) {
            $fieldList = $options['fieldList'];

            if (empty($fieldList)) {
                $fieldList = Constants::L10NMGR_LANGUAGE_RESTRICTION_FIELDNAME;
            }

            $typesList = '';
            if (isset($options['typesList']) && $options['typesList'] !== '') {
                $typesList = $options['typesList'];
            }

            $position = $tableName === 'pages' ? 'after:l18n_cfg' : 'after:sys_language_uid';
            if (!empty($options['position'])) {
                $position = $options['position'];
            }
            DebugUtility::debug($fieldList);
            // Makes the new "l10nmgr_language_restriction" field to be visible in TSFE.
            ExtensionManagementUtility::addToAllTCAtypes($tableName, $fieldList, $typesList, $position);
        }
    }

    /**
     * A slot method to inject the required language restriction database fields to the
     * tables definition string
     *
     * @param array $sqlString
     * @return array
     */
    public function addLanguageRestrictionDatabaseSchemaToTablesDefinition(array $sqlString)
    {
        $this->registerDefaultTranslationRestrictableTables();
        $sqlString[] = $this->getDatabaseTableDefinitions();
        return ['sqlString' => $sqlString];
    }

    /**
     * Generates tables definitions for all registered tables.
     *
     * @return string
     */
    public function getDatabaseTableDefinitions()
    {
        $sql = '';
        foreach ($this->getExtensionKeys() as $extensionKey) {
            $sql .= $this->getDatabaseTableDefinition($extensionKey);
        }
        return $sql;
    }

    /**
     * Gets all extension keys that registered a language restriction configuration.
     *
     * @return array
     */
    public function getExtensionKeys()
    {
        return array_keys($this->extensions);
    }

    /**
     * Generates table definitions for registered tables by an extension.
     *
     * @param string $extensionKey Extension key to have the database definitions created for
     * @return string
     */
    public function getDatabaseTableDefinition($extensionKey)
    {
        if (!isset($this->extensions[$extensionKey]) || !is_array($this->extensions[$extensionKey])) {
            return '';
        }
        $sql = '';

        foreach ($this->extensions[$extensionKey] as $tableName => $fields) {
            foreach ($fields as $fieldName) {
                $sql .= sprintf($this->template, $tableName, $fieldName);
            }
        }
        return $sql;
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }

}