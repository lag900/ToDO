<x-mail::message>
# Task Delivered! 📦

Hi,

**{{ $actor->display_name }}** has just added new deliverables/attachments to the task: **{{ $task->title }}**.

<x-mail::button :url="config('app.url') . '/tasks/' . $task->id">
View Deliverables
</x-mail::button>

Workspace: **{{ $task->board->plan->workspace->name }}**

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
