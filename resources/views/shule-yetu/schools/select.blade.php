<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select School</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; background: #f7f7f7; color: #111827; }
        .card { max-width: 720px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.25rem; }
        h1 { margin-top: 0; font-size: 1.5rem; }
        .muted { color: #6b7280; margin-bottom: 1rem; }
        .school { display: flex; justify-content: space-between; align-items: center; border: 1px solid #e5e7eb; border-radius: 6px; padding: 0.75rem; margin-bottom: 0.75rem; background: #fafafa; }
        button { border: 0; border-radius: 6px; padding: 0.5rem 0.8rem; background: #b45309; color: #fff; cursor: pointer; }
        button[disabled] { background: #9ca3af; cursor: not-allowed; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Select Active School</h1>
        <p class="muted">Choose the school context for this web session.</p>

        @if ($schools->isEmpty())
            <p>No schools assigned.</p>
        @else
            @foreach ($schools as $school)
                <div class="school">
                    <div>
                        <strong>{{ $school->name }}</strong><br>
                        <small>{{ $school->code }}</small>
                    </div>
                    <form method="POST" action="{{ route('shule.schools.switch', ['code' => $school->code]) }}">
                        @csrf
                        <button type="submit" @if ($activeSchoolId === $school->id) disabled @endif>
                            @if ($activeSchoolId === $school->id) Active @else Switch @endif
                        </button>
                    </form>
                </div>
            @endforeach
        @endif
    </div>
</body>
</html>

