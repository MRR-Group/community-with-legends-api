<?php

declare(strict_types=1);

namespace CommunityWithLegends\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "name" => ["required", "max:225"],
            "email" => ["required", "email:rfc,dns", "max:225", "string", "unique:users"],
            "password" => ["required", "min:8", "max:225", "string"],
        ];
    }
}
