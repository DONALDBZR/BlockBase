<div class="text-center mb-4">
    <h1 class="mb-3">Welcome to BlockBase CMS</h1>
    <p class="mb-4">A modern PHP Content Management System</p>
</div>
<div class="alert alert-info mb-4">
    <h3><?= htmlspecialchars($title ?? "Welcome") ?></h3>
    <p><?= htmlspecialchars($message ?? "Welcome to BlockBase CMS!") ?></p>
</div>
<h2>Features</h2>
<div class="row">
    <div class="col-md-6">
        <ul class="list-unstyled">
            <li class="mb-2">✅ Modular architecture</li>
            <li class="mb-2">✅ Scalable and flexible design</li>
            <li class="mb-2">✅ Easy to use and customize</li>
            <li class="mb-2">✅ Database-agnostic design</li>
        </ul>
    </div>
    <div class="col-md-6">
        <ul class="list-unstyled">
            <li class="mb-2">✅ PHP 8.4 ORM layer</li>
            <li class="mb-2">✅ Comprehensive logging system</li>
            <li class="mb-2">✅ Enhanced routing system</li>
            <li class="mb-2">✅ Controller-based architecture</li>
        </ul>
    </div>
</div>
<div class="text-center mt-4">
    <a href="/About" class="btn btn-primary">Learn More</a>
    <a href="/Contact" class="btn btn-secondary">Get in Touch</a>
</div>
<style>
.row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -0.5rem;
}
.col-md-6 {
    flex: 0 0 50%;
    padding: 0 0.5rem;
}
.list-unstyled {
    list-style: none;
    padding: 0;
}
@media (max-width: 768px) {
    .col-md-6 {
        flex: 0 0 100%;
    }
}
</style>
