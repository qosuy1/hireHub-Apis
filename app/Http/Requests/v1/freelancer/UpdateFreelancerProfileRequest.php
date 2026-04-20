<?php

namespace App\Http\Requests\v1\freelancer;

use App\Rules\CleanContentRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFreelancerProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $profile = $this->route('freelancerProfile');
        return $profile->user_id === $this->user()->id && $this->user()->has_profile;

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bio' => ['sometimes', 'string', 'max:255', new CleanContentRule()],
            'phone' => 'sometimes|string|unique:freelancer_profiles,phone|max:15',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'hourly_rate' => 'sometimes|numeric|min:0',
            'availability_status' => 'sometimes|in:available,not_available',
            'skills' => 'sometimes|array',
            'skills.*.id' => 'sometimes|exists:skills,id',
            'skills.*.experience_years' => 'sometimes|integer|min:0',
            'portfolio_links'   => ['sometimes', 'array'],
            'portfolio_links.*' => ['url'],  // each platform value must be a valid URL
        ];
    }
}
