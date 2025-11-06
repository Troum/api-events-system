<?php

namespace App\Filament\Resources;

use App\Enums\PaymentGatewayEnum;
use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $modelLabel = 'Бронирование';
    protected static ?string $pluralModelLabel = 'Бронирования';
    protected static ?string $navigationLabel = 'Бронирования';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Информация о бронировании')
                    ->schema([
                        Forms\Components\Select::make('trip_id')
                            ->label('Поездка')
                            ->relationship('trip', 'city_from')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('user_name')
                            ->label('Имя')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('user_phone')
                            ->label('Телефон')
                            ->tel()
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('user_email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('seats')
                            ->label('Количество мест')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1),
                    ])->columns(2),

                Forms\Components\Section::make('Оплата')
                    ->schema([
                        Forms\Components\Select::make('payment_gateway')
                            ->label('Способ оплаты')
                            ->options(PaymentGatewayEnum::options())
                            ->default(PaymentGatewayEnum::PAY_ON_ARRIVAL->value)
                            ->required()
                            ->native(false)
                            ->helperText('Выберите способ оплаты для этого бронирования'),

                        Forms\Components\Select::make('payment_status')
                            ->label('Статус оплаты')
                            ->options([
                                'pending' => 'Ожидает оплаты',
                                'paid' => 'Оплачено',
                                'failed' => 'Ошибка',
                                'cancelled' => 'Отменено',
                            ])
                            ->default('pending')
                            ->required()
                            ->native(false),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('trip.city_from')
                    ->label('Откуда')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user_name')
                    ->label('Имя')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user_email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('user_phone')
                    ->label('Телефон')
                    ->searchable(),

                Tables\Columns\TextColumn::make('seats')
                    ->label('Мест')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('payment_gateway')
                    ->label('Способ оплаты')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state?->label())
                    ->color(fn($state) => match ($state) {
                        PaymentGatewayEnum::PAY_ON_ARRIVAL => 'gray',
                        PaymentGatewayEnum::YOOKASSA => 'info',
                        PaymentGatewayEnum::STRIPE => 'purple',
                        PaymentGatewayEnum::PAYPAL => 'blue',
                        PaymentGatewayEnum::WEBPAY => 'indigo',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Статус оплаты')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'failed' => 'danger',
                        'cancelled' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Ожидает',
                        'paid' => 'Оплачено',
                        'failed' => 'Ошибка',
                        'cancelled' => 'Отменено',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Статус оплаты')
                    ->options([
                        'pending' => 'Ожидает оплаты',
                        'paid' => 'Оплачено',
                        'failed' => 'Ошибка',
                        'cancelled' => 'Отменено',
                    ]),

                Tables\Filters\SelectFilter::make('payment_gateway')
                    ->label('Способ оплаты')
                    ->options(PaymentGatewayEnum::options()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}
