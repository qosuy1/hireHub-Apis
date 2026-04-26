<?php

namespace App\Http\Requests\v1\project;

use App\Rules\ProjectBudgetRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->role === 'client';
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
            'type' => 'required|in:fixed,hourly',
            'title' => 'required|string|min:150|max:255',
            'description' => 'required|string|min:255',
            'status' => 'sometimes|in:open,closed,in_progress',
            'delivery_date' => 'required|date|after_or_equal:' . $project->created_at->format('Y-m-d'),
            'tags' => 'required|array|max:5',
            'budget' => [
                'required',
                'numeric',
                new ProjectBudgetRule($this->type ?? $project->type),
            ],
            'attachments' => ['sometimes', 'array', 'min:1'],
            'attachments.*.file' => ['sometimes', 'file', 'mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,zip', 'max:10240'],
        ];
    }
}
