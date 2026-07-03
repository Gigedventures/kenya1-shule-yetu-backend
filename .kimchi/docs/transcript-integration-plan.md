# Transcript Integration — Recovery & Completion Plan

> **Goal:** Complete the transcript integration that was interrupted by a power outage. Add dedicated transcript endpoints, services, resources, and Flutter models.

**Architecture:** The existing `ExamService::buildStudentTermReport()` provides term-level data. A new `TranscriptService` will build an **academic transcript** — a comprehensive record across all completed terms for a student. This is layered on top of the existing exams infrastructure.

## RECOVERY AUDIT

### Already completed:
- ✅ `ExamResultController` — functional
- ✅ `ExamService` — `buildStudentTermReport()` + `calculateTermResults()` 
- ✅ `StudentTermReportResource` — API resource exists
- ✅ `TermResultResource` — API resource exists
- ✅ Routes: `/students/{student}/term-results` + `/terms/{term}/calculate-results`
- ✅ Flutter `student.dart` model with `gpa`, `course`, `semester` fields

### Needs to be created:
- ❌ `TranscriptController` — new dedicated controller
- ❌ `TranscriptService` — new dedicated service
- ❌ `TranscriptResource` — new API resource
- ❌ `transcript_models.dart` — Flutter model
- ❌ `transcript_service.dart` — Flutter service
- ❌ Transcript route in `routes/api.php`
- ❌ Transcript UI widget connected to `_ResultsTab`
- ❌ Remove `// TODO(backend): route to transcript download endpoint.`

## Implementation

### Chunk 1: Backend — TranscriptController + TranscriptService (simple)

**Files:**
- Create: `app/Http/Controllers/Api/V1/ShuleYetu/TranscriptController.php`
- Create: `app/Modules/ShuleYetu/Transcripts/Services/TranscriptService.php`
- Create: `app/Http/Resources/Api/V1/ShuleYetu/Transcripts/TranscriptResource.php`
- Modify: `routes/api.php` (add transcript routes block)

**Pattern to follow:** Same as `ExamResultController` + existing `StudentTermReportResource` pattern.

```php
// TranscriptController
namespace App\Http\Controllers\Api\V1\ShuleYetu;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ShuleYetu\Transcripts\TranscriptResource;
use App\Modules\ShuleYetu\Transcripts\Services\TranscriptService;

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
```

```php
// TranscriptService
namespace App\Modules\ShuleYetu\Transcripts\Services;

use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use App\Modules\ShuleYetu\Models\ShuleTermResult;
use App\Modules\ShuleYetu\Models\ShuleStudent;

class TranscriptService
{
    public function buildTranscript(string $studentId): array
    {
        $schoolId = app(SchoolContext::class)->requireId();
        $student = ShuleStudent::findOrFail($studentId);

        // Get all completed term results for this student
        $termResults = ShuleTermResult::query()
            ->where('student_id', $studentId)
            ->with(['term'])
            ->orderBy('term_id')
            ->get();

        return [
            'student_id' => $studentId,
            'student_name' => $student->first_name . ' ' . $student->last_name,
            'admission_no' => $student->admission_no,
            'terms' => $termResults->map(fn($r) => [
                'term_id' => $r->term_id,
                'total_percentage' => $r->total_percentage,
                'average' => $r->average,
                'overall_grade' => $r->overall_grade,
                'rank' => $r->rank,
            ]),
            'cumulative_average' => $termResults->avg('average'),
        ];
    }
}
```

```php
// TranscriptResource
namespace App\Http\Resources\Api\V1\ShuleYetu\Transcripts;

use Illuminate\Http\Resources\Json\JsonResource;

class TranscriptResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'student_id' => $this['student_id'],
            'student_name' => $this['student_name'],
            'admission_no' => $this['admission_no'],
            'terms' => $this['terms'],
            'cumulative_average' => $this['cumulative_average'],
        ];
    }
}
```

**Routes to add in `routes/api.php`** (inside the exams section):

```php
// Inside the existing exams prefix group:
Route::get('/students/{student}/transcript', [
    \App\Http\Controllers\Api\V1\ShuleYetu\TranscriptController::class,
    'studentTranscript'
]);
```

### Chunk 2: Flutter — transcript_models.dart + transcript_service.dart (simple)

**Files:**
- Create: `flutter_ui/lib/models/transcript_models.dart`
- Create: `flutter_ui/lib/services/transcript_service.dart`
- Modify: `flutter_ui/lib/screens/student/tertiary/tertiary_student_dashboard_screen.dart` (connect View Transcript button)

```dart
// transcript_models.dart
class AcademicTranscript {
  final String studentId;
  final String studentName;
  final String admissionNo;
  final List<TranscriptTerm> terms;
  final double cumulativeAverage;

  factory AcademicTranscript.fromJson(Map<String, dynamic> json) => ...
}

class TranscriptTerm {
  ...
}
```

```dart
// transcript_service.dart
class TranscriptService {
  Future<AcademicTranscript> getStudentTranscript(String studentId) async {
    ...
  }
}
```

**Connect in `tertiary_student_dashboard_screen.dart`:**
- Replace `// TODO(backend): route to transcript download endpoint.` with real API call
- Show transcript data in a dialog or navigate to a new screen

### Chunk 3: Verification (simple)

- Run `php artisan route:list` — verify transcript route registered
- Run `flutter analyze` — verify no errors
- Run `composer dump-autoload` — verify autoloading
- Check `routes/api.php` — no syntax errors

## Verification

After all 3 chunks:
1. ✅ `routes/api.php` has transcript route
2. ✅ PHP controllers compile (no LSP errors)
3. ✅ Flutter `transcript_models.dart` + `transcript_service.dart` exist
4. ✅ No transcript `// TODO` comments remain
5. ✅ `flutter analyze` passes