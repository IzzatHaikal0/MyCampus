<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Assignment - ClassConnect</title>
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
                        <span> > Edit Assignment</span>
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

            <!-- Edit Assignment Form -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                        <i class="fas fa-edit text-purple-600"></i>
                        Edit Assignment
                    </h2>
                </div>

                <form action="{{ route('assignments.update', $assignment['id']) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('POST')

                    <!-- Assignment Name -->
                    <div>
                        <label for="assignment_name" class="block text-sm font-semibold text-gray-700 mb-2">
                            Assignment Name <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="assignment_name" 
                            name="assignment_name" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                            placeholder="Enter assignment name"
                            value="{{ old('assignment_name', $assignment['assignment_name']) }}"
                        >
                        @error('assignment_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Class Section Selection -->
                    <div>
                        <label for="class_section" class="block text-sm font-semibold text-gray-700 mb-2">
                            Class Section <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="class_section" 
                            name="class_section" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                        >
                            <option value="">-- Select Class Section --</option>
                            @php
                                $currentSection = old('class_section', $assignment['class_section'] ?? '');
                            @endphp
                            <option value="1A" {{ $currentSection == '1A' ? 'selected' : '' }}>1A</option>
                            <option value="1B" {{ $currentSection == '1B' ? 'selected' : '' }}>1B</option>
                            <option value="2A" {{ $currentSection == '2A' ? 'selected' : '' }}>2A</option>
                            <option value="2B" {{ $currentSection == '2B' ? 'selected' : '' }}>2B</option>
                        </select>
                        @error('class_section')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        
                        @if(isset($assignment['class_section']))
                            <p class="mt-2 text-sm text-gray-600">
                                <i class="fas fa-info-circle"></i> Currently assigned to: <span class="font-semibold text-purple-600">{{ $assignment['class_section'] }}</span>
                            </p>
                        @endif
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                            Description <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            id="description" 
                            name="description" 
                            rows="5"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition resize-none"
                            placeholder="Provide detailed instructions for the assignment..."
                        >{{ old('description', $assignment['description']) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Due Date -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="due_date" class="block text-sm font-semibold text-gray-700 mb-2">
                                Due Date <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="date" 
                                id="due_date" 
                                name="due_date" 
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                value="{{ old('due_date', $assignment['due_date']) }}"
                            >
                            @error('due_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="due_time" class="block text-sm font-semibold text-gray-700 mb-2">
                                Due Time <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="time" 
                                id="due_time" 
                                name="due_time" 
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                value="{{ old('due_time', $assignment['due_time']) }}"
                            >
                            @error('due_time')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Points/Grade -->
                    <div>
                        <label for="total_points" class="block text-sm font-semibold text-gray-700 mb-2">
                            Total Points <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            id="total_points" 
                            name="total_points" 
                            required
                            min="0"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                            placeholder="e.g., 100"
                            value="{{ old('total_points', $assignment['total_points']) }}"
                        >
                        @error('total_points')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Current Attachment Display -->
                    @if(isset($assignment['attachment_path']) && $assignment['attachment_path'])
                        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-file text-blue-600 text-2xl"></i>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-700">Current Attachment:</p>
                                        <p class="text-sm text-gray-600">{{ basename($assignment['attachment_path']) }}</p>
                                    </div>
                                </div>
                                <a href="{{ asset('storage/' . $assignment['attachment_path']) }}" 
                                   target="_blank" 
                                   class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition text-sm font-medium">
                                    <i class="fas fa-eye"></i> View File
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- File Attachment (Optional) -->
                    <div>
                        <label for="attachment" class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ isset($assignment['attachment_path']) ? 'Replace Attachment (Optional)' : 'Attachment (Optional)' }}
                        </label>
                        <div class="flex items-center justify-center w-full">
                            <label for="attachment" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3"></i>
                                    <p class="mb-2 text-sm text-gray-500">
                                        <span class="font-semibold">Click to upload</span> or drag and drop
                                    </p>
                                    <p class="text-xs text-gray-500">PDF, DOC, DOCX, PPT, or images (MAX. 10MB)</p>
                                    @if(isset($assignment['attachment_path']))
                                        <p class="text-xs text-purple-600 mt-1">Leave empty to keep current file</p>
                                    @endif
                                </div>
                                <input 
                                    id="attachment" 
                                    name="attachment" 
                                    type="file" 
                                    class="hidden"
                                    accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png"
                                    onchange="displayFileName(this)"
                                >
                            </label>
                        </div>
                        <p id="file-name" class="mt-2 text-sm text-gray-600"></p>
                        @error('attachment')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-4 pt-4">
                        <button 
                            type="submit" 
                            class="flex-1 bg-gradient-to-r from-purple-600 to-indigo-600 text-white py-3 px-6 rounded-lg font-semibold hover:from-purple-700 hover:to-indigo-700 transition duration-300 shadow-lg hover:shadow-xl flex items-center justify-center gap-2"
                        >
                            <i class="fas fa-save"></i>
                            Update Assignment
                        </button>
                        <a 
                            href="{{ route('assignments.list') }}" 
                            class="flex-1 bg-gray-200 text-gray-700 py-3 px-6 rounded-lg font-semibold hover:bg-gray-300 transition duration-300 flex items-center justify-center gap-2"
                        >
                            <i class="fas fa-times-circle"></i>
                            Cancel
                        </a>
                    </div>
                </form>
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

        function displayFileName(input) {
            const fileNameDisplay = document.getElementById('file-name');
            if (input.files && input.files[0]) {
                fileNameDisplay.textContent = '📎 New file selected: ' + input.files[0].name;
            } else {
                fileNameDisplay.textContent = '';
            }
        }

        // Auto-hide flash messages after 5 seconds
        document.querySelectorAll('.flash-message').forEach(message => {
            setTimeout(() => {
                message.style.opacity = '0';
                setTimeout(() => message.remove(), 500);
            }, 5000);
        });
    </script>
</body>
</html>