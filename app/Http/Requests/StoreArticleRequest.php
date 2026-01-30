<?php

namespace App\Http\Requests;

use App\Models\Article;
use Illuminate\Foundation\Http\FormRequest;

class StoreArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:140'],
            'excerpt' => ['nullable', 'string', 'max:300'],
            'content' => ['required', 'string', 'min:20'],
            'status' => ['nullable', 'string', 'in:' . implode(',', Article::statuses())],
        ];
    }
}
