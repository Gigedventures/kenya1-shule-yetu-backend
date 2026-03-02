<!DOCTYPE html>
<html>
<head>
    <title>Kenya 1</title>
</head>
<body style="font-family:Arial;text-align:center">

<h1>Welcome to Kenya 1</h1>

<div style="display:flex;flex-wrap:wrap;justify-content:center">

@foreach($modules as $module)

    <a href="/module/{{ $module->slug }}"
       style="border:1px solid #ddd;
              margin:10px;
              padding:20px;
              width:200px;
              text-decoration:none;
              color:black;">

        <h3>{{ $module->name }}</h3>

        @if(!$module->is_active)
            <p style="color:orange;">Coming Soon</p>
        @else
            <p style="color:green;">Live</p>
        @endif

    </a>

@endforeach

</div>

</body>
</html>
