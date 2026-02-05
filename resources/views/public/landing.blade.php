<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google-site-verification" content="h1Fs3IbIUF8YlHVRbEjb5te9hFjnwJRPXYLMA5TjxAM" />
    <title>Todo Batucore - Professional Task Management</title>
    
    <!-- We use inline CSS for the landing page to ensure Googlebot sees it immediately without JS execution -->
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-600: #475569;
            --slate-800: #1e293b;
            --slate-900: #0f172a;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--slate-50);
            color: var(--slate-900);
            margin: 0;
            line-height: 1.5;
        }
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }
        nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 5rem;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--slate-200);
            z-index: 50;
            display: flex;
            align-items: center;
        }
        .nav-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }
        .logo-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .logo-box {
            width: 2.5rem;
            height: 2.5rem;
            background: var(--primary);
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 900;
            font-size: 1.25rem;
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
        }
        .logo-text {
            font-size: 1.25rem;
            font-weight: 900;
            letter-spacing: -0.025em;
            color: var(--slate-800);
        }
        .btn {
            padding: 0.625rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-block;
        }
        .btn-primary {
            background: var(--primary);
            color: white;
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
        }
        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }
        .hero {
            padding-top: 8rem;
            padding-bottom: 5rem;
            display: grid;
            grid-template-columns: 1fr;
            gap: 4rem;
            align-items: center;
        }
        @media (min-width: 1024px) {
            .hero {
                grid-template-columns: 1fr 1fr;
            }
        }
        .badge {
            display: inline-flex;
            padding: 0.5rem 1rem;
            background: #eef2ff;
            border: 1px solid #e0e7ff;
            border-radius: 9999px;
            color: var(--primary);
            font-size: 0.875rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }
        h1 {
            font-size: 3rem;
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            letter-spacing: -0.025em;
        }
        @media (min-width: 1024px) {
            h1 { font-size: 4.5rem; }
        }
        .text-primary { color: var(--primary); }
        .hero-p {
            font-size: 1.25rem;
            color: var(--slate-600);
            margin-bottom: 2.5rem;
            max-width: 32rem;
        }
        .hero-img-container {
            position: relative;
        }
        .hero-blur {
            position: absolute;
            inset: 0;
            background: rgba(79, 70, 229, 0.2);
            filter: blur(120px);
            border-radius: 9999px;
            z-index: -1;
        }
        .hero-img {
            max-width: 100%;
            height: auto;
            border-radius: 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border: 1px solid var(--slate-200);
        }
        footer {
            padding: 3rem 0;
            border-top: 1px solid var(--slate-200);
            margin-top: 5rem;
        }
        .footer-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2rem;
        }
        @media (min-width: 768px) {
            .footer-content {
                flex-direction: row;
                justify-content: space-between;
            }
        }
        .footer-links {
            display: flex;
            gap: 2rem;
            font-size: 0.875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .footer-links a {
            color: var(--slate-600);
            text-decoration: none;
        }
        .footer-links a:hover {
            color: var(--primary);
        }
        .copyright {
            font-size: 0.875rem;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <nav>
        <div class="container nav-content">
            <div class="logo-group">
                <div class="logo-box">T</div>
                <span class="logo-text">Todo Batucore</span>
            </div>
            <a href="/login" class="btn btn-primary text-white">Get Started</a>
        </div>
    </nav>

    <main class="container hero">
        <div>
            <div class="badge">v1.0 is Live!</div>
            <h1>The smartest way to <span class="text-primary">organize</span> your work.</h1>
            <p class="hero-p">
                Todo Batucore helps you manage personal tasks, team projects, and deadlines in one beautiful interface.
            </p>
            <div class="cta-group">
                <a href="/login" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.125rem;">Start Building Today</a>
            </div>
        </div>
        <div class="hero-img-container">
            <div class="hero-blur"></div>
            <img src="https://i.postimg.cc/jqQRtc95/thinker-(1).png" alt="Todo Batucore App Preview" class="hero-img">
        </div>
    </main>

    <footer class="container">
        <div class="footer-content">
            <span style="font-weight: 900; color: var(--slate-800);">Todo Batucore</span>
            <div class="footer-links">
                <a href="/privacy-policy">Privacy Policy</a>
                <a href="/terms-of-service">Terms of Service</a>
                <a href="mailto:abdozero2030@gmail.com">Contact</a>
            </div>
            <p class="copyright">© 2026 Todo Batucore.</p>
        </div>
    </footer>
</body>
</html>
