<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - ClassConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                        <span> > Your Assignment List</span>
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
                                        <button type="button" 
                                                onclick='openSubmissionModal(@json($assignment))'
                                                class="w-full px-3 py-1.5 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors text-sm font-medium flex items-center gap-2">
                                            <i class="fas fa-plus"></i>
                                            Add Submission
                                        </button>
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

    <!-- Submission Modal -->
    <div id="submissionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="bg-gradient-to-r from-purple-600 to-pink-600 p-6 rounded-t-2xl">
                <div class="flex justify-between items-center">
                    <h3 class="text-2xl font-bold text-white flex items-center gap-3">
                        <i class="fas fa-upload"></i>
                        Add Submission
                    </h3>
                    <button onclick="closeSubmissionModal()" class="text-white hover:text-gray-200 transition">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
            </div>

            <div class="p-6">
                <!-- Assignment Information -->
                <div class="bg-purple-50 border-l-4 border-purple-600 rounded-lg p-4 mb-6">
                    <h4 class="font-semibold text-gray-800 text-lg mb-2">Assignment Details</h4>
                    <div id="modalAssignmentInfo" class="space-y-2 text-sm text-gray-600">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>

                <!-- Submission Form -->
                <form id="submissionForm" action="{{ route('assignments.addSubmission', $assignment['id']) }}" method="POST" enctype="multipart/form-data" onsubmit="return handleSubmission(event)">
                    @csrf
                    
                    <!-- Hidden field for assignment ID -->
                    <input type="hidden" name="assignment_id" id="assignmentIdInput" value="">
                    
                    <div class="space-y-4">
                        <!-- File Upload -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-file-upload text-purple-600"></i>
                                Upload File
                            </label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-purple-500 transition cursor-pointer"
                                onclick="document.getElementById('fileInput').click()">
                                <input type="file" id="fileInput" name="submission_file" class="hidden" onchange="updateFileName(this)" accept=".pdf,.doc,.docx,.zip">
                                <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                                <p class="text-gray-600 font-medium">Click to upload or drag and drop</p>
                                <p class="text-gray-400 text-sm mt-1">PDF, DOC, DOCX, ZIP (Max 10MB)</p>
                                <p id="fileName" class="text-purple-600 font-semibold mt-2 hidden"></p>
                            </div>
                        </div>

                        <!-- Link Input (Optional) -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-link text-purple-600"></i>
                                Submission Link
                            </label>
                            <input type="url" 
                                name="submission_link" 
                                id="submissionLink"
                                placeholder="https://example.com/your-submission"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                            <p class="text-gray-400 text-xs mt-1">Add a link to Google Drive, GitHub, or any online resource</p>
                        </div>

                        <p class="text-sm text-gray-500 italic">* Provide at least a file or a link</p>

                        <!-- Submit Buttons -->
                        <div class="flex gap-3 mt-6">
                            <button type="button" 
                                    onclick="closeSubmissionModal()"
                                    class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-semibold">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="flex-1 px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-700 hover:to-pink-700 transition-all font-semibold flex items-center justify-center gap-2">
                                <i class="fas fa-paper-plane"></i>
                                Submit Assignment
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
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

        function openSubmissionModal(assignment) {
            const modal = document.getElementById('submissionModal');
            const form = document.getElementById('submissionForm');
            const assignmentInfo = document.getElementById('modalAssignmentInfo');
            const assignmentIdInput = document.getElementById('assignmentIdInput');
            
            // Update form action with the correct assignment ID
            form.action = `/assignments/add-submission/${assignment.id}`;
            
            // Set hidden assignment ID field
            if (assignmentIdInput) {
                assignmentIdInput.value = assignment.id;
            }
            
            // Display assignment information
            assignmentInfo.innerHTML = `
                <div class="flex items-start gap-2">
                    <i class="fas fa-book text-purple-600 mt-1"></i>
                    <div>
                        <span class="font-semibold">Assignment:</span> ${assignment.assignment_name || 'N/A'}
                    </div>
                </div>
                <div class="flex items-start gap-2">
                    <i class="fas fa-align-left text-purple-600 mt-1"></i>
                    <div>
                        <span class="font-semibold">Description:</span> ${assignment.description || 'N/A'}
                    </div>
                </div>
                <div class="flex items-center gap-4 flex-wrap">
                    <span class="flex items-center gap-2">
                        <i class="fas fa-calendar text-purple-600"></i>
                        <span class="font-semibold">Due:</span> ${assignment.due_date ? new Date(assignment.due_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A'}
                    </span>
                    <span class="flex items-center gap-2">
                        <i class="fas fa-clock text-purple-600"></i>
                        ${assignment.due_time || 'N/A'}
                    </span>
                    <span class="flex items-center gap-2">
                        <i class="fas fa-star text-purple-600"></i>
                        ${assignment.total_points || '0'} Points
                    </span>
                </div>
            `;
            
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeSubmissionModal() {
            const modal = document.getElementById('submissionModal');
            const form = document.getElementById('submissionForm');
            const fileInput = document.getElementById('fileInput');
            const fileName = document.getElementById('fileName');
            const linkInput = document.getElementById('submissionLink');
            
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
            
            // Reset form
            form.reset();
            fileName.classList.add('hidden');
            fileName.textContent = '';
        }

        function updateFileName(input) {
            const fileName = document.getElementById('fileName');
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const fileSize = (file.size / 1024 / 1024).toFixed(2); // Convert to MB
                
                // Check file size
                if (file.size > 10 * 1024 * 1024) { // 10MB in bytes
                    Swal.fire({
                        icon: 'error',
                        title: 'File Too Large',
                        text: 'File size must not exceed 10MB',
                        confirmButtonColor: '#9333ea'
                    });
                    input.value = '';
                    fileName.classList.add('hidden');
                    return;
                }
                
                fileName.textContent = `Selected: ${file.name} (${fileSize} MB)`;
                fileName.classList.remove('hidden');
            } else {
                fileName.classList.add('hidden');
                fileName.textContent = '';
            }
        }

        function handleSubmission(event) {
            event.preventDefault();
            
            const fileInput = document.getElementById('fileInput');
            const linkInput = document.getElementById('submissionLink');
            const form = document.getElementById('submissionForm');
            
            // Validate that at least one field is filled
            if (!fileInput.files.length && !linkInput.value.trim()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Submission Required',
                    text: 'Please provide either a file or a link for your submission.',
                    confirmButtonColor: '#9333ea'
                });
                return false;
            }
            
            // Get submission details
            let submissionDetails = '';
            if (fileInput.files.length) {
                submissionDetails += `<strong>File:</strong> ${fileInput.files[0].name}<br>`;
            }
            if (linkInput.value.trim()) {
                submissionDetails += `<strong>Link:</strong> ${linkInput.value}`;
            }
            
            // Show confirmation dialog
            Swal.fire({
                title: 'Confirm Submission',
                html: `Are you sure you want to submit this assignment?<br><br>${submissionDetails}`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#9333ea',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Submit!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                // Debug FormData
                const fd = new FormData(form);
                for (let pair of fd.entries()) {
                    console.log(pair[0]+ ': '+ pair[1]);
                }

                form.submit();
            }

            });
            
            return false;
        }

        // Close modal when clicking outside
        document.getElementById('submissionModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeSubmissionModal();
            }
        });

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