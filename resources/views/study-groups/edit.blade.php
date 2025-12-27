<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Study Group</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-purple-400 via-pink-500 to-red-500 min-h-screen">

<div class="flex">
    <!-- Sidebar -->
    @include('layouts.sidebar')

    <!-- Main content -->
    <main class="flex-1 ml-72 p-6">

        <!-- Top Header -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6 flex justify-between items-center">
            <!-- Breadcrumb / Page Info -->
            <div class="flex items-center gap-3 text-gray-600">
                <i class="fas fa-home"></i>
                <span class="font-medium"> > Edit Study Group</span>
            </div>

            <!-- Notifications + User -->
            <div class="flex items-center gap-6">
                <button class="relative text-gray-600 hover:text-purple-600 transition">
                    <i class="fas fa-bell text-xl"></i>
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">3</span>
                </button>

                <div class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 rounded-xl p-2 transition">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(session('firebase_user.name', 'User')) }}&background=667eea&color=fff" 
                         alt="User" class="w-10 h-10 rounded-full">
                    <span class="font-semibold text-gray-800">{{ session('firebase_user.name', 'User') }}</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
                </div>
            </div>
        </div>

        <!-- Edit Form Card -->
        <div class="bg-white p-8 rounded-2xl shadow-xl max-w-lg mx-auto">
            <h1 class="text-2xl font-bold mb-6 text-purple-700 text-center">Edit Study Group</h1>

            <form action="{{ route('study-groups.update', $study_group->id) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block mb-2 font-medium text-gray-700">Group Name</label>
                    <input type="text" name="name" value="{{ old('name', $study_group->name) }}"
                           class="border p-3 w-full rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 font-medium text-gray-700">Subject</label>
                    <input type="text" name="subject" value="{{ old('subject', $study_group->subject) }}"
                           class="border p-3 w-full rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    @error('subject')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 font-medium text-gray-700">Description</label>
                    <textarea name="description" rows="4"
                              class="border p-3 w-full rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('description', $study_group->description) }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full bg-purple-500 text-white py-3 rounded-lg hover:bg-purple-600 transition font-semibold">
                    Update Group
                </button>

                <a href="{{ route('study-groups.index') }}" 
                   class="block mt-4 text-center text-purple-700 hover:underline">
                   ← Back to My Study Groups
                </a>
            </form>
        </div>

    </main>
</div>

</body>
</html>
