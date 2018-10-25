<?php

namespace Localizationteam\L10nmgr;

/**
 * Constants for the L10nmgr
 */

class Constants
{
    const L10NMGR_CONFIGURATION_DEFAULT = 0;
    const L10NMGR_CONFIGURATION_NONE = 1;
    const L10NMGR_CONFIGURATION_EXCLUDE = 2;
    const L10NMGR_CONFIGURATION_INCLUDE = 3;

    const L10NMGR_LANGUAGE_RESTRICTION_FOREIGN_TABLENAME = 'sys_language';
    const L10NMGR_LANGUAGE_RESTRICTION_MM_TABLENAME = 'sys_language_l10nmgr_language_restricted_record_mm';
    const L10NMGR_LANGUAGE_RESTRICTION_FIELDNAME = 'l10nmgr_language_restriction';

}
