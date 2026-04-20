<?php

namespace App\Http\Requests\v1\offer;

use App\Enums\UserTypeEnum;
use App\Rules\CleanContentRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreOfferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->type === UserTypeEnum::FREELANCER->value;
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
                'required',
                'min:150',
                new CleanContentRule(), // Custom Rule 
            ],
            'amount' => 'required|numeric|min:1',
            'delevery_time' => ['integer' , 'required' , 'min:1']
        ];
    }

    public function messages(): array
    {
        return [
            'cover_letter.min' => 'خطاب التقديم قصير جداً، يرجى كتابة تفاصيل جدية حول كيفية تنفيذك للمشروع.',
        ];
    }
}
