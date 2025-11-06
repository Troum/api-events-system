<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeamMemberResource\Pages;
use App\Filament\Resources\TeamMemberResource\RelationManagers;
use App\Models\TeamMember;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TeamMemberResource extends Resource
{
    protected static ?string $model = TeamMember::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    protected static ?string $navigationGroup = 'Управление командой';
    
    protected static ?string $modelLabel = 'Член команды';
    
    protected static ?string $pluralModelLabel = 'Команда';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Имя')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\Select::make('role')
                            ->label('Роль')
                            ->options([
                                'Тренер' => 'Тренер',
                                'Организатор' => 'Организатор',
                                'Гид' => 'Гид',
                                'Ассистент' => 'Ассистент',
                                'Координатор' => 'Координатор',
                            ])
                            ->required()
                            ->searchable(),
                        
                        Forms\Components\FileUpload::make('photo')
                            ->label('Фото')
                            ->image()
                            ->directory('team-members')
                            ->imageEditor()
                            ->columnSpanFull(),
                        
                        Forms\Components\RichEditor::make('bio')
                            ->label('Биография')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Контактная информация')
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('phone')
                            ->label('Телефон')
                            ->tel()
                            ->maxLength(255),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Социальные сети')
                    ->schema([
                        Forms\Components\Repeater::make('social_links')
                            ->label('Ссылки на соцсети')
                            ->schema([
                                Forms\Components\Select::make('platform')
                                    ->label('Платформа')
                                    ->options([
                                        'instagram' => 'Instagram',
                                        'telegram' => 'Telegram',
                                        'vk' => 'VK',
                                        'facebook' => 'Facebook',
                                        'whatsapp' => 'WhatsApp',
                                    ])
                                    ->required(),
                                
                                Forms\Components\TextInput::make('url')
                                    ->label('URL')
                                    ->url()
                                    ->required(),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make('Дополнительно')
                    ->schema([
                        Forms\Components\TextInput::make('rating')
                            ->label('Рейтинг')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(5)
                            ->step(0.1)
                            ->default(0),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Активен')
                            ->default(true)
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo')
                    ->label('Фото')
                    ->circular(),
                
                Tables\Columns\TextColumn::make('name')
                    ->label('Имя')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('role')
                    ->label('Роль')
                    ->badge()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('rating')
                    ->label('Рейтинг')
                    ->numeric(decimalPlaces: 1)
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('events_count')
                    ->label('Событий')
                    ->counts('events')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Роль')
                    ->options([
                        'Тренер' => 'Тренер',
                        'Организатор' => 'Организатор',
                        'Гид' => 'Гид',
                        'Ассистент' => 'Ассистент',
                        'Координатор' => 'Координатор',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активен'),
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
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\EventsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeamMembers::route('/'),
            'create' => Pages\CreateTeamMember::route('/create'),
            'edit' => Pages\EditTeamMember::route('/{record}/edit'),
        ];
    }
}
