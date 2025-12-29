<!-- resources/views/communicationhub/hub.blade.php -->
<div id="communicationHub" class="fixed bottom-6 right-6 w-80 lg:w-96 bg-white dark:bg-[#1a1a1a] rounded-xl shadow-lg overflow-hidden z-50 flex flex-col">
    <!-- Hub Header -->
    <div class="flex justify-between items-center p-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="font-semibold text-gray-800 dark:text-gray-100">Communication Hub</h3>
        <button id="hubClose" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">&times;</button>
    </div>

    <!-- Hub Tabs -->
    <div class="flex border-b border-gray-200 dark:border-gray-700">
        <button class="hub-tab flex-1 py-2 text-center text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800" data-tab="messages">Messages</button>
        <button class="hub-tab flex-1 py-2 text-center text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800" data-tab="notifications">Notifications</button>
        <button class="hub-tab flex-1 py-2 text-center text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800" data-tab="announcements">Announcements</button>
    </div>

    <!-- Hub Content -->
    <div class="hub-content flex-1 overflow-y-auto">
        @include('communicationhub.messages')
        @include('communicationhub.notifications')
        @include('communicationhub.announcements')
    </div>
</div>
