<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignment Submissions - ClassConnect</title>
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
                        <span> > Assignment Submissions</span>
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
                <div class="flash-message bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6 flex items-center gap-3">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="flash-message bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6 flex items-center gap-3">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Assignment Details Card -->
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold text-gray-800 mb-3 flex items-center gap-3">
                            <i class="fas fa-file-alt text-purple-600"></i>
                            {{ $assignment['assignment_name'] }}
                        </h2>
                        <p class="text-gray-600 mb-4">{{ $assignment['description'] }}</p>
                        <div class="flex items-center gap-6 text-sm text-gray-500">
                            <span class="flex items-center gap-2">
                                <i class="fas fa-calendar text-purple-600"></i>
                                Due: {{ date('M d, Y', strtotime($assignment['due_date'])) }}
                            </span>
                            <span class="flex items-center gap-2">
                                <i class="fas fa-clock text-purple-600"></i>
                                {{ date('h:i A', strtotime($assignment['due_time'])) }}
                            </span>
                            <span class="flex items-center gap-2">
                                <i class="fas fa-star text-purple-600"></i>
                                {{ $assignment['total_points'] }} Points
                            </span>
                            <span class="flex items-center gap-2">
                                <i class="fas fa-users text-purple-600"></i>
                                Class: {{ $assignment['class_section'] ?? 'N/A' }}
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('assignments.list') }}" 
                       class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors font-semibold flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i>
                        Back to List
                    </a>
                </div>
            </div>

            <!-- Submissions Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm mb-1">Total Submissions</p>
                            <p class="text-3xl font-bold text-purple-600">{{ $totalSubmissions }}</p>
                        </div>
                        <div class="bg-purple-100 rounded-full p-4">
                            <i class="fas fa-file-upload text-purple-600 text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm mb-1">Graded</p>
                            <p class="text-3xl font-bold text-green-600">{{ $totalGraded }}</p>
                        </div>
                        <div class="bg-green-100 rounded-full p-4">
                            <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm mb-1">Not Yet Graded</p>
                            <p class="text-3xl font-bold text-orange-600">{{ $totalUngraded }}</p>
                        </div>
                        <div class="bg-orange-100 rounded-full p-4">
                            <i class="fas fa-hourglass-half text-orange-600 text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm mb-1">Late Submissions</p>
                            <p class="text-3xl font-bold text-red-600">
                                {{ collect(array_merge($ungradedSubmissions, $gradedSubmissions))->filter(function($s) use ($assignment) {
                                    return strtotime($s['submitted_at']) > strtotime($assignment['due_date'] . ' ' . $assignment['due_time']);
                                })->count() }}
                            </p>
                        </div>
                        <div class="bg-red-100 rounded-full p-4">
                            <i class="fas fa-exclamation-circle text-red-600 text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- UNGRADED SUBMISSIONS SECTION -->
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                        <i class="fas fa-hourglass-half text-orange-600"></i>
                        Pending Grading ({{ $totalUngraded }})
                    </h2>
                    <input type="text" 
                           id="searchUngraded" 
                           placeholder="Search student name..." 
                           class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-600">
                </div>

                <div class="overflow-x-auto">
                    @if(count($ungradedSubmissions) > 0)
                        <table class="w-full" id="ungradedTable">
                            <thead class="bg-orange-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">#</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Student Name</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Email</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Class</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Submitted At</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($ungradedSubmissions as $index => $submission)
                                    @php
                                        $dueDateTime = strtotime($assignment['due_date'] . ' ' . $assignment['due_time']);
                                        $submittedDateTime = strtotime($submission['submitted_at']);
                                        $isLate = $submittedDateTime > $dueDateTime;
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition ungraded-row">
                                        <td class="px-4 py-4 text-sm text-gray-700">{{ $index + 1 }}</td>
                                        <td class="px-4 py-4">
                                            <div class="flex items-center gap-3">
                                                <img src="https://ui-avatars.com/api/?name={{ urlencode($submission['student_name']) }}&background=667eea&color=fff" 
                                                     alt="{{ $submission['student_name'] }}" 
                                                     class="w-10 h-10 rounded-full">
                                                <span class="font-semibold text-gray-800 student-name">{{ $submission['student_name'] }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-600">{{ $submission['student_email'] }}</td>
                                        <td class="px-4 py-4">
                                            <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-semibold">
                                                {{ $submission['class_section'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-600">
                                            {{ date('M d, Y h:i A', strtotime($submission['submitted_at'])) }}
                                        </td>
                                        <td class="px-4 py-4">
                                            @if($isLate)
                                                <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold flex items-center gap-1 w-fit">
                                                    <i class="fas fa-exclamation-circle"></i>
                                                    Late
                                                </span>
                                            @else
                                                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold flex items-center gap-1 w-fit">
                                                    <i class="fas fa-check-circle"></i>
                                                    On Time
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="flex flex-col gap-2">
                                                @if($submission['attachment_path'])
                                                    <a href="{{ asset('storage/' . $submission['attachment_path']) }}" 
                                                       target="_blank"
                                                       class="w-full px-3 py-1.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-xs font-medium flex items-center gap-2 justify-center">
                                                        <i class="fas fa-file-download"></i>
                                                        Download
                                                    </a>
                                                @endif
                                                @if($submission['submission_link'])
                                                    <a href="{{ $submission['submission_link'] }}" 
                                                       target="_blank"
                                                       class="w-full px-3 py-1.5 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600 transition-colors text-xs font-medium flex items-center gap-2 justify-center">
                                                        <i class="fas fa-external-link-alt"></i>
                                                        View Link
                                                    </a>
                                                @endif
                                                <button type="button" 
                                                    onclick='openGradingModal(@json($submission))'
                                                    class="w-full px-3 py-1.5 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors text-sm font-medium flex items-center gap-2 justify-center">
                                                    <i class="fas fa-edit"></i>
                                                    Grade
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="bg-gray-50 border-l-4 border-gray-300 rounded-lg p-8 text-center">
                            <i class="fas fa-check-double text-green-500 text-5xl mb-4"></i>
                            <p class="text-gray-600 text-lg font-semibold">All submissions have been graded!</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- GRADED SUBMISSIONS SECTION -->
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                        <i class="fas fa-check-circle text-green-600"></i>
                        Graded Submissions ({{ $totalGraded }})
                    </h2>
                    <div class="flex items-center gap-3">
                        <input type="text" 
                               id="searchGraded" 
                               placeholder="Search student name..." 
                               class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-600">
                        <button onclick="exportToCSV()" 
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-semibold flex items-center gap-2">
                            <i class="fas fa-download"></i>
                            Export CSV
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    @if(count($gradedSubmissions) > 0)
                        <table class="w-full" id="gradedTable">
                            <thead class="bg-green-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">#</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Student Name</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Email</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Grade</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Feedback</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Graded At</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Attachments</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($gradedSubmissions as $index => $submission)
                                    <tr class="hover:bg-gray-50 transition graded-row">
                                        <td class="px-4 py-4 text-sm text-gray-700">{{ $index + 1 }}</td>
                                        <td class="px-4 py-4">
                                            <div class="flex items-center gap-3">
                                                <img src="https://ui-avatars.com/api/?name={{ urlencode($submission['student_name']) }}&background=667eea&color=fff" 
                                                     alt="{{ $submission['student_name'] }}" 
                                                     class="w-10 h-10 rounded-full">
                                                <span class="font-semibold text-gray-800 student-name">{{ $submission['student_name'] }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-600">{{ $submission['student_email'] }}</td>
                                        <td class="px-4 py-4">
                                            <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-bold">
                                                {{ $submission['grade'] }} / {{ $assignment['total_points'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-600 max-w-xs truncate">
                                            {{ $submission['feedback'] }}
                                        </td>
                                        <td class="px-4 py-4">
                                            @if($submission['grade_status'] == 'graded')
                                                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                                                    Graded
                                                </span>
                                            @elseif($submission['grade_status'] == 'needs_revision')
                                                <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-semibold">
                                                    Needs Revision
                                                </span>
                                            @else
                                                <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-semibold">
                                                    {{ ucfirst($submission['grade_status']) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-600">
                                            {{ date('M d, Y h:i A', strtotime($submission['graded_at'])) }}
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="flex flex-col gap-2">
                                                @if($submission['attachment_path'])
                                                    <a href="{{ asset('storage/' . $submission['attachment_path']) }}" 
                                                       target="_blank"
                                                       class="w-full px-3 py-1.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-xs font-medium flex items-center gap-2 justify-center">
                                                        <i class="fas fa-file-download"></i>
                                                        Download
                                                    </a>
                                                @endif
                                                @if($submission['submission_link'])
                                                    <a href="{{ $submission['submission_link'] }}" 
                                                       target="_blank"
                                                       class="w-full px-3 py-1.5 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600 transition-colors text-xs font-medium flex items-center gap-2 justify-center">
                                                        <i class="fas fa-external-link-alt"></i>
                                                        View Link
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="bg-gray-50 border-l-4 border-gray-300 rounded-lg p-8 text-center">
                            <i class="fas fa-inbox text-gray-400 text-5xl mb-4"></i>
                            <p class="text-gray-600 text-lg font-semibold">No graded submissions yet</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Footer -->
            <footer class="mt-8 text-center text-white opacity-75">
                <p>© 2024 ClassConnect. All rights reserved.</p>
            </footer>
        </main>

        <!-- Grading Modal (same as before) -->
        <div id="gradingModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
                <div class="bg-gradient-to-r from-orange-600 to-red-600 p-6 rounded-t-xl">
                    <div class="flex justify-between items-center">
                        <h3 class="text-2xl font-bold text-white flex items-center gap-3">
                            Grade Submission
                        </h3>
                        <button onclick="closeGradingModal()" class="text-white hover:text-gray-200 transition">
                            <i class="fas fa-times text-2xl"></i>
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-blue-50 border-l-4 border-blue-600 rounded-lg p-4 mb-6">
                            <h4 class="font-semibold text-gray-800 text-lg mb-2">
                                <i class="fas fa-user text-blue-600"></i> Student Information
                            </h4>
                            <div id="studentInfo" class="space-y-2 text-sm text-gray-600"></div>
                        </div>

                        <div class="bg-purple-50 border-l-4 border-purple-600 rounded-lg p-4 mb-6">
                            <h4 class="font-semibold text-gray-800 text-lg mb-2">
                                <i class="fas fa-file-alt text-purple-600"></i> Assignment Details
                            </h4>
                            <div id="gradingModalAssignmentInfo" class="space-y-2 text-sm text-gray-600"></div>
                        </div>
                    </div>

                    <div class="bg-green-50 border-l-4 border-green-600 rounded-lg p-4 mb-6">
                        <h4 class="font-semibold text-gray-800 text-lg mb-2">
                            <i class="fas fa-paperclip text-green-600"></i> Submitted Work
                        </h4>
                        <div id="submissionPreview" class="space-y-2 text-sm text-gray-600"></div>
                    </div>

                    <form id="gradingForm" action="" method="POST" onsubmit="return handleGrading(event)">
                        @csrf
                        
                        <input type="hidden" name="submission_id" id="submissionIdInput" value="">
                        <input type="hidden" name="assignment_id" id="gradingAssignmentIdInput" value="">
                        <input type="hidden" name="student_id" id="studentIdInput" value="">
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-star text-orange-600"></i> Grade / Score
                                </label>
                                <div class="flex items-center gap-3">
                                    <input type="number" name="grade" id="gradeInput" min="0" step="0.5"
                                        placeholder="Enter grade"
                                        class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                                        required>
                                    <span class="text-gray-600 font-medium">/ <span id="maxPoints">100</span> Points</span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-comment text-orange-600"></i> Feedback / Comments
                                </label>
                                <textarea name="feedback" id="feedbackInput" rows="4"
                                    placeholder="Provide feedback for the student..."
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition resize-none"
                                    required></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-flag text-orange-600"></i> Status
                                </label>
                                <select name="status" id="statusInput"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition">
                                    <option value="graded">Graded</option>
                                    <option value="needs_revision">Needs Revision</option>
                                    <option value="excellent">Excellent</option>
                                </select>
                            </div>

                            <div class="flex gap-3 mt-6">
                                <button type="button" onclick="closeGradingModal()"
                                    class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-semibold">
                                    Cancel
                                </button>
                                <button type="submit" 
                                    class="flex-1 px-6 py-3 bg-gradient-to-r from-orange-600 to-red-600 text-white rounded-lg hover:from-orange-700 hover:to-red-700 transition-all font-semibold flex items-center justify-center gap-2">
                                    <i class="fas fa-check"></i> Submit Grade
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
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

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.flash-message').forEach(message => {
                setTimeout(() => {
                    message.style.opacity = '0';
                    setTimeout(() => message.remove(), 500);
                }, 5000);
            });
        });

        // Search ungraded submissions
        document.getElementById('searchUngraded')?.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.ungraded-row');
            rows.forEach(row => {
                const studentName = row.querySelector('.student-name').textContent.toLowerCase();
                row.style.display = studentName.includes(searchTerm) ? '' : 'none';
            });
        });

        // Search graded submissions
        document.getElementById('searchGraded')?.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.graded-row');
            rows.forEach(row => {
                const studentName = row.querySelector('.student-name').textContent.toLowerCase();
                row.style.display = studentName.includes(searchTerm) ? '' : 'none';
            });
        });

        function exportToCSV() {
            const table = document.getElementById('gradedTable');
            if (!table) return;
            
            let csv = [];
            const rows = table.querySelectorAll('tr');
            
            rows.forEach(row => {
                const cols = row.querySelectorAll('td, th');
                const csvRow = [];
                cols.forEach((col, index) => {
                    if (index < cols.length - 1) {
                        csvRow.push('"' + col.textContent.trim().replace(/"/g, '""') + '"');
                    }
                });
                csv.push(csvRow.join(','));
            });
            
            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'graded_submissions_{{ date("Y-m-d") }}.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }

        function openGradingModal(submission) {
            const modal = document.getElementById('gradingModal');
            const form = document.getElementById('gradingForm');
            const studentInfo = document.getElementById('studentInfo');
            const assignmentInfo = document.getElementById('gradingModalAssignmentInfo');
            const submissionPreview = document.getElementById('submissionPreview');
            const maxPoints = document.getElementById('maxPoints');
            const gradeInput = document.getElementById('gradeInput');
            
            form.action = `/assignments/grade-submission/${submission.submission_id}`;
            
            document.getElementById('submissionIdInput').value = submission.submission_id || '';
            document.getElementById('gradingAssignmentIdInput').value = submission.assignment_id || '';
            document.getElementById('studentIdInput').value = submission.student_id || '';
            
            studentInfo.innerHTML = `
                <div class="flex items-center gap-3 mb-2">
                    <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(submission.student_name)}&background=667eea&color=fff" 
                         alt="${submission.student_name}" 
                         class="w-12 h-12 rounded-full">
                    <div>
                        <p class="font-semibold text-gray-800">${submission.student_name || 'N/A'}</p>
                        <p class="text-xs text-gray-500">${submission.student_email || 'N/A'}</p>
                    </div>
                </div>
                <p class="text-sm">
                    <span class="font-semibold">Class:</span> 
                    <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded text-xs">${submission.class_section || 'N/A'}</span>
                </p>
            `;
            
            const assignment = @json($assignment);
            maxPoints.textContent = assignment.total_points || '100';
            gradeInput.max = assignment.total_points || '100';
            
            assignmentInfo.innerHTML = `
                <p><span class="font-semibold">Assignment:</span> ${assignment.assignment_name || 'N/A'}</p>
                <p><span class="font-semibold">Total Points:</span> ${assignment.total_points || '0'}</p>
                <p><span class="font-semibold">Due Date:</span> ${assignment.due_date ? new Date(assignment.due_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A'}</p>
            `;
            
            let previewHTML = `<p class="text-sm mb-2">
                <span class="font-semibold">Submitted:</span> ${submission.submitted_at ? new Date(submission.submitted_at).toLocaleString() : 'N/A'}
            </p>`;
            
            if (submission.attachment_path) {
                previewHTML += `
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-file text-green-600"></i>
                        <span class="text-sm">File: ${submission.attachment_path.split('/').pop()}</span>
                        <a href="/storage/${submission.attachment_path}" 
                           target="_blank"
                           class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors text-xs">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </div>
                `;
            }
            
            if (submission.submission_link) {
                previewHTML += `
                    <div class="flex items-center gap-2">
                        <i class="fas fa-link text-green-600"></i>
                        <a href="${submission.submission_link}" 
                           target="_blank" 
                           class="text-blue-600 hover:underline text-sm">
                            ${submission.submission_link}
                        </a>
                    </div>
                `;
            }
            
            if (!submission.attachment_path && !submission.submission_link) {
                previewHTML += `<p class="text-gray-400 text-sm italic">No attachments submitted</p>`;
            }
            
            submissionPreview.innerHTML = previewHTML;
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeGradingModal() {
            const modal = document.getElementById('gradingModal');
            const form = document.getElementById('gradingForm');
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
            form.reset();
        }

        function handleGrading(event) {
            event.preventDefault();
            
            const form = document.getElementById('gradingForm');
            const gradeInput = document.getElementById('gradeInput');
            const maxPoints = document.getElementById('maxPoints');
            
            if (parseFloat(gradeInput.value) > parseFloat(maxPoints.textContent)) {
                alert(`Grade cannot exceed ${maxPoints.textContent} points`);
                return false;
            }
            
            if (confirm('Are you sure you want to submit this grade?')) {
                form.submit();
            }
            
            return false;
        }
    </script>
</body>
</html>