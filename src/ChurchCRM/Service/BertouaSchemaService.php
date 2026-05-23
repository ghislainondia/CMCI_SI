<?php

namespace ChurchCRM\Service;

use ChurchCRM\dto\SystemURLs;
use ChurchCRM\Utils\LoggerUtils;
use ChurchCRM\Utils\SQLUtils;
use Propel\Runtime\Propel;

/**
 * Ensures Bertoua Message tables exist (handles deployments where DB migration was not run).
 */
class BertouaSchemaService
{
    private static bool $checked = false;

    public static function ensureSchema(): void
    {
        if (self::$checked) {
            return;
        }

        self::$checked = true;

        $connection = Propel::getConnection();
        $stmt = $connection->query("SHOW TABLES LIKE 'bertoua_btm_module'");
        if ($stmt !== false && $stmt->fetch(\PDO::FETCH_NUM)) {
            return;
        }

        $migrationFile = SystemURLs::getDocumentRoot() . '/mysql/upgrade/7.3.5.sql';
        if (!is_readable($migrationFile)) {
            LoggerUtils::getAppLogger()->error('Bertoua migration file missing', ['path' => $migrationFile]);
            throw new \RuntimeException(gettext('Bertoua database tables are missing. Please run the system database upgrade.'));
        }

        LoggerUtils::getAppLogger()->info('Installing Bertoua Message database schema from 7.3.5.sql');
        SQLUtils::sqlImport($migrationFile, $connection);
    }
}
