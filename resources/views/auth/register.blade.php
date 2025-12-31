<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyCampus - Register</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Poppins", sans-serif;
            background: linear-gradient(135deg, #ffd1dc 0%, #ffb6c1 50%, #ffc0cb 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .register-wrapper {
            width: 100%;
            max-width: 500px;
        }

        .register-container {
            background: white;
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }

        .register-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #ffb6c1 0%, #ff69b4 100%);
        }

        .logo-section {
            text-align: center;
            margin-bottom: 35px;
        }

        .logo-section h1 {
            color: #ff69b4;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .logo-section p {
            color: #6c757d;
            font-size: 14px;
            font-weight: 400;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            font-family: "Poppins", sans-serif;
            background: white;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #ff69b4;
            box-shadow: 0 0 0 4px rgba(255, 105, 180, 0.1);
        }

        .form-group input::placeholder {
            color: #adb5bd;
        }

        .form-group select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 18px center;
            padding-right: 45px;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #ffb6c1 0%, #ff69b4 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255, 105, 180, 0.4);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .login-link {
            text-align: center;
            margin-top: 25px;
            font-size: 14px;
            color: #6c757d;
        }

        .login-link a {
            color: #ff69b4;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
            color: #ff1493;
            text-decoration: underline;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-danger {
            background-color: #fee;
            color: #c33;
            border-left: 4px solid #c33;
        }

        .alert-success {
            background-color: #efe;
            color: #2a7;
            border-left: 4px solid #2a7;
        }

        .alert ul {
            margin: 0;
            padding-left: 20px;
        }

        .alert ul li {
            margin: 4px 0;
        }

        /* Hide class section field by default */
        #classSection {
            display: none;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 480px) {
            .register-container {
                padding: 40px 30px;
            }

            .logo-section h1 {
                font-size: 28px;
            }
        }
    </style>
</head>

<body>

<div class="register-wrapper">
    <div class="register-container">
        <div class="logo-section">
            <h1>MyCampus</h1>
            <p>Create your account to get started</p>
        </div>

        {{-- Error Message --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Success Message --}}
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('register.post') }}">
            @csrf

            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" name="name" id="name" required placeholder="Enter your full name">
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" required placeholder="Enter your email">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required placeholder="Enter your password">
            </div>

            <div class="form-group">
                <label for="role">Select Role</label>
                <select name="role" id="role" required>
                    <option value="">-- Choose Role --</option>
                    <option value="student">Student</option>
                    <option value="teacher">Teacher</option>
                    <option value="administrator">Administrator</option>
                </select>
            </div>

            <!-- Class Section Field (Hidden by default) -->
            <div id="classSection" class="form-group">
                <label for="class_section">Class Section</label>
                <select name="class_section" id="class_section">
                    <option value="">-- Choose Class Section --</option>
                    <option value="1A">1A</option>
                    <option value="1B">1B</option>
                    <option value="2A">2A</option>
                    <option value="2B">2B</option>
                </select>
            </div>

            <button type="submit" class="btn-submit">Create Account</button>
        </form>

        <div class="login-link">
            Already have an account? 
            <a href="{{ route('login') }}">Sign in here</a>
        </div>
    </div>
</div>

<script>
    // Show/hide class section based on role selection
    document.getElementById('role').addEventListener('change', function() {
        const classSectionDiv = document.getElementById('classSection');
        const classSectionSelect = document.getElementById('class_section');
        
        if (this.value === 'student') {
            classSectionDiv.style.display = 'block';
            classSectionSelect.setAttribute('required', 'required');
        } else {
            classSectionDiv.style.display = 'none';
            classSectionSelect.removeAttribute('required');
            classSectionSelect.value = ''; // Clear selection
        }
    });
</script>

</body>
</html>