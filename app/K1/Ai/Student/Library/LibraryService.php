<?php

namespace App\K1\Ai\Student\Library;

use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;

class LibraryService
{
    public function search(string $q): array
    {
        return DB::table('k1_resources')
            ->where('title', 'like', "%{$q}%")
            ->orWhere('description', 'like', "%{$q}%")
            ->get()
            ->toArray();
    }

    public function getFavorites(string $studentId): array
    {
        return DB::table('k1_bookmarks')
            ->where('student_id', $studentId)
            ->join('k1_resources', 'k1_bookmarks.material_id', '=', 'k1_resources.id')
            ->get()
            ->toArray();
    }

    public function download(string $resourceId): array
    {
        $r = DB::table('k1_resources')->find($resourceId);
        if (!$r) throw new \RuntimeException('Resource not found');
        return ['file' => $r->file, 'size' => '1MB', 'format' => 'PDF'];
    }
}