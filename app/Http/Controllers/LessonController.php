<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Kreait\Firebase\Auth as FirebaseAuth;

class LessonController extends Controller
{
    protected $database;

    public function __construct()
    {
        $credentialsPath = env('FIREBASE_CREDENTIALS');

        if (!file_exists($credentialsPath)) {
            throw new \Exception("Firebase credentials not found at: {$credentialsPath}");
        }

        $firebase = (new Factory)
            ->withServiceAccount($credentialsPath)
            ->withDatabaseUri(env('FIREBASE_DATABASE_URL'));

        $this->database = $firebase->createDatabase();
    }

    protected function firebaseDatabase()
    {
        $factory = (new Factory)
            ->withServiceAccount(base_path('firebase_credentials.json'))
            ->withDatabaseUri(env('FIREBASE_DATABASE_URL'));

        return $factory->createDatabase();
    }

    /* =========================================================
       CREATE LESSON VIEW
    ========================================================= */
    public function create()
    {
        return view('lessonscheduling.addlesson');
    }

    /* =========================================================
       STORE LESSON
    ========================================================= */
    public function store(Request $request)
    {
        // Validation
        $request->validate([
            'subject_name' => 'required|string|max:255',
            'class_section' => 'required|string|max:255', // updated
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'locationmeeting_link' => 'required|string|max:255',
        ]);

        try {
            $newStart = strtotime($request->start_time);
            $newEnd = strtotime($request->end_time);

            // Get existing lessons
            $lessonsRef = $this->database->getReference("lessons")->getValue() ?? [];
            $existingLessons = [];

            foreach ($lessonsRef as $lesson) {
                if (($lesson['date'] ?? null) === $request->date) {
                    $existingLessons[] = $lesson;
                }
            }

            // Check overlap
            foreach ($existingLessons as $lesson) {
                $existingStart = strtotime($lesson['start_time']);
                $existingEnd = strtotime($lesson['end_time']);
                if (($newStart < $existingEnd) && ($newEnd > $existingStart)) {
                    return back()->with('error', 'Another lesson is already scheduled at this time.');
                }
            }

            // Store lesson
            $lessonRef = $this->database->getReference('lessons')->push();
            $lessonRef->set([
                'subject_name' => $request->subject_name,
                'class_section' => $request->class_section, // always use class_section
                'date' => $request->date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'locationmeeting_link' => $request->locationmeeting_link,
                'teacher_id' => session('firebase_user.uid'),
                'repeat_schedule' => $request->repeat_schedule ?? null,
                'repeat_frequency' => $request->repeat_frequency ?? null,
                'repeat_until' => $request->repeat_until ?? null,
            ]);

            return redirect()->route('lessons.add')->with('success', 'Lesson added successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to add lesson: ' . $e->getMessage());
        }
    }

    /* =========================================================
       EDIT LESSON
    ========================================================= */
    public function edit($id)
    {
        $lesson = $this->firebaseDatabase()->getReference('lessons/' . $id)->getValue();

        if (!$lesson) {
            return back()->with('error', 'Lesson not found.');
        }

        return view('lessonscheduling.edit', [
            'id' => $id,
            'lesson' => $lesson
        ]);
    }

public function update(Request $request, $id)
{
    $lessonRef = $this->database->getReference("lessons/{$id}");
    $lesson = $lessonRef->getValue();

    if (!$lesson) {
        return back()->with('error', 'Lesson not found.');
    }

    // Prepare updated data
    $updatedData = [
        'subject_name' => $request->subject_name,
        'class_section' => $request->class_section,
        'date' => $request->date,
        'start_time' => $request->start_time,
        'end_time' => $request->end_time,
        'locationmeeting_link' => $request->locationmeeting_link,
        'repeat_schedule' => $request->has('repeat_schedule') ? 1 : 0,
        'repeat_frequency' => $request->repeat_frequency ?? null,
        'repeat_until' => $request->repeat_until ?? null,
    ];

    $editThisDateOnly = $request->has('edit_this_date_only');
    $cancelThisDate = $request->has('cancel_this_date');

    $notificationsToSend = [];

    // -------------------------------
    // 1️⃣ Cancel lesson for this date only
    // -------------------------------
    if ($cancelThisDate) {
        $notification = $this->buildNotification('cancelled', $id, $lesson, [
            'date' => $request->date
        ]);

        if (!empty($lesson['repeat_schedule'])) {
            // recurring lesson: add to cancelled_dates
            $lessonRef->getChild('cancelled_dates')->update([
                $request->date => true
            ]);
        } else {
            // single lesson: remove completely
            $lessonRef->remove();
        }

        // Notify students
        $this->notifyStudentsByClass($lesson['class_section'], $notification);

        return back()->with('success', 'Lesson cancelled for selected date.');
    }

    // -------------------------------
    // 2️⃣ Edit repeated lesson "this date only"
    // -------------------------------
    if ($editThisDateOnly && !empty($lesson['repeat_schedule'])) {
        // Save the override for this date
        $overridesRef = $lessonRef->getChild('overrides');
        $overridesRef->update([
            $request->date => [
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'locationmeeting_link' => $request->locationmeeting_link,
                'subject_name' => $request->subject_name,
            ]
        ]);

        // Build notifications for changes
        if (($request->start_time != $lesson['start_time']) || ($request->end_time != $lesson['end_time'])) {
            $notificationsToSend[] = $this->buildNotification('time', $id, $lesson, [
                'date' => $request->date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time
            ]);
        }

        if ($request->locationmeeting_link != $lesson['locationmeeting_link']) {
            $notificationsToSend[] = $this->buildNotification('location', $id, $lesson, [
                'date' => $request->date,
                'locationmeeting_link' => $request->locationmeeting_link
            ]);
        }
    } else {
        // -------------------------------
        // 3️⃣ Update lesson normally (single or full recurring)
        // -------------------------------
        $lessonRef->update($updatedData);

        // Determine changes for notification
        if (($request->start_time != $lesson['start_time']) || ($request->end_time != $lesson['end_time'])) {
            $notificationsToSend[] = $this->buildNotification('time', $id, $updatedData, [
                'date' => $request->date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time
            ]);
        }

        if ($request->locationmeeting_link != $lesson['locationmeeting_link']) {
            $notificationsToSend[] = $this->buildNotification('location', $id, $updatedData, [
                'date' => $request->date,
                'locationmeeting_link' => $request->locationmeeting_link
            ]);
        }
    }

    // -------------------------------
    // 4️⃣ Send notifications
    // -------------------------------
    foreach ($notificationsToSend as $notification) {
        $this->notifyStudentsByClass($updatedData['class_section'], $notification);
    }

   return back()->with('success', 'Lesson updated successfully.');

}

    /* =========================================================
       DELETE LESSON
    ========================================================= */
    public function destroy($id)
{
    $database = $this->firebaseDatabase();
    $lesson = $database->getReference('lessons/' . $id)->getValue();

    if (!$lesson) {
        return back()->with('error', 'Lesson not found.');
    }

    // ❌ PERMANENT DELETE (NO NOTIFICATION)
    $database->getReference('lessons/' . $id)->remove();

    return redirect()->route('lessons.list')
        ->with('success', 'Lesson deleted permanently.');
}


    /* =========================================================
       LIST LESSONS
    ========================================================= */
    public function index()
    {
        $lessonsData = $this->database->getReference('lessons')->getValue() ?? [];
        $lessons = collect($lessonsData)->map(function ($lesson, $id) {
            $lesson['class_section'] = $lesson['class_section'] ?? $lesson['class_title'] ?? null;
            return array_merge(['id' => $id], $lesson);
        })->all();

        return view('lessonscheduling.list', ['lessons' => $lessons]);
    }

    public function list()
    {
        $teacherId = session('firebase_user.uid');
        $lessonsRef = $this->database->getReference('lessons')->getValue() ?? [];
        $lessons = [];

        foreach ($lessonsRef as $id => $lesson) {
            if (($lesson['teacher_id'] ?? null) === $teacherId) {
                $lesson['class_section'] = $lesson['class_section'] ?? $lesson['class_title'] ?? null;
                $lessons[$id] = $lesson;
            }
        }

        return view('lessonscheduling.list', compact('lessons'));
    }

    /* =========================================================
       DASHBOARDS
    ========================================================= */
 
  public function teacherDashboard()
{
    $teacherId = session('firebase_user.uid');
    $lessonsRef = $this->database->getReference('lessons')->getValue() ?? [];

    $today = Carbon::now('Asia/Kuala_Lumpur')->toDateString();
    $lessonsToday = [];

    foreach ($lessonsRef as $id => $lesson) {

        // Apply override first (important!)
        $lessonCopy = $lesson;
        if (!empty($lesson['overrides'][$today])) {
            $lessonCopy = array_merge($lessonCopy, $lesson['overrides'][$today]);
        }

        // Skip lessons not assigned to this teacher
        if (($lessonCopy['teacher_id'] ?? null) !== $teacherId) continue;

        // Skip cancelled lessons
        if (!empty($lessonCopy['cancelled'])) continue;
        if (!empty($lessonCopy['cancelled_dates'][$today])) continue;

        // Must have a valid date
        if (empty($lessonCopy['date'])) continue;
        $lessonDate = Carbon::parse($lessonCopy['date'])->toDateString();

        // 1️⃣ Single (non-repeated) lesson
        if (empty($lessonCopy['repeat_frequency']) && $lessonDate === $today) {
            $lessonCopy['id'] = $id;
            $lessonsToday[] = $lessonCopy;
            continue; // no need to check repeated rules
        }

        // 2️⃣ Repeated lessons
        if (!empty($lessonCopy['repeat_frequency'])) {
            $start = Carbon::parse($lessonCopy['date'])->toDateString();
            $repeatUntil = !empty($lessonCopy['repeat_until'])
                ? Carbon::parse($lessonCopy['repeat_until'])->toDateString()
                : $start;

            if ($today >= $start && $today <= $repeatUntil) {
                $addLesson = false;

                switch ($lessonCopy['repeat_frequency']) {
                    case 'daily':
                        $addLesson = true;
                        break;
                    case 'weekly':
                        $startDow = Carbon::parse($lessonCopy['date'])->dayOfWeek;
                        if (Carbon::parse($today)->dayOfWeek === $startDow) $addLesson = true;
                        break;
                    case 'monthly':
                        $startDay = Carbon::parse($lessonCopy['date'])->day;
                        if (Carbon::parse($today)->day === $startDay) $addLesson = true;
                        break;
                }

                if ($addLesson) {
                    $lessonCopy['id'] = $id;
                    $lessonCopy['date'] = $today;
                    $lessonsToday[] = $lessonCopy;
                }
            }
        }
    }

    // Sort by start time
    usort($lessonsToday, fn($a, $b) => strcmp($a['start_time'] ?? '', $b['start_time'] ?? ''));

    return view('teacher.dashboard', ['lessons' => $lessonsToday]);
}


public function studentDashboard()
{
    $user = session('firebase_user');
    if (!$user) return redirect('/login');

    $firebaseUser = $user;
    $studentUid = $firebaseUser['uid'];

    // -----------------------------
    // Generate Firebase custom token
    // -----------------------------
    // Since you don't have $this->auth here, we use Factory temporarily for token creation
    $factory = (new \Kreait\Firebase\Factory())
        ->withServiceAccount(env('FIREBASE_CREDENTIALS'));
    $auth = $factory->createAuth();
    $customToken = $auth->createCustomToken($studentUid);

    // -----------------------------
    // Get student data from database
    // -----------------------------
    $studentData = $this->database->getReference("users/{$studentUid}")->getValue() ?? [];
    $classSection = $studentData['class_section'] ?? null;

    if (!$classSection) {
        return view('student.dashboard', [
            'firebase_custom_token' => $customToken->toString(),
            'firebase_user' => $firebaseUser,
            'todayLessons' => [],
            'studentsToday' => [],
        ]);
    }

    // -----------------------------
    // Today's lessons
    // -----------------------------
    $lessonsRef = $this->database->getReference('lessons')->getValue() ?? [];
    $today = \Carbon\Carbon::now('Asia/Kuala_Lumpur')->startOfDay();
    $todayString = $today->toDateString();
    $todayLessons = [];

    foreach ($lessonsRef as $id => $lesson) {
        $lessonClass = $lesson['class_section'] ?? $lesson['class_title'] ?? null;
        if ($lessonClass !== $classSection || empty($lesson['date'])) continue;

        // Skip cancelled lessons
        if (!empty($lesson['cancelled'])) continue;
        if (!empty($lesson['cancelled_dates'][$todayString])) continue;

        $baseDate = \Carbon\Carbon::parse($lesson['date'], 'Asia/Kuala_Lumpur')->startOfDay();

        // Apply overrides
        $lessonCopy = $lesson;
        if (!empty($lesson['overrides'][$todayString])) {
            $lessonCopy = array_merge($lessonCopy, $lesson['overrides'][$todayString]);
        }

        // Single lesson today
        if ($baseDate->equalTo($today)) {
            $lessonCopy['id'] = $id;
            $todayLessons[] = $lessonCopy;
            continue;
        }

        // Repeated lessons
        $repeat = $lesson['repeat_frequency'] ?? null;
        $repeatUntil = !empty($lesson['repeat_until'])
            ? \Carbon\Carbon::parse($lesson['repeat_until'], 'Asia/Kuala_Lumpur')->endOfDay()
            : $baseDate->copy();

        if ($repeat && $today->between($baseDate, $repeatUntil)) {
            $isRepeatedToday = false;

            if ($repeat === 'daily') $isRepeatedToday = true;
            elseif ($repeat === 'weekly' && $today->dayOfWeek === $baseDate->dayOfWeek) $isRepeatedToday = true;

            if ($isRepeatedToday) {
                $lessonCopy['date'] = $todayString;
                $lessonCopy['id'] = $id;
                $todayLessons[] = $lessonCopy;
            }
        }
    }

    usort($todayLessons, fn($a, $b) => strcmp($a['start_time'], $b['start_time']));

    // -----------------------------
    // Students in class today
    // -----------------------------
    $allUsers = $this->database->getReference('users')->getValue() ?? [];
    $studentsToday = [];

    foreach ($allUsers as $uid => $u) {
        if (($u['role'] ?? null) === 'student' &&
            strtolower(trim($u['class_section'] ?? '')) === strtolower(trim($classSection))) {
            $studentsToday[] = $u;
        }
    }

    // -----------------------------
    // Return to Blade
    // -----------------------------
    return view('student.dashboard', [
        'firebase_custom_token' => $customToken->toString(),
        'firebase_user' => $firebaseUser,
        'todayLessons' => $todayLessons,
        'studentsToday' => $studentsToday,
    ]);
}


   public function studentTimetable(Request $request)
{
    try {
        $studentId = session('firebase_user.uid');
        if (!$studentId) return redirect('/login');

        $student = $this->database
            ->getReference('users/' . $studentId)
            ->getValue();

        if (!$student || empty($student['class_section'])) {
            return view('lessonscheduling.viewlesson', [
                'lessons' => [],
                'error' => 'You are not assigned to any class.'
            ]);
        }

        $classSection = $student['class_section'];

        $month = $request->get('month')
            ? Carbon::parse($request->get('month'))
            : Carbon::now();

        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth   = $month->copy()->endOfMonth();

        $lessonsRef = $this->database->getReference('lessons')->getValue() ?? [];
        $lessons = [];

        foreach ($lessonsRef as $id => $lesson) {

            // ✅ Match class section
            if (($lesson['class_section'] ?? null) !== $classSection) continue;

            // ❌ Skip cancelled single lesson
            if (!empty($lesson['cancelled'])) continue;

            if (empty($lesson['date'])) continue;

            $baseDate = Carbon::parse($lesson['date']);
            $repeat = $lesson['repeat_frequency'] ?? null;
            $repeatUntil = Carbon::parse($lesson['repeat_until'] ?? $lesson['date']);

            // ===============================
            // 1️⃣ NON-REPEATED LESSON
            // ===============================
            if (empty($repeat)) {
                if ($baseDate->between($startOfMonth, $endOfMonth)) {
                    $lessonCopy = $lesson;
                    $lessonCopy['id'] = $id;
                    $lessonCopy['date'] = $baseDate->toDateString();
                    $lessons[] = $lessonCopy;
                }
                continue;
            }

            // ===============================
            // 2️⃣ REPEATED LESSON
            // ===============================
            $current = $baseDate->copy();

            while ($current->lte($repeatUntil) && $current->lte($endOfMonth)) {

                if ($current->gte($startOfMonth)) {

                    $dateKey = $current->toDateString();

                    // ❌ Skip cancelled date
                    if (!empty($lesson['cancelled_dates'][$dateKey])) {
                        $repeat === 'daily'
                            ? $current->addDay()
                            : $current->addWeek();
                        continue;
                    }

                    // 🔁 Apply override
                  $lessonCopy = $lesson;

if (!empty($lesson['overrides'][$dateKey])) {
    $lessonCopy = array_merge($lessonCopy, $lesson['overrides'][$dateKey]);
}

                    $lessonCopy['id'] = $id;
                    $lessonCopy['date'] = $dateKey;

                    $lessons[] = $lessonCopy;
                }

                $repeat === 'daily'
                    ? $current->addDay()
                    : $current->addWeek();
            }
        }

        return view('lessonscheduling.viewlesson', compact('lessons'));

    } catch (\Exception $e) {
        return view('lessonscheduling.viewlesson', [
            'error' => 'Failed to load timetable: ' . $e->getMessage()
        ]);
    }
}

/* =========================================================
   NOTIFICATIONS
========================================================= */
private function notifyStudentsByClass($classSection, $notification)
{
    $users = $this->database->getReference('users')->getValue() ?? [];
    foreach ($users as $uid => $user) {
        // normalize class_section
        if (
            ($user['role'] ?? null) === 'student' &&
            strtolower(trim($user['class_section'] ?? '')) === strtolower(trim($classSection))
        ) {
            // Push notification with read=false
            $this->database->getReference("notifications/{$uid}")->push(array_merge($notification, ['read' => false]));
        }
    }
}


/**
 * Build a notification array ready to push to Firebase
 *
 * @param string $type      'time', 'location', 'cancelled'
 * @param string $lessonId
 * @param array  $lesson    Lesson data (after update)
 * @param array  $requestData Optional, for extra info like start/end times
 * @return array
 */
private function buildNotification(string $type, string $lessonId, array $lesson, array $requestData = [])
{
    $classDate = $requestData['date'] ?? $lesson['date'] ?? date('Y-m-d');

    $message = '';
    $title = '';

    switch($type) {
        case 'time':
            $start = $requestData['start_time'] ?? $lesson['start_time'];
            $end = $requestData['end_time'] ?? $lesson['end_time'];
            $message = "{$lesson['subject_name']} class on {$classDate} changed time to {$start} - {$end}.";
            $title = "Class Time Changed";
            break;

        case 'location':
            $location = $requestData['locationmeeting_link'] ?? $lesson['locationmeeting_link'] ?? '';
            $message = "{$lesson['subject_name']} class on {$classDate} changed location to {$location}.";
            $title = "Class Location Changed";
            break;

        case 'cancelled':
            $message = "{$lesson['subject_name']} class on {$classDate} has been cancelled.";
            $title = "Class Cancelled";
            break;

        default:
            $message = "{$lesson['subject_name']} class on {$classDate} has an update.";
            $title = "Class Updated";
    }

    return [
        'lesson_id' => $lessonId,
        'type' => "lesson_{$type}",
        'title' => $title,
        'message' => $message,
        'class_date' => $classDate,
        'read' => false,
        'created_at' => now()->toIso8601String(),
    ];
}

/* =========================================================
   FETCH STUDENT NOTIFICATIONS
========================================================= */
public function studentNotifications()
{
    $user = session('firebase_user');
    if (!$user) return redirect('/login');

    $notificationsRef = $this->database
        ->getReference('notifications/' . $user['uid'])
        ->getValue() ?? [];

    $notifications = collect($notificationsRef)
        ->sortByDesc('created_at') // newest first
        ->values() // 🔥 reset array index (VERY IMPORTANT)
        ->take(50)
        ->all();

    return view('student.notifications', compact('notifications'));
}

/* =========================================================
   MARK NOTIFICATION AS READ
========================================================= */
public function deleteNotification($notificationId)
{
    $user = session('firebase_user');
    if (!$user) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $this->database
        ->getReference("notifications/{$user['uid']}/{$notificationId}")
        ->remove();

    return response()->json(['success' => true]);
}


    /* =========================================================
       CHECK OVERLAP
    ========================================================= */
    public function checkOverlap(Request $request)
    {
        $date = $request->date;
        $newStart = strtotime($request->start_time);
        $newEnd = strtotime($request->end_time);

        $lessonsRef = $this->database->getReference('lessons')->getValue() ?? [];
        $overlap = false;

        foreach ($lessonsRef as $lesson) {
            if (($lesson['date'] ?? null) === $date) {
                $existingStart = strtotime($lesson['start_time']);
                $existingEnd = strtotime($lesson['end_time']);
                if (($newStart < $existingEnd) && ($newEnd > $existingStart)) {
                    $overlap = true;
                    break;
                }
            }
        }

        return response()->json(['overlap' => $overlap]);
    }

    /* =========================================================
       MIGRATE OLD LESSONS
    ========================================================= */
    public function migrateClassSection()
    {
        $factory = (new \Kreait\Firebase\Factory())
            ->withServiceAccount(base_path('firebase_credentials.json'))
            ->withDatabaseUri(env('FIREBASE_DATABASE_URL'));

        $db = $factory->createDatabase();
        $lessons = $db->getReference('lessons')->getValue() ?? [];
        $updated = 0;

        foreach ($lessons as $id => $lesson) {
            if (!isset($lesson['class_section']) && isset($lesson['class_title'])) {
                $db->getReference('lessons/' . $id)->update([
                    'class_section' => $lesson['class_title']
                ]);
                $updated++;
            }
        }

        return "Migration completed. Updated {$updated} lessons.";
    }


}

