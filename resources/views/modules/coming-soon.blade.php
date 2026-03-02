<!DOCTYPE html>
<html>
<head>
    <title>{{ $module->name }}</title>
    <style>
        body {
            font-family: Arial;
            text-align: center;
            padding: 80px;
            background: #f4f6f9;
        }
        .box {
            background: white;
            padding: 40px;
            border-radius: 12px;
            display: inline-block;
            box-shadow: 0 4px 20px rgba(0,0,0,.08);
        }
        h2 { margin-bottom: 10px; }
        p { color: #666; }
    </style>
</head>
<body>

<div class="box">
    <h2>{{ $module->name }}</h2>
    <p>{{ $module->description }}</p>

    <strong>
        {{ $module->coming_soon_message ?? 'Launching Soon on Kenya 1' }}
    </strong>
</div>

</body>
</html>
