<?php

namespace ChurchCRM\dto;

/**
 * Domain vocabulary for this deployment (ChurchCRM "Family" → house assembly).
 */
class ChurchVocabulary
{
    public static function houseAssembly(): string
    {
        return gettext('House Assembly');
    }

    public static function houseAssemblies(): string
    {
        return gettext('House Assemblies');
    }

    public static function houseAssemblyDashboard(): string
    {
        return gettext('My House Assembly');
    }

    public static function houseAssemblyLeader(): string
    {
        return gettext('House Assembly Leader');
    }

    public static function headOfHousehold(): string
    {
        return gettext('Head of Household');
    }

    public static function houseAssemblyMembers(): string
    {
        return gettext('House Assembly Members');
    }

    public static function scopedHouseAssemblyGroup(): string
    {
        return gettext('Scoped House Assembly (Group)');
    }

    public static function scopedHouseAssemblyFamily(): string
    {
        return gettext('Scoped House Assembly (Family)');
    }

    public static function meetings(): string
    {
        return gettext('Meetings');
    }

    public static function meeting(): string
    {
        return gettext('Meeting');
    }

    /** @deprecated Use houseAssembly() — alias for legacy gettext('Family') call sites */
    public static function family(): string
    {
        return self::houseAssembly();
    }

    /** @deprecated Use houseAssemblies() */
    public static function families(): string
    {
        return self::houseAssemblies();
    }

    public static function familyListing(): string
    {
        return gettext('Family Listing');
    }

    public static function familyMap(): string
    {
        return gettext('Family Map');
    }

    public static function familyRole(): string
    {
        return gettext('Family Role');
    }

    public static function familyRoles(): string
    {
        return gettext('Family Roles');
    }

    public static function familyProperties(): string
    {
        return gettext('Family Properties');
    }

    public static function familyCustomFields(): string
    {
        return gettext('Family Custom Fields');
    }

    public static function browseAllHouseAssemblies(): string
    {
        return gettext('Browse and search all families in your congregation');
    }
}
