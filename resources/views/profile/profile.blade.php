<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - ClassConnect</title>
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
                        <span> > Profile Settings</span>
                    </div>
                    <div class="flex items-center gap-6">
                        <button class="relative text-gray-600 hover:text-purple-600 transition">
                            <i class="fas fa-bell text-xl"></i>
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">3</span>
                        </button>
                        <div class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 rounded-xl p-2 transition">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user['name']) }}&background=667eea&color=fff" alt="User" class="w-10 h-10 rounded-full">
                            <span class="font-semibold text-gray-800">{{ $user['name'] }}</span>
                            <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
                        </div>
                    </div>
                </div>
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

            <!-- Profile Information Section -->
            <div class="bg-white rounded-2xl shadow-lg p-8 mb-6">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                        <i class="fas fa-user-circle text-purple-600"></i>
                        Profile Information
                    </h2>
                    <p class="text-gray-600 mt-2">Update your account's profile information and email address.</p>
                </div>

                <form id="profile-form" action="{{ route('profile.update') }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PATCH')

                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                            placeholder="Enter your full name"
                            value="{{ old('name', $user['name']) }}"
                        >
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                            placeholder="Enter your email"
                            value="{{ old('email', $user['email']) }}"
                        >
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        
                        @if(!$user['email_verified'])
                            <div class="mt-2 flex items-center gap-2 text-amber-600">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span class="text-sm">Your email address is unverified.</span>
                            </div>
                        @endif
                    </div>

                    <!-- Role (Read-only) -->
                    <div>
                        <label for="role" class="block text-sm font-semibold text-gray-700 mb-2">
                            Role
                        </label>
                        <input 
                            type="text" 
                            id="role" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed"
                            value="{{ ucfirst($user['role']) }}" 
                            disabled
                        >
                    </div>

                    <!-- Class Section (Only for students) -->
                    @if($user['role'] === 'student')
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
                            <option value="1A" {{ old('class_section', $user['class_section']) == '1A' ? 'selected' : '' }}>1A</option>
                            <option value="1B" {{ old('class_section', $user['class_section']) == '1B' ? 'selected' : '' }}>1B</option>
                            <option value="2A" {{ old('class_section', $user['class_section']) == '2A' ? 'selected' : '' }}>2A</option>
                            <option value="2B" {{ old('class_section', $user['class_section']) == '2B' ? 'selected' : '' }}>2B</option>
                        </select>
                        @error('class_section')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    @endif

                    <!-- Action Button -->
                    <div class="flex gap-4 pt-4">
                        <button 
                            type="button"
                            onclick="confirmSubmit('profile-form', 'Update profile information?', 'Your profile will be updated.')"
                            class="flex-1 bg-gradient-to-r from-purple-600 to-indigo-600 text-white py-3 px-6 rounded-lg font-semibold hover:from-purple-700 hover:to-indigo-700 transition duration-300 shadow-lg hover:shadow-xl flex items-center justify-center gap-2"
                        >
                            <i class="fas fa-save"></i>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

            <!-- Update Password Section -->
            <div class="bg-white rounded-2xl shadow-lg p-8 mb-6">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                        <i class="fas fa-lock text-purple-600"></i>
                        Update Password
                    </h2>
                    <p class="text-gray-600 mt-2">Ensure your account is using a long, random password to stay secure.</p>
                </div>

                <form id="password-form" action="{{ route('profile.password.update') }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Current Password -->
                    <div>
                        <label for="current_password" class="block text-sm font-semibold text-gray-700 mb-2">
                            Current Password <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="password" 
                            id="current_password" 
                            name="current_password" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                            placeholder="Enter current password"
                            autocomplete="current-password"
                        >
                        @error('current_password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- New Password -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                            New Password <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                            placeholder="Enter new password (min. 6 characters)"
                            autocomplete="new-password"
                        >
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                            Confirm New Password <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="password" 
                            id="password_confirmation" 
                            name="password_confirmation" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                            placeholder="Confirm new password"
                            autocomplete="new-password"
                        >
                    </div>

                    <!-- Action Button -->
                    <div class="flex gap-4 pt-4">
                        <button 
                            type="button"
                            onclick="confirmSubmit('password-form', 'Update password?', 'Your password will be changed.')"
                            class="flex-1 bg-gradient-to-r from-purple-600 to-indigo-600 text-white py-3 px-6 rounded-lg font-semibold hover:from-purple-700 hover:to-indigo-700 transition duration-300 shadow-lg hover:shadow-xl flex items-center justify-center gap-2"
                        >
                            <i class="fas fa-key"></i>
                            Update Password
                        </button>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <footer class="mt-8 text-center text-white opacity-75">
                <p>© 2024 ClassConnect. All rights reserved.</p>
            </footer>
        </main>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        function confirmSubmit(formId, title, text) {
            Swal.fire({
                title: title,
                text: text,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#7c3aed',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, continue',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(formId).submit();
                }
            });
        }

        function showDeleteModal() {
            Swal.fire({
                title: 'Delete Account?',
                html: `
                    <div class="text-left">
                        <p class="text-gray-600 mb-4">Are you sure you want to delete your account? This action cannot be undone.</p>
                        <input 
                            type="password" 
                            id="delete-password" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                            placeholder="Enter your password to confirm"
                        >
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete my account',
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    const password = document.getElementById('delete-password').value;
                    if (!password) {
                        Swal.showValidationMessage('Please enter your password');
                        return false;
                    }
                    return password;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create and submit delete form
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("profile.destroy") }}';
                    
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';
                    
                    const methodField = document.createElement('input');
                    methodField.type = 'hidden';
                    methodField.name = '_method';
                    methodField.value = 'DELETE';
                    
                    const passwordField = document.createElement('input');
                    passwordField.type = 'hidden';
                    passwordField.name = 'password';
                    passwordField.value = result.value;
                    
                    form.appendChild(csrfToken);
                    form.appendChild(methodField);
                    form.appendChild(passwordField);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
</body>
</html>