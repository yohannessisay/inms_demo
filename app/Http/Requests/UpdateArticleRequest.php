<?php

namespace App\Http\Requests;

use App\Models\Article;
use Illuminate\Foundation\Http\FormRequest;

class UpdateArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:140'],
            'excerpt' => ['nullable', 'string', 'max:300'],
            'content' => ['sometimes', 'required', 'string', 'min:20'],
            'status' => ['nullable', 'string', 'in:' . implode(',', Article::statuses())],
            'refresh_slug' => ['nullable', 'boolean'],
        ];
    }
}
