<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class HardwareItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "title" => ["required", "string", "min:1", "max:40"],
            "value" => ["required", "string", "min:1", "max:60"],
        ];
    }
}
