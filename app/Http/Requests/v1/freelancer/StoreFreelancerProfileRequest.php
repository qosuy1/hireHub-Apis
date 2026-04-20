<?php

namespace App\Http\Requests\v1\freelancer;

use App\Enums\UserTypeEnum;
use App\Rules\CleanContentRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreFreelancerProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user() ;
        return $user->isFreelancer() &&  (!$user->has_profile) ;
    }
 
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bio' => ['required', 'string', 'min:155' , 'max:500', new CleanContentRule()],
            'phone' => 'required|string|unique:freelancer_profiles,phone|max:15',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'hourly_rate' => 'required|numeric|min:0',
            'availability_status' => 'required|in:available,not_available',
            'skills' => 'required|array',
            'skills.*.id' => 'required|exists:skills,id',
            'skills.*.experience_years' => 'required|integer|min:0',
            'portfolio_links'   => ['nullable', 'array'],
            'portfolio_links.*' => ['url'],  // each platform value must be a valid URL
        ];
    }
}
