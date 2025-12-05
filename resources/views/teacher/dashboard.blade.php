<!DOCTYPE html>
<html>
<head>
    <title>Teacher Dashboard</title>
</head>
<body>

<h1>Teacher Dashboard</h1>

<p><strong>Welcome:</strong> {{ session('firebase_user.name') }}</p>
<p><strong>Email:</strong> {{ session('firebase_user.email') }}</p>
<p><strong>Role:</strong> Teacher</p>

<hr>

<h2>Lesson Management</h2>
<ul>
    <li><a href="{{ route('lessons.add') }}">Add New Lesson</a></li>
</ul>

<hr>

<a href="/logout">Logout</a>

</body>
</html>
