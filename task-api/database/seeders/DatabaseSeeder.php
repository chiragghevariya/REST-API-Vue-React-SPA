<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // -----------------------------------------------------------------------
        // Demo User
        // -----------------------------------------------------------------------
        $user = User::firstOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name'     => 'Demo User',
                'password' => Hash::make('password'),
            ]
        );

        // -----------------------------------------------------------------------
        // Categories
        // -----------------------------------------------------------------------
        $work = Category::firstOrCreate(
            ['name' => 'Work', 'user_id' => $user->id],
            ['color' => '#3B82F6']
        );

        $personal = Category::firstOrCreate(
            ['name' => 'Personal', 'user_id' => $user->id],
            ['color' => '#10B981']
        );

        $shopping = Category::firstOrCreate(
            ['name' => 'Shopping', 'user_id' => $user->id],
            ['color' => '#F59E0B']
        );

        // -----------------------------------------------------------------------
        // Tasks — 8 todo, 7 in_progress, 5 done = 20 total
        // -----------------------------------------------------------------------
        $tasks = [
            // --- TODO (8) ---
            [
                'title'       => 'Write Q3 financial report',
                'description' => 'Compile revenue, expenses and KPIs for the board deck.',
                'status'      => 'todo',
                'priority'    => 'high',
                'category_id' => $work->id,
                'due_date'    => Carbon::today()->addDays(5),
            ],
            [
                'title'       => 'Update project documentation',
                'description' => 'Review and update the README and API docs.',
                'status'      => 'todo',
                'priority'    => 'medium',
                'category_id' => $work->id,
                'due_date'    => Carbon::today()->addDays(7),
            ],
            [
                'title'       => 'Buy groceries',
                'description' => 'Milk, eggs, bread, vegetables, and coffee.',
                'status'      => 'todo',
                'priority'    => 'low',
                'category_id' => $shopping->id,
                'due_date'    => Carbon::today()->addDay(),
            ],
            [
                'title'       => 'Schedule dentist appointment',
                'description' => null,
                'status'      => 'todo',
                'priority'    => 'medium',
                'category_id' => $personal->id,
                'due_date'    => Carbon::today()->addDays(14),
            ],
            [
                'title'       => 'Prepare sprint planning agenda',
                'description' => 'Collect backlog items, estimate story points.',
                'status'      => 'todo',
                'priority'    => 'high',
                'category_id' => $work->id,
                'due_date'    => Carbon::today()->addDays(2),
            ],
            [
                'title'       => 'Read "Clean Code" chapters 5-8',
                'description' => null,
                'status'      => 'todo',
                'priority'    => 'low',
                'category_id' => $personal->id,
                'due_date'    => null,
            ],
            [
                'title'       => 'Order birthday gift for mom',
                'description' => 'Looking at a nice scarf or book.',
                'status'      => 'todo',
                'priority'    => 'high',
                'category_id' => $shopping->id,
                'due_date'    => Carbon::today()->addDays(3),
            ],
            [
                'title'       => 'Configure CI/CD pipeline',
                'description' => 'Set up GitHub Actions for automated testing and deployment.',
                'status'      => 'todo',
                'priority'    => 'medium',
                'category_id' => null,
                'due_date'    => Carbon::today()->addDays(10),
            ],

            // --- IN PROGRESS (7) ---
            [
                'title'       => 'Build task manager REST API',
                'description' => 'Laravel 11 + Sanctum with full CRUD and filters.',
                'status'      => 'in_progress',
                'priority'    => 'high',
                'category_id' => $work->id,
                'due_date'    => Carbon::today()->addDays(1),
            ],
            [
                'title'       => 'Redesign landing page',
                'description' => 'New hero section, testimonials, and pricing table.',
                'status'      => 'in_progress',
                'priority'    => 'medium',
                'category_id' => $work->id,
                'due_date'    => Carbon::today()->addDays(4),
            ],
            [
                'title'       => 'Morning jog routine',
                'description' => 'Run 5 km every morning before 7 am.',
                'status'      => 'in_progress',
                'priority'    => 'low',
                'category_id' => $personal->id,
                'due_date'    => null,
            ],
            [
                'title'       => 'Research new laptop options',
                'description' => 'Compare MacBook Pro M3 vs Dell XPS 15.',
                'status'      => 'in_progress',
                'priority'    => 'medium',
                'category_id' => $shopping->id,
                'due_date'    => Carbon::today()->subDays(1), // overdue
            ],
            [
                'title'       => 'Implement dark mode in the SPA',
                'description' => 'Use Tailwind dark: classes and persist preference.',
                'status'      => 'in_progress',
                'priority'    => 'low',
                'category_id' => $work->id,
                'due_date'    => Carbon::today()->subDays(2), // overdue
            ],
            [
                'title'       => 'Learn Spanish — Duolingo streak',
                'description' => '30-minute sessions daily.',
                'status'      => 'in_progress',
                'priority'    => 'low',
                'category_id' => $personal->id,
                'due_date'    => null,
            ],
            [
                'title'       => 'Code review for PR #42',
                'description' => 'Review authentication refactor before merge.',
                'status'      => 'in_progress',
                'priority'    => 'high',
                'category_id' => null,
                'due_date'    => Carbon::today()->subDays(3), // overdue
            ],

            // --- DONE (5) ---
            [
                'title'       => 'Set up development environment',
                'description' => 'Docker, PHP 8.2, Node 20 installed and configured.',
                'status'      => 'done',
                'priority'    => 'high',
                'category_id' => $work->id,
                'due_date'    => Carbon::today()->subDays(10),
            ],
            [
                'title'       => 'Database schema design',
                'description' => 'ERD for users, tasks, and categories.',
                'status'      => 'done',
                'priority'    => 'high',
                'category_id' => $work->id,
                'due_date'    => Carbon::today()->subDays(8),
            ],
            [
                'title'       => 'Pay monthly bills',
                'description' => 'Electricity, internet, and streaming subscriptions.',
                'status'      => 'done',
                'priority'    => 'medium',
                'category_id' => $personal->id,
                'due_date'    => Carbon::today()->subDays(5),
            ],
            [
                'title'       => 'Weekly grocery run',
                'description' => 'All items purchased at the farmers market.',
                'status'      => 'done',
                'priority'    => 'low',
                'category_id' => $shopping->id,
                'due_date'    => Carbon::today()->subDays(3),
            ],
            [
                'title'       => 'Team standup slide deck',
                'description' => 'Weekly summary of completed work and blockers.',
                'status'      => 'done',
                'priority'    => 'medium',
                'category_id' => null,
                'due_date'    => Carbon::today()->subDays(2),
            ],
        ];

        foreach ($tasks as $data) {
            Task::create(array_merge($data, ['user_id' => $user->id]));
        }

        $this->command->info('Database seeded successfully with demo data.');
        $this->command->info('Demo credentials  →  demo@example.com / password');
    }
}
