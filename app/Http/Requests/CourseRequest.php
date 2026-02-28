<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class CourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:courses,slug,' . ($this->course?->id ?? 'NULL'),
            'description' => 'nullable|string',
            'status'      => 'required|in:draft,published,archived',
            'thumbnail'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (empty($this->slug) && $this->title) {
            $this->merge(['slug' => Str::slug($this->title)]);
        }
    }
}