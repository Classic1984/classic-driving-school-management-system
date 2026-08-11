<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Service;
use App\Models\Student;
use App\Models\StudentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;

class StudentServiceController extends Controller
{
    /**
     * Charge a student for a flat catalog service (e.g. Driver's License
     * Processing, Learner's Permit) - independent of any course
     * enrollment. The price is snapshotted from the service's current
     * price, the same way a course's fee is snapshotted onto an
     * enrollment.
     */
    public function store(Request $request, Student $student): RedirectResponse
    {
        $data = $request->validate([
            'service_id' => [
                'required',
                'integer',
                Rule::exists('services', 'id')->where('is_active', true),
                Rule::unique('student_services')->where('student_id', $student->id),
            ],
        ], [
            'service_id.unique' => 'This student has already been charged for that service.',
        ]);

        $service = Service::findOrFail($data['service_id']);

        StudentService::create([
            'student_id' => $student->id,
            'service_id' => $service->id,
            'price' => $service->price,
        ]);

        ActivityLog::record("Charged {$student->name} ₦".number_format((float) $service->price, 2)." for {$service->name}");

        return Redirect::route('students.show', $student)->with('status', 'service-charged');
    }
}
