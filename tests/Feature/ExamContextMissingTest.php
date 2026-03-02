<?php

namespace Tests\Feature;

use App\Modules\ShuleYetu\Models\ShuleExamType;
use App\Modules\ShuleYetu\Models\ShuleSchool;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class ExamContextMissingTest extends TestCase
{
    use RefreshDatabase;

    public function test_exam_queries_fail_closed_without_context(): void
    {
        $school = ShuleSchool::query()->create([
            'name' => 'School Z',
            'code' => 'EX-Z-' . Str::upper(Str::random(5)),
            'status' => 'active',
        ]);

        $context = app(SchoolContext::class);
        $context->setId($school->id);

        ShuleExamType::query()->create([
            'name' => 'Midterm',
            'weight' => 100,
            'is_active' => true,
        ]);

        $context->clear();

        $this->assertSame(0, ShuleExamType::query()->count());
    }

    public function test_exam_create_requires_context(): void
    {
        $this->expectException(RuntimeException::class);

        ShuleExamType::query()->create([
            'name' => 'CAT',
            'weight' => 100,
            'is_active' => true,
        ]);
    }
}
