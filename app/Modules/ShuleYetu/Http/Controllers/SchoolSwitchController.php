<?php

namespace App\Modules\ShuleYetu\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SchoolSwitchController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user, 403);

        $schools = DB::table('shule_school_user as ssu')
            ->join('shule_schools as ss', 'ss.id', '=', 'ssu.school_id')
            ->where('ssu.user_id', $user->getAuthIdentifier())
            ->where('ssu.status', 'active')
            ->where('ss.status', 'active')
            ->orderBy('ss.name')
            ->select(['ss.id', 'ss.name', 'ss.code'])
            ->get();

        return view('shule-yetu.schools.select', [
            'schools' => $schools,
            'activeSchoolId' => (string) $request->session()->get('active_school_id', ''),
        ]);
    }

    public function switch(Request $request, string $code): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        $school = DB::table('shule_school_user as ssu')
            ->join('shule_schools as ss', 'ss.id', '=', 'ssu.school_id')
            ->where('ssu.user_id', $user->getAuthIdentifier())
            ->where('ssu.status', 'active')
            ->where('ss.status', 'active')
            ->where('ss.code', $code)
            ->select(['ss.id'])
            ->first();

        abort_unless($school, 403, 'Invalid school selection.');

        $request->session()->put('active_school_id', (string) $school->id);
        app(SchoolContext::class)->setId((string) $school->id);

        return redirect('/admin');
    }
}

