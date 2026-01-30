<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $status = $request->string('status')->toString();

        $query = Article::query()
            ->with('author')
            ->orderByDesc('created_at');

        $scopeQuery = Article::query();
        if (!($user->hasPermission('articles.manage') || $user->hasPermission('articles.view_all'))) {
            $query->where('user_id', $user->id);
            $scopeQuery->where('user_id', $user->id);
        }

        $stats = [
            'total' => (clone $scopeQuery)->count(),
            'draft' => (clone $scopeQuery)->where('status', Article::STATUS_DRAFT)->count(),
            'review' => (clone $scopeQuery)->where('status', Article::STATUS_REVIEW)->count(),
            'approved' => (clone $scopeQuery)->where('status', Article::STATUS_APPROVED)->count(),
        ];

        if ($status && in_array($status, Article::statuses(), true)) {
            $query->where('status', $status);
        }

        $articles = $query
            ->paginate(10)
            ->withQueryString()
            ->through(function (Article $article) use ($user) {
                return [
                    'id' => $article->id,
                    'title' => $article->title,
                    'slug' => $article->slug,
                    'excerpt' => $article->excerpt,
                    'status' => $article->status,
                    'published_at' => $article->published_at,
                    'author' => [
                        'id' => $article->author?->id,
                        'name' => $article->author?->name,
                        'role' => $article->author?->role,
                    ],
                    'created_at' => $article->created_at,
                    'can' => [
                        'view' => $user->can('view', $article),
                        'edit' => $user->can('update', $article),
                        'move_to_review' => $user->can('setStatus', [$article, Article::STATUS_REVIEW]),
                        'approve' => $user->can('setStatus', [$article, Article::STATUS_APPROVED]),
                    ],
                ];
            });

        return Inertia::render('News/Index', [
            'articles' => $articles,
            'filters' => [
                'status' => $status,
            ],
            'statuses' => Article::statuses(),
            'stats' => $stats,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Article::class);

        return Inertia::render('News/Create', [
            'statuses' => Article::statuses(),
        ]);
    }

    public function store(StoreArticleRequest $request)
    {
        $this->authorize('create', Article::class);

        $data = $request->validated();
        $title = $data['title'];

        $article = Article::create([
            'title' => $title,
            'slug' => $this->uniqueSlug($title),
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['content'],
            'status' => Article::STATUS_DRAFT,
            'user_id' => $request->user()->id,
        ]);

        return redirect()
            ->route('news.edit', $article)
            ->with('success', 'Article drafted successfully.');
    }

    public function show(Request $request, Article $article)
    {
        $this->authorize('view', $article);

        $article->load('author');

        return Inertia::render('News/Show', [
            'article' => [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => $article->excerpt,
                'content' => $article->content,
                'status' => $article->status,
                'published_at' => $article->published_at,
                'created_at' => $article->created_at,
                'author' => [
                    'id' => $article->author?->id,
                    'name' => $article->author?->name,
                    'role' => $article->author?->role,
                ],
                'can' => [
                    'edit' => $request->user()->can('update', $article),
                    'move_to_review' => $request->user()->can('setStatus', [$article, Article::STATUS_REVIEW]),
                    'approve' => $request->user()->can('setStatus', [$article, Article::STATUS_APPROVED]),
                ],
            ],
        ]);
    }

    public function edit(Request $request, Article $article)
    {
        $this->authorize('update', $article);

        return Inertia::render('News/Edit', [
            'article' => [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => $article->excerpt,
                'content' => $article->content,
                'status' => $article->status,
            ],
            'statuses' => Article::statuses(),
            'can' => [
                'move_to_review' => $request->user()->can('setStatus', [$article, Article::STATUS_REVIEW]),
                'approve' => $request->user()->can('setStatus', [$article, Article::STATUS_APPROVED]),
            ],
        ]);
    }

    public function update(UpdateArticleRequest $request, Article $article)
    {
        $this->authorize('update', $article);

        $data = $request->validated();
        $updates = [
            'excerpt' => $data['excerpt'] ?? $article->excerpt,
        ];

        if (array_key_exists('title', $data)) {
            $updates['title'] = $data['title'];
        }

        if (array_key_exists('content', $data)) {
            $updates['content'] = $data['content'];
        }

        if (($data['refresh_slug'] ?? false) && !empty($data['title'])) {
            $updates['slug'] = $this->uniqueSlug($data['title'], $article->id);
        }

        $article->update($updates);

        return back()->with('success', 'Article updated.');
    }

    public function updateStatus(UpdateStatusRequest $request, Article $article)
    {
        $status = $request->validated()['status'];
        $this->authorize('setStatus', [$article, $status]);

        $article->status = $status;
        $article->published_at = $status === Article::STATUS_APPROVED ? now() : null;
        $article->save();

        return back()->with('success', 'Status updated.');
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $slug = Str::slug($title);
        $base = $slug;
        $counter = 1;

        while (Article::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
