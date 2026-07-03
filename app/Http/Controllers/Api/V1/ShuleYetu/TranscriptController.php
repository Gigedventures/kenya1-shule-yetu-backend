<?php

namespace App\Http\Controllers\Api\V1\ShuleYetu;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ShuleYetu\Transcripts\TranscriptResource;
use App\Modules\ShuleYetu\Transcripts\Services\TranscriptService;
use Illuminate\Http\JsonResponse;

class TranscriptController extends Controller
{
    public function studentTranscript(string $student, TranscriptService $service): TranscriptResource
    {
        $this->authorizePermission('transcripts.view');

        $transcript = $service->buildTranscript($student);

        return new TranscriptResource($transcript);
    }

    private function authorizePermission(string $permission): void
    {
        abort_unless(auth()->user()?->hasPermission($permission), 403);
    }
}