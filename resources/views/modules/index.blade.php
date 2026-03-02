<!DOCTYPE html>
<html>
<head>
    <title>{{ $module->name }}</title>
    <style>
        body {
            font-family: Arial;
            padding: 40px;
            background: #f4f6f9;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>{{ $module->name }}</h2>
    <p>{{ $module->description }}</p>

    <hr>

    <p>This module is ACTIVE.</p>
    <p>Next we connect its real functionality here.</p>
</div>

</body>
</html>
