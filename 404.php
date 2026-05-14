<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | Marrthings</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --blue: #1a56db;
            --orange: #f97316;
            --dark: #0f172a;
            --white: #ffffff;
            --gray-light: #f8fafc;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--gray-light);
            color: var(--dark);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
            overflow: hidden;
        }

        .container {
            max-width: 600px;
        }

        .error-code {
            font-size: clamp(8rem, 20vw, 12rem);
            font-weight: 800;
            line-height: 1;
            background: linear-gradient(135deg, var(--blue), var(--orange));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
            letter-spacing: -5px;
        }

        h1 {
            font-size: 2rem;
            margin-bottom: 1.5rem;
            color: var(--dark);
        }

        p {
            color: #64748b;
            font-size: 1.1rem;
            margin-bottom: 2.5rem;
            line-height: 1.6;
        }

        .btn-home {
            display: inline-block;
            background: var(--blue);
            color: white;
            padding: 1rem 2.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s;
            box-shadow: 0 10px 20px rgba(26, 86, 219, 0.2);
        }

        .btn-home:hover {
            transform: translateY(-3px);
            background: var(--orange);
            box-shadow: 0 15px 30px rgba(249, 115, 22, 0.3);
        }

        /* Grid Background */
        .grid-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: radial-gradient(#e2e8f0 1px, transparent 1px);
            background-size: 30px 30px;
            opacity: 0.3;
            z-index: -1;
        }
    </style>
</head>
<body>
    <div class="grid-bg"></div>
    <div class="container">
        <div class="error-code">404</div>
        <h1>Lost in Digital Space?</h1>
        <p>The page you're looking for has moved to another dimension or never existed in this one. Let's get you back on track.</p>
        <a href="index" class="btn-home">Return to Homepage ➔</a>
    </div>
</body>
</html>
