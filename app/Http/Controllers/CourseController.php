<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Models\Instructor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $courses = Course::with('instructors')->latest()->paginate(10);

        return view('courses.index', compact('courses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $instructors = Instructor::orderBy('name')->get();

        return view('courses.create', compact('instructors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $course = Course::create($request->safe()->except('instructors'));

        $course->instructors()->sync($request->validated('instructors', []));

        return Redirect::route('courses.index')->with('status', 'course-created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Course $course): View
    {
        $course->load('instructors');

        return view('courses.show', compact('course'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Course $course): View
    {
        $course->load('instructors');
        $instructors = Instructor::orderBy('name')->get();

        return view('courses.edit', compact('course', 'instructors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $course->update($request->safe()->except('instructors'));

        $course->instructors()->sync($request->validated('instructors', []));

        return Redirect::route('courses.index')->with('status', 'course-updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course): RedirectResponse
    {
        $course->delete();

        return Redirect::route('courses.index')->with('status', 'course-deleted');
    }
}
