<?php
$pageTitle = 'Tantiado Online E-bike Registration ';
$cssPath   = '';
$rootPath  = '';

include 'includes/header.php';
?>

<style>
/* Hero */
.hero {
  min-height: 100vh; display: flex; align-items: center;
  background: radial-gradient(ellipse 80% 60% at 60% 40%, rgba(0,230,122,0.07) 0%, transparent 70%);
  padding: 7rem 0 5rem;
}
.hero-inner {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: 4rem; align-items: center;
}
.hero-eyebrow {
  display: inline-block; font-size: 0.72rem; font-weight: 600;
  color: var(--green); letter-spacing: 3px; text-transform: uppercase;
  background: rgba(0,230,122,0.1); border: 1px solid rgba(0,230,122,0.25);
  border-radius: 100px; padding: 0.3rem 0.9rem; margin-bottom: 1.5rem;
}
.hero h1 {
  font-family: 'Bebas Neue', sans-serif;
  font-size: clamp(3rem, 6vw, 5.5rem);
  letter-spacing: 2px; line-height: 0.95;
  margin-bottom: 1.5rem;
}
.hero-sub {
  font-size: 1.05rem; line-height: 1.75;
  color: rgba(255,255,255,0.5); margin-bottom: 2.5rem; max-width: 440px;
}
.hero-actions { display: flex; gap: 1rem; flex-wrap: wrap; }
.hero-stat-row {
  display: flex; gap: 2.5rem; margin-top: 3rem; padding-top: 2.5rem;
  border-top: 1px solid rgba(255,255,255,0.08);
}
.hero-stat .num {
  font-family: 'Bebas Neue', sans-serif; font-size: 2.2rem;
  color: var(--green); letter-spacing: 1px;
}
.hero-stat .lbl { font-size: 0.78rem; color: var(--gray); text-transform: uppercase; letter-spacing: 1px; }

/* Visual card */
.hero-visual {
  display: flex; flex-direction: column; gap: 1rem;
}
.info-card {
  background: var(--dark2); border: 1px solid rgba(255,255,255,0.08);
  border-radius: 12px; padding: 1.5rem;
}
.info-card-top {
  display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;
}
.info-card-label { font-size: 0.72rem; color: var(--gray); text-transform: uppercase; letter-spacing: 1px; }
.info-card h4 { font-family: 'Bebas Neue', sans-serif; font-size: 1.3rem; letter-spacing: 1px; }
.progress-bar-wrap { background: rgba(255,255,255,0.07); border-radius: 100px; height: 6px; overflow: hidden; }
.progress-bar { height: 100%; border-radius: 100px; background: var(--green); }
.mini-stat-row { display: flex; gap: 1rem; margin-top: 1rem; }
.mini-stat { flex: 1; background: var(--dark3); border-radius: 8px; padding: 0.75rem; text-align: center; }
.mini-stat .mn { font-family: 'Bebas Neue', sans-serif; font-size: 1.4rem; }
.mini-stat .ml { font-size: 0.7rem; color: var(--gray); }

/* How it works */
.how-section { padding: 6rem 0; border-top: 1px solid rgba(255,255,255,0.06); }
.steps-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 2rem; margin-top: 3rem; }
.step-card {
  background: var(--dark2); border: 1px solid rgba(255,255,255,0.07);
  border-radius: 12px; padding: 2rem;
  transition: border-color 0.3s, transform 0.3s;
}
.step-card:hover { border-color: rgba(0,230,122,0.3); transform: translateY(-4px); }
.step-num {
  font-family: 'Bebas Neue', sans-serif; font-size: 3rem;
  color: rgba(0,230,122,0.2); line-height: 1; margin-bottom: 1rem;
}
.step-card h3 { font-family: 'Bebas Neue', sans-serif; font-size: 1.4rem; letter-spacing: 1px; margin-bottom: 0.5rem; }
.step-card p  { font-size: 0.88rem; color: rgba(255,255,255,0.5); line-height: 1.65; }

/* CTA */
.cta-section {
  text-align: center; padding: 6rem 0;
  border-top: 1px solid rgba(255,255,255,0.06);
}
.cta-section h2 { font-family: 'Bebas Neue', sans-serif; font-size: clamp(2rem,4vw,3.5rem); letter-spacing: 2px; margin-bottom: 1rem; }
.cta-section p  { color: rgba(255,255,255,0.5); margin-bottom: 2rem; max-width: 480px; margin-left: auto; margin-right: auto; }
.cta-actions { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }

@media (max-width: 900px) {
  .hero-inner  { grid-template-columns: 1fr; }
  .hero-visual { display: none; }
  .steps-grid  { grid-template-columns: 1fr; }
}
</style>

<!-- HERO -->
<section class="hero">
  <div class="container">
    <div class="hero-inner">
      <div>
        <div class="hero-eyebrow">Tantiado's Online Registration</div>
        <h1>REGISTER YOUR <span class="text-green">E-BIKE</span> TODAY</h1>
        <p class="hero-sub">
          Submit your application and documents online, then track your
          application status with the reference number you receive.
        </p>
        <div class="hero-actions">
          <a href="register.php" class="btn btn-primary" style="padding:0.85rem 2rem;font-size:1rem">Register Now →</a>
          <a href="check_status.php" class="btn btn-outline" style="padding:0.85rem 2rem;font-size:1rem">Check Status</a>
        </div>
        <div class="hero-stat-row">
          <div class="hero-stat"><div class="num">100%</div><div class="lbl">Online Submission</div></div>
          <div class="hero-stat"><div class="num">FREE</div><div class="lbl">No Fees</div></div>
          <div class="hero-stat"><div class="num">24/7</div><div class="lbl">Status Checks</div></div>
        </div>
      </div>

      <div class="hero-visual">
        <div class="info-card">
          <div class="info-card-top">
            <div>
              <div class="info-card-label">Registration</div>
              <h4>EB-2026-00001 · Approved</h4>
            </div>
            <span class="badge badge-approved">🟢 Approved</span>
          </div>
          <div class="progress-bar-wrap"><div class="progress-bar" style="width:100%"></div></div>
          <div class="mini-stat-row">
            <div class="mini-stat"><div class="mn text-green">✓</div><div class="ml">Verified</div></div>
            <div class="mini-stat"><div class="mn">2024</div><div class="ml">Year</div></div>
            <div class="mini-stat"><div class="mn text-green">Active</div><div class="ml">Status</div></div>
          </div>
        </div>
        <div class="info-card" style="background:rgba(0,230,122,0.06);border-color:rgba(0,230,122,0.2)">
          <div style="font-size:0.78rem;color:var(--gray);margin-bottom:0.5rem;text-transform:uppercase;letter-spacing:1px">Why Register?</div>
          <ul style="list-style:none;display:flex;flex-direction:column;gap:0.5rem">
            <li style="font-size:0.88rem;color:rgba(255,255,255,0.7)">✅ Prove ownership of your e-bike</li>
            <li style="font-size:0.88rem;color:rgba(255,255,255,0.7)">✅ Aid in recovery if stolen</li>
            <li style="font-size:0.88rem;color:rgba(255,255,255,0.7)">✅ Comply with local regulations</li>
            <li style="font-size:0.88rem;color:rgba(255,255,255,0.7)">✅ Submit online and track your status anytime</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="how-section" id="how">
  <div class="container">
    <div style="text-align:center;max-width:520px;margin:0 auto">
      <div class="section-label">How It Works</div>
      <h2 class="section-title">THREE SIMPLE STEPS</h2>
      <p class="section-sub" style="margin:0 auto">
        Registering your e-bike is fast and completely free.
      </p>
    </div>
    <div class="steps-grid">
      <div class="step-card">
        <div class="step-num">01</div>
        <h3>Fill the Form & Upload Documents</h3>
        <p>Enter your applicant and e-bike details, then upload a clear picture of your documents for confirmation.</p>
      </div>
      <div class="step-card">
        <div class="step-num">02</div>
        <h3>Get Your Reference</h3>
        <p>Submit and instantly receive a unique reference number (e.g. EB-2026-00001) to track your registration.</p>
      </div>
      <div class="step-card">
        <div class="step-num">03</div>
        <h3>Check Your Status</h3>
        <p>Use your reference number to check if your registration is pending, approved, or rejected, and view any admin remarks.</p>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-section">
  <div class="container">
    <div class="section-label">Get Started</div>
    <h2>READY TO REGISTER YOUR <span class="text-green">E-BIKE?</span></h2>
    <p>It only takes a few minutes. No fees — just fill out the form, upload your documents, and track your status online.</p>
    <div class="cta-actions">
      <a href="register.php" class="btn btn-primary" style="padding:0.85rem 2rem;font-size:1rem">Register Now →</a>
      <a href="check_status.php" class="btn btn-outline" style="padding:0.85rem 2rem;font-size:1rem">Check My Status</a>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
