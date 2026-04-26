<?php

namespace App\Http\Requests\v1\project;

use App\Enums\UserTypeEnum;
use App\Rules\ProjectBudgetRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $project = $this->route('project');
        return $this->user()->type === UserTypeEnum::CLIENT->value && $project->user_id === $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $project = $this->route('project');
        return [
            'type' => 'sometimes|in:fixed,hourly',
            'title' => 'sometimes|string|min:150|max:255',
            'description' => 'sometimes|string|min:255',
            'status' => 'sometimes|in:open,closed,in_progress',
            'delivery_date' => 'sometimes|date|after_or_equal:' . $project->created_at->format('Y-m-d'),
            'tags' => 'sometimes|array|max:5',
            'tags.*' => 'integer|exists:skills,id',
            'budget' => [
                'sometimes',
                'numeric',
                'min:0',
                new ProjectBudgetRule($this->type ?? $project->type),
            ],

            'attachments'         => ['sometimes', 'array', 'min:1'],
            // 'attachments.*.id'    => ['sometimes', 'integer', 'exists:attachments,id'],
            'attachments.*.file'  => ['sometimes', 'file', 'mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,zip', 'max:10240'],
        ];
    }
}
