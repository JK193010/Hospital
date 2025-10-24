<?php
// ✅ 1. Start session FIRST
session_start();

// ✅ 2. Load DB connection (optional for homepage, but kept for consistency)
// require_once __DIR__ . '/../includes/db_connect.php';

// ✅ 3. No auth needed for homepage
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Letik Hospital – Excellence in Healthcare</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    :root {
      --primary: #0d3b66;       /* Deep navy – trust, professionalism */
      --secondary: #00a896;     /* Teal – health, calm, healing */
      --light-bg: #f9fbfd;      /* Soft background */
      --text: #212529;
      --gray: #6c757d;
    }

    body {
      font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
      color: var(--text);
      background-color: var(--light-bg);
    }

    /* === Navbar === */
    .navbar {
      background-color: white;
      box-shadow: 0 2px 10px rgba(0,0,0,0.08);
      padding: 0.8rem 0;
    }

    .navbar-brand {
      font-weight: 800;
      color: var(--primary) !important;
      font-size: 1.5rem;
    }

    .nav-link {
      font-weight: 600;
      color: #495057 !important;
      margin: 0 0.6rem;
    }

    .nav-link:hover, .nav-link.active {
      color: var(--primary) !important;
    }

    /* === Hero Section (Text-only) === */
    .hero {
      background: white;
      padding: 5rem 0;
      text-align: center;
      border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    .hero h1 {
      font-weight: 800;
      font-size: 2.8rem;
      color: var(--primary);
      line-height: 1.2;
    }

    .hero p {
      font-size: 1.25rem;
      color: var(--gray);
      max-width: 700px;
      margin: 1.2rem auto 0;
    }

    .btn-primary-custom {
      background: var(--secondary);
      border: none;
      padding: 0.65rem 1.8rem;
      font-weight: 600;
      border-radius: 30px;
      color: white;
    }

    .btn-primary-custom:hover {
      background: #008a7a;
      transform: translateY(-2px);
      box-shadow: 0 4px 10px rgba(0, 168, 150, 0.25);
    }

    /* === Section Styling === */
    section {
      padding: 4.5rem 0;
    }

    .section-title {
      text-align: center;
      margin-bottom: 2.5rem;
    }

    .section-title h2 {
      font-weight: 700;
      color: var(--primary);
      font-size: 2.2rem;
    }

    .section-title p {
      color: var(--gray);
      max-width: 650px;
      margin: 0.8rem auto 0;
    }

    /* === Values / Services Cards (Icon-based) === */
    .feature-card {
      background: white;
      padding: 2rem;
      border-radius: 16px;
      text-align: center;
      box-shadow: 0 4px 12px rgba(13, 59, 102, 0.08);
      transition: transform 0.3s ease;
    }

    .feature-card:hover {
      transform: translateY(-5px);
    }

    .feature-card i {
      font-size: 2.2rem;
      color: var(--secondary);
      margin-bottom: 1.2rem;
    }

    .feature-card h5 {
      font-weight: 700;
      color: var(--primary);
      margin: 1rem 0 0.8rem;
    }

    /* === Footer === */
    footer {
      background: var(--primary);
      color: white;
      padding: 2.5rem 0 1.5rem;
    }

    footer a {
      color: rgba(255,255,255,0.85);
      text-decoration: none;
    }

    footer a:hover {
      color: white;
      text-decoration: underline;
    }

    .form-control:focus {
      border-color: var(--secondary);
      box-shadow: 0 0 0 0.25rem rgba(0, 168, 150, 0.25);
    }
  </style>
</head>
<body>

  <!-- 🔹 Navbar -->
  <nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
      <a class="navbar-brand" href="home.php">
        <i class="fas fa-hospital me-2"></i>Letik Hospital
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item"><a class="nav-link active" href="home.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="#">About</a></li>
          <li class="nav-item"><a class="nav-link" href="#">Services</a></li>
          <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
          <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- 🏥 Hero Section (Text Only) -->
  <div class="hero">
    <div class="container">
      <h1>Excellence in Healthcare, Rooted in Compassion</h1>
      <p>Letik Hospital provides 24/7 advanced medical care with integrity, innovation, and unwavering dedication to patient well-being.</p>
      <a href="#contact" class="btn btn-primary-custom btn-lg mt-3">Contact Us</a>
    </div>
  </div>

  <!-- 💡 Core Values -->
  <section>
    <div class="container">
      <div class="section-title">
        <h2>Our Commitment to You</h2>
        <p>Guided by ethics, expertise, and empathy</p>
      </div>

      <div class="row g-4">
        <div class="col-md-4">
          <div class="feature-card">
            <i class="fas fa-heartbeat"></i>
            <h5>Compassionate Care</h5>
            <p>Every patient is treated with dignity, respect, and personalized attention.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="feature-card">
            <i class="fas fa-microscope"></i>
            <h5>Clinical Excellence</h5>
            <p>Our team of board-certified specialists delivers evidence-based, cutting-edge treatments.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="feature-card">
            <i class="fas fa-shield-alt"></i>
            <h5>24/7 Availability</h5>
            <p>Emergency and critical care services are always ready, day or night.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 📩 Contact Form -->
  <section id="contact" style="background-color: white;">
    <div class="container">
      <div class="section-title">
        <h2>Get in Touch</h2>
        <p>We’re here to support your health journey. Reach out anytime.</p>
      </div>

      <!-- ✅ Alert Messages -->
      <?php if (isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success text-center">
          <?= htmlspecialchars($_SESSION['success_msg']); unset($_SESSION['success_msg']); ?>
        </div>
      <?php elseif (isset($_SESSION['error_msg'])): ?>
        <div class="alert alert-danger text-center">
          <?= htmlspecialchars($_SESSION['error_msg']); unset($_SESSION['error_msg']); ?>
        </div>
      <?php endif; ?>

      <div class="row justify-content-center">
        <div class="col-md-6">
          <form method="post" action="contact_process.php">
            <div class="mb-3">
              <label class="form-label">Full Name</label>
              <input type="text" class="form-control" name="name" placeholder="Enter your name" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" name="email" placeholder="name@example.com" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Message</label>
              <textarea class="form-control" name="message" rows="4" placeholder="How can we assist you?" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary-custom w-100">Send Message</button>
          </form>
        </div>
      </div>
    </div>
  </section>

  <!-- ⚙ Footer -->
  <footer>
    <div class="container">
      <div class="row">
        <div class="col-md-6 mb-4 mb-md-0">
          <h5><i class="fas fa-hospital me-2"></i> Letik Hospital</h5>
          <p class="mt-3">Providing world-class healthcare with compassion, innovation, and integrity since 2010.</p>
        </div>
        <div class="col-md-3">
          <h6>Quick Links</h6>
          <ul class="list-unstyled">
            <li><a href="#">About Us</a></li>
            <li><a href="#">Departments</a></li>
            <li><a href="#">Careers</a></li>
            <li><a href="#">Privacy Policy</a></li>
          </ul>
        </div>
        <div class="col-md-3">
          <h6>Contact</h6>
          <address>
            📍 123 Healing Avenue<br>
            Cityville, State 10001<br>
            📞 (123) 456-7890<br>
            ✉️ info@letikhospital.com
          </address>
        </div>
      </div>
      <hr class="my-4" style="background: rgba(255,255,255,0.1);">
      <p class="text-center mb-0">&copy; 2025 Letik Hospital. All Rights Reserved.</p>
    </div>
  </footer>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>