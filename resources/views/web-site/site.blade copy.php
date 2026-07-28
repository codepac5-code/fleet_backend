<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Default (Homepage) SEO, updated dynamically per page -->
  <title>FleetOS | The Shopify for Mobility - Launch a Virtual Taxi Office in the Cloud</title>
  <meta id="meta-description" name="description"
        content="Launch a branded taxi office in the cloud for $25/month + 12% commission. FleetOS is a multi-tenant marketplace where riders choose local taxi brands by rating and price range." />

  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
:root {
  --primary: #F29C0B;
  --secondary: #312873;
  --text: #1A1A1A;
  --bg: #FFFFFF;
  --radius: 16px;
}

body {
  font-family: 'Plus Jakarta Sans', sans-serif;
  color: var(--text);
  background: #F8F9FC;
  scroll-behavior: smooth;
  -webkit-font-smoothing: antialiased;
}

h1 { font-size: 2.2rem; font-weight: 900; letter-spacing: -1px; }
h2 { font-size: 1.8rem; font-weight: 800; }
h3 { font-size: 1.25rem; font-weight: 800; }
p  { font-size: 0.95rem; line-height: 1.7; color: #555; }

.btn-primary {
  background: linear-gradient(135deg, var(--primary), #ffb43b);
  color: white;
  padding: 0.9rem 1.5rem;
  border-radius: var(--radius);
  font-weight: 800;
  font-size: 0.9rem;
  border: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: .5rem;
  transition: all .25s ease;
  box-shadow: 0 8px 20px rgba(242,156,11,.25);
}

.btn-primary:hover {
  transform: translateY(-3px);
  box-shadow: 0 15px 30px rgba(242,156,11,.35);
}

.btn-secondary {
  border: 2px solid var(--secondary);
  color: var(--secondary);
  padding: 0.85rem 1.4rem;
  border-radius: var(--radius);
  font-weight: 800;
  font-size: 0.9rem;
  background: transparent;
  transition: all .25s ease;
}

.btn-secondary:hover {
  background: var(--secondary);
  color: white;
  box-shadow: 0 10px 25px rgba(49,40,115,.25);
}

.glass-card {
  background: white;
  border-radius: 24px;
  padding: 2rem;
  border: 1px solid rgba(0,0,0,0.04);
  box-shadow: 0 20px 45px rgba(0,0,0,0.06);
  transition: transform .3s ease, box-shadow .3s ease;
}

.glass-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 25px 55px rgba(0,0,0,0.08);
}

.page-content {
  display: none;
  animation: fadeIn .4s ease-out forwards;
}

.page-content.active {
  display: block;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}

.nav-link {
  font-size: 0.85rem;
  font-weight: 800;
  color: var(--secondary);
  cursor: pointer;
  padding: .5rem .75rem;
  border-radius: 12px;
  transition: all .2s ease;
}

.nav-link:hover {
  background: rgba(242,156,11,.1);
  color: var(--primary);
}

.mega-menu {
  display: none;
  position: absolute;
  top: 110%;
  left: 0;
  background: white;
  width: 320px;
  padding: 1rem;
  border-radius: 20px;
  box-shadow: 0 25px 60px rgba(0,0,0,0.1);
  border: 1px solid #f1f1f1;
  z-index: 100;
  animation: fadeIn .25s ease;
}

.group:hover .mega-menu {
  display: block;
}

.section-tag {
  font-size: 11px;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: 0.18em;
  color: var(--primary);
  margin-bottom: 1rem;
  display: inline-block;
}

input, select, textarea {
  width: 100%;
  padding: 1rem 1.1rem;
  border: 1px solid #E6E8EC;
  border-radius: var(--radius);
  background: #F9FAFB;
  font-weight: 600;
  font-size: 0.9rem;
  outline: none;
  transition: all .2s ease;
}

input:hover, select:hover, textarea:hover {
  background: #fff;
}

input:focus, select:focus, textarea:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 4px rgba(242,156,11,.18);
  background: #fff;
}

.pill {
  font-size: 10px;
  font-weight: 900;
  letter-spacing: .14em;
  text-transform: uppercase;
  border-radius: 999px;
  padding: .55rem .85rem;
  border: 1px solid rgba(0,0,0,0.08);
  background: rgba(0,0,0,0.03);
  backdrop-filter: blur(4px);
}


@keyframes spinSlow {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
.animate-spin-slow {
  animation: spinSlow 4s linear infinite;
}

@keyframes horizontalShake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-2px); }
  50% { transform: translateX(2px); }
  75% { transform: translateX(-2px); }
}
.animate-horizontal-spin {
  animation: horizontalShake 2s ease-in-out infinite;
}

</style>

</head>

<body class="bg-white">

  <!-- HEADER -->
<header class="fixed top-0 w-full z-50 bg-white/85 backdrop-blur-xl border-b border-gray-100 shadow-[0_10px_40px_rgba(0,0,0,0.05)]">
  <div class="max-w-7xl mx-auto px-8 h-24 flex items-center justify-between">

    <div class="flex items-center gap-14">

      <!-- Logo -->
<a href="#/" onclick="showPage('home')" class="flex items-center gap-2 group hover:cursor-pointer">
  <div class="w-10 h-10 bg-gradient-to-br from-[#312873] to-[#1f1a5c] rounded-full flex items-center justify-center shadow-lg relative transition-transform duration-500 group-hover:scale-110 animate-horizontal-spin">

    <svg class="w-6 h-6 text-[#F29C0B] animate-spin-slow" viewBox="0 0 64 64" fill="none">
      <circle cx="32" cy="32" r="30" stroke="currentColor" stroke-width="4" opacity="0.7"/>
      <circle cx="32" cy="32" r="12" stroke="currentColor" stroke-width="3"/>
      <line x1="32" y1="2" x2="32" y2="14" stroke="currentColor" stroke-width="2"/>
      <line x1="32" y1="50" x2="32" y2="62" stroke="currentColor" stroke-width="2"/>
      <line x1="2" y1="32" x2="14" y2="32" stroke="currentColor" stroke-width="2"/>
      <line x1="50" y1="32" x2="62" y2="32" stroke="currentColor" stroke-width="2"/>
      <line x1="10" y1="10" x2="18" y2="18" stroke="currentColor" stroke-width="2"/>
      <line x1="46" y1="46" x2="54" y2="54" stroke="currentColor" stroke-width="2"/>
      <line x1="10" y1="54" x2="18" y2="46" stroke="currentColor" stroke-width="2"/>
      <line x1="46" y1="18" x2="54" y2="10" stroke="currentColor" stroke-width="2"/>
    </svg>

    <!-- ظل داخلي لإضاءة -->
    <div class="absolute w-6 h-6 rounded-full bg-gradient-to-t from-white/20 to-transparent pointer-events-none"></div>
  </div>

  <!-- النص -->
  <span class="text-2xl font-black tracking-tight transition-all duration-500 group-hover:scale-105">
    <span class="text-[#312873] transition-colors duration-500 group-hover:text-[#F29C0B]">Fleet</span><span class="text-[#F29C0B] transition-colors duration-500 group-hover:text-[#312873]">OS</span>
  </span>
</a>



      <!-- Navigation -->
      <nav class="hidden xl:flex items-center gap-8 text-[15px] font-semibold">

        <!-- Platform -->
        <div class="relative group">
      <button class="group flex items-center gap-1 text-[#312873] hover:text-[#F29C0B] transition">
  Platform
  <svg class="w-4 h-4 opacity-70 group-hover:rotate-180 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.939l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
  </svg>
</button>


          <div class="absolute top-full left-0 mt-6 w-[560px] bg-white rounded-3xl shadow-2xl border border-gray-100 p-8 opacity-0 invisible group-hover:visible group-hover:opacity-100 transition-all duration-300">

            <div class="grid grid-cols-2 gap-6 text-sm">

              <a onclick="showPage('faas')" class="p-4 rounded-2xl hover:bg-slate-50 transition">
                <div class="font-bold text-[#312873] mb-1 hover:text-[#F29C0B]">Fleet-as-a-Service</div>
                <p class="text-gray-500 text-xs leading-relaxed">Complete fleet infrastructure powered by technology</p>
              </a>

              <a onclick="showPage('marketplace')" class="p-4 rounded-2xl hover:bg-slate-50 transition">
                <div class="font-bold text-[#312873] mb-1 hover:text-[#F29C0B]">Marketplace: How It Works</div>
                <p class="text-gray-500 text-xs leading-relaxed">Connecting supply & demand intelligently</p>
              </a>

              <a onclick="showPage('compare')" class="p-4 rounded-2xl hover:bg-slate-50 transition">
                <div class="font-bold text-[#312873] mb-1 hover:text-[#F29C0B]">Compare: FleetOS vs Others</div>
                <p class="text-gray-500 text-xs leading-relaxed">See the difference in structure & returns</p>
              </a>

              <a onclick="showPage('technology')" class="p-4 rounded-2xl hover:bg-slate-50 transition">
                <div class="font-bold text-[#312873] mb-1 hover:text-[#F29C0B]">Technology & Roadmap</div>
                <p class="text-gray-500 text-xs leading-relaxed">Architecture, scalability & roadmap</p>
              </a>

            </div>
          </div>
        </div>

        <!-- Solutions -->
        <div class="relative group">
       <button class="group flex items-center gap-1 text-[#312873] hover:text-[#F29C0B] transition">
  Solutions
  <svg class="w-4 h-4 opacity-70 group-hover:rotate-180 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.939l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
  </svg>
</button>


          <div class="absolute top-full left-0 mt-6 w-[520px] bg-white rounded-3xl shadow-2xl border border-gray-100 p-6 opacity-0 invisible group-hover:visible group-hover:opacity-100 transition-all duration-300">
            <div class="grid grid-cols-2 gap-4 text-sm">
              <a onclick="showPage('sol-fleets')" class="p-3 rounded-xl hover:bg-slate-50 font-semibold text-[#312873]">Existing Fleets</a>
              <a onclick="showPage('sol-startups')" class="p-3 rounded-xl hover:bg-slate-50 font-semibold text-[#312873]">Startups & Entrepreneurs</a>
              <a onclick="showPage('sol-corp')" class="p-3 rounded-xl hover:bg-slate-50 font-semibold text-[#312873]">Corporate Mobility</a>
              <a onclick="showPage('drivers')" class="p-3 rounded-xl hover:bg-slate-50 font-semibold text-[#312873]">Driver Experience</a>
            </div>
          </div>
        </div>

        <!-- Trust -->
        <div class="relative group">
          <button class="flex items-center gap-1 text-[#312873] hover:text-[#F29C0B] transition">
            Trust
            <svg class="w-4 h-4 opacity-70 group-hover:rotate-180 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.939l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
  </svg>
          </button>

          <div class="absolute top-full left-0 mt-6 w-[520px] bg-white rounded-3xl shadow-2xl border border-gray-100 p-6 opacity-0 invisible group-hover:visible group-hover:opacity-100 transition-all duration-300">
            <div class="grid grid-cols-2 gap-4 text-sm">
              <a onclick="showPage('safety')" class="p-3 rounded-xl hover:bg-slate-50 font-semibold text-[#312873]">Safety & Security</a>
              <a onclick="showPage('governance')" class="p-3 rounded-xl hover:bg-slate-50 font-semibold text-[#312873]">Marketplace Governance</a>
              <a onclick="showPage('conduct')" class="p-3 rounded-xl hover:bg-slate-50 font-semibold text-[#312873]">Code of Conduct</a>
              <a onclick="showPage('rollout')" class="p-3 rounded-xl hover:bg-slate-50 font-semibold text-[#312873]">Global Rollout</a>
            </div>
          </div>
        </div>

        <a onclick="showPage('pricing')" class="text-[#312873] hover:text-[#F29C0B] transition">Pricing</a>

        <!-- Resources -->
        <div class="relative group">
          <button class="flex items-center gap-1 text-[#312873] hover:text-[#F29C0B] transition">
            Resources
             <svg class="w-4 h-4 opacity-70 group-hover:rotate-180 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.939l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
  </svg>
          </button>

          <div class="absolute top-full left-0 mt-6 w-[420px] bg-white rounded-3xl shadow-2xl border border-gray-100 p-6 opacity-0 invisible group-hover:visible group-hover:opacity-100 transition-all duration-300">
            <div class="space-y-2 text-sm">
              <a onclick="showPage('academy')" class="block p-3 rounded-xl hover:bg-slate-50 font-semibold text-[#312873]">Academy</a>
              <a onclick="showPage('tech-faq')" class="block p-3 rounded-xl hover:bg-slate-50 font-semibold text-[#312873]">Technical FAQ</a>
              <a onclick="showPage('contact')" class="block p-3 rounded-xl hover:bg-slate-50 font-semibold text-[#312873]">Contact</a>
            </div>
          </div>
        </div>

      </nav>
    </div>

    <!-- Right -->
    <div class="flex items-center gap-4">
مخ
     <a href="{{ route('login.office') }}" class="hidden md:flex items-center gap-2 text-sm font-semibold text-[#312873] px-5 py-2.5 rounded-full hover:bg-slate-100 transition">
               <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>

        Sign In
    </a>


      <button onclick="showPage('office-signup')" class="btn-primary px-6 py-3 text-sm">
        Launch Office
      </button>

    </div>

  </div>
</header>


  <main class="pt-20">

    <!-- 1) HOMEPAGE -->
    <section id="page-home" class="page-content active">
      <div class="max-w-7xl mx-auto px-6 pt-20 pb-24">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
          <div>
            <span class="section-tag">Shopify for Mobility</span>
            <h1 class="text-6xl font-extrabold text-[#312873] leading-[1.1] mb-8">
              The Power of a Global Platform. <br />
              <span class="text-[#F29C0B]">The Independence of Your Local Brand.</span>
            </h1>
            <p class="text-xl text-gray-600 mb-10 leading-relaxed">
              Launch your taxi office in the cloud for <span class="font-extrabold text-[#312873]">$25/month</span>.
              Join a shared rider marketplace where your brand is visible, your pricing strategy drives demand,
              and riders choose you directly.
            </p>

            <div class="flex flex-wrap gap-4 mb-10">
              <button onclick="showPage('office-signup')" class="btn-primary px-8 py-4 text-lg">Launch Your Virtual Office</button>
              <button onclick="showPage('compare')" class="btn-secondary px-8 py-4 text-lg">Compare Our Model</button>
            </div>

            <div class="flex flex-wrap gap-3">
              <span class="pill">Multi-tenant marketplace</span>
              <span class="pill">Office dashboard</span>
              <span class="pill">Driver app</span>
              <span class="pill">Safety</span>
              <span class="pill">Governance</span>
            </div>
          </div>

          <div class="relative">
            <div class="glass-card p-10 bg-[#312873] text-white">
              <h3 class="text-2xl font-extrabold mb-6">Selection defines the marketplace.</h3>
              <p class="opacity-85 mb-8 leading-relaxed">
                FleetOS doesn’t hide local offices behind one algorithm. Riders see nearby offices — each with its own
                brand, rating, and price range — and choose you directly.
              </p>
              <div class="space-y-4">
                <div class="p-4 bg-white/10 rounded-xl flex justify-between items-center">
                  <span class="font-extrabold">Local Eco-Fleet</span>
                  <span class="text-[#F29C0B] font-black">$12.50</span>
                </div>
                <div class="p-4 bg-white/10 rounded-xl flex justify-between items-center">
                  <span class="font-extrabold">City Express</span>
                  <span class="text-[#F29C0B] font-black">$14.00</span>
                </div>
                <div class="p-4 bg-white/10 rounded-xl flex justify-between items-center">
                  <span class="font-extrabold">Premium Black</span>
                  <span class="text-[#F29C0B] font-black">$18.00</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- What FleetOS is (one breath) + pillars -->
        <div class="mt-20 grid lg:grid-cols-3 gap-8">
          <div class="glass-card p-10">
            <span class="section-tag">In one breath</span>
            <p class="text-gray-600 leading-relaxed">
              FleetOS is Fleet-as-a-Service (FaaS): a multi-tenant mobility platform where independent taxi offices run branded
              virtual operations inside one shared passenger marketplace.
            </p>
          </div>
          <div class="glass-card p-10">
            <h4 class="text-xl font-extrabold text-[#312873] mb-3">Shared Demand</h4>
            <p class="text-sm text-gray-500">Every rider in the FleetOS passenger app is a potential customer for your office.</p>
          </div>
          <div class="glass-card p-10">
            <h4 class="text-xl font-extrabold text-[#312873] mb-3">Owner Autonomy</h4>
            <p class="text-sm text-gray-500">You set pricing strategy, manage drivers, and build loyalty under your own brand.</p>
          </div>
        </div>

        <!-- How it works -->
        <div class="mt-16 bg-gray-50 rounded-[40px] p-12">
          <div class="max-w-5xl mx-auto">
            <div class="flex items-end justify-between gap-6 flex-wrap mb-10">
              <div>
                <span class="section-tag">How it works</span>
                <h3 class="text-3xl font-extrabold text-[#312873]">From application to marketplace growth</h3>
              </div>
              <div class="flex gap-3">
                <button onclick="showPage('marketplace')" class="btn-secondary px-6 py-3">Explore Marketplace</button>
                <button onclick="showPage('pricing')" class="btn-primary px-6 py-3">View Pricing</button>
              </div>
            </div>

            <div class="grid md:grid-cols-5 gap-4">
              <div class="glass-card p-6">
                <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center font-black text-[#F29C0B] mb-4">1</div>
                <p class="text-sm font-extrabold text-[#312873] mb-1">Apply & Verify</p>
                <p class="text-xs text-gray-500">Market status + office readiness checks.</p>
              </div>
              <div class="glass-card p-6">
                <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center font-black text-[#312873] mb-4">2</div>
                <p class="text-sm font-extrabold text-[#312873] mb-1">Launch Office</p>
                <p class="text-xs text-gray-500">Listing, zones, pricing ranges, policies.</p>
              </div>
              <div class="glass-card p-6">
                <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center font-black text-green-700 mb-4">3</div>
                <p class="text-sm font-extrabold text-[#312873] mb-1">Onboard Drivers</p>
                <p class="text-xs text-gray-500">Drivers join via FleetOS Driver App.</p>
              </div>
              <div class="glass-card p-6">
                <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center font-black text-purple-700 mb-4">4</div>
                <p class="text-sm font-extrabold text-[#312873] mb-1">Go Live</p>
                <p class="text-xs text-gray-500">Riders discover and choose your brand.</p>
              </div>
              <div class="glass-card p-6">
                <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center font-black text-gray-700 mb-4">5</div>
                <p class="text-sm font-extrabold text-[#312873] mb-1">Grow</p>
                <p class="text-xs text-gray-500">Improve ratings, conversion, reliability.</p>
              </div>
            </div>
          </div>
        </div>

      </div>
    </section>

    <!-- 2) FLEET-AS-A-SERVICE -->
    <section id="page-faas" class="page-content px-6 py-24 bg-gray-50">
      <div class="max-w-4xl mx-auto">
        <span class="section-tag">Category Page</span>
        <h2 class="text-5xl font-extrabold text-[#312873] mb-8">Fleet-as-a-Service: The Shopify for Mobility</h2>
        <div class="prose prose-lg text-gray-600 mb-12 max-w-none">
          <p class="text-xl leading-relaxed">
            Fleet-as-a-Service (FaaS) is a marketplace operating model where many independent taxi offices run in the cloud
            and compete inside one shared passenger marketplace while keeping full local ownership and brand identity.
          </p>

          <div class="grid md:grid-cols-2 gap-10 mt-12">
            <div class="glass-card p-8">
              <h4 class="font-extrabold text-xl text-[#312873] mb-3">The problem with the old choices</h4>
              <p class="text-sm">
                Traditional dispatch digitizes operations but doesn’t solve demand growth. Centralized ride-hailing creates demand,
                but often removes local brand identity and shifts control to the platform.
              </p>
            </div>
            <div class="glass-card p-8">
              <h4 class="font-extrabold text-xl text-[#312873] mb-3">The FleetOS model (the third option)</h4>
              <p class="text-sm">
                Shared demand plus brand ownership. Riders use one app, offices remain visible brands, and riders choose offices directly
                by rating and price range.
              </p>
            </div>
          </div>

          <div class="glass-card p-8 mt-10">
            <h4 class="font-extrabold text-xl text-[#312873] mb-3">What multi-tenant means in practice</h4>
            <ul class="list-disc pl-6 text-sm space-y-2">
              <li>Each office has its own dashboard, drivers, zones, pricing strategy, and reputation.</li>
              <li>All offices share one passenger marketplace and one trust layer (safety, governance, dispute resolution).</li>
              <li>Selection creates loyalty because riders choose brands — not anonymous supply.</li>
            </ul>
          </div>

          <div class="grid md:grid-cols-2 gap-8 mt-10">
            <div class="glass-card p-8">
              <h4 class="font-extrabold text-xl text-[#312873] mb-3">Offices own</h4>
              <p class="text-sm">Brand identity, pricing strategy, driver operations, service standards, customer experience.</p>
            </div>
            <div class="glass-card p-8">
              <h4 class="font-extrabold text-xl text-[#312873] mb-3">FleetOS provides</h4>
              <p class="text-sm">Passenger marketplace, office dashboard, driver app, safety tools, governance rules, compliance-first rollout.</p>
            </div>
          </div>
        </div>

        <div class="flex gap-4 flex-wrap">
          <button onclick="showPage('compare')" class="btn-secondary px-8 py-3">Compare Models</button>
          <button onclick="showPage('office-signup')" class="btn-primary px-8 py-3">Launch Office</button>
        </div>
      </div>
    </section>

    <!-- 3) MARKETPLACE -->
    <section id="page-marketplace" class="page-content px-6 py-24">
      <div class="max-w-6xl mx-auto">
        <span class="section-tag">Marketplace</span>
        <h2 class="text-5xl font-extrabold text-[#312873] mb-6">One App. A World of Choice.</h2>
        <p class="text-xl text-gray-500 mb-14 max-w-3xl">
          FleetOS creates a marketplace where riders compare local offices by brand, rating, and price range — and book directly into each office’s driver network.
        </p>

        <div class="grid md:grid-cols-3 gap-8 mb-16">
          <div class="glass-card p-10">
            <div class="w-12 h-12 bg-orange-100 rounded-xl mb-6 flex items-center justify-center text-[#F29C0B] font-black">1</div>
            <h4 class="font-extrabold mb-3">Open & Search</h4>
            <p class="text-sm text-gray-500">Riders enter pickup and destination in the FleetOS passenger app.</p>
          </div>
          <div class="glass-card p-10">
            <div class="w-12 h-12 bg-blue-100 rounded-xl mb-6 flex items-center justify-center text-[#312873] font-black">2</div>
            <h4 class="font-extrabold mb-3">Compare</h4>
            <p class="text-sm text-gray-500">The app lists available offices with brand, rating, and price range.</p>
          </div>
          <div class="glass-card p-10">
            <div class="w-12 h-12 bg-green-100 rounded-xl mb-6 flex items-center justify-center text-green-700 font-black">3</div>
            <h4 class="font-extrabold mb-3">Direct Selection</h4>
            <p class="text-sm text-gray-500">Riders choose the office that fits their needs and book directly.</p>
          </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-8 mb-12">
          <div class="glass-card p-10">
            <h4 class="text-2xl font-extrabold text-[#312873] mb-4">Why selection changes the economics</h4>
            <ul class="text-sm text-gray-500 space-y-2">
              <li>• Riders trust marketplaces with transparent choice.</li>
              <li>• Offices build loyalty because riders choose them intentionally.</li>
              <li>• Quality becomes a growth engine: better service → better ratings → better conversion.</li>
            </ul>
          </div>
          <div class="glass-card p-10 bg-gray-50">
            <h4 class="text-2xl font-extrabold text-[#312873] mb-4">Marketplace quality controls</h4>
            <ul class="text-sm text-gray-600 space-y-2">
              <li>• Verified office onboarding (market-dependent)</li>
              <li>• Verified-trip ratings + review integrity controls</li>
              <li>• Dispute resolution workflows</li>
              <li>• Safety tools + incident reporting pathways</li>
              <li>• Governance rules for fair competition</li>
            </ul>
          </div>
        </div>

        <div class="bg-[#312873] rounded-[40px] p-12 text-white text-center">
          <h3 class="text-2xl font-extrabold mb-3 text-[#F29C0B]">The Antidote to the “Black Box” Algorithm.</h3>
          <p class="opacity-80 mb-8 max-w-3xl mx-auto">
            We replace hidden routing with an open marketplace. High-quality service is rewarded through visibility, ratings integrity, and conversion.
          </p>
          <div class="flex gap-4 justify-center flex-wrap">
            <button onclick="showPage('office-signup')" class="btn-primary px-8 py-3">Launch Your Virtual Office</button>
            <button onclick="showPage('governance')" class="btn-secondary px-8 py-3">Explore Governance</button>
          </div>
        </div>
      </div>
    </section>

    <!-- 4) COMPARE -->
    <section id="page-compare" class="page-content px-6 py-24 bg-gray-50">
      <div class="max-w-6xl mx-auto">
        <span class="section-tag">Comparison</span>
        <h2 class="text-5xl font-extrabold text-[#312873] mb-6 text-center">Not Dispatch. Not Ride-Hailing. A New Operating Model.</h2>
        <p class="text-xl text-gray-500 mb-14 text-center max-w-4xl mx-auto">
          Most operators think they must choose between control without demand, or demand without ownership. FleetOS is the third model: shared demand plus brand ownership.
        </p>

        <div class="overflow-x-auto">
          <table class="w-full bg-white rounded-3xl overflow-hidden shadow-sm">
            <thead>
              <tr class="bg-gray-100 text-left">
                <th class="p-6 text-xs font-black uppercase tracking-widest opacity-40">Feature</th>
                <th class="p-6 text-sm font-extrabold text-[#312873]">Traditional Dispatch</th>
                <th class="p-6 text-sm font-extrabold text-[#312873]">Ride-Hailing Giants</th>
                <th class="p-6 text-sm font-black text-[#F29C0B]">FleetOS</th>
              </tr>
            </thead>
            <tbody class="text-sm">
              <tr class="border-b border-gray-50">
                <td class="p-6 font-extrabold">Ownership</td>
                <td class="p-6">You own software/hardware</td>
                <td class="p-6">They own the customer</td>
                <td class="p-6 text-[#F29C0B] font-black">You own brand & drivers</td>
              </tr>
              <tr class="border-b border-gray-50">
                <td class="p-6 font-extrabold">Cost Structure</td>
                <td class="p-6">High upfront + maintenance</td>
                <td class="p-6">25–30% commission</td>
                <td class="p-6 text-[#F29C0B] font-black">$25/mo + 12% commission</td>
              </tr>
              <tr class="border-b border-gray-50">
                <td class="p-6 font-extrabold">Marketplace</td>
                <td class="p-6">Isolated (your app only)</td>
                <td class="p-6">Shared (you are hidden)</td>
                <td class="p-6 text-[#F29C0B] font-black">Shared (you are visible)</td>
              </tr>
              <tr class="border-b border-gray-50">
                <td class="p-6 font-extrabold">Competition</td>
                <td class="p-6">Local only</td>
                <td class="p-6">Platform-controlled ranking</td>
                <td class="p-6 text-[#F29C0B] font-black">Selection + transparent rules</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="mt-12 flex justify-center gap-4 flex-wrap">
          <button onclick="showPage('pricing')" class="btn-primary px-8 py-3">View Pricing</button>
          <button onclick="showPage('office-signup')" class="btn-secondary px-8 py-3">Launch Your Virtual Office</button>
        </div>
      </div>
    </section>

    <!-- 5) SOLUTIONS: EXISTING FLEETS -->
    <section id="page-sol-fleets" class="page-content px-6 py-24">
      <div class="max-w-5xl mx-auto">
        <span class="section-tag">Solutions</span>
        <h2 class="text-5xl font-extrabold text-[#312873] mb-6">Bring Your Fleet Into the Cloud Era.</h2>
        <p class="text-xl text-gray-500 mb-12">Stop paying for legacy hardware. Start growing with modern demand.</p>

        <div class="grid md:grid-cols-2 gap-8 mb-10">
          <div class="glass-card p-10">
            <h4 class="font-extrabold text-2xl text-[#312873] mb-4">What FleetOS replaces</h4>
            <ul class="text-sm text-gray-600 space-y-2">
              <li>• Hardware-heavy dispatch infrastructure and maintenance costs</li>
              <li>• Manual coordination workflows</li>
              <li>• Limited reporting and low visibility across operations</li>
            </ul>
          </div>
          <div class="glass-card p-10 bg-gray-50">
            <h4 class="font-extrabold text-2xl text-[#312873] mb-4">What FleetOS adds</h4>
            <ul class="text-sm text-gray-700 space-y-2">
              <li>• Cloud dashboard for dispatch, drivers, zones, pricing ranges</li>
              <li>• Marketplace demand exposure inside the passenger app</li>
              <li>• Standardized safety and governance tools</li>
              <li>• Analytics to improve reliability, conversion, service quality</li>
            </ul>
          </div>
        </div>

        <div class="p-10 border rounded-[40px] bg-blue-50 border-blue-100 mb-12">
          <h4 class="font-extrabold text-blue-900 mb-3 text-2xl">The 48-Hour Migration</h4>
          <p class="text-blue-800 mb-8 opacity-90">
            Our onboarding team supports driver data migration and setup planning to minimize downtime and go live quickly.
          </p>
          <div class="flex gap-4 flex-wrap">
            <button onclick="showPage('contact', 'migration-audit')" class="btn-primary px-8 py-3">Schedule a Migration Audit</button>
            <button onclick="showPage('contact', 'demo')" class="btn-secondary px-8 py-3">Request a Demo</button>
          </div>
        </div>
      </div>
    </section>

    <!-- 6) SOLUTIONS: STARTUPS -->
    <section id="page-sol-startups" class="page-content px-6 py-24 bg-gray-50">
      <div class="max-w-5xl mx-auto">
        <span class="section-tag">Solutions</span>
        <h2 class="text-5xl font-extrabold text-[#312873] mb-6">Launch a Taxi Business for $25.</h2>
        <p class="text-xl text-gray-500 mb-12">No office space? No problem. Build a brand-led mobility business in the cloud.</p>

        <div class="grid md:grid-cols-2 gap-8 mb-12">
          <div class="glass-card p-10">
            <h4 class="font-extrabold text-2xl text-[#312873] mb-4">The entrepreneur kit</h4>
            <ul class="text-sm text-gray-600 space-y-2">
              <li>• Virtual office dashboard: dispatch, zones, pricing ranges, reporting</li>
              <li>• Driver ecosystem: recruit drivers with a dedicated Driver App workflow</li>
              <li>• Marketplace presence: approved offices appear to riders in your city</li>
              <li>• Trust layer: safety tools, governance rules, dispute workflows</li>
              <li>• Scalable model: grow without physical infrastructure</li>
            </ul>
          </div>
          <div class="glass-card p-10 bg-white">
            <h4 class="font-extrabold text-2xl text-[#312873] mb-4">Approval & requirements</h4>
            <p class="text-sm text-gray-600 leading-relaxed">
              FleetOS expands market-by-market with compliance-first onboarding. Requirements vary by jurisdiction, but typically include
              business and identity verification, driver and vehicle standards, and marketplace policy compliance.
            </p>
            <div class="mt-6 flex gap-3 flex-wrap">
              <button onclick="showPage('rollout')" class="btn-secondary px-6 py-3">See Rollout</button>
              <button onclick="showPage('office-signup')" class="btn-primary px-6 py-3">Launch Your Virtual Office</button>
            </div>
          </div>
        </div>

        <div class="glass-card p-10 bg-[#312873] text-white">
          <h4 class="text-2xl font-extrabold mb-3 text-[#F29C0B]">Start smart. Scale fast.</h4>
          <p class="opacity-85 mb-8 max-w-3xl">
            Your office profile is visible in the marketplace. Riders choose you directly. You own the brand relationship.
          </p>
          <div class="flex gap-4 flex-wrap">
            <button onclick="showPage('academy')" class="btn-secondary px-8 py-3">Explore Academy</button>
            <button onclick="showPage('office-signup')" class="btn-primary px-8 py-3">Apply Now</button>
          </div>
        </div>
      </div>
    </section>

    <!-- 7) SOLUTIONS: CORPORATE -->
    <section id="page-sol-corp" class="page-content px-6 py-24">
      <div class="max-w-5xl mx-auto">
        <span class="section-tag">Enterprise</span>
        <h2 class="text-5xl font-extrabold text-[#312873] mb-6">Corporate Mobility with Marketplace Reliability</h2>
        <p class="text-xl text-gray-500 mb-12">
          Corporate transport needs predictable service, clean billing, and reporting. FleetOS adds a corporate layer on top of a verified marketplace of local offices.
        </p>

        <div class="grid md:grid-cols-2 gap-8 mb-12">
          <div class="glass-card p-10">
            <h4 class="font-extrabold text-2xl text-[#312873] mb-4">Centralized billing</h4>
            <ul class="text-sm text-gray-600 space-y-2">
              <li>• Monthly consolidated invoicing</li>
              <li>• Allocation by department or cost center (configurable)</li>
              <li>• Policy controls (market-dependent)</li>
            </ul>
          </div>
          <div class="glass-card p-10 bg-gray-50">
            <h4 class="font-extrabold text-2xl text-[#312873] mb-4">Reporting & analytics</h4>
            <ul class="text-sm text-gray-700 space-y-2">
              <li>• Spend visibility by department, region, time period</li>
              <li>• Audit-friendly exports for finance teams</li>
              <li>• Optional sustainability reporting (distance-based estimates)</li>
            </ul>
          </div>
        </div>

        <div class="glass-card p-10">
          <h4 class="font-extrabold text-2xl text-[#312873] mb-4">Use cases</h4>
          <div class="grid md:grid-cols-2 gap-4 text-sm text-gray-600">
            <div>• Employee commute programs</div>
            <div>• Executive & VIP transport</div>
            <div>• Guest & client mobility</div>
            <div>• Airport transfers & events</div>
          </div>
        </div>

        <div class="flex gap-4 flex-wrap mt-10">
          <button onclick="showPage('contact', 'corporate')" class="btn-primary px-8 py-3">Request Corporate Brief</button>
          <button onclick="showPage('contact', 'demo')" class="btn-secondary px-8 py-3">Schedule a Demo</button>
        </div>
      </div>
    </section>

    <!-- 8) TECHNOLOGY -->
    <section id="page-technology" class="page-content px-6 py-24 bg-gray-50">
      <div class="max-w-5xl mx-auto">
        <span class="section-tag">Technology</span>
        <h2 class="text-5xl font-extrabold text-[#312873] mb-6">Enterprise-Grade Infrastructure Built for a Marketplace</h2>
        <p class="text-xl text-gray-500 mb-14 max-w-4xl">
          FleetOS is designed for scalable operations, secure data handling, and marketplace-level governance across multiple offices and regions.
        </p>

        <div class="grid md:grid-cols-2 gap-10 mb-12">
          <div class="glass-card p-10">
            <h4 class="text-xs font-black uppercase text-[#F29C0B] tracking-widest mb-6">Available now</h4>
            <ul class="space-y-3 text-sm font-extrabold text-[#312873]">
              <li class="flex items-center gap-3"><div class="w-1.5 h-1.5 bg-[#312873] rounded-full"></div> Office dashboard: dispatch, drivers, zones, pricing ranges, reporting</li>
              <li class="flex items-center gap-3"><div class="w-1.5 h-1.5 bg-[#312873] rounded-full"></div> Driver app workflow + trip routing</li>
              <li class="flex items-center gap-3"><div class="w-1.5 h-1.5 bg-[#312873] rounded-full"></div> Real-time GPS telemetry + trip status</li>
              <li class="flex items-center gap-3"><div class="w-1.5 h-1.5 bg-[#312873] rounded-full"></div> Automated billing + reporting workflows</li>
              <li class="flex items-center gap-3"><div class="w-1.5 h-1.5 bg-[#312873] rounded-full"></div> Role-based access control</li>
              <li class="flex items-center gap-3"><div class="w-1.5 h-1.5 bg-[#312873] rounded-full"></div> Audit-friendly logs for key actions</li>
            </ul>
          </div>

          <div class="glass-card p-10 bg-white">
            <h4 class="text-xs font-black uppercase text-gray-400 tracking-widest mb-6">Roadmap: FleetOS AI</h4>
            <p class="text-sm text-gray-500 mb-6">Roadmap timing depends on market readiness and compliance requirements.</p>
            <ul class="space-y-3 text-sm font-extrabold text-gray-700">
              <li class="flex items-center gap-3"><div class="w-1.5 h-1.5 bg-gray-300 rounded-full"></div> Demand insights + heatmaps</li>
              <li class="flex items-center gap-3"><div class="w-1.5 h-1.5 bg-gray-300 rounded-full"></div> Marketplace performance intelligence</li>
              <li class="flex items-center gap-3"><div class="w-1.5 h-1.5 bg-gray-300 rounded-full"></div> Routing + profit-per-mile optimization</li>
              <li class="flex items-center gap-3"><div class="w-1.5 h-1.5 bg-gray-300 rounded-full"></div> Marketplace risk + fraud detection signals</li>
            </ul>
          </div>
        </div>

        <div class="text-center">
          <button onclick="showPage('tech-faq')" class="btn-primary px-8 py-3">View Technical FAQ</button>
        </div>
      </div>
    </section>

    <!-- 9) SAFETY -->
    <section id="page-safety" class="page-content px-6 py-24">
      <div class="max-w-5xl mx-auto">
        <span class="section-tag">Trust</span>
        <h2 class="text-5xl font-extrabold text-[#312873] mb-6">Safety & Security Built for a Global Marketplace</h2>
        <p class="text-xl text-gray-500 mb-12 max-w-4xl">
          In a mobility marketplace, trust is the product. FleetOS is designed with safety tools, verification workflows, and data protection practices that support a trusted ecosystem.
        </p>

        <div class="grid md:grid-cols-3 gap-8 mb-12">
          <div class="glass-card p-10">
            <h4 class="font-extrabold text-xl text-[#312873] mb-3">Rider safety workflows</h4>
            <ul class="text-sm text-gray-600 space-y-2">
              <li>• Clear trip identity (office brand, driver, vehicle, reference)</li>
              <li>• Trip sharing workflows (market-dependent)</li>
              <li>• Incident reporting & escalation pathways</li>
            </ul>
          </div>

          <div class="glass-card p-10 bg-gray-50">
            <h4 class="font-extrabold text-xl text-[#312873] mb-3">Office & driver verification</h4>
            <ul class="text-sm text-gray-700 space-y-2">
              <li>• Office eligibility checks aligned to local requirements</li>
              <li>• Driver identity & documentation standards</li>
              <li>• Policies designed to protect trust & quality</li>
            </ul>
          </div>

          <div class="glass-card p-10">
            <h4 class="font-extrabold text-xl text-[#312873] mb-3">Data privacy & protection</h4>
            <ul class="text-sm text-gray-600 space-y-2">
              <li>• Encryption in transit and at rest</li>
              <li>• Role-based access controls</li>
              <li>• Audit trails for security-relevant actions</li>
              <li>• Market-aware data handling aligned with rollout requirements</li>
            </ul>
          </div>
        </div>

        <div class="flex gap-4 flex-wrap">
          <button onclick="showPage('governance')" class="btn-primary px-8 py-3">Explore Marketplace Governance</button>
          <button onclick="showPage('office-signup')" class="btn-secondary px-8 py-3">Launch Your Virtual Office</button>
        </div>
      </div>
    </section>

    <!-- 10) DRIVER EXPERIENCE -->
    <section id="page-drivers" class="page-content px-6 py-24 bg-gray-50">
      <div class="max-w-5xl mx-auto">
        <span class="section-tag">Drivers</span>
        <h2 class="text-5xl font-extrabold text-[#312873] mb-6">A Driver Experience Built for Professional Mobility</h2>
        <p class="text-xl text-gray-500 mb-12 max-w-4xl">
          Drivers are the engine of the marketplace. FleetOS is designed to make driving more predictable, more professional, and more transparent.
        </p>

        <div class="grid md:grid-cols-3 gap-8 mb-12">
          <div class="glass-card p-10">
            <h4 class="font-extrabold text-xl text-[#312873] mb-3">Earnings transparency</h4>
            <ul class="text-sm text-gray-600 space-y-2">
              <li>• Clear breakdown and take-home visibility</li>
              <li>• Predictable settlement logic</li>
              <li>• Market-dependent payout schedules/programs</li>
            </ul>
          </div>

          <div class="glass-card p-10 bg-white">
            <h4 class="font-extrabold text-xl text-[#312873] mb-3">Professional trip tools</h4>
            <ul class="text-sm text-gray-600 space-y-2">
              <li>• Clean trip workflow and details</li>
              <li>• Navigation and route guidance</li>
              <li>• Performance signals that help improve reliability</li>
            </ul>
          </div>

          <div class="glass-card p-10">
            <h4 class="font-extrabold text-xl text-[#312873] mb-3">Roadmap: demand intelligence</h4>
            <p class="text-sm text-gray-600">
              As FleetOS evolves, drivers can benefit from market-ready demand insights like heatmaps and proactive positioning guidance.
            </p>
          </div>
        </div>

        <div class="glass-card p-10 bg-[#312873] text-white">
          <h4 class="text-2xl font-extrabold mb-3 text-[#F29C0B]">Recruiting advantage for offices</h4>
          <p class="opacity-85 mb-8 max-w-3xl">
            Offices can recruit and retain better drivers with professional tools, clear economics, and brand-led stability.
          </p>
          <div class="flex gap-4 flex-wrap">
            <button onclick="showPage('office-signup')" class="btn-primary px-8 py-3">Launch Your Virtual Office</button>
            <button onclick="showPage('academy')" class="btn-secondary px-8 py-3">Explore Academy</button>
          </div>
        </div>
      </div>
    </section>

    <!-- 11) ROLLOUT -->
    <section id="page-rollout" class="page-content px-6 py-24">
      <div class="max-w-5xl mx-auto">
        <span class="section-tag">Rollout</span>
        <h2 class="text-5xl font-extrabold text-[#312873] mb-6">A Global Platform with a Local Heart</h2>
        <p class="text-xl text-gray-500 mb-12 max-w-4xl">
          FleetOS launches country-by-country with regulatory awareness and marketplace standards designed to protect riders and operators.
        </p>

        <div class="grid md:grid-cols-2 gap-8 mb-10">
          <div class="glass-card p-10">
            <h4 class="font-extrabold text-2xl text-[#312873] mb-4">Compliance-first approach</h4>
            <ul class="text-sm text-gray-600 space-y-2">
              <li>• Verified office onboarding (market-dependent)</li>
              <li>• Minimum driver standards and service policies</li>
              <li>• Governance rules that protect fair competition and trust</li>
            </ul>
          </div>
          <div class="glass-card p-10 bg-gray-50">
            <h4 class="font-extrabold text-2xl text-[#312873] mb-4">Launch process</h4>
            <ul class="text-sm text-gray-700 space-y-2">
              <li>• Market assessment</li>
              <li>• Office verification</li>
              <li>• Driver standards readiness</li>
              <li>• Controlled go-live and monitoring</li>
            </ul>
          </div>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
          <div class="glass-card p-10 border-green-200 bg-green-50">
            <h4 class="font-extrabold text-green-900 mb-3 text-xl">Active</h4>
            <p class="text-sm text-green-700 mb-8">Onboarding now.</p>
            <button onclick="showPage('office-signup')" class="btn-primary w-full py-3">Check Availability</button>
          </div>
          <div class="glass-card p-10 border-orange-200 bg-orange-50">
            <h4 class="font-extrabold text-orange-900 mb-3 text-xl">Pending</h4>
            <p class="text-sm text-orange-700 mb-8">Join the waitlist to become a founding office.</p>
            <button onclick="showPage('contact', 'waitlist')" class="btn-secondary w-full py-3">Join Waitlist</button>
          </div>
          <div class="glass-card p-10 border-gray-200 bg-gray-50">
            <h4 class="font-extrabold text-gray-900 mb-3 text-xl">Planned</h4>
            <p class="text-sm text-gray-700 mb-8">Interest registered; assessment underway.</p>
            <button onclick="showPage('contact', 'waitlist')" class="btn-secondary w-full py-3">Register Interest</button>
          </div>
        </div>
      </div>
    </section>

    <!-- 12) PRICING -->
    <section id="page-pricing" class="page-content px-6 py-24 text-center bg-gray-50">
      <div class="max-w-5xl mx-auto">
        <span class="section-tag">Pricing</span>
        <h2 class="text-6xl font-extrabold text-[#312873] mb-6">We Only Succeed When You Do.</h2>
        <p class="text-xl text-gray-500 mb-12">$25/month + 12% commission per completed ride. No hidden royalties.</p>

        <div class="grid lg:grid-cols-2 gap-8 mb-12 text-left">
          <div class="glass-card p-10">
            <h4 class="text-2xl font-extrabold text-[#312873] mb-4">Basic plan</h4>
            <ul class="text-sm text-gray-600 space-y-2">
              <li>• $25/month subscription</li>
              <li>• 12% commission per completed ride</li>
              <li>• Includes: office dashboard, marketplace listing, unlimited driver app access</li>
              <li>• Standard support, analytics, reporting</li>
            </ul>
            <div class="mt-6 p-4 rounded-2xl bg-orange-50 border border-orange-100 text-sm font-extrabold text-[#312873]">
              Callout: Keep 88% of fares — no hidden royalties or franchise fees.
            </div>
          </div>

          <div class="glass-card p-10 bg-white">
            <h4 class="text-2xl font-extrabold text-[#312873] mb-4">Included vs not included</h4>
            <div class="grid md:grid-cols-2 gap-4 text-sm">
              <div class="p-4 rounded-2xl bg-gray-50">
                <p class="font-extrabold text-[#312873] mb-2">Included</p>
                <ul class="text-gray-700 space-y-1">
                  <li>• Platform access</li>
                  <li>• Marketplace exposure</li>
                  <li>• Office tools</li>
                  <li>• Governance + trust layer</li>
                </ul>
              </div>
              <div class="p-4 rounded-2xl bg-gray-50">
                <p class="font-extrabold text-[#312873] mb-2">Not included</p>
                <ul class="text-gray-700 space-y-1">
                  <li>• Payment processing fees (market-dependent)</li>
                  <li>• SMS costs</li>
                  <li>• Map provider costs</li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <div class="glass-card p-12 max-w-2xl mx-auto mb-10 text-left">
          <h4 class="text-[10px] font-black text-gray-400 tracking-widest uppercase mb-8">Profit calculator</h4>
          <div class="space-y-6">
            <div>
              <label class="text-xs font-extrabold mb-2 block">Monthly rides</label>
              <input type="number" id="calc-rides" value="1000" oninput="runCalc()">
            </div>
            <div>
              <label class="text-xs font-extrabold mb-2 block">Average fare (USD)</label>
              <input type="number" id="calc-fare" value="15" oninput="runCalc()">
            </div>
            <div class="pt-8 border-t border-gray-100">
              <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-extrabold text-gray-400">FleetOS fee (12% + $25):</span>
                <span id="res-fee" class="text-sm font-extrabold text-red-400">-$0</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-xl font-extrabold text-[#312873]">Retained revenue:</span>
                <span id="res-net" class="text-3xl font-black text-[#F29C0B]">$0</span>
              </div>
            </div>
          </div>
        </div>

        <div class="flex gap-4 justify-center flex-wrap">
          <button onclick="showPage('office-signup')" class="btn-primary px-12 py-5 text-xl">Launch Your Office</button>
          <button onclick="showPage('contact', 'sales')" class="btn-secondary px-12 py-5 text-xl">Talk to Sales</button>
        </div>
      </div>
    </section>

    <!-- 13) ACADEMY -->
    <section id="page-academy" class="page-content px-6 py-24">
      <div class="max-w-6xl mx-auto">
        <span class="section-tag">Learning Center</span>
        <h2 class="text-5xl font-extrabold text-[#312873] mb-6">FleetOS Academy: Learn, Launch, Scale</h2>
        <p class="text-xl text-gray-500 mb-12 max-w-4xl">
          Shopify-level platforms win through education. FleetOS Academy is your operator learning system: playbooks, checklists, and training paths to help offices grow reliably.
        </p>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
          <div class="glass-card p-8">
            <h4 class="font-extrabold mb-2 text-[#312873]">Launch & Compliance Readiness</h4>
            <p class="text-xs text-gray-500">Onboarding checklist, service policy templates, safety standards, driver SOPs.</p>
          </div>
          <div class="glass-card p-8">
            <h4 class="font-extrabold mb-2 text-[#312873]">Marketplace Growth & Pricing Strategy</h4>
            <p class="text-xs text-gray-500">Price ranges that convert, positioning, response time, reputation loops.</p>
          </div>
          <div class="glass-card p-8">
            <h4 class="font-extrabold mb-2 text-[#312873]">Driver Recruitment & Retention</h4>
            <p class="text-xs text-gray-500">Recruiting playbook, training loops, retention incentives, quality control.</p>
          </div>
          <div class="glass-card p-8">
            <h4 class="font-extrabold mb-2 text-[#312873]">Corporate Mobility Setup</h4>
            <p class="text-xs text-gray-500">Billing workflows, reporting setup, policy controls.</p>
          </div>
          <div class="glass-card p-8 bg-gray-50">
            <h4 class="font-extrabold mb-2 text-[#312873]">Certification</h4>
            <p class="text-xs text-gray-600">Certified FleetOS Operator badge for offices completing launch & quality programs (market-dependent).</p>
          </div>
          <div class="glass-card p-8 bg-gray-50">
            <h4 class="font-extrabold mb-2 text-[#312873]">Community</h4>
            <p class="text-xs text-gray-600">A moderated operator community: regulatory shifts, standards, growth learnings.</p>
          </div>
        </div>

        <div class="flex gap-4 flex-wrap">
          <button onclick="showPage('office-signup')" class="btn-primary px-8 py-3">Start Learning</button>
          <button onclick="showPage('contact', 'demo')" class="btn-secondary px-8 py-3">Download Operator Playbook</button>
        </div>
      </div>
    </section>

    <!-- 14) TECHNICAL FAQ -->
    <section id="page-tech-faq" class="page-content px-6 py-24 bg-gray-50">
      <div class="max-w-5xl mx-auto">
        <span class="section-tag">Technical</span>
        <h2 class="text-5xl font-extrabold text-[#312873] mb-6">Technical FAQ for IT Teams, Enterprise Fleets, and Regulators</h2>
        <p class="text-xl text-gray-500 mb-12 max-w-4xl">
          FleetOS is designed for high-trust mobility operations. This page answers common technical questions from enterprise stakeholders.
        </p>

        <div class="grid md:grid-cols-2 gap-8">
          <div class="glass-card p-10">
            <h4 class="font-extrabold text-xl text-[#312873] mb-3">APIs & integration</h4>
            <p class="text-sm text-gray-600 mb-4">FleetOS is built with integration readiness in mind. API availability and scopes depend on plan level and market readiness.</p>
            <ul class="text-sm text-gray-600 space-y-2">
              <li>• Enterprise exports and reporting can be enabled by agreement.</li>
              <li>• Integration roadmaps align with compliance requirements.</li>
            </ul>
          </div>

          <div class="glass-card p-10 bg-white">
            <h4 class="font-extrabold text-xl text-[#312873] mb-3">Reliability & uptime</h4>
            <p class="text-sm text-gray-600 mb-4">Designed for high availability with monitoring and operational controls.</p>
            <ul class="text-sm text-gray-600 space-y-2">
              <li>• Enterprise SLAs can be available for qualifying partners and markets.</li>
            </ul>
          </div>

          <div class="glass-card p-10 bg-white">
            <h4 class="font-extrabold text-xl text-[#312873] mb-3">Security & auditability</h4>
            <ul class="text-sm text-gray-600 space-y-2">
              <li>• Role-based access control for office staff accounts.</li>
              <li>• Audit-friendly logs for key operational actions.</li>
              <li>• Structured incident workflows for safety and fraud scenarios.</li>
            </ul>
          </div>

          <div class="glass-card p-10">
            <h4 class="font-extrabold text-xl text-[#312873] mb-3">Data handling & residency</h4>
            <p class="text-sm text-gray-600">
              FleetOS expands market-by-market. Data handling aligns with operational and legal requirements as markets launch.
            </p>
          </div>
        </div>

        <div class="flex gap-4 flex-wrap mt-10">
          <button onclick="showPage('contact', 'corporate')" class="btn-primary px-8 py-3">Request Enterprise Brief</button>
          <button onclick="showPage('contact', 'sales')" class="btn-secondary px-8 py-3">Talk to Sales</button>
        </div>
      </div>
    </section>

    <!-- 15) GOVERNANCE -->
    <section id="page-governance" class="page-content px-6 py-24">
      <div class="max-w-5xl mx-auto">
        <span class="section-tag">Governance</span>
        <h2 class="text-5xl font-extrabold text-[#312873] mb-6">Marketplace Governance: The Rules of the Road</h2>
        <p class="text-xl text-gray-500 mb-12 max-w-4xl">
          A marketplace only succeeds when participants believe it is fair. FleetOS governance protects riders and offices through transparent rules and structured enforcement.
        </p>

        <div class="grid md:grid-cols-2 gap-8 mb-10">
          <div class="glass-card p-10">
            <h4 class="font-extrabold text-xl text-[#312873] mb-3">Fair play principles</h4>
            <ul class="text-sm text-gray-600 space-y-2">
              <li>• Transparency over black boxes</li>
              <li>• Competition based on quality, reliability, and trust</li>
              <li>• Consistent rules across participants</li>
            </ul>
          </div>
          <div class="glass-card p-10 bg-gray-50">
            <h4 class="font-extrabold text-xl text-[#312873] mb-3">Ratings integrity</h4>
            <ul class="text-sm text-gray-700 space-y-2">
              <li>• Verified-trip reviews only</li>
              <li>• Monitoring for manipulation signals</li>
              <li>• Structured investigation workflow for disputed ratings</li>
            </ul>
          </div>
        </div>

        <div class="glass-card p-10 mb-10">
          <h4 class="font-extrabold text-xl text-[#312873] mb-3">Dispute resolution workflow</h4>
          <div class="grid md:grid-cols-2 gap-4 text-sm text-gray-600">
            <div>• Incident submitted (rider, office, or driver)</div>
            <div>• Evidence review (trip logs, telemetry, notes)</div>
            <div>• Policy-based resolution (refund rules, warnings)</div>
            <div>• Escalation for safety or fraud cases</div>
            <div>• Enforcement actions when policies are violated</div>
          </div>
        </div>

        <div class="grid md:grid-cols-2 gap-8">
          <div class="glass-card p-10 bg-gray-50">
            <h4 class="font-extrabold text-xl text-[#312873] mb-3">Preventing price dumping</h4>
            <p class="text-sm text-gray-700">
              Governance supports pricing integrity through transparency requirements, guardrails against misleading fare presentation,
              and monitoring for abnormal patterns (market-dependent).
            </p>
          </div>

          <div class="glass-card p-10">
            <h4 class="font-extrabold text-xl text-[#312873] mb-3">Enforcement (quality control)</h4>
            <ul class="text-sm text-gray-600 space-y-2">
              <li>• Warnings and corrective action plans</li>
              <li>• Temporary restrictions or suspensions</li>
              <li>• Removal for severe or repeated violations</li>
            </ul>
          </div>
        </div>

        <div class="flex gap-4 flex-wrap mt-10">
          <button onclick="showPage('conduct')" class="btn-primary px-8 py-3">View Code of Conduct</button>
          <button onclick="showPage('safety')" class="btn-secondary px-8 py-3">View Safety & Security</button>
        </div>
      </div>
    </section>

    <!-- 16) CODE OF CONDUCT -->
    <section id="page-conduct" class="page-content px-6 py-24 bg-gray-50">
      <div class="max-w-5xl mx-auto">
        <span class="section-tag">Policy</span>
        <h2 class="text-5xl font-extrabold text-[#312873] mb-6">Marketplace Code of Conduct</h2>
        <p class="text-xl text-gray-500 mb-12 max-w-4xl">
          FleetOS is a selection-based marketplace where riders choose local taxi offices by brand, rating, and price range.
          This Code of Conduct protects trust and fairness for riders, drivers, and offices.
        </p>

        <div class="space-y-6">
          <div class="glass-card p-10">
            <h3 class="font-black text-[#312873] mb-3">1) Core principles</h3>
            <ul class="text-sm text-gray-600 space-y-2">
              <li>• Safety first: any behavior that creates safety risk is prohibited.</li>
              <li>• Transparency over manipulation: honest information and fair competition.</li>
              <li>• Respect and professional conduct: no harassment, threats, or discrimination.</li>
              <li>• Trust and accountability: rules exist to protect the marketplace for everyone.</li>
            </ul>
          </div>

          <div class="glass-card p-10">
            <h3 class="font-black text-[#312873] mb-3">2) Office standards</h3>
            <ul class="text-sm text-gray-600 space-y-2">
              <li>• Licensing and eligibility (market-dependent).</li>
              <li>• Accurate branding and representation (no impersonation or misleading claims).</li>
              <li>• Pricing transparency (no bait pricing, hidden fees, or deceptive fare presentation).</li>
              <li>• Service quality and incident responsiveness.</li>
              <li>• Data and privacy: use rider data only for legitimate trip operations and support.</li>
            </ul>
          </div>

          <div class="glass-card p-10">
            <h3 class="font-black text-[#312873] mb-3">3) Driver standards</h3>
            <ul class="text-sm text-gray-600 space-y-2">
              <li>• Identity and vehicle integrity: approved documentation and accurate identity.</li>
              <li>• Professional conduct: no harassment, discrimination, unsafe behavior, or impairment.</li>
              <li>• Trip integrity: no manipulation, impersonation, or account sharing.</li>
            </ul>
          </div>

          <div class="glass-card p-10">
            <h3 class="font-black text-[#312873] mb-3">4) Rider standards</h3>
            <ul class="text-sm text-gray-600 space-y-2">
              <li>• Respect and safety: no harassment, threats, or unsafe requests.</li>
              <li>• Booking integrity: no fraud, repeated abuse, or intentional misuse of cancellations.</li>
            </ul>
          </div>

          <div class="glass-card p-10">
            <h3 class="font-black text-[#312873] mb-3">5) Ratings and reputation integrity</h3>
            <ul class="text-sm text-gray-600 space-y-2">
              <li>• No incentivized ratings, pressure tactics, or retaliatory behavior.</li>
              <li>• Verified experience: ratings should reflect genuine trips.</li>
              <li>• Dispute process: FleetOS may review disputed ratings when evidence indicates abuse.</li>
            </ul>
          </div>

          <div class="glass-card p-10">
            <h3 class="font-black text-[#312873] mb-3">6) Fraud, abuse, and prohibited behavior</h3>
            <ul class="text-sm text-gray-600 space-y-2">
              <li>• Identity fraud, payment fraud, manipulation, sabotage, or harassment.</li>
              <li>• Unauthorized access, scraping, or data harvesting.</li>
              <li>• Off-platform schemes that violate platform rules.</li>
            </ul>
          </div>

          <div class="glass-card p-10">
            <h3 class="font-black text-[#312873] mb-3">7) Dispute resolution and incident handling</h3>
            <ul class="text-sm text-gray-600 space-y-2">
              <li>• Incident submitted</li>
              <li>• Evidence review (trip data, telemetry, relevant logs)</li>
              <li>• Policy-based resolution</li>
              <li>• Escalation for safety and fraud</li>
              <li>• Final outcome and accountability actions</li>
            </ul>
          </div>

          <div class="glass-card p-10">
            <h3 class="font-black text-[#312873] mb-3">8) Enforcement and consequences</h3>
            <ul class="text-sm text-gray-600 space-y-2">
              <li>• Warnings and corrective actions</li>
              <li>• Temporary restrictions or suspensions</li>
              <li>• Removal for severe or repeated violations</li>
              <li>• Reporting to authorities where required by law (market-dependent)</li>
            </ul>
          </div>

          <div class="glass-card p-10">
            <h3 class="font-black text-[#312873] mb-3">9) Reporting violations</h3>
            <p class="text-sm text-gray-600">
              Use in-app reporting tools where available, or contact FleetOS via the official channels on the Contact page.
            </p>
          </div>

          <div class="glass-card p-10 bg-gray-50">
            <h3 class="font-black text-[#312873] mb-3">10) Updates to this policy</h3>
            <p class="text-sm text-gray-700">
              FleetOS may update this Code of Conduct to reflect operational improvements, safety standards, and regulatory changes.
              Updates will be published on this page with an effective date.
            </p>
            <p class="text-xs text-gray-500 mt-4 font-extrabold">Effective Date: Month Day, 2026</p>
          </div>
        </div>
      </div>
    </section>

    <!-- 17) OFFICE SIGN-UP (aligned to doc: market/contact + ops snapshot + compliance readiness) -->
    <section id="page-office-signup" class="page-content px-6 py-24">
      <div class="max-w-2xl mx-auto glass-card p-12 border-t-[12px] border-[#F29C0B]">
        <h2 class="text-4xl font-extrabold text-[#312873] mb-2">Launch Your Virtual Taxi Office</h2>
        <p class="text-sm text-gray-500 mb-10 font-extrabold uppercase tracking-wider italic">
          Start in the cloud. Build your brand in a shared marketplace. Scale without physical infrastructure.
        </p>

        <form class="space-y-6" onsubmit="event.preventDefault(); alert('Application Received. Our onboarding team will contact you shortly.');">
          <!-- Step 1: Market & Contact -->
          <div class="p-6 rounded-2xl bg-gray-50 border border-gray-100">
            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-4">Step 1 — Market & Contact</p>
            <div class="grid md:grid-cols-2 gap-4">
              <div>
                <label class="text-[10px] font-black uppercase mb-2 block">Office / Brand Name</label>
                <input type="text" placeholder="e.g., City Express" required>
              </div>
              <div>
                <label class="text-[10px] font-black uppercase mb-2 block">Contact Name</label>
                <input type="text" placeholder="Full name" required>
              </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4 mt-4">
              <div>
                <label class="text-[10px] font-black uppercase mb-2 block">Email</label>
                <input type="email" placeholder="name@company.com" required>
              </div>
              <div>
                <label class="text-[10px] font-black uppercase mb-2 block">Phone (with country code)</label>
                <input type="tel" placeholder="+90 5xx xxx xxxx" required>
              </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4 mt-4">
              <div>
                <label class="text-[10px] font-black uppercase mb-2 block">City</label>
                <input type="text" placeholder="City" required>
              </div>
              <div>
                <label class="text-[10px] font-black uppercase mb-2 block">Country</label>
                <input type="text" placeholder="Country" required>
              </div>
            </div>

            <div class="mt-4">
              <label class="text-[10px] font-black uppercase mb-2 block">Website (optional)</label>
              <input type="url" placeholder="https://your-domain.com">
            </div>
          </div>

          <!-- Step 2: Operations Snapshot -->
          <div class="p-6 rounded-2xl bg-white border border-gray-100">
            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-4">Step 2 — Operations Snapshot</p>

            <div class="grid md:grid-cols-2 gap-4">
              <div>
                <label class="text-[10px] font-black uppercase mb-2 block">Business Category</label>
                <select required>
                  <option value="">Select...</option>
                  <option>Startup / Entrepreneur (New Brand)</option>
                  <option>Existing Fleet (Migrating from Legacy)</option>
                  <option>Corporate Partner</option>
                </select>
              </div>
              <div>
                <label class="text-[10px] font-black uppercase mb-2 block">Fleet Size (drivers)</label>
                <input type="number" min="1" placeholder="Estimated driver count" required>
              </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4 mt-4">
              <div>
                <label class="text-[10px] font-black uppercase mb-2 block">Service Types</label>
                <select required>
                  <option value="">Select...</option>
                  <option>City rides (short distance)</option>
                  <option>Airport transfers</option>
                  <option>Corporate contracts</option>
                  <option>Mixed services</option>
                </select>
              </div>
              <div>
                <label class="text-[10px] font-black uppercase mb-2 block">Current Tools</label>
                <input type="text" placeholder="e.g., radio dispatch, legacy software, spreadsheets">
              </div>
            </div>

            <div class="mt-4">
              <label class="text-[10px] font-black uppercase mb-2 block">Coverage / Zones</label>
              <input type="text" placeholder="e.g., Downtown + Airport + Suburbs">
            </div>
          </div>

          <!-- Step 3: Compliance & Readiness -->
          <div class="p-6 rounded-2xl bg-gray-50 border border-gray-100">
            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-4">Step 3 — Compliance & Readiness</p>
            <div class="grid md:grid-cols-2 gap-4">
              <div>
                <label class="text-[10px] font-black uppercase mb-2 block">License Status</label>
                <select required>
                  <option value="">Select...</option>
                  <option>Yes (licensed)</option>
                  <option>No (not licensed yet)</option>
                  <option>Not sure</option>
                </select>
              </div>
              <div>
                <label class="text-[10px] font-black uppercase mb-2 block">Preferred Onboarding Timeline</label>
                <select required>
                  <option value="">Select...</option>
                  <option>Immediately</option>
                  <option>Within 30 days</option>
                  <option>Within 60–90 days</option>
                  <option>Exploring</option>
                </select>
              </div>
            </div>

            <div class="mt-4">
              <label class="text-[10px] font-black uppercase mb-2 block">Notes (optional)</label>
              <textarea rows="4" placeholder="Anything we should know about your market, requirements, or goals?"></textarea>
            </div>

            <div class="mt-4 text-xs text-gray-600 font-semibold leading-relaxed">
              By submitting, you agree to our <span class="font-black text-[#312873] cursor-pointer" onclick="showPage('conduct')">Code of Conduct</span> and acknowledge our marketplace onboarding terms.
            </div>
          </div>

          <button class="w-full btn-primary py-5 text-xl shadow-2xl mt-2">Submit Application</button>

          <div class="text-center text-[10px] text-gray-400 mt-2 uppercase font-black tracking-widest">
            Important links:
            <span class="cursor-pointer hover:text-[#312873]" onclick="showPage('conduct')">Code of Conduct</span> ·
            <span class="cursor-pointer hover:text-[#312873]" onclick="showPage('privacy')">Privacy</span> ·
            <span class="cursor-pointer hover:text-[#312873]" onclick="showPage('terms')">Terms</span> ·
            <span class="cursor-pointer hover:text-[#312873]" onclick="showPage('billing')">Billing</span> ·
            <span class="cursor-pointer hover:text-[#312873]" onclick="showPage('safety')">Safety</span> ·
            <span class="cursor-pointer hover:text-[#312873]" onclick="showPage('rollout')">Rollout</span>
          </div>
        </form>
      </div>
    </section>

    <!-- 18) CONTACT & EARLY ACCESS (aligned fields) -->
    <section id="page-contact" class="page-content px-6 py-24">
      <div class="max-w-2xl mx-auto glass-card p-12">
        <span class="section-tag">Contact</span>
        <h2 class="text-4xl font-extrabold text-[#312873] mb-2">Secure Your Spot in the Marketplace</h2>
        <p class="text-sm text-gray-500 mb-10 font-extrabold">
          Tell us where you operate. We’ll confirm market availability, outline onboarding requirements, and guide your next steps.
        </p>

        <form class="space-y-5" onsubmit="event.preventDefault(); alert('Thanks — we received your message.');">
          <div>
            <label class="text-[10px] font-black uppercase mb-2 block">Reason</label>
            <select id="contact-intent" required>
              <option value="demo">Request a demo</option>
              <option value="sales">Talk to sales</option>
              <option value="migration-audit">Migration audit</option>
              <option value="waitlist">Join waitlist</option>
              <option value="corporate">Corporate inquiry</option>
              <option value="partnerships">Partnerships (cities/regulators/enterprises)</option>
              <option value="enterprise-tech">Enterprise & technical questions</option>
            </select>
          </div>

          <div class="grid md:grid-cols-2 gap-4">
            <div>
              <label class="text-[10px] font-black uppercase mb-2 block">Full name</label>
              <input type="text" placeholder="Your name" required>
            </div>
            <div>
              <label class="text-[10px] font-black uppercase mb-2 block">Company / Office name</label>
              <input type="text" placeholder="Company or office" required>
            </div>
          </div>

          <div class="grid md:grid-cols-2 gap-4">
            <div>
              <label class="text-[10px] font-black uppercase mb-2 block">Email</label>
              <input type="email" placeholder="name@company.com" required>
            </div>
            <div>
              <label class="text-[10px] font-black uppercase mb-2 block">Phone (with country code)</label>
              <input type="tel" placeholder="+90 5xx xxx xxxx" required>
            </div>
          </div>

          <div class="grid md:grid-cols-2 gap-4">
            <div>
              <label class="text-[10px] font-black uppercase mb-2 block">City</label>
              <input type="text" placeholder="City" required>
            </div>
            <div>
              <label class="text-[10px] font-black uppercase mb-2 block">Country</label>
              <input type="text" placeholder="Country" required>
            </div>
          </div>

          <div>
            <label class="text-[10px] font-black uppercase mb-2 block">Fleet size (or write “New Startup”)</label>
            <input type="text" placeholder="e.g., 25 drivers or New Startup" required>
          </div>

          <div>
            <label class="text-[10px] font-black uppercase mb-2 block">Notes (optional)</label>
            <textarea rows="4" placeholder="Tell us about your fleet size, timeline, and any requirements..."></textarea>
          </div>

          <button class="w-full btn-primary py-4 text-lg">Submit</button>

          <div class="text-center text-[10px] text-gray-400 mt-2 uppercase font-black tracking-widest">
            Or <span class="cursor-pointer hover:text-[#312873]" onclick="showPage('office-signup')">launch your virtual office</span>
          </div>
        </form>
      </div>
    </section>

    <!-- LEGAL PLACEHOLDERS (doc references links, so we include pages) -->
    <section id="page-privacy" class="page-content px-6 py-24 bg-gray-50">
      <div class="max-w-4xl mx-auto glass-card p-12">
        <span class="section-tag">Legal</span>
        <h2 class="text-4xl font-extrabold text-[#312873] mb-4">Privacy Policy</h2>
        <p class="text-sm text-gray-600 leading-relaxed">
          Placeholder page. Add your Privacy Policy content here (data collection, processing, retention, rights, and contact).
        </p>
      </div>
    </section>

    <section id="page-terms" class="page-content px-6 py-24">
      <div class="max-w-4xl mx-auto glass-card p-12">
        <span class="section-tag">Legal</span>
        <h2 class="text-4xl font-extrabold text-[#312873] mb-4">Terms of Service</h2>
        <p class="text-sm text-gray-600 leading-relaxed">
          Placeholder page. Add your Terms of Service here (eligibility, platform rules, liabilities, dispute resolution).
        </p>
      </div>
    </section>

    <section id="page-billing" class="page-content px-6 py-24 bg-gray-50">
      <div class="max-w-4xl mx-auto glass-card p-12">
        <span class="section-tag">Legal</span>
        <h2 class="text-4xl font-extrabold text-[#312873] mb-4">Billing Policy</h2>
        <p class="text-sm text-gray-600 leading-relaxed">
          Placeholder page. Add your Billing Policy here (subscription billing, commissions, refunds, and payment terms).
        </p>
      </div>
    </section>

  </main>

  <!-- FOOTER -->
  <footer class="bg-[#312873] text-white py-24 px-6 mt-20">
    <div class="max-w-7xl mx-auto grid md:grid-cols-4 lg:grid-cols-5 gap-16">
      <div class="col-span-2 lg:col-span-1">
        <div class="flex items-center gap-2 mb-6">
          <div class="w-8 h-8 bg-white/10 rounded flex items-center justify-center">
            <svg class="w-5 h-5 text-[#F29C0B]" fill="currentColor" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
          </div>
          <span class="text-2xl font-black">FleetOS</span>
        </div>
        <p class="text-xs opacity-50 leading-relaxed max-w-xs">
          Building the global infrastructure for local mobility entrepreneurs. $25/month + 12% commission. We win when you win.
        </p>
      </div>

      <div>
        <h5 class="text-[11px] font-black uppercase tracking-widest text-[#F29C0B] mb-8">Platform</h5>
        <ul class="space-y-4 text-xs opacity-60">
          <li><a onclick="showPage('faas')" class="hover:opacity-100 cursor-pointer">Fleet-as-a-Service</a></li>
          <li><a onclick="showPage('marketplace')" class="hover:opacity-100 cursor-pointer">Marketplace</a></li>
          <li><a onclick="showPage('pricing')" class="hover:opacity-100 cursor-pointer">Pricing</a></li>
          <li><a onclick="showPage('technology')" class="hover:opacity-100 cursor-pointer">Technology</a></li>
        </ul>
      </div>

      <div>
        <h5 class="text-[11px] font-black uppercase tracking-widest text-[#F29C0B] mb-8">Trust</h5>
        <ul class="space-y-4 text-xs opacity-60">
          <li><a onclick="showPage('safety')" class="hover:opacity-100 cursor-pointer">Safety</a></li>
          <li><a onclick="showPage('governance')" class="hover:opacity-100 cursor-pointer">Governance</a></li>
          <li><a onclick="showPage('conduct')" class="hover:opacity-100 cursor-pointer">Code of Conduct</a></li>
          <li><a onclick="showPage('rollout')" class="hover:opacity-100 cursor-pointer">Rollout</a></li>
        </ul>
      </div>

      <div>
        <h5 class="text-[11px] font-black uppercase tracking-widest text-[#F29C0B] mb-8">Resources</h5>
        <ul class="space-y-4 text-xs opacity-60">
          <li><a onclick="showPage('academy')" class="hover:opacity-100 cursor-pointer">Academy</a></li>
          <li><a onclick="showPage('tech-faq')" class="hover:opacity-100 cursor-pointer">Technical FAQ</a></li>
          <li><a onclick="showPage('contact')" class="hover:opacity-100 cursor-pointer">Contact</a></li>
        </ul>
      </div>

      <div>
        <h5 class="text-[11px] font-black uppercase tracking-widest text-[#F29C0B] mb-8">Legal</h5>
        <ul class="space-y-4 text-xs opacity-60">
          <li><a onclick="showPage('privacy')" class="hover:opacity-100 cursor-pointer">Privacy Policy</a></li>
          <li><a onclick="showPage('terms')" class="hover:opacity-100 cursor-pointer">Terms of Service</a></li>
          <li><a onclick="showPage('billing')" class="hover:opacity-100 cursor-pointer">Billing Policy</a></li>
        </ul>
      </div>
    </div>
  </footer>

  <script>
    // Page metadata aligned to your doc's SEO title/meta descriptions (used for SPA title/description updates)
    const PAGE_META = {
      home: {
        slug: "/",
        title: "FleetOS | The Shopify for Mobility - Launch a Virtual Taxi Office in the Cloud",
        description: "Launch a branded taxi office in the cloud for $25/month + 12% commission. FleetOS is a multi-tenant marketplace where riders choose local taxi brands by rating and price range."
      },
      faas: {
        slug: "/fleet-as-a-service",
        title: "Fleet-as-a-Service (FaaS) | The Shopify for Mobility Explained - FleetOS",
        description: "Fleet-as-a-Service is a multi-tenant mobility model where taxi offices launch branded virtual offices and compete in one shared passenger marketplace. Learn how FleetOS works."
      },
      marketplace: {
        slug: "/marketplace",
        title: "FleetOS Marketplace | One App Where Riders Choose Local Taxi Brands",
        description: "FleetOS is a selection-based marketplace: riders compare and choose local taxi offices by brand, rating, and price range."
      },
      compare: {
        slug: "/compare",
        title: "FleetOS vs Dispatch vs Ride-Hailing | The Marketplace Model for Local Taxi Brands",
        description: "Compare FleetOS with dispatch software and centralized ride-hailing. FleetOS is Fleet-as-a-Service: shared demand plus local brand ownership and selection-based competition."
      },
      "sol-fleets": {
        slug: "/solutions/fleets",
        title: "Taxi Fleet Platform | Cloud Dispatch + Marketplace Demand for Existing Offices - FleetOS",
        description: "Migrate from legacy dispatch and hardware to FleetOS cloud operations. Keep your brand, onboard drivers, and gain demand through the shared marketplace."
      },
      "sol-startups": {
        slug: "/solutions/startups",
        title: "Start a Taxi Business Online | Launch a Virtual Taxi Office for $25/Month - FleetOS",
        description: "Launch a taxi office in the cloud for $25/month + 12% commission. Recruit drivers, build your brand, and appear in the shared rider marketplace."
      },
      "sol-corp": {
        slug: "/solutions/corporate",
        title: "Corporate Mobility Platform | Centralized Billing + Reporting - FleetOS",
        description: "FleetOS Corporate supports centralized billing, policy controls, and reporting for employee and guest rides powered by verified local offices inside one marketplace."
      },
      technology: {
        slug: "/technology",
        title: "FleetOS Technology | Cloud Marketplace Infrastructure + AI Roadmap",
        description: "FleetOS provides cloud operations for offices, a shared passenger marketplace, secure trip telemetry, and an AI roadmap for demand forecasting and routing optimization."
      },
      safety: {
        slug: "/safety",
        title: "FleetOS Safety & Security | Rider Safety Tools + Data Protection",
        description: "FleetOS includes safety workflows, verification standards, and secure handling of sensitive mobility data for riders, drivers, and offices."
      },
      drivers: {
        slug: "/drivers",
        title: "FleetOS Driver App | Transparent Earnings + Professional Tools",
        description: "FleetOS supports drivers with transparent earnings visibility, professional trip workflows, and roadmap demand intelligence tools like heatmaps."
      },
      rollout: {
        slug: "/rollout",
        title: "FleetOS Rollout & Compliance | Verified Marketplace Expansion by Country",
        description: "FleetOS expands market-by-market using a compliance-first model with office verification, driver standards, and marketplace governance."
      },
      pricing: {
        slug: "/pricing",
        title: "FleetOS Pricing | $25/Month + 12% Commission - Transparent Marketplace Pricing",
        description: "FleetOS pricing is simple: $25/month plus 12% commission per completed ride. No franchise fees, no hidden royalties, aligned incentives."
      },
      academy: {
        slug: "/academy",
        title: "FleetOS Academy | Operator Training, Playbooks, Certification",
        description: "FleetOS Academy offers learning paths for operators and founders: compliance readiness, pricing strategy, driver retention, corporate mobility, and certification."
      },
      "tech-faq": {
        slug: "/technical-faq",
        title: "FleetOS Technical FAQ | APIs, Integrations, Security, and Reliability",
        description: "FleetOS answers enterprise IT and regulator questions: API readiness, uptime approach, role-based access, audit trails, and integration capabilities."
      },
      governance: {
        slug: "/governance",
        title: "FleetOS Marketplace Governance | Fair Play, Ratings Integrity, Dispute Resolution",
        description: "FleetOS governance protects the marketplace through fair competition principles, verified ratings, pricing transparency guardrails, and dispute workflows."
      },
      conduct: {
        slug: "/code-of-conduct",
        title: "FleetOS Marketplace Code of Conduct | Standards for Offices, Drivers, and Riders",
        description: "FleetOS Marketplace Code of Conduct defines fair competition, safety standards, service quality expectations, pricing transparency rules, and enforcement procedures."
      },
      "office-signup": {
        slug: "/office-signup",
        title: "Launch Your Virtual Taxi Office | FleetOS Office Sign-Up",
        description: "Apply to launch your office in the FleetOS marketplace. Start with $25/month + 12% commission per completed ride."
      },
      contact: {
        slug: "/contact",
        title: "Contact FleetOS | Request a Demo or Apply for Marketplace Access",
        description: "Contact FleetOS to request a demo, apply for onboarding, corporate mobility, partnerships, or enterprise questions. Provide your city and fleet size to confirm market readiness."
      },
      privacy: { slug: "/privacy", title: "FleetOS | Privacy Policy", description: "FleetOS privacy policy placeholder." },
      terms: { slug: "/terms", title: "FleetOS | Terms of Service", description: "FleetOS terms of service placeholder." },
      billing: { slug: "/billing", title: "FleetOS | Billing Policy", description: "FleetOS billing policy placeholder." }
    };

    function setMeta(pageId) {
      const meta = PAGE_META[pageId] || PAGE_META.home;
      document.title = meta.title;
      const md = document.getElementById("meta-description");
      if (md) md.setAttribute("content", meta.description);

      // lightweight URL update (hash-based so it works anywhere)
      const hash = "#" + meta.slug;
      if (window.location.hash !== hash) window.location.hash = hash;
    }

    function showPage(pageId, intent = null) {
      document.querySelectorAll('.page-content').forEach(p => p.classList.remove('active'));
      const target = document.getElementById('page-' + pageId);

      if (target) {
        target.classList.add('active');

        // Set contact intent dropdown
        if (pageId === "contact" && intent) {
          const select = document.getElementById('contact-intent');
          if (select) select.value = intent;
        }

        setMeta(pageId);
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    }

    function runCalc() {
      const rides = parseFloat(document.getElementById('calc-rides')?.value) || 0;
      const fare  = parseFloat(document.getElementById('calc-fare')?.value) || 0;
      const gross = rides * fare;
      const fee   = (gross * 0.12) + 25;
      const net   = Math.max(0, gross - fee);

      const feeEl = document.getElementById('res-fee');
      const netEl = document.getElementById('res-net');

      if (feeEl) feeEl.innerText = '-$' + Math.round(fee).toLocaleString();
      if (netEl) netEl.innerText = '$' + Math.round(net).toLocaleString();
    }

    // Open correct page from hash on load
    function resolvePageFromHash() {
      const h = (window.location.hash || "#/").replace("#", "");
      const entry = Object.entries(PAGE_META).find(([, v]) => v.slug === h);
      const pageId = entry ? entry[0] : "home";
      showPage(pageId);
    }

    window.addEventListener("hashchange", resolvePageFromHash);

    window.onload = () => {
      runCalc();
      resolvePageFromHash();
    };
  </script>

</body>
</html>
