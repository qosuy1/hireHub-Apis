<?php

namespace App\Http\Requests\v1\offer;

use App\Enums\UserTypeEnum;
use App\Rules\CleanContentRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOfferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->type === UserTypeEnum::FREELANCER->value
        && $this->user()->id === $this->offer->freelancer_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cover_letter' => [
                'sometimes',
                'min:150',
                new CleanContentRule(), // Custom Rule 
            ],
            'amount' => 'sometimes|numeric|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'cover_letter.min' => 'خطاب التقديم قصير جداً، يرجى كتابة تفاصيل جدية حول كيفية تنفيذك للمشروع.',
        ];
    }
}
