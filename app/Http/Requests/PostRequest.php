<?php

namespace App\Http\Requests;

use App\Support\Html;
use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('title'))) {
            $this->merge(['title' => Str::squish($this->input('title'))]);
        }
        if ($this->filled('slug')) {
            $this->merge(['slug' => Str::slug($this->input('slug'))]);
        }
        // The editor sends rich text; plain text (no JavaScript) is converted. Empty text fails "required".
        if (is_string($body = $this->input('body'))) {
            $html = Html::html(Post::isHtml($body) ? $body : Post::textToHtml($body));
            $this->merge(['body' => trim(strip_tags($html)) === '' ? '' : $html]);
        }
    }

    public function rules(): array
    {
        $post = $this->route('post');

        return [
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:190', 'alpha_dash', Rule::unique('posts', 'slug')->ignore($post)],
            'type' => ['required', Rule::in(array_keys(Post::TYPES))],
            'excerpt' => ['nullable', 'string', 'max:300'],
            'body' => ['required', 'string', 'max:60000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:min_width=600'],
            'image_alt' => ['nullable', 'string', 'max:200', 'required_with:image'],
            'remove_image' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(array_keys(Post::STATUSES))],
            'published_at' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'image.dimensions' => 'Please use an image at least 600 pixels wide.',
            'image.max' => 'Images can be up to 4 MB.',
            'image_alt.required_with' => 'Describe the image in a sentence for screen readers.',
        ];
    }
}
