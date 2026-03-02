<?php

namespace App\Modules\ShuleYetu\HR\Filament\Resources;

use App\Modules\ShuleYetu\HR\Filament\Resources\TeacherAssignmentResource\Pages\CreateTeacherAssignment;
use App\Modules\ShuleYetu\HR\Filament\Resources\TeacherAssignmentResource\Pages\EditTeacherAssignment;
use App\Modules\ShuleYetu\HR\Filament\Resources\TeacherAssignmentResource\Pages\ListTeacherAssignments;
use App\Modules\ShuleYetu\Models\ShuleAcademicYear;
use App\Modules\ShuleYetu\Models\ShuleClass;
use App\Modules\ShuleYetu\Models\ShuleDepartment;
use App\Modules\ShuleYetu\Models\ShuleStaff;
use App\Modules\ShuleYetu\Models\ShuleStream;
use App\Modules\ShuleYetu\Models\ShuleSubject;
use App\Modules\ShuleYetu\Models\ShuleTeacherAssignment;
use App\Modules\ShuleYetu\Models\ShuleTerm;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TeacherAssignmentResource extends Resource
{
    protected static ?string $model = ShuleTeacherAssignment::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Shule HR';

    protected static ?string $navigationLabel = 'Teacher Assignments';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('academic_year_id')
                ->label('Academic Year')
                ->options(ShuleAcademicYear::query()->orderBy('name')->pluck('name', 'id'))
                ->required()
                ->live(),
            Forms\Components\Select::make('term_id')
                ->label('Term')
                ->options(fn (Get $get) => ShuleTerm::query()
                    ->when($get('academic_year_id'), fn ($q, $yearId) => $q->where('academic_year_id', $yearId))
                    ->orderBy('name')
                    ->pluck('name', 'id'))
                ->required(),
            Forms\Components\Select::make('staff_id')
                ->label('Teacher')
                ->options(ShuleStaff::query()
                    ->where('staff_type', 'teacher')
                    ->orderBy('first_name')
                    ->get()
                    ->mapWithKeys(fn ($staff) => [$staff->id => trim("{$staff->first_name} {$staff->last_name}")]))
                ->searchable()
                ->required(),
            Forms\Components\Select::make('class_id')
                ->label('Class')
                ->options(ShuleClass::query()->orderBy('name')->pluck('name', 'id'))
                ->required()
                ->live(),
            Forms\Components\Select::make('stream_id')
                ->label('Stream')
                ->options(fn (Get $get) => ShuleStream::query()
                    ->when($get('class_id'), fn ($q, $classId) => $q->where('class_id', $classId))
                    ->orderBy('name')
                    ->pluck('name', 'id'))
                ->searchable(),
            Forms\Components\Select::make('subject_id')
                ->label('Subject')
                ->options(fn (Get $get) => ShuleSubject::query()
                    ->when($get('class_id'), function ($q, $classId) {
                        $q->whereIn('id', function ($sub) use ($classId) {
                            $sub->select('subject_id')
                                ->from('shule_class_subject')
                                ->where('class_id', $classId);
                        });
                    })
                    ->orderBy('name')
                    ->pluck('name', 'id'))
                ->searchable(),
            Forms\Components\Toggle::make('is_class_teacher')
                ->label('Class Teacher')
                ->default(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('staff_name')
                    ->label('Teacher')
                    ->getStateUsing(fn ($record) => trim(($record->staff?->first_name ?? '') . ' ' . ($record->staff?->last_name ?? ''))),
                Tables\Columns\TextColumn::make('class.name')->label('Class'),
                Tables\Columns\TextColumn::make('stream.name')->label('Stream'),
                Tables\Columns\TextColumn::make('subject.name')->label('Subject'),
                Tables\Columns\TextColumn::make('term.name')->label('Term'),
                Tables\Columns\TextColumn::make('academicYear.name')->label('Year'),
                Tables\Columns\IconColumn::make('is_class_teacher')->boolean()->label('Class Teacher'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Staff Status')
                    ->options([
                        'active' => 'Active',
                        'suspended' => 'Suspended',
                        'left' => 'Left',
                    ])
                    ->query(fn ($query, $value) => $query->whereHas('staff', fn ($q) => $q->where('status', $value))),
                Tables\Filters\SelectFilter::make('staff_type')
                    ->label('Staff Type')
                    ->options([
                        'teacher' => 'Teacher',
                        'admin' => 'Admin',
                        'support' => 'Support',
                        'driver' => 'Driver',
                        'librarian' => 'Librarian',
                        'nurse' => 'Nurse',
                    ])
                    ->query(fn ($query, $value) => $query->whereHas('staff', fn ($q) => $q->where('staff_type', $value))),
                Tables\Filters\SelectFilter::make('department_id')
                    ->label('Department')
                    ->options(ShuleDepartment::query()->orderBy('name')->pluck('name', 'id'))
                    ->query(fn ($query, $value) => $query->whereHas('staff', fn ($q) => $q->where('department_id', $value))),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTeacherAssignments::route('/'),
            'create' => CreateTeacherAssignment::route('/create'),
            'edit' => EditTeacherAssignment::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('teacher_assignments.manage') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission('teacher_assignments.manage') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermission('teacher_assignments.manage') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasPermission('teacher_assignments.manage') ?? false;
    }
}
