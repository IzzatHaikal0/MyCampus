<!-- resources/views/lessonscheduling/list.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Lessons - MY CAMPUS</title>
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
                    <i class="fas fa-book text-purple-600"></i>
                    My Lessons
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

            <!-- Lessons List -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-list text-purple-600"></i>
                    All Lessons
                </h2>

                @php
                    use Carbon\Carbon;
                    $teacherUid = session('firebase_user.uid');
                    $teacherLessons = [];

                    if(!empty($lessons)) {
                        foreach($lessons as $id => $lesson) {
                            if(isset($lesson['teacher_id']) && $lesson['teacher_id'] === $teacherUid) {
                                $teacherLessons[$id] = $lesson;
                            }
                        }
                    }
                @endphp

                @if(!empty($teacherLessons))
                    <div class="space-y-4">
                        @foreach($teacherLessons as $id => $lesson)
                            <div class="bg-purple-50 border-l-4 border-purple-600 rounded-lg p-4 flex justify-between items-center hover:shadow-md transition">
                                <div>
                                    <div class="font-semibold text-gray-800">{{ $lesson['subject_name'] ?? 'Untitled Subject' }}</div>
                                    <div class="text-gray-600 text-sm">{{ $lesson['class_title'] ?? 'No class title' }}</div>
                                    <div class="text-gray-500 text-sm mt-1">
                                        {{ $lesson['date'] ?? '' }} | {{ $lesson['start_time'] ?? '' }} - {{ $lesson['end_time'] ?? '' }}
                                    </div>
                                    <div class="text-gray-500 text-sm mt-1">
                                        Location: {{ $lesson['locationmeeting_link'] ?? 'No location' }}
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('lessons.edit', $id) }}" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition">
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </a>

                                    <form action="{{ route('lessons.destroy', $id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this lesson?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition">
                                            <i class="fas fa-trash-alt mr-1"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-gray-500">No lessons scheduled yet.</div>
                @endif
            </div>

            <!-- Footer -->
            <footer class="mt-8 text-center text-white opacity-75">
                <p>© 2025 MY CAMPUS. All rights reserved.</p>
            </footer>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarTexts = document.querySelectorAll('.sidebar-text');
            
            if (sidebar.classList.contains('w-72')) {
                sidebar.classList.replace('w-72','w-20');
                mainContent.classList.replace('ml-72','ml-20');
                sidebarTexts.forEach(text => text.classList.add('hidden'));
            } else {
                sidebar.classList.replace('w-20','w-72');
                mainContent.classList.replace('ml-20','ml-72');
                sidebarTexts.forEach(text => text.classList.remove('hidden'));
            }
        }
    </script>
</body>
</html>
