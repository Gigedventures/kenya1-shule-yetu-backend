<?php

namespace App\Modules\ShuleYetu\Students\Filament\Resources;

use App\Modules\ShuleYetu\Models\ShuleClass;
use App\Modules\ShuleYetu\Models\ShuleStream;
use App\Modules\ShuleYetu\Models\ShuleStudent;
use App\Modules\ShuleYetu\Students\Filament\Resources\ShuleStudentResource\Pages\CreateShuleStudent;
use App\Modules\ShuleYetu\Students\Filament\Resources\ShuleStudentResource\Pages\EditShuleStudent;
use App\Modules\ShuleYetu\Students\Filament\Resources\ShuleStudentResource\Pages\ListShuleStudents;
use App\Modules\ShuleYetu\Students\Filament\Resources\ShuleStudentResource\RelationManagers\GuardiansRelationManager;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShuleStudentResource extends Resource
{
    protected static ?string $model = ShuleStudent::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Shule Students';

    protected static ?string $navigationLabel = 'Students';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('admission_no')->maxLength(255),
            Forms\Components\TextInput::make('first_name')->required()->maxLength(255),
            Forms\Components\TextInput::make('middle_name')->maxLength(255),
            Forms\Components\TextInput::make('last_name')->required()->maxLength(255),
            Forms\Components\Select::make('gender')
                ->options([
                    'male' => 'Male',
                    'female' => 'Female',
                    'other' => 'Other',
                ]),
            Forms\Components\DatePicker::make('dob'),
            Forms\Components\DatePicker::make('admission_date'),
            Forms\Components\Select::make('status')
                ->required()
                ->default('active')
                ->options([
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                    'graduated' => 'Graduated',
                    'transferred' => 'Transferred',
                    'suspended' => 'Suspended',
                ]),
            Forms\Components\Select::make('current_class_id')
                ->label('Current Class')
                ->options(ShuleClass::query()->orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->live(),
            Forms\Components\Select::make('current_stream_id')
                ->label('Current Stream')
                ->options(fn (Get $get) => ShuleStream::query()
                    ->when($get('current_class_id'), fn ($q, $classId) => $q->where('class_id', $classId))
                    ->orderBy('name')
                    ->pluck('name', 'id'))
                ->searchable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('admission_no')->searchable(),
                Tables\Columns\TextColumn::make('first_name')->searchable(),
                Tables\Columns\TextColumn::make('last_name')->searchable(),
                Tables\Columns\TextColumn::make('currentClass.name')->label('Class'),
                Tables\Columns\TextColumn::make('currentStream.name')->label('Stream'),
                Tables\Columns\TextColumn::make('status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            GuardiansRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShuleStudents::route('/'),
            'create' => CreateShuleStudent::route('/create'),
            'edit' => EditShuleStudent::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('students.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission('students.manage') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermission('students.manage') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasPermission('students.manage') ?? false;
    }
}

