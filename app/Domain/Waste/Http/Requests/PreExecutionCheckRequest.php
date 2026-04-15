<?php

namespace App\Domain\Waste\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PreExecutionCheckRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'collect_task_ids' => ['required', 'array', 'min:1'],
            'collect_task_ids.*' => ['integer', 'distinct', 'exists:collect_tasks,id'],
        ];
    }
}
