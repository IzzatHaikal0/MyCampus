<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
</head>
<body>

<h1>Administrator Dashboard</h1>

<p><strong>Welcome:</strong> {{ session('firebase_user.name') }}</p>
<p><strong>Email:</strong> {{ session('firebase_user.email') }}</p>
<p><strong>Role:</strong> Administrator</p>

<hr>

<h2>Admin Controls</h2>
<ul>
    <li><a href="/manage/users">Manage Users (coming soon)</a></li>
    <li><a href="/reports">Reports (coming soon)</a></li>
</ul>

<hr>

<a href="/logout">Logout</a>

</body>
</html>
