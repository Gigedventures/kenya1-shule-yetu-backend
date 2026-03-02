<?php

namespace App\Http\Controllers\ShuleYetu;

use App\Http\Controllers\Controller;
use App\Modules\ShuleYetu\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::latest()->get();
        return view('shule-yetu.students.index', compact('students'));
    }

    public function create()
    {
        return view('shule-yetu.students.create');
    }

    public function store(Request $request)
    {
        Student::create($request->all());

        return redirect()->route('students.index')
            ->with('success', 'Student created successfully');
    }
}
