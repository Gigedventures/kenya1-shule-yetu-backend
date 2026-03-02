<?php

namespace App\Modules\ShuleYetu\Students\Filament\Resources\ShuleStudentResource\RelationManagers;

use App\Modules\ShuleYetu\Models\ShuleGuardian;
use App\Modules\ShuleYetu\Models\ShuleStudentGuardian;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class GuardiansRelationManager extends RelationManager
{
    protected static string $relationship = 'guardians';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('first_name')->required()->maxLength(255),
            Forms\Components\TextInput::make('last_name')->required()->maxLength(255),
            Forms\Components\TextInput::make('phone')->maxLength(255),
            Forms\Components\TextInput::make('email')->email()->maxLength(255),
            Forms\Components\TextInput::make('id_number')->maxLength(255),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name'),
                Tables\Columns\TextColumn::make('last_name'),
                Tables\Columns\TextColumn::make('phone'),
                Tables\Columns\TextColumn::make('pivot.relationship'),
                Tables\Columns\IconColumn::make('pivot.is_primary')->boolean(),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->recordSelectOptionsQuery(fn ($query) => $query->orderBy('first_name'))
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\Select::make('relationship')
                            ->required()
                            ->default('guardian')
                            ->options([
                                'father' => 'Father',
                                'mother' => 'Mother',
                                'guardian' => 'Guardian',
                                'sponsor' => 'Sponsor',
                                'other' => 'Other',
                            ]),
                        Forms\Components\Toggle::make('is_primary')->default(false),
                    ])
                    ->using(function (RelationManager $livewire, array $data): void {
                        ShuleStudentGuardian::query()->create([
                            'student_id' => $livewire->getOwnerRecord()->getKey(),
                            'guardian_id' => $data['recordId'],
                            'relationship' => $data['relationship'],
                            'is_primary' => (bool) ($data['is_primary'] ?? false),
                        ]);
                    }),
                Tables\Actions\CreateAction::make()
                    ->using(function (array $data, RelationManager $livewire): ShuleGuardian {
                        $guardian = ShuleGuardian::query()->create($data);

                        ShuleStudentGuardian::query()->create([
                            'student_id' => $livewire->getOwnerRecord()->getKey(),
                            'guardian_id' => $guardian->getKey(),
                            'relationship' => 'guardian',
                            'is_primary' => false,
                        ]);

                        return $guardian;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
            ]);
    }
}

