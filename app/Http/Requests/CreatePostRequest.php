<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "content" => ["required", "string", "max:512"],
            "tag_id" => ["nullable", "integer", "exists:tags,id"],
            "game_id" => ["nullable", "integer", "exists:games,id"],
            "asset_type_id" => ["nullable", "integer", "exists:asset_types,id"],
            "asset_link" => ["nullable", "string", "max:512"],
        ];
    }
}
