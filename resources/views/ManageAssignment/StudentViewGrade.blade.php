<!--TESTING UNTUK IMAN-->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Grades - ClassConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-purple-400 via-pink-500 to-red-500 min-h-screen">
    <div class="flex">
        @include('layouts.sidebar')

        <main id="mainContent" class="flex-1 ml-72 transition-all duration-300 p-6">
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-3 text-gray-600">
                        <i class="fas fa-home"></i>
                        <span> > My Grades</span>
                    </div>
                    <div class="flex items-center gap-6">
                        <div class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 rounded-xl p-2 transition">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($studentName) }}&background=667eea&color=fff" alt="User" class="w-10 h-10 rounded-full">
                            <span class="font-semibold text-gray-800">{{ $studentName }}</span>
                            <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-purple-600">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-medium">Assignments Graded</p>
                            <h3 class="text-3xl font-bold text-gray-800">{{ count($grades) }}</h3>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-xl">
                            <i class="fas fa-check-double text-purple-600 text-2xl"></i>
                        </div>
                    </div>
                </div>
                </div>

            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center gap-3">
                        <i class="fas fa-graduation-cap text-purple-600"></i>
                        Recent Academic Performance
                    </h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 text-gray-600 uppercase text-xs font-bold">
                            <tr>
                                <th class="px-6 py-4">Assignment Name</th>
                                <th class="px-6 py-4 text-center">Score</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Feedback</th>
                                <th class="px-6 py-4">Date Graded</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($grades as $grade)
                                <tr class="hover:bg-purple-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-800">{{ $grade['assignment_name'] }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="inline-block px-3 py-1 bg-purple-100 text-purple-700 rounded-lg font-bold">
                                            {{ $grade['grade'] }} / {{ $grade['total_points'] }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $statusColor = match(strtolower($grade['status'])) {
                                                'excellent' => 'bg-green-100 text-green-700',
                                                'needs_revision' => 'bg-orange-100 text-orange-700',
                                                default => 'bg-blue-100 text-blue-700',
                                            };
                                        @endphp
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">
                                            {{ ucfirst($grade['status']) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="group relative cursor-pointer">
                                            <p class="text-sm text-gray-600 max-w-xs truncate italic">
                                                "{{ $grade['feedback'] }}"
                                            </p>
                                            <div class="hidden group-hover:block absolute z-10 w-64 p-3 bg-gray-800 text-white text-xs rounded-lg shadow-xl -top-2 left-0 transform -translate-y-full">
                                                {{ $grade['feedback'] }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ date('M d, Y', strtotime($grade['graded_at'])) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-info-circle text-gray-300 text-5xl mb-4"></i>
                                            <p class="text-gray-500 text-lg">No grades available yet.</p>
                                            <p class="text-gray-400 text-sm">Once your teacher finishes grading, your scores will appear here.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <footer class="mt-8 text-center text-white opacity-75">
                <p>© 2024 ClassConnect. All rights reserved.</p>
            </footer>
        </main>
    </div>

    <script>
        // Sidebar Toggle Script (Matching your other pages)
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarTexts = document.querySelectorAll('.sidebar-text');
            
            if (sidebar.classList.contains('w-72')) {
                sidebar.classList.remove('w-72');
                sidebar.classList.add('w-20');
                mainContent.classList.remove('ml-72');
                mainContent.classList.add('ml-20');
                sidebarTexts.forEach(text => text.classList.add('hidden'));
            } else {
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