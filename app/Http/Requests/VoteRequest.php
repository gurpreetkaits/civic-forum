<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'votable_id' => ['required', 'integer'],
            'votable_type' => ['required', Rule::in(['post', 'comment'])],
            'value' => ['required', Rule::in([1, -1])],
        ];
    }
}
