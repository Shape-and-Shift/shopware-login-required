<?php declare(strict_types=1);

namespace SasLoginRequired\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1620465124CreateGateCustomerGroupTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1620465124;
    }

    public function update(Connection $connection): void
    {
        $connection->executeUpdate('
            CREATE TABLE IF NOT EXISTS `sas_gate_customer_group` (
                `gate_id` BINARY(16) NOT NULL,
                `customer_group_id` BINARY(16) NOT NULL,
                `created_at` DATETIME(3) NOT NULL,
                PRIMARY KEY (`gate_id`, `customer_group_id`),
                CONSTRAINT `fk.sas_gate_customer_group.gate_id` FOREIGN KEY (`gate_id`)
                    REFERENCES `sas_gate` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.sas_gate_customer_group.customer_group_id` FOREIGN KEY (`customer_group_id`)
                    REFERENCES `customer_group` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
