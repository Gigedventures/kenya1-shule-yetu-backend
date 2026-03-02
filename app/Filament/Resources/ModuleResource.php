<?php

namespace App\Filament\Resources;

use App\Models\Module;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class ModuleResource extends Resource
{
    protected static ?string $model = Module::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationLabel = 'Modules';
    protected static ?string $navigationGroup = 'Kenya 1';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->required(),
            TextInput::make('slug')->disabled(),
            Toggle::make('is_active')->label('Live'),
            Textarea::make('description'),
            TextInput::make('coming_soon_message'),
            TextInput::make('display_order')->numeric(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                IconColumn::make('is_active')->boolean()->label('Live'),
                TextColumn::make('display_order')->sortable(),
            ])
            ->defaultSort('display_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\ModuleResource\Pages\ListModules::route('/'),
            'create' => \App\Filament\Resources\ModuleResource\Pages\CreateModule::route('/create'),
            'edit' => \App\Filament\Resources\ModuleResource\Pages\EditModule::route('/{record}/edit'),
        ];
    }
}
