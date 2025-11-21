<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Платежи';

    protected static ?string $pluralModelLabel = 'Платежи';

    protected static ?string $navigationLabel = 'Платёж';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('booking_id')
                    ->label('Бронирование')
                    ->relationship('booking', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "Бронирование #{$record->id} - {$record->user_name} ({$record->user_email})")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->disabledOn('edit'),
                Forms\Components\TextInput::make('amount')
                    ->label('Сумма')
                    ->numeric()
                    ->required()
                    ->prefix('₽')
                    ->step(0.01),
                Forms\Components\Select::make('provider')
                    ->label('Провайдер')
                    ->options([
                        'yookassa' => 'YooKassa',
                        'stripe' => 'Stripe',
                        'paypal' => 'PayPal',
                        'webpay' => 'WebPay',
                    ])
                    ->required()
                    ->disabledOn('edit'),
                Forms\Components\Select::make('status')
                    ->label('Статус')
                    ->options([
                        'pending' => 'Ожидает оплаты',
                        'success' => 'Успешно',
                        'failed' => 'Ошибка',
                        'cancelled' => 'Отменен',
                    ])
                    ->default('pending')
                    ->required(),
                Forms\Components\TextInput::make('transaction_id')
                    ->label('ID транзакции')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('booking.id')
                    ->label('Бронирование')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('booking.user_name')
                    ->label('Клиент')
                    ->searchable(),
                Tables\Columns\TextColumn::make('booking.user_email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Сумма')
                    ->money('RUB')
                    ->sortable(),
                Tables\Columns\TextColumn::make('provider')
                    ->label('Провайдер')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'yookassa' => 'success',
                        'stripe' => 'info',
                        'paypal' => 'warning',
                        'webpay' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'yookassa' => 'YooKassa',
                        'stripe' => 'Stripe',
                        'paypal' => 'PayPal',
                        'webpay' => 'WebPay',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        'cancelled' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'success' => 'Успешно',
                        'pending' => 'Ожидает оплаты',
                        'failed' => 'Ошибка',
                        'cancelled' => 'Отменен',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('transaction_id')
                    ->label('ID транзакции')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'pending' => 'Ожидает оплаты',
                        'success' => 'Успешно',
                        'failed' => 'Ошибка',
                        'cancelled' => 'Отменен',
                    ]),
                Tables\Filters\SelectFilter::make('provider')
                    ->label('Провайдер')
                    ->options([
                        'yookassa' => 'YooKassa',
                        'stripe' => 'Stripe',
                        'paypal' => 'PayPal',
                        'webpay' => 'WebPay',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
