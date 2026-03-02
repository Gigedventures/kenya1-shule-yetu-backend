<h2>Students</h2>

<a href="{{ route('students.create') }}">+ Add Student</a>

<table border="1" cellpadding="10">
    <tr>
        <th>Admission No</th>
        <th>Name</th>
        <th>Parent</th>
        <th>Status</th>
    </tr>

    @foreach($students as $student)
    <tr>
        <td>{{ $student->admission_number }}</td>
        <td>{{ $student->first_name }} {{ $student->last_name }}</td>
        <td>{{ $student->parent_name }}</td>
        <td>{{ $student->is_active ? 'Active' : 'Inactive' }}</td>
    </tr>
    @endforeach
</table>
