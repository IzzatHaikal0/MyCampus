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
                        <span> > Manage Assignment List</span>
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

             <!-- Today's Classes -->
           <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                        <i class="fas fa-calendar-day text-purple-600"></i>
                        List of Active Assignment
                    </h2>
            
                    <a href="{{ route('assignments.create') }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-semibold">
                        <i class="fas fa-plus text-lg w-5"></i>
                        <span class="sidebar-text">Add New</span>
                    </a>
                </div>

                <div class="space-y-4">
                    @if(isset($assignments) && count($assignments) > 0)
                        @foreach($assignments as $assignment)
                            <div class="bg-purple-50 border-l-4 border-purple-600 rounded-lg p-4 hover:shadow-md transition">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="font-semibold text-gray-800 text-lg mb-1">
                                            {{ $assignment['assignment_name'] }}
                                        </div>
                                        <div class="text-sm text-gray-600 mb-2">
                                            {{ Str::limit($assignment['description'], 100) }}
                                        </div>
                                        <div class="flex items-center gap-4 text-sm text-gray-500">
                                            <span class="flex items-center gap-1">
                                                <i class="fas fa-calendar text-purple-600"></i>
                                                Due: {{ date('M d, Y', strtotime($assignment['due_date'])) }}
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <i class="fas fa-clock text-purple-600"></i>
                                                {{ date('h:i A', strtotime($assignment['due_time'])) }}
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <i class="fas fa-star text-purple-600"></i>
                                                {{ $assignment['total_points'] }} Points
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-2 ml-4">
                                        @if($assignment['attachment_path'])
                                            <a href="{{ asset('storage/' . $assignment['attachment_path']) }}" 
                                            target="_blank" 
                                            class="px-3 py-1.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-sm font-medium flex items-center gap-2">
                                                <i class="fas fa-file-download"></i>
                                                View File
                                            </a>
                                        @endif
                                        <a href="{{ route('assignments.edit', $assignment['id']) }}" 
                                        class="px-3 py-1.5 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors text-sm font-medium flex items-center gap-2">
                                            <i class="fas fa-edit"></i>
                                            Edit
                                        </a>
                                        <form action="{{ route('assignments.delete', $assignment['id']) }}" 
                                            method="POST" 
                                            onsubmit="return confirm('Are you sure you want to delete this assignment?')"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="w-full px-3 py-1.5 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors text-sm font-medium flex items-center gap-2">
                                                <i class="fas fa-trash"></i>
                                                Delete
                                            </button>
                                        </form>
                                        <a href="{{ route('submission-teacher.view', $assignment['id']) }}" 
                                        class="px-3 py-1.5 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors text-sm font-medium flex items-center gap-2">
                                            <i class="fas fa-eye"></i>
                                            View Submission
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="bg-gray-50 border-l-4 border-gray-300 rounded-lg p-8 text-center">
                            <i class="fas fa-inbox text-gray-400 text-5xl mb-4"></i>
                            <p class="text-gray-600 text-lg font-semibold">No assignments found</p>
                            <p class="text-gray-500 text-sm mt-2">Click "Add New" to create your first assignment</p>
                        </div>
                    @endif
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

        

    // Auto-hide all flash messages after 5 seconds
    document.querySelectorAll('.flash-message').forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => message.remove(), 500);
        }, 5000);
    });

    </script>
</body>
</html>