<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TripResource\Pages;
use App\Models\Trip;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Guava\FilamentIconPicker\Forms\IconPicker;

class TripResource extends Resource
{
    protected static ?string $model = Trip::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    
    protected static ?string $navigationLabel = 'Поездки';
    
    protected static ?string $modelLabel = 'Поездка';
    
    protected static ?string $pluralModelLabel = 'Поездки';

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
                                        Forms\Components\Select::make('event_id')
                                            ->label('Мероприятие')
                                            ->relationship('event', 'title')
                                            ->required()
                                            ->searchable()
                                            ->preload(),
                                        
                                        Forms\Components\Select::make('status')
                                            ->label('Статус')
                                            ->options([
                                                'draft' => 'Черновик',
                                                'published' => 'Опубликовано',
                                                'cancelled' => 'Отменено',
                                                'completed' => 'Завершено',
                                            ])
                                            ->default('draft')
                                            ->required(),
                                        
                                        Forms\Components\TextInput::make('title')
                                            ->label('Название поездки')
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                        
                                        Forms\Components\TextInput::make('slug')
                                            ->label('URL (slug)')
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255),
                                        
                                        Forms\Components\Toggle::make('is_featured')
                                            ->label('Рекомендуемая поездка'),
                                        
                                        Forms\Components\Textarea::make('description')
                                            ->label('Описание')
                                            ->rows(4)
                                            ->columnSpanFull(),
                                        
                                        Forms\Components\FileUpload::make('images')
                                            ->label('Изображения')
                                            ->image()
                                            ->multiple()
                                            ->directory('trips')
                                            ->columnSpanFull(),
                                    ])->columns(2),
                                
                                Forms\Components\Section::make('Маршрут')
                                    ->schema([
                                        Forms\Components\TextInput::make('city_from')
                                            ->label('Город отправления')
                                            ->required()
                                            ->maxLength(255),
                                        
                                        Forms\Components\TextInput::make('city_to')
                                            ->label('Город назначения')
                                            ->maxLength(255),
                                        
                                        Forms\Components\Select::make('transport_type')
                                            ->label('Тип транспорта')
                                            ->options([
                                                'bus' => 'Автобус',
                                                'minibus' => 'Микроавтобус',
                                                'plane' => 'Самолет',
                                                'train' => 'Поезд',
                                                'car' => 'Автомобиль',
                                            ]),
                                        
                                        Forms\Components\TextInput::make('duration')
                                            ->label('Продолжительность')
                                            ->placeholder('3 часа 30 минут'),
                                        
                                        Forms\Components\TimePicker::make('departure_time')
                                            ->label('Время отправления')
                                            ->required(),
                                        
                                        Forms\Components\TimePicker::make('arrival_time')
                                            ->label('Время прибытия'),
                                        
                                        Forms\Components\Textarea::make('route_description')
                                            ->label('Описание маршрута')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ])->columns(2),
                            ]),
                        
                        // Места и цены
                        Forms\Components\Tabs\Tab::make('Места и цены')
                            ->icon('heroicon-o-currency-dollar')
                            ->schema([
                                Forms\Components\Section::make('Места')
                                    ->schema([
                                        Forms\Components\TextInput::make('seats_total')
                                            ->label('Всего мест')
                                            ->numeric()
                                            ->required()
                                            ->minValue(1),
                                        
                                        Forms\Components\TextInput::make('seats_taken')
                                            ->label('Занято мест')
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0),
                                        
                                        Forms\Components\Toggle::make('allow_waitlist')
                                            ->label('Разрешить лист ожидания'),
                                        
                                        Forms\Components\TextInput::make('waitlist_count')
                                            ->label('В листе ожидания')
                                            ->numeric()
                                            ->default(0)
                                            ->disabled(),
                                    ])->columns(2),
                                
                                Forms\Components\Section::make('Цены')
                                    ->schema([
                                        Forms\Components\TextInput::make('price')
                                            ->label('Обычная цена')
                                            ->numeric()
                                            ->prefix('₽')
                                            ->required(),
                                        
                                        Forms\Components\TextInput::make('early_bird_price')
                                            ->label('Цена раннего бронирования')
                                            ->numeric()
                                            ->prefix('₽'),
                                        
                                        Forms\Components\DatePicker::make('early_bird_deadline')
                                            ->label('Дедлайн ранней цены'),
                                        
                                        Forms\Components\Repeater::make('discounts')
                                            ->label('Скидки')
                                            ->schema([
                                                Forms\Components\TextInput::make('name')
                                                    ->label('Название')
                                                    ->placeholder('Групповая скидка'),
                                                
                                                Forms\Components\TextInput::make('amount')
                                                    ->label('Размер скидки')
                                                    ->numeric()
                                                    ->suffix('%'),
                                                
                                                Forms\Components\Textarea::make('conditions')
                                                    ->label('Условия')
                                                    ->rows(2),
                                            ])
                                            ->columns(3)
                                            ->collapsible()
                                            ->columnSpanFull(),
                                    ])->columns(3),
                            ]),
                        
                        // Что включено
                        Forms\Components\Tabs\Tab::make('Что включено')
                            ->icon('heroicon-o-check-circle')
                            ->schema([
                                Forms\Components\Repeater::make('includes')
                                    ->label('Что входит в стоимость')
                                    ->schema([
                                        Forms\Components\TextInput::make('item')
                                            ->label('Пункт')
                                            ->required(),
                                    ])
                                    ->columnSpanFull()
                                    ->collapsible(),
                                
                                Forms\Components\Repeater::make('not_includes')
                                    ->label('Что не входит в стоимость')
                                    ->schema([
                                        Forms\Components\TextInput::make('item')
                                            ->label('Пункт')
                                            ->required(),
                                    ])
                                    ->columnSpanFull()
                                    ->collapsible(),
                                
                                Forms\Components\Repeater::make('amenities')
                                    ->label('Удобства в транспорте')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Название')
                                            ->required(),
                                        
                                        Forms\Components\Select::make('icon')
                                            ->label('Иконка (Lucide)')
                                            ->options([
                                                // Связь и интернет
                                                'lucide-wifi' => '📶 Wi-Fi',
                                                'lucide-signal' => '📡 Сигнал',
                                                'lucide-smartphone' => '📱 Телефон',
                                                'lucide-tablet' => '📱 Планшет',
                                                
                                                // Электричество
                                                'lucide-plug' => '🔌 Розетки',
                                                'lucide-plug-zap' => '⚡ Зарядка',
                                                'lucide-battery-charging' => '🔋 Батарея',
                                                'lucide-usb' => '🔌 USB',
                                                
                                                // Развлечения
                                                'lucide-tv' => '📺 ТВ',
                                                'lucide-music' => '🎵 Музыка',
                                                'lucide-video' => '📹 Видео',
                                                'lucide-headphones' => '🎧 Наушники',
                                                'lucide-radio' => '📻 Радио',
                                                'lucide-volume-2' => '🔊 Аудио',
                                                
                                                // Комфорт
                                                'lucide-air-vent' => '❄️ Кондиционер',
                                                'lucide-wind' => '💨 Вентиляция',
                                                'lucide-thermometer' => '🌡️ Климат',
                                                'lucide-lamp' => '💡 Освещение',
                                                'lucide-sun' => '☀️ Свет',
                                                'lucide-flame' => '🔥 Обогрев',
                                                
                                                // Сиденья и пространство
                                                'lucide-armchair' => '💺 Сиденья',
                                                'lucide-sofa' => '🛋️ Диван',
                                                'lucide-bed' => '🛏️ Спальное место',
                                                'lucide-luggage' => '🧳 Багаж',
                                                'lucide-backpack' => '🎒 Ручная кладь',
                                                
                                                // Еда и напитки
                                                'lucide-coffee' => '☕ Кофе',
                                                'lucide-cup-soda' => '🥤 Напитки',
                                                'lucide-utensils' => '🍴 Еда',
                                                'lucide-sandwich' => '🥪 Снеки',
                                                
                                                // Чтение и работа
                                                'lucide-book-open' => '📖 Книги',
                                                'lucide-newspaper' => '📰 Газеты',
                                                'lucide-laptop' => '💻 Ноутбук',
                                                'lucide-wifi-off' => '📵 Тихая зона',
                                                
                                                // Безопасность
                                                'lucide-shield' => '🛡️ Безопасность',
                                                'lucide-lock' => '🔒 Сейф',
                                                'lucide-life-buoy' => '🆘 Помощь',
                                            ])
                                            ->searchable()
                                            ->default('lucide-wifi')
                                            ->helperText('Все иконки с lucide.dev')
                                            ->columns(1),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull()
                                    ->collapsible(),
                            ]),
                        
                        // Точки посадки/высадки
                        Forms\Components\Tabs\Tab::make('Точки посадки')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Forms\Components\Repeater::make('pickup_points')
                                    ->label('Точки посадки')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Название')
                                            ->required(),
                                        
                                        Forms\Components\TextInput::make('address')
                                            ->label('Адрес')
                                            ->required(),
                                        
                                        Forms\Components\TimePicker::make('time')
                                            ->label('Время'),
                                        
                                        Forms\Components\Textarea::make('description')
                                            ->label('Описание/Ориентиры')
                                            ->rows(2),
                                    ])
                                    ->columns(2)
                                    ->collapsible()
                                    ->columnSpanFull(),
                                
                                Forms\Components\Repeater::make('dropoff_points')
                                    ->label('Точки высадки')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Название')
                                            ->required(),
                                        
                                        Forms\Components\TextInput::make('address')
                                            ->label('Адрес')
                                            ->required(),
                                        
                                        Forms\Components\TimePicker::make('time')
                                            ->label('Время'),
                                    ])
                                    ->columns(3)
                                    ->collapsible()
                                    ->columnSpanFull(),
                                
                                Forms\Components\Repeater::make('stops')
                                    ->label('Остановки по пути')
                                    ->schema([
                                        Forms\Components\TextInput::make('location')
                                            ->label('Место')
                                            ->required(),
                                        
                                        Forms\Components\TextInput::make('duration')
                                            ->label('Длительность')
                                            ->placeholder('15 минут'),
                                        
                                        Forms\Components\Textarea::make('description')
                                            ->label('Описание')
                                            ->rows(2),
                                    ])
                                    ->columns(3)
                                    ->collapsible()
                                    ->columnSpanFull(),
                            ]),
                        
                        // Багаж и правила
                        Forms\Components\Tabs\Tab::make('Багаж и правила')
                            ->icon('heroicon-o-briefcase')
                            ->schema([
                                Forms\Components\Section::make('Багаж')
                                    ->schema([
                                        Forms\Components\TextInput::make('luggage_allowance')
                                            ->label('Разрешенный багаж')
                                            ->placeholder('1 чемодан + 1 ручная кладь')
                                            ->columnSpanFull(),
                                        
                                        Forms\Components\Textarea::make('luggage_rules')
                                            ->label('Правила провоза багажа')
                                            ->rows(4)
                                            ->columnSpanFull(),
                                    ]),
                                
                                Forms\Components\Section::make('Правила и условия')
                                    ->schema([
                                        Forms\Components\TextInput::make('min_age')
                                            ->label('Минимальный возраст')
                                            ->numeric()
                                            ->suffix('лет'),
                                        
                                        Forms\Components\Textarea::make('requirements')
                                            ->label('Требования к участникам')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                        
                                        Forms\Components\Textarea::make('cancellation_policy')
                                            ->label('Политика отмены')
                                            ->rows(4)
                                            ->columnSpanFull(),
                                        
                                        Forms\Components\Textarea::make('terms_and_conditions')
                                            ->label('Условия участия')
                                            ->rows(4)
                                            ->columnSpanFull(),
                                    ])->columns(2),
                            ]),
                        
                        // Контакты и доп. услуги
                        Forms\Components\Tabs\Tab::make('Дополнительно')
                            ->icon('heroicon-o-plus-circle')
                            ->schema([
                                Forms\Components\Section::make('Контакты')
                                    ->schema([
                                        Forms\Components\TextInput::make('driver_name')
                                            ->label('Имя водителя'),
                                        
                                        Forms\Components\TextInput::make('driver_phone')
                                            ->label('Телефон водителя')
                                            ->tel(),
                                        
                                        Forms\Components\TextInput::make('guide_name')
                                            ->label('Имя гида/сопровождающего'),
                                        
                                        Forms\Components\TextInput::make('guide_phone')
                                            ->label('Телефон гида')
                                            ->tel(),
                                    ])->columns(2),
                                
                                Forms\Components\Section::make('Дополнительные услуги')
                                    ->schema([
                                        Forms\Components\Repeater::make('additional_services')
                                            ->schema([
                                                Forms\Components\TextInput::make('name')
                                                    ->label('Название услуги')
                                                    ->required(),
                                                
                                                Forms\Components\TextInput::make('price')
                                                    ->label('Цена')
                                                    ->numeric()
                                                    ->prefix('₽'),
                                                
                                                Forms\Components\Textarea::make('description')
                                                    ->label('Описание')
                                                    ->rows(2),
                                            ])
                                            ->columns(3)
                                            ->collapsible()
                                            ->columnSpanFull(),
                                    ]),
                                
                                Forms\Components\Section::make('Рейтинг')
                                    ->schema([
                                        Forms\Components\TextInput::make('rating')
                                            ->label('Средний рейтинг')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(5)
                                            ->step(0.1)
                                            ->disabled(),
                                        
                                        Forms\Components\TextInput::make('reviews_count')
                                            ->label('Количество отзывов')
                                            ->numeric()
                                            ->disabled(),
                                    ])->columns(2),
                                
                                Forms\Components\Section::make('SEO')
                                    ->schema([
                                        Forms\Components\Textarea::make('meta_description')
                                            ->label('Meta описание')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                    ])->collapsible(),
                            ]),
                        
                        // Способы оплаты
                        Forms\Components\Tabs\Tab::make('Оплата')
                            ->icon('heroicon-o-credit-card')
                            ->schema([
                                Forms\Components\Section::make('Доступные способы оплаты')
                                    ->description('Выберите, какие способы оплаты будут доступны пользователям для этой поездки')
                                    ->schema([
                                        Forms\Components\CheckboxList::make('available_payment_gateways')
                                            ->label('Способы оплаты')
                                            ->options(\App\Enums\PaymentGateway::options())
                                            ->descriptions([
                                                'yookassa' => 'Банковская карта, СБП',
                                                'stripe' => 'Международные карты',
                                                'paypal' => 'PayPal аккаунт',
                                                'webpay' => 'Онлайн платежи',
                                                'pay_on_arrival' => 'Оплата при встрече с водителем',
                                            ])
                                            ->columns(2)
                                            ->gridDirection('row')
                                            ->default(['pay_on_arrival'])
                                            ->required()
                                            ->helperText('Если ничего не выбрано, будет доступна только оплата по факту'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event.title')
                    ->label('Мероприятие')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('title')
                    ->label('Название')
                    ->searchable()
                    ->limit(30),
                
                Tables\Columns\TextColumn::make('city_from')
                    ->label('Откуда')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('city_to')
                    ->label('Куда')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('departure_time')
                    ->label('Отправление')
                    ->time('H:i'),
                
                Tables\Columns\TextColumn::make('price')
                    ->label('Цена')
                    ->money('RUB')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('seats_taken')
                    ->label('Мест')
                    ->badge()
                    ->formatStateUsing(fn ($state, $record) => 
                        "{$state}/{$record->seats_total}"
                    )
                    ->color(fn ($record) => 
                        $record->seats_taken >= $record->seats_total ? 'danger' : 'success'
                    ),
                
                Tables\Columns\SelectColumn::make('status')
                    ->label('Статус')
                    ->options([
                        'draft' => 'Черновик',
                        'published' => 'Опубликовано',
                        'cancelled' => 'Отменено',
                        'completed' => 'Завершено',
                    ]),
                
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Рекомендуемая')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event_id')
                    ->label('Мероприятие')
                    ->relationship('event', 'title')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'draft' => 'Черновик',
                        'published' => 'Опубликовано',
                        'cancelled' => 'Отменено',
                        'completed' => 'Завершено',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Рекомендуемые'),
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
            ->defaultSort('departure_time', 'asc');
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
            'index' => Pages\ListTrips::route('/'),
            'create' => Pages\CreateTrip::route('/create'),
            'edit' => Pages\EditTrip::route('/{record}/edit'),
        ];
    }
}
