<?php

namespace CommunityWithLegends\Http\Requests;

use CommunityWithLegends\Enums\UserGameStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UserGameRequest extends FormRequest
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
        $validStatuses = implode(',', array_column(UserGameStatus::cases(), 'value'));

        return [
            'game_id' => ['required', 'exists:games,id'],
            'status' => ['required', "in:$validStatuses"],
        ];
    }
}
