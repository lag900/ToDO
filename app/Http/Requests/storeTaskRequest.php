<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class storeTaskRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'board_id' => 'required|exists:boards,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|string',
            'parent_id' => 'nullable|exists:tasks,id',
            'deadline' => 'nullable|string',
            'start_date' => 'nullable|string',
            'assigned_to' => 'sometimes|nullable|exists:users,id',
            'task_type' => 'sometimes|string|in:general,file,design,call',
            'contact_info' => 'sometimes|nullable|string',
            'is_public' => 'sometimes|boolean'
        ];
    }
}
