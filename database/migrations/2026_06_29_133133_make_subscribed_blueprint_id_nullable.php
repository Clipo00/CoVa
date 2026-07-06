<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite doesn't support column modifications, so we recreate
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('CREATE TABLE blueprint_subscriptions_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                subscribed_blueprint_id INTEGER NULL,
                copied_blueprint_id INTEGER NOT NULL,
                notify_on_update INTEGER DEFAULT 1,
                created_at DATETIME,
                updated_at DATETIME,
                FOREIGN KEY (user_id) REFERENCES users(id),
                FOREIGN KEY (subscribed_blueprint_id) REFERENCES blueprints(id) ON DELETE SET NULL,
                FOREIGN KEY (copied_blueprint_id) REFERENCES blueprints(id),
                UNIQUE(user_id, subscribed_blueprint_id)
            )');
            DB::statement('INSERT INTO blueprint_subscriptions_new SELECT * FROM blueprint_subscriptions');
            DB::statement('DROP TABLE blueprint_subscriptions');
            DB::statement('ALTER TABLE blueprint_subscriptions_new RENAME TO blueprint_subscriptions');
        } else {
            Schema::table('blueprint_subscriptions', function ($table) {
                $table->dropForeign(['subscribed_blueprint_id']);
                $table->foreignId('subscribed_blueprint_id')->nullable()->change()->constrained('blueprints')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('CREATE TABLE blueprint_subscriptions_old (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                subscribed_blueprint_id INTEGER NOT NULL,
                copied_blueprint_id INTEGER NOT NULL,
                notify_on_update INTEGER DEFAULT 1,
                created_at DATETIME,
                updated_at DATETIME,
                FOREIGN KEY (user_id) REFERENCES users(id),
                FOREIGN KEY (subscribed_blueprint_id) REFERENCES blueprints(id) ON DELETE CASCADE,
                FOREIGN KEY (copied_blueprint_id) REFERENCES blueprints(id),
                UNIQUE(user_id, subscribed_blueprint_id)
            )');
            DB::statement('INSERT INTO blueprint_subscriptions_old SELECT * FROM blueprint_subscriptions WHERE subscribed_blueprint_id IS NOT NULL');
            DB::statement('DROP TABLE blueprint_subscriptions');
            DB::statement('ALTER TABLE blueprint_subscriptions_old RENAME TO blueprint_subscriptions');
        } else {
            Schema::table('blueprint_subscriptions', function ($table) {
                $table->dropForeign(['subscribed_blueprint_id']);
                $table->foreignId('subscribed_blueprint_id')->nullable(false)->change()->constrained('blueprints')->cascadeOnDelete();
            });
        }
    }
};
