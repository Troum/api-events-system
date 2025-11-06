<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use App\Enums\PaymentGateway;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TripsRelationManager extends RelationManager
{
    protected static string $relationship = 'trips';
    
    protected static ?string $recordTitleAttribute = 'city_from';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('city_from')
                            ->label('Откуда')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('city_to')
                            ->label('Куда')
                            ->maxLength(255),
                        
                        Forms\Components\DateTimePicker::make('departure_time')
                            ->label('Время отправления')
                            ->required(),
                        
                        Forms\Components\DateTimePicker::make('arrival_time')
                            ->label('Время прибытия'),
                        
                        Forms\Components\TextInput::make('duration')
                            ->label('Длительность')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('price')
                            ->label('Цена')
                            ->required()
                            ->numeric()
                            ->prefix('₽')
                            ->minValue(0),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Места')
                    ->schema([
                        Forms\Components\TextInput::make('seats_total')
                            ->label('Всего мест')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(50),
                        
                        Forms\Components\TextInput::make('seats_taken')
                            ->label('Занято мест')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Дополнительно')
                    ->schema([
                        Forms\Components\Select::make('transport_type')
                            ->label('Тип транспорта')
                            ->options([
                                'bus' => 'Автобус',
                                'minibus' => 'Микроавтобус',
                                'car' => 'Легковой автомобиль',
                                'train' => 'Поезд',
                                'plane' => 'Самолет',
                            ]),
                        
                        Forms\Components\Select::make('status')
                            ->label('Статус')
                            ->options([
                                'draft' => 'Черновик',
                                'published' => 'Опубликовано',
                                'cancelled' => 'Отменено',
                                'completed' => 'Завершено',
                            ])
                            ->default('draft'),
                        
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Рекомендуемая')
                            ->default(false),
                    ])
                    ->columns(3),
                
                Forms\Components\Section::make('Способы оплаты')
                    ->description('Выберите, какие способы оплаты будут доступны пользователям для этой поездки')
                    ->schema([
                        Forms\Components\CheckboxList::make('available_payment_gateways')
                            ->label('Доступные способы оплаты')
                            ->options(PaymentGateway::options())
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
                            ->helperText('Если ничего не выбрано, будет доступна только оплата по факту'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('city_from')
            ->columns([
                Tables\Columns\TextColumn::make('city_from')
                    ->label('Откуда')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('city_to')
                    ->label('Куда')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('departure_time')
                    ->label('Отправление')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('price')
                    ->label('Цена')
                    ->money('RUB')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('seats')
                    ->label('Места')
                    ->getStateUsing(fn ($record) => "{$record->seats_taken} / {$record->seats_total}")
                    ->badge()
                    ->color(fn ($record) => $record->seats_taken >= $record->seats_total ? 'danger' : 'success'),
                
                Tables\Columns\TextColumn::make('transport_type')
                    ->label('Транспорт')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'bus' => 'Автобус',
                        'minibus' => 'Микроавтобус',
                        'car' => 'Легковой',
                        'train' => 'Поезд',
                        'plane' => 'Самолет',
                        default => $state,
                    }),
                
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Рекомендуемая')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'published' => 'success',
                        'cancelled' => 'danger',
                        'completed' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Черновик',
                        'published' => 'Опубликовано',
                        'cancelled' => 'Отменено',
                        'completed' => 'Завершено',
                        default => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('transport_type')
                    ->label('Тип транспорта')
                    ->options([
                        'bus' => 'Автобус',
                        'minibus' => 'Микроавтобус',
                        'car' => 'Легковой автомобиль',
                        'train' => 'Поезд',
                        'plane' => 'Самолет',
                    ]),
                
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'draft' => 'Черновик',
                        'published' => 'Опубликовано',
                        'cancelled' => 'Отменено',
                        'completed' => 'Завершено',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
