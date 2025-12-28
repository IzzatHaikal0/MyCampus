<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

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
        // This will pull the path from your .env (local) or Render Environment (production)
        $credentialsPath = env('FIREBASE_CREDENTIALS');

        $factory = (new Factory)
            ->withServiceAccount($credentialsPath)
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
        // 1. Initialize Database if it hasn't been done yet
        if (!$this->database) {
            $this->database = $this->firebaseDatabase();
        }

        // 2. Validation
        $request->validate([
            'subject_name' => 'required|string|max:255',
            'class_section' => 'required|string|max:255',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'locationmeeting_link' => 'required|string|max:255',
        ]);

        try {
            $newStart = strtotime($request->start_time);
            $newEnd = strtotime($request->end_time);

            // 3. Fetch lessons from Firebase
            $lessonsRef = $this->database->getReference("lessons")->getValue() ?? [];
            $existingLessons = [];

            foreach ($lessonsRef as $lesson) {
                if (($lesson['date'] ?? null) === $request->date) {
                    $existingLessons[] = $lesson;
                }
            }

            // 4. Check for Overlaps
            foreach ($existingLessons as $lesson) {
                $existingStart = strtotime($lesson['start_time']);
                $existingEnd = strtotime($lesson['end_time']);
                if (($newStart < $existingEnd) && ($newEnd > $existingStart)) {
                    return back()->with('error', 'Another lesson is already scheduled at this time.');
                }
            }

            // 5. Store the data in Firebase
            $newLessonRef = $this->database->getReference('lessons')->push();
            $newLessonRef->set([
                'subject_name' => $request->subject_name,
                'class_section' => $request->class_section,
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
            // This will now show the REAL error if it fails (likely a path error)
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

    /* =========================================================
       UPDATE LESSON
    ========================================================= */
    public function update(Request $request, $id)
{
    $request->validate([
        'subject_name' => 'required|string|max:255',
        'class_section' => 'required|string|max:255',
        'date' => 'required|date',
        'start_time' => 'required',
        'end_time' => 'required|string',
        'locationmeeting_link' => 'required|string|max:255',
    ]);

    $database = $this->firebaseDatabase();
    $lessonRef = $database->getReference("lessons/{$id}");
    $oldLesson = $lessonRef->getValue();

    if (!$oldLesson) {
        return back()->with('error', 'Lesson not found.');
    }

    /* ===============================
       ❌ CANCEL THIS DATE ONLY
    =============================== */
    if ($request->has('cancel_this_date')) {

        $cancelDate = $request->date;

        // Repeated lesson → cancel specific date
        if (!empty($oldLesson['repeat_frequency'])) {
            $lessonRef
                ->getChild("cancelled_dates/{$cancelDate}")
                ->set(true);
        }
        // Single lesson → cancel completely
        else {
            $lessonRef->update(['cancelled' => true]);
        }

        // 🔔 Notify students (CANCEL)
        if (!empty($oldLesson['class_section'])) {
            $notification = $this->buildNotification(
                'cancelled',
                $id,
                array_merge($oldLesson, ['date' => $cancelDate])
            );
            $this->notifyStudentsByClass($oldLesson['class_section'], $notification);
        }

        return redirect()->route('lessons.list')
            ->with('success', 'Lesson cancelled successfully.');
    }

    /* ===============================
       ✏️ NORMAL UPDATE
    =============================== */

    // Detect changes
    $timeChanged =
        $oldLesson['start_time'] !== $request->start_time ||
        $oldLesson['end_time'] !== $request->end_time;

    $locationChanged =
        $oldLesson['locationmeeting_link'] !== $request->locationmeeting_link;

    // Update lesson
    $lessonRef->update([
        'subject_name' => $request->subject_name,
        'class_section' => $request->class_section,
        'date' => $request->date,
        'start_time' => $request->start_time,
        'end_time' => $request->end_time,
        'locationmeeting_link' => $request->locationmeeting_link,
        'repeat_schedule' => $request->repeat_schedule ?? null,
        'repeat_frequency' => $request->repeat_frequency ?? null,
        'repeat_until' => $request->repeat_until ?? null,
    ]);

    /* ===============================
       🔔 NOTIFICATIONS (TIME / LOCATION)
    =============================== */
    if (!empty($oldLesson['class_section'])) {

        // ⏰ Time change
        if ($timeChanged) {
            $notification = $this->buildNotification(
                'time',
                $id,
                $oldLesson,
                $request->all()
            );
            $this->notifyStudentsByClass($oldLesson['class_section'], $notification);
        }

        // 📍 Location change
        if ($locationChanged) {
            $notification = $this->buildNotification(
                'location',
                $id,
                $oldLesson,
                $request->all()
            );
            $this->notifyStudentsByClass($oldLesson['class_section'], $notification);
        }
    }

    return redirect()->route('lessons.list')
        ->with('success', 'Lesson updated successfully.');
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
    public function dashboard()
    {
        $lessons = $this->database->getReference('lessons')->getValue() ?? [];
        return view('teacher.dashboard', ['lessons' => $lessons]);
    }

    public function teacherDashboard()
{
    $teacherId = session('firebase_user.uid');
    $lessonsRef = $this->database->getReference('lessons')->getValue() ?? [];

    $today = Carbon::now('Asia/Kuala_Lumpur')->startOfDay();
    $todayString = $today->toDateString();
    $lessonsToday = [];

    foreach ($lessonsRef as $id => $lesson) {

        if (($lesson['teacher_id'] ?? null) !== $teacherId) continue;
        if (empty($lesson['date'])) continue;

        // ❌ Skip cancelled single lesson
        if (!empty($lesson['cancelled'])) continue;

        // ❌ Skip cancelled repeated lesson for today
        if (!empty($lesson['cancelled_dates'][$todayString])) continue;

        $lessonDate = Carbon::parse($lesson['date'], 'Asia/Kuala_Lumpur')->startOfDay();

        // ✅ NORMAL lesson today
        if ($lessonDate->equalTo($today)) {
            $lesson['id'] = $id;
            $lessonsToday[] = $lesson;
            continue;
        }

        // ✅ REPEATED lessons (UNCHANGED)
        if (!empty($lesson['repeat_frequency']) && !empty($lesson['repeat_until'])) {
            $start = Carbon::parse($lesson['date'], 'Asia/Kuala_Lumpur')->startOfDay();
            $end   = Carbon::parse($lesson['repeat_until'], 'Asia/Kuala_Lumpur')->endOfDay();

            if ($today->between($start, $end)) {
                if (
                    $lesson['repeat_frequency'] === 'daily' ||
                    ($lesson['repeat_frequency'] === 'weekly' &&
                     $today->dayOfWeek === $start->dayOfWeek)
                ) {
                    $lessonCopy = $lesson;
                    $lessonCopy['date'] = $todayString;
                    $lessonCopy['id'] = $id;
                    $lessonsToday[] = $lessonCopy;
                }
            }
        }
    }

    usort($lessonsToday, fn($a, $b) => strcmp($a['start_time'], $b['start_time']));

    return view('teacher.dashboard', ['lessons' => $lessonsToday]);
}


    public function studentDashboard()
{
    $user = session('firebase_user');
    if (!$user) return redirect('/login');

    $student = $this->database->getReference('users/' . $user['uid'])->getValue();
    if (!$student || empty($student['class_section'])) {
        return view('student.dashboard', ['todayLessons' => []]);
    }

    $classSection = $student['class_section'];
    $lessonsRef = $this->database->getReference('lessons')->getValue() ?? [];

    $today = Carbon::now('Asia/Kuala_Lumpur')->startOfDay();
    $todayString = $today->toDateString();
    $todayLessons = [];

    foreach ($lessonsRef as $id => $lesson) {

        $lessonClass = $lesson['class_section'] ?? $lesson['class_title'] ?? null;
        if ($lessonClass !== $classSection || empty($lesson['date'])) continue;

        // ❌ Skip cancelled single lesson
        if (!empty($lesson['cancelled'])) continue;

        // ❌ Skip cancelled repeated lesson for today
        if (!empty($lesson['cancelled_dates'][$todayString])) continue;

        $lessonDate = Carbon::parse($lesson['date'], 'Asia/Kuala_Lumpur')->startOfDay();

        // ✅ NORMAL lesson today
        if ($lessonDate->equalTo($today)) {
            $lesson['id'] = $id;
            $todayLessons[] = $lesson;
            continue;
        }

        // ✅ REPEATED lessons (UNCHANGED)
        if (!empty($lesson['repeat_frequency']) && !empty($lesson['repeat_until'])) {
            $start = Carbon::parse($lesson['date'], 'Asia/Kuala_Lumpur')->startOfDay();
            $end   = Carbon::parse($lesson['repeat_until'], 'Asia/Kuala_Lumpur')->endOfDay();

            if ($today->between($start, $end)) {
                if (
                    $lesson['repeat_frequency'] === 'daily' ||
                    ($lesson['repeat_frequency'] === 'weekly' &&
                     $today->dayOfWeek === $start->dayOfWeek)
                ) {
                    $lessonCopy = $lesson;
                    $lessonCopy['date'] = $todayString;
                    $lessonCopy['id'] = $id;
                    $todayLessons[] = $lessonCopy;
                }
            }
        }
    }

    usort($todayLessons, fn($a, $b) => strcmp($a['start_time'], $b['start_time']));

    return view('student.dashboard', ['todayLessons' => $todayLessons]);
}

   public function studentTimetable(Request $request)
{
    $user = Session::get('firebase_user');
    if (!$user) return redirect('/login')->with('error', 'Please login first');

    $studentId = $user['uid'];
    $student = $this->database->getReference('users/' . $studentId)->getValue();

    if (!$student || empty($student['class_section'])) {
        return view('lessonscheduling.viewlesson', [
            'lessons' => [],
            'classSection' => null,
            'error' => 'You are not assigned to any class.'
        ]);
    }

    $classSection = $student['class_section'];
    $lessonsRef = $this->database->getReference('lessons')->getValue() ?? [];
    $lessons = [];

    foreach ($lessonsRef as $id => $lesson) {

        $lessonClass = $lesson['class_section'] ?? $lesson['class_title'] ?? null;
        if ($lessonClass !== $classSection || empty($lesson['date'])) continue;

        // ❌ Skip cancelled single lesson
        if (!empty($lesson['cancelled'])) continue;

        $startDate = Carbon::parse($lesson['date'], 'Asia/Kuala_Lumpur')->startOfDay();

        // ✅ 1️⃣ NORMAL lesson
        $lessonCopy = $lesson;
        $lessonCopy['id'] = $id;
        $lessons[] = $lessonCopy;

        // ✅ 2️⃣ REPEATED lessons
        if (!empty($lesson['repeat_frequency']) && !empty($lesson['repeat_until'])) {

            $endDate = Carbon::parse($lesson['repeat_until'], 'Asia/Kuala_Lumpur')->endOfDay();
            $current = $startDate->copy()->addDay();

            while ($current->lte($endDate)) {

                // ❌ Skip cancelled repeated date
                if (!empty($lesson['cancelled_dates'][$current->toDateString()])) {
                    $current->addDay();
                    continue;
                }

                if (
                    $lesson['repeat_frequency'] === 'daily' ||
                    (
                        $lesson['repeat_frequency'] === 'weekly' &&
                        $current->dayOfWeek === $startDate->dayOfWeek
                    )
                ) {
                    $repeatLesson = $lesson;
                    $repeatLesson['date'] = $current->toDateString();
                    $repeatLesson['id'] = $id;
                    $lessons[] = $repeatLesson;
                }

                $current->addDay();
            }
        }
    }

    // ✅ Sort by date then start time
    usort($lessons, fn($a, $b) =>
        strcmp($a['date'], $b['date']) ?: strcmp($a['start_time'], $b['start_time'])
    );

    return view('lessonscheduling.viewlesson', [
        'lessons' => $lessons,
        'classSection' => $classSection
    ]);
}

  /* =========================================================
   NOTIFICATIONS
========================================================= */
private function notifyStudentsByClass($classSection, $notification)
{
    $users = $this->database->getReference('users')->getValue() ?? [];
    foreach ($users as $uid => $user) {
        if (($user['role'] ?? null) === 'student' && ($user['class_section'] ?? null) === $classSection) {
            // Push notification with read=false
            $this->database->getReference("notifications/{$uid}")->push(array_merge($notification, ['read' => false]));
        }
    }
}

private function buildNotification($type, $lessonId, $lesson, $newData = [])
{
    $createdAt = now()->toIso8601String(); // ISO format timestamp
    $classDate = $lesson['date'] ?? ($newData['date'] ?? 'N/A'); // get the lesson date

    switch ($type) {
        case 'cancelled':
            return [
                'type' => 'lesson_cancelled',
                'title' => 'Class Cancelled',
                'message' => "{$lesson['subject_name']} class on {$classDate} has been cancelled.",
                'lesson_id' => $lessonId,
                'class_date' => $classDate, // <-- include class date
                'read' => false,
                'created_at' => $createdAt,
            ];
        case 'time':
            return [
                'type' => 'lesson_time_changed',
                'title' => 'Class Time Changed',
                'message' => "{$lesson['subject_name']} class on {$classDate} changed time to {$newData['start_time']} - {$newData['end_time']}.",
                'lesson_id' => $lessonId,
                'class_date' => $classDate, // <-- include class date
                'read' => false,
                'created_at' => $createdAt,
            ];
        case 'location':
            return [
                'type' => 'lesson_location_changed',
                'title' => 'Class Location Changed',
                'message' => "{$lesson['subject_name']} class on {$classDate} location updated to {$newData['locationmeeting_link']}.",
                'lesson_id' => $lessonId,
                'class_date' => $classDate, // <-- include class date
                'read' => false,
                'created_at' => $createdAt,
            ];
    }
    return [];
}

/* =========================================================
   FETCH STUDENT NOTIFICATIONS
========================================================= */
public function studentNotifications()
{
    $user = session('firebase_user');
    if (!$user) return redirect('/login');

    $notificationsRef = $this->database->getReference('notifications/' . $user['uid'])->getValue() ?? [];
    
    $notifications = collect($notificationsRef)
        ->sortByDesc('created_at') // newest first
        ->take(50) // limit last 50
        ->all();

    return view('student.notifications', compact('notifications'));
}

/* =========================================================
   MARK NOTIFICATION AS READ
========================================================= */
public function markNotificationRead($notificationId)
{
    $user = session('firebase_user');
    if (!$user) return response()->json(['error' => 'Unauthorized'], 401);

    $this->database->getReference("notifications/{$user['uid']}/{$notificationId}")
        ->update(['read' => true]);

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
