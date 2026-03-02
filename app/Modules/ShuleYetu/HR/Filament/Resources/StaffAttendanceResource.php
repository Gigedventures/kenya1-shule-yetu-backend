<?php

namespace App\Modules\ShuleYetu\HR\Filament\Resources;

use App\Modules\ShuleYetu\HR\Filament\Resources\StaffAttendanceResource\Pages\CreateStaffAttendance;
use App\Modules\ShuleYetu\HR\Filament\Resources\StaffAttendanceResource\Pages\EditStaffAttendance;
use App\Modules\ShuleYetu\HR\Filament\Resources\StaffAttendanceResource\Pages\ListStaffAttendance;
use App\Modules\ShuleYetu\Models\ShuleDepartment;
use App\Modules\ShuleYetu\Models\ShuleStaff;
use App\Modules\ShuleYetu\Models\ShuleStaffAttendance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StaffAttendanceResource extends Resource
{
    protected static ?string $model = ShuleStaffAttendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Shule HR';

    protected static ?string $navigationLabel = 'Staff Attendance';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('staff_id')
                ->label('Staff')
                ->options(ShuleStaff::query()
                    ->orderBy('first_name')
                    ->get()
                    ->mapWithKeys(fn ($staff) => [$staff->id => trim("{$staff->first_name} {$staff->last_name}")]))
                ->searchable()
                ->required(),
            Forms\Components\DatePicker::make('attendance_date')->required(),
            Forms\Components\Select::make('status')
                ->required()
                ->default('present')
                ->options([
                    'present' => 'Present',
                    'absent' => 'Absent',
                    'late' => 'Late',
                    'excused' => 'Excused',
                ]),
            Forms\Components\TextInput::make('remarks')->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('staff_name')
                    ->label('Staff')
                    ->getStateUsing(fn ($record) => trim(($record->staff?->first_name ?? '') . ' ' . ($record->staff?->last_name ?? ''))),
                Tables\Columns\TextColumn::make('attendance_date')->date(),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('remarks'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'present' => 'Present',
                        'absent' => 'Absent',
                        'late' => 'Late',
                        'excused' => 'Excused',
                    ]),
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
            'index' => ListStaffAttendance::route('/'),
            'create' => CreateStaffAttendance::route('/create'),
            'edit' => EditStaffAttendance::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('staff_attendance.manage') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission('staff_attendance.manage') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermission('staff_attendance.manage') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasPermission('staff_attendance.manage') ?? false;
    }
}
