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
    if (test('[ -d public/build ]') && test('[ "$(ls -A public/build)" ]')) {
        info('✓ Assets already built (public/build exists)');
        return;
    }
    
    if (test('command -v npm')) {
        info('Building assets with npm...');
        run('cd {{release_path}} && npm install --production=false');
        run('cd {{release_path}} && npm run build');
        info('✓ Assets built successfully');
    } else {
        warning('⚠ npm not found. Please build assets locally and commit them.');
    }
});

desc('Optimize Filament');
task('filament:optimize', function () {
    run('cd {{release_path}} && {{bin/php}} artisan filament:optimize');
});

desc('Clear all caches');
task('cache:clear-all', function () {
    run('cd {{release_path}} && {{bin/php}} artisan config:clear');
    run('cd {{release_path}} && {{bin/php}} artisan route:clear');
    run('cd {{release_path}} && {{bin/php}} artisan view:clear');
    run('cd {{release_path}} && {{bin/php}} artisan cache:clear');
});

desc('Restart PHP-FPM');
task('php-fpm:restart', function () {
    run('sudo systemctl restart php8.4-fpm');
})->once();

desc('Restart Nginx');
task('nginx:restart', function () {
    run('sudo systemctl restart nginx');
})->once();

desc('Setup production .env file');
task('env:setup', function () {
    $envContent = <<<'ENV'
APP_NAME="Camp Events API"
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

CACHE_STORE=database

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

VITE_APP_NAME="${APP_NAME}"
ENV;

    // Создаём директорию shared если её нет
    run('mkdir -p {{deploy_path}}/shared');
    
    // Записываем .env файл
    run("cat > {{deploy_path}}/shared/.env << 'ENVFILE'\n{$envContent}\nENVFILE");
    
    info('✅ Production .env file created successfully!');
    info('📍 Location: {{deploy_path}}/shared/.env');
});

// Hooks - убраны, так как задачи уже в основном flow

// Main deploy task
desc('Deploy the application');
task('deploy', [
    'deploy:prepare',
    'deploy:vendors',
    'build:assets',
    'artisan:storage:link',
    'artisan:migrate',
    'artisan:view:cache',
    'artisan:config:cache',
    'artisan:route:cache',
    'artisan:optimize',
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

