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
                My Monthly Timetable
            </h1>
            <p class="text-gray-500 mt-1">View your classes for the month</p>
        </div>

        @if(isset($error))
            <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-4">
                {{ $error }}
            </div>
        @endif

        @php
            use Carbon\Carbon;

            $currentMonth = request()->get('month') 
                            ? Carbon::parse(request()->get('month')) 
                            : Carbon::now();
            $startOfMonth = $currentMonth->copy()->startOfMonth();
            $endOfMonth = $currentMonth->copy()->endOfMonth();
            $daysInMonth = $startOfMonth->daysInMonth;

            // Map lessons by date
            $lessonsByDate = [];
            if(!empty($lessons)) {
                foreach($lessons as $lesson) {
                    $lessonDate = isset($lesson['date']) ? Carbon::parse($lesson['date'])->format('Y-m-d') : null;
                    if($lessonDate) {
                        $lesson['class_section'] = $lesson['class_section'] ?? $lesson['class_title'] ?? 'Unknown';
                        $lessonsByDate[$lessonDate][] = $lesson; // append multiple lessons
                    }
                }
            }

            // Determine starting day of week (0=Sunday)
            $firstDayOfWeek = $startOfMonth->dayOfWeek;
            $weekdays = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
        @endphp

        <!-- Month Navigation -->
        <div class="flex justify-between items-center mb-4">
            <a href="{{ route('student.timetable', ['month' => $currentMonth->copy()->subMonth()->format('Y-m-d')]) }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">&lt; Previous</a>
            <h2 class="text-xl font-semibold text-gray-800">{{ $currentMonth->format('F Y') }}</h2>
            <a href="{{ route('student.timetable', ['month' => $currentMonth->copy()->addMonth()->format('Y-m-d')]) }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">Next &gt;</a>
        </div>

        <!-- Calendar Grid -->
        <div class="grid grid-cols-7 gap-2 text-sm">
            <!-- Weekday Headers -->
            @foreach($weekdays as $day)
                <div class="text-center font-bold text-gray-700">{{ $day }}</div>
            @endforeach

            <!-- Empty cells before first day -->
            @for($i=0; $i<$firstDayOfWeek; $i++)
                <div></div>
            @endfor

            <!-- Days of the Month -->
            @for($day=1; $day<=$daysInMonth; $day++)
                @php
                    $date = $currentMonth->copy()->startOfMonth()->addDays($day-1)->format('Y-m-d');
                    $dayLessons = $lessonsByDate[$date] ?? [];
                @endphp

                <div class="border rounded-lg p-2 h-40 flex flex-col">
                    <div class="font-bold text-gray-700 mb-1">{{ $day }}</div>

                    @if(!empty($dayLessons))
                        <div class="flex-1 overflow-auto">
                            @foreach($dayLessons as $lesson)
                                <div class="bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-md p-1 mb-1 text-xs shadow-md">
                                    <div class="font-bold">{{ $lesson['subject_name'] ?? 'Subject' }}</div>
                                    <div>{{ $lesson['class_section'] }}</div>
                                    <div>{{ $lesson['start_time'] ?? '' }} - {{ $lesson['end_time'] ?? '' }}</div>
                                    <div class="mt-1 text-white/80 text-xs flex items-center gap-1">
                                        <i class="fas fa-location-dot text-xs"></i>
                                        {{ $lesson['locationmeeting_link'] ?? 'Location' }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-gray-400 text-xs mt-auto text-center">No classes</div>
                    @endif
                </div>
            @endfor
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
