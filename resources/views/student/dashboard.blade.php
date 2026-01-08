<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - ClassConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Firebase SDK -->
    <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-database-compat.js"></script>
</head>
<body class="bg-gradient-to-br from-purple-400 via-pink-500 to-red-500 min-h-screen">
    
<div class="flex">
    @include('layouts.sidebar')

    <main id="mainContent" class="flex-1 ml-72 transition-all duration-300 p-6">
        <!-- Top Header -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6 flex justify-between items-center">
            <div class="flex items-center gap-3 text-gray-600">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </div>
            <div class="flex items-center gap-6 relative">

               <!-- Notification Button & Dropdown -->
<div class="relative">
    <button id="notificationBtn" class="relative text-gray-600 hover:text-purple-600 transition focus:outline-none">
        <i class="fas fa-bell text-2xl"></i>
        <span id="notificationCount"
              class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center hidden">
            0
        </span>
    </button>

    <div id="notificationDropdown"
         class="hidden absolute right-0 mt-2 w-96 bg-white rounded-xl shadow-lg z-50 flex flex-col"
         style="max-height: calc(100vh - 120px);">
        <div class="flex justify-between items-center px-4 py-3 border-b font-semibold text-gray-700">
            <span>Notifications</span>
            <button id="markAllRead" class="text-sm text-purple-600 hover:underline">Mark all read</button>
        </div>

        <div id="notificationsList" class="flex-1 overflow-y-auto divide-y divide-gray-200"></div>
        <div id="noNotifications" class="px-4 py-6 text-center text-gray-500 text-sm">
            No notifications
        </div>
    </div>
</div>

                <!-- User Info -->
                <div class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 rounded-xl p-2 transition">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(session('firebase_user.name', 'Student')) }}&background=667eea&color=fff" alt="User" class="w-10 h-10 rounded-full">
                    <span class="font-semibold text-gray-800">{{ session('firebase_user.name', 'Student') }}</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
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

        <!-- Bottom Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
           <!-- Today's Classes -->
<div class="space-y-4">
    <div class="bg-white rounded-xl shadow-md p-4">
        <h2 class="text-lg font-semibold text-gray-800 mb-3">
            {{ empty($todayLessons) ? 'No Classes Today' : "Today's Classes" }}
        </h2>

        @if(empty($todayLessons))
            <div class="text-gray-500 flex flex-col items-center justify-center py-6 gap-2">
                <i class="fas fa-calendar-xmark text-4xl"></i>
                <span class="font-medium">Enjoy your free day!</span>
            </div>
        @else
            <div class="space-y-4">
                @foreach($todayLessons as $lesson)
                    <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                        
                        <!-- Subject -->
                        <h3 class="text-lg font-semibold text-gray-800">
                            {{ $lesson['subject_name'] ?? 'Subject' }}
                        </h3>

                        <!-- Time -->
                        <p class="text-sm text-gray-600 mt-1">
                            🕒 Time:
                            {{ $lesson['start_time'] ?? '' }} – {{ $lesson['end_time'] ?? '' }}
                        </p>

                        <!-- Location -->
                        <p class="text-sm text-gray-600 mt-1">
                            📍 Location:
                            {{ $lesson['locationmeeting_link'] ?? 'Location' }}
                        </p>

                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

            <!-- Students in Class Today -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
                    <i class="fas fa-users text-purple-600"></i>
                    Students in Class Today
                </h2>
                <div class="space-y-3 max-h-[500px] overflow-y-auto">
                    @foreach($studentsToday ?? [] as $student)
                        <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($student['name']) }}&background=667eea&color=fff" class="w-12 h-12 rounded-full">
                            <div>
                                <div class="font-semibold text-gray-800">{{ $student['name'] }}</div>
                                <div class="text-sm text-gray-500">{{ $student['class_title'] ?? '' }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="mt-8 text-center text-white opacity-75">
            <p>© 2025 ClassConnect. All rights reserved.</p>
        </footer>
    </main>
</div>

<!-- Firebase SDKs -->
<script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-database-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-auth-compat.js"></script>

<script>
  // Firebase config
  const firebaseConfig = {
    apiKey: "AIzaSyB5rsKpQJGQZ591chvLNU_xNyKVIWSBRTk",
    authDomain: "mycampus-f7b98.firebaseapp.com",
    databaseURL: "https://mycampus-f7b98-default-rtdb.asia-southeast1.firebasedatabase.app",
    projectId: "mycampus-f7b98",
    storageBucket: "mycampus-f7b98.firebasestorage.app",
    messagingSenderId: "662548896164",
    appId: "1:662548896164:web:39cc937ad9230877d34931"
  };

  firebase.initializeApp(firebaseConfig);

  const db = firebase.database();
  const auth = firebase.auth();
  const studentUid = "{{ session('firebase_user.uid') }}";
  const firebaseCustomToken = "{{ $firebase_custom_token }}";

  const notificationBtn = document.getElementById('notificationBtn');
  const notificationDropdown = document.getElementById('notificationDropdown');
  const notificationsList = document.getElementById('notificationsList');
  const notificationCount = document.getElementById('notificationCount');
  const noNotifications = document.getElementById('noNotifications');
  const markAllReadBtn = document.getElementById('markAllRead');

  let unreadCount = 0;

  // Update badge
  function updateBadge() {
    if (unreadCount > 0) {
      notificationCount.innerText = unreadCount;
      notificationCount.classList.remove('hidden');
    } else {
      notificationCount.classList.add('hidden');
    }
  }

  // Render a notification
  function renderNotification(notification, key) {
    if (document.getElementById(`noti-${key}`)) return;

    const div = document.createElement('div');
    div.id = `noti-${key}`;
    div.className = 'p-3 flex justify-between items-start gap-2 cursor-pointer transition';
    if (!notification.read) div.classList.add('rounded-lg', 'shadow-sm');

    div.innerHTML = `
      <div class="flex-1 flex flex-col gap-1">
        <div class="flex items-center justify-between">
          <span class="font-semibold px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-700 flex items-center gap-2">
            <i class="fas fa-bell"></i> Notification
          </span>
          <span class="text-xs text-gray-400">${notification.created_at ? new Date(notification.created_at).toLocaleString() : ''}</span>
        </div>
        <div class="text-sm text-gray-700 mt-1">${notification.message ?? ''}</div>
      </div>
      <button class="delete-notification text-gray-400 hover:text-red-500 ml-2 mt-1">
        <i class="fas fa-xmark"></i>
      </button>
    `;

    // Mark as read
    div.querySelector('.flex-1').addEventListener('click', () => {
      if (!notification.read) {
        db.ref(`notifications/${studentUid}/${key}`).update({ read: true });
        notification.read = true;
        div.classList.remove('shadow-sm');
        unreadCount--;
        updateBadge();
      }
    });

    // Delete notification
    div.querySelector('.delete-notification').addEventListener('click', e => {
      e.stopPropagation();
      if (!confirm('Delete this notification?')) return;
      db.ref(`notifications/${studentUid}/${key}`).remove();
      div.remove();
      if (!notification.read) unreadCount--;
      updateBadge();
      if (!notificationsList.children.length) noNotifications.classList.remove('hidden');
    });

    // Prepend latest on top
    notificationsList.prepend(div);
  }

  // Toggle dropdown
  notificationBtn.addEventListener('click', e => {
    e.stopPropagation();
    notificationDropdown.classList.toggle('hidden');
  });
  document.addEventListener('click', () => notificationDropdown.classList.add('hidden'));
  notificationDropdown.addEventListener('click', e => e.stopPropagation());

  // Mark all as read
  markAllReadBtn.addEventListener('click', () => {
    db.ref(`notifications/${studentUid}`).once('value', snapshot => {
      snapshot.forEach(child => {
        db.ref(`notifications/${studentUid}/${child.key}`).update({ read: true });
      });
      unreadCount = 0;
      updateBadge();
      document.querySelectorAll('#notificationsList .shadow-sm').forEach(div => div.classList.remove('shadow-sm'));
    });
  });

  // Sign in with custom token
  auth.signInWithCustomToken(firebaseCustomToken)
    .then(() => {
      console.log("✅ Student signed in to Firebase");

      // Load last 50 notifications
      db.ref(`notifications/${studentUid}`).limitToLast(50).once('value', snapshot => {
        notificationsList.innerHTML = '';
        unreadCount = 0;
        if (!snapshot.exists()) {
          noNotifications.classList.remove('hidden');
          updateBadge();
          return;
        }
        noNotifications.classList.add('hidden');
        snapshot.forEach(child => {
          const notification = child.val();
          const key = child.key;
          if (!notification.read) unreadCount++;
          renderNotification(notification, key);
        });
        updateBadge();
      });

      // Listen for new notifications
      db.ref(`notifications/${studentUid}`).on('child_added', snapshot => {
        const notification = snapshot.val();
        const key = snapshot.key;
        if (document.getElementById(`noti-${key}`)) return;
        noNotifications.classList.add('hidden');
        if (!notification.read) unreadCount++;
        renderNotification(notification, key);
        updateBadge();
      });
    })
    .catch(error => console.error("❌ Firebase auth failed:", error));
</script>

</body>
</html>
