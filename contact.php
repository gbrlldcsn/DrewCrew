<?php
include 'includes/header.php';

$message = "";
$messageType = "";

if (isset($_POST['submit'])) {
    $name = htmlspecialchars($_POST['name'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $subject = htmlspecialchars($_POST['subject'] ?? '');
    $message_text = htmlspecialchars($_POST['message'] ?? '');
    
    if ($name && $email && $subject && $message_text) {
        // In a real application, you would send an email here
        // For now, we'll just show a success message
        $message = "Thank you for contacting us! We'll get back to you soon.";
        $messageType = "success";
    } else {
        $message = "Please fill in all fields.";
        $messageType = "danger";
    }
}
?>

<h1 class="fw-bold mb-4 text-center">Contact Us</h1>

<div class="row justify-content-center mb-5">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <h3 class="fw-bold mb-3">Get in Touch</h3>
                <p class="text-muted mb-4">Have a question or need assistance? Fill out the form below and we'll get back to you as soon as possible.</p>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="subject" class="form-label fw-semibold">Subject *</label>
                        <input type="text" class="form-control" id="subject" name="subject" required>
                    </div>

                    <div class="mb-3">
                        <label for="message" class="form-label fw-semibold">Message *</label>
                        <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                    </div>

                    <button type="submit" name="submit" class="btn btn-gold w-100">Send Message</button>
                </form>

                <hr class="my-4">

                <h5 class="fw-bold mb-3">Other Ways to Reach Us</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <p class="mb-1"><strong>Email:</strong></p>
                        <p class="text-muted">drewcrew@gmail.com</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <p class="mb-1"><strong>Business Hours:</strong></p>
                        <p class="text-muted">Monday - Friday: 9:00 AM - 6:00 PM</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

