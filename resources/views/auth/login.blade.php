<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyCampus - Login</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: "Poppins", sans-serif;
            background: url('{{ asset('images/login.jpeg') }}') no-repeat center center/cover;
            color: white;
            margin: 0;
            padding: 0;
        }

        .contact-section {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            backdrop-filter: blur(3px);
            padding-top: 80px;
        }

        .contact-container {
            background: rgba(255, 255, 255, 0.95);
            color: #333;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            width: 400px;
        }

        h2 {
            text-align: center;
            color: #1976d2; /* MyCampus Blue */
            margin-bottom: 25px;
            font-weight: 700;
        }

        label {
            font-weight: 600;
            color: #555;
            margin-top: 15px;
        }

        input {
            margin-top: 5px;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            width: 100%;
            font-size: 14px;
            transition: 0.3s;
        }

        input:focus {
            border-color: #1976d2;
        }

        .btn-submit {
            background-color: #1976d2;
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            margin-top: 20px;
            transition: background 0.3s;
        }

        .btn-submit:hover {
            background-color: #0d47a1;
        }

        .text-center a {
            color: #1976d2;
            font-weight: 600;
            text-decoration: none;
        }

        .text-center a:hover {
            text-decoration: underline;
        }

        .alert {
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</head>

<body>

<div class="contact-section">
    <div class="contact-container">
        <h2>Sign In to MyCampus</h2>

        {{-- Error Message --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Success Message --}}
        @if (session('success'))
            <div class="alert alert-success text-center">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <label for="email">Email Address</label>
            <input type="email" name="email" id="email" required placeholder="Enter your email">

            <label for="password">Password</label>
            <input type="password" name="password" id="password" required placeholder="Enter your password">

            <button type="submit" class="btn-submit">Login</button>
        </form>

        <div class="text-center mt-3">
            <p>Don't have an account? 
                <a href="{{ route('register') }}">Register here</a>
            </p>
        </div>
    </div>
</div>

</body>
</html>
