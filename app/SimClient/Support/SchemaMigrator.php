<?php
declare(strict_types=1);

namespace App\SimClient\Support;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

class SchemaMigrator
{
    /**
     * 已处理的表，防止重复执行
     *
     * @var array<string, bool>
     */
    private static array $ensured = [];

    /**
     * 确保接码兑换码表结构符合当前代码需求
     */
    public static function ensureRechargeCodeTable(string $table): void
    {
        if (isset(self::$ensured[$table])) {
            return;
        }

        $schema = Capsule::schema();
        $connection = Capsule::connection();
        $prefixedTable = $connection->getTablePrefix() . $table;
        $database = $connection->getDatabaseName();

        if (!$schema->hasTable($table)) {
            $schema->create($table, function (Blueprint $blueprint) {
                $blueprint->increments('id');
                $blueprint->string('code', 32)->comment('兑换码');
                $blueprint->decimal('points', 10, 2)->unsigned()->comment('可兑换点数');
                $blueprint->boolean('is_used')->default(false)->comment('是否已使用');
                $blueprint->unsignedInteger('used_user_id')->nullable()->comment('使用用户ID');
                $blueprint->timestamp('used_at')->nullable()->comment('使用时间');
                $blueprint->timestamp('expires_at')->nullable()->comment('过期时间');
                $blueprint->timestamp('created_at')->useCurrent()->comment('创建时间');
                $blueprint->timestamp('updated_at')->nullable()->useCurrentOnUpdate()->comment('更新时间');
                $blueprint->unique('code', 'uk_code');
                $blueprint->index('is_used', 'idx_is_used');
                $blueprint->index('expires_at', 'idx_expires');
            });

            self::$ensured[$table] = true;
            return;
        }

        $columnNames = self::fetchColumns($connection, $prefixedTable);
        $alterParts = [];

        if (!in_array('is_used', $columnNames, true)) {
            $alterParts[] = "ADD COLUMN `is_used` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否已使用' AFTER `points`";
        }
        if (!in_array('used_user_id', $columnNames, true)) {
            $alterParts[] = "ADD COLUMN `used_user_id` INT UNSIGNED NULL DEFAULT NULL COMMENT '使用用户ID' AFTER `is_used`";
        }
        if (!in_array('used_at', $columnNames, true)) {
            $alterParts[] = "ADD COLUMN `used_at` TIMESTAMP NULL DEFAULT NULL COMMENT '使用时间' AFTER `used_user_id`";
        }
        if (!in_array('expires_at', $columnNames, true)) {
            $alterParts[] = "ADD COLUMN `expires_at` TIMESTAMP NULL DEFAULT NULL COMMENT '过期时间' AFTER `used_at`";
        }
        if (!in_array('created_at', $columnNames, true)) {
            $alterParts[] = "ADD COLUMN `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间' AFTER `expires_at`";
        }
        if (!in_array('updated_at', $columnNames, true)) {
            $alterParts[] = "ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间' AFTER `created_at`";
        }

        if (!empty($alterParts)) {
            $connection->statement("ALTER TABLE `{$prefixedTable}` " . implode(', ', $alterParts));
        }

        $indexNames = self::fetchIndexes($connection, $database, $prefixedTable);
        $indexStatements = [];

        if (!in_array('uk_code', $indexNames, true)) {
            $indexStatements[] = "ADD UNIQUE KEY `uk_code` (`code`)";
        }
        if (!in_array('idx_is_used', $indexNames, true)) {
            $indexStatements[] = "ADD INDEX `idx_is_used` (`is_used`)";
        }
        if (!in_array('idx_expires', $indexNames, true)) {
            $indexStatements[] = "ADD INDEX `idx_expires` (`expires_at`)";
        }

        if (!empty($indexStatements)) {
            $connection->statement("ALTER TABLE `{$prefixedTable}` " . implode(', ', $indexStatements));
        }

        self::$ensured[$table] = true;
    }

    /**
     * 确保接码订单表结构
     */
    public static function ensurePhoneCodeOrderTable(string $table): void
    {
        if (isset(self::$ensured[$table])) {
            return;
        }

        $schema = Capsule::schema();
        $connection = Capsule::connection();
        $prefixedTable = $connection->getTablePrefix() . $table;
        $database = $connection->getDatabaseName();

        if (!$schema->hasTable($table)) {
            $schema->create($table, function (Blueprint $blueprint) {
                $blueprint->bigIncrements('id');
                $blueprint->unsignedBigInteger('user_id')->comment('主站用户ID');
                $blueprint->string('fivesim_order_id', 64)->comment('5sim订单ID');
                $blueprint->string('phone', 32)->comment('号码');
                $blueprint->string('status', 32)->default('WAITING')->comment('订单状态');
                $blueprint->text('sms_content')->nullable()->comment('短信内容');
                $blueprint->decimal('cost', 10, 2)->default(0)->comment('成本');
                $blueprint->string('country', 64)->nullable()->comment('国家');
                $blueprint->string('operator', 64)->nullable()->comment('运营商');
                $blueprint->string('product', 64)->nullable()->comment('产品');
                $blueprint->timestamp('create_time')->useCurrent()->comment('创建时间');
                $blueprint->timestamp('update_time')->nullable()->useCurrentOnUpdate()->comment('更新时间');
                $blueprint->timestamp('expire_time')->nullable()->comment('过期时间');
                $blueprint->tinyInteger('is_deleted')->default(0)->comment('是否删除');
                $blueprint->index('user_id', 'idx_user_id');
                $blueprint->index('status', 'idx_status');
                $blueprint->index('is_deleted', 'idx_is_deleted');
            });
            self::$ensured[$table] = true;
            return;
        }

        $columnDetails = self::fetchColumnDetails($connection, $prefixedTable);
        $alterParts = [];

        $addColumn = function (string $name, string $definition) use (&$columnDetails, &$alterParts): void {
            if (!isset($columnDetails[$name])) {
                $alterParts[] = "ADD COLUMN {$definition}";
            }
        };

        $addColumn('fivesim_order_id', "`fivesim_order_id` VARCHAR(64) NOT NULL COMMENT '5sim订单ID' AFTER `user_id`");
        $addColumn('phone', "`phone` VARCHAR(32) NOT NULL COMMENT '号码' AFTER `fivesim_order_id`");
        $addColumn('status', "`status` VARCHAR(32) NOT NULL DEFAULT 'WAITING' COMMENT '订单状态' AFTER `phone`");
        $addColumn('sms_content', "`sms_content` TEXT NULL COMMENT '短信内容' AFTER `status`");
        $addColumn('cost', "`cost` DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT '成本' AFTER `sms_content`");
        $addColumn('country', "`country` VARCHAR(64) NULL COMMENT '国家' AFTER `cost`");
        $addColumn('operator', "`operator` VARCHAR(64) NULL COMMENT '运营商' AFTER `country`");
        $addColumn('product', "`product` VARCHAR(64) NULL COMMENT '产品' AFTER `operator`");
        $addColumn('create_time', "`create_time` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间' AFTER `product`");
        $addColumn('update_time', "`update_time` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间' AFTER `create_time`");
        $addColumn('expire_time', "`expire_time` TIMESTAMP NULL DEFAULT NULL COMMENT '过期时间' AFTER `update_time`");
        $addColumn('is_deleted', "`is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否删除' AFTER `expire_time`");

        if (!empty($alterParts)) {
            $connection->statement("ALTER TABLE `{$prefixedTable}` " . implode(', ', $alterParts));
        }

        // 确保状态列为字符串
        if (isset($columnDetails['status'])) {
            $statusType = $columnDetails['status']['type'];
            if (!str_contains($statusType, 'char') && !str_contains($statusType, 'text')) {
                $connection->statement("ALTER TABLE `{$prefixedTable}` MODIFY `status` VARCHAR(32) NOT NULL DEFAULT 'WAITING' COMMENT '订单状态'");
            }
        }

        // 索引
        $indexNames = self::fetchIndexes($connection, $database, $prefixedTable);
        $indexStatements = [];

        if (!in_array('idx_user_id', $indexNames, true)) {
            $indexStatements[] = "ADD INDEX `idx_user_id` (`user_id`)";
        }
        if (!in_array('idx_status', $indexNames, true)) {
            $indexStatements[] = "ADD INDEX `idx_status` (`status`)";
        }
        if (!in_array('idx_is_deleted', $indexNames, true)) {
            $indexStatements[] = "ADD INDEX `idx_is_deleted` (`is_deleted`)";
        }

        if (!empty($indexStatements)) {
            $connection->statement("ALTER TABLE `{$prefixedTable}` " . implode(', ', $indexStatements));
        }

        self::$ensured[$table] = true;
    }

    /**
     * @param \Illuminate\Database\ConnectionInterface $connection
     * @param string $table
     * @return array<int, string>
     */
    private static function fetchColumns($connection, string $table): array
    {
        return array_keys(self::fetchColumnDetails($connection, $table));
    }

    /**
     * @param \Illuminate\Database\ConnectionInterface $connection
     * @param string $table
     * @return array<string, array{name:string,type:string}>
     */
    private static function fetchColumnDetails($connection, string $table): array
    {
        $columns = $connection->select("SHOW COLUMNS FROM `{$table}`");
        $details = [];
        foreach ($columns as $column) {
            $name = is_array($column) ? $column['Field'] : $column->Field;
            $type = strtolower(is_array($column) ? $column['Type'] : $column->Type);
            $details[strtolower($name)] = [
                'name' => $name,
                'type' => $type,
            ];
        }
        return $details;
    }

    /**
     * @param \Illuminate\Database\ConnectionInterface $connection
     * @param string $database
     * @param string $table
     * @return array<int, string>
     */
    private static function fetchIndexes($connection, string $database, string $table): array
    {
        $indexes = $connection->select(
            "SELECT INDEX_NAME FROM information_schema.statistics WHERE table_schema = ? AND table_name = ?",
            [$database, $table]
        );

        $names = [];
        foreach ($indexes as $index) {
            $names[] = strtolower(is_array($index) ? $index['INDEX_NAME'] : $index->INDEX_NAME);
        }
        return $names;
    }
}
