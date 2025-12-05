<!DOCTYPE html>
<html>
<head>
    <title>Add Lesson</title>
</head>
<body>
    <h1>Add Lesson</h1>

    @if(session('success'))
        <p style="color:green;">{{ session('success') }}</p>
    @endif

    <form method="POST" action="{{ route('lessons.store') }}">
        @csrf
        <label>Subject Name:</label>
        <input type="text" name="subject_name" required><br><br>

        <label>Class Title:</label>
        <input type="text" name="class_title" required><br><br>

        <label>Date:</label>
        <input type="date" name="date" required><br><br>

        <label>Start Time:</label>
        <input type="time" name="start_time" required><br><br>

        <label>End Time:</label>
        <input type="time" name="end_time" required><br><br>

        <label>Location / Meeting Link:</label>
        <input type="text" name="locationmeeting_link" required><br><br>

        <label>Repeat Lesson:</label>
        <input type="checkbox" name="repeat_schedule" value="1"><br><br>

        <label>Repeat Frequency:</label>
        <select name="repeat_frequency">
            <option value="weekly">Weekly</option>
            <option value="daily">Daily</option>
        </select><br><br>

        <label>Repeat Until:</label>
        <input type="date" name="repeat_until"><br><br>

        <button type="submit">Add Lesson</button>
    </form>
</body>
</html>
