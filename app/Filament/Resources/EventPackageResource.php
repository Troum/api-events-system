<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventPackageResource\Pages;
use App\Models\EventPackage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EventPackageResource extends Resource
{
    protected static ?string $model = EventPackage::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Пакеты событий';

    protected static ?string $navigationGroup = 'Управление событиями';

    protected static ?string $modelLabel = 'Пакет';

    protected static ?string $pluralModelLabel = 'Пакеты событий';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\Select::make('event_id')
                            ->label('Событие')
                            ->relationship('event', 'title')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('name')
                            ->label('Название пакета')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\RichEditor::make('description')
                            ->label('Описание')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('price')
                            ->label('Цена')
                            ->required()
                            ->numeric()
                            ->prefix('₽')
                            ->minValue(0),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Участники')
                    ->schema([
                        Forms\Components\TextInput::make('max_participants')
                            ->label('Максимум участников')
                            ->numeric()
                            ->minValue(1)
                            ->helperText('Оставьте пустым для неограниченного количества'),

                        Forms\Components\TextInput::make('current_participants')
                            ->label('Текущее количество')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Что включено')
                    ->schema([
                        Forms\Components\Repeater::make('includes')
                            ->label('Включено в пакет')
                            ->schema([
                                Forms\Components\TextInput::make('item')
                                    ->label('Пункт')
                                    ->required(),
                            ])
                            ->simple()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Что не включено')
                    ->schema([
                        Forms\Components\Repeater::make('not_includes')
                            ->label('Не включено в пакет')
                            ->schema([
                                Forms\Components\TextInput::make('item')
                                    ->label('Пункт')
                                    ->required(),
                            ])
                            ->simple()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Настройки')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Активен')
                            ->default(true)
                            ->required(),

                        Forms\Components\Toggle::make('is_featured')
                            ->label('Рекомендуемый')
                            ->default(false)
                            ->required(),

                        Forms\Components\TextInput::make('order')
                            ->label('Порядок сортировки')
                            ->numeric()
                            ->default(0)
                            ->required(),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event.title')
                    ->label('Событие')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Цена')
                    ->money('RUB')
                    ->sortable(),

                Tables\Columns\TextColumn::make('participants')
                    ->label('Участники')
                    ->getStateUsing(fn ($record) => "{$record->current_participants}".
                        ($record->max_participants ? " / {$record->max_participants}" : ' / ∞'))
                    ->badge()
                    ->color(fn ($record) => $record->max_participants &&
                        $record->current_participants >= $record->max_participants ? 'danger' : 'success'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Рекомендуемый')
                    ->boolean(),

                Tables\Columns\TextColumn::make('order')
                    ->label('Порядок')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event_id')
                    ->label('Событие')
                    ->relationship('event', 'title')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активен'),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Рекомендуемый'),
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
            ->defaultSort('order');
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
            'index' => Pages\ListEventPackages::route('/'),
            'create' => Pages\CreateEventPackage::route('/create'),
            'edit' => Pages\EditEventPackage::route('/{record}/edit'),
        ];
    }
}
