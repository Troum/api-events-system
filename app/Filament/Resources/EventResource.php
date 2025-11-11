<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Guava\FilamentIconPicker\Forms\IconPicker;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    
    protected static ?string $navigationLabel = 'Мероприятия';
    
    protected static ?string $modelLabel = 'Мероприятие';
    
    protected static ?string $pluralModelLabel = 'Мероприятия';
    
    // Используем ID для маршрутов в админке, несмотря на то что модель использует slug
    protected static ?string $recordRouteKeyName = 'id';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Tabs')
                    ->tabs([
                        // Основная информация
                        Forms\Components\Tabs\Tab::make('Основное')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Forms\Components\Section::make('Основная информация')
                                    ->schema([
                                        Forms\Components\TextInput::make('title')
                                            ->label('Название')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                                if ($operation !== 'create') {
                                                    return;
                                                }
                                                
                                                $set('slug', \Illuminate\Support\Str::slug($state));
                                            })
                                            ->columnSpanFull(),
                                        
                                        Forms\Components\TextInput::make('subtitle')
                                            ->label('Подзаголовок')
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                        
                                        Forms\Components\TextInput::make('slug')
                                            ->label('URL (slug)')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255)
                                            ->helperText('Автоматически генерируется из названия при создании'),
                                        
                                        Forms\Components\FileUpload::make('image')
                                            ->label('Главное изображение')
                                            ->image()
                                            ->directory('events')
                                            ->columnSpanFull(),
                                        
                                        Forms\Components\Textarea::make('description')
                                            ->label('Краткое описание')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                        
                                        Forms\Components\RichEditor::make('hero_description')
                                            ->label('Описание в Hero секции')
                                            ->columnSpanFull(),
                                        
                                        Forms\Components\RichEditor::make('about')
                                            ->label('Подробное описание')
                                            ->columnSpanFull(),
                                    ])->columns(2),
                                
                                Forms\Components\Section::make('Даты и локация')
                                    ->schema([
                                        Forms\Components\DatePicker::make('date_start')
                                            ->label('Дата начала')
                                            ->required(),
                                        
                                        Forms\Components\DatePicker::make('date_end')
                                            ->label('Дата окончания')
                                            ->required(),
                                        
                                        Forms\Components\TextInput::make('location')
                                            ->label('Локация (краткая)')
                                            ->required()
                                            ->maxLength(255),
                                        
                                        Forms\Components\TextInput::make('venue_name')
                                            ->label('Название места проведения')
                                            ->maxLength(255),
                                        
                                        Forms\Components\Textarea::make('venue_description')
                                            ->label('Описание места')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                        
                                        Forms\Components\TextInput::make('venue_address')
                                            ->label('Адрес')
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                        
                                        Forms\Components\TextInput::make('venue_latitude')
                                            ->label('Широта')
                                            ->numeric(),
                                        
                                        Forms\Components\TextInput::make('venue_longitude')
                                            ->label('Долгота')
                                            ->numeric(),
                                        
                                        Forms\Components\TextInput::make('airport_distance')
                                            ->label('Расстояние от аэропорта')
                                            ->maxLength(255),
                                    ])->columns(2),
                            ]),
                        
                        // Особенности
                        Forms\Components\Tabs\Tab::make('Особенности')
                            ->icon('heroicon-o-star')
                            ->schema([
                                Forms\Components\Repeater::make('features')
                                    ->label('Особенности мероприятия')
                                    ->schema([
                                        Forms\Components\TextInput::make('title')
                                            ->label('Заголовок')
                                            ->required(),
                                        
                                        Forms\Components\Textarea::make('description')
                                            ->label('Описание')
                                            ->rows(3),
                                        
                                        Forms\Components\Select::make('icon')
                                            ->label('Иконка (Lucide)')
                                            ->options([
                                                // Популярные
                                                'lucide-star' => '⭐ Звезда',
                                                'lucide-sparkles' => '✨ Искры',
                                                'lucide-flame' => '🔥 Огонь',
                                                'lucide-zap' => '⚡ Молния',
                                                'lucide-trophy' => '🏆 Трофей',
                                                'lucide-award' => '🏅 Награда',
                                                'lucide-medal' => '🥇 Медаль',
                                                'lucide-crown' => '👑 Корона',
                                                
                                                // Люди и команда
                                                'lucide-users' => '👥 Группа',
                                                'lucide-user-check' => '✅ Проверенный',
                                                'lucide-heart' => '❤️ Сердце',
                                                'lucide-heart-handshake' => '🤝 Рукопожатие',
                                                'lucide-graduation-cap' => '🎓 Образование',
                                                
                                                // Локация и путешествия
                                                'lucide-map-pin' => '📍 Метка',
                                                'lucide-map' => '🗺️ Карта',
                                                'lucide-compass' => '🧭 Компас',
                                                'lucide-plane' => '✈️ Самолет',
                                                'lucide-palmtree' => '🌴 Пальма',
                                                'lucide-mountain' => '⛰️ Гора',
                                                'lucide-waves' => '🌊 Волны',
                                                
                                                // Спорт и активность
                                                'lucide-dumbbell' => '🏋️ Гантели',
                                                'lucide-bike' => '🚴 Велосипед',
                                                'lucide-footprints' => '👣 Следы',
                                                'lucide-activity' => '📊 Активность',
                                                'lucide-trending-up' => '📈 Рост',
                                                
                                                // Время и события
                                                'lucide-calendar' => '📅 Календарь',
                                                'lucide-calendar-days' => '📆 Дни',
                                                'lucide-clock' => '🕐 Часы',
                                                'lucide-timer' => '⏱️ Таймер',
                                                'lucide-sunrise' => '🌅 Рассвет',
                                                'lucide-sunset' => '🌇 Закат',
                                                'lucide-sun' => '☀️ Солнце',
                                                'lucide-moon' => '🌙 Луна',
                                                
                                                // Технологии
                                                'lucide-camera' => '📷 Камера',
                                                'lucide-video' => '📹 Видео',
                                                'lucide-music' => '🎵 Музыка',
                                                'lucide-mic' => '🎤 Микрофон',
                                                'lucide-lightbulb' => '💡 Идея',
                                                'lucide-rocket' => '🚀 Ракета',
                                                
                                                // Безопасность и качество
                                                'lucide-shield' => '🛡️ Щит',
                                                'lucide-shield-check' => '✅ Защита',
                                                'lucide-badge-check' => '✔️ Проверено',
                                                'lucide-verified' => '✓ Верифицировано',
                                                
                                                // Прочее
                                                'lucide-globe' => '🌍 Глобус',
                                                'lucide-target' => '🎯 Цель',
                                                'lucide-beaker' => '🧪 Эксперимент',
                                                'lucide-gem' => '💎 Бриллиант',
                                            ])
                                            ->searchable()
                                            ->default('lucide-star')
                                            ->helperText('Все иконки с lucide.dev')
                                            ->columns(1),
                                    ])
                                    ->columns(1)
                                    ->collapsible()
                                    ->columnSpanFull(),
                            ]),
                        
                        // Программа
                        Forms\Components\Tabs\Tab::make('Программа')
                            ->icon('heroicon-o-calendar')
                            ->schema([
                                Forms\Components\Repeater::make('schedule')
                                    ->label('Расписание по дням')
                                    ->schema([
                                        Forms\Components\TextInput::make('date')
                                            ->label('Дата/День')
                                            ->required(),
                                        
                                        Forms\Components\TextInput::make('title')
                                            ->label('Название дня')
                                            ->placeholder('День заезда, День отдыха и т.д.'),
                                        
                                        Forms\Components\Repeater::make('activities')
                                            ->label('Активности')
                                            ->schema([
                                                Forms\Components\TextInput::make('time')
                                                    ->label('Время')
                                                    ->placeholder('10:00 - 11:30'),
                                                
                                                Forms\Components\Textarea::make('description')
                                                    ->label('Описание')
                                                    ->rows(2),
                                            ])
                                            ->columns(2)
                                            ->defaultItems(0),
                                    ])
                                    ->columns(1)
                                    ->collapsible()
                                    ->columnSpanFull(),
                            ]),
                        
                        // Инфраструктура
                        Forms\Components\Tabs\Tab::make('Инфраструктура')
                            ->icon('heroicon-o-building-office-2')
                            ->schema([
                                Forms\Components\Repeater::make('infrastructure')
                                    ->label('Объекты инфраструктуры')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Название')
                                            ->required(),
                                        
                                        Forms\Components\Textarea::make('description')
                                            ->label('Описание')
                                            ->rows(3),
                                        
                                        Forms\Components\FileUpload::make('images')
                                            ->label('Изображения')
                                            ->image()
                                            ->multiple()
                                            ->directory('infrastructure')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(1)
                                    ->collapsible()
                                    ->columnSpanFull(),
                            ]),
                        
                        // Команда
                        Forms\Components\Tabs\Tab::make('Команда')
                            ->icon('heroicon-o-user-group')
                            ->schema([
                                Forms\Components\Repeater::make('team')
                                    ->label('Тренеры / Организаторы')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Имя')
                                            ->required(),
                                        
                                        Forms\Components\TextInput::make('role')
                                            ->label('Роль/Должность'),
                                        
                                        Forms\Components\Textarea::make('bio')
                                            ->label('Биография')
                                            ->rows(3),
                                        
                                        Forms\Components\FileUpload::make('photo')
                                            ->label('Фото')
                                            ->image()
                                            ->directory('team'),
                                        
                                        Forms\Components\TextInput::make('instagram')
                                            ->label('Instagram')
                                            ->prefix('@'),
                                        
                                        Forms\Components\TextInput::make('telegram')
                                            ->label('Telegram')
                                            ->prefix('@'),
                                    ])
                                    ->columns(2)
                                    ->collapsible()
                                    ->columnSpanFull(),
                            ]),
                        
                        // Пакеты и цены
                        Forms\Components\Tabs\Tab::make('Стоимость')
                            ->icon('heroicon-o-currency-dollar')
                            ->schema([
                                Forms\Components\Repeater::make('packages')
                                    ->label('Пакеты участия')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Название пакета')
                                            ->required(),
                                        
                                        Forms\Components\Select::make('icon')
                                            ->label('Иконка (Lucide)')
                                            ->options([
                                                // Базовые пакеты
                                                'lucide-package' => '📦 Базовый',
                                                'lucide-box' => '📦 Стандарт',
                                                'lucide-gift' => '🎁 Подарок',
                                                'lucide-shopping-bag' => '🛍️ Покупка',
                                                
                                                // Премиум
                                                'lucide-star' => '⭐ Премиум',
                                                'lucide-sparkles' => '✨ Эксклюзив',
                                                'lucide-crown' => '👑 Королевский',
                                                'lucide-gem' => '💎 Бриллиант',
                                                'lucide-diamond' => '💠 Платина',
                                                
                                                // Специальные
                                                'lucide-rocket' => '🚀 Супер',
                                                'lucide-zap' => '⚡ Быстрый старт',
                                                'lucide-flame' => '🔥 Горячее',
                                                'lucide-trophy' => '🏆 VIP',
                                                'lucide-award' => '🏅 Победитель',
                                                'lucide-medal' => '🥇 Золотой',
                                                
                                                // Популярные
                                                'lucide-heart' => '❤️ Популярный',
                                                'lucide-trending-up' => '📈 Топ',
                                                'lucide-star-half' => '⭐ Рекомендуем',
                                                
                                                // Акции
                                                'lucide-tag' => '🏷️ Акция',
                                                'lucide-percent' => '💯 Скидка',
                                                'lucide-ticket' => '🎫 Билет',
                                            ])
                                            ->searchable()
                                            ->default('lucide-package')
                                            ->helperText('Иконки с lucide.dev')
                                            ->columnSpanFull(),
                                        
                                        Forms\Components\TextInput::make('price')
                                            ->label('Цена')
                                            ->numeric()
                                            ->prefix('€')
                                            ->required(),
                                        
                                        Forms\Components\TextInput::make('price_note')
                                            ->label('Примечание к цене')
                                            ->placeholder('с человека'),
                                        
                                        Forms\Components\Textarea::make('description')
                                            ->label('Описание пакета')
                                            ->rows(2),
                                        
                                        Forms\Components\Repeater::make('includes')
                                            ->label('Что входит')
                                            ->schema([
                                                Forms\Components\TextInput::make('item')
                                                    ->label('Пункт')
                                                    ->required(),
                                            ])
                                            ->defaultItems(0)
                                            ->collapsible(),
                                        
                                        Forms\Components\Toggle::make('is_featured')
                                            ->label('Рекомендуемый пакет'),
                                    ])
                                    ->columns(2)
                                    ->collapsible()
                                    ->columnSpanFull(),
                                
                                Forms\Components\Repeater::make('not_included')
                                    ->label('Что не входит в стоимость')
                                    ->schema([
                                        Forms\Components\TextInput::make('item')
                                            ->label('Пункт')
                                            ->required(),
                                    ])
                                    ->columnSpanFull()
                                    ->collapsible(),
                            ]),
                        
                        // Дополнительно
                        Forms\Components\Tabs\Tab::make('Дополнительно')
                            ->icon('heroicon-o-plus-circle')
                            ->schema([
                                Forms\Components\Section::make('Рекомендованные рейсы')
                                    ->schema([
                                        Forms\Components\Repeater::make('recommended_flights')
                                            ->schema([
                                                Forms\Components\Select::make('direction')
                                                    ->label('Направление')
                                                    ->options([
                                                        'outbound' => 'Туда',
                                                        'return' => 'Обратно',
                                                    ])
                                                    ->required(),
                                                
                                                Forms\Components\TextInput::make('airline')
                                                    ->label('Авиакомпания'),
                                                
                                                Forms\Components\Textarea::make('details')
                                                    ->label('Детали рейса')
                                                    ->rows(3),
                                            ])
                                            ->columns(3)
                                            ->collapsible(),
                                    ])->collapsible(),
                                
                                Forms\Components\Section::make('FAQ')
                                    ->schema([
                                        Forms\Components\Repeater::make('faq')
                                            ->schema([
                                                Forms\Components\TextInput::make('question')
                                                    ->label('Вопрос')
                                                    ->required(),
                                                
                                                Forms\Components\Textarea::make('answer')
                                                    ->label('Ответ')
                                                    ->rows(3)
                                                    ->required(),
                                            ])
                                            ->columns(1)
                                            ->collapsible(),
                                    ])->collapsible(),
                                
                                Forms\Components\Section::make('Галерея')
                                    ->schema([
                                        Forms\Components\FileUpload::make('gallery')
                                            ->label('Изображения')
                                            ->image()
                                            ->multiple()
                                            ->directory('gallery')
                                            ->columnSpanFull(),
                                        
                                        Forms\Components\FileUpload::make('hero_images')
                                            ->label('Hero слайдер')
                                            ->image()
                                            ->multiple()
                                            ->directory('hero')
                                            ->columnSpanFull(),
                                    ])->collapsible(),
                            ]),
                        
                        // Контакты и настройки
                        Forms\Components\Tabs\Tab::make('Настройки')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Forms\Components\Section::make('Контакты организатора')
                                    ->schema([
                                        Forms\Components\TextInput::make('organizer_name')
                                            ->label('Имя организатора'),
                                        
                                        Forms\Components\TextInput::make('organizer_phone')
                                            ->label('Телефон')
                                            ->tel(),
                                        
                                        Forms\Components\TextInput::make('organizer_email')
                                            ->label('Email')
                                            ->email(),
                                        
                                        Forms\Components\TextInput::make('organizer_telegram')
                                            ->label('Telegram')
                                            ->prefix('@'),
                                        
                                        Forms\Components\TextInput::make('organizer_whatsapp')
                                            ->label('WhatsApp'),
                                    ])->columns(2),
                                
                                Forms\Components\Section::make('Настройки отображения')
                                    ->schema([
                                        Forms\Components\Toggle::make('show_booking_form')
                                            ->label('Показывать форму бронирования')
                                            ->default(true),
                                        
                                        Forms\Components\Toggle::make('show_countdown')
                                            ->label('Показывать обратный отсчет'),
                                        
                                        Forms\Components\TextInput::make('max_participants')
                                            ->label('Максимум участников')
                                            ->numeric(),
                                        
                                        Forms\Components\TextInput::make('current_participants')
                                            ->label('Текущее количество участников')
                                            ->numeric()
                                            ->default(0),
                                    ])->columns(2),
                                
                                Forms\Components\Section::make('SEO')
                                    ->schema([
                                        Forms\Components\Textarea::make('meta_description')
                                            ->label('Meta описание')
                                            ->rows(2),
                                        
                                        Forms\Components\TagsInput::make('meta_keywords')
                                            ->label('Ключевые слова'),
                                        
                                        Forms\Components\FileUpload::make('og_image')
                                            ->label('Open Graph изображение')
                                            ->image()
                                            ->directory('og'),
                                    ])->collapsible(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Изображение'),
                
                Tables\Columns\TextColumn::make('title')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('location')
                    ->label('Локация')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('date_start')
                    ->label('Начало')
                    ->date('d.m.Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('date_end')
                    ->label('Окончание')
                    ->date('d.m.Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('current_participants')
                    ->label('Участников')
                    ->badge()
                    ->formatStateUsing(fn ($state, $record) => 
                        $record->max_participants 
                            ? "{$state}/{$record->max_participants}" 
                            : $state
                    ),
                
                Tables\Columns\IconColumn::make('show_booking_form')
                    ->label('Бронирование')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('show_booking_form')
                    ->label('С бронированием'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date_start', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TripsRelationManager::class,
            RelationManagers\TeamMembersRelationManager::class,
            RelationManagers\EventPackagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}
