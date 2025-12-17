<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class GradeController extends Controller
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

    protected function getFirebaseUserId()
    {
        // Check if the 'firebase_user' session key exists and has a 'uid'
        return Session::get('firebase_user.uid');
    }


    public function addGrading(Request $request, $id)
    {
        $teacherId = $this->getFirebaseUserId(); 

        if (!$teacherId) {
            return view('ManageAssignment.TeacherViewSubmission')
                ->with('error', 'Authentication failed. Please log in.');
        }
        
        // Validate inputs - only the three fields needed
        $request->validate([
            'grade' => 'required|numeric',
            'feedback' => 'required|string',
            'status' => 'required|string',
        ]);

        try {
            // Fetch the submission data to get assignment_id and student_id
            $submissionData = $this->database->getReference('assignments_submission/' . $id)->getValue();
            
            if (!$submissionData) {
                return redirect()->back()
                    ->with('error', 'Submission not found.');
            }

            // Prepare grading data
            $gradingData = [
                'assignment_id' => $submissionData['assignment_id'] ?? null,
                'student_id' => $submissionData['student_id'] ?? null,
                'grade' => $request->grade,
                'feedback' => $request->feedback,
                'status' => $request->status,
                'graded_at' => now()->toDateTimeString(),
            ];

            // Update the submission with grading information
            $this->database->getReference('assignment_grade')->push($gradingData);
            
            return redirect()->back()
                ->with('success', 'Grading submitted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to save grading: ' . $e->getMessage());
        }
    }


}