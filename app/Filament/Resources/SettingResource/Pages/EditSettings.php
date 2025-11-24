<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use App\Models\Setting;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditSettings extends EditRecord
{
    protected static string $resource = SettingResource::class;

    protected function resolveRecord(int|string $key): Model
    {
        // Создаем или получаем реальную запись с ID=1 для настроек
        $record = Setting::find(1);

        if (! $record) {
            // Создаем запись вручную с ID=1
            $record = new Setting;
            $record->id = 1;
            $record->key = 'settings_singleton';
            $record->value = null;
            $record->group = 'general';
            $record->type = 'text';
            $record->description = 'Singleton record for site settings';
            $record->save();
        }

        return $record;
    }

    public function getRecord(): Model
    {
        // Всегда возвращаем виртуальную запись
        if (! $this->record) {
            $this->record = $this->resolveRecord(1);
        }

        return $this->record;
    }

    public function mount(int|string $record = 1): void
    {
        // Создаем виртуальную запись для настроек
        $this->record = $this->resolveRecord($record);

        $this->form->fill([
            'contact_phone' => Setting::get('contact_phone'),
            'contact_email' => Setting::get('contact_email'),
            'contact_address' => Setting::get('contact_address'),
            'contact_telegram' => Setting::get('contact_telegram'),
            'contact_whatsapp' => Setting::get('contact_whatsapp'),
            'contact_instagram' => Setting::get('contact_instagram'),
            'contact_vk' => Setting::get('contact_vk'),
            'contact_facebook' => Setting::get('contact_facebook'),
            'site_name' => Setting::get('site_name', 'Camp Events'),
            'site_description' => Setting::get('site_description'),
        ]);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Нормализуем данные социальных сетей
        if (! empty($data['contact_telegram']) && ! str_starts_with($data['contact_telegram'], 'http') && ! str_starts_with($data['contact_telegram'], '@')) {
            $data['contact_telegram'] = '@'.ltrim($data['contact_telegram'], '@');
        }

        if (! empty($data['contact_instagram']) && ! str_starts_with($data['contact_instagram'], 'http') && ! str_starts_with($data['contact_instagram'], '@')) {
            $data['contact_instagram'] = '@'.ltrim($data['contact_instagram'], '@');
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Сохраняем каждую настройку через наш метод Setting::set()
        Setting::set('contact_phone', $data['contact_phone'] ?? null, 'contact', 'phone', 'Основной телефон для связи');
        Setting::set('contact_email', $data['contact_email'] ?? null, 'contact', 'email', 'Основной email для связи');
        Setting::set('contact_address', $data['contact_address'] ?? null, 'contact', 'text', 'Физический адрес');
        Setting::set('contact_telegram', $data['contact_telegram'] ?? null, 'contact', 'url', 'Telegram');
        Setting::set('contact_whatsapp', $data['contact_whatsapp'] ?? null, 'contact', 'phone', 'WhatsApp');
        Setting::set('contact_instagram', $data['contact_instagram'] ?? null, 'contact', 'url', 'Instagram');
        Setting::set('contact_vk', $data['contact_vk'] ?? null, 'contact', 'url', 'VKontakte');
        Setting::set('contact_facebook', $data['contact_facebook'] ?? null, 'contact', 'url', 'Facebook');
        Setting::set('site_name', $data['site_name'] ?? 'Camp Events', 'general', 'text', 'Название сайта');
        Setting::set('site_description', $data['site_description'] ?? null, 'general', 'text', 'Описание сайта');

        // Возвращаем запись без изменений (настройки сохраняются через Setting::set())
        return $record;
    }

    protected function getRedirectUrl(): ?string
    {
        // Не перенаправляем, остаемся на той же странице
        return null;
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Настройки сохранены')
            ->body('Настройки сайта успешно обновлены.');
    }

    protected function getHeaderActions(): array
    {
        return [
            // Убираем DeleteAction, так как настройки нельзя удалить
        ];
    }
}
