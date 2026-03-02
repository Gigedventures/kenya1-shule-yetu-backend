<h2>Add Student</h2>

<form method="POST" action="{{ route('students.store') }}">
    @csrf

    Admission No:<br>
    <input name="admission_number"><br><br>

    First Name:<br>
    <input name="first_name"><br><br>

    Last Name:<br>
    <input name="last_name"><br><br>

    Parent Name:<br>
    <input name="parent_name"><br><br>

    Parent Phone:<br>
    <input name="parent_phone"><br><br>

    <button type="submit">Save Student</button>
</form>
