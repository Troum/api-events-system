<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Filament\Resources\SettingResource\RelationManagers;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Настройки';

    protected static ?string $modelLabel = 'Настройка';

    protected static ?string $pluralModelLabel = 'Настройки';

    protected static ?int $navigationSort = 100;

    public static function form(Form $form): Form
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
                                        
                                        Forms\Components\TextInput::make('contact_address')
                                            ->label('Адрес')
                                            ->placeholder('г. Москва, ул. Примерная, д. 1')
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
                                Forms\Components\TextInput::make('site_name')
                                    ->label('Название сайта')
                                    ->default('Camp Events')
                                    ->maxLength(255),
                                
                                Forms\Components\Textarea::make('site_description')
                                    ->label('Описание сайта')
                                    ->rows(3)
                                    ->maxLength(1000),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label('Ключ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('value')
                    ->label('Значение')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('group')
                    ->label('Группа')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Тип')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->label('Группа')
                    ->options([
                        'general' => 'Общие',
                        'contact' => 'Контакты',
                        'social' => 'Социальные сети',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
