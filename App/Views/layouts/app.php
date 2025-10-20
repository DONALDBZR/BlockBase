<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($title ?? "BlockBase CMS") ?></title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                line-height: 1.6;
                color: #333;
                background-color: #f8f9fa;
            }
            .container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 1rem;
            }
            .header {
                background: #fff;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                margin-bottom: 2rem;
            }
            .header-content {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 1rem 0;
            }
            .logo {
                font-size: 1.5rem;
                font-weight: bold;
                color: #2c3e50;
                text-decoration: none;
            }
            .nav {
                display: flex;
                gap: 1rem;
            }
            .nav a {
                padding: 0.5rem 1rem;
                background: #007bff;
                color: white;
                text-decoration: none;
                border-radius: 4px;
                transition: background-color 0.3s;
            }
            .nav a:hover {
                background: #0056b3;
            }
            .main-content {
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                padding: 2rem;
                margin-bottom: 2rem;
            }
            .footer {
                text-align: center;
                color: #6c757d;
                font-size: 0.9rem;
                padding: 2rem 0;
                border-top: 1px solid #e1e5e9;
            }
            .alert {
                padding: 1rem;
                border-radius: 4px;
                margin-bottom: 1rem;
            }
            .alert-success {
                background: #d4edda;
                color: #155724;
                border: 1px solid #c3e6cb;
            }
            .alert-error {
                background: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
            }
            .alert-info {
                background: #d1ecf1;
                color: #0c5460;
                border: 1px solid #bee5eb;
            }
            .btn {
                display: inline-block;
                padding: 0.75rem 1.5rem;
                background: #007bff;
                color: white;
                text-decoration: none;
                border-radius: 4px;
                border: none;
                cursor: pointer;
                font-size: 1rem;
                transition: background-color 0.3s;
            }
            .btn:hover {
                background: #0056b3;
            }
            .btn-secondary {
                background: #6c757d;
            }
            .btn-secondary:hover {
                background: #545b62;
            }
            .btn-success {
                background: #28a745;
            }
            .btn-success:hover {
                background: #1e7e34;
            }
            .btn-danger {
                background: #dc3545;
            }
            .btn-danger:hover {
                background: #c82333;
            }
            .form-group {
                margin-bottom: 1rem;
            }
            .form-group label {
                display: block;
                margin-bottom: 0.5rem;
                font-weight: bold;
            }
            .form-group input,
            .form-group textarea,
            .form-group select {
                width: 100%;
                padding: 0.75rem;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 1rem;
            }
            .form-group textarea {
                height: 120px;
                resize: vertical;
            }
            .text-center {
                text-align: center;
            }
            .text-right {
                text-align: right;
            }
            .mb-1 { margin-bottom: 0.25rem; }
            .mb-2 { margin-bottom: 0.5rem; }
            .mb-3 { margin-bottom: 1rem; }
            .mb-4 { margin-bottom: 1.5rem; }
            .mb-5 { margin-bottom: 3rem; }
            .mt-1 { margin-top: 0.25rem; }
            .mt-2 { margin-top: 0.5rem; }
            .mt-3 { margin-top: 1rem; }
            .mt-4 { margin-top: 1.5rem; }
            .mt-5 { margin-top: 3rem; }
            @media (max-width: 768px) {
                .header-content {
                    flex-direction: column;
                    gap: 1rem;
                }
                .nav {
                    flex-wrap: wrap;
                    justify-content: center;
                }
                .container {
                    padding: 0 0.5rem;
                }
                .main-content {
                    padding: 1rem;
                }
            }
        </style>
        <?php if (isset($additionalStyles)): ?>
            <?= $additionalStyles ?>
        <?php endif; ?>
    </head>
    <body>
        <header class="header">
            <div class="container">
                <div class="header-content">
                    <a href="/" class="logo">BlockBase CMS</a>
                    <nav class="nav">
                        <a href="/">Home</a>
                        <a href="/About">About</a>
                        <a href="/Contact">Contact</a>
                        <a href="/Admin">Admin</a>
                    </nav>
                </div>
            </div>
        </header>
        <main class="container">
            <div class="main-content">
                <?php if (isset($flashMessages)): ?>
                    <?php foreach ($flashMessages as $type => $message): ?>
                        <div class="alert alert-<?= $type ?>">
                            <?= htmlspecialchars($message) ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <?= $content ?? "" ?>
            </div>
        </main>
        <footer class="footer">
            <div class="container">
                <p>&copy; <?= date('Y') ?> BlockBase CMS. Built with PHP 8.4.</p>
            </div>
        </footer>
        <?php if (isset($additionalScripts)): ?>
            <?= $additionalScripts ?>
        <?php endif; ?>
    </body>
</html>
