<?php
/**
 * app/Views/home.php
 *
 * Rendered directly by HomeController::index() — this page does NOT use
 * layouts/main.php, since it's the public marketing homepage (its own top
 * nav, no sidebar) rather than a page inside the authenticated app shell.
 *
 * Expects (with safe fallbacks for local/dev preview):
 *   array $impactStats  ['meals_rescued', 'active_charities', 'co2_offset']
 */
 
$impactStats = $impactStats ?? [
  'produce_rescued'    => '550+ kg',
  'active_charities' => '45+',
  'supermarket_outlets'       => '96+',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>2nd Harvest — Saving Food, Feeding Communities</title>
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/landing.css">
</head>
<body class="landing">
 
  <nav class="public-nav">
    <a href="/" class="public-nav-logo">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 20A7 7 0 0 1 4 13v-1a7 7 0 0 1 7-7h1a7 7 0 0 1 7 7v1a7 7 0 0 1-7 7h-1z"/><path d="M12 20V10"/></svg>
      2nd Harvest
    </a>
 
    <div class="public-nav-links">
      <a href="#impact">Impact</a>
      <a href="#process">Process</a>
      <a href="#solutions">Solutions</a>
    </div>
 
    <div class="public-nav-right">
      <a href="/login" class="btn btn-primary btn-sm">Sign in</a>
      <button class="icon-btn" type="button" aria-label="Notifications">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
      </button>
    </div>
  </nav>
 
  <section class="hero">
    <div>
      <span class="pill">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 20A7 7 0 0 1 4 13v-1a7 7 0 0 1 7-7h1a7 7 0 0 1 7 7v1a7 7 0 0 1-7 7h-1z"/></svg>
        Join the Rescue Mission
      </span>
 
      <h1 class="hero-heading">Saving Food, <span class="accent">Feeding Communities.</span></h1>
 
      <p class="hero-desc">
        2nd Harvest bridges the gap between surplus food and those who need it most.
        Our intelligent platform connects supermarkets with charities to reduce waste
        and fight hunger in real-time.
      </p>
 
      <div class="flex gap-3">
        <a href="/register" class="btn btn-primary btn-lg">Start Rescuing Now</a>
        <a href="#process" class="btn btn-secondary btn-lg">How It Works</a>
      </div>
    </div>
 
    <div class="hero-image">
      <img src="/assets/images/hero-crate.jpg" alt="Crate of fresh vegetables">
    </div>
  </section>
 
  <section id="impact">
    <div class="section-heading-center">
      <h2>Measurable Social Impact</h2>
      <p>Real-time data reflecting our collective progress toward a zero-waste future.</p>
    </div>
 
    <div class="impact-grid">
      <div class="impact-card">
        <div class="impact-icon tone-1">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <div class="impact-value"><?= htmlspecialchars($impactStats['produce_rescued']) ?></div>
        <div class="impact-label">Produce Rescued</div>
        <div class="impact-desc">Diverted from landfills and provided to charities.</div>
      </div>
 
      <div class="impact-card">
        <div class="impact-icon tone-2">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div class="impact-value"><?= htmlspecialchars($impactStats['active_charities']) ?></div>
        <div class="impact-label">Active Charities</div>
        <div class="impact-desc">Local organizations using our platform daily to source food.</div>
      </div>
 
      <div class="impact-card">
        <div class="impact-icon tone-3">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 20A7 7 0 0 1 4 13v-1a7 7 0 0 1 7-7h1a7 7 0 0 1 7 7v1a7 7 0 0 1-7 7h-1z"/><path d="M12 20V10"/></svg>
        </div>
        <div class="impact-value"><?= htmlspecialchars($impactStats['supermarket_outlets']) ?></div>
        <div class="impact-label">Supermarket outlets</div>
        <div class="impact-desc">Number of supermarkets partnered up with us for the initiative.</div>
      </div>
    </div>
  </section>
 
  <section id="solutions" style="max-width: 1200px; margin: 0 auto; padding: 0 var(--space-8);">
    <h2 class="section-heading-underline">Empowering Every Partner</h2>
  </section>
 
  <div class="partner-grid">
    <div class="partner-card">
      <div class="partner-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      </div>
      <h3 class="partner-title">For Retailers</h3>
      <p class="partner-desc">
        Automate your surplus management. Reduce waste disposal costs while
        claiming tax incentives and boosting your CSR profile.
      </p>
      <ul class="partner-checklist">
        <li>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg>
          One-click surplus listing
        </li>
        <li>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg>
          Detailed impact reporting
        </li>
      </ul>
      <a href="/register/supermarket" class="btn-partner">Register as a retailer</a>
    </div>
 
    <div class="partner-card featured">
      <div class="partner-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
      </div>
      <h3 class="partner-title">For Charities</h3>
      <p class="partner-desc">
        Access a steady stream of high-quality fresh produce and shelf-stable
        goods at zero cost to support your community missions.
      </p>
      <ul class="partner-checklist">
        <li>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg>
          Real-time stock alerts
        </li>
        <li>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg>
          Priority window
        </li>
      </ul>
      <a href="/register/charity" class="btn-partner">Apply as Charity</a>
    </div>
 
    <div class="partner-card">
      <div class="partner-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
      </div>
      <h3 class="partner-title">For Customers</h3>
      <p class="partner-desc">
        Shop at steep discounts from your favorite local stores. Save money
        while directly preventing food waste.
      </p>
      <ul class="partner-checklist">
        <li>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg>
          Up to 90% off retail prices
        </li>
        <li>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg>
          Platform-based rescue tracking
        </li>
      </ul>
      <a href="/register/consumer" class="btn-partner">Register as a customer</a>
    </div>
  </div>
 
  <div class="cta-banner">
    <div class="cta-banner-inner">
      <h2>Ready to make a difference?</h2>
      <p>Join the network today. Registration is completely free!</p>
      <div class="flex gap-3">
        <a href="/register" class="btn btn-lg btn-cta-primary">Register Now</a>
        <a href="/contact" class="btn btn-lg btn-cta-secondary">Contact Us</a>
      </div>
    </div>
  </div>
 
  <footer class="public-footer">
    <div class="public-footer-inner">
      <div class="public-footer-brand">
        <h3>2nd Harvest</h3>
        <p>A mission-driven platform dedicated to solving food logistics and environmental waste through community collaboration and technology.</p>
        <div class="public-footer-social">
          <a href="#" aria-label="Facebook">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
          </a>
          <a href="#" aria-label="Twitter">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"/></svg>
          </a>
          <a href="#" aria-label="LinkedIn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>
          </a>
        </div>
      </div>
 
      <div class="public-footer-columns">
        <div class="public-footer-col">
          <h4>Platform</h4>
          <a href="/about">About Us</a>
          <a href="/case-studies">Case Studies</a>
          <a href="/contact">Contact Support</a>
        </div>
        <div class="public-footer-col">
          <h4>Legal</h4>
          <a href="/terms">Terms of Service</a>
          <a href="/privacy">Privacy Policy</a>
          <a href="/cookies">Cookie Policy</a>
        </div>
      </div>
    </div>
 
    <div class="public-footer-bottom">
      <span>&copy; <?= date('Y') ?> 2nd Harvest Food Rescue. All rights reserved.</span>
      <span>v2.1.0</span>
    </div>
  </footer>
 
</body>
</html>
 