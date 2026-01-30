<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $status = $request->string('status')->toString();

        $query = Article::query()->with('author')->orderByDesc('created_at');

        if (!($user->hasPermission('articles.manage') || $user->hasPermission('articles.view_all'))) {
            $query->where('user_id', $user->id);
        }

        if ($status && in_array($status, Article::statuses(), true)) {
            $query->where('status', $status);
        }

        return response()->json([
            'data' => $query->paginate(15),
        ]);
    }

    public function store(StoreArticleRequest $request)
    {
        $this->authorize('create', Article::class);

        $data = $request->validated();

        $article = Article::create([
            'title' => $data['title'],
            'slug' => $this->uniqueSlug($data['title']),
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['content'],
            'status' => Article::STATUS_DRAFT,
            'user_id' => $request->user()->id,
        ]);

        return response()->json(['data' => $article], 201);
    }

    public function show(Article $article)
    {
        $this->authorize('view', $article);

        return response()->json(['data' => $article->load('author')]);
    }

    public function update(UpdateArticleRequest $request, Article $article)
    {
        $this->authorize('update', $article);

        $data = $request->validated();
        $updates = [];

        if (array_key_exists('title', $data)) {
            $updates['title'] = $data['title'];
        }

        if (array_key_exists('excerpt', $data)) {
            $updates['excerpt'] = $data['excerpt'];
        }

        if (array_key_exists('content', $data)) {
            $updates['content'] = $data['content'];
        }

        if (($data['refresh_slug'] ?? false) && !empty($data['title'])) {
            $updates['slug'] = $this->uniqueSlug($data['title'], $article->id);
        }

        $article->update($updates);

        return response()->json(['data' => $article]);
    }

    public function updateStatus(UpdateStatusRequest $request, Article $article)
    {
        $status = $request->validated()['status'];
        $this->authorize('setStatus', [$article, $status]);

        $article->status = $status;
        $article->published_at = $status === Article::STATUS_APPROVED ? now() : null;
        $article->save();

        return response()->json(['data' => $article]);
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
