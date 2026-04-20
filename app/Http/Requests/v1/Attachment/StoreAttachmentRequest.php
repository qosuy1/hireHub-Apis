<?php

namespace App\Http\Requests\v1\Attachment;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAttachmentRequest extends FormRequest
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
            'attachments'         => ['required', 'array', 'min:1'],
            'attachments.*.id'    => ['sometimes', 'integer', 'exists:attachments,id'],
            'attachments.*.file'  => ['required', 'file', 'mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,zip', 'max:10240'],
        ];
    }

    /**
     * Get custom human-readable attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'attachments'        => 'attachments list',
            'attachments.*.id'   => 'attachment ID',
            'attachments.*.file' => 'attachment file',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'attachments.required'        => 'At least one attachment is required.',
            'attachments.*.file.required' => 'Each attachment must include a file.',
            'attachments.*.file.file'     => 'Each attachment must be a valid uploaded file.',
            'attachments.*.file.mimes'    => 'Allowed file types: jpg, jpeg, png, gif, pdf, doc, docx, xls, xlsx, zip.',
            'attachments.*.file.max'      => 'Each file may not be larger than 10 MB.',
            'attachments.*.id.exists'     => 'The selected attachment ID does not exist.',
        ];
    }
}
