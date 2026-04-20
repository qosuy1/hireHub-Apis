<?php

namespace App\Http\Requests\v1\skill;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSkillRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Middleware already checks auth + verified_freelancer type.
     * Here we verify the profile in the URL belongs to the authenticated user.
     */
    public function authorize(): bool
    {
        $profile = $this->route('freelancerProfile');
        return $profile && $this->user()->id === $profile->user_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'skills'                       => ['required', 'array', 'min:1'],
            // distinct prevents sending the same skill ID twice in one request.
            'skills.*.id'                  => ['required', 'integer', 'distinct', 'exists:skills,id'],
            'skills.*.experience_years'    => ['required', 'integer', 'min:0', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'skills.required'                       => 'Skills are required.',
            'skills.array'                          => 'Skills must be an array.',
            'skills.min'                            => 'At least one skill is required.',
            'skills.*.id.required'                  => 'Each skill must have an ID.',
            'skills.*.id.distinct'                  => 'Duplicate skill IDs are not allowed.',
            'skills.*.id.exists'                    => 'One or more skill IDs do not exist.',
            'skills.*.experience_years.required'    => 'Experience years is required for each skill.',
            'skills.*.experience_years.integer'     => 'Experience years must be an integer.',
            'skills.*.experience_years.min'         => 'Experience years must be at least 0.',
            'skills.*.experience_years.max'         => 'Experience years must be at most 50.',
        ];
    }
}
