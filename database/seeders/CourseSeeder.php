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
                'user_id' => 5,
                'course_name' => 'MPU3023 - Islamic and Asian Civilization',
                'hours' => 3,
                'description' => 'General Education Course - 3 hours per week',
            ],
            
            // FCI Core Courses (1-2 hours each)
            [
                'user_id' => 5,
                'course_name' => 'CS1133 - Programming Fundamentals',
                'hours' => 2,
                'description' => 'Introduction to programming using C/C++ - 2 hours per week',
            ],
            [
                'user_id' => 6,
                'course_name' => 'CS1213 - Data Structures',
                'hours' => 2,
                'description' => 'Basic data structures and algorithms - 2 hours per week',
            ],
            [
                'user_id' => 6,
                'course_name' => 'CS2123 - Web Development Basics',
                'hours' => 2,
                'description' => 'Introduction to HTML, CSS, and JavaScript - 2 hours per week',
            ],
            [
                'user_id' => 6,
                'course_name' => 'CS2133 - Database Management Systems',
                'hours' => 2,
                'description' => 'Relational databases and SQL - 2 hours per week',
            ],
            [
                'user_id' => 7,
                'course_name' => 'CS3113 - Software Engineering',
                'hours' => 2,
                'description' => 'Software development lifecycle and best practices - 2 hours per week',
            ],
            [
                'user_id' => 7,
                'course_name' => 'CS2243 - Computer Networks',
                'hours' => 1,
                'description' => 'Network protocols and architecture - 1 hour per week',
            ],
            [
                'user_id' => 7,
                'course_name' => 'CS3143 - Operating Systems',
                'hours' => 2,
                'description' => 'OS concepts and system programming - 2 hours per week',
            ],
            [
                'user_id' => 5,
                'course_name' => 'CS3223 - Artificial Intelligence',
                'hours' => 1,
                'description' => 'Introduction to AI and machine learning - 1 hour per week',
            ],
            [
                'user_id' => 6,
                'course_name' => 'CS3243 - Cybersecurity Fundamentals',
                'hours' => 1,
                'description' => 'Security principles and cryptography basics - 1 hour per week',
            ],
        ];

        foreach ($courses as $course) {
            Course::create($course);
        }
    }
}
