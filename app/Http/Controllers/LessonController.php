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
    $factory = (new Factory)
        ->withServiceAccount(base_path('firebase_credentials.json')) // your JSON file
        ->withDatabaseUri(env('FIREBASE_DATABASE_URL')); // make sure this is in your .env

    return $factory->createDatabase();
}


    // Show Add Lesson Form
    public function create()
    {
        return view('lessonscheduling.addlesson');
    }

    // Store Lesson
    public function store(Request $request)
    {
        // Validation
        $request->validate([
            'subject_name' => 'required|string|max:255',
            'class_title' => 'required|string|max:255',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'locationmeeting_link' => 'required|string|max:255',
        ]);

        try {
            // Convert times to comparable format
            $newStart = strtotime($request->start_time);
            $newEnd = strtotime($request->end_time);

            // Get all existing lessons on the same date
            $lessonsRef = $this->database->getReference("lessons")->getValue();
            $existingLessons = [];

            if ($lessonsRef) {
                foreach ($lessonsRef as $lesson) {
                    if ($lesson['date'] === $request->date) {
                        $existingLessons[] = $lesson;
                    }
                }
            }

            // Check for overlapping times
            foreach ($existingLessons as $lesson) {
                $existingStart = strtotime($lesson['start_time']);
                $existingEnd = strtotime($lesson['end_time']);

                // Overlap condition: if new lesson start < existing end AND new lesson end > existing start
                if (($newStart < $existingEnd) && ($newEnd > $existingStart)) {
                    return back()->with('error', 'Another lesson is already scheduled at this time.');
                }
            }

            // Store the new lesson in Firebase
        $lessonRef = $this->database->getReference('lessons')->push();
        $lessonRef->set([
        'subject_name' => $request->subject_name,
        'class_title' => $request->class_title,
        'date' => $request->date,
        'start_time' => $request->start_time,
        'end_time' => $request->end_time,
        'locationmeeting_link' => $request->locationmeeting_link,
        'teacher_id' => session('firebase_user.uid'), // <-- Add this line
        ]);


            return redirect()->route('lessons.add')->with('success', 'Lesson added successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to add lesson: ' . $e->getMessage());
        }
    }

    // AJAX check for overlapping lessons
    public function checkOverlap(Request $request)
    {
        $date = $request->date;
        $newStart = strtotime($request->start_time);
        $newEnd = strtotime($request->end_time);

        $lessonsRef = $this->database->getReference('lessons')->getValue();
        $overlap = false;

        if ($lessonsRef) {
            foreach ($lessonsRef as $lesson) {
                if ($lesson['date'] === $date) {
                    $existingStart = strtotime($lesson['start_time']);
                    $existingEnd = strtotime($lesson['end_time']);

                    if (($newStart < $existingEnd) && ($newEnd > $existingStart)) {
                        $overlap = true;
                        break;
                    }
                }
            }
        }

        return response()->json(['overlap' => $overlap]);
    }

    public function dashboard()
{
    $lessons = $this->database->getReference('lessons')->getValue() ?? [];
   return view('teacher.dashboard', ['lessons' => $lessons]);

}

public function teacherDashboard()
{
    $firebase = (new Factory)
        ->withServiceAccount(base_path('firebase_credentials.json'))
        ->withDatabaseUri(env('FIREBASE_DATABASE_URL'));

    $database = $firebase->createDatabase();

    $lessonsRef = $database->getReference('lessons')->getValue(); // fetch all lessons

    $today = Carbon::today()->toDateString();
    $teacherId = session('firebase_user.uid'); // get logged-in teacher's Firebase UID

    $lessons = [];

    if ($lessonsRef) {
        foreach ($lessonsRef as $id => $lesson) {
            // Filter by today AND teacher_id
            if (isset($lesson['date'], $lesson['teacher_id']) 
                && $lesson['date'] === $today 
                && $lesson['teacher_id'] === $teacherId) {
                $lessons[$id] = $lesson;
            }
        }
    }

    return view('teacher.dashboard', compact('lessons'));
}

public function edit($id)
    {
        $database = $this->firebaseDatabase();

        $lesson = $database->getReference('lessons/' . $id)->getValue();

        if (!$lesson) {
            return redirect()->back()->with('error', 'Lesson not found.');
        }

        // Pass the array directly to the Blade
        return view('lessonscheduling.edit', [
            'id' => $id,
            'lesson' => $lesson
        ]);
    }

    public function update(Request $request, $id)
{
    $request->validate([
        'subject_name' => 'required|string',
        'class_title' => 'required|string',
        'date' => 'required|date',
        'start_time' => 'required|string',
        'end_time' => 'required|string',
        'locationmeeting_link' => 'required|string',
        // optional fields
        'repeat_schedule' => 'nullable|boolean',
        'repeat_frequency' => 'nullable|string',
        'repeat_until' => 'nullable|date',
    ]);

    $database = $this->firebaseDatabase();

    $database->getReference('lessons/' . $id)->update([
        'subject_name' => $request->subject_name,
        'class_title' => $request->class_title,
        'date' => $request->date,
        'start_time' => $request->start_time,
        'end_time' => $request->end_time,
        'locationmeeting_link' => $request->locationmeeting_link,
        'repeat_schedule' => $request->repeat_schedule ? 1 : 0,
        'repeat_frequency' => $request->repeat_frequency ?? null,
        'repeat_until' => $request->repeat_until ?? null,
    ]);

    return redirect()->route('lessons.list')->with('success', 'Lesson updated successfully!');
}

    public function destroy($id)
    {
        $database = $this->firebaseDatabase();

        $database->getReference('lessons/' . $id)->remove();

        return redirect()->route('lessons.list')->with('success', 'Lesson deleted successfully!');
    }

    public function index()
{
    $database = $this->firebaseDatabase();

    $lessonsData = $database->getReference('lessons')->getValue();

    // Convert Firebase array to collection for easier use in Blade
    $lessons = collect($lessonsData)->map(function ($lesson, $id) {
        return array_merge(['id' => $id], $lesson);
    })->all();

    return view('lessonscheduling.list', [
        'lessons' => $lessons
    ]);
}

public function list()
{
    $firebase = (new \Kreait\Firebase\Factory)
        ->withServiceAccount(base_path('firebase_credentials.json'))
        ->withDatabaseUri(env('FIREBASE_DATABASE_URL'));

    $database = $firebase->createDatabase();
    $lessonsRef = $database->getReference('lessons')->getValue();

    $teacherId = session('firebase_user.uid');
    $lessons = [];

    if ($lessonsRef) {
        foreach ($lessonsRef as $id => $lesson) {
            if (isset($lesson['teacher_id']) && $lesson['teacher_id'] === $teacherId) {
                $lessons[$id] = $lesson;
            }
        }
    }

    return view('lessonscheduling.list', compact('lessons'));
}



}
