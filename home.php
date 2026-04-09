<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Income and Expense Tracker</title>
  <style>
    /* Reset */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      background: #ffffff;
      color: #2b2b2b;
      line-height: 1.6;
      overflow-x: hidden;
    }

    /* Header */
    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 5%;
      background: #e6f2ff;  /* Light blue header */
      border-bottom: 1px solid #cce0ff;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      z-index: 1000;
    }

    header .logo {
      font-size: 1.4rem;
      font-weight: bold;
      color: #1a4d8f;
    }

    nav ul {
      display: flex;
      gap: 2rem;
      list-style: none;
    }

    nav ul li a {
      text-decoration: none;
      color: #1a4d8f;
      font-weight: 500;
      transition: color 0.3s;
    }

    nav ul li a:hover {
      color: #0056cc;
    }

    .auth-buttons {
      display: flex;
      gap: 1rem;
    }

    .auth-buttons a {
      padding: 0.6rem 1.2rem;
      border-radius: 5px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease-in-out;
    }

    .login-btn {
      border: 1px solid #1a4d8f;
      color: #1a4d8f;
      background: #fff;
    }
    .login-btn:hover {
      background: #1a4d8f;
      color: #fff;
    }

    .signup-btn {
      background: #1a4d8f;
      color: #fff;
    }
    .signup-btn:hover {
      background: #003d80;
    }

    /* Hero Section */
    .hero {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 6rem 5% 3rem;
      gap: 3rem;
      flex-wrap: wrap;
    }

    .hero-text {
      flex: 1;
      min-width: 300px;
    }

    .hero-text h1 {
      font-size: 2.6rem;
      font-weight: 700;
      margin-bottom: 1rem;
      color: #1a4d8f;
    }

    .hero-text p {
      margin-bottom: 2rem;
      font-size: 1.1rem;
      color: #444;
    }

    .hero-buttons {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
    }

    .hero-buttons a {
      padding: 0.9rem 1.5rem;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s;
    }

    .download-btn {
      border: 2px solid #1a4d8f;
      color: #1a4d8f;
      background: #fff;
    }
    .download-btn:hover {
      background: #1a4d8f;
      color: #fff;
    }

    .get-started-btn {
      background: #1a4d8f;
      color: #fff;
    }
    .get-started-btn:hover {
      background: #003d80;
    }

    .hero-image {
      flex: 1;
      text-align: center;
      min-width: 280px;
    }

    .hero-image img {
      max-width: 100%;
      border-radius: 15px;
      box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.15);
      transition: transform 0.5s ease;
    }

    .hero-image img:hover {
      transform: scale(1.05);
    }

    /* About Project Section */
    .about-project {
      padding: 4rem 8%;
      background: #f7faff;
      text-align: center;
    }

    .about-project h2 {
      font-size: 2rem;
      margin-bottom: 1.5rem;
      color: #1a4d8f;
    }

    .about-project p {
      font-size: 1.05rem;
      color: #333;
      max-width: 900px;
      margin: 0 auto;
      line-height: 1.8;
    }

    /* Features Section */
    .features {
      padding: 4rem 8% 5rem;
      background: #e6f2ff;
      text-align: center;
    }

    .features h2 {
      font-size: 2rem;
      margin-bottom: 2.5rem;
      color: #1a4d8f;
    }

    .features-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 2rem;
    }

    .feature-card {
      background: #fff;
      padding: 2rem 1.5rem;
      border-radius: 12px;
      box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .feature-card:hover {
      transform: translateY(-10px);
      box-shadow: 0px 12px 25px rgba(0, 0, 0, 0.15);
    }

    .feature-card .icon {
      font-size: 2.5rem;
      display: block;
      margin-bottom: 1rem;
    }

    .feature-card h3 {
      font-size: 1.3rem;
      margin-bottom: 0.8rem;
      color: #1a4d8f;
    }

    .feature-card p {
      font-size: 0.95rem;
      color: #555;
      line-height: 1.5;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .hero {
        flex-direction: column;
        text-align: center;
      }

      nav ul {
        display: none;
      }

      .auth-buttons {
        display: none;
      }
    }
	.hero-text p {
  margin-bottom: 2rem; /* Increase gap below paragraph */
  font-size: 1.1rem;
  color: #444;
}

.hero-buttons {
  margin-top: 2rem; /* Optional extra spacing if needed */
}
.hero-text h1 {
  font-size: 2rem; /* larger and prominent */
  font-weight: 800;
  margin-bottom: 1rem;
  color: #1a4d8f;
  line-height: 1.2;
  letter-spacing: 1px;
  background: linear-gradient(90deg, #1a4d8f, #0056cc); /* subtle gradient */
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent; /* gradient text effect */
  text-transform: uppercase; /* optional for emphasis */
}

h3 {
	color: #1a4d8f;
}


  </style>
</head>
<body>

  <!-- Header -->
  <header>
    <div class="logo">Income and Expense Tracker</div>
    <div class="auth-buttons">
      <a href="login.php" class="login-btn">Login</a>
      <a href="register.php" class="signup-btn">Sign up for free</a>
    </div>
  </header>

  <!-- Hero Section -->
  <section class="hero">
    <div class="hero-text">
      <h1>Take Control of Your Finances</h1>
    <p>
      Simplify your financial management with our intuitive Income and Expense Tracker. 
      Monitor your spending, optimize your budget, and gain actionable insights to achieve your financial goals with ease.
    </p>
	   <div class="hero-info">
      <h3>Why Choose Our Tracker?</h3>
      <div class="info-cards">
        <div class="info-card">💡<span>Instant budget insights and alerts</span></div>
        <div class="info-card">📂<span>Organize finances with categories and reports</span></div>
        <div class="info-card">📊<span>Analyze spending with clear visual charts</span></div>
        <div class="info-card">🔒<span>Secure and user-friendly platform</span></div>
      </div>
    </div>
      <div class="hero-buttons">
        <a href="login.php" class="get-started-btn">Get Started</a>
      </div>
    </div>
    <div class="hero-image">
      <img src="cover_image2.png" alt="App Screenshot">
    </div>
  </section>

  <!-- About Project Section -->
  <section class="about-project">
    <h2>About This Project</h2>
    <p>
      The Income and Expense Tracker is a modern web application designed to help individuals and small businesses
      manage their finances efficiently. Users can create categories for income and expenses, set budgets, and
      monitor their spending habits through real-time alerts. Interactive charts and visual reports allow for
      quick analysis, empowering users to make informed financial decisions. With a user-friendly interface
      and secure login, the platform ensures that financial management is both simple and reliable.
    </p>
  </section>

  <!-- Features Section -->
  <section class="features">
    <h2>Key Features</h2>
    <div class="features-container">
      <div class="feature-card">
        <span class="icon">📂</span>
        <h3>Category Management</h3>
        <p>Create and organize categories for your income and expenses, making budgeting simple and structured.</p>
      </div>
      <div class="feature-card">
        <span class="icon">🔔</span>
        <h3>Budget Alerts</h3>
        <p>Receive instant notifications when your spending exceeds the set budget, helping you stay in control of finances.</p>
      </div>
      <div class="feature-card">
        <span class="icon">📊</span>
        <h3>Visual Reports</h3>
        <p>Generate interactive charts and graphs to analyze your spending patterns and make informed financial decisions.</p>
      </div>
    </div>
  </section>

</body>
</html>
