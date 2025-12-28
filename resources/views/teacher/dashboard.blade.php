<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - ClassConnect</title>
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
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-3 text-gray-600">
                        <i class="fas fa-home"></i>
                        <span>Home</span>
                    </div>
                    <div class="flex items-center gap-6">
                        <button class="relative text-gray-600 hover:text-purple-600 transition">
                            <i class="fas fa-bell text-xl"></i>
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">3</span>
                        </button>
                        <div class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 rounded-xl p-2 transition">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(session('firebase_user.name', 'Cikgu Saodah')) }}&background=667eea&color=fff" alt="User" class="w-10 h-10 rounded-full">
                            <span class="font-semibold text-gray-800">{{ session('firebase_user.name', 'Cikgu Saodah') }}</span>
                            <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
                        </div>
                    </div>
                </div>
            </div>

           <!-- Flash Messages -->
            @if(session('success'))
                <div class="flash-message bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6 flex items-center gap-3 transition-opacity duration-500">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="flash-message bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6 flex items-center gap-3 transition-opacity duration-500">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif
    
            
            <!-- Bottom Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
<<<<<<< HEAD
                <!-- Today's Classes -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
                        <i class="fas fa-calendar-day text-purple-600"></i>
                        Today's Classes
                    </h2>
                    <div class="space-y-4">
                        <div class="bg-purple-50 border-l-4 border-purple-600 rounded-lg p-4 flex justify-between items-center hover:shadow-md transition">
                            <div>
                                <div class="font-semibold text-gray-800">Mathematics 101</div>
                                <div class="text-sm text-gray-500">Room 204</div>
                            </div>
                            <div class="text-purple-600 font-semibold">09:00 AM</div>
                        </div>
                        <div class="bg-purple-50 border-l-4 border-purple-600 rounded-lg p-4 flex justify-between items-center hover:shadow-md transition">
                            <div>
                                <div class="font-semibold text-gray-800">Physics Advanced</div>
                                <div class="text-sm text-gray-500">Lab 3</div>
                            </div>
                            <div class="text-purple-600 font-semibold">11:00 AM</div>
                        </div>
                        <div class="bg-purple-50 border-l-4 border-purple-600 rounded-lg p-4 flex justify-between items-center hover:shadow-md transition">
                            <div>
                                <div class="font-semibold text-gray-800">Chemistry Basics</div>
                                <div class="text-sm text-gray-500">Room 105</div>
                            </div>
                            <div class="text-purple-600 font-semibold">02:00 PM</div>
                        </div>
                    </div>
                </div>
=======
             <!-- Today's Classes -->
<div class="bg-white rounded-2xl shadow-lg p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
        <i class="fas fa-calendar-day text-purple-600"></i>
        Today's Classes
    </h2>

    <div class="space-y-4">
       @if(count($lessons) > 0)
    <div class="space-y-4">
        @foreach($lessons as $lesson)
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold">
                    {{ $lesson['subject_name'] }}
                </h3>

                <p class="text-sm text-gray-600">
                    📚 Class Section:
                    <span class="font-medium text-gray-800">
                        {{ $lesson['class_section'] }}
                    </span>
                </p>

                <p class="text-sm text-gray-600">
                    🕒 Time:
                    {{ $lesson['start_time'] }} – {{ $lesson['end_time'] }}
                </p>

                <p class="text-sm text-gray-600">
                    📍 Location:
                    {{ $lesson['locationmeeting_link'] }}
                </p>
            </div>
        @endforeach
    </div>
@else
    <div class="text-center py-10 text-gray-500">
        <h2 class="text-xl font-semibold">No Classes Today</h2>
        <p>Enjoy your free day!</p>
    </div>
@endif

    </div>
</div>
>>>>>>> origin/ManageAssignment

                <!-- Students in Class Today -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
                        <i class="fas fa-users text-purple-600"></i>
                        Students in Class Today
                    </h2>
                    <div class="space-y-3">
                        <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition">
                            <img src="https://ui-avatars.com/api/?name=Ahmad+Ali&background=667eea&color=fff" alt="Student" class="w-12 h-12 rounded-full">
                            <div>
                                <div class="font-semibold text-gray-800">Ahmad Ali</div>
                                <div class="text-sm text-gray-500">Mathematics 101</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition">
                            <img src="https://ui-avatars.com/api/?name=Siti+Nurhaliza&background=f093fb&color=fff" alt="Student" class="w-12 h-12 rounded-full">
                            <div>
                                <div class="font-semibold text-gray-800">Siti Nurhaliza</div>
                                <div class="text-sm text-gray-500">Physics Advanced</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition">
                            <img src="https://ui-avatars.com/api/?name=Kumar+Raj&background=4facfe&color=fff" alt="Student" class="w-12 h-12 rounded-full">
                            <div>
                                <div class="font-semibold text-gray-800">Kumar Raj</div>
                                <div class="text-sm text-gray-500">Chemistry Basics</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition">
                            <img src="https://ui-avatars.com/api/?name=Emily+Wong&background=f5576c&color=fff" alt="Student" class="w-12 h-12 rounded-full">
                            <div>
                                <div class="font-semibold text-gray-800">Emily Wong</div>
                                <div class="text-sm text-gray-500">Mathematics 101</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="mt-8 text-center text-white opacity-75">
                <p>© 2024 ClassConnect. All rights reserved.</p>
            </footer>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarTexts = document.querySelectorAll('.sidebar-text');
            
            if (sidebar.classList.contains('w-72')) {
                // Collapse
                sidebar.classList.remove('w-72');
                sidebar.classList.add('w-20');
                mainContent.classList.remove('ml-72');
                mainContent.classList.add('ml-20');
                sidebarTexts.forEach(text => text.classList.add('hidden'));
            } else {
                // Expand
                sidebar.classList.remove('w-20');
                sidebar.classList.add('w-72');
                mainContent.classList.remove('ml-20');
                mainContent.classList.add('ml-72');
                sidebarTexts.forEach(text => text.classList.remove('hidden'));
            }
        }
    </script>
</body>
</html>