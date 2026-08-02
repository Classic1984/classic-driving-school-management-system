<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInstructorRequest;
use App\Http\Requests\UpdateInstructorRequest;
use App\Models\Instructor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class InstructorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $instructors = Instructor::latest()->paginate(10);

        return view('instructors.index', compact('instructors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('instructors.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInstructorRequest $request): RedirectResponse
    {
        Instructor::create($request->validated());

        return Redirect::route('instructors.index')->with('status', 'instructor-created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Instructor $instructor): View
    {
        $instructor->load('courses');

        return view('instructors.show', compact('instructor'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Instructor $instructor): View
    {
        return view('instructors.edit', compact('instructor'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInstructorRequest $request, Instructor $instructor): RedirectResponse
    {
        $instructor->update($request->validated());

        return Redirect::route('instructors.index')->with('status', 'instructor-updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Instructor $instructor): RedirectResponse
    {
        $instructor->delete();

        return Redirect::route('instructors.index')->with('status', 'instructor-deleted');
    }
}
