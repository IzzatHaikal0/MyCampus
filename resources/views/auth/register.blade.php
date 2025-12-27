<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyCampus - Register</title>

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
            width: 450px;
        }

        h2 {
            text-align: center;
            color: #1976d2;
            margin-bottom: 25px;
            font-weight: 700;
        }

        label {
            font-weight: 600;
            color: #555;
            margin-top: 15px;
        }

        input, select {
            margin-top: 5px;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            width: 100%;
            font-size: 14px;
            transition: 0.3s;
        }

        input:focus, select:focus {
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

        /* Hide class section field by default */
        #classSection {
            display: none;
        }
    </style>
</head>

<body>

<div class="contact-section">
    <div class="contact-container">
        <h2>Create a MyCampus Account</h2>

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

        <form method="POST" action="{{ route('register.post') }}">
            @csrf

            <label for="name">Full Name</label>
            <input type="text" name="name" id="name" required placeholder="Enter your full name">

            <label for="email">Email Address</label>
            <input type="email" name="email" id="email" required placeholder="Enter your email">

            <label for="password">Password</label>
            <input type="password" name="password" id="password" required placeholder="Enter your password">

            <label for="role">Select Role</label>
            <select name="role" id="role" required>
                <option value="">-- Choose Role --</option>
                <option value="student">Student</option>
                <option value="teacher">Teacher</option>
                <option value="administrator">Administrator</option>
            </select>

            <!-- Class Section Field (Hidden by default) -->
            <div id="classSection">
                <label for="class_section">Class Section</label>
                <select name="class_section" id="class_section">
                    <option value="">-- Choose Class Section --</option>
                    <option value="1A">1A</option>
                    <option value="1B">1B</option>
                    <option value="2A">2A</option>
                    <option value="2B">2B</option>
                </select>
            </div>

            <button type="submit" class="btn-submit">Register</button>
        </form>

        <div class="text-center mt-3">
            <p>Already have an account?
                <a href="{{ route('login') }}">Login here</a>
            </p>
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