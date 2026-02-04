<!DOCTYPE html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml">
<head>
    <meta charset="utf-8">
    <meta name="x-apple-disable-message-reformatting">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
    <title>New Task Assigned</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=Outfit:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0; 
            width: 100% !important;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
            background-color: #f8fafc;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f8fafc;
            padding-bottom: 40px;
        }
        .main-card {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .header {
            background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
            padding: 40px;
            text-align: center;
        }
        .content {
            padding: 40px;
        }
        .footer {
            padding: 24px;
            text-align: center;
            color: #64748b;
            font-size: 12px;
            line-height: 1.5;
        }
        .h1 {
            color: #ffffff;
            font-family: 'Outfit', sans-serif;
            font-size: 24px;
            font-weight: 900;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .task-title {
            color: #0f172a;
            font-family: 'Outfit', sans-serif;
            font-size: 28px;
            font-weight: 700;
            margin: 12px 0;
            line-height: 1.2;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 16px;
        }
        .priority-urgent { background-color: #fee2e2; color: #ef4444; }
        .priority-high { background-color: #ffedd5; color: #f97316; }
        .priority-medium { background-color: #e0e7ff; color: #6366f1; }
        .priority-low { background-color: #f1f5f9; color: #64748b; }
        
        .info-row {
            padding: 16px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .info-label {
            color: #94a3b8;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }
        .info-value {
            color: #334155;
            font-size: 14px;
            font-weight: 700;
        }
        .button {
            display: inline-block;
            background-color: #4f46e5;
            color: #ffffff !important;
            padding: 16px 32px;
            border-radius: 16px;
            text-decoration: none;
            font-weight: 700;
            font-size: 16px;
            margin-top: 32px;
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4);
        }
        .creator-strip {
            background-color: #f8fafc;
            padding: 12px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <table class="main-card" width="100%" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
                <td class="header">
                    <img src="https://i.postimg.cc/jqQRtc95/thinker-(1).png" alt="THINKER" height="40" style="margin-bottom: 20px;">
                    <p class="h1">New Task Alert</p>
                </td>
            </tr>
            <tr>
                <td class="content">
                    <div class="creator-strip">
                        <strong style="color: #4f46e5;">{{ $task->creator?->display_name ?? $task->creator?->name ?? 'System' }}</strong>
                        <span style="color: #64748b; margin-left: 4px;">Created a new challenge in</span>
                        <strong style="color: #0f172a; margin-left: 4px;">{{ $task->board?->plan?->workspace?->name ?? 'THINKER' }}</strong>
                    </div>

                    <div class="badge {{ 'priority-' . ($task->priority ?? 'medium') }}">
                        {{ $task->priority ?? 'medium' }} priority
                    </div>
                    
                    <h2 class="task-title">{{ $task->title }}</h2>
                    <p style="color: #64748b; font-size: 14px; line-height: 1.6; margin-bottom: 32px;">
                        {{ $task->description ?? 'No description provided for this task.' }}
                    </p>

                    <div class="info-row">
                        <div class="info-label">Deadline</div>
                        <div class="info-value">
                            {{ $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('M d, Y - h:i A') : 'Whenever you can' }}
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-label">Assigned To</div>
                        <div class="info-value">
                            {{ $task->assignee?->display_name ?? 'Team Choice' }}
                        </div>
                    </div>

                    <div style="text-align: center;">
                        <a href="{{ url('/') }}" class="button">Open in THINKER</a>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="footer">
                    <p>You received this email because you are a member of the <strong>{{ $task->board?->plan?->workspace?->name ?? 'THINKER' }}</strong> workspace.</p>
                    <p>© 2026 THINKER Task Management. All rights reserved.</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>