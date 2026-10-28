<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $admin = config('cinemax.admin');

        if (empty($admin['email']) || empty($admin['password'])) {
            throw new RuntimeException('ADMIN_EMAIL and ADMIN_PASSWORD must be set to run this migration.');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('customer')->index()->after('password');
        });

        // The only admin ever created. Guards below are installed afterwards.
        DB::table('users')->insert([
            'name' => $admin['name'],
            'email' => $admin['email'],
            'password' => Hash::make($admin['password']),
            'email_verified_at' => now(),
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->installGuards();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS users_block_admin_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS users_block_admin_update');
        DB::unprepared('DROP TRIGGER IF EXISTS users_block_admin_delete');
        DB::unprepared('DROP INDEX users_single_admin'.(DB::getDriverName() === 'mysql' ? ' ON users' : ''));

        DB::table('users')->where('role', 'admin')->delete();

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }

    /**
     * Enforce at database level: one admin, none created, promoted or removed.
     */
    private function installGuards(): void
    {
        $message = 'Admin accounts cannot be created, promoted or removed.';

        match (DB::getDriverName()) {
            'mysql', 'mariadb' => $this->mysqlGuards($message),
            'sqlite' => $this->sqliteGuards($message),
            default => throw new RuntimeException('Unsupported database driver for admin guards.'),
        };
    }

    private function mysqlGuards(string $message): void
    {
        DB::unprepared("CREATE UNIQUE INDEX users_single_admin ON users ((CASE WHEN role = 'admin' THEN 1 END))");

        DB::unprepared("CREATE TRIGGER users_block_admin_insert BEFORE INSERT ON users FOR EACH ROW
            BEGIN
                IF NEW.role = 'admin' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = '$message'; END IF;
            END");

        DB::unprepared("CREATE TRIGGER users_block_admin_update BEFORE UPDATE ON users FOR EACH ROW
            BEGIN
                IF NEW.role = 'admin' AND OLD.role <> 'admin' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = '$message'; END IF;
            END");

        DB::unprepared("CREATE TRIGGER users_block_admin_delete BEFORE DELETE ON users FOR EACH ROW
            BEGIN
                IF OLD.role = 'admin' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = '$message'; END IF;
            END");
    }

    private function sqliteGuards(string $message): void
    {
        DB::unprepared("CREATE UNIQUE INDEX users_single_admin ON users (role) WHERE role = 'admin'");

        DB::unprepared("CREATE TRIGGER users_block_admin_insert BEFORE INSERT ON users
            WHEN NEW.role = 'admin'
            BEGIN SELECT RAISE(ABORT, '$message'); END");

        DB::unprepared("CREATE TRIGGER users_block_admin_update BEFORE UPDATE ON users
            WHEN NEW.role = 'admin' AND OLD.role <> 'admin'
            BEGIN SELECT RAISE(ABORT, '$message'); END");

        DB::unprepared("CREATE TRIGGER users_block_admin_delete BEFORE DELETE ON users
            WHEN OLD.role = 'admin'
            BEGIN SELECT RAISE(ABORT, '$message'); END");
    }
};
