<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Administrator',
                'slug' => User::ROLE_ADMIN,
                'description' => 'Full system access',
                'permissions' => ['*'],
            ],
            [
                'name' => 'Editor',
                'slug' => User::ROLE_EDITOR,
                'description' => 'Review and approve newsroom content',
                'permissions' => [
                    'articles.view_all',
                    'articles.edit_all',
                    'articles.approve',
                ],
            ],
            [
                'name' => 'Reporter',
                'slug' => User::ROLE_REPORTER,
                'description' => 'Create and submit drafts',
                'permissions' => [
                    'articles.create',
                    'articles.edit',
                    'articles.review',
                ],
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['slug' => $role['slug']], $role);
        }

        $admin = User::factory()->create([
            'name' => 'Admin Desk',
            'email' => 'admin@inms.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $editor = User::factory()->create([
            'name' => 'Editor Desk',
            'email' => 'editor@inms.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_EDITOR,
            'is_active' => true,
        ]);

        $reporter = User::factory()->create([
            'name' => 'Field Reporter',
            'email' => 'reporter@inms.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_REPORTER,
            'is_active' => true,
        ]);

        $stories = [
            [
                'title' => 'Civic pulse rises ahead of regional vote',
                'excerpt' => 'Local councils test rapid verification pipelines as voters mobilize.',
                'status' => Article::STATUS_DRAFT,
                'user_id' => $reporter->id,
            ],
            [
                'title' => 'Election board releases live dashboard preview',
                'excerpt' => 'Editors prep a verification checklist for regional counts.',
                'status' => Article::STATUS_REVIEW,
                'user_id' => $editor->id,
            ],
            [
                'title' => 'National turnout expected to surpass previous cycle',
                'excerpt' => 'Early indicators show strong participation in rural zones.',
                'status' => Article::STATUS_APPROVED,
                'user_id' => $admin->id,
            ],
        ];

        foreach ($stories as $story) {
            Article::create([
                'title' => $story['title'],
                'slug' => Str::slug($story['title']),
                'excerpt' => $story['excerpt'],
                'content' => $story['excerpt'] . ' Full story details for newsroom review and approval workflow.',
                'status' => $story['status'],
                'user_id' => $story['user_id'],
                'published_at' => $story['status'] === Article::STATUS_APPROVED ? now() : null,
            ]);
        }
    }
}
