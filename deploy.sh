#!/bin/bash

# Скрипт для деплоя Laravel приложения на сервер
# Использование: bash deploy.sh

echo "🚀 Начинаем деплой приложения..."

# Переход в директорию проекта
cd /var/www/api.events-system.online || exit

echo "📥 Обновление кода из репозитория..."
git pull origin main

echo "📦 Установка зависимостей..."
composer install --optimize-autoloader --no-dev

echo "🧹 Очистка кешей..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan clear-compiled

echo "⚡ Оптимизация для продакшена..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:optimize

echo "🔐 Установка прав доступа..."
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

echo "🔄 Перезапуск сервисов..."
sudo systemctl restart php8.4-fpm
sudo systemctl restart nginx

echo "✅ Деплой завершён успешно!"
echo "🌐 Приложение доступно по адресу: https://api.events-system.online"
echo "🔑 Админ-панель: https://api.events-system.online/admin"

