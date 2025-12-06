<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;

class AssignmentController extends Controller
{
    protected $database;

    public function __construct()
    {
        $credentialsPath = realpath(env('FIREBASE_CREDENTIALS'));

        if (!$credentialsPath || !file_exists($credentialsPath)) {
            throw new \Exception("Firebase credentials not found at: {$credentialsPath}");
        }

        $firebase = (new Factory)
            ->withServiceAccount($credentialsPath)
            ->withDatabaseUri(env('FIREBASE_DATABASE_URL'));

        $this->auth = $firebase->createAuth();
        $this->database = $firebase->createDatabase();
    }

    public function list()
    {
        return view('ManageAssignment.TeacherListAssignment');
    }
    // Show Add Lesson Form
    public function create()
    {
        return view('ManageAssignment.TeacherAddAssignment');
    }

    // Store Lesson in Firebase
    public function store(Request $request)
    {
        // Validate inputs
        $request->validate([
            'subject_name' => 'required|string',
            'class_title' => 'required|string',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'locationmeeting_link' => 'required|string',
            'repeat_schedule' => 'nullable|boolean',
            'repeat_frequency' => 'nullable|string',
            'repeat_until' => 'nullable|date',
        ]);

        // Prepare lesson data
        $lessonData = [
            'subject_name' => $request->subject_name,
            'class_title' => $request->class_title,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'locationmeeting_link' => $request->locationmeeting_link,
        ];

        // Store main lesson
        $ref = $this->database->getReference('lessons')->push($lessonData);

        // Handle repeated lessons if any
        if ($request->repeat_schedule && $request->repeat_frequency && $request->repeat_until) {
            $startDate = new \DateTime($request->date);
            $endDate = new \DateTime($request->repeat_until);

            while ($startDate < $endDate) {
                if ($request->repeat_frequency === 'weekly') {
                    $startDate->modify('+1 week');
                } elseif ($request->repeat_frequency === 'daily') {
                    $startDate->modify('+1 day');
                } // add more frequencies if needed

                $lessonData['date'] = $startDate->format('Y-m-d');
                $lessonData['subject_name'] .= ' (Repeated)';
                
                $this->database->getReference('lessons')->push($lessonData);
            }
        }

        return redirect()->back()->with('success', 'Lesson added successfully!');
    }
}
