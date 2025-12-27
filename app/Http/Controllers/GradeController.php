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

    public function viewGrade(Request $request)
    {
        $studentId = $this->getFirebaseUserId();

        if (!$studentId) {
            return redirect()->route('login')
                ->with('error', 'Please log in to view assignments.');
        }

        try {
            // 1. Get Student Data
            $studentData = $this->database->getReference('users/' . $studentId)->getValue();
            
            // 2. Get all Grades and all Assignments
            $allGrades = $this->database->getReference('assignment_grade')->getValue();
            $allAssignments = $this->database->getReference('assignments')->getValue();

            $studentGrades = [];

            if ($allGrades) {
                foreach ($allGrades as $gradeId => $grade) {
                    // Filter only grades belonging to this student
                    if (isset($grade['student_id']) && $grade['student_id'] === $studentId) {
                        
                        $assignmentId = $grade['assignment_id'] ?? null;
                        $assignmentInfo = $allAssignments[$assignmentId] ?? null;

                        // Combine grade data with assignment name/points for the view
                        $studentGrades[] = [
                            'grade_id' => $gradeId,
                            'assignment_name' => $assignmentInfo['assignment_name'] ?? 'Unknown Assignment',
                            'total_points' => $assignmentInfo['total_points'] ?? 100,
                            'grade' => $grade['grade'],
                            'feedback' => $grade['feedback'] ?? 'No feedback provided',
                            'status' => $grade['status'] ?? 'Graded',
                            'graded_at' => $grade['graded_at'] ?? 'N/A'
                        ];
                    }
                }
            }

            // Sort by most recent grade
            usort($studentGrades, function($a, $b) {
                return strtotime($b['graded_at']) - strtotime($a['graded_at']);
            });

            return view('ManageAssignment.StudentViewGrade', [
                'grades' => $studentGrades,
                'studentName' => $studentData['name'] ?? 'Student'
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error fetching grades: ' . $e->getMessage());
        }
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
            $submissionRef = $this->database->getReference('assignments_submission/' . $id);
            $submissionData = $submissionRef->getValue();

            if (!$submissionData) return redirect()->back()->with('error', 'Submission not found.');

            $gradingData = [
                'submission_id' => $id, // Store this to make editing easy!
                'assignment_id' => $submissionData['assignment_id'],
                'student_id' => $submissionData['student_id'],
                'grade' => $request->grade,
                'feedback' => $request->feedback,
                'status' => $request->status,
                'graded_at' => now()->toDateTimeString(),
            ];

            // Push new grade
            $this->database->getReference('assignment_grade')->push($gradingData);
            
            return redirect()->back()->with('success', 'Grading submitted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function editGrading(Request $request, $id) // $id is the submission_id
    {
        $request->validate([
            'grade' => 'required|numeric',
            'feedback' => 'required|string',
            'status' => 'required|string',
        ]);

        try {
            // 1. Get submission info to know who we are grading
            $submissionData = $this->database->getReference('assignments_submission/' . $id)->getValue();
            
            if (!$submissionData) return redirect()->back()->with('error', 'Submission not found.');

            $assignmentId = $submissionData['assignment_id'];
            $studentId = $submissionData['student_id'];

            // 2. Find the grade record where assignment_id and student_id match
            $allGrades = $this->database->getReference('assignment_grade')->getValue();
            $targetGradeKey = null;

            if ($allGrades) {
                foreach ($allGrades as $key => $grade) {
                    if (($grade['assignment_id'] ?? '') === $assignmentId && 
                        ($grade['student_id'] ?? '') === $studentId) {
                        $targetGradeKey = $key;
                        break;
                    }
                }
            }

            if (!$targetGradeKey) {
                return redirect()->back()->with('error', 'Original grade record not found in database.');
            }

            // 3. Update the existing grade
            $this->database->getReference('assignment_grade/' . $targetGradeKey)->update([
                'grade' => $request->grade,
                'feedback' => $request->feedback,
                'status' => $request->status,
                'graded_at' => now()->toDateTimeString(), // Update the timestamp
            ]);

            return redirect()->back()->with('success', 'Grade updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update: ' . $e->getMessage());
        }
    }


}