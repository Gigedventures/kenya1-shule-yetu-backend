<?php

namespace App\Modules\ShuleYetu\Exams\Filament\Resources;

use App\Modules\ShuleYetu\Exams\Filament\Resources\ShuleExamResource\Pages\CreateShuleExam;
use App\Modules\ShuleYetu\Exams\Filament\Resources\ShuleExamResource\Pages\EditShuleExam;
use App\Modules\ShuleYetu\Exams\Filament\Resources\ShuleExamResource\Pages\ListShuleExams;
use App\Modules\ShuleYetu\Exams\Filament\Resources\ShuleExamResource\Pages\MarksEntryPage;
use App\Modules\ShuleYetu\Exams\Filament\Resources\ShuleExamResource\RelationManagers\ShuleExamSubjectRelationManager;
use App\Modules\ShuleYetu\Exams\Services\ExamService;
use App\Modules\ShuleYetu\Models\ShuleAcademicYear;
use App\Modules\ShuleYetu\Models\ShuleClass;
use App\Modules\ShuleYetu\Models\ShuleExam;
use App\Modules\ShuleYetu\Models\ShuleExamType;
use App\Modules\ShuleYetu\Models\ShuleStream;
use App\Modules\ShuleYetu\Models\ShuleTerm;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ShuleExamResource extends Resource
{
    protected static ?string $model = ShuleExam::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Shule Exams';

    protected static ?string $navigationLabel = 'Exams';

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
            Forms\Components\Select::make('exam_type_id')
                ->label('Exam Type')
                ->options(ShuleExamType::query()->orderBy('name')->pluck('name', 'id'))
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
            Forms\Components\TextInput::make('title')->required()->maxLength(255),
            Forms\Components\TextInput::make('total_marks')->numeric()->default(100)->required(),
            Forms\Components\DatePicker::make('start_date')->required(),
            Forms\Components\DatePicker::make('end_date'),
            Forms\Components\Select::make('status')
                ->options([
                    'draft' => 'Draft',
                    'published' => 'Published',
                    'closed' => 'Closed',
                ])
                ->disabled()
                ->default('draft'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('examType.name')->label('Type'),
                Tables\Columns\TextColumn::make('term.name')->label('Term'),
                Tables\Columns\TextColumn::make('class.name')->label('Class'),
                Tables\Columns\TextColumn::make('stream.name')->label('Stream'),
                Tables\Columns\TextColumn::make('start_date')->date(),
                Tables\Columns\TextColumn::make('status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('publish')
                    ->label('Publish')
                    ->visible(fn (ShuleExam $record) => $record->status === 'draft' && static::canPublish())
                    ->requiresConfirmation()
                    ->action(fn (ShuleExam $record) => app(ExamService::class)->publishExam($record->id)),
                Action::make('close')
                    ->label('Close')
                    ->visible(fn (ShuleExam $record) => $record->status === 'published' && static::canPublish())
                    ->requiresConfirmation()
                    ->action(fn (ShuleExam $record) => app(ExamService::class)->closeExam($record->id)),
                Action::make('marks')
                    ->label('Marks')
                    ->url(fn (ShuleExam $record) => static::getUrl('marks', ['record' => $record]))
                    ->visible(fn (ShuleExam $record) => $record->status === 'published' && static::canScore()),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ShuleExamSubjectRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShuleExams::route('/'),
            'create' => CreateShuleExam::route('/create'),
            'edit' => EditShuleExam::route('/{record}/edit'),
            'marks' => MarksEntryPage::route('/{record}/marks'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('exams.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission('exams.manage') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermission('exams.manage') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasPermission('exams.manage') ?? false;
    }

    private static function canPublish(): bool
    {
        return Auth::user()?->hasPermission('exams.publish') ?? false;
    }

    private static function canScore(): bool
    {
        return Auth::user()?->hasPermission('exams.score') ?? false;
    }
}
