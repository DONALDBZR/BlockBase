<h1><?= htmlspecialchars($title ?? "About BlockBase CMS") ?></h1>
<p class="mb-4"><?= htmlspecialchars($message ?? "Learn more about our platform.") ?></p>
<div class="row">
    <div class="col-md-8">
        <h2>What is BlockBase CMS?</h2>
        <p class="mb-4">BlockBase is a PHP-based Content Management System designed to be modular, scalable, and easy to use. It provides a robust set of features for managing content, users, and settings, making it a great starting point for building web applications.</p>
        <h2>Key Features</h2>
        <ul class="mb-4">
            <li class="mb-2"><strong>Modular Architecture:</strong> Built with modularity in mind for easy extension</li>
            <li class="mb-2"><strong>Database Agnostic:</strong> Supports MySQL, PostgreSQL, and SQLite</li>
            <li class="mb-2"><strong>PHP 8.4 ORM:</strong> Modern object-relational mapping with full test coverage</li>
            <li class="mb-2"><strong>Comprehensive Logging:</strong> Built-in logging system with log rotation</li>
            <li class="mb-2"><strong>Enhanced Routing:</strong> Advanced routing system with controller support</li>
            <li class="mb-2"><strong>Security First:</strong> Built with security best practices in mind</li>
        </ul>
    </div>
    <div class="col-md-4">
        <div class="alert alert-info">
            <h3>Technology Stack</h3>
            <ul class="list-unstyled">
                <li class="mb-2">✅ PHP 8.4+</li>
                <li class="mb-2">✅ PDO for database operations</li>
                <li class="mb-2">✅ Composer for dependency management</li>
                <li class="mb-2">✅ PHPUnit for testing</li>
                <li class="mb-2">✅ PSR-4 autoloading</li>
            </ul>
        </div>
        <div class="text-center">
            <a href="/" class="btn btn-primary">Back to Home</a>
            <a href="/Contact" class="btn btn-secondary">Contact Us</a>
        </div>
    </div>
</div>
