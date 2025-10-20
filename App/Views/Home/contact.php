<h1><?= htmlspecialchars($title ?? "Contact Us") ?></h1>
<p class="mb-4"><?= htmlspecialchars($message ?? "We would love to hear from you.") ?></p>

<div class="row">
    <div class="col-md-8">
        <form method="POST" action="/Contact">
            <div class="form-group">
                <label for="name">Name:</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    required
                />
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    required
                />
            </div>
            <div class="form-group">
                <label for="subject">Subject:</label>
                <input
                    type="text"
                    id="subject"
                    name="subject"
                    required
                />
            </div>
            <div class="form-group">
                <label for="message">Message:</label>
                <textarea id="message" name="message" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Send Message</button>
        </form>
    </div>
    <div class="col-md-4">
        <div class="alert alert-info">
            <h3>Other Ways to Reach Us</h3>
            <ul class="list-unstyled">
                <li class="mb-2"><strong>Email:</strong><br>andygaspard@hotmail.com</li>
                <li class="mb-2"><strong>GitHub:</strong><br><a href="https://github.com/DONALDBZR/BlockBase" target="_blank">github.com/DONALDBZR/BlockBase</a></li>
                <li class="mb-2"><strong>Documentation:</strong><br>Coming soon...</li>
            </ul>
        </div>
        <div class="text-center">
            <a href="/" class="btn btn-secondary">Back to Home</a>
            <a href="/About" class="btn btn-secondary">About Us</a>
        </div>
    </div>
</div>
