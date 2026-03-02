<?php

namespace App\Modules\ShuleYetu\Filament\Resources;

use App\Modules\ShuleYetu\Filament\Resources\StudentResource\Pages;
use App\Modules\ShuleYetu\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Students';
    protected static ?string $navigationGroup = 'Shule Yetu';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('shule_class_id')
                ->relationship('shuleClass', 'name')
                ->label('Class')
                ->searchable()
                ->required(),

            Forms\Components\Select::make('shule_stream_id')
                ->relationship('stream', 'name')
                ->label('Stream')
                ->searchable(),

            Forms\Components\TextInput::make('admission_number')
                ->required()
                ->unique(ignoreRecord: true),

            Forms\Components\TextInput::make('first_name')->required(),
            Forms\Components\TextInput::make('last_name')->required(),

            Forms\Components\DatePicker::make('date_of_birth'),

            Forms\Components\Select::make('gender')->options([
                'Male' => 'Male',
                'Female' => 'Female',
            ]),

            Forms\Components\TextInput::make('guardian_name'),
            Forms\Components\TextInput::make('guardian_phone'),
            Forms\Components\Textarea::make('address')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('shuleClass.name')->label('Class'),
            Tables\Columns\TextColumn::make('stream.name')->label('Stream'),
            Tables\Columns\TextColumn::make('admission_number')->searchable(),
            Tables\Columns\TextColumn::make('first_name'),
            Tables\Columns\TextColumn::make('last_name'),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
