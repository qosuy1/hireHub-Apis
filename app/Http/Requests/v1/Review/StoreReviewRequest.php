<?php

namespace App\Http\Requests\v1\Review;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
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
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'min:10', 'max:500'],
            'reviewable_id' => ['required', 'integer', 'exists:freelancer_profiles,id'],
            'reviewable_type' => ['required', 'string', 'in:freelancer_profiles,projects'],
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => 'The rating is required.',
            'rating.integer' => 'The rating must be an integer.',
            'rating.min' => 'The rating must be at least 1.',
            'rating.max' => 'The rating must be at most 5.',
            'comment.required' => 'The comment is required.',
            'comment.string' => 'The comment must be a string.',
            'comment.min' => 'The comment must be at least 10 characters.',
            'comment.max' => 'The comment must be at most 500 characters.',
            'reviewable_id.required' => 'The reviewable id is required.',
            'reviewable_id.integer' => 'The reviewable id must be an integer.',
            'reviewable_id.exists' => 'The reviewable id does not exist.',
            'reviewable_type.required' => 'The reviewable type is required.',
            'reviewable_type.string' => 'The reviewable type must be a string.',
            'reviewable_type.in' => 'The reviewable type must be either freelancer_profiles or projects.',
        ];
    }
}
