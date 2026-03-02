<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class K1Install extends Command
{
    /**
     * Install Kenya 1 platform (monolith) end-to-end.
     */
    protected $signature = 'k1:install
        {--fresh : Drop all tables and re-run all migrations}
        {--seed : Run seeders after migrating}
        {--admin : Create (or update) the super admin user}
        {--email= : Super admin email (used with --admin)}
        {--password= : Super admin password (used with --admin)}
        {--force : Force in production (bypass confirmation)}';

    protected $description = 'Kenya 1: full platform installer (env checks, migrate, seed, super admin, modules, wallet defaults, optimize).';

    public function handle(): int
    {
        $this->banner();

        // 0) Production guard
        if (app()->environment('production') && !$this->option('force')) {
            if (!$this->confirm('You are in PRODUCTION. Continue?', false)) {
                $this->warn('Cancelled.');
                return self::SUCCESS;
            }
        }

        // 1) Validate environment & filesystem
        if (!$this->validateEnvironment()) {
            $this->error('Fix the above issues, then re-run: php artisan k1:install');
            return self::FAILURE;
        }

        // 2) Ensure APP_KEY
        $this->ensureAppKey();

        // 3) DB connectivity check
        if (!$this->checkDatabaseConnection()) {
            $this->error('Database connection failed. Check DB_* values in .env');
            return self::FAILURE;
        }

        // 4) Migrations
        $this->runMigrations();

        // 5) Core seeding (roles/permissions/modules/settings/wallet defaults)
        if ($this->option('seed')) {
            $this->runSeeders();
        }

        // 6) Super admin creation (optional, but recommended)
        if ($this->option('admin')) {
            $this->createOrUpdateSuperAdmin();
        }

        // 7) Storage link
        $this->createStorageLink();

        // 8) Optimize caches
        $this->optimizeApp();

        // 9) Health report
        $this->healthReport();

        $this->info("\n✅ Kenya 1 install complete.");
        $this->line("Next: php artisan serve   (or deploy to Nginx/Apache)");
        return self::SUCCESS;
    }

    private function banner(): void
    {
        $this->line('');
        $this->line('=============================================');
        $this->line('      KENYA 1 PLATFORM INSTALLER (K1)');
        $this->line('=============================================');
        $this->line('');
    }

    /**
     * 10 things this installer automates:
     * 1) env + permissions validation
     * 2) APP_KEY generation if missing
     * 3) DB connectivity verification
     * 4) migrations (fresh optional)
     * 5) core seeds (roles/permissions/modules/settings/wallet defaults)
     * 6) super admin create/update
     * 7) storage:link
     * 8) caches optimize
     * 9) health report summary
     * 10) production safety guard
     */
    private function validateEnvironment(): bool
    {
        $ok = true;

        // .env exists?
        if (!File::exists(base_path('.env'))) {
            $this->error('Missing .env file. Create it from .env.example');
            $ok = false;
        }

        // writable storage & bootstrap/cache
        foreach (['storage', 'bootstrap/cache'] as $path) {
            $full = base_path($path);
            if (!File::exists($full)) {
                $this->error("Missing directory: {$path}");
                $ok = false;
                continue;
            }
            if (!is_writable($full)) {
                $this->error("Not writable: {$path} (fix permissions)");
                $ok = false;
            }
        }

        // required env keys (basic)
        $required = ['APP_NAME', 'APP_ENV', 'APP_URL', 'DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME'];
        foreach ($required as $key) {
            if (blank(env($key))) {
                $this->error("Missing env: {$key}");
                $ok = false;
            }
        }

        // PHP extensions check (minimal)
        $exts = ['openssl', 'pdo', 'mbstring', 'tokenizer', 'xml', 'ctype', 'json'];
        foreach ($exts as $ext) {
            if (!extension_loaded($ext)) {
                $this->error("PHP extension missing: {$ext}");
                $ok = false;
            }
        }

        if ($ok) $this->info('✔ Environment looks OK.');
        return $ok;
    }

    private function ensureAppKey(): void
    {
        $key = config('app.key');
        if (blank($key)) {
            $this->warn('APP_KEY missing → generating...');
            Artisan::call('key:generate', ['--force' => true]);
            $this->info('✔ APP_KEY generated.');
        } else {
            $this->info('✔ APP_KEY present.');
        }
    }

    private function checkDatabaseConnection(): bool
    {
        try {
            DB::connection()->getPdo();
            $this->info('✔ Database connection OK.');
            return true;
        } catch (\Throwable $e) {
            $this->error('DB error: ' . $e->getMessage());
            return false;
        }
    }

    private function runMigrations(): void
    {
        if ($this->option('fresh')) {
            $this->warn('Running migrate:fresh...');
            Artisan::call('migrate:fresh', ['--force' => true]);
        } else {
            $this->warn('Running migrate...');
            Artisan::call('migrate', ['--force' => true]);
        }

        $this->line(Artisan::output());
        $this->info('✔ Migrations complete.');
    }

    private function runSeeders(): void
    {
        $this->warn('Running seeders...');
        Artisan::call('db:seed', ['--force' => true]);
        $this->line(Artisan::output());
        $this->info('✔ Seeding complete.');
    }

    private function createOrUpdateSuperAdmin(): void
    {
        $email = $this->option('email') ?: $this->ask('Super admin email', 'admin@kenya1.local');
        $password = $this->option('password') ?: $this->secret('Super admin password (min 10 chars)');
        if (strlen((string)$password) < 10) {
            $this->error('Password too short. Use at least 10 chars.');
            return;
        }

        // We assume a users table exists with at least: id, name, email, password
        $name = 'Kenya 1 Super Admin';

        $user = DB::table('users')->where('email', $email)->first();
        if ($user) {
            DB::table('users')->where('id', $user->id)->update([
                'name' => $name,
                'password' => Hash::make($password),
                'updated_at' => now(),
            ]);
            $userId = $user->id;
            $this->info("✔ Updated super admin: {$email}");
        } else {
            $userId = DB::table('users')->insertGetId([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->info("✔ Created super admin: {$email}");
        }

        // Assign role (custom role system expected)
        // If roles table exists: roles(name, slug); role_user(user_id, role_id)
        if ($this->tableExists('roles') && $this->tableExists('role_user')) {
            $roleId = DB::table('roles')->where('slug', 'super_admin')->value('id');
            if ($roleId) {
                DB::table('role_user')->updateOrInsert(
                    ['user_id' => $userId, 'role_id' => $roleId],
                    ['created_at' => now(), 'updated_at' => now()]
                );
                $this->info('✔ Assigned role: super_admin');
            } else {
                $this->warn('roles table exists, but super_admin role not found. Run --seed.');
            }
        } else {
            $this->warn('Role tables not found yet. Create roles migrations + seeders first.');
        }
    }

    private function createStorageLink(): void
    {
        $publicStorage = public_path('storage');
        if (File::exists($publicStorage)) {
            $this->info('✔ storage link already exists.');
            return;
        }

        $this->warn('Creating storage link...');
        Artisan::call('storage:link');
        $this->line(Artisan::output());
        $this->info('✔ storage link created.');
    }

    private function optimizeApp(): void
    {
        $this->warn('Optimizing caches...');
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');

        $this->info('✔ Optimization complete.');
    }

    private function healthReport(): void
    {
        $this->line("\n--- Health Report ---");

        $tables = [];
        try {
            $tables = DB::select('SHOW TABLES');
        } catch (\Throwable $e) {
            // For non-MySQL, ignore
        }

        $this->line('APP_ENV: ' . app()->environment());
        $this->line('APP_URL: ' . (config('app.url') ?: 'n/a'));
        $this->line('DB: ' . (config('database.default') ?: 'n/a'));
        if (!empty($tables)) {
            $this->line('Tables: ' . count($tables));
        }

        $this->line('---------------------');
    }

    private function tableExists(string $table): bool
    {
        try {
            return DB::getSchemaBuilder()->hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }
}
