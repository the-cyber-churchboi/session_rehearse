<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Feedback Guides</title>
  <style>
    body {
      font-family: 'Arial', sans-serif;
      background-color: #f4f4f4;
      color: #333;
      margin: 0;
      padding: 0;
    }

    header {
      background-color: #4CAF50;
      color: #fff;
      text-align: left;
      padding: 1em;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    nav {
      background-color: #333;
      padding: 1em;
      text-align: center;
    }

    a.nav-link {
      color: #fff;
      text-decoration: none;
      margin: 0 1em;
      font-weight: bold;
      font-size: 16px;
    }

    a.nav-link.active {
      color: #4CAF50; /* Active link color */
    }

    a.nav-link:hover {
      color: #4CAF50;
    }

    .back-button {
      display: inline-block;
      margin-top: 1em;
      padding: 0.5em 1em;
      background-color: #333;
      color: #fff;
      text-decoration: none;
      border-radius: 4px;
      transition: background-color 0.3s;
    }

    .back-button:hover {
      background-color: #4CAF50;
    }

    .back-icon {
      margin-right: 0.5em;
      font-size: 18px;
    }

    .guide-section {
      display: none;
      padding: 2em;
      margin: 1em;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .guide-section.active {
      display: block;
    }

    .pdf-link {
      display: inline-block;
      margin-top: 1em;
      padding: 0.5em 1em;
      background-color: #4CAF50;
      color: #fff;
      text-decoration: none;
      border-radius: 4px;
      transition: background-color 0.3s;
    }

    .pdf-link:hover {
      background-color: #333;
    }

    .download-icon,
    .view-icon {
      margin-right: 0.5em;
      cursor: pointer;
    }

    img {
      height: 60px;
      width: 100px;
      margin-right: 1em;
    }
  </style>
</head>

<body>
  <header>
    <div class="first-section">
      <div class="header-item center">
        <img src="Logo_final.png" alt="Logo">
      </div>
    </div>
    <h1>Feedback Guides</h1>
  </header>

  <nav>
    <a href="#" onclick="showGuide('general')" class="nav-link">General Feedback Guide</a>
    <a href="#" onclick="showGuide('registered')" class="nav-link">Registered User Feedback Guide</a>
    <a href="#" onclick="showGuide('non_registered')" class="nav-link">Non Registered User Feedback Guide</a>
    <a href="index.html" class="back-button">
      <span class="back-icon">⬅</span> Back to Homepage
    </a>
  </nav>

  <section id="general" class="guide-section">
    <h2>General Feedback Guide</h2>
    <a href="Guide/EOD_Feedback_Guide_General.pdf" class="pdf-link" download>
      <span class="download-icon">📥 Download PDF</span>
    </a>
    <span class="view-icon pdf-link" onclick="viewOrDownloadPDF('Guide/EOD_Feedback_Guide_General.pdf')">👁️ View PDF</span>
  </section>

  <section id="registered" class="guide-section">
    <h2>Registered User Feedback Guide</h2>
    <a href="Guide/EOD_Registered_User_Feedback_Guide.pdf" class="pdf-link" download>
      <span class="download-icon">📥 Download PDF</span>
    </a>
    <span class="view-icon pdf-link" onclick="viewOrDownloadPDF('Guide/EOD_Registered_User_Feedback_Guide.pdf')">👁️ View PDF</span>
  </section>

  <section id="non_registered" class="guide-section">
    <h2>Non Registered User Feedback Guide</h2>
    <a href="Guide/EOD_Non_Registered_User_Feedback_Guide.pdf" class="pdf-link" download>
      <span class="download-icon">📥 Download PDF</span>
    </a>
    <span class="view-icon pdf-link" onclick="viewOrDownloadPDF('Guide/EOD_Non_Registered_User_Feedback_Guide.pdf')">👁️ View PDF</span>
  </section>

  <!-- ... (previous HTML code) ... -->

<script>
  function showGuide(section) {
    // Hide all guide sections
    document.querySelectorAll('.guide-section').forEach(function (el) {
      el.classList.remove('active');
    });

    // Deactivate all navigation links
    document.querySelectorAll('.nav-link').forEach(function (el) {
      el.classList.remove('active');
    });

    // Show the selected guide section
    document.getElementById(section).classList.add('active');

    // Activate the selected navigation link
    var selectedNavLink = document.querySelector(`a[onclick="showGuide('${section}')"]`);
    selectedNavLink.classList.add('active');
  }

  function viewOrDownloadPDF(pdfPath) {
    window.open(pdfPath, '_blank');
  }

  // Add this script to show the "general" section by default on page load
  document.addEventListener('DOMContentLoaded', function () {
    showGuide('general');
  });
</script>

</body>

</html>
