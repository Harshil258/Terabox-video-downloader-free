<?php
// Initialize variables
$result = null;
$error = null;

// Process form submission
if (isset($_POST['terabox_url']) && !empty($_POST['terabox_url'])) {
    $terabox_url = $_POST['terabox_url'];
    
    // Prepare the API request
    $encoded_url = urlencode($terabox_url);
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://terabox-download-pro-api.p.rapidapi.com/teraapi/getinfo.php?url=$encoded_url",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "X-RapidAPI-Host: terabox-download-pro-api.p.rapidapi.com",
            "X-RapidAPI-Key: 164e6735f7msh33ffc4f2116a6c7p1b218ajsn18c9dc7ace34"
        ],
    ]);
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    
    curl_close($curl);
    
    if ($err) {
        $error = "API Error: " . $err;
    } else {
        $result = json_decode($response, true);
        
        if (isset($result['status']) && $result['status'] === 'error') {
            $error = $result['message'] ?? 'Unknown error occurred';
            $result = null;
        }
    }
}

// Helper function to format file size
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TeraBox Downloader Pro - Download Files from TeraBox</title>
    <meta name="description" content="Free TeraBox downloader tool to download files and videos from TeraBox with high speed. Get direct download links instantly.">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --success-color: #4cc9f0;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f2f5;
            color: var(--dark-color);
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 4rem 0;
            border-radius: 0 0 20px 20px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .hero-title {
            font-weight: 800;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .hero-subtitle {
            font-weight: 400;
            opacity: 0.9;
            margin-bottom: 2rem;
        }
        
        .search-box {
            background-color: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .form-control {
            border-radius: 10px;
            padding: 12px 20px;
            font-size: 1rem;
            border: 2px solid #e9ecef;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }
        
        .result-box {
            background-color: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            transition: all 0.3s;
        }
        
        .result-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        
        .file-info {
            margin-top: 1.5rem;
        }
        
        .file-name {
            font-weight: 700;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            word-break: break-word;
        }
        
        .file-meta {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }
        
        .thumbnail-container {
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .thumbnail-img {
            width: 100%;
            height: auto;
            transition: transform 0.3s;
        }
        
        .thumbnail-img:hover {
            transform: scale(1.03);
        }
        
        .download-btn {
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: 600;
            margin-right: 10px;
            margin-bottom: 10px;
            transition: all 0.3s;
        }
        
        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }
        
        .btn-download {
            background-color: var(--success-color);
            border-color: var(--success-color);
            color: white;
        }
        
        .btn-download:hover {
            background-color: #3db8e0;
            border-color: #3db8e0;
            color: white;
        }
        
        .btn-stream {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            color: white;
        }
        
        .btn-stream:hover {
            background-color: #3a7bc8;
            border-color: #3a7bc8;
            color: white;
        }
        
        .features-section {
            padding: 4rem 0;
        }
        
        .feature-card {
            background-color: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            height: 100%;
            transition: all 0.3s;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        
        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }
        
        .feature-title {
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .feature-text {
            color: #6c757d;
        }
        
        .how-it-works {
            padding: 4rem 0;
            background-color: #f8f9fa;
            border-radius: 20px;
            margin: 2rem 0;
        }
        
        .step-card {
            background-color: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            height: 100%;
            position: relative;
            transition: all 0.3s;
        }
        
        .step-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        
        .step-number {
            position: absolute;
            top: -15px;
            left: -15px;
            width: 50px;
            height: 50px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.5rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }
        
        .step-title {
            font-weight: 700;
            margin-top: 1rem;
            margin-bottom: 1rem;
        }
        
        .step-text {
            color: #6c757d;
        }
        
        .faq-section {
            padding: 4rem 0;
        }
        
        .accordion-item {
            border: none;
            margin-bottom: 1rem;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        
        .accordion-button {
            padding: 1.5rem;
            font-weight: 600;
            background-color: white;
        }
        
        .accordion-button:not(.collapsed) {
            background-color: var(--primary-color);
            color: white;
        }
        
        .accordion-body {
            padding: 1.5rem;
            background-color: white;
        }
        
        .cta-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 4rem 0;
            border-radius: 20px;
            margin: 2rem 0;
            text-align: center;
        }
        
        .cta-title {
            font-weight: 800;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .cta-text {
            opacity: 0.9;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }
        
        .btn-cta {
            background-color: white;
            color: var(--primary-color);
            border-radius: 10px;
            padding: 15px 30px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s;
        }
        
        .btn-cta:hover {
            background-color: var(--light-color);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        footer {
            background-color: var(--dark-color);
            color: white;
            padding: 3rem 0;
        }
        
        .footer-title {
            font-weight: 700;
            margin-bottom: 1.5rem;
        }
        
        .footer-links {
            list-style: none;
            padding: 0;
        }
        
        .footer-links li {
            margin-bottom: 0.5rem;
        }
        
        .footer-links a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .footer-links a:hover {
            color: white;
            text-decoration: underline;
        }
        
        .social-icons {
            font-size: 1.5rem;
        }
        
        .social-icons a {
            color: rgba(255, 255, 255, 0.7);
            margin-right: 1rem;
            transition: all 0.3s;
        }
        
        .social-icons a:hover {
            color: white;
        }
        
        .copyright {
            background-color: rgba(0, 0, 0, 0.2);
            padding: 1.5rem 0;
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
        }
        
        /* JSON Response Box Styles */
        .json-response-box {
            margin-top: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            background-color: #f8f9fa;
        }
        
        .json-response-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            background-color: #e9ecef;
            border-bottom: 1px solid #ddd;
            font-weight: 500;
            color: #495057;
        }
        
        .json-response-content {
            margin: 0;
            padding: 15px;
            max-height: 400px;
            overflow-y: auto;
            background-color: #f8f9fa;
            color: #212529;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            white-space: pre-wrap;
            word-break: break-word;
        }

        /* Loading Dialog Styles */
        .loading-dialog {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        
        .loading-content {
            background-color: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            width: 90%;
        }
        
        .spinner {
            border: 5px solid #f3f3f3;
            border-top: 5px solid var(--primary-color);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Improved Result Box Styles */
        .result-box {
            background-color: white;
            border-radius: 15px;
            padding: 25px;
            margin-top: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            overflow: visible;
        }
        
        .file-info {
            overflow: hidden;
        }
        
        .file-name {
            word-break: break-word;
            margin-bottom: 15px;
        }
        
        .file-meta {
            margin-bottom: 20px;
        }
        
        /* Ensure content doesn't overflow */
        .thumbnail-img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }
        
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }
        
        /* JSON Response Display */
        .json-response-box {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            margin-bottom: 30px;
            border: 1px solid #dee2e6;
            overflow: auto;
            max-height: 400px;
        }
        
        .json-response-title {
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .json-response-content {
            font-family: monospace;
            white-space: pre-wrap;
            word-break: break-all;
            font-size: 0.9rem;
            background-color: #212529;
            color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2rem;
            }
            
            .cta-title {
                font-size: 2rem;
            }
            
            .download-btn {
                width: 100%;
                margin-right: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Loading Dialog -->
    <div class="loading-dialog" id="loadingDialog">
        <div class="loading-content">
            <div class="spinner"></div>
            <h4>Processing...</h4>
            <p>Please wait while we fetch your TeraBox file information.</p>
        </div>
    </div>
    
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">TeraBox Downloader Pro</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#how-it-works">How It Works</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#faq">FAQ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://rapidapi.com/harshilvekariya12345/api/terabox-download-pro-api" target="_blank">API</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="hero-title">Download Files from TeraBox</h1>
            <p class="hero-subtitle">Get direct download links for TeraBox files instantly. Fast, secure, and free!</p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container">
        <!-- Search Box -->
        <div class="search-box">
            <form method="post" action="" id="teraboxForm">
                <div class="mb-3">
                    <label for="terabox_url" class="form-label">Enter TeraBox URL</label>
                    <input type="text" class="form-control" id="terabox_url" name="terabox_url" placeholder="https://terabox.com/s/1xxxxxxx" required value="<?php echo isset($_POST['terabox_url']) ? htmlspecialchars($_POST['terabox_url']) : ''; ?>">
                    <div class="form-text">Works with any TeraBox link format (sharing link, Mdisk link, video ID, or full video URL)</div>
                </div>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-search me-2"></i>Get Download Link
                </button>
            </form>
        </div>

        <!-- Results -->
        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($result && isset($result['status']) && $result['status'] === 'success'): ?>
            <div class="result-box">
                <div class="row">
                    <div class="col-md-4">
                        <?php if (isset($result['fileInfo']['thumbnails']['url3'])): ?>
                            <div class="thumbnail-container">
                                <img src="<?php echo htmlspecialchars($result['fileInfo']['thumbnails']['url3']); ?>" alt="File Thumbnail" class="thumbnail-img">
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-8">
                        <div class="file-info">
                            <h3 class="file-name"><?php echo htmlspecialchars($result['fileInfo']['fileName']); ?></h3>
                            <div class="file-meta">
                                <p><strong>File Size:</strong> <?php echo formatFileSize($result['fileInfo']['fileSize']); ?></p>
                                <p><strong>File Type:</strong> <?php echo strtoupper(htmlspecialchars($result['fileInfo']['fileType'])); ?></p>
                            </div>
                            <div class="download-options">
                                <?php if (isset($result['streamingLinks']['direct'])): ?>
                                    <a href="<?php echo htmlspecialchars($result['streamingLinks']['direct']); ?>" class="btn download-btn btn-download" target="_blank">
                                        <i class="fas fa-download me-2"></i>Download File
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (isset($result['streamingLinks']['hls'])): ?>
                                    <a href="<?php echo htmlspecialchars($result['streamingLinks']['hls']); ?>" class="btn download-btn btn-stream" target="_blank">
                                        <i class="fas fa-play-circle me-2"></i>Stream Online
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($result && isset($result['status']) && $result['status'] === 'success'): ?>
            <!-- JSON Response Display -->
            <div class="json-response-box">
                <div class="json-response-title">
                    <span><i class="fas fa-code me-2"></i>API Response from https://terabox-download-pro-api.p.rapidapi.com/</span>
                    <button class="btn btn-sm btn-outline-primary" onclick="copyJsonToClipboard()" id="copyBtn">
                        <i class="fas fa-copy me-1"></i>Copy
                    </button>
                </div>
                <pre class="json-response-content" id="jsonResponse"><?php echo htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT)); ?></pre>
            </div>
        <?php endif; ?>

        <!-- Features Section -->
        <section class="features-section" id="features">
            <h2 class="text-center mb-5">Key Features</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h3 class="feature-title">Fast Downloads</h3>
                        <p class="feature-text">Get high-speed direct download links for any TeraBox file instantly.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-play-circle"></i>
                        </div>
                        <h3 class="feature-title">Online Streaming</h3>
                        <p class="feature-text">Stream videos directly in your browser without downloading the entire file.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3 class="feature-title">Secure & Private</h3>
                        <p class="feature-text">We don't store your files or personal information. Your privacy is guaranteed.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h3 class="feature-title">All File Types</h3>
                        <p class="feature-text">Download any file type from TeraBox including videos, documents, images, and more.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h3 class="feature-title">Mobile Friendly</h3>
                        <p class="feature-text">Our downloader works perfectly on all devices including smartphones and tablets.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-code"></i>
                        </div>
                        <h3 class="feature-title">API Available</h3>
                        <p class="feature-text">Integrate TeraBox downloading capabilities into your own applications with our API.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works -->
        <section class="how-it-works" id="how-it-works">
            <div class="container">
                <h2 class="text-center mb-5">How It Works</h2>
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="step-card">
                            <div class="step-number">1</div>
                            <div class="text-center mb-4">
                                <i class="fas fa-link fa-3x text-primary"></i>
                            </div>
                            <h3 class="step-title text-center">Paste TeraBox Link</h3>
                            <p class="step-text">Copy the TeraBox sharing link and paste it into the search box above.</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="step-card">
                            <div class="step-number">2</div>
                            <div class="text-center mb-4">
                                <i class="fas fa-search fa-3x text-primary"></i>
                            </div>
                            <h3 class="step-title text-center">Process Link</h3>
                            <p class="step-text">Click the "Get Download Link" button and wait a few seconds for processing.</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="step-card">
                            <div class="step-number">3</div>
                            <div class="text-center mb-4">
                                <i class="fas fa-download fa-3x text-primary"></i>
                            </div>
                            <h3 class="step-title text-center">Download or Stream</h3>
                            <p class="step-text">Choose to download the file directly or stream it online in your browser.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="faq-section" id="faq">
            <h2 class="text-center mb-5">Frequently Asked Questions</h2>
            <div class="accordion" id="faqAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            Is this service completely free?
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Yes, our TeraBox downloader is completely free for personal use. There are no hidden charges or subscription fees.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingTwo">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            What types of TeraBox links are supported?
                        </button>
                    </h2>
                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Our downloader supports all types of TeraBox links including sharing links, Mdisk links, video IDs, and full video URLs. Just paste any TeraBox-related link and our system will automatically process it.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingThree">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            Is there a file size limit for downloads?
                        </button>
                    </h2>
                    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            There is no file size limit imposed by our service. You can download files of any size from TeraBox. However, larger files may take longer to download depending on your internet connection speed.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingFour">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                            Can I use this service on mobile devices?
                        </button>
                    </h2>
                    <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Yes, our TeraBox downloader is fully responsive and works on all devices including smartphones and tablets. You can download or stream TeraBox files on any device with a web browser.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingFive">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                            Do you offer an API for developers?
                        </button>
                    </h2>
                    <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Yes, we offer a powerful API for developers who want to integrate TeraBox downloading capabilities into their own applications. Visit <a href="https://rapidapi.com/harshilvekariya12345/api/terabox-download-pro-api" target="_blank">RapidAPI</a> to get started with our API.
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="container text-center">
                <h2 class="cta-title">Ready to Integrate This in Your App?</h2>
                <p class="cta-text">Get access to our TeraBox Download Pro API and add powerful file downloading capabilities to your applications.</p>
                <a href="https://rapidapi.com/harshilvekariya12345/api/terabox-download-pro-api" class="btn btn-cta" target="_blank">Get API Access</a>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="footer-title">TeraBox Downloader Pro</h5>
                    <p>The ultimate solution for downloading and streaming files from TeraBox. Fast, secure, and completely free.</p>
                    <div class="social-icons mt-3">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-github"></i></a>
                    </div>
                </div>
                <div class="col-md-2 mb-4">
                    <h5 class="footer-title">Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="#">Home</a></li>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#how-it-works">How It Works</a></li>
                        <li><a href="#faq">FAQ</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4">
                    <h5 class="footer-title">Resources</h5>
                    <ul class="footer-links">
                        <li><a href="https://rapidapi.com/harshilvekariya12345/api/terabox-download-pro-api" target="_blank">API Documentation</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4">
                    <h5 class="footer-title">Developer</h5>
                    <ul class="footer-links">
                        <li><a href="https://rapidapi.com/harshilvekariya12345/api/terabox-download-pro-api" target="_blank">RapidAPI Profile</a></li>
                        <li><a href="#">GitHub Repository</a></li>
                        <li><a href="#">Report Issues</a></li>
                        <li><a href="#">API Status</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="copyright mt-4">
            <div class="container">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> TeraBox Downloader Pro. All rights reserved. Powered by <a href="https://rapidapi.com/harshilvekariya12345/api/terabox-download-pro-api" target="_blank" class="text-white">TeraBox Download Pro API</a>.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Loading Dialog Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('teraboxForm');
            const loadingDialog = document.getElementById('loadingDialog');
            const submitBtn = document.getElementById('submitBtn');
            const originalBtnText = submitBtn.innerHTML;
            const resultBox = document.querySelector('.result-box');
            
            // If results are present, scroll to them
            if (resultBox) {
                // Slight delay to ensure page is fully loaded
                setTimeout(() => {
                    resultBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 500);
            }
            
            form.addEventListener('submit', function() {
                // Disable button and change text
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...';
                
                // Show loading dialog
                loadingDialog.style.display = 'flex';
                
                // Store the scroll position to return to after form submission
                sessionStorage.setItem('scrollPosition', window.scrollY);
                
                // If form is submitted via AJAX, you would reset the button here after response
                // For regular form submission, the page will reload so no need to reset
            });
        });
        
        // Function to copy JSON response to clipboard
        function copyJsonToClipboard() {
            const jsonText = document.getElementById('jsonResponse').textContent;
            navigator.clipboard.writeText(jsonText)
                .then(() => {
                    const copyBtn = document.getElementById('copyBtn');
                    const originalText = copyBtn.innerHTML;
                    copyBtn.innerHTML = '<i class="fas fa-check me-1"></i>Copied!';
                    copyBtn.classList.remove('btn-outline-primary');
                    copyBtn.classList.add('btn-success');
                    
                    setTimeout(() => {
                        copyBtn.innerHTML = originalText;
                        copyBtn.classList.remove('btn-success');
                        copyBtn.classList.add('btn-outline-primary');
                    }, 2000);
                })
                .catch(err => {
                    console.error('Failed to copy: ', err);
                    alert('Failed to copy to clipboard. Please try again.');
                });
        }
    </script>
</body>
</html>