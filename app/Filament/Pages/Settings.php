<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'filament.pages.settings';

    protected static ?string $navigationLabel = 'Настройки сайта';

    protected static ?int $navigationSort = 99;

    public ?array $data = [];

    public function mount(): void
    {
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

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('SettingsTabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Контактные данные')
                            ->icon('heroicon-o-phone')
                            ->schema([
                                Forms\Components\Section::make('Основные контакты')
                                    ->schema([
                                        Forms\Components\TextInput::make('contact_phone')
                                            ->label('Телефон')
                                            ->tel()
                                            ->placeholder('+7 (999) 123-45-67')
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('contact_email')
                                            ->label('Email')
                                            ->email()
                                            ->placeholder('info@example.com')
                                            ->maxLength(255),

                                        Forms\Components\Textarea::make('contact_address')
                                            ->label('Адрес')
                                            ->placeholder('г. Москва, ул. Примерная, д. 1')
                                            ->rows(2)
                                            ->maxLength(500),
                                    ])
                                    ->columns(2),

                                Forms\Components\Section::make('Социальные сети')
                                    ->schema([
                                        Forms\Components\TextInput::make('contact_telegram')
                                            ->label('Telegram')
                                            ->placeholder('@username или https://t.me/username')
                                            ->maxLength(255)
                                            ->helperText('Можно указать @username или полную ссылку'),

                                        Forms\Components\TextInput::make('contact_whatsapp')
                                            ->label('WhatsApp')
                                            ->placeholder('+7 (999) 123-45-67')
                                            ->tel()
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('contact_instagram')
                                            ->label('Instagram')
                                            ->placeholder('@username или https://instagram.com/username')
                                            ->maxLength(255)
                                            ->helperText('Можно указать @username или полную ссылку'),

                                        Forms\Components\TextInput::make('contact_vk')
                                            ->label('VKontakte')
                                            ->placeholder('https://vk.com/username')
                                            ->url()
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('contact_facebook')
                                            ->label('Facebook')
                                            ->placeholder('https://facebook.com/username')
                                            ->url()
                                            ->maxLength(255),
                                    ])
                                    ->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Общие настройки')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Forms\Components\Section::make('Информация о сайте')
                                    ->schema([
                                        Forms\Components\TextInput::make('site_name')
                                            ->label('Название сайта')
                                            ->default('Camp Events')
                                            ->maxLength(255),

                                        Forms\Components\Textarea::make('site_description')
                                            ->label('Описание сайта')
                                            ->rows(3)
                                            ->maxLength(1000),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Нормализуем данные социальных сетей
        if (! empty($data['contact_telegram']) && ! str_starts_with($data['contact_telegram'], 'http') && ! str_starts_with($data['contact_telegram'], '@')) {
            $data['contact_telegram'] = '@'.ltrim($data['contact_telegram'], '@');
        }

        if (! empty($data['contact_instagram']) && ! str_starts_with($data['contact_instagram'], 'http') && ! str_starts_with($data['contact_instagram'], '@')) {
            $data['contact_instagram'] = '@'.ltrim($data['contact_instagram'], '@');
        }

        // Сохраняем каждую настройку
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

        Notification::make()
            ->title('Настройки сохранены')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Сохранить')
                ->submit('save'),
        ];
    }
}
