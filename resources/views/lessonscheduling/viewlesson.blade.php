<!-- resources/views/lessonscheduling/viewlesson.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Timetable - MY CAMPUS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gradient-to-br from-purple-400 via-pink-500 to-red-500 min-h-screen">

<div class="flex">
    @include('layouts.sidebar')

    <main id="mainContent" class="flex-1 ml-72 transition-all duration-300 p-6">

        <!-- Header -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                <i class="fas fa-calendar-alt text-purple-600"></i>
                My Timetable
            </h1>
            <p class="text-gray-500 mt-1">View all your weekly lessons</p>
        </div>

        <!-- Timetable -->
        <div class="bg-white rounded-2xl shadow-lg p-6">
            
            @php
                use Carbon\Carbon;

                // Group lessons by weekday
                $days = [
                    'Monday' => [],
                    'Tuesday' => [],
                    'Wednesday' => [],
                    'Thursday' => [],
                    'Friday' => [],
                    'Saturday' => [],
                    'Sunday' => [],
                ];

                if (!empty($lessons)) {
                    foreach ($lessons as $lesson) {
                        if (isset($lesson['date'])) {
                            $day = Carbon::parse($lesson['date'])->format('l');
                            if (array_key_exists($day, $days)) {
                                $days[$day][] = $lesson;
                            }
                        }
                    }
                }
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-7 gap-4">

                @foreach($days as $day => $dayLessons)
                    <div class="bg-purple-50 rounded-xl shadow p-4">
                        <h2 class="text-lg font-semibold text-purple-700 mb-3 text-center">{{ $day }}</h2>

                        @if(!empty($dayLessons))
                            @foreach($dayLessons as $lesson)
                                <div class="bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg p-3 mb-3 shadow-md">
                                    <div class="font-bold">
                                        {{ $lesson['subject_name'] ?? 'Subject' }}
                                    </div>

                                    <div class="text-sm opacity-90">
                                        {{ $lesson['class_title'] ?? 'Class' }}
                                    </div>

                                    <div class="text-sm mt-1 flex items-center gap-2">
                                        <i class="fas fa-clock text-xs"></i>
                                        {{ $lesson['start_time'] ?? '' }} - {{ $lesson['end_time'] ?? '' }}
                                    </div>

                                    <div class="text-sm mt-1 flex items-center gap-2">
                                        <i class="fas fa-location-dot text-xs"></i>
                                        {{ $lesson['locationmeeting_link'] ?? 'Location' }}
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-gray-400 text-sm text-center">No classes</p>
                        @endif
                    </div>
                @endforeach

            </div>
        </div>

        <footer class="mt-8 text-center text-white opacity-80">
            © 2025 MY CAMPUS. All rights reserved.
        </footer>

    </main>
</div>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const main = document.getElementById('mainContent');
        const text = document.querySelectorAll('.sidebar-text');

        if (sidebar.classList.contains('w-72')) {
            sidebar.classList.replace('w-72', 'w-20');
            main.classList.replace('ml-72', 'ml-20');
            text.forEach(t => t.classList.add('hidden'));
        } else {
            sidebar.classList.replace('w-20', 'w-72');
            main.classList.replace('ml-20', 'ml-72');
            text.forEach(t => t.classList.remove('hidden'));
        }
    }
</script>

</body>
</html>
