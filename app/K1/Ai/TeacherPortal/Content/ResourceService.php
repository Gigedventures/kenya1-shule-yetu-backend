<?php

namespace App\K1\Ai\TeacherPortal\Content;

use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Support\Facades\DB;

class ResourceService
{
    public function upload(string $file, string $subject, string $grade): array
    {
        DB::table('k1_resources')->insert([
            'school_id' => app(SchoolContext::class)->requireId(),
            'file' => $file, 'subject' => $subject, 'grade' => $grade,
            'created_at' => now(),
        ]);
        return ['uploaded' => true, 'file' => $file];
    }

    public function search(string $query): array
    {
        return DB::table('k1_resources')->where('subject', 'like', "%{$query}%")->orWhere('grade', 'like', "%{$query}%")->get()->toArray();
    }

    public function tag(string $resourceId, array $tags): array
    {
        DB::table('k1_resources')->where('id', $resourceId)->update(['tags' => json_encode($tags)]);
        return ['tagged' => true];
    }
}