<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
</head>
<body>

<h1>Student Dashboard</h1>

<p><strong>Welcome:</strong> {{ session('firebase_user.name') }}</p>
<p><strong>Email:</strong> {{ session('firebase_user.email') }}</p>
<p><strong>Role:</strong> Student</p>

<hr>

<h2>Your Learning</h2>
<ul>
    <li><a href="#">View Lessons (coming soon)</a></li>
</ul>

<hr>

<a href="/logout">Logout</a>

</body>
</html>
