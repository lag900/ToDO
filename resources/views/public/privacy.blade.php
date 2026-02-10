<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - Todo Batucore</title>
    <style>
        :root {
            --primary: #4f46e5;
            --slate-50: #f8fafc;
            --slate-200: #e2e8f0;
            --slate-600: #475569;
            --slate-800: #1e293b;
            --slate-900: #0f172a;
        }
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background-color: var(--slate-50);
            color: var(--slate-900);
            margin: 0;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 3rem 1.5rem;
        }
        .card {
            background: white;
            border: 1px solid var(--slate-200);
            padding: 2.5rem;
            border-radius: 2rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 2.5rem;
            font-weight: 900;
            margin-bottom: 0.5rem;
            letter-spacing: -0.025em;
        }
        .updated {
            color: var(--slate-600);
            font-weight: 500;
            margin-bottom: 2rem;
        }
        h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-top: 2rem;
            margin-bottom: 1rem;
            color: var(--slate-800);
        }
        p {
            margin-bottom: 1.25rem;
            color: var(--slate-600);
        }
        ul {
            color: var(--slate-600);
            padding-left: 1.5rem;
            margin-bottom: 1.5rem;
        }
        li {
            margin-bottom: 0.5rem;
        }
        a {
            color: var(--primary);
            font-weight: 700;
            text-decoration: none;
        }
        .back-link {
            margin-bottom: 2rem;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="/" class="back-link">← Back to Home</a>

        <div class="card">
            <h1>Privacy Policy</h1>
            <p class="updated">Last Updated: February 10, 2026</p>

            <section>
                <h2>Introduction</h2>
                <p>Welcome to <strong>Todo Batucore</strong> (https://todo.batucore.website). Todo Batucore is an independent productivity web application that helps users manage tasks, collaborate with teams, and optionally synchronize task-related events with Google Calendar. This Privacy Policy explains what data we collect, how we use it, how long we keep it, and how you can request deletion or revoke access.</p>
            </section>

            <section>
                <h2>Google OAuth data usage</h2>
                <p>When you sign in with Google OAuth we request basic profile information to create and manage your account:</p>
                <ul>
                    <li><strong>Identity:</strong> Your Google name, email address, and profile picture (used to identify and personalize your account).</li>
                    <li><strong>Authentication tokens:</strong> Access and refresh tokens necessary to perform calendar event operations on your behalf when you enable Google Calendar integration.</li>
                </ul>
            </section>

            <section>
                <h2>Google Calendar data usage</h2>
                <p>If you enable Google Calendar integration we use the Google Calendar Events scope to create, update, and delete events that correspond to tasks you create or assign. We only use this access to provide synchronization features and do not read or export calendar content beyond what is necessary to manage those events.</p>
            </section>

            <section>
                <h2>Data retention policy</h2>
                <p>We retain the minimum data necessary to operate the service. Specifically:</p>
                <ul>
                    <li><strong>Account profile (name, email, avatar):</strong> retained for as long as your account exists.</li>
                    <li><strong>Task data:</strong> retained until you delete it or your account.</li>
                    <li><strong>Google Calendar event identifiers:</strong> we store only the Google event ID required for synchronization.</li>
                </ul>
            </section>

            <section>
                <h2>Google User Data Retention and Deletion</h2>
                <p>We limit what Google user data we store and how long we keep it:</p>
                <ul>
                    <li>Only the Google Calendar <strong>event_id</strong> is stored on our servers to map tasks to calendar events.</li>
                    <li>If you disconnect your Google account or delete your Todo Batucore account, all Google-related data (including stored event IDs, tokens, and any cached Google metadata) will be deleted from our servers within 30 days.</li>
                    <li>You may request immediate deletion at any time by contacting us at the email below; we will process such requests promptly.</li>
                </ul>
            </section>

            <section>
                <h2>Data deletion policy</h2>
                <p>When you request deletion of your account or we remove your data as part of our retention rules, we permanently delete the data from our primary databases and any backups within our control within 30 days. Some residual copies may remain in logs or backups for a short period for disaster recovery, and will be purged according to our backup retention schedule.</p>
            </section>

            <section>
                <h2>No selling/sharing statement</h2>
                <p>Todo Batucore does not sell, rent, or trade your personal information. We do not share Google data with third parties except as required to provide the service (for example, communicating with Google APIs) and for essential infrastructure providers (hosting).</p>
            </section>

            <section>
                <h2>User revoke access info</h2>
                <p>You may revoke Todo Batucore's access to your Google account at any time from your Google Account permissions page: <a href="https://myaccount.google.com/permissions">https://myaccount.google.com/permissions</a>. After revoking access, synchronization features will stop and any stored Google data will be deleted within 30 days.</p>
            </section>

            <section>
                <h2>Contact</h2>
                <p>If you have questions or would like to request deletion or export of your data, please contact us at: <a href="mailto:abdozero2030@gmail.com">abdozero2030@gmail.com</a></p>
            </section>
        </div>
    </div>
</body>
</html>
