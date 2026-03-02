<?php

namespace App\Modules\ShuleYetu\HR\Filament\Resources;

use App\Models\User;
use App\Modules\ShuleYetu\HR\Filament\Resources\StaffResource\Pages\CreateStaff;
use App\Modules\ShuleYetu\HR\Filament\Resources\StaffResource\Pages\EditStaff;
use App\Modules\ShuleYetu\HR\Filament\Resources\StaffResource\Pages\ListStaff;
use App\Modules\ShuleYetu\Models\ShuleDepartment;
use App\Modules\ShuleYetu\Models\ShuleStaff;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StaffResource extends Resource
{
    protected static ?string $model = ShuleStaff::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'Shule HR';

    protected static ?string $navigationLabel = 'Staff';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->label('Linked User')
                ->options(User::query()->orderBy('name')->pluck('name', 'id'))
                ->searchable(),
            Forms\Components\TextInput::make('staff_no')->maxLength(255),
            Forms\Components\TextInput::make('first_name')->required()->maxLength(255),
            Forms\Components\TextInput::make('last_name')->required()->maxLength(255),
            Forms\Components\TextInput::make('phone')->maxLength(255),
            Forms\Components\TextInput::make('email')->email()->maxLength(255),
            Forms\Components\Select::make('gender')
                ->options([
                    'male' => 'Male',
                    'female' => 'Female',
                    'other' => 'Other',
                ]),
            Forms\Components\Select::make('staff_type')
                ->required()
                ->default('teacher')
                ->options([
                    'teacher' => 'Teacher',
                    'admin' => 'Admin',
                    'support' => 'Support',
                    'driver' => 'Driver',
                    'librarian' => 'Librarian',
                    'nurse' => 'Nurse',
                ]),
            Forms\Components\Select::make('department_id')
                ->label('Department')
                ->options(ShuleDepartment::query()->orderBy('name')->pluck('name', 'id'))
                ->searchable(),
            Forms\Components\Select::make('status')
                ->required()
                ->default('active')
                ->options([
                    'active' => 'Active',
                    'suspended' => 'Suspended',
                    'left' => 'Left',
                ]),
            Forms\Components\DatePicker::make('joined_at'),
            Forms\Components\DatePicker::make('left_at'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('staff_no')->searchable(),
                Tables\Columns\TextColumn::make('first_name')->searchable(),
                Tables\Columns\TextColumn::make('last_name')->searchable(),
                Tables\Columns\TextColumn::make('staff_type')->label('Type'),
                Tables\Columns\TextColumn::make('department.name')->label('Department'),
                Tables\Columns\TextColumn::make('status'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'suspended' => 'Suspended',
                        'left' => 'Left',
                    ]),
                Tables\Filters\SelectFilter::make('staff_type')
                    ->options([
                        'teacher' => 'Teacher',
                        'admin' => 'Admin',
                        'support' => 'Support',
                        'driver' => 'Driver',
                        'librarian' => 'Librarian',
                        'nurse' => 'Nurse',
                    ]),
                Tables\Filters\SelectFilter::make('department_id')
                    ->label('Department')
                    ->options(ShuleDepartment::query()->orderBy('name')->pluck('name', 'id')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStaff::route('/'),
            'create' => CreateStaff::route('/create'),
            'edit' => EditStaff::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('staff.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission('staff.manage') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermission('staff.manage') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasPermission('staff.manage') ?? false;
    }
}
