<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Study Group</title>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gradient-to-br from-purple-400 via-pink-500 to-red-500 min-h-screen">

<div class="flex">

    <!-- Sidebar (SAMA) -->
    @include('layouts.sidebar')

    <!-- Main Content (SAMA) -->
    <main class="flex-1 ml-72 p-6">

        <!-- Header (SAMA STYLE) -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6 flex justify-between items-center">
            <div class="flex items-center gap-3 text-gray-600">
                <i class="fas fa-users"></i>
                <span class="font-medium"> > Edit Study Group</span>
            </div>

            <div class="flex items-center gap-3">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(session('firebase_user.name','User')) }}"
                     class="w-10 h-10 rounded-full">
                <span class="font-semibold">
                    {{ session('firebase_user.name','User') }}
                </span>
            </div>
        </div>

        <!-- CONTENT CARD (SAMA MACAM PAGE LAIN) -->
        <div class="bg-white p-8 rounded-2xl shadow-xl max-w-xl mx-auto">

            <!-- ERROR -->
            @if ($errors->any())
                <div class="mb-4 bg-red-100 text-red-700 p-4 rounded">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- FORM -->
            <form action="{{ route('study-groups.update', $groupId) }}"
                  method="POST"
                  class="space-y-5">

                @csrf
                @method('PUT')

                <!-- Group Name -->
                <div>
                    <label class="block mb-2 font-medium text-gray-700">
                        Group Name
                    </label>
                    <input type="text"
                           name="name"
                           value="{{ old('name', $group['name'] ?? '') }}"
                           required
                           class="border p-3 w-full rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <!-- Subject -->
                <div>
                    <label class="block mb-2 font-medium text-gray-700">
                        Subject
                    </label>
                    <input type="text"
                           name="subject"
                           value="{{ old('subject', $group['subject'] ?? '') }}"
                           class="border p-3 w-full rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <!-- Description -->
                <div>
                    <label class="block mb-2 font-medium text-gray-700">
                        Description
                    </label>
                    <textarea name="description"
                              rows="4"
                              class="border p-3 w-full rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('description', $group['description'] ?? '') }}</textarea>
                </div>

                <!-- BUTTONS -->
                <div class="flex justify-between items-center pt-4">
                    <a href="{{ route('study-groups.index') }}"
                       class="text-gray-600 hover:underline">
                        ← Back
                    </a>

                    <button type="submit"
                            class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition">
                        Update Group
                    </button>
                </div>

            </form>
        </div>

    </main>
</div>

</body>
</html>
