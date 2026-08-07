<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: Recreate table with CHECK constraint for role
            DB::statement('PRAGMA foreign_keys = OFF');

            try {
                // Backup old table
                DB::statement('ALTER TABLE users RENAME TO users_backup');

                // Create new table with role having CHECK constraint
                DB::statement("
                    CREATE TABLE users (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        name TEXT NOT NULL,
                        email VARCHAR(191) NOT NULL UNIQUE,
                        email_verified_at DATETIME NULL,
                        password TEXT NOT NULL,
                        role TEXT NOT NULL DEFAULT 'employee'
                            CHECK(role IN ('superadmin', 'admin', 'employee', 'rh')),
                        tenant_id CHAR(36) NULL,
                        remember_token TEXT NULL,
                        created_at DATETIME NULL,
                        updated_at DATETIME NULL,
                        FOREIGN KEY(tenant_id) REFERENCES tenants(id) ON DELETE SET NULL
                    )
                ");

                // Copy all data from backup
                DB::statement("
                    INSERT INTO users
                    (id, name, email, email_verified_at, password, role, tenant_id, remember_token, created_at, updated_at)
                    SELECT id, name, email, email_verified_at, password, role, tenant_id, remember_token, created_at, updated_at
                    FROM users_backup
                ");

                // Drop backup
                DB::statement('DROP TABLE users_backup');

                DB::statement('PRAGMA foreign_keys = ON');
            } catch (\Exception $e) {
                DB::statement('PRAGMA foreign_keys = ON');
                throw $e;
            }
        } else {
            // MySQL/PostgreSQL: Alter enum values
            DB::statement("
                ALTER TABLE users
                MODIFY COLUMN role
                ENUM('superadmin', 'admin', 'employee', 'rh')
                NOT NULL DEFAULT 'employee'
            ");
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: Recreate with old enum values
            DB::statement('PRAGMA foreign_keys = OFF');

            try {
                DB::statement('ALTER TABLE users RENAME TO users_backup');

                DB::statement("
                    CREATE TABLE users (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        name TEXT NOT NULL,
                        email VARCHAR(191) NOT NULL UNIQUE,
                        email_verified_at DATETIME NULL,
                        password TEXT NOT NULL,
                        role TEXT NOT NULL DEFAULT 'employee'
                            CHECK(role IN ('superadmin', 'admin', 'employee')),
                        tenant_id CHAR(36) NULL,
                        remember_token TEXT NULL,
                        created_at DATETIME NULL,
                        updated_at DATETIME NULL,
                        FOREIGN KEY(tenant_id) REFERENCES tenants(id) ON DELETE SET NULL
                    )
                ");

                DB::statement("
                    INSERT INTO users
                    (id, name, email, email_verified_at, password, role, tenant_id, remember_token, created_at, updated_at)
                    SELECT id, name, email, email_verified_at, password, role, tenant_id, remember_token, created_at, updated_at
                    FROM users_backup
                    WHERE role != 'rh'
                ");

                DB::statement('DROP TABLE users_backup');

                DB::statement('PRAGMA foreign_keys = ON');
            } catch (\Exception $e) {
                DB::statement('PRAGMA foreign_keys = ON');
                throw $e;
            }
        } else {
            // MySQL: Revert enum
            DB::statement("
                ALTER TABLE users
                MODIFY COLUMN role
                ENUM('superadmin', 'admin', 'employee')
                NOT NULL DEFAULT 'employee'
            ");
        }
    }
};
