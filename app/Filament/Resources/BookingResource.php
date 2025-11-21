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

                Forms\Components\Section::make('Статус бронирования')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Статус бронирования')
                            ->options([
                                'pending' => 'Ожидает подтверждения',
                                'confirmed' => 'Подтверждено',
                                'cancelled' => 'Отменено',
                                'refund_requested' => 'Запрошен возврат',
                                'refunded' => 'Возвращено',
                            ])
                            ->default('pending')
                            ->required()
                            ->native(false)
                            ->helperText('Измените статус для подтверждения или отмены бронирования'),

                        Forms\Components\Textarea::make('cancellation_reason')
                            ->label('Причина отмены')
                            ->rows(3)
                            ->visible(fn ($get) => $get('status') === 'cancelled')
                            ->maxLength(65535),
                    ])->columns(1),
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
                    ->formatStateUsing(fn ($state) => $state?->label())
                    ->color(fn ($state) => match ($state) {
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
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'failed' => 'danger',
                        'cancelled' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Ожидает',
                        'paid' => 'Оплачено',
                        'failed' => 'Ошибка',
                        'cancelled' => 'Отменено',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Статус бронирования')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'confirmed' => 'success',
                        'pending' => 'warning',
                        'cancelled' => 'danger',
                        'refund_requested' => 'info',
                        'refunded' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Ожидает подтверждения',
                        'confirmed' => 'Подтверждено',
                        'cancelled' => 'Отменено',
                        'refund_requested' => 'Запрошен возврат',
                        'refunded' => 'Возвращено',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус бронирования')
                    ->options([
                        'pending' => 'Ожидает подтверждения',
                        'confirmed' => 'Подтверждено',
                        'cancelled' => 'Отменено',
                        'refund_requested' => 'Запрошен возврат',
                        'refunded' => 'Возвращено',
                    ]),

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
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('confirm')
                        ->label('Подтвердить')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Booking $record) {
                            app(\App\Services\BookingService::class)->confirm($record->id);
                        })
                        ->visible(fn (Booking $record) => $record->status === 'pending'),

                    Tables\Actions\Action::make('cancel')
                        ->label('Отменить')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->form([
                            Forms\Components\Textarea::make('cancellation_reason')
                                ->label('Причина отмены')
                                ->required(),
                        ])
                        ->action(function (Booking $record, array $data) {
                            app(\App\Services\BookingService::class)->cancel($record->id, $data['cancellation_reason']);
                        })
                        ->visible(fn (Booking $record) => $record->status !== 'cancelled' && $record->status !== 'refunded'),

                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
                    ->label('Действия')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray')
                    ->button(),
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
