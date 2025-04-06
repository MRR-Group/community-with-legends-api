<?php

namespace CommunityWithLegends\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:512'],
            'tag_id' => ['nullable', 'integer', 'exists:tags,id'],
            'game_id' => ['nullable', 'integer', 'exists:games,id'],
        ];
    }
}
