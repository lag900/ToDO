<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Task Updated</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=Outfit:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; padding: 0; width: 100% !important; background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        .wrapper { width: 100%; padding-bottom: 40px; }
        .main-card { max-width: 600px; margin: 40px auto; background-color: #ffffff; border-radius: 32px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }
        .header { background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%); padding: 40px; text-align: center; }
        .content { padding: 40px; }
        .footer { padding: 24px; text-align: center; color: #64748b; font-size: 12px; }
        .h1 { color: #ffffff; font-family: 'Outfit', sans-serif; font-size: 24px; font-weight: 900; text-transform: uppercase; letter-spacing: 2px; }
        .task-title { color: #0f172a; font-family: 'Outfit', sans-serif; font-size: 28px; font-weight: 700; margin: 12px 0; }
        .change-item { padding: 12px; background-color: #f8fafc; border-radius: 12px; margin-bottom: 8px; font-size: 14px; }
        .change-label { font-weight: 900; text-transform: uppercase; font-size: 10px; color: #6366f1; display: block; }
        .button { display: inline-block; background-color: #6366f1; color: #ffffff !important; padding: 16px 32px; border-radius: 16px; text-decoration: none; font-weight: 700; margin-top: 32px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <table class="main-card" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td class="header">
                    <p class="h1">Task Updated</p>
                </td>
            </tr>
            <tr>
                <td class="content">
                    <p style="color: #64748b; font-size: 14px;">Hello,</p>
                    <p style="color: #0f172a; font-size: 16px; font-weight: 700;">
                        {{ $actor?->display_name ?? 'Someone' }} updated a task in <strong>{{ $task->board?->plan?->workspace?->name ?? 'THINKER' }}</strong>.
                    </p>
                    
                    <h2 class="task-title">{{ $task->title }}</h2>

                    <div style="margin-top: 24px;">
                        <p style="font-size: 12px; font-weight: 900; text-transform: uppercase; color: #94a3b8; letter-spacing: 1px; margin-bottom: 12px;">Changes made:</p>
                        @foreach($changes as $field => $change)
                            <div class="change-item">
                                <span class="change-label">{{ str_replace('_', ' ', $field) }}</span>
                                <div style="margin-top: 4px;">
                                    <span style="color: #94a3b8; text-decoration: line-through;">{{ is_array($change['from']) ? json_encode($change['from']) : $change['from'] }}</span>
                                    <span style="color: #0f172a; font-weight: 700; margin-left: 8px;">→ {{ is_array($change['to']) ? json_encode($change['to']) : $change['to'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div style="text-align: center;">
                        <a href="{{ url('/') }}" class="button">View Task Details</a>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="footer">
                    <p>© 2026 THINKER Task Management.</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
