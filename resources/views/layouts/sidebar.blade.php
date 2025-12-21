<!-- resources/views/layouts/sidebar.blade.php -->
<aside id="sidebar" class="fixed h-screen bg-white shadow-xl transition-all duration-300 z-50 w-72">
    <!-- Toggle Button -->
    <button onclick="toggleSidebar()"
        class="absolute -right-4 top-6 bg-purple-600 text-white p-2 rounded-full shadow-lg hover:bg-purple-700 transition z-50">
        <i class="fas fa-bars text-sm"></i>
    </button>

    <!-- Logo -->
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center gap-3">
            <div
                class="w-12 h-12 bg-gradient-to-br from-purple-600 to-pink-600 rounded-xl flex items-center justify-center text-white text-xl">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <span class="sidebar-text text-xl font-bold text-gray-800">MY CAMPUS</span>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="p-4">
        <ul class="space-y-2">
            @php
                $role = Session::get('firebase_user.role', 'student');
            @endphp

            {{-- ===============================
                TEACHER SIDEBAR
            =============================== --}}
            @if($role === 'teacher')

                <li>
                    <a href="{{ route('teacher.dashboard') }}"
                        class="flex items-center gap-4 px-4 py-3
                        {{ request()->routeIs('teacher.dashboard')
                            ? 'text-white bg-gradient-to-r from-purple-600 to-pink-600'
                            : 'text-gray-600 hover:bg-gray-100' }}
                        rounded-xl transition">
                        <i class="fas fa-home text-lg w-5"></i>
                        <span class="sidebar-text">Dashboard</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('lessons.add') }}"
                        class="flex items-center gap-4 px-4 py-3
                        {{ request()->routeIs('lessons.add')
                            ? 'text-white bg-gradient-to-r from-purple-600 to-pink-600'
                            : 'text-gray-600 hover:bg-gray-100' }}
                        rounded-xl transition">
                        <i class="fas fa-plus-circle text-lg w-5"></i>
                        <span class="sidebar-text">Add Lesson</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('lessons.list') }}"
                        class="flex items-center gap-4 px-4 py-3
                        {{ request()->routeIs('lessons.list')
                            ? 'text-white bg-gradient-to-r from-purple-600 to-pink-600'
                            : 'text-gray-600 hover:bg-gray-100' }}
                        rounded-xl transition">
                        <i class="fas fa-list text-lg w-5"></i>
                        <span class="sidebar-text">View Lessons</span>
                    </a>
                </li>

            {{-- ===============================
                STUDENT SIDEBAR
            =============================== --}}
            @else

                <li>
                    <a href="{{ route('student.dashboard') }}"
                        class="flex items-center gap-4 px-4 py-3
                        {{ request()->routeIs('student.dashboard')
                            ? 'text-white bg-gradient-to-r from-purple-600 to-pink-600'
                            : 'text-gray-600 hover:bg-gray-100' }}
                        rounded-xl transition">
                        <i class="fas fa-home text-lg w-5"></i>
                        <span class="sidebar-text">Dashboard</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('student.timetable') }}"
                        class="flex items-center gap-4 px-4 py-3
                        {{ request()->routeIs('student.timetable')
                            ? 'text-white bg-gradient-to-r from-purple-600 to-pink-600'
                            : 'text-gray-600 hover:bg-gray-100' }}
                        rounded-xl transition">
                        <i class="fas fa-calendar-alt text-lg w-5"></i>
                        <span class="sidebar-text">My Timetable</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('assignments.viewStudentAssignment') }}"
                        class="flex items-center gap-4 px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-xl transition">
                        <i class="fas fa-tasks text-lg w-5"></i>
                        <span class="sidebar-text">Assignments</span>
                    </a>
                </li>

            @endif
        </ul>
    </nav>
</aside>
