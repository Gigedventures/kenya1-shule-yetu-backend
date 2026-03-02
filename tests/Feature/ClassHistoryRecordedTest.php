<?php

namespace Tests\Feature;

use App\Modules\ShuleYetu\Models\ShuleClass;
use App\Modules\ShuleYetu\Models\ShuleSchool;
use App\Modules\ShuleYetu\Models\ShuleStream;
use App\Modules\ShuleYetu\Models\ShuleStudent;
use App\Modules\ShuleYetu\Models\ShuleStudentClassHistory;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ClassHistoryRecordedTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_class_stream_change_creates_history_row(): void
    {
        $school = ShuleSchool::query()->create([
            'name' => 'History School',
            'code' => 'HIS-' . Str::upper(Str::random(5)),
            'status' => 'active',
        ]);

        app(SchoolContext::class)->setId($school->id);

        $classOne = ShuleClass::query()->create([
            'name' => 'Grade 1',
            'level' => 1,
        ]);
        $classTwo = ShuleClass::query()->create([
            'name' => 'Grade 2',
            'level' => 2,
        ]);

        $streamOne = ShuleStream::query()->create([
            'class_id' => $classOne->id,
            'name' => 'East',
            'capacity' => 35,
        ]);
        $streamTwo = ShuleStream::query()->create([
            'class_id' => $classTwo->id,
            'name' => 'West',
            'capacity' => 35,
        ]);

        $student = ShuleStudent::query()->create([
            'admission_no' => 'ADM-300',
            'first_name' => 'History',
            'last_name' => 'Student',
            'status' => 'active',
            'current_class_id' => $classOne->id,
            'current_stream_id' => $streamOne->id,
        ]);

        $student->update([
            'current_class_id' => $classTwo->id,
            'current_stream_id' => $streamTwo->id,
        ]);

        $history = ShuleStudentClassHistory::query()->where('student_id', $student->id)->latest('changed_at')->first();

        $this->assertNotNull($history);
        $this->assertSame($classOne->id, $history->from_class_id);
        $this->assertSame($streamOne->id, $history->from_stream_id);
        $this->assertSame($classTwo->id, $history->to_class_id);
        $this->assertSame($streamTwo->id, $history->to_stream_id);
    }
}

