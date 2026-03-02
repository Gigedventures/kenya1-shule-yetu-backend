<?php

namespace App\Http\Middleware;

use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class ShuleTenancyMiddleware
{
    public function __construct(private readonly SchoolContext $schoolContext)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $this->schoolContext->clear();

        $user = $request->user();
        if (!$user) {
            abort(403, 'No active school context.');
        }

        $isApiRequest = $request->is('api/*') || $request->expectsJson();
        $activeSchool = $isApiRequest
            ? $this->resolveApiSchool($request, $user->getAuthIdentifier())
            : $this->resolveWebSchool($request, $user->getAuthIdentifier());

        $this->schoolContext->setId((string) $activeSchool->id);

        if ($request->hasSession()) {
            $request->session()->put('active_school_id', (string) $activeSchool->id);
        }

        return $next($request);
    }

    private function resolveApiSchool(Request $request, int|string $userId): object
    {
        $requestedSchoolCode = $request->header('X-School-Code');
        if (empty($requestedSchoolCode)) {
            abort(403, 'Missing X-School-Code header.');
        }

        $memberships = $this->membershipQuery($userId)
            ->where('ss.code', $requestedSchoolCode)
            ->get();

        if ($memberships->count() !== 1) {
            abort(403, 'Invalid school context.');
        }

        return $memberships->first();
    }

    private function resolveWebSchool(Request $request, int|string $userId): object
    {
        $requestedSchoolCode = $request->header('X-School-Code');
        $activeSchoolId = $request->session()->get('active_school_id');

        if (!empty($requestedSchoolCode)) {
            $memberships = $this->membershipQuery($userId)
                ->where('ss.code', $requestedSchoolCode)
                ->get();

            if ($memberships->count() !== 1) {
                abort(403, 'Invalid school context.');
            }

            return $memberships->first();
        }

        if (!empty($activeSchoolId)) {
            $memberships = $this->membershipQuery($userId)
                ->where('ss.id', $activeSchoolId)
                ->get();

            if ($memberships->count() !== 1) {
                abort(403, 'Invalid school context.');
            }

            return $memberships->first();
        }

        $memberships = $this->membershipQuery($userId)->get();

        if ($memberships->count() === 1) {
            return $memberships->first();
        }

        $this->redirectToSchoolSelection();
    }

    private function membershipQuery(int|string $userId)
    {
        return DB::table('shule_school_user as ssu')
            ->join('shule_schools as ss', 'ss.id', '=', 'ssu.school_id')
            ->where('ssu.user_id', $userId)
            ->where('ssu.status', 'active')
            ->where('ss.status', 'active')
            ->select(['ss.id', 'ss.code'])
            ->orderBy('ssu.joined_at');
    }

    private function redirectToSchoolSelection(): RedirectResponse
    {
        if (Route::has('shule.schools.select')) {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                redirect()->route('shule.schools.select')
            );
        }

        abort(403, 'No active school context.');
    }
}
