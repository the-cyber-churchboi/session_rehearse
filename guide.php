<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Interactive Guide | EOD Platform</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #0a2540;
      --secondary: #00d4aa;
      --accent: #635bff;
      --accent-light: #8a85ff;
      --light: #f6f9fc;
      --dark: #0a2540;
      --success: #00c9a7;
      --warning: #ffb800;
      --info: #14b8ff;
      --gray: #7c8ca1;
      --transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
      --shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
      --shadow-lg: 0 25px 50px rgba(0, 0, 0, 0.15);
      --radius: 16px;
      --glass: rgba(255, 255, 255, 0.08);
      --glass-border: rgba(255, 255, 255, 0.18);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      font-family: 'Inter', sans-serif;
      line-height: 1.6;
      color: var(--dark);
      background: linear-gradient(135deg, #f6f9fc 0%, #f0f4f8 100%);
      min-height: 100vh;
      overflow-x: hidden;
    }

    h1, h2, h3, h4, h5 {
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-weight: 700;
      line-height: 1.2;
    }

    a {
      text-decoration: none;
      color: inherit;
      transition: var(--transition);
    }

    .container {
      width: 100%;
      max-width: 1400px;
      margin: 0 auto;
      padding: 0 30px;
    }

    /* Header Styles */
    header {
      background: linear-gradient(135deg, var(--primary) 0%, #1a365d 100%);
      color: white;
      padding: 0;
      position: relative;
      overflow: hidden;
      min-height: 400px;
      display: flex;
      align-items: center;
    }

    .header-bg {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: 
        radial-gradient(circle at 20% 80%, rgba(99, 91, 255, 0.15) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(0, 212, 170, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 40% 40%, rgba(20, 184, 255, 0.1) 0%, transparent 50%);
      z-index: 1;
    }

    .header-content {
      position: relative;
      z-index: 2;
      width: 100%;
      padding: 60px 0;
    }

    .header-top {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 60px;
    }

    .logo-section {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .logo-section img {
      height: 50px;
      width: auto;
    }

    .logo-text {
      font-size: 28px;
      font-weight: 800;
      letter-spacing: -0.5px;
    }

    .back-button {
      display: inline-flex;
      align-items: center;
      gap: 12px;
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(10px);
      padding: 12px 24px;
      border-radius: 50px;
      font-weight: 500;
      transition: var(--transition);
      border: 1px solid var(--glass-border);
    }

    .back-button:hover {
      background: rgba(255, 255, 255, 0.25);
      transform: translateX(-5px);
    }

    .page-title {
      text-align: center;
      max-width: 800px;
      margin: 0 auto;
    }

    .page-title h1 {
      font-size: 3.5rem;
      margin-bottom: 20px;
      background: linear-gradient(90deg, #ffffff, #e0f7fa);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      letter-spacing: -1px;
    }

    .page-title p {
      font-size: 1.3rem;
      opacity: 0.9;
      margin-bottom: 30px;
    }

    .search-box {
      max-width: 500px;
      margin: 0 auto;
      position: relative;
    }

    .search-box input {
      width: 100%;
      padding: 16px 50px 16px 20px;
      border-radius: 50px;
      border: none;
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(10px);
      color: white;
      font-size: 1rem;
      transition: var(--transition);
      border: 1px solid var(--glass-border);
    }

    .search-box input::placeholder {
      color: rgba(255, 255, 255, 0.7);
    }

    .search-box input:focus {
      outline: none;
      background: rgba(255, 255, 255, 0.25);
      box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.3);
    }

    .search-box i {
      position: absolute;
      right: 20px;
      top: 50%;
      transform: translateY(-50%);
      color: white;
      font-size: 1.2rem;
    }

    /* Main Content */
    main {
      padding: 80px 0;
      position: relative;
    }

    .guide-layout {
      display: grid;
      grid-template-columns: 300px 1fr;
      gap: 40px;
    }

    /* Sidebar Navigation */
    .sidebar-nav {
      position: sticky;
      top: 100px;
      height: fit-content;
    }

    .nav-card {
      background: white;
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 30px;
    }

    .nav-title {
      font-size: 1.3rem;
      margin-bottom: 20px;
      color: var(--primary);
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .nav-links {
      list-style: none;
    }

    .nav-links li {
      margin-bottom: 12px;
      position: relative;
    }

    .nav-links a {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 15px;
      border-radius: 10px;
      transition: var(--transition);
      color: black;
      font-weight: 500;
      position: relative;
      z-index: 2;
    }

    .nav-links a:hover, .nav-links a.active {
      color: var(--accent);
      transform: translateX(5px);
    }

    /* Enhanced Active Indicator */
    .nav-links li::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      width: 0;
      height: 100%;
      background: linear-gradient(135deg, var(--accent), var(--accent-light));
      border-radius: 10px;
      transition: var(--transition);
      z-index: 1;
    }

    .nav-links li.active::before {
      width: 100%;
    }

    .nav-links a.active {
      color: white;
      transform: translateX(5px);
    }

    .nav-links i {
      width: 20px;
      text-align: center;
    }

    /* Guide Content */
    .guide-content {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
      gap: 30px;
      position: relative;
      min-height: 400px;
    }

    .guide-card {
      background: white;
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      overflow: hidden;
      transition: var(--transition);
      height: 100%;
      display: flex;
      flex-direction: column;
      position: relative;
    }

    .guide-card:hover {
      transform: translateY(-10px);
      box-shadow: var(--shadow-lg);
    }

    /* Enhanced Active Card Indicator */
    .guide-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 5px;
      background: linear-gradient(90deg, var(--accent), var(--secondary));
      transform: scaleX(0);
      transform-origin: left;
      transition: transform 0.5s ease;
      z-index: 3;
    }

    .guide-card.active::before {
      transform: scaleX(1);
    }

    .guide-card.active {
      box-shadow: 0 15px 40px rgba(99, 91, 255, 0.2);
      transform: translateY(-5px);
    }

    .card-header {
      padding: 25px 25px 0;
    }

    .card-icon {
      width: 70px;
      height: 70px;
      border-radius: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 28px;
      margin-bottom: 20px;
    }

    .card-title {
      font-size: 1.5rem;
      margin-bottom: 10px;
      color: var(--primary);
    }

    .card-desc {
      color: var(--gray);
      margin-bottom: 20px;
      line-height: 1.6;
    }

    .card-content {
      padding: 0 25px 25px;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
    }

    .card-links {
      margin-top: auto;
      display: flex;
      gap: 12px;
    }

    .card-link {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: var(--light);
      padding: 10px 16px;
      border-radius: 10px;
      font-weight: 500;
      font-size: 0.9rem;
      transition: var(--transition);
      flex: 1;
      justify-content: center;
    }

    .card-link:hover {
      background: var(--accent);
      color: white;
      transform: translateY(-3px);
    }

    .dropdown-items {
      margin-top: 20px;
    }

    .dropdown-item {
      border-top: 1px solid rgba(0, 0, 0, 0.05);
      padding: 15px 0;
    }

    .dropdown-item:first-child {
      border-top: none;
      padding-top: 0;
    }

    .dropdown-heading {
      display: flex;
      justify-content: space-between;
      align-items: center;
      cursor: pointer;
      padding: 10px 0;
    }

    .dropdown-heading h4 {
      font-size: 1.1rem;
      display: flex;
      align-items: center;
      gap: 10px;
      color: var(--primary);
    }

    .dropdown-heading i {
      transition: var(--transition);
      color: var(--gray);
    }

    .dropdown-heading.active i {
      transform: rotate(180deg);
      color: var(--accent);
    }

    .dropdown-content {
      padding: 0;
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.4s ease;
    }

    .dropdown-content.active {
      padding: 15px 0 0;
      max-height: 500px;
    }

    .nested-item {
      margin-left: 15px;
      border-left: 2px solid var(--light);
      padding-left: 15px;
    }

    /* Category Colors */
    .card-eod {
      border-top: 5px solid var(--accent);
    }

    .card-eod .card-icon {
      background: linear-gradient(135deg, rgba(99, 91, 255, 0.1), rgba(138, 133, 255, 0.2));
      color: var(--accent);
    }

    .card-end-users {
      border-top: 5px solid var(--success);
    }

    .card-end-users .card-icon {
      background: linear-gradient(135deg, rgba(0, 201, 167, 0.1), rgba(0, 212, 170, 0.2));
      color: var(--success);
    }

    .card-professionals {
      border-top: 5px solid var(--warning);
    }

    .card-professionals .card-icon {
      background: linear-gradient(135deg, rgba(255, 184, 0, 0.1), rgba(255, 203, 60, 0.2));
      color: var(--warning);
    }

    .card-developers {
      border-top: 5px solid var(--info);
    }

    .card-developers .card-icon {
      background: linear-gradient(135deg, rgba(20, 184, 255, 0.1), rgba(95, 208, 255, 0.2));
      color: var(--info);
    }

    /* Floating Elements */
    .floating-shape {
      position: absolute;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--accent), var(--secondary));
      opacity: 0.05;
      z-index: -1;
    }

    .shape-1 {
      width: 300px;
      height: 300px;
      top: 10%;
      right: 5%;
    }

    .shape-2 {
      width: 200px;
      height: 200px;
      bottom: 20%;
      left: 5%;
    }

    /* Repositioning Animation */
    .guide-card.repositioning {
      transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
      z-index: 10;
    }

    /* No Results Message */
    .no-results {
      grid-column: 1 / -1;
      text-align: center;
      padding: 60px 30px;
      background: white;
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      display: none;
    }

    .no-results.active {
      display: block;
    }

    .no-results-icon {
      font-size: 4rem;
      color: var(--gray);
      margin-bottom: 20px;
      opacity: 0.5;
    }

    .no-results h3 {
      font-size: 1.8rem;
      color: var(--primary);
      margin-bottom: 15px;
    }

    .no-results p {
      color: var(--gray);
      font-size: 1.1rem;
      max-width: 500px;
      margin: 0 auto;
    }

    /* Responsive Design */
    @media (max-width: 1100px) {
      .guide-layout {
        grid-template-columns: 1fr;
      }
      
      .sidebar-nav {
        position: static;
        order: 2;
      }
      
      .nav-card {
        margin-bottom: 0;
      }
    }

    @media (max-width: 768px) {
      .header-top {
        flex-direction: column;
        gap: 20px;
        text-align: center;
      }
      
      .page-title h1 {
        font-size: 2.5rem;
      }
      
      .guide-content {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 576px) {
      .container {
        padding: 0 20px;
      }
      
      .page-title h1 {
        font-size: 2rem;
      }
      
      .page-title p {
        font-size: 1.1rem;
      }
      
      .card-links {
        flex-direction: column;
      }
      
      .no-results {
        padding: 40px 20px;
      }
      
      .no-results-icon {
        font-size: 3rem;
      }
      
      .no-results h3 {
        font-size: 1.5rem;
      }
    }
  </style>
</head>

<body>
  <header>
    <div class="header-bg"></div>
    <div class="container">
      <div class="header-content">
        <div class="header-top">
          <div class="logo-section">
            <img src="Logo_final.png" alt="EOD Platform">
            <span class="logo-text">EOD Platform</span>
          </div>
          
          <a href="index.html" class="back-button">
            <i class="fas fa-arrow-left"></i> Back to Homepage
          </a>
        </div>
        
        <div class="page-title">
          <h1>Interactive Guide Center</h1>
          <p>Explore comprehensive documentation and resources for all platform users</p>
          
          <div class="search-box">
            <input type="text" placeholder="Search guides, tutorials, and resources...">
            <i class="fas fa-search"></i>
          </div>
        </div>
      </div>
    </div>
  </header>

  <main>
    <div class="floating-shape shape-1"></div>
    <div class="floating-shape shape-2"></div>
    
    <div class="container">
      <div class="guide-layout">
        <div class="sidebar-nav">
          <div class="nav-card">
            <h3 class="nav-title"><i class="fas fa-compass"></i> Quick Navigation</h3>
            <ul class="nav-links">
              <li class="active"><a href="#eod-usage"><i class="fas fa-book-open"></i> Platform Usage</a></li>
              <li><a href="#end-users"><i class="fas fa-users"></i> End Users</a></li>
              <li><a href="#professionals"><i class="fas fa-user-tie"></i> Professionals</a></li>
              <li><a href="#building-developers"><i class="fas fa-hard-hat"></i> Developers</a></li>
              <li><a href="#"><i class="fas fa-question-circle"></i> FAQs</a></li>
            </ul>
          </div>
        </div>
        
        <div class="guide-content" id="guide-content">
          <!-- No Results Message -->
          <div class="no-results" id="no-results">
            <div class="no-results-icon">
              <i class="fas fa-search"></i>
            </div>
            <h3>No Results Found</h3>
            <p>We couldn't find any guides matching your search. Try different keywords or browse the categories below.</p>
          </div>
          
          <!-- EOD Platform Usage Card -->
          <div class="guide-card card-eod active" id="eod-usage">
            <div class="card-header">
              <div class="card-icon">
                <i class="fas fa-book-open"></i>
              </div>
              <h3 class="card-title">EOD Platform Usage</h3>
              <p class="card-desc">Complete guide to using the EOD platform for all user types</p>
            </div>
            <div class="card-content">
              <div class="card-links">
                <a href="pdfs/EOD Platform Usage.pdf" class="card-link" download>
                  <i class="fas fa-download"></i> Download
                </a>
                <a href="pdfs/EOD Platform Usage.pdf" class="card-link" target="_blank>
                  <i class="fas fa-eye"></i> View Online
                </a>
              </div>
            </div>
          </div>
          
          <!-- End Users Card -->
          <div class="guide-card card-end-users" id="end-users">
            <div class="card-header">
              <div class="card-icon">
                <i class="fas fa-users"></i>
              </div>
              <h3 class="card-title">End Users</h3>
              <p class="card-desc">Resources for building occupants and end-users</p>
            </div>
            <div class="card-content">
              <div class="dropdown-items">
                <div class="dropdown-item">
                  <div class="dropdown-heading" onclick="toggleDropdown(this)">
                    <h4><i class="fas fa-user-plus"></i> Registration & Login</h4>
                    <i class="fas fa-chevron-down"></i>
                  </div>
                  <div class="dropdown-content">
                    <div class="card-links">
                      <a href="pdfs/EOD_End-user_Registration_Guide.pdf" class="card-link" download>
                        <i class="fas fa-download"></i> Download
                      </a>
                      <a href="pdfs/EOD_End-user_Registration_Guide.pdf" class="card-link" target="_blank">
                        <i class="fas fa-eye"></i> View
                      </a>
                    </div>
                  </div>
                </div>
                
                <div class="dropdown-item">
                  <div class="dropdown-heading" onclick="toggleDropdown(this)">
                    <h4><i class="fas fa-comments"></i> Feedback Systems</h4>
                    <i class="fas fa-chevron-down"></i>
                  </div>
                  <div class="dropdown-content">
                    <div class="nested-item">
                      <div class="dropdown-heading" onclick="toggleDropdown(this)">
                        <h4><i class="fas fa-comment-alt"></i> General Feedback</h4>
                        <i class="fas fa-chevron-down"></i>
                      </div>
                      <div class="dropdown-content">
                        <div class="card-links">
                          <a href="pdfs/EOD_Feedback_Guide_General.pdf" class="card-link" download>
                            <i class="fas fa-download"></i> Download
                          </a>
                          <a href="pdfs/EOD_Feedback_Guide_General.pdf" class="card-link" target="_blank">
                            <i class="fas fa-eye"></i> View
                          </a>
                        </div>
                      </div>
                    </div>
                    
                    <div class="nested-item">
                      <div class="dropdown-heading" onclick="toggleDropdown(this)">
                        <h4><i class="fas fa-comment-dots"></i> Specific Feedback</h4>
                        <i class="fas fa-chevron-down"></i>
                      </div>
                      <div class="dropdown-content">
                        <div class="nested-item">
                          <div class="dropdown-heading" onclick="toggleDropdown(this)">
                            <h4><i class="fas fa-user-check"></i> Registered Users</h4>
                            <i class="fas fa-chevron-down"></i>
                          </div>
                          <div class="dropdown-content">
                            <div class="card-links">
                              <a href="pdfs/EOD_Registered_User_Feedback_Guide.pdf" class="card-link" download>
                                <i class="fas fa-download"></i> Download
                              </a>
                              <a href="pdfs/EOD_Registered_User_Feedback_Guide.pdf" class="card-link" target="_blank">
                                <i class="fas fa-eye"></i> View
                              </a>
                            </div>
                          </div>
                        </div>
                        
                        <div class="nested-item">
                          <div class="dropdown-heading" onclick="toggleDropdown(this)">
                            <h4><i class="fas fa-user-clock"></i> Non-registered Users</h4>
                            <i class="fas fa-chevron-down"></i>
                          </div>
                          <div class="dropdown-content">
                            <div class="card-links">
                              <a href="pdfs/EOD_Non_Registered_User_Feedback_Guide.pdf" class="card-link" download>
                                <i class="fas fa-download"></i> Download
                              </a>
                              <a href="pdfs/EOD_Non_Registered_User_Feedback_Guide.pdf" class="card-link" target="_blank">
                                <i class="fas fa-eye"></i> View
                              </a>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="dropdown-item">
                  <div class="dropdown-heading" onclick="toggleDropdown(this)">
                    <h4><i class="fas fa-search"></i> Browse Developments</h4>
                    <i class="fas fa-chevron-down"></i>
                  </div>
                  <div class="dropdown-content">
                    <div class="card-links">
                      <a href="pdfs/EOD_Browsing_Developments_Guide.pdf" class="card-link" download>
                        <i class="fas fa-download"></i> Download
                      </a>
                      <a href="pdfs/EOD_Browsing_Developments_Guide.pdf" class="card-link" target="_blank">
                        <i class="fas fa-eye"></i> View
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Professionals Card -->
          <div class="guide-card card-professionals" id="professionals">
            <div class="card-header">
              <div class="card-icon">
                <i class="fas fa-user-tie"></i>
              </div>
              <h3 class="card-title">Professionals</h3>
              <p class="card-desc">Resources for architects, property managers, and other professionals</p>
            </div>
            <div class="card-content">
              <div class="dropdown-items">
                <div class="dropdown-item">
                  <div class="dropdown-heading" onclick="toggleDropdown(this)">
                    <h4><i class="fas fa-building"></i> Property Managers</h4>
                    <i class="fas fa-chevron-down"></i>
                  </div>
                  <div class="dropdown-content">
                    <div class="card-links">
                      <a href="pdfs/Property_Manager_Guide.pdf" class="card-link" download>
                        <i class="fas fa-download"></i> Download
                      </a>
                      <a href="pdfs/Property_Manager_Guide.pdf" class="card-link" target="_blank">
                        <i class="fas fa-eye"></i> View
                      </a>
                    </div>
                  </div>
                </div>
                
                <div class="dropdown-item">
                  <div class="dropdown-heading" onclick="toggleDropdown(this)">
                    <h4><i class="fas fa-drafting-compass"></i> Architects</h4>
                    <i class="fas fa-chevron-down"></i>
                  </div>
                  <div class="dropdown-content">
                    <div class="card-links">
                      <a href="pdfs/EOD_Platform_Guide_Architect.pdf" class="card-link" download>
                        <i class="fas fa-download"></i> Download
                      </a>
                      <a href="pdfs/EOD_Platform_Guide_Architect.pdf" class="card-link" target="_blank">
                        <i class="fas fa-eye"></i> View
                      </a>
                    </div>
                  </div>
                </div>
                
                <div class="dropdown-item">
                  <div class="dropdown-heading" onclick="toggleDropdown(this)">
                    <h4><i class="fas fa-users-cog"></i> Other Professionals</h4>
                    <i class="fas fa-chevron-down"></i>
                  </div>
                  <div class="dropdown-content">
                    <div class="card-links">
                      <a href="pdfs/EOD_Platform_Guide_Others.pdf" class="card-link" download>
                        <i class="fas fa-download"></i> Download
                      </a>
                      <a href="pdfs/EOD_Platform_Guide_Others.pdf" class="card-link" target="_blank>
                        <i class="fas fa-eye"></i> View
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Building Developers Card -->
          <div class="guide-card card-developers" id="building-developers">
            <div class="card-header">
              <div class="card-icon">
                <i class="fas fa-hard-hat"></i>
              </div>
              <h3 class="card-title">Building Developers</h3>
              <p class="card-desc">Complete guide for building developers using the EOD platform</p>
            </div>
            <div class="card-content">
              <div class="card-links">
                <a href="pdfs/EOD_Platform_Developer_Guide_With_Images_Integrated.pdf" class="card-link" download>
                  <i class="fas fa-download"></i> Download
                </a>
                <a href="pdfs/EOD_Platform_Developer_Guide_With_Images_Integrated.pdf" class="card-link" target="_blank">
                  <i class="fas fa-eye"></i> View Online
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script>
    function toggleDropdown(element) {
      // Toggle active class on the dropdown heading
      element.classList.toggle('active');
      
      // Find the dropdown content
      const dropdownContent = element.nextElementSibling;
      
      // Toggle active class on dropdown content
      dropdownContent.classList.toggle('active');
    }

    // Add smooth scrolling for navigation links
    document.querySelectorAll('.nav-links a').forEach(link => {
      link.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Get target section ID
        const targetId = this.getAttribute('href');
        
        if (targetId && targetId.startsWith('#')) {
          const targetSection = document.querySelector(targetId);
          
          if (targetSection) {
            // Reposition the clicked section to the first position
            repositionSectionToFirst(targetSection);
            
            // Update active states
            updateActiveStates(targetId);
            
            // Scroll to the section
            window.scrollTo({
              top: 0,
              behavior: 'smooth'
            });
          }
        }
      });
    });

    // Function to reposition section to first position
    function repositionSectionToFirst(section) {
      const guideContent = document.getElementById('guide-content');
      const allCards = Array.from(guideContent.children);
      
      // Add repositioning class for animation
      section.classList.add('repositioning');
      
      // Move the section to the first position (after the no-results message)
      guideContent.insertBefore(section, guideContent.children[1]);
      
      // Remove repositioning class after animation
      setTimeout(() => {
        section.classList.remove('repositioning');
      }, 600);
    }

    // Function to update active states
    function updateActiveStates(targetId) {
      // Remove active class from all navigation items
      document.querySelectorAll('.nav-links li').forEach(item => {
        item.classList.remove('active');
      });
      
      // Remove active class from all cards
      document.querySelectorAll('.guide-card').forEach(card => {
        card.classList.remove('active');
      });
      
      // Add active class to target navigation item
      const targetNavItem = document.querySelector(`.nav-links a[href="${targetId}"]`).parentElement;
      targetNavItem.classList.add('active');
      
      // Add active class to target card
      const targetCard = document.querySelector(targetId);
      targetCard.classList.add('active');
    }

    // Enhanced search functionality
    const searchInput = document.querySelector('.search-box input');
    const noResultsMessage = document.getElementById('no-results');
    
    searchInput.addEventListener('input', function() {
      const searchTerm = this.value.toLowerCase().trim();
      const cards = document.querySelectorAll('.guide-card');
      let hasResults = false;
      
      // Search through all cards and their content
      cards.forEach(card => {
        const cardTitle = card.querySelector('.card-title').textContent.toLowerCase();
        const cardDesc = card.querySelector('.card-desc').textContent.toLowerCase();
        
        // Also search through dropdown content
        const dropdownHeadings = card.querySelectorAll('.dropdown-heading h4');
        let hasMatchingDropdown = false;
        
        dropdownHeadings.forEach(heading => {
          if (heading.textContent.toLowerCase().includes(searchTerm)) {
            hasMatchingDropdown = true;
          }
        });
        
        if (cardTitle.includes(searchTerm) || cardDesc.includes(searchTerm) || hasMatchingDropdown) {
          card.style.display = 'flex';
          hasResults = true;
        } else {
          card.style.display = 'none';
        }
      });
      
      // Show/hide no results message
      if (searchTerm.length > 0 && !hasResults) {
        noResultsMessage.classList.add('active');
      } else {
        noResultsMessage.classList.remove('active');
      }
      
      // If search is empty, show all cards
      if (searchTerm.length === 0) {
        cards.forEach(card => {
          card.style.display = 'flex';
        });
        noResultsMessage.classList.remove('active');
      }
    });

    // Initialize first section as active
    document.addEventListener('DOMContentLoaded', function() {
      updateActiveStates('#eod-usage');
    });
  </script>
</body>
</html>