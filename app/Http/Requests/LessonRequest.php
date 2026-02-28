<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video_url' => 'nullable|url|max:500',
            'pdf_file' => 'nullable|file|mimes:pdf|max:10240', // 10MB max
            'content' => 'nullable|string',
            'type' => 'required|in:video,pdf,text,mixed',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'pdf_file.max' => 'PDF file must not exceed 10MB.',
            'video_url.url' => 'Please provide a valid YouTube or video URL.',
        ];
    }
}
