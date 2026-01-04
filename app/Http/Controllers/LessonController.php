<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
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

    /* =========================================================
       UPDATE LESSON
    ========================================================= */
public function update(Request $request, $id)
{
    // 1️⃣ Validate input
    $request->validate([
        'subject_name' => 'required|string|max:255',
        'class_section' => 'required|string|max:255',
        'date' => 'required|date',
        'start_time' => 'required',
        'end_time' => 'required|after:start_time',
        'locationmeeting_link' => 'required|string|max:255',
    ]);

    $database = $this->firebaseDatabase();
    $lessonRef = $database->getReference("lessons/{$id}");
    $oldLesson = $lessonRef->getValue();

    if (!$oldLesson) {
        return back()->with('error', 'Lesson not found.');
    }

    $classSection = $oldLesson['class_section'] ?? null;
    $lessonDate = $request->date;

    /* ===============================
       ❌ CANCEL THIS DATE ONLY
    =============================== */
    if ($request->has('cancel_this_date')) {

        if (!empty($oldLesson['repeat_frequency'])) {
            $lessonRef->getChild("cancelled_dates/{$lessonDate}")->set(true);
        } else {
            $lessonRef->update(['cancelled' => true]);
        }

        if ($classSection) {
            $notification = $this->buildNotification(
                'cancelled',
                $id,
                array_merge($oldLesson, ['date' => $lessonDate])
            );
            $this->notifyStudentsByClass($classSection, $notification);
        }

        return redirect()->route('lessons.list')
            ->with('success', 'Lesson cancelled successfully.');
    }

    /* ===============================
       ✏️ EDIT THIS DATE ONLY (REPEATED LESSON)
    =============================== */
    if (!empty($oldLesson['repeat_frequency']) && $request->has('edit_this_date_only')) {

        // Update override for this date
        $lessonRef->getChild("overrides/{$lessonDate}")->update([
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'locationmeeting_link' => $request->locationmeeting_link,
        ]);

        // Merge override for notifications
        $updatedLesson = array_merge($oldLesson, [
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'locationmeeting_link' => $request->locationmeeting_link,
            'date' => $lessonDate,
        ]);

        if ($classSection) {
            // Notify time changes
            if ($oldLesson['start_time'] !== $request->start_time || $oldLesson['end_time'] !== $request->end_time) {
                $notification = $this->buildNotification('time', $id, $updatedLesson, $request->all());
                $this->notifyStudentsByClass($classSection, $notification);
            }

            // Notify location changes
            if ($oldLesson['locationmeeting_link'] !== $request->locationmeeting_link) {
                $notification = $this->buildNotification('location', $id, $updatedLesson, $request->all());
                $this->notifyStudentsByClass($classSection, $notification);
            }
        }

        return redirect()->route('lessons.list')
            ->with('success', 'Lesson updated for this date only.');
    }

    /* ===============================
       ✏️ NORMAL UPDATE (entire lesson/series)
    =============================== */
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

    $updatedLesson = array_merge($oldLesson, [
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

    if ($classSection) {
        // Notify time changes
        if ($oldLesson['start_time'] !== $request->start_time || $oldLesson['end_time'] !== $request->end_time) {
            $notification = $this->buildNotification('time', $id, $updatedLesson, $request->all());
            $this->notifyStudentsByClass($classSection, $notification);
        }

        // Notify location changes
        if ($oldLesson['locationmeeting_link'] !== $request->locationmeeting_link) {
            $notification = $this->buildNotification('location', $id, $updatedLesson, $request->all());
            $this->notifyStudentsByClass($classSection, $notification);
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

        // 🔁 Apply override if exists
$lessonCopy = $lesson;

if (!empty($lesson['overrides'][$todayString])) {
    $lessonCopy = array_merge($lessonCopy, $lesson['overrides'][$todayString]);
}

// ✅ NORMAL (non-repeated) lesson today ONLY
if ($lessonDate->equalTo($today) && empty($lesson['repeat_frequency'])) {
    $lessonCopy['id'] = $id;
    $lessonsToday[] = $lessonCopy;
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

        // Skip cancelled single lesson
        if (!empty($lesson['cancelled'])) continue;

        // Skip cancelled repeated lesson for today
        if (!empty($lesson['cancelled_dates'][$todayString])) continue;

        $baseDate = Carbon::parse($lesson['date'], 'Asia/Kuala_Lumpur')->startOfDay();

        // Initialize lesson copy
        $lessonCopy = $lesson;

        // Apply override if exists for today (important: includes location)
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
                        ? Carbon::parse($lesson['repeat_until'], 'Asia/Kuala_Lumpur')->endOfDay() 
                        : $baseDate->copy();

        if ($repeat && $today->between($baseDate, $repeatUntil)) {

            $isRepeatedToday = false;

            if ($repeat === 'daily') {
                $isRepeatedToday = true;
            } elseif ($repeat === 'weekly' && $today->dayOfWeek === $baseDate->dayOfWeek) {
                $isRepeatedToday = true;
            }

            if ($isRepeatedToday) {
                $lessonCopy = $lesson;
                $lessonCopy['date'] = $todayString;

                // Apply override if exists (must include location)
                if (!empty($lesson['overrides'][$todayString])) {
                    $lessonCopy = array_merge($lessonCopy, $lesson['overrides'][$todayString]);
                }

                $lessonCopy['id'] = $id;
                $todayLessons[] = $lessonCopy;
            }
        }
    }

    // Sort by start time
    usort($todayLessons, fn($a, $b) => strcmp($a['start_time'], $b['start_time']));

    return view('student.dashboard', ['todayLessons' => $todayLessons]);
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
