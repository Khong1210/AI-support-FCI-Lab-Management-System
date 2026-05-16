<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::with('user')->get();
        return view('admin.courses.index', compact('courses'));
    }

    public function create()
    {
        $lecturers = User::all(); // Assuming all users can be assigned for now
        return view('admin.courses.create', compact('lecturers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'course_name' => 'required|string|max:255',
            'user_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
        ]);

        Course::create($request->all());

        return redirect('/admin/courses')->with('status', 'Course created successfully.');
    }

    public function edit(Course $course)
    {
        $lecturers = User::all();
        return view('admin.courses.edit', compact('course', 'lecturers'));
    }

    public function update(Request $request, Course $course)
    {
        $request->validate([
            'course_name' => 'required|string|max:255',
            'user_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
        ]);

        $course->update($request->all());

        return redirect('/admin/courses')->with('status', 'Course updated successfully.');
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return redirect('/admin/courses')->with('status', 'Course deleted successfully.');
    }
}
