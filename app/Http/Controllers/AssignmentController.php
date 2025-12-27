<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

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
        $teacherId = $this->getFirebaseUserId(); 
        
        if (!$teacherId) {
            return view('ManageAssignment.TeacherListAssignment')
                ->with('error', 'Authentication failed. Please log in.');
        }

        try {
            // Get ALL assignments
            $allAssignments = $this->database->getReference('assignments')->getValue();
            
            $assignments = [];
            
            if ($allAssignments) {
                foreach ($allAssignments as $key => $assignment) {
                    // Filter by teacherId
                    if (is_array($assignment) && isset($assignment['teacherId']) && $assignment['teacherId'] === $teacherId) {
                        $assignments[] = [
                            'id' => $key,
                            'assignment_name' => $assignment['assignment_name'] ?? '',
                            'description' => $assignment['description'] ?? '',
                            'due_date' => $assignment['due_date'] ?? '',
                            'due_time' => $assignment['due_time'] ?? '',
                            'total_points' => $assignment['total_points'] ?? 0,
                            'attachment_path' => $assignment['attachment_path'] ?? null,
                            'created_at' => $assignment['created_at'] ?? '',
                        ];
                    }
                }
                
                // Sort by due date
                usort($assignments, function($a, $b) {
                    $dateA = $a['due_date'] ? strtotime($a['due_date']) : 0;
                    $dateB = $b['due_date'] ? strtotime($b['due_date']) : 0;
                    return $dateB - $dateA;
                });
            }
            
            return view('ManageAssignment.TeacherListAssignment', compact('assignments'));
            
        } catch (\Exception $e) {
            return view('ManageAssignment.TeacherListAssignment')
                ->with('error', 'Failed to fetch assignments: ' . $e->getMessage());
        }
    }

    // Show Add Lesson Form
    public function create()
    {
        return view('ManageAssignment.TeacherAddAssignment');
    }

    // Store Lesson in Firebase
    public function store(Request $request)
    {
        $teacherId = $this->getFirebaseUserId(); 

        if (!$teacherId) {
            // Now it properly checks the custom session status
            return view('ManageAssignment.TeacherListAssignment')
                ->with('error', 'Authentication failed. Please log in.');
        }
        
        // Validate inputs
        $request->validate([
            'assignment_name' => 'required|string',
            'class_section' => 'required|string', // Added class_section validation
            'description' => 'required|string',
            'due_date' => 'required|date',
            'due_time' => 'required|date_format:H:i',
            'total_points' => 'required|integer',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx|max:10240', // max 10MB
        ]);

        // Handle file upload
        $filePath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            
            // Generate unique filename
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Store file in storage/app/public/assignments directory
            $filePath = $file->storeAs('assignments', $fileName, 'public');
            
            // Alternative: Store in public/uploads/assignments (directly accessible)
            // $file->move(public_path('uploads/assignments'), $fileName);
            // $filePath = 'uploads/assignments/' . $fileName;
        }

        // Prepare assignment data
        $assignmentData = [
            'assignment_name' => $request->assignment_name,
            'class_section' => $request->class_section, // Store class section
            'description' => $request->description,
            'due_date' => $request->due_date,
            'due_time' => $request->due_time,
            'total_points' => $request->total_points,
            'attachment_path' => $filePath, // Store file path
            'created_at' => now()->toDateTimeString(),
            'teacherId' => $teacherId,
        ];

        try {
            $this->database->getReference('assignments')->push($assignmentData);

            return redirect()->route('assignments.list')
                ->with('success', 'Assignment added successfully for class ' . $request->class_section . '!');
        } catch (\Exception $e) {
            // Return to the previous form with the error message
            return redirect()->back()->withInput()
                ->with('error', 'Failed to save assignment to database: ' . $e->getMessage());
        }
    }

    // Optional: Method to download/view attachment
    public function downloadAttachment($id)
    {
        $assignment = $this->database->getReference('assignments/' . $id)->getValue();
        
        if (!$assignment || !isset($assignment['attachment_path'])) {
            abort(404, 'Assignment or attachment not found');
        }

        $filePath = storage_path('app/public/' . $assignment['attachment_path']);
        
        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }

        return response()->download($filePath);
    }

   public function delete(Request $request, $id)
    {
        try {
            // Get assignment data from Firebase
            $assignment = $this->database->getReference('assignments/' . $id)->getValue();
            
            // Check if assignment exists
            if (!$assignment) {
                return redirect()->back()->with('error', 'Assignment not found!');
            }
            
            // Delete the file if it exists
            if (isset($assignment['attachment_path']) && $assignment['attachment_path']) {
                $filePath = storage_path('app/public/' . $assignment['attachment_path']);
                
                // Check if file exists and delete it
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            // Delete assignment from Firebase
            $this->database->getReference('assignments/' . $id)->remove();
            
            return redirect()->back()->with('success', 'Assignment deleted successfully!');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete assignment: ' . $e->getMessage());
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            // Get the logged-in teacher's ID
            $teacherId = $this->getFirebaseUserId();
            
            if (!$teacherId) {
                return redirect()->route('assignments.list')
                    ->with('error', 'Authentication failed. Please log in.');
            }

            // Fetch assignment from Firebase
            $assignmentData = $this->database->getReference('assignments/' . $id)->getValue();

            // Check if assignment exists
            if (!$assignmentData) {
                return redirect()->route('assignments.list')
                    ->with('error', 'Assignment not found!');
            }

            //  Verify that the assignment belongs to the logged-in teacher
            if (!isset($assignmentData['teacherId']) || $assignmentData['teacherId'] !== $teacherId) {
                return redirect()->route('assignments.list')
                    ->with('error', 'Unauthorized: You can only edit your own assignments!');
            }

            // Add the ID to the assignment array so it's available in the view
            $assignment = array_merge(['id' => $id], $assignmentData);

            return view('ManageAssignment.TeacherEditAssignment', compact('assignment'));
            
        } catch (\Exception $e) {
            \Log::error('Assignment edit error: ' . $e->getMessage());
            return redirect()->route('assignments.list')
                ->with('error', 'Failed to load assignment: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Get the logged-in teacher's ID
            $teacherId = $this->getFirebaseUserId();
            
            if (!$teacherId) {
                return redirect()->route('assignments.list')
                    ->with('error', 'Authentication failed. Please log in.');
            }

            // Validate inputs
            $request->validate([
                'assignment_name' => 'required|string|max:255',
                'class_section' => 'required|string', // Added class_section validation
                'description' => 'required|string',
                'due_date' => 'required|date',
                'due_time' => 'required|date_format:H:i',
                'total_points' => 'required|integer|min:0',
                'attachment' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,jpg,jpeg,png|max:10240', 
            ]);

            // Get existing assignment data from Firebase
            $existingAssignment = $this->database->getReference('assignments/' . $id)->getValue();
            
            // Check if assignment exists
            if (!$existingAssignment) {
                return redirect()->route('assignments.list')
                    ->with('error', 'Assignment not found!');
            }

            // ✅ Verify that the assignment belongs to the logged-in teacher
            if (!isset($existingAssignment['teacherId']) || $existingAssignment['teacherId'] !== $teacherId) {
                return redirect()->route('assignments.list')
                    ->with('error', 'Unauthorized: You can only update your own assignments!');
            }

            // Prepare updated assignment data
            $assignmentData = [
                'assignment_name' => $request->assignment_name,
                'class_section' => $request->class_section, // Store class section
                'description' => $request->description,
                'due_date' => $request->due_date,
                'due_time' => $request->due_time,
                'total_points' => $request->total_points,
                'updated_at' => now()->toDateTimeString(),
            ];

            // Handle file upload
            if ($request->hasFile('attachment')) {
                // Delete old file if it exists
                if (isset($existingAssignment['attachment_path']) && $existingAssignment['attachment_path']) {
                    $oldFilePath = storage_path('app/public/' . $existingAssignment['attachment_path']);
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                }

                // Upload new file
                $file = $request->file('attachment');
                $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('assignments', $fileName, 'public');
                $assignmentData['attachment_path'] = $filePath;
            } else {
                // Keep existing attachment if no new file uploaded
                if (isset($existingAssignment['attachment_path'])) {
                    $assignmentData['attachment_path'] = $existingAssignment['attachment_path'];
                }
            }

            // ✅ Preserve created_at timestamp
            if (isset($existingAssignment['created_at'])) {
                $assignmentData['created_at'] = $existingAssignment['created_at'];
            }

            // ✅ Preserve teacherId - IMPORTANT: Don't lose the teacher association
            if (isset($existingAssignment['teacherId'])) {
                $assignmentData['teacherId'] = $existingAssignment['teacherId'];
            }

            // Update assignment in Firebase
            $this->database->getReference('assignments/' . $id)->set($assignmentData);

            return redirect()->route('assignments.list')
                ->with('success', 'Assignment updated successfully for class ' . $request->class_section . '!');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
                
        } catch (\Exception $e) {
            \Log::error('Assignment update error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to update assignment: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Add this helper method to your controller to get the UID from the session
    protected function getFirebaseUserId()
    {
        // Check if the 'firebase_user' session key exists and has a 'uid'
        return Session::get('firebase_user.uid');
    }

   public function viewStudentAssignment(Request $request)
    {
        $studentId = $this->getFirebaseUserId();

        if (!$studentId) {
            return redirect()->route('login')
                ->with('error', 'Please log in to view assignments.');
        }

        try {
            // Get student's data to find their class section
            $studentData = $this->database->getReference('users/' . $studentId)->getValue();
            $studentClassSection = $studentData['class_section'] ?? null;

            if (!$studentClassSection) {
                return view('ManageAssignment.StudentListAssignment', ['assignments' => []])
                    ->with('error', 'You are not assigned to any class section.');
            }

            // Get all assignments from Firebase
            $allAssignments = $this->database->getReference('assignments')->getValue();

            // Get all submissions by this student - FORCE FRESH DATA
            $allSubmissions = $this->database->getReference('assignments_submission')->getValue();
            
            // Create a map of assignment_id => submission data for this student
            $studentSubmissions = [];
            if ($allSubmissions) {
                foreach ($allSubmissions as $submissionId => $submission) {
                    if (isset($submission['student_id']) && $submission['student_id'] === $studentId) {
                        $assignmentId = $submission['assignment_id'] ?? null;
                        if ($assignmentId) {
                            // Store the LATEST submission data
                            $studentSubmissions[$assignmentId] = [
                                'submission_id' => $submissionId,
                                'submitted_at' => $submission['submitted_at'] ?? '',
                                'status' => $submission['status'] ?? 'submitted',
                                'attachment_path' => $submission['attachment_path'] ?? null,
                                'submission_link' => $submission['submission_link'] ?? null,
                            ];
                        }
                    }
                }
            }

            $assignments = [];
            
            if ($allAssignments) {
                foreach ($allAssignments as $id => $assignment) {
                    // Filter: Only include assignments matching student's class section
                    if (isset($assignment['class_section']) && $assignment['class_section'] === $studentClassSection) {
                        
                        // Check if student has submitted this assignment
                        $hasSubmitted = isset($studentSubmissions[$id]);
                        $submissionData = $hasSubmitted ? $studentSubmissions[$id] : null;
                        
                        $assignments[] = [
                            'id' => $id,
                            'assignment_name' => $assignment['assignment_name'] ?? '',
                            'description' => $assignment['description'] ?? '',
                            'due_date' => $assignment['due_date'] ?? '',
                            'due_time' => $assignment['due_time'] ?? '',
                            'total_points' => $assignment['total_points'] ?? 0,
                            'attachment_path' => $assignment['attachment_path'] ?? null,
                            'created_at' => $assignment['created_at'] ?? '',
                            'class_section' => $assignment['class_section'] ?? '',
                            // Submission status
                            'has_submitted' => $hasSubmitted,
                            'submission' => $submissionData,
                        ];
                    }
                }
                
                // Sort by due date (newest first)
                usort($assignments, function($a, $b) {
                    $dateA = $a['due_date'] ? strtotime($a['due_date']) : 0;
                    $dateB = $b['due_date'] ? strtotime($b['due_date']) : 0;
                    return $dateB - $dateA;
                });
            }

            // Disable caching for this view
            return response()
                ->view('ManageAssignment.StudentListAssignment', compact('assignments'))
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
            
        } catch (\Exception $e) {
            return view('ManageAssignment.StudentListAssignment', ['assignments' => []])
                ->with('error', 'Failed to fetch assignments: ' . $e->getMessage());
        }
    }

    public function addSubmission(Request $request, $id)
    {
        $studentId = $this->getFirebaseUserId();

        if (!$studentId) {
            return redirect()->route('ManageAssignment.StudentListAssignment')
                ->with('error', 'Authentication failed. Please log in.');
        }

        // Validate inputs - at least one of file or link must be provided
        $request->validate([
            'submission_file' => 'nullable|file|mimes:pdf,doc,docx,zip|max:10240',
            'submission_link' => 'nullable|url|max:500',
        ]);

        // Ensure at least one submission method is provided
        if (!$request->hasFile('submission_file') && !$request->filled('submission_link')) {
            return redirect()->back()
                ->with('error', 'Please provide either a file or a link for your submission.');
        }

        try {
            // Get student's class section
            $studentData = $this->database->getReference('users/' . $studentId)->getValue();
            $studentClassSection = $studentData['class_section'] ?? null;

            if (!$studentClassSection) {
                return redirect()->back()
                    ->with('error', 'You are not assigned to any class section.');
            }

            // Get assignment data to verify it matches student's class section
            $assignmentData = $this->database->getReference('assignments/' . $id)->getValue();
            
            if (!$assignmentData) {
                return redirect()->back()
                    ->with('error', 'Assignment not found.');
            }

            // Verify student is submitting to the correct class assignment
            if (isset($assignmentData['class_section']) && $assignmentData['class_section'] !== $studentClassSection) {
                return redirect()->back()
                    ->with('error', 'You cannot submit to an assignment from a different class section.');
            }

            $filePath = null;
            if ($request->hasFile('submission_file')) {
                $file = $request->file('submission_file');
                
                // Generate unique filename
                $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                
                // Store file in storage/app/public/assignments directory
                $filePath = $file->storeAs('assignment_submission', $fileName, 'public');
            }

            // Prepare submission data
            $submissionData = [
                'assignment_id' => $id,
                'student_id' => $studentId,
                'class_section' => $studentClassSection, // Add student's class section
                'attachment_path' => $filePath,
                'submission_link' => $request->input('submission_link'),
                'submitted_at' => now()->toDateTimeString(),
                'status' => 'submitted',
            ];

            $this->database->getReference('assignments_submission')->push($submissionData);

            return redirect()->route('assignments.viewStudentAssignment')
                ->with('success', 'Assignment submitted successfully for class ' . $studentClassSection . '!');
                
        } catch (\Exception $e) {
            // Return to the previous form with the error message
            return redirect()->back()->withInput()
                ->with('error', 'Failed to save assignment to database: ' . $e->getMessage());
        }
    }


    public function editSubmission(Request $request, $id)
    {
        $studentId = $this->getFirebaseUserId();

        if (!$studentId) {
            return redirect()->route('assignments.viewStudentAssignment')
                ->with('error', 'Authentication failed. Please log in.');
        }

        // Validate inputs - both can be optional for editing
        $request->validate([
            'submission_file' => 'nullable|file|mimes:pdf,doc,docx,zip|max:10240',
            'submission_link' => 'nullable|url|max:500',
        ]);

        try {
            // Get student's class section
            $studentData = $this->database->getReference('users/' . $studentId)->getValue();
            $studentClassSection = $studentData['class_section'] ?? null;

            if (!$studentClassSection) {
                return redirect()->back()
                    ->with('error', 'You are not assigned to any class section.');
            }

            // Get assignment data to verify it matches student's class section
            $assignmentData = $this->database->getReference('assignments/' . $id)->getValue();
            
            if (!$assignmentData) {
                return redirect()->back()
                    ->with('error', 'Assignment not found.');
            }

            // Verify student is submitting to the correct class assignment
            if (isset($assignmentData['class_section']) && $assignmentData['class_section'] !== $studentClassSection) {
                return redirect()->back()
                    ->with('error', 'You cannot edit submission for an assignment from a different class section.');
            }

            // Find existing submission by this student for this assignment
            $allSubmissions = $this->database->getReference('assignments_submission')->getValue();
            $existingSubmissionId = null;
            $existingSubmission = null;

            if ($allSubmissions) {
                foreach ($allSubmissions as $submissionId => $submission) {
                    if (isset($submission['assignment_id']) && $submission['assignment_id'] === $id &&
                        isset($submission['student_id']) && $submission['student_id'] === $studentId) {
                        $existingSubmissionId = $submissionId;
                        $existingSubmission = $submission;
                        break;
                    }
                }
            }

            // If no existing submission found, redirect back
            if (!$existingSubmissionId) {
                return redirect()->back()
                    ->with('error', 'No existing submission found to edit.');
            }

            // Prepare updated submission data - start with existing data
            $submissionData = $existingSubmission;

            // Handle file upload
            if ($request->hasFile('submission_file')) {
                // Delete old file if it exists
                if (isset($existingSubmission['attachment_path']) && $existingSubmission['attachment_path']) {
                    $oldFilePath = storage_path('app/public/' . $existingSubmission['attachment_path']);
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                }

                // Upload new file
                $file = $request->file('submission_file');
                $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('assignment_submission', $fileName, 'public');
                
                $submissionData['attachment_path'] = $filePath;
            }

            // Update link if provided
            if ($request->filled('submission_link')) {
                $submissionData['submission_link'] = $request->input('submission_link');
            }

            // Update submission timestamp
            $submissionData['submitted_at'] = now()->toDateTimeString();
            $submissionData['status'] = 'submitted';

            // Preserve original data
            $submissionData['assignment_id'] = $id;
            $submissionData['student_id'] = $studentId;
            $submissionData['class_section'] = $studentClassSection;

            // Update submission in Firebase
            $this->database->getReference('assignments_submission/' . $existingSubmissionId)->set($submissionData);

            return redirect()->route('assignments.viewStudentAssignment')
                ->with('success', 'Assignment submission updated successfully for class ' . $studentClassSection . '!');
                
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to update submission: ' . $e->getMessage());
        }
    }



   public function viewListSubmission(Request $request, $id)
    {
        $teacherId = $this->getFirebaseUserId();

        if (!$teacherId) {
            return redirect()->route('assignments.list')
                ->with('error', 'Authentication failed. Please log in.');
        }

        try {
            // Get assignment data
            $assignmentData = $this->database->getReference('assignments/' . $id)->getValue();
            
            if (!$assignmentData) {
                return redirect()->route('assignments.list')
                    ->with('error', 'Assignment not found.');
            }

            // Verify the teacher owns this assignment
            if (isset($assignmentData['teacherId']) && $assignmentData['teacherId'] !== $teacherId) {
                return redirect()->route('assignments.list')
                    ->with('error', 'Unauthorized: You can only view submissions for your own assignments!');
            }

            // Get all submissions for this assignment
            $allSubmissions = $this->database->getReference('assignments_submission')->getValue();
            
            // Get all grades
            $allGrades = $this->database->getReference('assignment_grade')->getValue();
            
            $ungradedSubmissions = [];
            $gradedSubmissions = [];

            if ($allSubmissions) {
                foreach ($allSubmissions as $submissionId => $submission) {
                    if (isset($submission['assignment_id']) && $submission['assignment_id'] === $id) {
                        // Get student details
                        $studentData = $this->database->getReference('users/' . $submission['student_id'])->getValue();
                        
                        // Check if this submission has been graded
                        $gradeData = null;
                        if ($allGrades) {
                            foreach ($allGrades as $gradeId => $grade) {
                                if (isset($grade['assignment_id']) && 
                                    isset($grade['student_id']) &&
                                    $grade['assignment_id'] === $id && 
                                    $grade['student_id'] === $submission['student_id']) {
                                    $gradeData = $grade;
                                    $gradeData['grade_id'] = $gradeId;
                                    break;
                                }
                            }
                        }
                        
                        $submissionData = [
                            'submission_id' => $submissionId,
                            'student_id' => $submission['student_id'] ?? '',
                            'student_name' => $studentData['name'] ?? 'Unknown Student',
                            'student_email' => $studentData['email'] ?? 'N/A',
                            'class_section' => $submission['class_section'] ?? 'N/A',
                            'attachment_path' => $submission['attachment_path'] ?? null,
                            'submission_link' => $submission['submission_link'] ?? null,
                            'submitted_at' => $submission['submitted_at'] ?? 'N/A',
                            'status' => $submission['status'] ?? 'submitted',
                        ];
                        
                        // Add grade information if exists
                        if ($gradeData) {
                            $submissionData['grade'] = $gradeData['grade'] ?? 'N/A';
                            $submissionData['feedback'] = $gradeData['feedback'] ?? '';
                            $submissionData['grade_status'] = $gradeData['status'] ?? 'graded';
                            $submissionData['graded_at'] = $gradeData['graded_at'] ?? 'N/A';
                            $gradedSubmissions[] = $submissionData;
                        } else {
                            $ungradedSubmissions[] = $submissionData;
                        }
                    }
                }
            }

            // Sort both arrays by submitted date (newest first)
            usort($ungradedSubmissions, function($a, $b) {
                $dateA = $a['submitted_at'] !== 'N/A' ? strtotime($a['submitted_at']) : 0;
                $dateB = $b['submitted_at'] !== 'N/A' ? strtotime($b['submitted_at']) : 0;
                return $dateB - $dateA;
            });
            
            usort($gradedSubmissions, function($a, $b) {
                $dateA = $a['graded_at'] !== 'N/A' ? strtotime($a['graded_at']) : 0;
                $dateB = $b['graded_at'] !== 'N/A' ? strtotime($b['graded_at']) : 0;
                return $dateB - $dateA;
            });

            // Add assignment ID to data
            $assignment = array_merge(['id' => $id], $assignmentData);

            return view('ManageAssignment.TeacherViewSubmission', [
                'assignment' => $assignment,
                'ungradedSubmissions' => $ungradedSubmissions,
                'gradedSubmissions' => $gradedSubmissions,
                'totalSubmissions' => count($ungradedSubmissions) + count($gradedSubmissions),
                'totalGraded' => count($gradedSubmissions),
                'totalUngraded' => count($ungradedSubmissions)
            ]);

        } catch (\Exception $e) {
            return redirect()->route('assignments.list')
                ->with('error', 'Failed to load submissions: ' . $e->getMessage());
        }
    }

}