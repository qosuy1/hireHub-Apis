<?php

namespace App\Http\Requests\v1\skill;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSkillRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Route: PUT /freelancer-profiles/{freelancerProfile}/skills/{skill}
     * Middleware handles auth + verified_freelancer; here we verify profile ownership.
     */
    public function authorize(): bool
    {
        $profile = $this->route('freelancerProfile');
        return $profile && $this->user()->id === $profile->user_id;
    }

    /**
     * Get the validation rules that apply to the request.
     * The skill to update is already identified by the {skill} route parameter —
     * we only need to validate the new value being set.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [ 
            'experience_years' => ['required', 'integer', 'min:0', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'experience_years.required' => 'Experience years is required.',
            'experience_years.integer'  => 'Experience years must be an integer.',
            'experience_years.min'      => 'Experience years must be at least 0.',
            'experience_years.max'      => 'Experience years must be at most 50.',
        ];
    }
}
