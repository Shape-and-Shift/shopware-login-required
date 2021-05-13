<?php declare(strict_types=1);

namespace SasLoginRequired\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1620228331CreateGateTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1620228331;
    }

    public function update(Connection $connection): void
    {
        $connection->executeUpdate('
            CREATE TABLE IF NOT EXISTS `sas_gate` (
              `id` BINARY(16) NOT NULL,
              `category_id` BINARY(16) NOT NULL,
              `is_enabled` TINYINT(1) NOT NULL DEFAULT 0,
              `created_at` DATETIME(3) NOT NULL,
              `updated_at` DATETIME(3) DEFAULT NULL,
              PRIMARY KEY (`id`, `category_id`),
              CONSTRAINT `fk.sas_category_gate.category_id` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
