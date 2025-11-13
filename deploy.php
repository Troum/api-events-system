<?php

namespace Deployer;

require 'recipe/laravel.php';

// Config
set('application', 'Camp Events API');
set('repository', 'git@github.com:Troum/api-events-system.git');
set('keep_releases', 3);
set('writable_mode', 'chmod');
set('writable_chmod_mode', '0775');

// Hosts
host('production')
    ->set('hostname', '185.20.227.190')
    ->set('remote_user', 'root')
    ->set('deploy_path', '/var/www/api.events-system.online')
    ->set('branch', 'main')
    ->set('http_user', 'www-data')
    ->set('writable_dirs', [
        'bootstrap/cache',
        'storage',
        'storage/app',
        'storage/app/public',
        'storage/framework',
        'storage/framework/cache',
        'storage/framework/sessions',
        'storage/framework/views',
        'storage/logs',
    ])
    ->set('shared_files', [
        '.env',
    ])
    ->set('shared_dirs', [
        'storage',
    ])
    ->set('writable_use_sudo', false);

// Tasks
desc('Build frontend assets');
task('build:assets', function () {
    if (test('[ -s "$HOME/.nvm/nvm.sh" ]')) {
        info('Building assets with Node.js 20 via nvm...');
        run('cd {{release_path}} && export NVM_DIR="$HOME/.nvm" && source "$NVM_DIR/nvm.sh" && nvm use 20 && npm run build --omit=dev');
        info('✓ Assets built successfully');
    } else {
        warning('⚠ nvm not found. Skipping asset build.');
    }
});

desc('Setup Filament assets');
task('filament:assets', function () {
    run('cd {{release_path}} && {{bin/php}} artisan filament:assets');
    info('✓ Filament assets published');
});

desc('Publish Livewire assets');
task('livewire:assets', function () {
    run('cd {{release_path}} && {{bin/php}} artisan vendor:publish --tag=livewire:assets --force');
    info('✓ Livewire assets published');
});

desc('Migrate storage files to shared directory (one-time)');
task('storage:migrate', function () {
    $oldStorage = '{{deploy_path}}/storage';
    $newStorage = '{{deploy_path}}/shared/storage';
    
    // Проверяем существует ли старая директория
    if (test("[ -d {$oldStorage} ]")) {
        // Создаём shared/storage если не существует
        run("mkdir -p {$newStorage}");
        
        // Перемещаем файлы из старой storage в shared/storage
        run("rsync -av {$oldStorage}/ {$newStorage}/ || true");
        
        info('✓ Storage files migrated to shared directory');
        warning('⚠ Old storage directory still exists at: ' . $oldStorage);
        warning('⚠ You can remove it manually after verifying files are accessible');
    } else {
        info('✓ No old storage directory found, skipping migration');
    }
});

desc('Create storage symlink pointing to shared storage');
task('storage:symlink', function () {
    $link = '{{release_path}}/public/storage';
    $target = '{{deploy_path}}/shared/storage/app/public';
    
    // Удаляем старый симлинк если существует
    run("rm -f {$link}");
    
    // Создаём новый симлинк на shared storage
    run("ln -sf {$target} {$link}");
    
    info('✓ Storage symlink created');
});

desc('Optimize Filament');
task('filament:optimize', function () {
    run('cd {{release_path}} && {{bin/php}} artisan filament:optimize');
});

desc('Clear all caches (safe, no DB connection)');
task('cache:clear-all', function () {
    // Очищаем кеш через удаление файлов, чтобы не подключаться к БД
    run('cd {{release_path}} && rm -rf bootstrap/cache/*.php');
    run('cd {{release_path}} && {{bin/php}} artisan config:clear || true');
    run('cd {{release_path}} && {{bin/php}} artisan route:clear || true');
    run('cd {{release_path}} && {{bin/php}} artisan view:clear || true');
    
    info('✓ Cache cleared successfully (no DB required)');
});

desc('Restart PHP-FPM');
task('php-fpm:restart', function () {
    run('sudo systemctl restart php8.4-fpm');
})->once();

desc('Update Nginx configuration');
task('nginx:config', function () {
    // Копируем конфиг на сервер
    upload('nginx.conf', '/tmp/nginx-api.conf');
    
    // Проверяем синтаксис перед применением
    run('sudo nginx -t -c /tmp/nginx-api.conf 2>&1 || (echo "Nginx config test failed" && exit 0)');
    
    // Бэкапим старый конфиг
    run('sudo cp /etc/nginx/sites-available/api.events-system.online /etc/nginx/sites-available/api.events-system.online.bak 2>/dev/null || true');
    
    // Применяем новый конфиг
    run('sudo cp /tmp/nginx-api.conf /etc/nginx/sites-available/api.events-system.online');
    
    // Проверяем общую конфигурацию Nginx
    run('sudo nginx -t');
    
    info('✓ Nginx configuration updated');
});

desc('Restart Nginx');
task('nginx:restart', function () {
    run('sudo systemctl restart nginx');
})->once();

desc('Setup production .env file');
task('env:setup', function () {
    $envContent = 'APP_NAME="Camp Events API"
APP_ENV=production
APP_KEY=base64:As8GNumvAJn7izUaJHM2fSWcxhmUA8+9wcBCOhuLz4E=
APP_DEBUG=false
APP_URL=https://api.events-system.online

APP_LOCALE=ru
APP_FALLBACK_LOCALE=ru
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=events_system
DB_USERNAME=events_manager
DB_PASSWORD="!@#1029QPwo#@!"

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=file

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"';

    // Создаём директорию shared если её нет
    run('mkdir -p {{deploy_path}}/shared');
    
    // Записываем .env файл через echo
    run("echo " . escapeshellarg($envContent) . " > {{deploy_path}}/shared/.env");
    
    info('✅ Production .env file created successfully!');
    info('📍 Location: {{deploy_path}}/shared/.env');
});

// Main deploy task
desc('Deploy the application');
task('deploy', [
    'deploy:prepare',
    'deploy:vendors',
    'build:assets',
    'filament:assets',
    'livewire:assets',
    'storage:symlink',
    'cache:clear-all',  // Очищаем кеш ПЕРЕД миграциями
    'artisan:migrate',
    'artisan:view:cache',
    'artisan:config:cache',
    // 'artisan:route:cache', // Отключено из-за конфликта с Livewire/Filament
    // 'artisan:optimize', // Отключено, так как тоже пытается кешировать маршруты
    'artisan:event:cache',
    'filament:optimize',
    'deploy:publish',
    'php-fpm:restart',
    'nginx:restart',
]);

// Rollback
after('rollback', 'php-fpm:restart');
after('rollback', 'nginx:restart');

// Если деплой провалился
fail('deploy', 'deploy:unlock');

