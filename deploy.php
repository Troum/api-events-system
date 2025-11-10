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

