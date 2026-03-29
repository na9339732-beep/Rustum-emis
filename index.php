<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EMIS - Education Management & Information System</title>
  <meta name="description" content="Streamline your school's operations with EMIS – the all-in-one platform for student, teacher, and administrative management.">
  <link rel="stylesheet" href="./assets/css/styles.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    :root {
      --primary: #4f46e5; 
      --primary-dark: #4338ca;
      --secondary: #1083b9ff;
      --text: #1e293b;
      --muted: #64748b;
      --light-bg: #f8fafc;
      --bg: #f6f8fb;
      --card: #ffffff;
      --muted: #667085;
      --accent: #3A6FF8;
      --accent-2: #6A2FF8;
      --glass: rgba(255, 255, 255, 0.65);
      --radius: 16px;
      --shadow: 0 10px 25px rgba(18, 38, 63, 0.08);
      --glass-shadow: 0 12px 35px rgba(34, 41, 61, 0.08);
      --transition: all 0.28s cubic-bezier(0.4, 0, 0.2, 1);
        }
    body {
      font-family: 'Inter', sans-serif;
      color: var(--text);
      line-height: 1.6;
    }
    h1, h2, h3, h4, h5 {
      font-weight: 700;
      line-height: 1.2;
    }
    .feature-icon {
      font-size: 2.5rem;
      color: var(--primary);
      margin-bottom: 1rem;
    }
    .c2a {
      background: linear-gradient(135deg, var(--primary-dark), var(--accent-2));
    }
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 12px 20px;
      border: none;
      border-radius: 12px;
      font-weight: 600;
      font-size: 0.95rem;
      text-decoration: none;
      background: linear-gradient(90deg, var(--accent), var(--accent-2));
      color: white;
      box-shadow: 0 10px 25px rgba(106, 47, 248, 0.22);
      transition: var(--transition);
      cursor: pointer;
      white-space: nowrap;
      min-width: fit-content;
    }

    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 15px 30px rgba(106, 47, 248, 0.3);
    }

    .btn.ghost {
      background: transparent;
      color: var(--accent);
      border: 1.5px solid var(--accent);
      box-shadow: none;
    }

    .btn.ghost:hover {
      background:linear-gradient(90deg, var(--accent), var(--accent-2));
      color: white;
    }
    .hero-bg {
      background: linear-gradient(135deg, #f0f4ff 0%, #e0e7ff 100%);
    }
    section {
      padding: 5rem 0;
    }
    footer {
      background-color: #1e293b;
      color: #cbd5e1;
    }
  </style>
</head>
<body>
<section class="hero-bg py-5 py-lg-7 text-center text-lg-start">
  <div class="container">
    <div class="row align-items-center gy-5">
      <div class="col-lg-6">
        <h1 class="display-4 fw-bold mb-4">Streamline Your School's Success with EMIS</h1>
        <p class="lead text-muted mb-5">The all-in-one Education Management & Information System designed to empower administrators, teachers, and parents with seamless tools for efficient school operations.</p>
        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start">
          <a href="register.php" class="btn shadow">Get Started Free</a>
          <a href="login.php" class="btn ghost ">Sign In</a>
        </div>
      </div>
      <div class="col-lg-6 text-center">
        <!-- Placeholder for hero illustration/dashboard preview -->
        <img src="./assets/images/dashboard.png" alt="EMIS Dashboard Preview" class="img-fluid rounded-4 shadow-lg">
      </div>
    </div>
  </div>
</section>

<!-- Features Section -->
<section class="bg-white">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="display-5 fw-bold">Powerful Features for Modern Education</h2>
      <p class="lead text-muted">Everything you need to manage your institution efficiently and securely.</p>
    </div>
    <div class="row g-5">
      <div class="col-md-6 col-lg-3">
        <div class="text-center p-4">
          <i class="fas fa-user-graduate feature-icon"></i>
          <h4 class="fw-bold mb-3">Student Management</h4>
          <p class="text-muted">Track attendance, grades, assignments, and student profiles with real-time updates and secure access.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="text-center p-4">
          <i class="fas fa-chalkboard-teacher feature-icon"></i>
          <h4 class="fw-bold mb-3">Teacher Management</h4>
          <p class="text-muted">Manage schedules, performance reviews, and class assignments with complete transparency.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="text-center p-4">
          <i class="fas fa-users feature-icon"></i>
          <h4 class="fw-bold mb-3">Parent Portal</h4>
          <p class="text-muted">Empower parents with real-time access to progress reports, attendance, and direct communication.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="text-center p-4">
          <i class="fas fa-chart-bar feature-icon"></i>
          <h4 class="fw-bold mb-3">Reports & Analytics</h4>
          <p class="text-muted">Generate insightful reports and dashboards for data-driven decision making.</p>
        </div>
      </div>
    </div>
  </div>
</section>


<!-- Call-to-Action Section -->
<section class="py-5 text-center text-white c2a">
  <div class="container">
    <h2 class="display-5 fw-bold mb-4">Ready to Transform Your School Management?</h2>
    <p class="lead mb-5">Join thousands of institutions already using EMIS to simplify operations and focus on education.</p>
    <a href="register.php" class="btn ghost shadow">Get yourself <b>Registered</b></a>
  </div>
</section>

<!-- Footer -->
<footer class="py-5 text-center mx-auto justify-content-center align-items-center d-flex flex-column gap-3">
    <p class="mb-0">&copy; 2025 EMIS  All Rights Reserved  <a href="#" class="text-decoration-none text-muted">Privacy Policy</a>  <a href="#" class="text-decoration-none text-muted">Terms of Service</a></p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7H6jIeHz" crossorigin="anonymous"></script>
</body>
</html>