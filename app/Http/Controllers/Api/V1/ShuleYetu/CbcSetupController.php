<?php

namespace App\Http\Controllers\Api\V1\ShuleYetu;

use App\Http\Controllers\Controller;
use App\Models\ShuleSubject;
use App\Modules\ShuleYetu\Models\School;
use App\Modules\ShuleYetu\Models\ShuleClass;
use App\Modules\ShuleYetu\Models\ShuleStream;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CbcSetupController extends Controller
{
    public function setup(Request $request)
    {
        $school = School::where('owner_user_id', $request->user()->id)->firstOrFail();

        // RESET
        ShuleStream::where('school_id', $school->id)->delete();
        ShuleClass::where('school_id', $school->id)->delete();
        ShuleSubject::where('school_id', $school->id)->delete();

        // CBC + JSS + SENIOR CLASSES
        $classes = [
            'PP1','PP2',
            'Grade 1','Grade 2','Grade 3','Grade 4','Grade 5','Grade 6',
            'Grade 7','Grade 8','Grade 9',
            'Senior 10','Senior 11','Senior 12'
        ];

        foreach ($classes as $name) {
            $class = ShuleClass::create([
                'uuid' => (string) Str::uuid(),
                'school_id' => $school->id,
                'name' => $name,
            ]);

            // Default Streams A, B
            foreach (['A','B'] as $streamName) {
                ShuleStream::create([
                    'uuid' => (string) Str::uuid(),
                    'school_id' => $school->id,
                    'class_id' => $class->id,
                    'name' => $streamName,
                ]);
            }
        }

        // SUBJECTS
        $subjects = [
            // PRIMARY
            ['Mathematics','primary'],
            ['English','primary'],
            ['Kiswahili','primary'],
            ['Environmental Activities','primary'],
            ['Creative Arts','primary'],

            // JSS
            ['Integrated Science','jss'],
            ['Social Studies','jss'],
            ['Pre-Technical Studies','jss'],
            ['Business Studies','jss'],
            ['Agriculture','jss'],
            ['Computer Science','jss'],

            // SENIOR PATHWAYS
            ['STEM','senior'],
            ['Arts & Sports','senior'],
            ['Social Sciences','senior'],
            ['Technical & Vocational','senior'],
        ];

        foreach ($subjects as [$name,$level]) {
            ShuleSubject::create([
                'school_id' => $school->id,
                'name' => $name,
                'level' => $level,
            ]);
        }

        return response()->json([
            'message' => 'CBC, JSS and Senior Secondary fully configured'
        ]);
    }
}
