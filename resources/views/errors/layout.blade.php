<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - {{ config('app.name') }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: @yield('background', 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)');
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-container {
            text-align: center;
            background: white;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 500px;
            margin: 2rem;
        }
        .error-code {
            font-size: 6rem;
            font-weight: bold;
            color: @yield('color', '#667eea');
            margin: 0;
            line-height: 1;
        }
        .error-message {
            font-size: 1.5rem;
            color: #333;
            margin: 1rem 0;
        }
        .error-description {
            color: #666;
            margin: 1rem 0 2rem;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: @yield('background', 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)');
            color: white;
            text-decoration: none;
            border-radius: 25px;
            transition: transform 0.3s ease;
            font-weight: 500;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="icon">@yield('icon')</div>
        <h1 class="error-code">@yield('code')</h1>
        <h2 class="error-message">@yield('message')</h2>
        <p class="error-description">
            @yield('description')
        </p>
        <a href="{{ url('/') }}" class="btn">Go Back Home</a>
    </div>
</body>
</html>
