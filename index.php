<?php
require_once 'classes/Session.php';
$session = new Session();

// Check if user is already logged in
if ($session->isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        /* Specific styles for index.php */
        /* Ensure hero section uses variables from styles.css */
        .hero-section {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color)); /* Use variables from styles.css */
            color: white;
            padding: 100px 0;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url("data:image/svg+xml,%3Csvg width='64' height='64' viewBox='0 0 64 64' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M8 16c4.418 0 8-3.582 8-8s-3.582-8-8-8-8 3.582-8 8 3.582 8 8 8zm0 32c4.418 0 8-3.582 8-8s-3.582-8-8-8-8 3.582-8 8 3.582 8 8 8zm56 0c4.418 0 8-3.582 8-8s-3.582-8-8-8-8 3.582-8 8 3.582 8 8 8zm0-32c4.418 0 8-3.582 8-8s-3.582-8-8-8-8 3.582-8 8 3.582 8 8 8zM0 24c0 4.418 3.582 8 8 8s8-3.582 8-8-3.582-8-8-8-8 3.582-8 8zM64 24c0 4.418-3.582 8-8 8s-8-3.582-8-8 3.582-8 8-8 8 3.582 8 8zM0 40c0 4.418 3.582 8 8 8s8-3.582 8-8-3.582-8-8-8-8 3.582-8 8zM64 40c0 4.418-3.582 8-8 8s-8-3.582-8-8 3.582-8 8-8 8 3.582 8 8zM24 0c4.418 0 8 3.582 8 8s-3.582 8-8 8-8-3.582-8-8 3.582-8 8-8zM40 0c0 4.418 3.582 8 8 8s8-3.582 8-8-3.582-8-8-8-8 3.582-8 8zM24 64c4.418 0 8-3.582 8-8s-3.582-8-8-8-8 3.582-8 8 3.582 8 8 8zM40 64c0 4.418 3.582 8 8 8s8-3.582 8-8-3.582-8-8-8-8 3.582-8 8z' fill='%23ffffff' fill-opacity='0.1' fill-rule='evenodd'/%3E%3C/svg%3E");
            background-size: 64px 64px;
            z-index: 0;
        }

        .hero-section .container {
            position: relative;
            z-index: 1;
        }

        .hero-section h1 {
            font-size: 3.5rem;
            margin-bottom: 20px;
        }

        .hero-section p {
            font-size: 1.25rem;
            margin-bottom: 30px;
        }

        .features-section .feature-box {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,.05);
            transition: all 0.3s;
            border: 1px solid var(--light-color);
        }

        .features-section .feature-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,.1);
        }

        .features-section .feature-box i {
            color: var(--primary-color); /* Use primary color for icons */
        }

        .testimonial-section .blockquote {
            font-size: 1.1rem;
            font-style: italic;
            color: var(--dark-color); /* Use dark color for blockquote text */
        }

        .testimonial-section .blockquote-footer {
            color: #6c757d;
        }

        .cta-section {
             background: linear-gradient(to right, var(--secondary-color), var(--primary-color)); /* Use variables from styles.css */
             color: white;
             padding: 60px 0;
             text-align: center;
        }

        .cta-section h2 {
            margin-bottom: 15px;
        }

        .cta-section .btn-light {
            color: var(--primary-color); /* Use primary color for button text */
        }

         .cta-section .btn-outline-light {
            color: white;
            border-color: white;
        }

        .cta-section .btn-outline-light:hover {
            background: white;
            color: var(--primary-color); /* Use primary color for hover */
        }

        .footer {
            background: var(--dark-color); /* Use dark color for footer */
            color: rgba(255,255,255,.8);
            padding: 30px 0;
            text-align: center;
            font-size: 0.9rem;
        }

    </style>
</head>
<body class="fade-in">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white">
        <div class="container">
            <a class="navbar-brand" href="index.php">Document Management System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="index.php"><i class="bi bi-house-door me-1"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php"><i class="bi bi-box-arrow-in-right me-1"></i> Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="signup.php"><i class="bi bi-person-plus me-1"></i> Sign Up</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero-section text-white text-center d-flex align-items-center min-vh-100">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">Your Secure Document Management Solution</h1>
            <p class="lead mb-4">Simplify your workflow with secure storage, easy sharing, and robust access control, tailored for legal, HR, and enterprise environments.</p>
            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                <a href="signup.php" class="btn btn-light btn-lg px-4 me-sm-3">Get Started Today <i class="bi bi-arrow-right"></i></a>
                <a href="login.php" class="btn btn-outline-light btn-lg px-4">Existing User? Login <i class="bi bi-box-arrow-in-right"></i></a>
            </div>
        </div>
    </header>

    <!-- Features Section -->
    <section class="features-section py-5">
        <div class="container">
            <h2 class="text-center mb-5">Key Features Designed for Your Needs</h2>
            <div class="row text-center justify-content-center">
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="feature-box p-4 h-100">
                        <i class="bi bi-cloud-upload fa-3x mb-3 text-primary"></i>
                        <h3 class="h5">Effortless Document Upload & Storage</h3>
                        <p class="text-muted">Securely store and organize documents with easy drag-and-drop functionality.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 mb-4">
                     <div class="feature-box p-4 h-100">
                        <i class="bi bi-shield-check fa-3x mb-3 text-primary"></i>
                        <h3 class="h5">Advanced Role-Based Access Control</h3>
                        <p class="text-muted">Define granular permissions for different user roles, ensuring data security.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 mb-4">
                     <div class="feature-box p-4 h-100">
                        <i class="bi bi-file-earmark-check fa-3x mb-3 text-primary"></i>
                        <h3 class="h5">Integrated Digital Document Signing</h3>
                        <p class="text-muted">Facilitate legally binding electronic signatures directly within the platform.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 mb-4">
                     <div class="feature-box p-4 h-100">
                        <i class="bi bi-share fa-3x mb-3 text-primary"></i>
                        <h3 class="h5">Secure Document Sharing & Collaboration</h3>
                        <p class="text-muted">Share documents with internal and external stakeholders securely.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 mb-4">
                     <div class="feature-box p-4 h-100">
                        <i class="bi bi-clock-history fa-3x mb-3 text-primary"></i>
                        <h3 class="h5">Comprehensive Version Control</h3>
                        <p class="text-muted">Track every change and easily revert to previous document versions.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 mb-4">
                     <div class="feature-box p-4 h-100">
                        <i class="bi bi-sliders fa-3x mb-3 text-primary"></i>
                        <h3 class="h5">Intuitive Admin Dashboard</h3>
                        <p class="text-muted">Powerful tools for administrators to manage users, roles, and monitor system activity.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonial Section (Example) -->
    <section class="testimonial-section bg-light py-5">
        <div class="container">
            <h2 class="text-center mb-5">What Our Clients Say</h2>
            <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <blockquote class="blockquote text-center">
                                    <p class="mb-0">"This document management system has revolutionized our HR processes. It's intuitive, secure, and the role-based access is exactly what we needed."</p>
                                    <footer class="blockquote-footer mt-2">Kareem Ahmed, <cite title="Source Title">HR Manager</cite></footer>
                                </blockquote>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <blockquote class="blockquote text-center">
                                    <p class="mb-0">"Secure document signing has never been easier. Our legal team saves hours every week using this platform."</p>
                                    <footer class="blockquote-footer mt-2">Adham Ahmed, <cite title="Source Title">Legal Counsel</cite></footer>
                                </blockquote>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="cta-section py-5">
        <div class="container">
            <h2>Start Managing Your Documents Efficiently Today</h2>
            <p class="lead mb-4">Join thousands of professionals who trust our secure platform.</p>
             <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                <a href="signup.php" class="btn btn-light btn-lg px-4 me-sm-3">Sign Up Now <i class="bi bi-person-plus"></i></a>
                <a href="login.php" class="btn btn-outline-light btn-lg px-4">Already a Member? Login <i class="bi bi-box-arrow-in-right"></i></a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer bg-dark text-white text-center py-4">
        <div class="container">
            <p class="mb-0">&copy; 2023 Document Management System. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 