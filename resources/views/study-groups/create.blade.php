<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Study Group</title>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gradient-to-br from-purple-400 via-pink-500 to-red-500 min-h-screen">

<div class="flex">

    <!-- Sidebar -->
    @include('layouts.sidebar')

    <!-- Main Content -->
    <main class="flex-1 ml-72 p-6">

        <!-- Header -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6 flex justify-between items-center">
            <div class="flex items-center gap-3 text-gray-600">
                <i class="fas fa-plus-circle"></i>
                <span class="font-medium"> > Create Study Group</span>
            </div>

            <div class="flex items-center gap-3">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(session('firebase_user.name','User')) }}&background=667eea&color=fff"
                     class="w-10 h-10 rounded-full">
                <span class="font-semibold">{{ session('firebase_user.name','User') }}</span>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white p-6 rounded-2xl shadow-md max-w-lg mx-auto">

            <form action="{{ route('study-groups.store') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label class="block mb-1 font-semibold text-gray-700">Group Name</label>
                    <input type="text" name="name" placeholder="Enter group name"
                           class="w-full p-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500"
                           required>
                </div>

                <div class="mb-4">
                    <label class="block mb-1 font-semibold text-gray-700">Subject</label>
                    <input type="text" name="subject" placeholder="Enter subject"
                           class="w-full p-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <div class="mb-4">
                    <label class="block mb-1 font-semibold text-gray-700">Description</label>
                    <textarea name="description" placeholder="Enter description"
                              class="w-full p-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
                </div>

                <button type="submit"
                        class="bg-purple-500 text-white px-6 py-3 rounded-xl hover:bg-purple-600 transition w-full">
                    Create Group
                </button>
            </form>

        </div>

    </main>
</div>

</body>
</html>
