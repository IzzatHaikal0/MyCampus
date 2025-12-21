<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Lesson - MY CAMPUS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-purple-400 via-pink-500 to-red-500 min-h-screen">
    <div class="flex">
        <!-- Sidebar -->
        @include('layouts.sidebar')

        <!-- Main Content -->
        <main id="mainContent" class="flex-1 ml-72 transition-all duration-300 p-6">
            <!-- Top Header -->
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-6 flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                    <i class="fas fa-plus-circle text-purple-600"></i>
                    Add Lesson
                </h1>
                <a href="{{ route('teacher.dashboard') }}" class="text-purple-600 hover:text-purple-800 font-semibold">
                    Back to Dashboard
                </a>
            </div>

            <!-- Flash Messages -->
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6 flex items-center gap-3">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6 flex items-center gap-3">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif 

            <!-- Validation Errors -->
            @if($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Add Lesson Form -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <form method="POST" action="{{ route('lessons.store') }}" class="space-y-4" id="lessonForm">
                    @csrf

                    <div>
                        <label class="block font-semibold text-gray-700">Subject Name:</label>
                        <input type="text" name="subject_name" value="{{ old('subject_name') }}" required class="w-full border border-gray-300 rounded-lg p-2 mt-1">
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700">Class Section:</label>
                       <input type="text" name="class_section" value="{{ old('class_section') }}" required class="w-full border border-gray-300 rounded-lg p-2 mt-1" placeholder="e.g. 1A, 2B">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-gray-700">Date:</label>
                            <input type="date" name="date" value="{{ old('date') }}" required class="w-full border border-gray-300 rounded-lg p-2 mt-1" id="lessonDate" min="{{ date('Y-m-d') }}">
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700">Start Time:</label>
                            <input type="time" name="start_time" value="{{ old('start_time') }}" required class="w-full border border-gray-300 rounded-lg p-2 mt-1" id="startTime">
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700">End Time:</label>
                            <input type="time" name="end_time" value="{{ old('end_time') }}" required class="w-full border border-gray-300 rounded-lg p-2 mt-1" id="endTime">
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700">Location / Meeting Link:</label>
                            <input type="text" name="locationmeeting_link" value="{{ old('locationmeeting_link') }}" required class="w-full border border-gray-300 rounded-lg p-2 mt-1">
                        </div>
                    </div>

                    <div class="flex items-center gap-4 mt-2">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="repeat_schedule" value="1" {{ old('repeat_schedule') ? 'checked' : '' }} class="h-4 w-4 text-purple-600" id="repeatLesson">
                            <label class="text-gray-700 font-semibold">Repeat Lesson</label>
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-700">Repeat Frequency:</label>
                            <select name="repeat_frequency" id="repeatFrequency" class="border border-gray-300 rounded-lg p-2 mt-1">
                                <option value="weekly" {{ old('repeat_frequency') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                <option value="daily" {{ old('repeat_frequency') == 'daily' ? 'selected' : '' }}>Daily</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-700">Repeat Until:</label>
                            <input type="date" name="repeat_until" id="repeatUntil" value="{{ old('repeat_until') }}" class="border border-gray-300 rounded-lg p-2 mt-1" min="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                    <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition">
                        <i class="fas fa-plus mr-2"></i> Add Lesson
                    </button>
                </form>
            </div>
        </main>
    </div>

<script>
    // Toggle sidebar
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const sidebarTexts = document.querySelectorAll('.sidebar-text');
        if (sidebar.classList.contains('w-72')) {
            sidebar.classList.replace('w-72', 'w-20');
            mainContent.classList.replace('ml-72', 'ml-20');
            sidebarTexts.forEach(text => text.classList.add('hidden'));
        } else {
            sidebar.classList.replace('w-20', 'w-72');
            mainContent.classList.replace('ml-20', 'ml-72');
            sidebarTexts.forEach(text => text.classList.remove('hidden'));
        }
    }

    // Show/hide repeat fields
    const repeatCheckbox = document.getElementById('repeatLesson');
    const repeatFrequency = document.getElementById('repeatFrequency').parentElement;
    const repeatUntil = document.getElementById('repeatUntil').parentElement;

    function toggleRepeatFields() {
        const show = repeatCheckbox.checked;
        repeatFrequency.style.display = show ? 'block' : 'none';
        repeatUntil.style.display = show ? 'block' : 'none';
    }

    repeatCheckbox.addEventListener('change', toggleRepeatFields);
    toggleRepeatFields();

    // Form submit
    const lessonForm = document.getElementById('lessonForm');
    lessonForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const start = document.getElementById('startTime').value;
        const end = document.getElementById('endTime').value;
        const date = document.getElementById('lessonDate').value;

        if (start >= end) {
            alert('End Time must be after Start Time.');
            return;
        }

        try {
            const response = await fetch('{{ route("lessons.check-overlap") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ date, start_time: start, end_time: end })
            });

            const data = await response.json();
            if (data.overlap) {
                alert('Another lesson is already scheduled at this time.');
                return;
            }

            lessonForm.submit();
        } catch (err) {
            console.error(err);
            alert('Error checking lesson overlap. Please try again.');
        }
    });
</script>
</body>
</html>
