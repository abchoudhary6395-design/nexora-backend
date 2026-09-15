<?php

namespace App\Http\Requests\Work;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Task::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'in:Low,Medium,High,Urgent'],
            'status' => ['nullable', 'in:todo,in_progress,review,done'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'taskable_type' => ['nullable', 'string'],
            'taskable_id' => ['nullable', 'integer'],
            'due_date' => ['nullable', 'date'],
            'reminder_at' => ['nullable', 'date'],
            'estimated_minutes' => ['nullable', 'integer', 'min:0'],
            'recurrence' => ['nullable', 'string'],
            'depends_on_task_id' => ['nullable', 'exists:tasks,id'],
            'custom_fields' => ['nullable', 'array'],
            'checklist' => ['nullable', 'array'],
            'checklist.*' => ['string'],
        ];
    }
}
