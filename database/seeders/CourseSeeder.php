<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the courses table.
     * 10 courses: 1 MPU (3 hours) and 9 FCI courses (1-2 hours each)
     */
    public function run(): void
    {
        $courses = [
            // MPU Course (General Education) - 3 hours
            [
                'user_id' => 1,
                'course_name' => 'MPU3023 - Islamic and Asian Civilization',
                'description' => 'General Education Course - 3 hours per week',
            ],
            
            // FCI Core Courses (1-2 hours each)
            [
                'user_id' => 1,
                'course_name' => 'CS1133 - Programming Fundamentals',
                'description' => 'Introduction to programming using C/C++ - 2 hours per week',
            ],
            [
                'user_id' => 2,
                'course_name' => 'CS1213 - Data Structures',
                'description' => 'Basic data structures and algorithms - 2 hours per week',
            ],
            [
                'user_id' => 3,
                'course_name' => 'CS2123 - Web Development Basics',
                'description' => 'Introduction to HTML, CSS, and JavaScript - 2 hours per week',
            ],
            [
                'user_id' => 1,
                'course_name' => 'CS2133 - Database Management Systems',
                'description' => 'Relational databases and SQL - 2 hours per week',
            ],
            [
                'user_id' => 2,
                'course_name' => 'CS3113 - Software Engineering',
                'description' => 'Software development lifecycle and best practices - 2 hours per week',
            ],
            [
                'user_id' => 3,
                'course_name' => 'CS2243 - Computer Networks',
                'description' => 'Network protocols and architecture - 1 hour per week',
            ],
            [
                'user_id' => 1,
                'course_name' => 'CS3143 - Operating Systems',
                'description' => 'OS concepts and system programming - 2 hours per week',
            ],
            [
                'user_id' => 2,
                'course_name' => 'CS3223 - Artificial Intelligence',
                'description' => 'Introduction to AI and machine learning - 1 hour per week',
            ],
            [
                'user_id' => 3,
                'course_name' => 'CS3243 - Cybersecurity Fundamentals',
                'description' => 'Security principles and cryptography basics - 1 hour per week',
            ],
        ];

        foreach ($courses as $course) {
            Course::create($course);
        }
    }
}
