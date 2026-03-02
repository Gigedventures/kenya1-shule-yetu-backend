<?php

namespace Tests\Feature;

use App\Modules\ShuleYetu\Models\ShuleClass;
use App\Modules\ShuleYetu\Models\ShuleSchool;
use App\Modules\ShuleYetu\Models\ShuleStream;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class CrossSchoolRelationTest extends TestCase
{
    use RefreshDatabase;

    public function test_stream_cannot_reference_class_from_other_school(): void
    {
        $schoolA = ShuleSchool::query()->create([
            'name' => 'School A',
            'code' => 'REL-A-' . Str::upper(Str::random(5)),
            'status' => 'active',
        ]);
        $schoolB = ShuleSchool::query()->create([
            'name' => 'School B',
            'code' => 'REL-B-' . Str::upper(Str::random(5)),
            'status' => 'active',
        ]);

        $context = app(SchoolContext::class);
        $context->setId($schoolA->id);

        $classA = ShuleClass::query()->create([
            'name' => 'Grade 1',
            'level' => 1,
        ]);

        $context->setId($schoolB->id);

        $this->expectException(RuntimeException::class);

        ShuleStream::query()->create([
            'class_id' => $classA->id,
            'name' => 'East',
            'capacity' => 40,
        ]);
    }
}

