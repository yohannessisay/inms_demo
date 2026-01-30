<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    public function viewAny(User $user): bool
    {
        return $user !== null;
    }

    public function view(User $user, Article $article): bool
    {
        if ($user->hasPermission('articles.manage') || $user->hasPermission('articles.view_all')) {
            return true;
        }

        return $article->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('articles.manage') || $user->hasPermission('articles.create');
    }

    public function update(User $user, Article $article): bool
    {
        if ($user->hasPermission('articles.manage')) {
            return true;
        }

        if ($user->hasPermission('articles.edit_all')) {
            return $article->status !== Article::STATUS_APPROVED;
        }

        return $user->hasPermission('articles.edit')
            && $article->user_id === $user->id
            && $article->status !== Article::STATUS_APPROVED;
    }

    public function setStatus(User $user, Article $article, string $status): bool
    {
        $current = $article->status;

        if ($user->hasPermission('articles.manage')) {
            return $current !== $status;
        }

        if ($status === Article::STATUS_REVIEW) {
            return $user->hasPermission('articles.review') && $article->user_id === $user->id;
        }

        if ($status === Article::STATUS_APPROVED) {
            return $user->hasPermission('articles.approve') && $current === Article::STATUS_REVIEW;
        }

        return false;
    }
}
