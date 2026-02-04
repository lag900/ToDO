<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Task Completed</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=Outfit:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; padding: 0; width: 100% !important; background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        .wrapper { width: 100%; padding-bottom: 40px; }
        .main-card { max-width: 600px; margin: 40px auto; background-color: #ffffff; border-radius: 32px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }
        .header { background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 40px; text-align: center; }
        .content { padding: 40px; text-align: center; }
        .footer { padding: 24px; text-align: center; color: #64748b; font-size: 12px; }
        .h1 { color: #ffffff; font-family: 'Outfit', sans-serif; font-size: 24px; font-weight: 900; text-transform: uppercase; letter-spacing: 2px; }
        .task-title { color: #0f172a; font-family: 'Outfit', sans-serif; font-size: 28px; font-weight: 700; margin: 12px 0; }
        .confetti { font-size: 40px; margin-bottom: 20px; }
        .button { display: inline-block; background-color: #10b981; color: #ffffff !important; padding: 16px 32px; border-radius: 16px; text-decoration: none; font-weight: 700; margin-top: 32px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <table class="main-card" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td class="header">
                    <p class="h1">Goal Achieved!</p>
                </td>
            </tr>
            <tr>
                <td class="content">
                    <div class="confetti">🎉</div>
                    <p style="color: #64748b; font-size: 14px; text-transform: uppercase; font-weight: 900; letter-spacing: 1px;">Task Marked as Done</p>
                    <h2 class="task-title">{{ $task->title }}</h2>
                    <p style="color: #64748b; font-size: 16px;">
                        Congratulations! {{ $actor?->display_name ?? 'The team' }} has successfully completed this task in <strong>{{ $task->board?->plan?->workspace?->name ?? 'THINKER' }}</strong>.
                    </p>
                    
                    <div style="text-align: center;">
                        <a href="{{ url('/') }}" class="button">Go to Workspace</a>
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
