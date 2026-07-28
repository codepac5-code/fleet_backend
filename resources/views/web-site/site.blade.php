د <!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ session('dir', 'ltr') }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Default (Homepage) SEO, updated dynamically per page -->
  <title>FleetOS | The Shopify for Mobility - Launch a Virtual Taxi Office in the Cloud</title>
  <meta id="meta-description" name="description"
        content="Launch a branded taxi office in the cloud for $25/month + 12% commission. FleetOS is a multi-tenant marketplace where riders choose local taxi brands by rating and price range." />

  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<!-- FontAwesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

  <style>

:root{
  --primary:#F29C0B;
  --secondary:#312873;
  --accent:#FFB43B;
  --text:#1A1A1A;
  --text-muted:#555;
  --bg:#F8F9FC;
  --radius:16px;
  --card-bg:rgba(255,255,255,0.85);
}

body{
  font-family:'Plus Jakarta Sans',sans-serif;
  background:var(--bg);
  color:var(--text);

}





/* HERO GLOW */
.hero-background{position:absolute;inset:0;overflow:hidden;z-index:0;}
.hero-glow{position:absolute;border-radius:50%;filter:blur(120px);opacity:.25;}
.hero-glow.primary{width:520px;height:520px;background:var(--primary);top:-140px;left:-140px;}
.hero-glow.secondary{width:480px;height:480px;background:var(--secondary);bottom:-160px;right:-140px;}
.hero-glow.soft{width:380px;height:380px;background:var(--accent);top:40%;left:45%;opacity:.12;}

/* HERO TEXT */
.hero-title{font-size:3.2rem;font-weight:900;letter-spacing:-1px;line-height:1.05;color:var(--secondary);}
.hero-highlight{color:var(--primary);}
.section-tag{font-size:30px;letter-spacing:.18em;font-weight:900;text-transform:uppercase;color:var(--primary);margin-bottom:1rem;display:inline-block;}

/* BUTTONS */
.btn-primary,.btn-secondary{display:inline-flex;align-items:center;gap:.6rem;transition:.25s;font-weight:800;border-radius:var(--radius);}
.btn-primary{background:linear-gradient(135deg,var(--primary),var(--accent));color:white;padding:1rem 1.6rem;border:none;box-shadow:0 12px 30px rgba(242,156,11,.35);}
.btn-primary:hover{transform:translateY(-3px);}
.btn-secondary{border:2px solid var(--secondary);color:var(--secondary);background:white;padding:.9rem 1.4rem;}
.btn-secondary:hover{background:var(--secondary);color:white;}
.btn-primary i,.btn-secondary i{font-size:1.1rem;vertical-align:middle;}

/* MINI BENEFITS */
.hero-benefits{margin-top:1.5rem;display:flex;flex-wrap:wrap;gap:.8rem;font-size:.85rem;}
.hero-benefits span{background:var(--card-bg);padding:.5rem .8rem;border-radius:999px;border:1px solid rgba(0,0,0,.05);transition:.2s;opacity:0;animation:floatIn .6s ease-out forwards;}
.hero-benefits span:hover{transform:translateY(-2px);background:rgba(242,156,11,.12);}
.hero-benefits span:nth-child(1){animation-delay:.3s;}
.hero-benefits span:nth-child(2){animation-delay:.35s;}
.hero-benefits span:nth-child(3){animation-delay:.4s;}
.hero-benefits span:nth-child(4){animation-delay:.45s;}
.hero-benefits span:nth-child(5){animation-delay:.5s;}

/* PLATFORM BADGES */
.platforms{display:flex;gap:1.2rem;margin-top:1.5rem;}
.platform{background:white;padding:.7rem 1rem;font-weight:600;font-size:.85rem;display:flex;align-items:center;gap:.5rem;border-radius:14px;box-shadow:0 8px 20px rgba(0,0,0,.06);transition:.25s;opacity:0;animation:floatIn .6s ease-out forwards;}
.platform i{font-size:1rem;color:var(--primary);}
.platform:hover{transform:translateY(-3px);}
.platform:hover i{color:var(--accent);}
.platform:nth-child(1){animation-delay:.4s;}
.platform:nth-child(2){animation-delay:.45s;}
.platform:nth-child(3){animation-delay:.5s;}

/* MARKETPLACE CARD */
.market-card{background:linear-gradient(140deg,var(--secondary),#1f1848);color:white;border-radius:28px;padding:2rem;box-shadow:0 40px 90px rgba(49,40,115,.45);margin-top:2rem;opacity:0;animation:floatIn .6s ease-out forwards;animation-delay:.2s;}
.market-item{background:rgba(255,255,255,.08);padding:1rem;border-radius:14px;display:flex;justify-content:space-between;align-items:center;font-weight:700;margin-bottom:.6rem;transition:.25s;}
.market-item:hover{background:rgba(255,255,255,.18);transform:translateX(6px);}

/* STARS */
.star{font-size:.85rem;color:#FFD700;margin-left:3px;vertical-align:middle;}
.market-item:hover i.star{transform:scale(1.15);transition:transform .2s ease;}

/* LIVE ACTIVITY */
.live-activity{margin-top:1.2rem;display:flex;gap:1rem;font-size:.85rem;opacity:.9;}
.live-dot{display:inline-flex;align-items:center;justify-content:center;}
.live-dot i{font-size:.95rem;margin-right:4px;transition:transform .25s ease;}
.live-dot:hover i{transform:translateY(-2px) scale(1.1);}

/* PILLS */
.pill{font-size:10px;font-weight:900;letter-spacing:.14em;padding:.55rem .85rem;border-radius:999px;border:1px solid rgba(0,0,0,.08);background:var(--card-bg);backdrop-filter:blur(6px);transition:.2s;opacity:0;animation:floatIn .6s ease-out forwards;}
.pill:hover{background:rgba(242,156,11,.12);transform:translateY(-2px);}
.pill:nth-child(1){animation-delay:.35s;}
.pill:nth-child(2){animation-delay:.37s;}
.pill:nth-child(3){animation-delay:.39s;}
.pill:nth-child(4){animation-delay:.41s;}
.pill:nth-child(5){animation-delay:.43s;}



/* PAGE CONTENT - showPage */
.page-content{display:none;animation:fadeIn .4s ease-out forwards;}
.page-content.active{display:block;}

/* ANIMATIONS */
@keyframes fadeIn {from {opacity:0; transform:translateY(12px);} to {opacity:1; transform:translateY(0);}}
@keyframes floatIn {from {opacity:0; transform:translateY(20px);} to {opacity:1; transform:translateY(0);}}


a,
button,
input[type="button"],
input[type="submit"],
input[type="reset"],
label[for],
[onclick] {
  cursor: pointer;
}



/* FOOTER RADICAL DESIGN */
footer {
  position: relative;
  background: linear-gradient(135deg, #312873, #1f1848);
  color: white;
  padding: 8rem 2rem 4rem 2rem;
  overflow: hidden;
  font-family: 'Plus Jakarta Sans', sans-serif;
  z-index: 10;
}

footer::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle at 30% 30%, rgba(242,156,11,0.15), transparent 70%);
  z-index: 0;
}

footer::after {
  content: '';
  position: absolute;
  bottom: -50%;
  right: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle at 70% 70%, rgba(49,40,115,0.15), transparent 70%);
  z-index: 0;
}

footer .footer-content {
  position: relative;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 3rem;
  max-width: 1200px;
  margin: 0 auto;
  z-index: 10;
}

footer .footer-logo {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

footer .footer-logo svg {
  width: 2rem;
  height: 2rem;
  color: var(--primary);
}

footer .footer-logo span {
  font-size: 2rem;
  font-weight: 900;
}

footer .footer-desc {
  font-size: 0.875rem;
  line-height: 1.6;
  opacity: 0.75;
  max-width: 16rem;
}

footer h5 {
  font-size: 1.2rem;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: 0.18em;
  color: var(--primary);
  margin-bottom: 1.5rem;
}

footer ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

footer ul li a {
  font-size: 0.825rem;
  color: rgba(255,255,255,0.7);
  display: inline-block;
  margin-bottom: 0.6rem;
  transition: all 0.2s ease;
}

footer ul li a:hover {
  color: var(--accent);
  transform: translateX(4px);
}

/* Footer Social Icons */
footer .footer-social {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
}

footer .footer-social a {
  width: 2.5rem;
  height: 2.5rem;
  background: rgba(255,255,255,0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.2s ease;
}

footer .footer-social a:hover {
  background: var(--primary);
  color: white;
  transform: translateY(-3px);
}

footer .footer-social i {
  font-size: 1.1rem;
}

/* Responsive adjustments */
@media (max-width: 1024px){
  footer .footer-content {
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 2rem;
  }
  footer .footer-logo span {
    font-size: 1.5rem;
  }
  footer .footer-desc { max-width: 100%; }
}
















/* Reset */
*{margin:0;padding:0;box-sizing:border-box;font-family:'Plus Jakarta Sans',sans-serif;}

/* Header */
.header{
  position:fixed; top:0; left:0; right:0; z-index:100;
  display:flex; justify-content:space-between; align-items:center;
  backdrop-filter:blur(15px); background:rgba(255,255,255,0.15);
  padding:0 2rem; height:80px; transition:background 0.3s, box-shadow 0.3s;
}
.header.scrolled{ background:rgba(255,255,255,0.05); box-shadow:0 10px 40px rgba(0,0,0,0.08); }

/* Logo */
.logo{ display:flex; align-items:center; gap:0.5rem; cursor:pointer; }
.logo-icon{
  width:48px; height:48px; border-radius:50%; background:linear-gradient(135deg,var(--secondary),#1f1a5c);
  display:flex; justify-content:center; align-items:center; position:relative; transition:transform 0.5s;
}
.logo:hover .logo-icon{ transform: scale(1.15); }
.animate-spin-slow{ animation: spinSlow 4s linear infinite; }
.animate-horizontal-spin{ animation: horizontalShake 2s ease-in-out infinite; }
.logo-text{ font-weight:900; font-size:1.8rem; display:flex; gap:0.1rem; }
.logo-text .accent{ color: var(--primary); }
.logo-text .main{ color: var(--secondary); }
.logo-text {
  direction: ltr;
  unicode-bidi: isolate;
}
/* Nav */
.nav{ display:flex; gap:2rem; align-items:center; }
.nav-list{ display:flex; gap:2rem; align-items:center; list-style:none; }
.nav-item {
    position: relative;
    font-weight: 600;
    font-size: 1rem;
    color: var(--secondary);
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
}

.nav-item button {
    background: none;
    border: none;
    cursor: pointer;
    font-weight: 600;
    color: inherit;
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-family: 'Poppins', sans-serif;
}
/* Mega Menu Side */
.dropdown-menu{
  position:absolute; top:0; left:100%;
  display:grid; grid-template-columns: repeat(2, 1fr); gap:1rem;
  min-width:480px; max-width:600px; background:#fff;
  border-radius:24px; border:1px solid #E5E7EB;
  box-shadow:0 20px 40px rgba(0,0,0,0.08);
  padding:1rem; opacity:0; visibility:hidden; transform:translateX(-10px);
  transition: all 0.25s ease; z-index:200;
}
.nav-item.dropdown:hover .dropdown-menu{ opacity:1; visibility:visible; transform:translateX(0); }

/* Card inside menu */
.card{
  display:flex; align-items:flex-start; gap:0.75rem;
  padding:0.75rem 1rem; border-radius:16px; background:#F8F9FC;
  cursor:pointer; transition:all 0.2s; width:100%;
}
.card:hover{ transform:translateY(-2px); box-shadow:0 10px 20px rgba(0,0,0,0.08); }
.card-icon{ width:28px; height:28px; display:flex; justify-content:center; align-items:center; border-radius:50%; font-size:14px; color:#fff; flex-shrink:0; }
.card-title{ font-weight:700; font-size:0.95rem; color: var(--secondary); }
.card-desc{ font-size:0.85rem; color:#555; line-height:1.4; }
.card:hover .card-title{ color: var(--primary); }

/* Header actions */
.header-actions{ display:flex; gap:1rem; align-items:center; }
.lang-dropdown{ position:relative; }
.lang-button{ background:transparent; border:1px solid var(--secondary); border-radius:12px; padding:0.35rem 0.75rem; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:0.25rem; transition:all 0.25s; color: var(--secondary);}
.lang-button:hover{ background: var(--secondary); color:#fff; }
.lang-menu{ position:absolute; top:100%; right:0; background:#fff; border:1px solid #E5E7EB; border-radius:16px; box-shadow:0 10px 25px rgba(0,0,0,0.08);
  padding:0.5rem 0; display:none; flex-direction:column; min-width:130px; z-index:300;
}
.lang-menu.active{ display:flex; }
.lang-menu button{ background:none; border:none; padding:0.5rem 1rem; display:flex; align-items:center; gap:0.5rem; cursor:pointer; transition:all 0.2s; width:100%; text-align:left;}
.lang-menu button:hover{ background: var(--secondary); color:#fff; border-radius:12px;}

/* Sign in icon only */
.sign-in{ font-size:1.2rem; color: var(--secondary); cursor:pointer; transition:all 0.25s;}
.sign-in:hover{ color: var(--primary);}

/* Animations */
@keyframes spinSlow{ 0%{transform:rotate(0deg);} 100%{transform:rotate(360deg);} }
@keyframes horizontalShake{ 0%,100%{transform:translateX(0);} 25%{transform:translateX(-2px);}50%{transform:translateX(2px);}75%{transform:translateX(-2px);} }


















.cards-wrapper{
  width:100%;
  display:flex;
  justify-content:center;
  position: relative;
  margin: 120px 0;

  padding: 80px 0;

  background: linear-gradient(
    180deg,
    rgba(49, 40, 115, 0.023),
    rgba(242, 157, 11, 0.049)
  );

  border-radius: 40px;
}

.features-section::before{
  content:"";
  position:absolute;
  top:-80px;
  left:50%;
  transform:translateX(-50%);
  width:600px;
  height:300px;

  background: radial-gradient(
    circle,
    rgba(242,156,11,0.15),
    transparent 70%
  );

  filter: blur(80px);
  z-index:0;

  position: relative;
  z-index:1;

}
.cards-grid{
  display:grid;

  grid-template-columns: repeat(2, 520px);
  gap:2rem;

  width: max-content;

  margin: 0 auto;
}

.card-pro{
  display:flex;
  align-items:center;
  gap:1.2rem;
    width:540px;


  height:190px;
  padding:1.2rem 1.4rem;

  border-radius:22px;

  backdrop-filter: blur(16px);
  border:1px solid rgba(255,255,255,0.4);

  box-shadow:0 15px 40px rgba(0,0,0,0.06);
  transition:all .3s ease;
}

.card-pro:hover{
  transform: translateY(-6px);
  box-shadow:0 25px 60px rgba(0,0,0,0.1);
}

.icon-wrap{
  width:46px;
  height:46px;
  border-radius:14px;
  display:flex;
  align-items:center;
  justify-content:center;
  flex-shrink:0;
  font-size:18px;
}

.card-content{
  display:flex;
  flex-direction:column;
  justify-content:center;
  gap:4px;
  flex:1;
}


.card-content h4 {
  font-size: 1.3rem;
  font-weight: 800;
  color: #312873;
  font-family: 'Plus Jakarta Sans', sans-serif;
}

.card-content p {
  font-size: 1rem;
  color: #555;
  line-height: 1.5;
  max-width: 460px;
  font-family: 'Plus Jakarta Sans', sans-serif;
}

.card-tags{
  display:flex;
  flex-direction:column;
  gap:6px;
}

.card-tags span{
  font-size:0.72rem;
  padding:0.25rem 0.6rem;
  border-radius:999px;
  background:rgba(49,40,115,0.08);
  color:#312873;
  white-space:nowrap;
}

.card-pro.primary{
  background:linear-gradient(135deg, rgba(242,156,11,0.18), rgba(255,255,255,0.25));
  border:1px solid rgba(242,156,11,0.25);
}
.card-pro.blue{
  background:linear-gradient(135deg, rgba(59,130,246,0.15), rgba(255,255,255,0.25));
  border:1px solid rgba(59,130,246,0.25);
}
.card-pro.purple{
  background:linear-gradient(135deg, rgba(139,92,246,0.15), rgba(255,255,255,0.25));
  border:1px solid rgba(139,92,246,0.25);
}
.card-pro.green{
  background:linear-gradient(135deg, rgba(16,185,129,0.15), rgba(255,255,255,0.25));
  border:1px solid rgba(16,185,129,0.25);
}

.primary .icon-wrap{ background:rgba(242,156,11,0.2); color:#F29C0B; }
.blue .icon-wrap{ background:rgba(59,130,246,0.2); color:#3B82F6; }
.purple .icon-wrap{ background:rgba(139,92,246,0.2); color:#8B5CF6; }
.green .icon-wrap{ background:rgba(16,185,129,0.2); color:#10B981; }



















/* SECTION */
.how-section{
  margin:120px 0;
}

/* BOX */
.how-box{
  background: linear-gradient(
    180deg,
    rgba(49,40,115,0.04),
    rgba(242,156,11,0.04)
  );
  padding:70px 40px;
  border-radius:40px;
  position:relative;
}

/* glow */
.how-box::before{
  content:"";
  position:absolute;
  top:-60px;
  left:50%;
  transform:translateX(-50%);
  width:500px;
  height:250px;
  background: radial-gradient(circle, rgba(242,156,11,0.15), transparent);
  filter: blur(80px);
}

/* GRID */
.steps-grid{
  position:relative;
}

/* line */
.steps-grid::before{
  content:"";
  position:absolute;
  top:40px;
  left:5%;
  right:5%;
  height:2px;
  background: linear-gradient(
    90deg,
    rgba(242, 157, 11, 0.271),
    rgba(49, 40, 115, 0.111)
  );
  z-index:0;
}

/* CARD */
.step-card{
  text-align:center;
  position:relative;
  z-index:1;
  background: rgba(255,255,255,0.7);
  backdrop-filter: blur(12px);
  border-radius:20px;
  padding:22px 16px;
  transition:all .3s ease;
}

.step-card:hover{
  transform: translateY(-8px);
  box-shadow:0 20px 50px rgba(0,0,0,0.1);
}

/* ICON */
.step-icon{
  width:48px;
  height:48px;
  border-radius:14px;
  display:flex;
  align-items:center;
  justify-content:center;
  margin:0 auto 12px;
  font-size:18px;
}

/* COLORS */
.orange{ background:rgba(242,156,11,0.15); color:#F29C0B; }
.blue{ background:rgba(59,130,246,0.15); color:#3B82F6; }
.green{ background:rgba(16,185,129,0.15); color:#10B981; }
.purple{ background:rgba(139,92,246,0.15); color:#8B5CF6; }
.gray{ background:rgba(107,114,128,0.15); color:#6B7280; }

/* TEXT */
.step-title{
  font-size:0.9rem;
  font-weight:800;
  color:#312873;
  margin-bottom:4px;
}

.step-desc{
  font-size:0.75rem;
  color:#555;
}























.logo-icon.motion-clear{
  width:44px;
  height:44px;
  border-radius:14px;

  display:flex;
  align-items:center;
  justify-content:center;

  background: linear-gradient(135deg,#312873,#1f1a5c);

  color:#F29C0B;

  box-shadow:
    0 8px 25px rgba(0,0,0,0.12),
    0 0 25px rgba(242,156,11,0.35);
}

.logo-icon.motion-clear svg{
  width:26px;
  height:26px;
}

.dot{
  animation: moveDotClear 2.2s linear infinite;
}

@keyframes moveDotClear{
  0%   { transform: translate(0,0); }
  50%  { transform: translate(22px,-16px); }
  100% { transform: translate(44px,-12px); }
}

.path{
  animation: dashClear 1.8s linear infinite;
}

@keyframes dashClear{
  to { stroke-dashoffset: -20; }
}

/* hover */
.logo:hover .logo-icon{
  transform: scale(1.1);
}




.lang-dropdown {
  display: inline-block;
}

.lang-button {
  display: flex;
  align-items: center;
  gap:3px;
  padding: 3px 7px;
  border-radius: 8px;
  text-decoration: none;
  cursor: pointer;
  background: rgba(255,255,255,0.1);
  backdrop-filter: blur(6px);
  color: #312873;
  font-size: 0.85rem;
  transition: 0.3s;
}

.lang-button i {
  font-size: 0.8rem;
}

.lang-button:hover {
  background: #312873;
}


























.incubator-logo {
    display: inline-block;
    padding: 4px;
    border-radius: 12px;
    border: 3px solid gold;
    animation: glow 2s infinite alternate;
}

.incubator-logo img {
    display: block;
    width: 200px;
    height: 100px;

    border-radius: 8px;
}

@keyframes glow {
    0% {
        box-shadow: 0 0 5px gold, 0 0 10px gold, 0 0 15px gold;
    }
    50% {
        box-shadow: 0 0 10px gold, 0 0 20px gold, 0 0 25px gold;
    }
    100% {
        box-shadow: 0 0 5px gold, 0 0 10px gold, 0 0 15px gold;
    }
}


.no-rtl {
  direction: ltr;
  unicode-bidi: isolate;
}











.hero-image-container {
  position: relative;
  width: 100%;
  height: 300px;
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
  background: rgba(47, 47, 47, 0.073);
}

.hero-image-container img {
  width: 30%;
  height: 80%;
  object-fit: cover;
  border-radius: 15px;
  filter: brightness(1.1) drop-shadow(0 0 20px rgba(255, 217, 0, 0.932));
  transition: transform 0.5s, filter 0.5s;
}

.hero-image-container:hover img {
  transform: scale(1.03);
  filter: brightness(1.2) drop-shadow(0 0 30px rgba(255, 217, 0, 0.786));
}

.gold-frame {
  position: absolute;
  top: -5%;
  left: -5%;
  width: 110%;
  height: 110%;
  border: 4px solid transparent;
  border-radius: 25px;
  background: conic-gradient(from 0deg, rgba(255, 217, 0, 0.373), rgba(255, 217, 0, 0.444), rgba(255, 217, 0, 0.801), rgba(255,215,0,0.6));
  animation: spin-frame 10s linear infinite;
  pointer-events: none;
  mix-blend-mode: lighten;
}

@keyframes spin-frame {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>




</head>


<body class="bg-white">

  <!-- HEADER -->

  <header class="header" id="header">

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
<nav class="nav">
  <ul class="nav-list">

    <!-- Platform -->
    <li class="nav-item dropdown">
      <button>{{ __('messages.platform') }} <i class="fas fa-chevron-down"></i></button>
      <ul class="dropdown-menu">
        <li class="card" onclick="showPage('faas')">
          <div class="card-icon" style="background:var(--secondary)"><i class="fas fa-cogs"></i></div>
          <div>
            <div class="card-title">{{ __('messages.faas') }}</div>
            <div class="card-desc">{{ __('messages.faas_desc') }}</div>
          </div>
        </li>
        <li class="card" onclick="showPage('marketplace')">
          <div class="card-icon" style="background:var(--primary)"><i class="fas fa-shopping-cart"></i></div>
          <div>
            <div class="card-title">{{ __('messages.marketplace') }}</div>
            <div class="card-desc">{{ __('messages.marketplace_desc') }}</div>
          </div>
        </li>
        <li class="card" onclick="showPage('compare')">
          <div class="card-icon" style="background:var(--secondary)"><i class="fas fa-balance-scale"></i></div>
          <div>
            <div class="card-title">{{ __('messages.compare_models') }}</div>
            <div class="card-desc">{{ __('messages.compare_models_desc') }}</div>
          </div>
        </li>
        <li class="card" onclick="showPage('technology')">
          <div class="card-icon" style="background:var(--primary)"><i class="fas fa-microchip"></i></div>
          <div>
            <div class="card-title">{{ __('messages.technology') }}</div>
            <div class="card-desc">{{ __('messages.technology_desc') }}</div>
          </div>
        </li>
      </ul>
    </li>

    <!-- Solutions -->
    <li class="nav-item dropdown">
      <button>{{ __('messages.solutions') }} <i class="fas fa-chevron-down"></i></button>
      <ul class="dropdown-menu">
        <li class="card" onclick="showPage('sol-fleets')"><div class="card-icon" style="background:var(--secondary)"><i class="fas fa-shuttle-van"></i></div><div><div class="card-title">{{ __('messages.existing_fleets') }}</div></div></li>
        <li class="card" onclick="showPage('sol-startups')"><div class="card-icon" style="background:var(--primary)"><i class="fas fa-rocket"></i></div><div><div class="card-title">{{ __('messages.startups') }}</div></div></li>
        <li class="card" onclick="showPage('sol-corp')"><div class="card-icon" style="background:var(--secondary)"><i class="fas fa-building"></i></div><div><div class="card-title">{{ __('messages.corp_mobility') }}</div></div></li>
        <li class="card" onclick="showPage('drivers')"><div class="card-icon" style="background:var(--primary)"><i class="fas fa-user-tie"></i></div><div><div class="card-title">{{ __('messages.driver_experience') }}</div></div></li>
      </ul>
    </li>

    <!-- Trust -->
    <li class="nav-item dropdown">
      <button>{{ __('messages.trust') }} <i class="fas fa-chevron-down"></i></button>
      <ul class="dropdown-menu">
        <li class="card" onclick="showPage('safety')"><div class="card-icon" style="background:var(--secondary)"><i class="fas fa-shield-alt"></i></div><div><div class="card-title">{{ __('messages.safety_security') }}</div></div></li>
        <li class="card" onclick="showPage('governance')"><div class="card-icon" style="background:var(--primary)"><i class="fas fa-gavel"></i></div><div><div class="card-title">{{ __('messages.marketplace_governance') }}</div></div></li>
        <li class="card" onclick="showPage('conduct')"><div class="card-icon" style="background:var(--secondary)"><i class="fas fa-balance-scale"></i></div><div><div class="card-title">{{ __('messages.code_of_conduct') }}</div></div></li>
        <li class="card" onclick="showPage('rollout')"><div class="card-icon" style="background:var(--primary)"><i class="fas fa-globe"></i></div><div><div class="card-title">{{ __('messages.global_rollout') }}</div></div></li>
      </ul>
    </li>

    <li class="nav-item" onclick="showPage('pricing')">{{ __('messages.pricing') }}</li>

    <!-- Resources -->
    <li class="nav-item dropdown">
      <button>{{ __('messages.resources') }} <i class="fas fa-chevron-down"></i></button>
      <ul class="dropdown-menu">
        <li class="card" onclick="showPage('academy')"><div class="card-icon" style="background:var(--secondary)"><i class="fas fa-graduation-cap"></i></div><div><div class="card-title">{{ __('messages.academy') }}</div></div></li>
        <li class="card" onclick="showPage('tech-faq')"><div class="card-icon" style="background:var(--primary)"><i class="fas fa-question-circle"></i></div><div><div class="card-title">{{ __('messages.tech_faq') }}</div></div></li>
        <li class="card" onclick="showPage('contact')"><div class="card-icon" style="background:var(--secondary)"><i class="fas fa-envelope"></i></div><div><div class="card-title">{{ __('messages.contact') }}</div></div></li>
      </ul>
    </li>

  </ul>
</nav>

  <!-- Actions -->
  <div class="header-actions">



<div class="lang-dropdown">
  <a href="{{ route('lang.switch', ['lang' => session('locale', 'en') === 'ar' ? 'en' : 'ar']) }}"
     class="lang-button">

    <i class="fas fa-globe"></i>
    {{ session('locale', 'en') === 'ar' ?   'AR' :'EN'}}

  </a>
</div>


<!-- Sign In -->
<a href="{{ route('login.office') }}" class="hidden md:flex items-center gap-2 text-sm font-semibold text-[#312873] px-5 py-2.5 rounded-full hover:bg-slate-100 transition">
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
        <polyline points="10 17 15 12 10 7"/>
        <line x1="15" y1="12" x2="3" y2="12"/>
    </svg>
    {{ __('messages.sign_in') }}
</a>

<!-- Launch Office -->
<button class="btn-primary" onclick="showPage('office-signup')">
    {{ __('messages.launch_office') }}
</button>

  </div>

</header>





  <main class="pt-20">

    <!-- 1) HOMEPAGE -->
<section id="page-home" class="page-content active relative overflow-hidden">
  <div class="hero-background">
    <div class="hero-glow primary"></div>
    <div class="hero-glow secondary"></div>
    <div class="hero-glow soft"></div>
  </div>


<div class="max-w-7xl mx-auto px-6 pt-24 pb-28 relative z-10">
  <div class="grid lg:grid-cols-2 gap-16 items-center">

    <div>
      <span class="section-tag">{{ __('messages.hero_tag') }}</span>
      <h1 class="hero-title mb-6">
        {{ __('messages.hero_main') }}<br>
        <span class="hero-highlight">{{ __('messages.hero_highlight') }}</span>
      </h1>
      <p class="text-lg text-gray-600 mb-8 leading-relaxed">
        {{ __('messages.hero_desc') }}
      </p>

      <div class="flex gap-4 mb-6">
        <button onclick="showPage('office-signup')" class="btn-primary">
          <i class="fas fa-rocket"></i> {{ __('messages.hero_btn_launch') }}
        </button>
        <button onclick="showPage('compare')" class="btn-secondary">
          <i class="fas fa-chart-bar"></i> {{ __('messages.hero_btn_compare') }}
        </button>
      </div>

      <!-- Mini Benefits -->
      <div class="hero-benefits">
        <span>{{ __('messages.benefit_marketplace') }}</span>
        <span>{{ __('messages.benefit_dashboard') }}</span>
        <span>{{ __('messages.benefit_driver_app') }}</span>
        <span>{{ __('messages.benefit_safety') }}</span>
        <span>{{ __('messages.benefit_governance') }}</span>
      </div>

      <!-- Platform Badges -->
      <div class="platforms mt-4">
        <div class="platform"><i class="fas fa-user"></i> {{ __('messages.platform_passenger') }}</div>
        <div class="platform"><i class="fas fa-building"></i> {{ __('messages.platform_office') }}</div>
        <div class="platform"><i class="fas fa-car"></i> {{ __('messages.platform_driver') }}</div>
      </div>

    </div>

    <!-- Marketplace Card -->
    <div class="market-card">
      <h3 class="text-xl font-extrabold mb-4">{{ __('messages.market_title') }}</h3>

      <div class="market-item">
        <span>{{ __('messages.fleet_local') }} <i class="fas fa-star star"></i><i class="fas fa-star star"></i><i class="fas fa-star star"></i><i class="fas fa-star star"></i><i class="fas fa-star-half-alt star"></i></span>
        <span style="color:#F29C0B">$12.50</span>
      </div>
      <div class="market-item">
        <span>{{ __('messages.fleet_city') }} <i class="fas fa-star star"></i><i class="fas fa-star star"></i><i class="fas fa-star star"></i><i class="fas fa-star star"></i><i class="fas fa-star-half-alt star"></i></span>
        <span style="color:#F29C0B">$14.00</span>
      </div>
      <div class="market-item">
        <span>{{ __('messages.fleet_premium') }} <i class="fas fa-star star"></i><i class="fas fa-star star"></i><i class="fas fa-star star"></i><i class="fas fa-star star"></i><i class="fas fa-star star"></i></span>
        <span style="color:#F29C0B">$18.00</span>
      </div>

      <!-- Live Activity -->
      <div class="live-activity mt-4">
        <div class="live-dot"><i class="fas fa-car-side" style="color:#4ade80;"></i> {{ __('messages.live_drivers', ['count' => 12]) }}</div>
        <div class="live-dot"><i class="fas fa-building" style="color:#4ade80;"></i> {{ __('messages.live_offices', ['count' => 4]) }}</div>
        <div class="live-dot"><i class="fas fa-user-check" style="color:#4ade80;"></i> {{ __('messages.live_rides', ['count' => 3]) }}</div>
      </div>

    </div>

  </div>
</div>







<div class="cards-wrapper">
  <div class="cards-grid">

    <div class="card-pro primary">
      <div class="icon-wrap">
        <i class="fa-solid fa-layer-group"></i>
      </div>

      <div class="card-content">
        <h4>{{ __('messages.marketplace_title') }}</h4>
        <p>{{ __('messages.marketplace_desc') }}</p>
      </div>

      <div class="card-tags">
        <span>{{ __('messages.shared_demand') }}</span>
        <span>{{ __('messages.visibility') }}</span>
      </div>
    </div>

    <div class="card-pro blue">
      <div class="icon-wrap">
        <i class="fa-solid fa-gauge-high"></i>
      </div>

      <div class="card-content">
        <h4>{{ __('messages.control_title') }}</h4>
        <p>{{ __('messages.control_desc') }}</p>
      </div>

      <div class="card-tags">
        <span>{{ __('messages.pricing') }}</span>
        <span>{{ __('messages.drivers') }}</span>
      </div>
    </div>

    <div class="card-pro purple">
      <div class="icon-wrap">
        <i class="fa-solid fa-shield-halved"></i>
      </div>

      <div class="card-content">
        <h4>{{ __('messages.trust_title') }}</h4>
        <p>{{ __('messages.trust_desc') }}</p>
      </div>

      <div class="card-tags">
        <span>{{ __('messages.security') }}</span>
        <span>{{ __('messages.ratings') }}</span>
      </div>
    </div>

    <div class="card-pro green">
      <div class="icon-wrap">
        <i class="fa-solid fa-chart-line"></i>
      </div>

      <div class="card-content">
        <h4>{{ __('messages.growth_title') }}</h4>
        <p>{{ __('messages.growth_desc') }}</p>
      </div>

      <div class="card-tags">
        <span>{{ __('messages.analytics') }}</span>
        <span>{{ __('messages.growth') }}</span>
      </div>
    </div>

  </div>
</div>





<div class="how-section">

  <div class="mt-16 how-box">
    <div class="max-w-5xl mx-auto">

      <div class="flex items-end justify-between gap-6 flex-wrap mb-12">
        <div>
          <span class="section-tag">{{ __('messages.how_tag') }}</span>
          <h3 class="text-3xl font-extrabold text-[#312873]">
            {{ __('messages.how_title') }}
          </h3>
        </div>

        <div class="flex gap-3">
          <button onclick="showPage('marketplace')" class="btn-secondary px-6 py-3">
            {{ __('messages.how_btn_marketplace') }}
          </button>
          <button onclick="showPage('pricing')" class="btn-primary px-6 py-3">
            {{ __('messages.how_btn_pricing') }}
          </button>
        </div>
      </div>

      <div class="grid md:grid-cols-5 gap-6 steps-grid">

        <div class="glass-card step-card">
          <div class="step-icon orange">
            <i class="fa-solid fa-user-check"></i>
          </div>
          <p class="step-title">{{ __('messages.step_apply') }}</p>
          <p class="step-desc">{{ __('messages.step_apply_desc') }}</p>
        </div>

        <div class="glass-card step-card">
          <div class="step-icon blue">
            <i class="fa-solid fa-building"></i>
          </div>
          <p class="step-title">{{ __('messages.step_launch') }}</p>
          <p class="step-desc">{{ __('messages.step_launch_desc') }}</p>
        </div>

        <div class="glass-card step-card">
          <div class="step-icon green">
            <i class="fa-solid fa-id-badge"></i>
          </div>
          <p class="step-title">{{ __('messages.step_onboard') }}</p>
          <p class="step-desc">{{ __('messages.step_onboard_desc') }}</p>
        </div>

        <div class="glass-card step-card">
          <div class="step-icon purple">
            <i class="fa-solid fa-rocket"></i>
          </div>
          <p class="step-title">{{ __('messages.step_live') }}</p>
          <p class="step-desc">{{ __('messages.step_live_desc') }}</p>
        </div>

        <div class="glass-card step-card">
          <div class="step-icon gray">
            <i class="fa-solid fa-chart-line"></i>
          </div>
          <p class="step-title">{{ __('messages.step_grow') }}</p>
          <p class="step-desc">{{ __('messages.step_grow_desc') }}</p>
        </div>

      </div>

    </div>
  </div>

</div>
<div class="hero-image-container">
  <img src="storage/qatar111.jpg">
  <div class="gold-frame"></div>
</div>



</section>



    <!-- 2) FLEET-AS-A-SERVICE -->
<section id="page-faas" class="page-content px-6 py-24 bg-gray-50">
  <div class="max-w-4xl mx-auto">
    <span class="section-tag">{{ __('messages.category_page') }}</span>
    <h2 class="text-5xl font-extrabold text-[#312873] mb-8">{{ __('messages.faas_title') }}</h2>
    <div class="prose prose-lg text-gray-600 mb-12 max-w-none">
      <p class="text-xl leading-relaxed">
        {{ __('messages.faas_desc') }}
      </p>

      <div class="grid md:grid-cols-2 gap-10 mt-12">
        <div class="glass-card p-8">
          <h4 class="font-extrabold text-xl text-[#312873] mb-3">{{ __('messages.old_problem_title') }}</h4>
          <p class="text-sm">
            {{ __('messages.old_problem_desc') }}
          </p>
        </div>
        <div class="glass-card p-8">
          <h4 class="font-extrabold text-xl text-[#312873] mb-3">{{ __('messages.fleetos_model_title') }}</h4>
          <p class="text-sm">
            {{ __('messages.fleetos_model_desc') }}
          </p>
        </div>
      </div>

      <div class="glass-card p-8 mt-10">
        <h4 class="font-extrabold text-xl text-[#312873] mb-3">{{ __('messages.multi_tenant_title') }}</h4>
        <ul class="list-disc pl-6 text-sm space-y-2">
          <li>{{ __('messages.multi_tenant_li1') }}</li>
          <li>{{ __('messages.multi_tenant_li2') }}</li>
          <li>{{ __('messages.multi_tenant_li3') }}</li>
        </ul>
      </div>

      <div class="grid md:grid-cols-2 gap-8 mt-10">
        <div class="glass-card p-8">
          <h4 class="font-extrabold text-xl text-[#312873] mb-3">{{ __('messages.offices_own_title') }}</h4>
          <p class="text-sm">{{ __('messages.offices_own_desc') }}</p>
        </div>
        <div class="glass-card p-8">
          <h4 class="font-extrabold text-xl text-[#312873] mb-3">{{ __('messages.fleetos_provides_title') }}</h4>
          <p class="text-sm">{{ __('messages.fleetos_provides_desc') }}</p>
        </div>
      </div>
    </div>

    <div class="flex gap-4 flex-wrap">
      <button onclick="showPage('compare')" class="btn-secondary px-8 py-3">{{ __('messages.compare_models') }}</button>
      <button onclick="showPage('office-signup')" class="btn-primary px-8 py-3">{{ __('messages.launch_office') }}</button>
    </div>
  </div>
</section>

    <!-- 3) MARKETPLACE -->
<section id="page-marketplace" class="page-content px-6 py-24">
  <div class="max-w-6xl mx-auto">
    <span class="section-tag">{{ __('messages.marketplace_tag') }}</span>
    <h2 class="text-5xl font-extrabold text-[#312873] mb-6">{{ __('messages.marketplace_title') }}</h2>
    <p class="text-xl text-gray-500 mb-14 max-w-3xl">
      {{ __('messages.marketplace_desc') }}
    </p>

    <div class="grid md:grid-cols-3 gap-8 mb-16">
      <div class="glass-card p-10">
        <div class="w-12 h-12 bg-orange-100 rounded-xl mb-6 flex items-center justify-center text-[#F29C0B] font-black">1</div>
        <h4 class="font-extrabold mb-3">{{ __('messages.step1_title') }}</h4>
        <p class="text-sm text-gray-500">{{ __('messages.step1_desc') }}</p>
      </div>
      <div class="glass-card p-10">
        <div class="w-12 h-12 bg-blue-100 rounded-xl mb-6 flex items-center justify-center text-[#312873] font-black">2</div>
        <h4 class="font-extrabold mb-3">{{ __('messages.step2_title') }}</h4>
        <p class="text-sm text-gray-500">{{ __('messages.step2_desc') }}</p>
      </div>
      <div class="glass-card p-10">
        <div class="w-12 h-12 bg-green-100 rounded-xl mb-6 flex items-center justify-center text-green-700 font-black">3</div>
        <h4 class="font-extrabold mb-3">{{ __('messages.step3_title') }}</h4>
        <p class="text-sm text-gray-500">{{ __('messages.step3_desc') }}</p>
      </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-8 mb-12">
      <div class="glass-card p-10">
        <h4 class="text-2xl font-extrabold text-[#312873] mb-4">{{ __('messages.selection_economics_title') }}</h4>
        <ul class="text-sm text-gray-500 space-y-2">
          <li>• {{ __('messages.selection_li1') }}</li>
          <li>• {{ __('messages.selection_li2') }}</li>
          <li>• {{ __('messages.selection_li3') }}</li>
        </ul>
      </div>
      <div class="glass-card p-10 bg-gray-50">
        <h4 class="text-2xl font-extrabold text-[#312873] mb-4">{{ __('messages.quality_controls_title') }}</h4>
        <ul class="text-sm text-gray-600 space-y-2">
          <li>• {{ __('messages.quality_li1') }}</li>
          <li>• {{ __('messages.quality_li2') }}</li>
          <li>• {{ __('messages.quality_li3') }}</li>
          <li>• {{ __('messages.quality_li4') }}</li>
          <li>• {{ __('messages.quality_li5') }}</li>
        </ul>
      </div>
    </div>

    <div class="bg-[#312873] rounded-[40px] p-12 text-white text-center">
      <h3 class="text-2xl font-extrabold mb-3 text-[#F29C0B]">{{ __('messages.antidote_title') }}</h3>
      <p class="opacity-80 mb-8 max-w-3xl mx-auto">
        {{ __('messages.antidote_desc') }}
      </p>
      <div class="flex gap-4 justify-center flex-wrap">
        <button onclick="showPage('office-signup')" class="btn-primary px-8 py-3">{{ __('messages.launch_office') }}</button>
        <button onclick="showPage('governance')" class="btn-secondary px-8 py-3">{{ __('messages.explore_governance') }}</button>
      </div>
    </div>
  </div>
</section>

    <!-- 4) COMPARE -->
<section id="page-compare" class="page-content px-6 py-24 bg-gray-50">
  <div class="max-w-6xl mx-auto">
    <span class="section-tag">{{ __('messages.compare_tag') }}</span>
    <h2 class="text-5xl font-extrabold text-[#312873] mb-6 text-center">{{ __('messages.compare_title') }}</h2>
    <p class="text-xl text-gray-500 mb-14 text-center max-w-4xl mx-auto">
      {{ __('messages.compare_desc') }}
    </p>

    <div class="overflow-x-auto">
      <table class="w-full bg-white rounded-3xl overflow-hidden shadow-sm">
        <thead>
          <tr class="bg-gray-100 text-left">
            <th class="p-6 text-xs font-black uppercase tracking-widest opacity-40">{{ __('messages.feature') }}</th>
            <th class="p-6 text-sm font-extrabold text-[#312873]">{{ __('messages.traditional_dispatch') }}</th>
            <th class="p-6 text-sm font-extrabold text-[#312873]">{{ __('messages.ride_hailing') }}</th>
            <th class="p-6 text-sm font-black text-[#F29C0B]">{{ __('messages.fleetos') }}</th>
          </tr>
        </thead>
        <tbody class="text-sm">
          <tr class="border-b border-gray-50">
            <td class="p-6 font-extrabold">{{ __('messages.ownership') }}</td>
            <td class="p-6">{{ __('messages.ownership_td1') }}</td>
            <td class="p-6">{{ __('messages.ownership_td2') }}</td>
            <td class="p-6 text-[#F29C0B] font-black">{{ __('messages.ownership_td3') }}</td>
          </tr>
          <tr class="border-b border-gray-50">
            <td class="p-6 font-extrabold">{{ __('messages.cost_structure') }}</td>
            <td class="p-6">{{ __('messages.cost_td1') }}</td>
            <td class="p-6">{{ __('messages.cost_td2') }}</td>
            <td class="p-6 text-[#F29C0B] font-black">{{ __('messages.cost_td3') }}</td>
          </tr>
          <tr class="border-b border-gray-50">
            <td class="p-6 font-extrabold">{{ __('messages.marketplace') }}</td>
            <td class="p-6">{{ __('messages.marketplace_td1') }}</td>
            <td class="p-6">{{ __('messages.marketplace_td2') }}</td>
            <td class="p-6 text-[#F29C0B] font-black">{{ __('messages.marketplace_td3') }}</td>
          </tr>
          <tr class="border-b border-gray-50">
            <td class="p-6 font-extrabold">{{ __('messages.competition') }}</td>
            <td class="p-6">{{ __('messages.competition_td1') }}</td>
            <td class="p-6">{{ __('messages.competition_td2') }}</td>
            <td class="p-6 text-[#F29C0B] font-black">{{ __('messages.competition_td3') }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="mt-12 flex justify-center gap-4 flex-wrap">
      <button onclick="showPage('pricing')" class="btn-primary px-8 py-3">{{ __('messages.view_pricing') }}</button>
      <button onclick="showPage('office-signup')" class="btn-secondary px-8 py-3">{{ __('messages.launch_office') }}</button>
    </div>
  </div>
</section>

    <!-- 5) SOLUTIONS: EXISTING FLEETS -->
<section id="page-sol-fleets" class="page-content px-6 py-24">
  <div class="max-w-5xl mx-auto">
    <span class="section-tag">{{ __('messages.solutions_tag') }}</span>
    <h2 class="text-5xl font-extrabold text-[#312873] mb-6">{{ __('messages.solutions_title') }}</h2>
    <p class="text-xl text-gray-500 mb-12">{{ __('messages.solutions_desc') }}</p>

    <div class="grid md:grid-cols-2 gap-8 mb-10">
      <div class="glass-card p-10">
        <h4 class="font-extrabold text-2xl text-[#312873] mb-4">{{ __('messages.replaces_title') }}</h4>
        <ul class="text-sm text-gray-600 space-y-2">
          <li>• {{ __('messages.replaces_li1') }}</li>
          <li>• {{ __('messages.replaces_li2') }}</li>
          <li>• {{ __('messages.replaces_li3') }}</li>
        </ul>
      </div>
      <div class="glass-card p-10 bg-gray-50">
        <h4 class="font-extrabold text-2xl text-[#312873] mb-4">{{ __('messages.adds_title') }}</h4>
        <ul class="text-sm text-gray-700 space-y-2">
          <li>• {{ __('messages.adds_li1') }}</li>
          <li>• {{ __('messages.adds_li2') }}</li>
          <li>• {{ __('messages.adds_li3') }}</li>
          <li>• {{ __('messages.adds_li4') }}</li>
        </ul>
      </div>
    </div>

    <div class="p-10 border rounded-[40px] bg-blue-50 border-blue-100 mb-12">
      <h4 class="font-extrabold text-blue-900 mb-3 text-2xl">{{ __('messages.migration_title') }}</h4>
      <p class="text-blue-800 mb-8 opacity-90">
        {{ __('messages.migration_desc') }}
      </p>
      <div class="flex gap-4 flex-wrap">
        <button onclick="showPage('contact', 'migration-audit')" class="btn-primary px-8 py-3">{{ __('messages.schedule_audit') }}</button>
        <button onclick="showPage('contact', 'demo')" class="btn-secondary px-8 py-3">{{ __('messages.request_demo') }}</button>
      </div>
    </div>
  </div>
</section>

    <!-- 6) SOLUTIONS: STARTUPS -->
<section id="page-sol-startups" class="page-content px-6 py-24 bg-gray-50">
  <div class="max-w-5xl mx-auto">
    <span class="section-tag">{{ __('messages.solutions_tag') }}</span>
    <h2 class="text-5xl font-extrabold text-[#312873] mb-6">{{ __('messages.startup_title') }}</h2>
    <p class="text-xl text-gray-500 mb-12">{{ __('messages.startup_desc') }}</p>

    <div class="grid md:grid-cols-2 gap-8 mb-12">
      <div class="glass-card p-10">
        <h4 class="font-extrabold text-2xl text-[#312873] mb-4">{{ __('messages.entrepreneur_title') }}</h4>
        <ul class="text-sm text-gray-600 space-y-2">
          <li>• {{ __('messages.entrepreneur_li1') }}</li>
          <li>• {{ __('messages.entrepreneur_li2') }}</li>
          <li>• {{ __('messages.entrepreneur_li3') }}</li>
          <li>• {{ __('messages.entrepreneur_li4') }}</li>
          <li>• {{ __('messages.entrepreneur_li5') }}</li>
        </ul>
      </div>
      <div class="glass-card p-10 bg-white">
        <h4 class="font-extrabold text-2xl text-[#312873] mb-4">{{ __('messages.approval_title') }}</h4>
        <p class="text-sm text-gray-600 leading-relaxed">
          {{ __('messages.approval_desc') }}
        </p>
        <div class="mt-6 flex gap-3 flex-wrap">
          <button onclick="showPage('rollout')" class="btn-secondary px-6 py-3">{{ __('messages.see_rollout') }}</button>
          <button onclick="showPage('office-signup')" class="btn-primary px-6 py-3">{{ __('messages.launch_office') }}</button>
        </div>
      </div>
    </div>

    <div class="glass-card p-10 bg-[#312873] text-white">
      <h4 class="text-2xl font-extrabold mb-3 text-[#F29C0B]">{{ __('messages.start_smart_title') }}</h4>
      <p class="opacity-85 mb-8 max-w-3xl">
        {{ __('messages.start_smart_desc') }}
      </p>
      <div class="flex gap-4 flex-wrap">
        <button onclick="showPage('academy')" class="btn-secondary px-8 py-3">{{ __('messages.explore_academy') }}</button>
        <button onclick="showPage('office-signup')" class="btn-primary px-8 py-3">{{ __('messages.apply_now') }}</button>
      </div>
    </div>
  </div>
</section>

    <!-- 7) SOLUTIONS: CORPORATE -->
<section id="page-sol-corp" class="page-content px-6 py-24">
  <div class="max-w-5xl mx-auto">
    <span class="section-tag">{{ __('messages.enterprise_tag') }}</span>
    <h2 class="text-5xl font-extrabold text-[#312873] mb-6">{{ __('messages.enterprise_title') }}</h2>
    <p class="text-xl text-gray-500 mb-12">
      {{ __('messages.enterprise_desc') }}
    </p>

    <div class="grid md:grid-cols-2 gap-8 mb-12">
      <div class="glass-card p-10">
        <h4 class="font-extrabold text-2xl text-[#312873] mb-4">{{ __('messages.billing_title') }}</h4>
        <ul class="text-sm text-gray-600 space-y-2">
          <li>• {{ __('messages.billing_li1') }}</li>
          <li>• {{ __('messages.billing_li2') }}</li>
          <li>• {{ __('messages.billing_li3') }}</li>
        </ul>
      </div>
      <div class="glass-card p-10 bg-gray-50">
        <h4 class="font-extrabold text-2xl text-[#312873] mb-4">{{ __('messages.reporting_title') }}</h4>
        <ul class="text-sm text-gray-700 space-y-2">
          <li>• {{ __('messages.reporting_li1') }}</li>
          <li>• {{ __('messages.reporting_li2') }}</li>
          <li>• {{ __('messages.reporting_li3') }}</li>
        </ul>
      </div>
    </div>

    <div class="glass-card p-10">
      <h4 class="font-extrabold text-2xl text-[#312873] mb-4">{{ __('messages.use_cases_title') }}</h4>
      <div class="grid md:grid-cols-2 gap-4 text-sm text-gray-600">
        <div>• {{ __('messages.use_case1') }}</div>
        <div>• {{ __('messages.use_case2') }}</div>
        <div>• {{ __('messages.use_case3') }}</div>
        <div>• {{ __('messages.use_case4') }}</div>
      </div>
    </div>

    <div class="flex gap-4 flex-wrap mt-10">
      <button onclick="showPage('contact', 'corporate')" class="btn-primary px-8 py-3">{{ __('messages.request_brief') }}</button>
      <button onclick="showPage('contact', 'demo')" class="btn-secondary px-8 py-3">{{ __('messages.schedule_demo') }}</button>
    </div>
  </div>
</section>

    <!-- 8) TECHNOLOGY -->
<section id="page-technology" class="page-content px-6 py-24 bg-gray-50">
  <div class="max-w-5xl mx-auto">
    <span class="section-tag">{{ __('messages.tech_tag') }}</span>
    <h2 class="text-5xl font-extrabold text-[#312873] mb-6">{{ __('messages.tech_title') }}</h2>
    <p class="text-xl text-gray-500 mb-14 max-w-4xl">
      {{ __('messages.tech_desc') }}
    </p>

    <div class="grid md:grid-cols-2 gap-10 mb-12">
      <div class="glass-card p-10">
        <h4 class="text-xs font-black uppercase text-[#F29C0B] tracking-widest mb-6">{{ __('messages.available_now') }}</h4>
        <ul class="space-y-3 text-sm font-extrabold text-[#312873]">
          <li class="flex items-center gap-3"><div class="w-1.5 h-1.5 bg-[#312873] rounded-full"></div> {{ __('messages.tech_li1') }}</li>
          <li class="flex items-center gap-3"><div class="w-1.5 h-1.5 bg-[#312873] rounded-full"></div> {{ __('messages.tech_li2') }}</li>
          <li class="flex items-center gap-3"><div class="w-1.5 h-1.5 bg-[#312873] rounded-full"></div> {{ __('messages.tech_li3') }}</li>
          <li class="flex items-center gap-3"><div class="w-1.5 h-1.5 bg-[#312873] rounded-full"></div> {{ __('messages.tech_li4') }}</li>
          <li class="flex items-center gap-3"><div class="w-1.5 h-1.5 bg-[#312873] rounded-full"></div> {{ __('messages.tech_li5') }}</li>
          <li class="flex items-center gap-3"><div class="w-1.5 h-1.5 bg-[#312873] rounded-full"></div> {{ __('messages.tech_li6') }}</li>
        </ul>
      </div>

      <div class="glass-card p-10 bg-white">
        <h4 class="text-xs font-black uppercase text-gray-400 tracking-widest mb-6">{{ __('messages.roadmap_title') }}</h4>
        <p class="text-sm text-gray-500 mb-6">{{ __('messages.roadmap_desc') }}</p>
        <ul class="space-y-3 text-sm font-extrabold text-gray-700">
          <li class="flex items-center gap-3"><div class="w-1.5 h-1.5 bg-gray-300 rounded-full"></div> {{ __('messages.roadmap_li1') }}</li>
          <li class="flex items-center gap-3"><div class="w-1.5 h-1.5 bg-gray-300 rounded-full"></div> {{ __('messages.roadmap_li2') }}</li>
          <li class="flex items-center gap-3"><div class="w-1.5 h-1.5 bg-gray-300 rounded-full"></div> {{ __('messages.roadmap_li3') }}</li>
          <li class="flex items-center gap-3"><div class="w-1.5 h-1.5 bg-gray-300 rounded-full"></div> {{ __('messages.roadmap_li4') }}</li>
        </ul>
      </div>
    </div>

    <div class="text-center">
      <button onclick="showPage('tech-faq')" class="btn-primary px-8 py-3">{{ __('messages.view_faq') }}</button>
    </div>
  </div>
</section>

    <!-- 9) SAFETY -->
<section id="page-safety" class="page-content px-6 py-24">
  <div class="max-w-5xl mx-auto">
    <span class="section-tag">{{ __('messages.safety_tag') }}</span>
    <h2 class="text-5xl font-extrabold text-[#312873] mb-6">{{ __('messages.safety_title') }}</h2>
    <p class="text-xl text-gray-500 mb-12 max-w-4xl">
      {{ __('messages.safety_desc') }}
    </p>

    <div class="grid md:grid-cols-3 gap-8 mb-12">
      <div class="glass-card p-10">
        <h4 class="font-extrabold text-xl text-[#312873] mb-3">{{ __('messages.rider_safety_title') }}</h4>
        <ul class="text-sm text-gray-600 space-y-2">
          <li>• {{ __('messages.rider_safety_li1') }}</li>
          <li>• {{ __('messages.rider_safety_li2') }}</li>
          <li>• {{ __('messages.rider_safety_li3') }}</li>
        </ul>
      </div>

      <div class="glass-card p-10 bg-gray-50">
        <h4 class="font-extrabold text-xl text-[#312873] mb-3">{{ __('messages.verification_title') }}</h4>
        <ul class="text-sm text-gray-700 space-y-2">
          <li>• {{ __('messages.verification_li1') }}</li>
          <li>• {{ __('messages.verification_li2') }}</li>
          <li>• {{ __('messages.verification_li3') }}</li>
        </ul>
      </div>

      <div class="glass-card p-10">
        <h4 class="font-extrabold text-xl text-[#312873] mb-3">{{ __('messages.data_protection_title') }}</h4>
        <ul class="text-sm text-gray-600 space-y-2">
          <li>• {{ __('messages.data_li1') }}</li>
          <li>• {{ __('messages.data_li2') }}</li>
          <li>• {{ __('messages.data_li3') }}</li>
          <li>• {{ __('messages.data_li4') }}</li>
        </ul>
      </div>
    </div>

    <div class="flex gap-4 flex-wrap">
      <button onclick="showPage('governance')" class="btn-primary px-8 py-3">{{ __('messages.explore_governance') }}</button>
      <button onclick="showPage('office-signup')" class="btn-secondary px-8 py-3">{{ __('messages.launch_office') }}</button>
    </div>
  </div>
</section>


<!-- DRIVER EXPERIENCE -->
<section id="page-drivers" class="page-content px-6 py-24 bg-gray-50">
  <div class="max-w-5xl mx-auto">
    <span class="section-tag">{{ __('messages.drivers_tag') }}</span>
    <h2 class="text-5xl font-extrabold text-[#312873] mb-6">{{ __('messages.drivers_title') }}</h2>
    <p class="text-xl text-gray-500 mb-12 max-w-4xl">
      {{ __('messages.drivers_desc') }}
    </p>

    <div class="grid md:grid-cols-3 gap-8 mb-12">
      <div class="glass-card p-10">
        <h4 class="font-extrabold text-xl text-[#312873] mb-3">{{ __('messages.earnings_title') }}</h4>
        <ul class="text-sm text-gray-600 space-y-2">
          <li>• {{ __('messages.earnings_li1') }}</li>
          <li>• {{ __('messages.earnings_li2') }}</li>
          <li>• {{ __('messages.earnings_li3') }}</li>
        </ul>
      </div>

      <div class="glass-card p-10 bg-white">
        <h4 class="font-extrabold text-xl text-[#312873] mb-3">{{ __('messages.trip_tools_title') }}</h4>
        <ul class="text-sm text-gray-600 space-y-2">
          <li>• {{ __('messages.trip_tools_li1') }}</li>
          <li>• {{ __('messages.trip_tools_li2') }}</li>
          <li>• {{ __('messages.trip_tools_li3') }}</li>
        </ul>
      </div>

      <div class="glass-card p-10">
        <h4 class="font-extrabold text-xl text-[#312873] mb-3">{{ __('messages.driver_roadmap_title') }}</h4>
        <p class="text-sm text-gray-600">
          {{ __('messages.driver_roadmap_desc') }}
        </p>
      </div>
    </div>

    <div class="glass-card p-10 bg-[#312873] text-white">
      <h4 class="text-2xl font-extrabold mb-3 text-[#F29C0B]">{{ __('messages.recruiting_title') }}</h4>
      <p class="opacity-85 mb-8 max-w-3xl">
        {{ __('messages.recruiting_desc') }}
      </p>
      <div class="flex gap-4 flex-wrap">
        <button onclick="showPage('office-signup')" class="btn-primary px-8 py-3">{{ __('messages.launch_office') }}</button>
        <button onclick="showPage('academy')" class="btn-secondary px-8 py-3">{{ __('messages.explore_academy') }}</button>
      </div>
    </div>
  </div>
</section>

<!-- ROLLOUT -->
<section id="page-rollout" class="page-content px-6 py-24">
  <div class="max-w-5xl mx-auto">
    <span class="section-tag">{{ __('messages.rollout_tag') }}</span>
    <h2 class="text-5xl font-extrabold text-[#312873] mb-6">{{ __('messages.rollout_title') }}</h2>
    <p class="text-xl text-gray-500 mb-12 max-w-4xl">
      {{ __('messages.rollout_desc') }}
    </p>

    <div class="grid md:grid-cols-2 gap-8 mb-10">
      <div class="glass-card p-10">
        <h4 class="font-extrabold text-2xl text-[#312873] mb-4">{{ __('messages.compliance_title') }}</h4>
        <ul class="text-sm text-gray-600 space-y-2">
          <li>• {{ __('messages.compliance_li1') }}</li>
          <li>• {{ __('messages.compliance_li2') }}</li>
          <li>• {{ __('messages.compliance_li3') }}</li>
        </ul>
      </div>
      <div class="glass-card p-10 bg-gray-50">
        <h4 class="font-extrabold text-2xl text-[#312873] mb-4">{{ __('messages.launch_process_title') }}</h4>
        <ul class="text-sm text-gray-700 space-y-2">
          <li>• {{ __('messages.launch_li1') }}</li>
          <li>• {{ __('messages.launch_li2') }}</li>
          <li>• {{ __('messages.launch_li3') }}</li>
          <li>• {{ __('messages.launch_li4') }}</li>
        </ul>
      </div>
    </div>

    <div class="grid md:grid-cols-3 gap-8">
      <div class="glass-card p-10 border-green-200 bg-green-50">
        <h4 class="font-extrabold text-green-900 mb-3 text-xl">{{ __('messages.active') }}</h4>
        <p class="text-sm text-green-700 mb-8">{{ __('messages.active_desc') }}</p>
        <button onclick="showPage('office-signup')" class="btn-primary w-full py-3">{{ __('messages.check_availability') }}</button>
      </div>
      <div class="glass-card p-10 border-orange-200 bg-orange-50">
        <h4 class="font-extrabold text-orange-900 mb-3 text-xl">{{ __('messages.pending') }}</h4>
        <p class="text-sm text-orange-700 mb-8">{{ __('messages.pending_desc') }}</p>
        <button onclick="showPage('contact', 'waitlist')" class="btn-secondary w-full py-3">{{ __('messages.join_waitlist') }}</button>
      </div>
      <div class="glass-card p-10 border-gray-200 bg-gray-50">
        <h4 class="font-extrabold text-gray-900 mb-3 text-xl">{{ __('messages.planned') }}</h4>
        <p class="text-sm text-gray-700 mb-8">{{ __('messages.planned_desc') }}</p>
        <button onclick="showPage('contact', 'waitlist')" class="btn-secondary w-full py-3">{{ __('messages.register_interest') }}</button>
      </div>
    </div>
  </div>
</section>


<!-- 12) PRICING -->
<section id="page-pricing" class="page-content px-6 py-24 text-center bg-gray-50">
  <div class="max-w-5xl mx-auto">
    <span class="section-tag">{{ __('messages.pricing_tag') }}</span>
    <h2 class="text-6xl font-extrabold text-[#312873] mb-6">{{ __('messages.pricing_title') }}</h2>
    <p class="text-xl text-gray-500 mb-12">{{ __('messages.pricing_desc') }}</p>

    <div class="grid lg:grid-cols-2 gap-8 mb-12 text-left">
      <div class="glass-card p-10">
        <h4 class="text-2xl font-extrabold text-[#312873] mb-4">{{ __('messages.basic_plan') }}</h4>
        <ul class="text-sm text-gray-600 space-y-2">
          <li>• {{ __('messages.plan_li1') }}</li>
          <li>• {{ __('messages.plan_li2') }}</li>
          <li>• {{ __('messages.plan_li3') }}</li>
          <li>• {{ __('messages.plan_li4') }}</li>
        </ul>
        <div class="mt-6 p-4 rounded-2xl bg-orange-50 border border-orange-100 text-sm font-extrabold text-[#312873]">
          {{ __('messages.plan_note') }}
        </div>
      </div>

      <div class="glass-card p-10 bg-white">
        <h4 class="text-2xl font-extrabold text-[#312873] mb-4">{{ __('messages.included_title') }}</h4>
        <div class="grid md:grid-cols-2 gap-4 text-sm">
          <div class="p-4 rounded-2xl bg-gray-50">
            <p class="font-extrabold text-[#312873] mb-2">{{ __('messages.included') }}</p>
            <ul class="text-gray-700 space-y-1">
              <li>• {{ __('messages.inc1') }}</li>
              <li>• {{ __('messages.inc2') }}</li>
              <li>• {{ __('messages.inc3') }}</li>
              <li>• {{ __('messages.inc4') }}</li>
            </ul>
          </div>
          <div class="p-4 rounded-2xl bg-gray-50">
            <p class="font-extrabold text-[#312873] mb-2">{{ __('messages.not_included') }}</p>
            <ul class="text-gray-700 space-y-1">
              <li>• {{ __('messages.not1') }}</li>
              <li>• {{ __('messages.not2') }}</li>
              <li>• {{ __('messages.not3') }}</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <div class="glass-card p-12 max-w-2xl mx-auto mb-10 text-left">
      <h4 class="text-[10px] font-black text-gray-400 tracking-widest uppercase mb-8">{{ __('messages.calc_title') }}</h4>
      <div class="space-y-6">
        <div>
          <label class="text-xs font-extrabold mb-2 block">{{ __('messages.calc_rides') }}</label>
          <input type="number" id="calc-rides" value="1000" oninput="runCalc()">
        </div>
        <div>
          <label class="text-xs font-extrabold mb-2 block">{{ __('messages.calc_fare') }}</label>
          <input type="number" id="calc-fare" value="15" oninput="runCalc()">
        </div>
        <div class="pt-8 border-t border-gray-100">
          <div class="flex justify-between items-center mb-2">
            <span class="text-sm font-extrabold text-gray-400">{{ __('messages.calc_fee') }}</span>
            <span id="res-fee" class="text-sm font-extrabold text-red-400">-$0</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-xl font-extrabold text-[#312873]">{{ __('messages.calc_net') }}</span>
            <span id="res-net" class="text-3xl font-black text-[#F29C0B]">$0</span>
          </div>
        </div>
      </div>
    </div>

    <div class="flex gap-4 justify-center flex-wrap">
      <button onclick="showPage('office-signup')" class="btn-primary px-12 py-5 text-xl">{{ __('messages.launch_office') }}</button>
      <button onclick="showPage('contact', 'sales')" class="btn-secondary px-12 py-5 text-xl">{{ __('messages.talk_sales') }}</button>
    </div>
  </div>
</section>

<!-- 13) ACADEMY -->
<section id="page-academy" class="page-content px-6 py-24">
  <div class="max-w-6xl mx-auto">
    <span class="section-tag">{{ __('messages.academy_tag') }}</span>
    <h2 class="text-5xl font-extrabold text-[#312873] mb-6">{{ __('messages.academy_title') }}</h2>
    <p class="text-xl text-gray-500 mb-12 max-w-4xl">
      {{ __('messages.academy_desc') }}
    </p>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
      <div class="glass-card p-8">
        <h4 class="font-extrabold mb-2 text-[#312873]">{{ __('messages.ac1') }}</h4>
        <p class="text-xs text-gray-500">{{ __('messages.ac1_desc') }}</p>
      </div>
      <div class="glass-card p-8">
        <h4 class="font-extrabold mb-2 text-[#312873]">{{ __('messages.ac2') }}</h4>
        <p class="text-xs text-gray-500">{{ __('messages.ac2_desc') }}</p>
      </div>
      <div class="glass-card p-8">
        <h4 class="font-extrabold mb-2 text-[#312873]">{{ __('messages.ac3') }}</h4>
        <p class="text-xs text-gray-500">{{ __('messages.ac3_desc') }}</p>
      </div>
      <div class="glass-card p-8">
        <h4 class="font-extrabold mb-2 text-[#312873]">{{ __('messages.ac4') }}</h4>
        <p class="text-xs text-gray-500">{{ __('messages.ac4_desc') }}</p>
      </div>
      <div class="glass-card p-8 bg-gray-50">
        <h4 class="font-extrabold mb-2 text-[#312873]">{{ __('messages.ac5') }}</h4>
        <p class="text-xs text-gray-600">{{ __('messages.ac5_desc') }}</p>
      </div>
      <div class="glass-card p-8 bg-gray-50">
        <h4 class="font-extrabold mb-2 text-[#312873]">{{ __('messages.ac6') }}</h4>
        <p class="text-xs text-gray-600">{{ __('messages.ac6_desc') }}</p>
      </div>
    </div>

    <div class="flex gap-4 flex-wrap">
      <button onclick="showPage('office-signup')" class="btn-primary px-8 py-3">{{ __('messages.start_learning') }}</button>
      <button onclick="showPage('contact', 'demo')" class="btn-secondary px-8 py-3">{{ __('messages.download_playbook') }}</button>
    </div>
  </div>
</section>

<!-- 14) TECHNICAL FAQ -->
<section id="page-tech-faq" class="page-content px-6 py-24 bg-gray-50">
  <div class="max-w-5xl mx-auto">
    <span class="section-tag">{{ __('messages.techfaq_tag') }}</span>
    <h2 class="text-5xl font-extrabold text-[#312873] mb-6">{{ __('messages.techfaq_title') }}</h2>
    <p class="text-xl text-gray-500 mb-12 max-w-4xl">
      {{ __('messages.techfaq_desc') }}
    </p>

    <div class="grid md:grid-cols-2 gap-8">
      <div class="glass-card p-10">
        <h4 class="font-extrabold text-xl text-[#312873] mb-3">{{ __('messages.api_title') }}</h4>
        <p class="text-sm text-gray-600 mb-4">{{ __('messages.api_desc') }}</p>
        <ul class="text-sm text-gray-600 space-y-2">
          <li>• {{ __('messages.api_li1') }}</li>
          <li>• {{ __('messages.api_li2') }}</li>
        </ul>
      </div>

      <div class="glass-card p-10 bg-white">
        <h4 class="font-extrabold text-xl text-[#312873] mb-3">{{ __('messages.uptime_title') }}</h4>
        <p class="text-sm text-gray-600 mb-4">{{ __('messages.uptime_desc') }}</p>
        <ul class="text-sm text-gray-600 space-y-2">
          <li>• {{ __('messages.uptime_li1') }}</li>
        </ul>
      </div>

      <div class="glass-card p-10 bg-white">
        <h4 class="font-extrabold text-xl text-[#312873] mb-3">{{ __('messages.security_title') }}</h4>
        <ul class="text-sm text-gray-600 space-y-2">
          <li>• {{ __('messages.sec1') }}</li>
          <li>• {{ __('messages.sec2') }}</li>
          <li>• {{ __('messages.sec3') }}</li>
        </ul>
      </div>

      <div class="glass-card p-10">
        <h4 class="font-extrabold text-xl text-[#312873] mb-3">{{ __('messages.data_title') }}</h4>
        <p class="text-sm text-gray-600">
          {{ __('messages.data_desc') }}
        </p>
      </div>
    </div>

    <div class="flex gap-4 flex-wrap mt-10">
      <button onclick="showPage('contact', 'corporate')" class="btn-primary px-8 py-3">{{ __('messages.request_brief') }}</button>
      <button onclick="showPage('contact', 'sales')" class="btn-secondary px-8 py-3">{{ __('messages.talk_sales') }}</button>
    </div>
  </div>
</section>



<!-- GOVERNANCE -->
<section id="page-governance" class="page-content px-6 py-24">
  <div class="max-w-5xl mx-auto">
    <span class="section-tag">{{ __('messages.governance_tag') }}</span>
    <h2 class="text-5xl font-extrabold text-[#312873] mb-6">{{ __('messages.governance_title') }}</h2>
    <p class="text-xl text-gray-500 mb-12 max-w-4xl">
      {{ __('messages.governance_desc') }}
    </p>

    <div class="grid md:grid-cols-2 gap-8 mb-10">
      <div class="glass-card p-10">
        <h4 class="font-extrabold text-xl text-[#312873] mb-3">{{ __('messages.fair_play_title') }}</h4>
        <ul class="text-sm text-gray-600 space-y-2">
          <li>• {{ __('messages.fair_play_li1') }}</li>
          <li>• {{ __('messages.fair_play_li2') }}</li>
          <li>• {{ __('messages.fair_play_li3') }}</li>
        </ul>
      </div>
      <div class="glass-card p-10 bg-gray-50">
        <h4 class="font-extrabold text-xl text-[#312873] mb-3">{{ __('messages.ratings_title') }}</h4>
        <ul class="text-sm text-gray-700 space-y-2">
          <li>• {{ __('messages.ratings_li1') }}</li>
          <li>• {{ __('messages.ratings_li2') }}</li>
          <li>• {{ __('messages.ratings_li3') }}</li>
        </ul>
      </div>
    </div>

    <div class="glass-card p-10 mb-10">
      <h4 class="font-extrabold text-xl text-[#312873] mb-3">{{ __('messages.dispute_title') }}</h4>
      <div class="grid md:grid-cols-2 gap-4 text-sm text-gray-600">
        <div>• {{ __('messages.dispute_li1') }}</div>
        <div>• {{ __('messages.dispute_li2') }}</div>
        <div>• {{ __('messages.dispute_li3') }}</div>
        <div>• {{ __('messages.dispute_li4') }}</div>
        <div>• {{ __('messages.dispute_li5') }}</div>
      </div>
    </div>

    <div class="grid md:grid-cols-2 gap-8">
      <div class="glass-card p-10 bg-gray-50">
        <h4 class="font-extrabold text-xl text-[#312873] mb-3">{{ __('messages.price_dumping_title') }}</h4>
        <p class="text-sm text-gray-700">{{ __('messages.price_dumping_desc') }}</p>
      </div>

      <div class="glass-card p-10">
        <h4 class="font-extrabold text-xl text-[#312873] mb-3">{{ __('messages.enforcement_title') }}</h4>
        <ul class="text-sm text-gray-600 space-y-2">
          <li>• {{ __('messages.enforcement_li1') }}</li>
          <li>• {{ __('messages.enforcement_li2') }}</li>
          <li>• {{ __('messages.enforcement_li3') }}</li>
        </ul>
      </div>
    </div>

    <div class="flex gap-4 flex-wrap mt-10">
      <button onclick="showPage('conduct')" class="btn-primary px-8 py-3">{{ __('messages.view_conduct') }}</button>
      <button onclick="showPage('safety')" class="btn-secondary px-8 py-3">{{ __('messages.view_safety') }}</button>
    </div>
  </div>
</section>

<!-- CODE OF CONDUCT -->
<section id="page-conduct" class="page-content px-6 py-24 bg-gray-50">
  <div class="max-w-5xl mx-auto">
    <span class="section-tag">{{ __('messages.conduct_tag') }}</span>
    <h2 class="text-5xl font-extrabold text-[#312873] mb-6">{{ __('messages.conduct_title') }}</h2>
    <p class="text-xl text-gray-500 mb-12 max-w-4xl">{{ __('messages.conduct_desc') }}</p>

    <div class="space-y-6">
      @foreach(__('messages.conduct_sections') as $section)
      <div class="glass-card p-10">
        <h3 class="font-black text-[#312873] mb-3">{{ $section['title'] }}</h3>
        @if(isset($section['paragraph']))
          <p class="text-sm text-gray-600">{{ $section['paragraph'] }}</p>
        @endif
        @if(isset($section['list']))
          <ul class="text-sm text-gray-600 space-y-2">
            @foreach($section['list'] as $li)
            <li>• {{ $li }}</li>
            @endforeach
          </ul>
        @endif
      </div>
      @endforeach
    </div>
  </div>
</section>


<style>
form{
  display:flex;
  flex-direction:column;
  gap:2rem;
}

form > div{
  border-radius:24px !important;
  padding:2rem !important;
  background: linear-gradient(
    135deg,
    rgba(255,255,255,0.9),
    rgba(255,255,255,0.6)
  ) !important;

  backdrop-filter: blur(14px);
  border:1px solid rgba(0,0,0,0.06) !important;

  box-shadow: 0 10px 40px rgba(0,0,0,0.05);
}

form > div > p:first-child{
  font-size:0.7rem !important;
  letter-spacing:2px;
  color:#999 !important;
  margin-bottom:1rem;
}

/* LABEL */
label{
  font-size:0.7rem !important;
  color:#666;
  margin-bottom:6px !important;
  font-weight:700 !important;
  letter-spacing:1px;
}

/* INPUTS */
input,
select,
textarea{
  width:100%;
  padding:14px 16px;
  border-radius:14px;

  border:1px solid #E5E7EB;
  background:#fff;

  font-size:0.9rem;
  color:#312873;

  transition: all .25s ease;
}

input::placeholder,
textarea::placeholder{
  color:#aaa;
  font-weight:500;
}

input:focus,
select:focus,
textarea:focus{
  outline:none;
  border-color:#F29C0B;
  box-shadow:
    0 0 0 3px rgba(242,156,11,0.15),
    0 6px 20px rgba(242,156,11,0.1);

  transform: translateY(-1px);
}

input:hover,
select:hover,
textarea:hover{
  border-color:#ccc;
}

.grid{
  gap:1rem !important;
}

textarea{
  resize:none;
  min-height:110px;
}

button.btn-primary{
  border-radius:16px !important;
  font-size:1.1rem !important;
  font-weight:800;
  letter-spacing:0.5px;

  background: linear-gradient(135deg,#F29C0B,#ffb43b);

  transition: all .25s ease;
}

button.btn-primary:hover{
  transform: translateY(-3px);
  box-shadow: 0 20px 40px rgba(242,156,11,0.35);
}

form .text-center{
  font-size:0.7rem !important;
  letter-spacing:1px;
}

.cursor-pointer{
  transition: color .2s ease;
}

.cursor-pointer:hover{
  color:#F29C0B !important;
}
    </style>
    <!-- 17) OFFICE SIGN-UP (aligned to doc: market/contact + ops snapshot + compliance readiness) -->
<section id="page-office-signup" class="page-content px-6 py-24">
  <div class="max-w-2xl mx-auto glass-card p-12 border-t-[12px] border-[#F29C0B]">
    <h2 class="text-4xl font-extrabold text-[#312873] mb-2">{{ __('messages.office_signup_title') }}</h2>
    <p class="text-sm text-gray-500 mb-10 font-extrabold uppercase tracking-wider italic">
      {{ __('messages.office_signup_subtitle') }}
    </p>

<form id="officeForm" class="space-y-6">
  @csrf

  <!-- Step 1 -->
  <div>
    <p>STEP 1 · OFFICE INFO</p>

    <div class="grid md:grid-cols-2 gap-4">
      <div>
        <label>OFFICE</label>
        <input type="text" name="office_name" placeholder="Office name" required>
      </div>

      <div>
        <label>CONTACT</label>
        <input type="text" name="contact_name" placeholder="Contact person" required>
      </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4 mt-4">
      <div>
        <label>EMAIL</label>
        <input type="email" name="email" placeholder="email@example.com" required>
      </div>

      <div>
        <label>PHONE</label>
        <input type="tel" name="phone" placeholder="+31..." required>
      </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4 mt-4">
      <div>
        <label>CITY</label>
        <input type="text" name="city" required>
      </div>

      <div>
        <label>COUNTRY</label>
        <input type="text" name="country" required>
      </div>
    </div>

    <div class="mt-4">
      <label>WEBSITE</label>
      <input type="url" name="website" placeholder="https://">
    </div>
  </div>

  <!-- Step 2 -->
  <div>
    <p>STEP 2 · BUSINESS</p>

    <div class="grid md:grid-cols-2 gap-4">
      <div>
        <label>BUSINESS TYPE</label>
        <select name="business_category" required>
          <option value="">Select</option>
          <option>New</option>
          <option>Existing</option>
          <option>Corporate</option>
        </select>
      </div>

      <div>
        <label>FLEET SIZE</label>
        <input type="number" name="fleet_size" min="1" required>
      </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4 mt-4">
      <div>
        <label>SERVICE TYPE</label>
        <select name="service_type" required>
          <option value="">Select</option>
          <option>City</option>
          <option>Airport</option>
          <option>Corporate</option>
          <option>Mixed</option>
        </select>
      </div>

      <div>
        <label>CURRENT TOOLS</label>
        <input type="text" name="current_tools">
      </div>
    </div>

    <div class="mt-4">
      <label>COVERAGE</label>
      <input type="text" name="coverage">
    </div>
  </div>

  <!-- Step 3 -->
  <div>
    <p>STEP 3 · DETAILS</p>

    <div class="grid md:grid-cols-2 gap-4">
      <div>
        <label>LICENSE</label>
        <select name="license_status" required>
          <option value="">Select</option>
          <option>Yes</option>
          <option>No</option>
          <option>Not sure</option>
        </select>
      </div>

      <div>
        <label>TIMELINE</label>
        <select name="timeline" required>
          <option value="">Select</option>
          <option>Immediate</option>
          <option>30 days</option>
          <option>60-90 days</option>
          <option>Exploring</option>
        </select>
      </div>
    </div>

    <div class="mt-4">
      <label>NOTES</label>
      <textarea name="notes"></textarea>
    </div>
  </div>

  <!-- Submit -->
  <button class="w-full btn-primary py-5 text-xl mt-2">
    Submit Request
  </button>
</form>
  </div>
</section>


<div id="toast"></div>

<style>
#toast{
  position:fixed;
  bottom:30px;
  right:30px;
  background:linear-gradient(135deg,#312873,#1f1848);
  color:#fff;
  padding:16px 22px;
  border-radius:14px;
  font-weight:700;
  opacity:0;
  transform:translateY(20px);
  transition:.3s;
  z-index:9999;
  box-shadow:0 10px 30px rgba(0,0,0,0.2);
}
#toast.show{
  opacity:1;
  transform:translateY(0);
}
</style>

<script>
function showToast(message){
    let toast = document.getElementById('toast');
    toast.innerText = message;
    toast.classList.add('show');

    setTimeout(()=>{
        toast.classList.remove('show');
    },3000);
}
</script>
<script>
document.getElementById('officeForm').addEventListener('submit', function(e){
    e.preventDefault();

    let form = this;
    let formData = new FormData(form);

    fetch("{{ route('office.request.store') }}", {
        method: "POST",
        headers: {
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        showToast("تم إرسال الطلب بنجاح، سيتم التواصل معك قريباً");
        form.reset();
    })
    .catch(error => {
        showToast("حدث خطأ، حاول مرة أخرى");
    });
});
</script>

    <!-- 18) CONTACT & EARLY ACCESS (aligned fields) -->
<section id="page-contact" class="page-content px-6 py-24">
  <div class="max-w-2xl mx-auto glass-card p-12">
    <span class="section-tag">{{ __('messages.contact_tag') }}</span>
    <h2 class="text-4xl font-extrabold text-[#312873] mb-2">{{ __('messages.contact_title') }}</h2>
    <p class="text-sm text-gray-500 mb-10 font-extrabold">
      {{ __('messages.contact_desc') }}
    </p>

    <form class="space-y-5" onsubmit="event.preventDefault(); alert('{{ __('messages.contact_alert') }}');">
      <div>
        <label class="text-[10px] font-black uppercase mb-2 block">{{ __('messages.contact_reason') }}</label>
        <select id="contact-intent" required>
          <option value="demo">{{ __('messages.contact_demo') }}</option>
          <option value="sales">{{ __('messages.contact_sales') }}</option>
          <option value="migration-audit">{{ __('messages.contact_migration') }}</option>
          <option value="waitlist">{{ __('messages.contact_waitlist') }}</option>
          <option value="corporate">{{ __('messages.contact_corporate') }}</option>
          <option value="partnerships">{{ __('messages.contact_partnerships') }}</option>
          <option value="enterprise-tech">{{ __('messages.contact_enterprise') }}</option>
        </select>
      </div>

      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="text-[10px] font-black uppercase mb-2 block">{{ __('messages.contact_fullname') }}</label>
          <input type="text" placeholder="{{ __('messages.contact_fullname_ph') }}" required>
        </div>
        <div>
          <label class="text-[10px] font-black uppercase mb-2 block">{{ __('messages.contact_company') }}</label>
          <input type="text" placeholder="{{ __('messages.contact_company_ph') }}" required>
        </div>
      </div>

      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="text-[10px] font-black uppercase mb-2 block">{{ __('messages.contact_email') }}</label>
          <input type="email" placeholder="{{ __('messages.contact_email_ph') }}" required>
        </div>
        <div>
          <label class="text-[10px] font-black uppercase mb-2 block">{{ __('messages.contact_phone') }}</label>
          <input type="tel" placeholder="{{ __('messages.contact_phone_ph') }}" required>
        </div>
      </div>

      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="text-[10px] font-black uppercase mb-2 block">{{ __('messages.contact_city') }}</label>
          <input type="text" placeholder="{{ __('messages.contact_city_ph') }}" required>
        </div>
        <div>
          <label class="text-[10px] font-black uppercase mb-2 block">{{ __('messages.contact_country') }}</label>
          <input type="text" placeholder="{{ __('messages.contact_country_ph') }}" required>
        </div>
      </div>

      <div>
        <label class="text-[10px] font-black uppercase mb-2 block">{{ __('messages.contact_fleet') }}</label>
        <input type="text" placeholder="{{ __('messages.contact_fleet_ph') }}" required>
      </div>

      <div>
        <label class="text-[10px] font-black uppercase mb-2 block">{{ __('messages.contact_notes') }}</label>
        <textarea rows="4" placeholder="{{ __('messages.contact_notes_ph') }}"></textarea>
      </div>

      <button class="w-full btn-primary py-4 text-lg">{{ __('messages.contact_submit') }}</button>

      <div class="text-center text-[10px] text-gray-400 mt-2 uppercase font-black tracking-widest">
        {{ __('messages.contact_or') }} <span class="cursor-pointer hover:text-[#312873]" onclick="showPage('office-signup')">{{ __('messages.contact_launch') }}</span>
      </div>
    </form>
  </div>
</section>



<section id="page-privacy" class="page-content px-6 py-24 bg-gray-50">
  <div class="max-w-4xl mx-auto glass-card p-12">
    <span class="section-tag">{{ __('messages.legal_tag') }}</span>
    <h2 class="text-4xl font-extrabold text-[#312873] mb-4">{{ __('messages.privacy_title') }}</h2>
    <p class="text-sm text-gray-600 leading-relaxed">
      {{ __('messages.privacy_desc') }}
    </p>
  </div>
</section>

<section id="page-terms" class="page-content px-6 py-24">
  <div class="max-w-4xl mx-auto glass-card p-12">
    <span class="section-tag">{{ __('messages.legal_tag') }}</span>
    <h2 class="text-4xl font-extrabold text-[#312873] mb-4">{{ __('messages.terms_title') }}</h2>
    <p class="text-sm text-gray-600 leading-relaxed">
      {{ __('messages.terms_desc') }}
    </p>
  </div>
</section>

<section id="page-billing" class="page-content px-6 py-24 bg-gray-50">
  <div class="max-w-4xl mx-auto glass-card p-12">
    <span class="section-tag">{{ __('messages.legal_tag') }}</span>
    <h2 class="text-4xl font-extrabold text-[#312873] mb-4">{{ __('messages.billing_title') }}</h2>
    <p class="text-sm text-gray-600 leading-relaxed">
      {{ __('messages.billing_desc') }}
    </p>
  </div>
</section>

  </main>

  <!-- FOOTER -->
<footer>
  <div class="footer-content">

    <!-- Logo & Description -->
    <div>
      <div class="footer-logo">
        <svg fill="currentColor" viewBox="0 0 24 24">
          <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
        <span>FleetOS</span>
      </div>
      <p class="footer-desc">
        {{ __('messages.footer_desc') }}
      </p>
      <div class="footer-social">
        <a href="#"><i class="fab fa-facebook-f"></i></a>
        <a href="#"><i class="fab fa-twitter"></i></a>
        <a href="#"><i class="fab fa-linkedin-in"></i></a>
        <a href="#"><i class="fab fa-instagram"></i></a>
      </div>
    </div>

    <!-- Platform -->
    <div>
      <h5>{{ __('messages.platform') }}</h5>
      <ul>
        <li><a onclick="showPage('faas')">{{ __('messages.faas') }}</a></li>
        <li><a onclick="showPage('marketplace')">{{ __('messages.marketplace') }}</a></li>
        <li><a onclick="showPage('pricing')">{{ __('messages.pricing') }}</a></li>
        <li><a onclick="showPage('technology')">{{ __('messages.technology') }}</a></li>
      </ul>
    </div>

    <!-- Trust -->
    <div>
      <h5>{{ __('messages.trust') }}</h5>
      <ul>
        <li><a onclick="showPage('safety')">{{ __('messages.safety') }}</a></li>
        <li><a onclick="showPage('governance')">{{ __('messages.governance') }}</a></li>
        <li><a onclick="showPage('conduct')">{{ __('messages.conduct') }}</a></li>
        <li><a onclick="showPage('rollout')">{{ __('messages.rollout') }}</a></li>
      </ul>
    </div>

    <!-- Resources -->
    <div>
      <h5>{{ __('messages.resources') }}</h5>
      <ul>
        <li><a onclick="showPage('academy')">{{ __('messages.academy') }}</a></li>
        <li><a onclick="showPage('tech-faq')">{{ __('messages.tech_faq') }}</a></li>
        <li><a onclick="showPage('contact')">{{ __('messages.contact') }}</a></li>
      </ul>
    </div>

    <!-- Legal -->
    <div>
      <h5>{{ __('messages.legal_tag') }}</h5>
      <ul>
        <li><a onclick="showPage('privacy')">{{ __('messages.privacy_title') }}</a></li>
        <li><a onclick="showPage('terms')">{{ __('messages.terms_title') }}</a></li>
        <li><a onclick="showPage('billing')">{{ __('messages.billing_title') }}</a></li>
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
