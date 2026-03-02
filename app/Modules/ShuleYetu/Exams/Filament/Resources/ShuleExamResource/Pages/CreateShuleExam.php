<?php

namespace App\Modules\ShuleYetu\Exams\Filament\Resources\ShuleExamResource\Pages;

use App\Modules\ShuleYetu\Exams\Filament\Resources\ShuleExamResource;
use App\Modules\ShuleYetu\Exams\Services\ExamService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateShuleExam extends CreateRecord
{
    protected static string $resource = ShuleExamResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(ExamService::class)->createExam($data);
    }
}
