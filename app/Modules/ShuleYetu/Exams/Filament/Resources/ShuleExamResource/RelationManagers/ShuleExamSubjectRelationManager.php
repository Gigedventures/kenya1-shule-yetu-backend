<?php

namespace App\Modules\ShuleYetu\Exams\Filament\Resources\ShuleExamResource\RelationManagers;

use App\Modules\ShuleYetu\Models\ShuleExamSubject;
use App\Modules\ShuleYetu\Models\ShuleSubject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Support\Facades\DB;

class ShuleExamSubjectRelationManager extends RelationManager
{
    protected static string $relationship = 'subjects';

    public function form(Form $form): Form
    {
        $exam = $this->getOwnerRecord();
        $schoolId = app(SchoolContext::class)->id();
        $subjectIds = DB::table('shule_class_subject')
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('class_id', $exam->class_id)
            ->pluck('subject_id')
            ->all();

        return $form->schema([
            Forms\Components\Select::make('subject_id')
                ->label('Subject')
                ->options(ShuleSubject::query()
                    ->whereIn('id', $subjectIds)
                    ->orderBy('name')
                    ->pluck('name', 'id'))
                ->required(),
            Forms\Components\TextInput::make('max_marks')->numeric()->required(),
            Forms\Components\TextInput::make('pass_mark')->numeric(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subject.name')->label('Subject'),
                Tables\Columns\TextColumn::make('max_marks'),
                Tables\Columns\TextColumn::make('pass_mark'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->visible(fn () => (auth()->user()?->hasPermission('exams.manage') ?? false) && $this->getOwnerRecord()->status === 'draft'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn () => (auth()->user()?->hasPermission('exams.manage') ?? false) && $this->getOwnerRecord()->status === 'draft'),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => (auth()->user()?->hasPermission('exams.manage') ?? false) && $this->getOwnerRecord()->status === 'draft'),
            ]);
    }
}
