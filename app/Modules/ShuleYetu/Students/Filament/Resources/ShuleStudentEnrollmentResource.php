<?php

namespace App\Modules\ShuleYetu\Students\Filament\Resources;

use App\Modules\ShuleYetu\Models\ShuleAcademicYear;
use App\Modules\ShuleYetu\Models\ShuleClass;
use App\Modules\ShuleYetu\Models\ShuleStream;
use App\Modules\ShuleYetu\Models\ShuleStudent;
use App\Modules\ShuleYetu\Models\ShuleStudentEnrollment;
use App\Modules\ShuleYetu\Students\Filament\Resources\ShuleStudentEnrollmentResource\Pages\CreateShuleStudentEnrollment;
use App\Modules\ShuleYetu\Students\Filament\Resources\ShuleStudentEnrollmentResource\Pages\EditShuleStudentEnrollment;
use App\Modules\ShuleYetu\Students\Filament\Resources\ShuleStudentEnrollmentResource\Pages\ListShuleStudentEnrollments;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShuleStudentEnrollmentResource extends Resource
{
    protected static ?string $model = ShuleStudentEnrollment::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Shule Students';

    protected static ?string $navigationLabel = 'Enrollments';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('student_id')
                ->options(ShuleStudent::query()->orderBy('first_name')->pluck('first_name', 'id'))
                ->searchable()
                ->required(),
            Forms\Components\Select::make('academic_year_id')
                ->options(ShuleAcademicYear::query()->orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->required(),
            Forms\Components\Select::make('class_id')
                ->options(ShuleClass::query()->orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->live(),
            Forms\Components\Select::make('stream_id')
                ->options(fn (Get $get) => ShuleStream::query()
                    ->when($get('class_id'), fn ($q, $classId) => $q->where('class_id', $classId))
                    ->orderBy('name')
                    ->pluck('name', 'id'))
                ->searchable(),
            Forms\Components\DatePicker::make('enrollment_date'),
            Forms\Components\DatePicker::make('exit_date'),
            Forms\Components\Select::make('status')
                ->required()
                ->default('enrolled')
                ->options([
                    'enrolled' => 'Enrolled',
                    'promoted' => 'Promoted',
                    'repeated' => 'Repeated',
                    'left' => 'Left',
                ]),
            Forms\Components\TextInput::make('remarks')->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.first_name')->label('Student'),
                Tables\Columns\TextColumn::make('academicYear.name')->label('Academic Year'),
                Tables\Columns\TextColumn::make('class.name')->label('Class'),
                Tables\Columns\TextColumn::make('stream.name')->label('Stream'),
                Tables\Columns\TextColumn::make('status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShuleStudentEnrollments::route('/'),
            'create' => CreateShuleStudentEnrollment::route('/create'),
            'edit' => EditShuleStudentEnrollment::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('enrollments.manage') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission('enrollments.manage') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermission('enrollments.manage') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasPermission('enrollments.manage') ?? false;
    }
}

