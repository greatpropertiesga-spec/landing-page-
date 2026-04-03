<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sell Your House Fast in Georgia | Great Properties GA</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Arial, sans-serif; color: #222; }

  /* ── HERO ── */
  .hero {
    background: url('https://images.unsplash.com/photo-1600585154340-be6161a56a0c') center/cover no-repeat;
    min-height: 100vh;
    display: flex;
    align-items: center;
  }
  .hero-overlay {
    background: rgba(0,0,0,0.62);
    width: 100%;
    padding: 60px 40px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 40px;
  }
  .hero-text { flex: 1 1 380px; color: white; }
  .hero-text h1 { font-size: 48px; line-height: 1.15; margin-bottom: 16px; }
  .hero-text .sub { color: #ffd700; font-size: 24px; margin-bottom: 20px; }
  .hero-text .bullets { list-style: none; color: #ddd; font-size: 17px; }
  .hero-text .bullets li { padding: 6px 0; }
  .hero-text .bullets li::before { content: "✔ "; color: #ffd700; }

  .hero-form {
    flex: 0 1 370px;
    background: white;
    color: #222;
    padding: 30px 24px;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.35);
  }
  .hero-form h2 { font-size: 20px; margin-bottom: 18px; text-align: center; color: #b00; }
  .hero-form input {
    width: 100%;
    padding: 12px 14px;
    margin-bottom: 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 15px;
  }
  .hero-form button {
    background: #cc0000;
    color: white;
    padding: 15px;
    width: 100%;
    border: none;
    border-radius: 6px;
    font-size: 17px;
    font-weight: bold;
    cursor: pointer;
    letter-spacing: 0.5px;
    transition: background 0.2s;
  }
  .hero-form button:hover { background: #a00; }
  .hero-form .disclaimer { font-size: 11px; color: #888; text-align: center; margin-top: 10px; }

  /* ── TRUST BAR ── */
  .trust-bar {
    background: #111;
    color: white;
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0;
  }
  .trust-item {
    flex: 1 1 200px;
    text-align: center;
    padding: 22px 20px;
    border-right: 1px solid #333;
  }
  .trust-item:last-child { border-right: none; }
  .trust-item .num { font-size: 32px; font-weight: bold; color: #ffd700; }
  .trust-item .label { font-size: 13px; color: #aaa; margin-top: 4px; }

  /* ── SECTIONS SHARED ── */
  section { padding: 70px 40px; }
  section h2 { font-size: 34px; text-align: center; margin-bottom: 14px; }
  section .sub-head { text-align: center; color: #666; font-size: 17px; margin-bottom: 50px; }

  /* ── HOW IT WORKS ── */
  .how { background: #f9f9f9; }
  .steps { display: flex; flex-wrap: wrap; gap: 30px; justify-content: center; }
  .step {
    flex: 1 1 220px;
    max-width: 260px;
    text-align: center;
    padding: 30px 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 16px rgba(0,0,0,0.08);
  }
  .step .icon { font-size: 48px; margin-bottom: 16px; }
  .step .num-badge {
    display: inline-block;
    background: #cc0000;
    color: white;
    width: 36px; height: 36px;
    border-radius: 50%;
    line-height: 36px;
    font-weight: bold;
    font-size: 18px;
    margin-bottom: 12px;
  }
  .step h3 { font-size: 18px; margin-bottom: 8px; }
  .step p { color: #666; font-size: 14px; line-height: 1.6; }

  /* ── BENEFITS ── */
  .benefits { background: #cc0000; color: white; }
  .benefits h2 { color: white; }
  .benefits .sub-head { color: rgba(255,255,255,0.8); }
  .benefits-grid {
    display: flex; flex-wrap: wrap; gap: 20px; justify-content: center;
  }
  .benefit-card {
    flex: 1 1 220px; max-width: 260px;
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 10px;
    padding: 28px 20px;
    text-align: center;
  }
  .benefit-card .icon { font-size: 40px; margin-bottom: 12px; }
  .benefit-card h3 { font-size: 17px; margin-bottom: 8px; }
  .benefit-card p { font-size: 13px; color: rgba(255,255,255,0.8); line-height: 1.6; }

  /* ── TESTIMONIALS ── */
  .testimonials { background: white; }
  .testi-grid { display: flex; flex-wrap: wrap; gap: 24px; justify-content: center; }
  .testi-card {
    flex: 1 1 280px; max-width: 340px;
    background: #f9f9f9;
    border-left: 4px solid #cc0000;
    border-radius: 8px;
    padding: 24px 20px;
  }
  .testi-card .stars { color: #ffd700; font-size: 20px; margin-bottom: 10px; }
  .testi-card p { color: #444; font-size: 15px; line-height: 1.65; font-style: italic; margin-bottom: 14px; }
  .testi-card .author { font-weight: bold; color: #222; font-size: 14px; }
  .testi-card .location { color: #999; font-size: 12px; }

  /* ── FAQ ── */
  .faq { background: #f4f4f4; }
  .faq-list { max-width: 760px; margin: 0 auto; }
  .faq-item {
    background: white;
    border-radius: 8px;
    margin-bottom: 14px;
    overflow: hidden;
    box-shadow: 0 1px 8px rgba(0,0,0,0.07);
  }
  .faq-item summary {
    padding: 18px 22px;
    font-weight: bold;
    font-size: 16px;
    cursor: pointer;
    list-style: none;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .faq-item summary::after { content: "+"; font-size: 22px; color: #cc0000; }
  .faq-item[open] summary::after { content: "−"; }
  .faq-item p { padding: 0 22px 18px; color: #555; line-height: 1.7; font-size: 15px; }

  /* ── CTA BOTTOM ── */
  .cta-bottom {
    background: #111;
    color: white;
    text-align: center;
    padding: 70px 40px;
  }
  .cta-bottom h2 { font-size: 36px; margin-bottom: 14px; }
  .cta-bottom p { color: #aaa; font-size: 17px; margin-bottom: 30px; }
  .cta-bottom a {
    background: #cc0000;
    color: white;
    padding: 18px 44px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 18px;
    font-weight: bold;
    display: inline-block;
    transition: background 0.2s;
  }
  .cta-bottom a:hover { background: #a00; }

  /* ── FOOTER ── */
  footer {
    background: #000;
    color: #666;
    text-align: center;
    padding: 28px 20px;
    font-size: 13px;
  }
  footer a { color: #888; text-decoration: none; margin: 0 10px; }

  /* ── SUCCESS MODAL ── */
  .modal-bg {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.7);
    z-index: 999;
    align-items: center;
    justify-content: center;
  }
  .modal-bg.show { display: flex; }
  .modal {
    background: white;
    border-radius: 14px;
    padding: 48px 36px;
    max-width: 420px;
    text-align: center;
    box-shadow: 0 16px 60px rgba(0,0,0,0.4);
  }
  .modal .check { font-size: 64px; margin-bottom: 16px; }
  .modal h3 { font-size: 26px; margin-bottom: 10px; color: #222; }
  .modal p { color: #666; font-size: 16px; line-height: 1.6; margin-bottom: 24px; }
  .modal button {
    background: #cc0000; color: white;
    border: none; padding: 13px 36px;
    border-radius: 8px; font-size: 16px;
    font-weight: bold; cursor: pointer;
  }

  @media(max-width: 600px) {
    .hero-text h1 { font-size: 30px; }
    section { padding: 50px 20px; }
    .hero-overlay { padding: 40px 20px; }
  }
</style>
</head>
<body>

<!-- ── HERO ── -->
<div class="hero">
  <div class="hero-overlay">
    <div class="hero-text">
      <h1>Sell Your House Fast in Georgia</h1>
      <p class="sub">Get a Fair Cash Offer in 24 Hours</p>
      <ul class="bullets">
        <li>No repairs or renovations needed</li>
        <li>No real estate agent commissions</li>
        <li>Close in as little as 7 days</li>
        <li>Any condition, any situation</li>
        <li>100% free, no obligation offer</li>
      </ul>
    </div>
    <div class="hero-form">
      <h2>🏠 Get Your Cash Offer Now</h2>
      <form id="leadForm">
        <input name="address" placeholder="Property Address" required>
        <input name="name" placeholder="Full Name" required>
        <input name="phone" placeholder="Phone Number" required>
        <input name="email" placeholder="Email Address" required type="email">
        <button type="submit">GET MY CASH OFFER →</button>
      </form>
      <p class="disclaimer">No spam. No obligations. 100% confidential.</p>
    </div>
  </div>
</div>

<!-- ── TRUST BAR ── -->
<div class="trust-bar">
  <div class="trust-item">
    <div class="num">500+</div>
    <div class="label">Homes Purchased</div>
  </div>
  <div class="trust-item">
    <div class="num">24 hrs</div>
    <div class="label">Cash Offer Delivered</div>
  </div>
  <div class="trust-item">
    <div class="num">7 Days</div>
    <div class="label">Average Closing Time</div>
  </div>
  <div class="trust-item">
    <div class="num">$0</div>
    <div class="label">Fees or Commissions</div>
  </div>
</div>

<!-- ── HOW IT WORKS ── -->
<section class="how">
  <h2>How It Works</h2>
  <p class="sub-head">Selling your home has never been this simple — 3 easy steps.</p>
  <div class="steps">
    <div class="step">
      <div class="num-badge">1</div>
      <div class="icon">📋</div>
      <h3>Tell Us About Your Property</h3>
      <p>Fill out our quick form above with your property address and contact info. It only takes 60 seconds.</p>
    </div>
    <div class="step">
      <div class="num-badge">2</div>
      <div class="icon">💵</div>
      <h3>Receive Your Cash Offer</h3>
      <p>We'll review your property and present you with a fair, no-obligation cash offer within 24 hours.</p>
    </div>
    <div class="step">
      <div class="num-badge">3</div>
      <div class="icon">🔑</div>
      <h3>Close on Your Schedule</h3>
      <p>Pick your closing date. We can close in as little as 7 days or work around your timeline.</p>
    </div>
  </div>
</section>

<!-- ── BENEFITS ── -->
<section class="benefits">
  <h2>Why Sell to Great Properties GA?</h2>
  <p class="sub-head">We make selling your home stress-free and straightforward.</p>
  <div class="benefits-grid">
    <div class="benefit-card">
      <div class="icon">🔨</div>
      <h3>As-Is Purchase</h3>
      <p>We buy homes in any condition — no repairs, no cleaning, no staging required.</p>
    </div>
    <div class="benefit-card">
      <div class="icon">📝</div>
      <h3>No Listings Needed</h3>
      <p>Skip the MLS, open houses, and months of uncertainty. Sell directly to us.</p>
    </div>
    <div class="benefit-card">
      <div class="icon">⚡</div>
      <h3>Fast Closing</h3>
      <p>Traditional sales take 60–90 days. We close in 7 to 21 days on your schedule.</p>
    </div>
    <div class="benefit-card">
      <div class="icon">🤝</div>
      <h3>Zero Commissions</h3>
      <p>No agent fees. No closing costs on your side. The price we offer is what you get.</p>
    </div>
    <div class="benefit-card">
      <div class="icon">🛡️</div>
      <h3>Any Situation</h3>
      <p>Foreclosure, divorce, inherited property, relocating — we handle every situation.</p>
    </div>
    <div class="benefit-card">
      <div class="icon">📍</div>
      <h3>All of Georgia</h3>
      <p>Atlanta, Savannah, Augusta, Columbus, Macon and surrounding areas.</p>
    </div>
  </div>
</section>

<!-- ── TESTIMONIALS ── -->
<section class="testimonials">
  <h2>What Homeowners Say</h2>
  <p class="sub-head">Real stories from real Georgia homeowners.</p>
  <div class="testi-grid">
    <div class="testi-card">
      <div class="stars">★★★★★</div>
      <p>"I needed to sell fast due to a job relocation. Great Properties GA gave me an offer within a day and we closed in 10 days. Absolutely stress-free!"</p>
      <div class="author">Michael T.</div>
      <div class="location">Atlanta, GA</div>
    </div>
    <div class="testi-card">
      <div class="stars">★★★★★</div>
      <p>"My house needed major repairs and no buyer wanted it. These guys bought it as-is and gave me a fair price. No headaches, no fees. Highly recommend."</p>
      <div class="author">Sandra R.</div>
      <div class="location">Marietta, GA</div>
    </div>
    <div class="testi-card">
      <div class="stars">★★★★★</div>
      <p>"After inheriting my mother's home I didn't know what to do. They walked me through everything and made the process so easy. Closed in 2 weeks!"</p>
      <div class="author">James L.</div>
      <div class="location">Decatur, GA</div>
    </div>
  </div>
</section>

<!-- ── FAQ ── -->
<section class="faq">
  <h2>Frequently Asked Questions</h2>
  <p class="sub-head">Have questions? We have answers.</p>
  <div class="faq-list">
    <details class="faq-item">
      <summary>Is the cash offer really free with no obligation?</summary>
      <p>Yes, 100%. There is absolutely no cost to receive your offer and no pressure to accept. You decide what's right for you.</p>
    </details>
    <details class="faq-item">
      <summary>Do I need to make repairs before selling?</summary>
      <p>Not at all. We buy homes in any condition — whether it needs minor updates or major renovations. Just leave it as-is.</p>
    </details>
    <details class="faq-item">
      <summary>How quickly can you close?</summary>
      <p>We can close in as little as 7 days. However, we work around your schedule, so if you need more time we can accommodate that too.</p>
    </details>
    <details class="faq-item">
      <summary>Are there any fees or commissions?</summary>
      <p>Zero. We cover all closing costs. No agent fees, no hidden charges. The offer we make is what you receive at closing.</p>
    </details>
    <details class="faq-item">
      <summary>What types of properties do you buy?</summary>
      <p>We purchase single-family homes, condos, townhouses, duplexes, and multi-family properties anywhere in Georgia.</p>
    </details>
    <details class="faq-item">
      <summary>What if I'm behind on mortgage payments or facing foreclosure?</summary>
      <p>We specialize in helping homeowners in difficult situations. Contact us as soon as possible — the sooner you reach out, the more options we have to help.</p>
    </details>
  </div>
</section>

<!-- ── CTA BOTTOM ── -->
<div class="cta-bottom">
  <h2>Ready to Get Your Cash Offer?</h2>
  <p>Join hundreds of Georgia homeowners who sold fast, easy, and stress-free.</p>
  <a href="#top" onclick="window.scrollTo({top:0,behavior:'smooth'});return false;">GET MY FREE CASH OFFER →</a>
</div>

<!-- ── FOOTER ── -->
<footer>
  <p>© 2026 Great Properties GA · Atlanta, Georgia</p>
  <p style="margin-top:8px;">
    <a href="mailto:info@greatpropertiesga.com">info@greatpropertiesga.com</a>
    <a href="tel:+14045901613">(404) 590-1613</a>
  </p>
</footer>

<!-- ── SUCCESS MODAL ── -->
<div class="modal-bg" id="successModal">
  <div class="modal">
    <div class="check">✅</div>
    <h3>We Got Your Request!</h3>
    <p>Thank you! A member of our team will contact you within 24 hours with your cash offer.</p>
    <button onclick="document.getElementById('successModal').classList.remove('show')">Close</button>
  </div>
</div>

<script>
document.getElementById('leadForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  const btn = this.querySelector('button');
  btn.textContent = 'Sending...';
  btn.disabled = true;
  try {
    let data = new FormData(this);
    await fetch('save_lead.php', { method: 'POST', body: data });
    this.reset();
    document.getElementById('successModal').classList.add('show');
  } catch(err) {
    alert('Something went wrong. Please try again.');
  }
  btn.textContent = 'GET MY CASH OFFER →';
  btn.disabled = false;
});
</script>

</body>
</html>
