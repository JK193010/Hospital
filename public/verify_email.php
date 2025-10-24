<?php
$pageTitle = "Email Verification - Hospital System";
include "includes/header.php";
?>

<div class="d-flex align-items-center justify-content-center vh-100">
  <div class="card shadow-lg p-4" style="max-width: 400px; width: 100%;">
    <div class="card-body">
      <h3 class="text-center mb-4 text-primary">Verify Your Email</h3>
      <p class="text-muted text-center">Enter your email address to receive a verification code.</p>

      <form action="send_mail.php" method="POST">
        <div class="mb-3">
          <label for="email" class="form-label">Email Address</label>
          <input type="email" name="email" id="email" class="form-control" placeholder="e.g. user@example.com" required>
        </div>
        <div class="d-grid">
          <button type="submit" class="btn btn-primary">Send Verification Code</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include "includes/footer.php"; ?>
