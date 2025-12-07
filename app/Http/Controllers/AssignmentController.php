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
        try {
            // Fetch all assignments from Firebase
            $assignmentsRef = $this->database->getReference('assignments')->getValue();
            
            // Convert to array and add Firebase keys
            $assignments = [];
            if ($assignmentsRef) {
                foreach ($assignmentsRef as $key => $assignment) {
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
                
                // Sort by due date (descending)
                usort($assignments, function($a, $b) {
                    return strtotime($b['due_date']) - strtotime($a['due_date']);
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
        // Validate inputs
        $request->validate([
            'assignment_name' => 'required|string',
            'description' => 'required|string',
            'due_date' => 'required|date',
            'due_time' => 'required|date_format:H:i',
            'total_points' => 'required|integer',
            'attachment' => 'required|file|mimes:pdf,doc,docx|max:10240', // max 10MB
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
            'description' => $request->description,
            'due_date' => $request->due_date,
            'due_time' => $request->due_time,
            'total_points' => $request->total_points,
            'attachment_path' => $filePath, // Store file path
            'created_at' => now()->toDateTimeString(),
        ];

        // Store assignment in Firebase
        $ref = $this->database->getReference('assignments')->push($assignmentData);

        return redirect()->route('assignments.list')->with('success', 'Assignment added successfully!');
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
            // Fetch assignment from Firebase
            $assignmentData = $this->database->getReference('assignments/' . $id)->getValue();

            // Check if assignment exists
            if (!$assignmentData) {
                return redirect()->route('assignments.list')->with('error', 'Assignment not found!');
            }

            // Add the ID to the assignment array so it's available in the view
            $assignment = array_merge(['id' => $id], $assignmentData);

            return view('ManageAssignment.TeacherEditAssignment', compact('assignment'));
            
        } catch (\Exception $e) {
            return redirect()->route('assignments.list')->with('error', 'Failed to load assignment: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Validate inputs
            $request->validate([
                'assignment_name' => 'required|string|max:255',
                'description' => 'required|string',
                'due_date' => 'required|date',
                'due_time' => 'required|date_format:H:i',
                'total_points' => 'required|integer|min:0',
                'attachment' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,jpg,jpeg,png|max:10240', // 10MB max
            ]);

            // Get existing assignment data from Firebase
            $existingAssignment = $this->database->getReference('assignments/' . $id)->getValue();
            
            // Check if assignment exists
            if (!$existingAssignment) {
                return redirect()->route('assignments.list')->with('error', 'Assignment not found!');
            }

            // Prepare updated assignment data
            $assignmentData = [
                'assignment_name' => $request->assignment_name,
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

            // Preserve created_at timestamp if it exists
            if (isset($existingAssignment['created_at'])) {
                $assignmentData['created_at'] = $existingAssignment['created_at'];
            }

            // Update assignment in Firebase
            $this->database->getReference('assignments/' . $id)->set($assignmentData);

            return redirect()->route('assignments.list')->with('success', 'Assignment updated successfully!');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update assignment: ' . $e->getMessage())
                ->withInput();
        }
    }
}