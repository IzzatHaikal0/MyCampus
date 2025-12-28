<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Study Groups</title>

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
                <i class="fas fa-users"></i>
                <span class="font-medium"> > My Study Groups</span>
            </div>

            <div class="flex items-center gap-3">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(session('firebase_user.name','User')) }}"
                     class="w-10 h-10 rounded-full">
                <span class="font-semibold">{{ session('firebase_user.name','User') }}</span>
            </div>
        </div>

        <!-- Create + Join -->
        <div class="flex justify-between items-center mb-6">
            <a href="{{ route('study-groups.create') }}"
               class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">
                + Create Group
            </a>

            <form action="{{ route('study-groups.joinByCode') }}" method="POST" class="flex gap-2">
                @csrf
                <input type="text" name="code" placeholder="Group Code"
                       class="border p-2 rounded">
                <button class="bg-blue-500 text-white px-3 py-2 rounded">
                    Join
                </button>
            </form>
        </div>

        <!-- Group Cards -->
        @if($groups->count())
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            @foreach($groups as $group)
            <div
                onclick="window.location='{{ route('study-groups.chat',$group->id) }}'"
                class="bg-white p-5 rounded-xl shadow-md hover:shadow-lg transition cursor-pointer relative">

                <div class="flex justify-between items-start">

                    <!-- Group Info -->
                    <div>
                        <h2 class="text-xl font-semibold text-purple-700">
                            {{ $group->name }}
                        </h2>
                        <p class="text-gray-600">{{ $group->subject }}</p>
                        <p class="text-sm text-gray-500 mt-2 line-clamp-2">
                            {{ $group->description }}
                        </p>
                    </div>

                    <!-- More Info -->
                    <button type="button"
                        onclick="event.stopPropagation(); openModal({{ $group->id }})"
                        class="bg-purple-500 text-white px-3 py-1 rounded hover:bg-purple-600">
                        More Info
                    </button>

                </div>

                <!-- Edit / Delete -->
                <div class="absolute bottom-3 right-3 flex gap-2">
                    <a href="{{ route('study-groups.edit',$group->id) }}"
                       onclick="event.stopPropagation()"
                       class="bg-yellow-400 text-white px-3 py-1 rounded text-sm">
                        Edit
                    </a>

                    <form action="{{ route('study-groups.destroy', $group->id) }}" method="POST" 
      onsubmit="event.stopPropagation(); return confirm('Delete this group?')">
    @csrf
    @method('DELETE')
    <button type="submit" onclick="event.stopPropagation()"
            class="bg-red-500 text-white px-3 py-1 rounded text-sm">
        Delete
    </button>
</form>


                </div>

            </div>
            @endforeach

        </div>
        @else
            <p class="text-white">No study groups yet.</p>
        @endif

    </main>
</div>

<!-- MODAL -->
<div id="groupModal"
     class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">

    <div class="bg-white rounded-2xl p-6 w-96 relative">

        <button onclick="closeModal()"
                class="absolute top-2 right-2 text-xl">&times;</button>

        <h2 id="modalName" class="text-2xl font-bold text-purple-700 mb-2"></h2>
        <p id="modalLeader" class="text-gray-700 mb-1"></p>
        <p id="modalCode" class="text-gray-700 mb-2"></p>
        <p id="modalDescription" class="text-gray-600"></p>
    </div>
</div>

<script>
const groups = @json($groups);

function openModal(id){
    const g = groups.find(x => x.id === id);
    if(!g) return;

    document.getElementById('modalName').textContent = g.name;
    document.getElementById('modalLeader').textContent = "Leader: " + (g.owner_name ?? '-');
    document.getElementById('modalCode').textContent = "Join Code: " + (g.join_code ?? '-');
    document.getElementById('modalDescription').textContent = g.description ?? '-';

    document.getElementById('groupModal').classList.remove('hidden');
    document.getElementById('groupModal').classList.add('flex');
}

function closeModal(){
    document.getElementById('groupModal').classList.add('hidden');
    document.getElementById('groupModal').classList.remove('flex');
}
</script>

</body>
</html>
