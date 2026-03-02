<?php

namespace App\Modules\ShuleYetu\Exams\Filament\Resources;

use App\Modules\ShuleYetu\Exams\Filament\Resources\TermResultsResource\Pages\ListTermResults;
use App\Modules\ShuleYetu\Models\ShuleTermResult;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TermResultsResource extends Resource
{
    protected static ?string $model = ShuleTermResult::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Shule Exams';

    protected static ?string $navigationLabel = 'Term Results';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('term.name')->label('Term'),
                Tables\Columns\TextColumn::make('student.first_name')->label('First Name'),
                Tables\Columns\TextColumn::make('student.last_name')->label('Last Name'),
                Tables\Columns\TextColumn::make('total_marks'),
                Tables\Columns\TextColumn::make('total_percentage'),
                Tables\Columns\TextColumn::make('average'),
                Tables\Columns\TextColumn::make('overall_grade')->label('Grade'),
                Tables\Columns\TextColumn::make('rank'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('term_id')
                    ->label('Term')
                    ->relationship('term', 'name'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTermResults::route('/'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('exams.view') ?? false;
    }
}
