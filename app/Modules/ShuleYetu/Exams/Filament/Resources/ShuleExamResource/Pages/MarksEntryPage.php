<?php

namespace App\Modules\ShuleYetu\Exams\Filament\Resources\ShuleExamResource\Pages;

use App\Modules\ShuleYetu\Exams\Filament\Resources\ShuleExamResource;
use App\Modules\ShuleYetu\Exams\Services\ExamService;
use App\Modules\ShuleYetu\Models\ShuleExam;
use App\Modules\ShuleYetu\Models\ShuleExamScore;
use App\Modules\ShuleYetu\Models\ShuleExamSubject;
use App\Modules\ShuleYetu\Models\ShuleStudent;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;

class MarksEntryPage extends Page
{
    use InteractsWithForms;

    protected static string $resource = ShuleExamResource::class;

    protected static string $view = 'filament.resources.pages.marks-entry';

    public ShuleExam $record;

    public ?string $examSubjectId = null;

    public array $marks = [];

    public function mount(ShuleExam $record): void
    {
        $this->record = $record;
        if ($record->status !== 'published') {
            abort(403);
        }
        $this->examSubjectId = $record->subjects()->orderBy('created_at')->value('id');
        $this->loadMarks();
        $this->form->fill([
            'exam_subject_id' => $this->examSubjectId,
            'marks' => $this->marks,
        ]);
    }

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->user()?->hasPermission('exams.score') ?? false;
    }

    public function updatedExamSubjectId(): void
    {
        $this->loadMarks();
        $this->form->fill([
            'exam_subject_id' => $this->examSubjectId,
            'marks' => $this->marks,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('exam_subject_id')
                ->label('Exam Subject')
                ->options($this->getExamSubjectOptions())
                ->required()
                ->live()
                ->afterStateUpdated(function ($state): void {
                    $this->examSubjectId = $state;
                    $this->updatedExamSubjectId();
                }),
            Forms\Components\Repeater::make('marks')
                ->schema([
                    Forms\Components\Hidden::make('student_id'),
                    Forms\Components\TextInput::make('student_name')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('marks_obtained')
                        ->numeric()
                        ->required(),
                    Forms\Components\TextInput::make('remarks')->maxLength(255),
                ])
                ->disableItemCreation()
                ->disableItemDeletion()
                ->columns(4),
        ]);
    }

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('save')
                ->label('Save Marks')
                ->action(fn () => $this->saveMarks()),
        ];
    }

    private function saveMarks(): void
    {
        $data = $this->form->getState();
        $examSubjectId = $data['exam_subject_id'] ?? null;

        if (!$examSubjectId) {
            $this->addError('exam_subject_id', 'Select an exam subject.');
            return;
        }

        app(ExamService::class)->enterMarksBulk($examSubjectId, $data['marks'] ?? [], auth()->user());
    }

    private function getExamSubjectOptions(): array
    {
        return $this->record->subjects()
            ->with('subject')
            ->get()
            ->mapWithKeys(fn (ShuleExamSubject $subject) => [
                $subject->id => $subject->subject?->name ?? 'Subject',
            ])
            ->all();
    }

    private function loadMarks(): void
    {
        if (!$this->examSubjectId) {
            $this->marks = [];
            return;
        }

        $students = $this->getStudentsForExam();
        $scores = ShuleExamScore::query()
            ->where('exam_subject_id', $this->examSubjectId)
            ->whereIn('student_id', $students->pluck('id')->all())
            ->get()
            ->keyBy('student_id');

        $this->marks = $students->map(function ($student) use ($scores) {
            $score = $scores->get($student->id);
            return [
                'student_id' => $student->id,
                'student_name' => trim($student->first_name . ' ' . $student->last_name),
                'marks_obtained' => $score?->marks_obtained,
                'remarks' => $score?->remarks,
            ];
        })->values()->all();
    }

    private function getStudentsForExam(): Collection
    {
        return ShuleStudent::query()
            ->where('current_class_id', $this->record->class_id)
            ->when($this->record->stream_id, fn ($q) => $q->where('current_stream_id', $this->record->stream_id))
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name']);
    }
}
