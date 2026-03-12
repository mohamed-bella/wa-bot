<?php
/**
 * Basma Mall - E-Commerce Website Layout
 * Header, Hero, Features, Banner, Newsletter, Footer
 * Products managed by separate plugin
 */
if (!defined('ABSPATH')) { exit; }

// ============================================================
// 1. ENQUEUE ASSETS
// ============================================================
function basma_enqueue_assets() {
    wp_enqueue_style('basma-google-fonts', 'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@400;500;600;700;800&display=swap', array(), null);
    wp_enqueue_style('basma-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', array(), '6.5.1');
}
add_action('wp_enqueue_scripts', 'basma_enqueue_assets');

// ============================================================
// 2. GLOBAL CSS
// ============================================================
function basma_output_global_css() {
?>
<style id="basma-mall-styles">
:root{--primary-red:#E74C3C;--primary-red-dark:#C0392B;--dark-bg:#1a1a1a;--darker-bg:#0f0f0f;--charcoal:#2c2c2c;--white:#FFFFFF;--light-gray-bg:#F5F5F5;--text-dark:#333333;--text-gray:#666666;--text-light-gray:#999999;--border-gray:#E0E0E0;--star-gold:#FFC107;--success-green:#27AE60}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Poppins',sans-serif;font-size:16px;line-height:1.6;color:var(--text-dark)}
h1,h2,h3,h4,h5,h6{font-family:'Montserrat',sans-serif;font-weight:700}
a{text-decoration:none;color:inherit}
img{max-width:100%;height:auto}
.basma-container{max-width:1400px;margin:0 auto;padding:0 50px}
@media(max-width:767px){.basma-container{padding:0 20px}}

/* TOP BAR */
.basma-top-bar{background:var(--dark-bg);height:40px;display:flex;align-items:center;padding:0 50px;font-size:13px;color:#fff;z-index:1001;position:relative}
.basma-top-bar-inner{display:flex;justify-content:space-between;align-items:center;width:100%;max-width:1400px;margin:0 auto}
.basma-top-left,.basma-top-center,.basma-top-right{display:flex;align-items:center;gap:8px}
.basma-top-right{gap:15px}
.basma-top-right a{color:#fff;transition:.3s;font-size:14px}
.basma-top-right a:hover{color:var(--primary-red)}
@media(max-width:991px){
    .basma-top-bar{padding:0 20px;height:35px}
    .basma-top-left, .basma-top-right{display:none}
    .basma-top-center{width:100%;justify-content:center;font-size:11px}
}

/* MAIN HEADER */
.basma-main-header{background:rgba(255,255,255,0.95);backdrop-filter:blur(10px);height:90px;padding:0 50px;display:flex;align-items:center;transition:all 0.4s ease;border-bottom:1px solid rgba(0,0,0,0.05)}
.basma-main-header-inner{display:flex;justify-content:space-between;align-items:center;width:100%;max-width:1400px;margin:0 auto;gap:30px}
.basma-logo{font-family:'Montserrat',sans-serif;font-size:28px;font-weight:900;display:flex;align-items:center;white-space:nowrap}
.basma-logo .red{color:var(--primary-red)}
.basma-logo .dark{color:var(--text-dark)}
.basma-logo i{margin-right:10px;color:var(--primary-red);font-size:24px}
.basma-search{flex:1;max-width:600px;position:relative}
.basma-search input{width:100%;height:48px;border:2px solid #f0f0f0;border-radius:12px;padding:0 60px 0 25px;font-family:'Poppins',sans-serif;font-size:14px;outline:none;transition:.3s;background:#f9f9f9}
.basma-search input:focus{border-color:var(--primary-red);background:#fff;box-shadow:0 0 0 4px rgba(231,76,60,0.1)}
.basma-search button{position:absolute;right:5px;top:5px;height:38px;width:45px;background:var(--primary-red);border:none;border-radius:10px;color:#fff;cursor:pointer;font-size:16px;transition:.3s}
.basma-header-icons{display:flex;align-items:center;gap:20px}
.basma-header-icons a{color:var(--text-dark);font-size:20px;transition:.3s;position:relative;width:40px;height:40px;display:flex;align-items:center;justify-content:center;border-radius:10px;background:#f5f5f5}
.basma-header-icons a:hover{color:#fff;background:var(--primary-red)}
.basma-cart-badge, .basma-wishlist-badge{position:absolute;top:-5px;right:-5px;background:var(--primary-red);color:#fff;min-width:18px;height:18px;border-radius:10px;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;padding:0 4px;border:2px solid #fff}

/* CART & WISHLIST DRAWERS */
.basma-cart-drawer, .basma-wishlist-drawer{position:fixed;top:0;right:-100%;width:100%;max-width:400px;height:100vh;background:#fff;z-index:10010;transition:all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);display:flex;flex-direction:column;box-shadow:-10px 0 30px rgba(0,0,0,0.1)}
.basma-cart-drawer.open, .basma-wishlist-drawer.open{right:0}
@media(max-width:767px){
    .basma-cart-drawer, .basma-wishlist-drawer{top:auto;bottom:-100%;right:0;max-width:100%;height:82vh;border-radius:25px 25px 0 0;box-shadow:0 -10px 40px rgba(0,0,0,0.15)}
    .basma-cart-drawer.open, .basma-wishlist-drawer.open{bottom:0}
    .basma-drawer-header{border-radius:25px 25px 0 0;padding:20px 25px}
    .basma-drawer-header::before{content:'';position:absolute;top:10px;left:50%;transform:translateX(-50%);width:40px;height:4px;background:#e0e0e0;border-radius:2px;}
}

.basma-drawer-header{padding:25px;border-bottom:1px solid #eee;display:flex;justify-content:space-between;align-items:center;background:#f9f9f9}
.basma-drawer-header h3{margin:0;font-size:18px;font-weight:800;text-transform:uppercase;letter-spacing:1px}
.basma-drawer-header .close-drawer{cursor:pointer;font-size:20px;color:var(--text-dark);transition:.3s}
.basma-drawer-header .close-drawer:hover{color:var(--primary-red);transform:rotate(90deg)}

.basma-drawer-items{flex:1;overflow-y:auto;padding:20px}
.basma-drawer-empty{text-align:center;padding:50px 20px;color:var(--text-light-gray)}
.basma-drawer-empty i{font-size:50px;margin-bottom:20px;opacity:0.3}

.basma-drawer-item{display:flex;gap:15px;margin-bottom:20px;padding-bottom:15px;border-bottom:1px dashed #eee;align-items:center}
.basma-drawer-item-img{width:70px;height:70px;border-radius:10px;overflow:hidden;background:#f5f5f5;flex-shrink:0}
.basma-drawer-item-img img{width:100%;height:100%;object-fit:cover}
.basma-drawer-item-info{flex:1}
.basma-drawer-item-info h4{font-size:14px;margin:0 0 5px;font-weight:700;line-height:1.4}
.basma-drawer-item-info .price{color:var(--primary-red);font-weight:800;font-size:15px}
.basma-drawer-remove{cursor:pointer;color:#999;transition:.3s;font-size:14px}
.basma-drawer-remove:hover{color:var(--primary-red)}

.basma-drawer-footer{padding:25px;background:#f9f9f9;border-top:1px solid #eee}
.basma-cart-total{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;font-size:18px;font-weight:900}
.basma-primary-btn{display:block;width:100%;padding:18px;background:var(--dark-bg);color:#fff;text-align:center;font-weight:800;border-radius:12px;text-transform:uppercase;letter-spacing:1px;transition:.3s;border:none;cursor:pointer}
.basma-primary-btn:hover{background:var(--primary-red);box-shadow:0 10px 20px rgba(231,76,60,0.2)}

/* WISH-NAV BUTTONS */
.basma-bottom-item .badge{position:absolute;top:5px;right:25%;background:var(--primary-red);color:#fff;width:16px;height:16px;border-radius:50%;font-size:9px;display:flex;align-items:center;justify-content:center;border:1px solid #fff}

/* MOBILE HEADER RE-DESIGN */
@media(max-width:991px){
    .basma-main-header{height:70px;padding:0 15px}
    .basma-logo{font-size:22px}
    .basma-search{display:none} /* Desktop search hidden */
    .basma-header-icons .hide-mobile{display:none}
    .basma-header-icons{gap:10px}
    .basma-header-icons a{width:36px;height:36px;font-size:18px}
}

/* NAVIGATION */
.basma-navigation{background:#fff;height:55px;padding:0 50px;display:flex;align-items:center;justify-content:center;border-top:1px solid #f0f0f0}
.basma-nav-menu{display:flex;align-items:center;gap:35px;list-style:none}
.basma-nav-menu li a{font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--text-dark);transition:.3s;padding:18px 0;position:relative}
.basma-nav-menu li a::after{content:'';position:absolute;bottom:0;left:0;width:0;height:3px;background:var(--primary-red);transition:.3s}
.basma-nav-menu li a:hover::after,.basma-nav-menu li a.active::after{width:100%}
.basma-nav-menu li a:hover,.basma-nav-menu li a.active{color:var(--primary-red)}

.basma-hamburger{display:none;background:var(--dark-bg);border:none;width:40px;height:40px;border-radius:10px;color:#fff;font-size:20px;cursor:pointer;align-items:center;justify-content:center;transition:.3s}
.basma-hamburger:hover{background:var(--primary-red)}

@media(max-width:991px){
    .basma-navigation{display:none}
    .basma-hamburger{display:flex}
}

/* MOBILE SEARCH OVERLAY */
.basma-mobile-search-overlay{position:fixed;top:-100%;left:0;width:100%;height:100px;background:#fff;z-index:10002;padding:20px;transition:0.4s cubic-bezier(0.4, 0, 0.2, 1);box-shadow:0 10px 30px rgba(0,0,0,0.1);display:flex;align-items:center;gap:15px}
.basma-mobile-search-overlay.open{top:0}
.basma-mobile-search-overlay input{flex:1;height:50px;border:2px solid #f0f0f0;border-radius:12px;padding:0 20px;font-size:16px;outline:none}
.basma-mobile-search-overlay .close-search{background:none;border:none;font-size:24px;color:var(--text-dark);cursor:pointer}

/* STICKY BOTTOM NAV (THE SMART WAY) */
.basma-bottom-nav{display:none;position:fixed;bottom:0;left:0;width:100%;background:rgba(255,255,255,0.9);backdrop-filter:blur(15px);height:65px;z-index:9999;border-top:1px solid rgba(0,0,0,0.05);padding:0 10px;box-shadow:0 -5px 20px rgba(0,0,0,0.05)}
.basma-bottom-nav-inner{display:flex;justify-content:space-around;align-items:center;height:100%;max-width:500px;margin:0 auto}
.basma-bottom-item{display:flex;flex-direction:column;align-items:center;color:var(--text-dark);text-decoration:none;gap:4px;transition:.3s;flex:1}
.basma-bottom-item i{font-size:20px}
.basma-bottom-item span{font-size:10px;font-weight:700;text-transform:uppercase}
.basma-bottom-item.active{color:var(--primary-red)}
.basma-bottom-item:active{transform:scale(0.9)}

@media(max-width:991px){
    .basma-bottom-nav{display:block}
    body{padding-bottom:65px} /* Space for bottom nav */
}

/* OFF-CANVAS MENU IMPROVEMENTS */
.basma-mobile-menu{position:fixed;top:0;left:-100%;width:85%;max-width:350px;height:100vh;background:#fff;z-index:10005;padding:40px 25px;transition:left 0.4s ease;box-shadow:15px 0 40px rgba(0,0,0,0.1);overflow-y:auto}
.basma-mobile-menu.open{left:0}
.basma-mobile-menu .close-btn{position:absolute;top:20px;right:20px;font-size:24px;background:none;border:none;color:var(--text-dark)}
.basma-mobile-menu-header{margin-bottom:40px}
.basma-mobile-menu ul{list-style:none}
.basma-mobile-menu ul li{margin-bottom:5px}
.basma-mobile-menu ul li a{display:flex;align-items:center;justify-content:space-between;padding:15px 20px;font-size:15px;font-weight:700;color:var(--text-dark);background:#f9f9f9;border-radius:12px;transition:.3s}
.basma-mobile-menu ul li a i{font-size:12px;color:var(--text-light-gray)}
.basma-mobile-menu ul li a:hover{background:var(--primary-red);color:#fff}
.basma-mobile-menu ul li a:hover i{color:#fff}
.basma-mobile-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);backdrop-filter:blur(3px);z-index:10004;opacity:0;visibility:hidden;transition:.3s}
.basma-mobile-overlay.open{opacity:1;visibility:visible}

/* STICKY HEADER */
.basma-header{position:sticky;top:0;z-index:999;transition:all 0.3s ease}
.basma-header.scrolled .basma-top-bar{margin-top:-40px}
.basma-header.scrolled .basma-main-header{box-shadow:0 4px 20px rgba(0,0,0,0.08);height:75px}
@media(max-width:991px){
    .basma-header.scrolled .basma-top-bar{margin-top:-35px}
    .basma-header.scrolled .basma-main-header{height:60px}
}

/* HERO SECTION */
.basma-hero-section{position:relative;width:100%;height:600px;background-size:cover;background-position:center right;overflow:hidden}
.basma-hero-overlay{position:absolute;inset:0;background:linear-gradient(90deg,rgba(0,0,0,.7) 0%,rgba(0,0,0,.3) 50%,transparent 100%);display:flex;align-items:center}
.basma-hero-content{position:relative;left:10%;max-width:550px;color:#fff}
.basma-hero-tag{display:inline-block;background:var(--primary-red);padding:8px 20px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:2px;margin-bottom:20px;color:#fff}
.basma-hero-title{font-family:'Montserrat',sans-serif;font-size:56px;font-weight:800;line-height:1.2;margin-bottom:15px;text-shadow:2px 2px 4px rgba(0,0,0,.3)}
.basma-hero-subtitle{font-size:16px;line-height:1.8;color:rgba(255,255,255,.9);max-width:480px;margin-bottom:30px}
.basma-hero-btn{display:inline-flex;align-items:center;gap:10px;background:var(--primary-red);color:#fff;padding:16px 45px;font-size:14px;font-weight:700;text-transform:uppercase;border:none;border-radius:4px;cursor:pointer;transition:.3s}
.basma-hero-btn:hover{background:var(--primary-red-dark);transform:translateY(-2px);box-shadow:0 8px 25px rgba(231,76,60,.4)}
.basma-hero-links{margin-top:20px;font-size:13px;color:rgba(255,255,255,.8)}
.basma-hero-links a{color:rgba(255,255,255,.8);transition:.3s}
.basma-hero-links a:hover{color:var(--primary-red);text-decoration:underline}
@media(max-width:767px){.basma-hero-section{height:450px}.basma-hero-content{left:5%;max-width:90%}.basma-hero-title{font-size:28px}.basma-hero-subtitle{font-size:14px;max-width:100%}.basma-hero-btn{width:100%;justify-content:center;padding:14px}.basma-hero-overlay{background:linear-gradient(90deg,rgba(0,0,0,.8) 0%,rgba(0,0,0,.5) 100%)}}

/* FEATURES STRIP */
.basma-features-section{background:var(--charcoal);padding:40px 50px}
.basma-features-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:30px;max-width:1400px;margin:0 auto}
.basma-feature-box{text-align:center;padding:20px}
.basma-feature-box i{font-size:48px;color:var(--primary-red);margin-bottom:15px}
.basma-feature-box h4{font-family:'Montserrat',sans-serif;font-size:16px;font-weight:700;color:#fff;text-transform:uppercase;margin-bottom:8px}
.basma-feature-box p{font-size:13px;color:var(--text-light-gray);line-height:1.5}
@media(max-width:1023px){.basma-features-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:767px){.basma-features-section{padding:30px 20px}.basma-features-grid{grid-template-columns:1fr;gap:20px}}

/* FEATURED BANNER */
.basma-featured-banner{display:grid;grid-template-columns:1fr 1fr;min-height:600px;margin:80px 0}
.basma-featured-content{background:var(--charcoal);padding:80px 60px;display:flex;flex-direction:column;justify-content:center}
.basma-featured-tag{color:var(--text-light-gray);font-size:13px;text-transform:uppercase;letter-spacing:3px;margin-bottom:20px}
.basma-featured-heading{font-family:'Montserrat',sans-serif;font-size:42px;font-weight:700;color:#fff;line-height:1.3;margin-bottom:25px;max-width:400px}
.basma-featured-desc{font-size:15px;color:#b3b3b3;line-height:1.8;margin-bottom:35px;max-width:450px}
.basma-featured-signature{font-family:'Brush Script MT',cursive;font-size:32px;color:#fff;margin-bottom:30px}
.basma-featured-btn{display:inline-block;border:2px solid #fff;color:#fff;padding:15px 35px;font-size:13px;font-weight:700;text-transform:uppercase;border-radius:4px;transition:.3s;background:transparent;cursor:pointer}
.basma-featured-btn:hover{background:#fff;color:var(--charcoal)}
.basma-featured-image{overflow:hidden}.basma-featured-image img{width:100%;height:100%;object-fit:cover;min-height:600px}
@media(max-width:767px){.basma-featured-banner{grid-template-columns:1fr;margin:40px 0}.basma-featured-content{padding:40px 25px;order:2}.basma-featured-heading{font-size:28px}.basma-featured-image{order:1}.basma-featured-image img{min-height:400px}}

/* NEWSLETTER */
.basma-newsletter-section{position:relative;height:500px;background-size:cover;background-position:center;margin:80px 0;overflow:hidden}
.basma-newsletter-overlay{position:absolute;inset:0;background:linear-gradient(135deg,rgba(0,0,0,.75),rgba(231,76,60,.6));display:flex;align-items:center;justify-content:center}
.basma-newsletter-content{max-width:650px;text-align:center;padding:40px;color:#fff}
.basma-newsletter-heading{font-family:'Montserrat',sans-serif;font-size:42px;font-weight:700;margin-bottom:15px;text-shadow:2px 2px 4px rgba(0,0,0,.3)}
.basma-newsletter-sub{font-size:16px;color:rgba(255,255,255,.95);line-height:1.7;margin-bottom:35px}
.basma-newsletter-form{display:flex;max-width:550px;margin:0 auto}
.basma-newsletter-form input{flex:1;height:55px;padding:0 25px;border:none;border-radius:30px 0 0 30px;font-family:'Poppins',sans-serif;font-size:15px;color:var(--text-dark);outline:none}
.basma-newsletter-form input::placeholder{color:var(--text-light-gray)}
.basma-newsletter-form button{height:55px;padding:0 40px;background:var(--primary-red);color:#fff;border:none;border-radius:0 30px 30px 0;font-family:'Poppins',sans-serif;font-size:14px;font-weight:700;text-transform:uppercase;cursor:pointer;transition:.3s;white-space:nowrap}
.basma-newsletter-form button:hover{background:var(--primary-red-dark);box-shadow:0 5px 20px rgba(231,76,60,.4)}
.basma-newsletter-msg{margin-top:15px;font-size:14px}
@media(max-width:767px){.basma-newsletter-section{height:auto;margin:40px 0}.basma-newsletter-overlay{padding:40px 20px}.basma-newsletter-heading{font-size:28px}.basma-newsletter-form{flex-direction:column;gap:15px}.basma-newsletter-form input,.basma-newsletter-form button{border-radius:30px;width:100%}}

/* FOOTER */
.basma-footer{background:var(--dark-bg);color:var(--text-light-gray);padding:70px 50px 0}
.basma-footer-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:50px;max-width:1400px;margin:0 auto;padding-bottom:50px;border-bottom:1px solid rgba(255,255,255,.1)}
.basma-footer-logo{font-family:'Montserrat',sans-serif;font-size:28px;font-weight:700;color:#fff;margin-bottom:20px}
.basma-footer-logo .red{color:var(--primary-red)}
.basma-footer-desc{font-size:14px;line-height:1.8;margin-bottom:25px;color:#b3b3b3}
.basma-footer-contact p{font-size:14px;margin-bottom:12px;color:var(--text-light-gray)}
.basma-footer-contact i{color:var(--primary-red);margin-right:10px;width:18px}
.basma-footer-title{font-family:'Montserrat',sans-serif;font-size:16px;font-weight:700;color:#fff;text-transform:uppercase;margin-bottom:25px;letter-spacing:1px}
.basma-footer-links{list-style:none}
.basma-footer-links li{margin-bottom:12px}
.basma-footer-links a{color:var(--text-light-gray);font-size:14px;transition:.3s;display:inline-block}
.basma-footer-links a:hover{color:var(--primary-red);padding-left:5px}
.basma-footer-bottom{background:var(--darker-bg);margin:0 -50px;padding:25px 50px}
.basma-footer-bottom-inner{display:flex;justify-content:space-between;align-items:center;max-width:1400px;margin:0 auto}
.basma-footer-copyright{font-size:13px;color:var(--text-gray)}
.basma-payment-methods{display:flex;align-items:center;gap:15px;font-size:13px;color:var(--text-light-gray)}
.basma-payment-methods i{font-size:28px;opacity:.7;transition:.3s}
.basma-payment-methods i:hover{opacity:1;color:var(--primary-red)}
.basma-footer-social{display:flex;gap:15px}
.basma-footer-social a{width:38px;height:38px;background:rgba(255,255,255,.1);color:var(--text-light-gray);display:flex;align-items:center;justify-content:center;border-radius:50%;font-size:16px;transition:.3s}
.basma-footer-social a:hover{background:var(--primary-red);color:#fff}
@media(max-width:767px){.basma-footer{padding:40px 20px 0}.basma-footer-grid{grid-template-columns:1fr;gap:30px}.basma-footer-bottom{margin:0 -20px;padding:20px}.basma-footer-bottom-inner{flex-direction:column;gap:20px;text-align:center}}

/* PRODUCT CARDS (GLOBAL) */
/* PRODUCT CARDS (GLOBAL) */
.cso-prod-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px; margin: 40px 0; }
.cso-prod-card { background: #fff; border-radius: 12px; overflow: hidden; transition: none; text-decoration: none; color: inherit; border: 1.5px solid #f0f0f0; display: flex; flex-direction: column; position: relative; }
.cso-prod-card:hover { transform: none; box-shadow: none; border-color: #e0e0e0; }
.cso-prod-thumb-wrap { width: 100%; aspect-ratio: 4/5; overflow: hidden; background: #fdfdfd; position: relative; border-bottom: 1.5px solid #f0f0f0; }
.cso-prod-thumb { width: 100%; height: 100%; object-fit: cover; transition: none; }
.cso-prod-card:hover .cso-prod-thumb { transform: none; }
.cso-prod-body { padding: 15px; flex: 1; display: flex; flex-direction: column; text-align: center; }
.cso-prod-title { font-family: 'Montserrat', sans-serif; font-size: 15px; font-weight: 700; margin: 0 0 5px; color: #111; line-height: 1.3; text-transform: uppercase; letter-spacing: 0; }
.cso-prod-price { font-family: 'Montserrat', sans-serif; font-size: 16px; font-weight: 900; color: #111; margin-bottom: 15px; display: flex; align-items: center; justify-content: center; gap: 4px; }
.cso-prod-price::after { content: 'DH'; font-size: 11px; font-weight: 700; color: #111; }
.cso-prod-actions { display: grid; grid-template-columns: 1fr; gap: 8px; margin-top: auto; }
.cso-prod-btn { padding: 10px; background: transparent; color: #111; text-align: center; font-weight: 700; border-radius: 6px; font-size: 12px; transition: none; text-decoration: none; border: 1.5px solid #111; text-transform: uppercase; }
.cso-prod-btn:hover { background: #111; color: #fff; }
.cso-add-cart-btn { padding: 12px; background: #111; color: #fff; text-align: center; font-weight: 700; border-radius: 6px; font-size: 12px; border: none; cursor: pointer; transition: none; display: flex; align-items: center; justify-content: center; gap: 8px; text-transform: uppercase; }
.cso-add-cart-btn:hover { background: var(--primary-red); }
.cso-wish-btn { position: absolute; top: 12px; right: 12px; width: 36px; height: 36px; background: #fff; border: 1.5px solid #f0f0f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #111; cursor: pointer; z-index: 5; transition: none; }
.cso-wish-btn.active { background: var(--primary-red); color: #fff; border-color: var(--primary-red); }

@media(max-width:767px) {
    .cso-prod-grid { grid-template-columns: repeat(2, 1fr); gap: 15px; margin: 30px 0; }
    .cso-prod-card { border-radius: 8px; border-width: 1px; }
    .cso-prod-body { padding: 10px; }
    .cso-prod-title { font-size: 12px; margin-bottom: 4px; }
    .cso-prod-price { font-size: 14px; margin-bottom: 10px; }
    .cso-prod-btn, .cso-add-cart-btn { padding: 8px; font-size: 10px; border-radius: 4px; }
    .cso-wish-btn { width: 30px; height: 30px; top: 8px; right: 8px; font-size: 12px; }
    .cso-prod-thumb-wrap { border-bottom-width: 1px; }
}

/* PREMIUM SEARCH MODAL */
.basma-search-modal { position: fixed; inset: 0; background: #ffffff; z-index: 999999; display: none; flex-direction: column; opacity: 0; visibility: hidden; transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); }
.basma-search-modal.active { display: flex; opacity: 1; visibility: visible; }
.basma-search-modal-header { padding: 40px 50px; border-bottom: 1px solid #f0f0f0; background: #fff; position: relative; }
.basma-search-modal-close { position: absolute; right: 50px; top: 40px; font-size: 24px; color: #111; cursor: pointer; transition: .3s; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: #f8fafc; }
.basma-search-modal-close:hover { background: var(--primary-red); color: #fff; transform: rotate(90deg); }

.basma-search-modal-content { flex: 1; overflow-y: auto; padding: 60px 50px; background: #fafafa; }
.basma-search-modal-inner { max-width: 1100px; margin: 0 auto; }

.basma-search-form-wrap { max-width: 800px; margin: 0 auto 50px; text-align: center; }
.basma-search-modal-label { display: block; font-family: 'Montserrat', sans-serif; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 2px; color: var(--primary-red); margin-bottom: 15px; }
.basma-search-modal-input-group { position: relative; }
.basma-search-modal-input { width: 100%; height: 80px; border: none; background: transparent; font-family: 'Montserrat', sans-serif; font-size: 42px; font-weight: 800; color: #111; text-align: center; border-bottom: 3px solid #eee; outline: none; transition: .4s; padding-bottom: 10px; }
.basma-search-modal-input:focus { border-color: var(--primary-red); padding-bottom: 20px; }
.basma-search-modal-input::placeholder { color: #cbd5e1; }

.basma-search-modal-results { display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px; }
.basma-search-item-card { background: #fff; border-radius: 16px; padding: 15px; border: 1px solid #eee; display: flex; gap: 20px; align-items: center; transition: .3s; text-decoration: none; }
.basma-search-item-card:hover { border-color: var(--primary-red); box-shadow: 0 15px 40px rgba(0,0,0,0.06); transform: translateY(-3px); }
.basma-search-item-img { width: 90px; height: 90px; border-radius: 12px; object-fit: cover; background: #f8fafc; border: 1px solid #f0f0f0; }
.basma-search-item-info { flex: 1; min-width: 0; }
.basma-search-item-tag { font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; display: block; }
.basma-search-item-title { font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; text-transform: none; }
.basma-search-item-price { font-size: 15px; font-weight: 900; color: var(--primary-red); display: flex; align-items: center; gap: 5px; }

.basma-search-status { grid-column: 1 / -1; text-align: center; padding: 80px 20px; }
.basma-search-status i { font-size: 40px; color: #cbd5e1; margin-bottom: 20px; display: block; }
.basma-search-status h3 { font-family: 'Montserrat', sans-serif; font-size: 20px; font-weight: 800; color: #1e293b; margin-bottom: 10px; }
.basma-search-status p { color: #64748b; font-size: 15px; }

.basma-search-view-all-box { grid-column: 1 / -1; text-align: center; margin-top: 50px; }
.basma-search-view-all-btn { display: inline-flex; align-items: center; gap: 10px; background: #111; color: #fff; padding: 18px 40px; border-radius: 40px; font-weight: 800; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; transition: .3s; text-decoration: none; }
.basma-search-view-all-btn:hover { background: var(--primary-red); transform: translateY(-3px); box-shadow: 0 10px 25px rgba(231,76,60,0.2); }

@media(max-width: 1023px) {
    .basma-search-modal-header { padding: 20px; }
    .basma-search-modal-close { top: 20px; right: 20px; width: 40px; height: 40px; font-size: 18px; }
    .basma-search-modal-content { padding: 40px 20px; }
    .basma-search-modal-input { font-size: 24px; height: 60px; }
    .basma-search-modal-results { grid-template-columns: 1fr; }
}

/* ANIMATIONS */
@keyframes fadeInUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
@keyframes pulse{0%,100%{transform:scale(1)}50%{transform:scale(1.2)}}
.basma-animate{animation:fadeInUp .6s ease forwards}
</style>
<?php
}
add_action('wp_head', 'basma_output_global_css');

// ============================================================
// 3. SHORTCODES
// ============================================================

// --- HEADER ---
function basma_header_shortcode() {
    $cart_count = 0;
    ob_start();
    ?>
    <header class="basma-header" id="basmaHeader">
        <div class="basma-top-bar">
            <div class="basma-top-bar-inner">
                <div class="basma-top-left"><i class="fas fa-phone-alt"></i> <span>+212 6XX-XXX-XXX</span></div>
                <div class="basma-top-center"><i class="fas fa-truck"></i> <span>Livraison gratuite partout au Maroc</span></div>
                <div class="basma-top-right">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
        </div>

        <div class="basma-main-header">
            <div class="basma-main-header-inner">
                <div class="basma-hamburger" id="basmaHamburger">
                    <i class="fas fa-bars"></i>
                </div>

                <a href="<?php echo esc_url(home_url('/')); ?>" class="basma-logo">
                    <img src="<?php echo esc_url( home_url('/wp-content/uploads/2026/02/bassma-mall-logo.png') ); ?>" alt="Basma Mall" style="max-height: 45px; width: auto; display: block;">
                </a>

                <div class="basma-search" id="basmaSearchOpener" style="cursor: pointer;">
                    <input type="text" placeholder="Rechercher des produits..." readonly style="cursor: pointer;">
                    <button type="button"><i class="fas fa-search"></i></button>
                </div>

                <div class="basma-header-icons">
                    <a href="#" class="search-trigger hide-mobile" id="mobileSearchTrigger" aria-label="Search"><i class="fas fa-search"></i></a>
                    <a href="#" class="hide-mobile" aria-label="Wishlist" id="basmaWishlistTrigger">
                        <i class="far fa-heart"></i>
                        <span class="basma-wishlist-badge" id="basmaWishlistBadge">0</span>
                    </a>
                    <a href="#" aria-label="Cart" id="basmaCartTrigger">
                        <i class="fas fa-shopping-bag"></i>
                        <span class="basma-cart-badge" id="basmaCartBadge">0</span>
                    </a>
                </div>
            </div>
        </div>

        <nav class="basma-navigation">
            <ul class="basma-nav-menu">
                <li><a href="<?php echo esc_url(home_url('/')); ?>" class="active">ACCUEIL</a></li>
                <li><a href="#">NOUVEAUTÉS</a></li>
                <li><a href="#">FEMMES</a></li>
                <li><a href="#">HOMMES</a></li>
                <li><a href="#">ACCESSOIRES</a></li>
                <li><a href="#">PROMOTIONS</a></li>
                <li><a href="#">CONTACT</a></li>
            </ul>
        </nav>

        <!-- Redesigned Premium Search Modal -->
        <div class="basma-search-modal" id="basmaSearchModal">
            <div class="basma-search-modal-header">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="basma-logo">
                    <img src="<?php echo esc_url( home_url('/wp-content/uploads/2026/02/bassma-mall-logo.png') ); ?>" alt="Basma Mall" style="max-height: 40px; width: auto; display: block;">
                </a>
                <div class="basma-search-modal-close" id="closeSearchModal"><i class="fas fa-times"></i></div>
            </div>
            
            <div class="basma-search-modal-content">
                <div class="basma-search-modal-inner">
                    <div class="basma-search-form-wrap">
                        <span class="basma-search-modal-label">Exploration de la Boutique</span>
                        <div class="basma-search-modal-input-group">
                            <input type="text" id="modalSearchInput" class="basma-search-modal-input" placeholder="Que cherchez-vous ?" autocomplete="off">
                        </div>
                    </div>

                    <div class="basma-search-modal-results" id="modalSearchResults">
                        <!-- Default empty state or suggestions can go here -->
                        <div class="basma-search-status">
                            <i class="fas fa-search"></i>
                            <h3>Commencez à taper...</h3>
                            <p>Entrez le nom d'un produit, d'une catégorie ou d'une marque.</p>
                        </div>
                    </div>

                    <div class="basma-search-view-all-box" id="modalSearchViewAllContainer" style="display: none;">
                        <a href="#" class="basma-search-view-all-btn" id="modalSearchViewAll">
                            Voir tous les produits <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cart Drawer -->
        <div class="basma-cart-drawer" id="basmaCartDrawer">
            <div class="basma-drawer-header">
                <h3>Votre Panier</h3>
                <div class="close-drawer" id="closeCartDrawer"><i class="fas fa-times"></i></div>
            </div>
            <div class="basma-drawer-items" id="basmaCartItems"></div>
            <div class="basma-drawer-footer" id="basmaCartFooter">
                <div class="basma-cart-total">
                    <span>Total:</span>
                    <span id="basmaCartTotalAmount">0.00 dh</span>
                </div>
                <button class="basma-primary-btn" id="basmaCheckoutBtn">Passer à la Caisse</button>
            </div>
        </div>

        <!-- Wishlist Drawer -->
        <div class="basma-wishlist-drawer" id="basmaWishlistDrawer">
            <div class="basma-drawer-header">
                <h3>Favoris</h3>
                <div class="close-drawer" id="closeWishlistDrawer"><i class="fas fa-times"></i></div>
            </div>
            <div class="basma-drawer-items" id="basmaWishlistItems"></div>
            <div class="basma-drawer-footer">
                <button class="basma-primary-btn" id="closeWishlistFinal">Continuer mes achats</button>
            </div>
        </div>

        <!-- Sticky Bottom Nav (Mobile Only) -->
        <nav class="basma-bottom-nav">
            <div class="basma-bottom-nav-inner">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="basma-bottom-item active">
                    <i class="fas fa-home"></i>
                    <span>Accueil</span>
                </a>
                <a href="#" class="basma-bottom-item" id="bottomSearchTrigger">
                    <i class="fas fa-search"></i>
                    <span>Rechercher</span>
                </a>
                <a href="#" class="basma-bottom-item" id="bottomWishlistTrigger">
                    <i class="far fa-heart"></i>
                    <span class="badge" id="basmaMobileWishBadge">0</span>
                    <span>Favoris</span>
                </a>
                <a href="#" class="basma-bottom-item" id="bottomCartTrigger">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="badge" id="basmaMobileCartBadge">0</span>
                    <span>Panier</span>
                </a>
                <a href="#" class="basma-bottom-item">
                    <i class="fas fa-user"></i>
                    <span>Compte</span>
                </a>
            </div>
        </nav>

        <div class="basma-mobile-overlay" id="basmaMobileOverlay"></div>
        <div class="basma-mobile-menu" id="basmaMobileMenu">
            <div class="basma-mobile-menu-header">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="basma-logo">
                <img src="<?php echo esc_url( home_url('/wp-content/uploads/2026/02/bassma-mall-logo.png') ); ?>" alt="Basma Mall" style="max-height: 40px; width: auto; display: block;">
            </a>
            </div>
            <button class="close-btn" id="basmaMobileClose"><i class="fas fa-times"></i></button>
            <ul>
                <li><a href="<?php echo esc_url(home_url('/')); ?>">Accueil <i class="fas fa-chevron-right"></i></a></li>
                <li><a href="#">Nouveautés <i class="fas fa-chevron-right"></i></a></li>
                <li><a href="#">Collections <i class="fas fa-chevron-right"></i></a></li>
                <li><a href="#">Tendances <i class="fas fa-chevron-right"></i></a></li>
                <li><a href="#">Promotions <i class="fas fa-chevron-right"></i></a></li>
                <li><a href="#">À Propos <i class="fas fa-chevron-right"></i></a></li>
                <li><a href="#">Contact <i class="fas fa-chevron-right"></i></a></li>
            </ul>
        </div>
    </header>
    <?php
    return ob_get_clean();
}

// --- HERO ---
function basma_hero_shortcode() {
    $bg = 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=1920&q=80';
    ob_start();
    ?>
    <section class="basma-hero-section" style="background-image:url('<?php echo esc_url($bg); ?>')">
        <div class="basma-hero-overlay">
            <div class="basma-hero-content">
                <span class="basma-hero-tag">COLLECTION PRINTEMPS 2024</span>
                <h1 class="basma-hero-title">Nouvelle Collection de Marque</h1>
                <p class="basma-hero-subtitle">Découvrez les dernières tendances de la mode féminine avec des pièces uniques et élégantes pour toutes les occasions</p>
                <a href="#" class="basma-hero-btn">ACHETER MAINTENANT <i class="fas fa-shopping-bag"></i></a>
                <div class="basma-hero-links">
                    <a href="#">Tendances</a> &nbsp;|&nbsp; <a href="#">Collections</a> &nbsp;|&nbsp; <a href="#">Nouveautés</a>
                </div>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}

// --- FEATURES ---
function basma_features_shortcode() {
    ob_start();
    ?>
    <section class="basma-features-section">
        <div class="basma-features-grid">
            <div class="basma-feature-box"><i class="fas fa-truck-fast"></i><h4>Livraison Gratuite</h4><p>Pour toute commande de plus de 300 DH</p></div>
            <div class="basma-feature-box"><i class="fas fa-rotate-left"></i><h4>Retours Gratuits</h4><p>Retour sous 7 jours sans frais</p></div>
            <div class="basma-feature-box"><i class="fas fa-headset"></i><h4>Support Client 24/7</h4><p>Service client disponible toujours</p></div>
            <div class="basma-feature-box"><i class="fas fa-lock"></i><h4>Paiement Sécurisé</h4><p>100% sécurisé et protégé</p></div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}

// --- FEATURED BANNER ---
function basma_featured_banner_shortcode() {
    $img = 'https://images.unsplash.com/photo-1487222477894-8943e31ef7b2?w=1000&q=80';
    ob_start();
    ?>
    <section class="basma-featured-banner">
        <div class="basma-featured-content">
            <span class="basma-featured-tag">PRINTEMPS ÉTÉ 2024</span>
            <h2 class="basma-featured-heading">Nouvelle Technologie de Tissus</h2>
            <p class="basma-featured-desc">Nous avons créé une nouvelle génération de vêtements en utilisant des tissus innovants qui offrent un confort exceptionnel, une durabilité supérieure et un style intemporel. Découvrez notre collection exclusive conçue pour la femme moderne.</p>
            <span class="basma-featured-signature">Basma Mall</span>
            <a href="#" class="basma-featured-btn">DÉCOUVRIR MAINTENANT</a>
        </div>
        <div class="basma-featured-image">
            <img src="<?php echo esc_url($img); ?>" alt="Nouvelle collection Basma Mall" loading="lazy">
        </div>
    </section>
    <?php
    return ob_get_clean();
}

// --- NEWSLETTER ---
function basma_newsletter_shortcode() {
    $bg = 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=1920&q=80';
    $nonce = wp_create_nonce('basma_newsletter_nonce');
    ob_start();
    ?>
    <section class="basma-newsletter-section" style="background-image:url('<?php echo esc_url($bg); ?>')">
        <div class="basma-newsletter-overlay">
            <div class="basma-newsletter-content">
                <h2 class="basma-newsletter-heading">Mises à Jour de la Newsletter</h2>
                <p class="basma-newsletter-sub">Abonnez-vous à notre newsletter et recevez des offres exclusives, les dernières tendances et des conseils mode personnalisés</p>
                <form class="basma-newsletter-form" id="basmaNewsletterForm" data-nonce="<?php echo $nonce; ?>">
                    <input type="email" name="email" placeholder="Entrez votre adresse email" required>
                    <button type="submit">S'INSCRIRE</button>
                </form>
                <div class="basma-newsletter-msg" id="basmaNewsletterMsg"></div>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}

// --- FOOTER ---
function basma_footer_shortcode() {
    ob_start();
    ?>
    <footer class="basma-footer">
        <div class="basma-footer-grid">
            <div class="basma-footer-col basma-footer-about">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="basma-footer-logo">
                     <img src="<?php echo esc_url( home_url('/wp-content/uploads/2026/02/bassma-mall-logo.png') ); ?>" alt="Basma Mall" style="max-height: 45px; width: auto; display: block; margin-bottom: 20px;">
                </a>
                <p>Votre destination shopping numéro 1 au Maroc. Découvrez les dernières tendances avec paiement à la livraison sécurisé.</p>
                <div class="basma-footer-contact">
                    <p><i class="fas fa-map-marker-alt"></i> 123 Rue Mohammed V, Casablanca 20000</p>
                    <p><i class="fas fa-phone-alt"></i> +212 522-XXX-XXX</p>
                    <p><i class="fas fa-envelope"></i> contact@basmamall.ma</p>
                </div>
            </div>
            <div>
                <h4 class="basma-footer-title">Navigation</h4>
                <ul class="basma-footer-links">
                    <li><a href="#">Accueil</a></li>
                    <li><a href="#">Nouveautés</a></li>
                    <li><a href="#">Collections</a></li>
                    <li><a href="#">Promotions</a></li>
                    <li><a href="#">À Propos</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
            </div>
            <div>
                <h4 class="basma-footer-title">Shopping</h4>
                <ul class="basma-footer-links">
                    <li><a href="#">Mon Compte</a></li>
                    <li><a href="#">Panier</a></li>
                    <li><a href="#">Liste de Souhaits</a></li>
                    <li><a href="#">Suivi de Commande</a></li>
                    <li><a href="#">Politique de Retour</a></li>
                    <li><a href="#">FAQ</a></li>
                </ul>
            </div>
            <div>
                <h4 class="basma-footer-title">Support Client</h4>
                <ul class="basma-footer-links">
                    <li><a href="#">Centre d'Aide</a></li>
                    <li><a href="#">Guide des Tailles</a></li>
                    <li><a href="#">Livraison</a></li>
                    <li><a href="#">Moyens de Paiement</a></li>
                    <li><a href="#">Conditions d'Utilisation</a></li>
                    <li><a href="#">Confidentialité</a></li>
                </ul>
            </div>
        </div>
        <div class="basma-footer-bottom">
            <div class="basma-footer-bottom-inner">
                <p class="basma-footer-copyright">© <?php echo date('Y'); ?> Basma Mall. Tous droits réservés.</p>
                <div class="basma-payment-methods">
                    <i class="fab fa-cc-visa" title="Visa"></i>
                    <i class="fab fa-cc-mastercard" title="Mastercard"></i>
                    <i class="fab fa-cc-paypal" title="PayPal"></i>
                    <span>Paiement à la livraison</span>
                </div>
                <div class="basma-footer-social">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
        </div>
    </footer>
    <?php
    return ob_get_clean();
}

// ============================================================
// 4. REGISTER SHORTCODES
// ============================================================
function basma_register_shortcodes() {
    add_shortcode('basma_header', 'basma_header_shortcode');
    add_shortcode('basma_hero', 'basma_hero_shortcode');
    add_shortcode('basma_features', 'basma_features_shortcode');
    add_shortcode('basma_featured_banner', 'basma_featured_banner_shortcode');
    add_shortcode('basma_newsletter', 'basma_newsletter_shortcode');
    add_shortcode('basma_footer', 'basma_footer_shortcode');
}
add_action('init', 'basma_register_shortcodes');

// ============================================================
// 5. AJAX HANDLERS
// ============================================================

/**
 * Handle Live Search AJAX
 */
add_action('wp_ajax_basma_search_ajax', 'basma_search_ajax_handler');
add_action('wp_ajax_nopriv_basma_search_ajax', 'basma_search_ajax_handler');

function basma_search_ajax_handler() {
    // Basic Security: Check nonce
    if ( !isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'basma_search_nonce') ) {
        wp_send_json_error('Security check failed');
    }

    $q = isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '';
    
    if ( strlen($q) < 2 ) {
        wp_send_json_success(array('results' => []));
    }

    $args = array(
        'post_type'      => 'cso_product',
        's'              => $q,
        'posts_per_page' => 5,
        'post_status'    => 'publish'
    );

    $query = new WP_Query($args);
    $results = [];

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $pid = get_the_ID();
            $data = function_exists('cso_get_product_data') ? cso_get_product_data($pid) : null;
            
            if ($data) {
                $results[] = array(
                    'id'    => $pid,
                    'title' => get_the_title(),
                    'price' => number_format(floatval($data['base_price']), 2) . ' dh',
                    'url'   => get_permalink(),
                    'img'   => !empty($data['images']) ? $data['images'][0] : get_the_post_thumbnail_url($pid, 'thumbnail')
                );
            }
        }
    }
    wp_reset_postdata();

    wp_send_json_success(array('results' => $results));
}

/**
 * Custom Search Results Template Redirect
 * Intercepts /recherche and renders the product search results
 */
add_action('template_redirect', function() {
    $path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    if ($path === 'recherche') {
        status_header(200);
        get_header();
        
        $q = isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '';
        
        // Use the existing search results logic (updated for 'q' param)
        echo basma_render_custom_search_results($q);
        
        get_footer();
        exit;
    }
});

/**
 * Helper to render custom search results
 */
function basma_render_custom_search_results($s) {
    if(empty($s)) {
        return '<div class="basma-container" style="padding: 100px 0; text-align: center;">
            <i class="fas fa-search" style="font-size:50px; color:#cbd5e1; margin-bottom:20px;"></i>
            <h2 style="font-family:\'Montserrat\',sans-serif; font-weight:800; color:#1e293b; margin-bottom:10px;">Recherche de produits</h2>
            <p style="color:#64748b; font-size:16px;">Veuillez entrer un terme dans la barre de recherche pour trouver des produits.</p>
        </div>';
    }

    $args = array(
        's' => $s,
        'post_type' => 'cso_product',
        'posts_per_page' => 24
    );
    $q = new WP_Query($args);

    ob_start();
    ?>
    <div class="basma-container" style="padding: 60px 0; min-height: 50vh;">
        <div style="text-align:center; margin-bottom: 60px;">
            <h1 style="font-family:'Montserrat',sans-serif; font-size:36px; font-weight:800; color:#1e293b; margin-bottom:15px; text-transform:uppercase; letter-spacing:1px;">Résultats de recherche</h1>
            <p style="font-size:16px; color:#64748b; max-width:600px; margin:0 auto;">Nous avons cherché partout dans la boutique pour "<strong><?php echo esc_html($s); ?></strong>"</p>
            <div style="width:60px; height:4px; background:var(--primary-red); margin: 25px auto 0; border-radius:2px;"></div>
        </div>

        <?php if($q->have_posts()): ?>
            <div class="cso-prod-grid">
                <?php 
                while($q->have_posts()): $q->the_post();
                    $pid = get_the_ID();
                    $data = function_exists('cso_get_product_data') ? cso_get_product_data($pid) : null;
                    if($data):
                        $thumb = !empty($data['images']) ? $data['images'][0] : get_the_post_thumbnail_url($pid, 'full');
                        $price = $data['base_price'];
                        ?>
                        <div class="cso-prod-card basma-animate">
                            <button class="cso-wish-btn" data-id="<?php echo esc_attr($pid); ?>" data-name="<?php echo esc_attr($data['name']); ?>" data-price="<?php echo esc_attr($price); ?>" data-img="<?php echo esc_url($thumb); ?>"><i class="fas fa-heart"></i></button>
                            <a href="<?php the_permalink(); ?>" class="cso-prod-thumb-wrap">
                                <?php if($thumb): ?>
                                    <img src="<?php echo esc_url($thumb); ?>" class="cso-prod-thumb" alt="<?php echo esc_attr($data['name']); ?>">
                                <?php else: ?>
                                    <div class="cso-prod-thumb" style="display:flex;align-items:center;justify-content:center;color:#cbd5e1;background:#f8fafc;"><i class="fa-solid fa-image fa-2x"></i></div>
                                <?php endif; ?>
                            </a>
                            <div class="cso-prod-body">
                                <a href="<?php the_permalink(); ?>" style="text-decoration:none"><h3 class="cso-prod-title"><?php echo esc_html($data['name']); ?></h3></a>
                                <div class="cso-prod-price"><?php echo number_format(floatval($price), 2); ?> dh</div>
                                <div class="cso-prod-actions">
                                    <a href="<?php the_permalink(); ?>" class="cso-prod-btn">Détails</a>
                                    <button class="cso-add-cart-btn" data-id="<?php echo esc_attr($pid); ?>" data-name="<?php echo esc_attr($data['name']); ?>" data-price="<?php echo esc_attr($price); ?>" data-img="<?php echo esc_url($thumb); ?>"><i class="fas fa-cart-plus"></i> Panier</button>
                                </div>
                            </div>
                        </div>
                        <?php
                    endif;
                endwhile;
                wp_reset_postdata();
                ?>
            </div>
        <?php else: ?>
            <div style="text-align:center; padding: 80px 20px; background:#f8fafc; border-radius:30px; border:2px dashed #e2e8f0; max-width:650px; margin:0 auto;">
                <div style="width:100px; height:100px; background:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 25px; box-shadow:0 10px 30px rgba(0,0,0,0.05);">
                    <i class="fas fa-box-open" style="font-size:40px; color:#cbd5e1;"></i>
                </div>
                <h3 style="font-family:'Montserrat',sans-serif; font-size:24px; font-weight:800; color:#1e293b; margin-bottom:15px;">Oups! Rien n'a été trouvé</h3>
                <p style="color:#64748b; font-size:16px; margin-bottom:30px; line-height:1.6;">Désolé, nous n'avons trouvé aucun produit correspondant à "<strong><?php echo esc_html($s); ?></strong>". Essayez de vérifier l'orthographe ou utilisez d'autres mots-clés plus généraux.</p>
                <div style="display:flex; justify-content:center; gap:15px; flex-wrap:wrap;">
                    <button onclick="document.querySelector('.basma-mobile-search-overlay').classList.add('open'); document.getElementById('mobileSearchInput').focus();" style="padding:15px 35px; background:var(--primary-red); color:#fff; border:none; border-radius:30px; font-weight:700; font-size:15px; transition:0.3s; box-shadow:0 10px 25px rgba(231,76,60,0.3); cursor:pointer;">Nouvelle recherche</button>
                    <a href="<?php echo esc_url(home_url('/')); ?>" style="padding:15px 35px; background:#1e293b; color:#fff; border-radius:30px; font-weight:700; font-size:15px; transition:0.3s; box-shadow:0 10px 25px rgba(0,0,0,0.1); text-decoration:none;">Retour à l'accueil</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
// ============================================================

// --- Newsletter AJAX ---
function basma_ajax_newsletter_subscribe() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'basma_newsletter_nonce')) {
        wp_send_json_error(array('message' => 'Erreur de sécurité'));
    }
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    if (!is_email($email)) {
        wp_send_json_error(array('message' => 'Adresse email invalide'));
    }
    $subscribers = get_option('basma_newsletter_subscribers', array());
    if (in_array($email, $subscribers)) {
        wp_send_json_error(array('message' => 'Vous êtes déjà inscrit(e)!'));
    }
    $subscribers[] = $email;
    update_option('basma_newsletter_subscribers', $subscribers);
    wp_send_json_success(array('message' => 'Merci pour votre inscription! 🎉'));
}
add_action('wp_ajax_basma_newsletter', 'basma_ajax_newsletter_subscribe');
add_action('wp_ajax_nopriv_basma_newsletter', 'basma_ajax_newsletter_subscribe');

// ============================================================
// 6. JAVASCRIPT
// ============================================================
function basma_output_footer_js() {
?>
<script id="basma-mall-js">
(function(){
    "use strict";

    /* --- Mobile Menu & Search --- */
    var hamburger = document.getElementById('basmaHamburger');
    var mobileMenu = document.getElementById('basmaMobileMenu');
    var mobileClose = document.getElementById('basmaMobileClose');
    var mobileOverlay = document.getElementById('basmaMobileOverlay');
    
    var searchOverlay = document.getElementById('basmaMobileSearchOverlay');
    var searchClose = document.getElementById('closeSearch');
    var searchTriggers = [document.getElementById('mobileSearchTrigger'), document.getElementById('bottomSearchTrigger')];

    // Success Modal UI Injection
    const urlParams = new URLSearchParams(window.location.search);
    if(urlParams.get('order') === 'success') {
        const successModalHTML = `
            <div id="basmaSuccessModal" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); backdrop-filter:blur(5px); z-index:100000; display:flex; align-items:center; justify-content:center; opacity:0; visibility:hidden; transition:all 0.4s ease;">
                <div style="background:#fff; max-width:400px; width:90%; border-radius:30px; padding:40px 30px; text-align:center; transform:scale(0.8); transition:all 0.4s ease; box-shadow:0 20px 50px rgba(0,0,0,0.15); position:relative; overflow:hidden;">
                    <button onclick="this.closest('#basmaSuccessModal').style.opacity='0'; setTimeout(()=>this.closest('#basmaSuccessModal').remove(), 400);" style="position:absolute; top:15px; right:20px; background:none; border:none; font-size:24px; color:#aaa; cursor:pointer; transition:.3s;">&times;</button>
                    <div style="width:80px; height:80px; background:#27ae60; border-radius:50%; color:#fff; display:flex; align-items:center; justify-content:center; font-size:40px; margin:0 auto 20px; animation:pulse 2s infinite;">
                        <i class="fas fa-check"></i>
                    </div>
                    <h2 style="font-family:'Montserrat',sans-serif; font-size:24px; font-weight:800; color:#1e293b; margin-bottom:10px;">Commande Réussie!</h2>
                    <p style="color:#666; font-size:15px; line-height:1.6; margin-bottom:25px;">Merci pour votre commande. Notre équipe vous contactera dans les plus brefs délais pour confirmer la livraison à domicile.</p>
                    <button onclick="this.closest('#basmaSuccessModal').style.opacity='0'; setTimeout(()=>this.closest('#basmaSuccessModal').remove(), 400);" style="background:#1e293b; color:#fff; padding:15px 30px; border:none; border-radius:30px; font-weight:700; width:100%; font-size:15px; cursor:pointer; transition:.3s; box-shadow:0 10px 20px rgba(0,0,0,0.05);">Continuer les achats</button>
                    <!-- Confetti background shapes -->
                    <div style="position:absolute; top:-20px; left:-20px; width:100px; height:100px; background:var(--primary-red); opacity:0.1; border-radius:50%; z-index:-1;"></div>
                    <div style="position:absolute; bottom:-30px; right:-30px; width:150px; height:150px; background:#27ae60; opacity:0.1; border-radius:50%; z-index:-1;"></div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', successModalHTML);
        const basmaModal = document.getElementById('basmaSuccessModal');
        
        // Trigger animations
        setTimeout(() => {
            basmaModal.style.opacity = '1';
            basmaModal.style.visibility = 'visible';
            basmaModal.children[0].style.transform = 'scale(1)';
        }, 100);

        // Clean URL properly without reloading
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    /* --- Drawers Handling --- */
    var cartDrawer = document.getElementById('basmaCartDrawer');
    var wishDrawer = document.getElementById('basmaWishlistDrawer');
    var mobileOverlay = document.getElementById('basmaMobileOverlay');

    function openDrawer(drawer) {
        if(!drawer) return;
        if(drawer === cartDrawer) refreshCartDrawer();
        if(drawer === wishDrawer) refreshWishlistDrawer();
        drawer.classList.add('open');
        if(mobileOverlay) mobileOverlay.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeAllDrawers() {
        if(cartDrawer) cartDrawer.classList.remove('open');
        if(wishDrawer) wishDrawer.classList.remove('open');
        if(mobileMenu) mobileMenu.classList.remove('open');
        if(searchOverlay) searchOverlay.classList.remove('open');
        if(mobileOverlay) mobileOverlay.classList.remove('open');
        document.body.style.overflow = '';
    }

    if(hamburger && mobileMenu) {
        hamburger.addEventListener('click', function(e) {
            e.preventDefault();
            mobileMenu.classList.add('open');
            mobileOverlay.classList.add('open');
            document.body.style.overflow = 'hidden';
        });
    }

    if(mobileClose) {
        mobileClose.addEventListener('click', closeAllDrawers);
    }

    searchTriggers.forEach(t => {
        if(t) {
            t.addEventListener('click', function(e) {
                e.preventDefault();
                searchOverlay.classList.add('open');
                setTimeout(() => {
                    const inp = searchOverlay.querySelector('input');
                    if(inp) inp.focus();
                }, 100);
            });
        }
    });

    if(searchClose) {
        searchClose.addEventListener('click', function(e) {
            e.preventDefault();
            searchOverlay.classList.remove('open');
        });
    }

    [
        'basmaCartTrigger', 'bottomCartTrigger', 
        'basmaWishlistTrigger', 'bottomWishlistTrigger', 
        'closeCartDrawer', 'closeWishlistDrawer', 
        'closeWishlistFinal', 'basmaMobileOverlay'
    ].forEach(id => {
        const el = document.getElementById(id);
        if(!el) return;
        el.addEventListener('click', (e) => {
            e.preventDefault();
            if(id.includes('Trigger')) {
                openDrawer(id.includes('Cart') ? cartDrawer : wishDrawer);
            } else {
                closeAllDrawers();
            }
        });
    });

    /* --- GLOBAL STATE MANAGER --- */
    window.BasmaShop = {
        cart: JSON.parse(localStorage.getItem('basma_cart')) || [],
        wishlist: JSON.parse(localStorage.getItem('basma_wishlist')) || [],

        save: function() {
            localStorage.setItem('basma_cart', JSON.stringify(this.cart));
            localStorage.setItem('basma_wishlist', JSON.stringify(this.wishlist));
            this.updateBadges();
        },

        addItem: function(id, name, price, img) {
            const idx = this.cart.findIndex(x => x.id === id);
            if(idx > -1) {
                this.cart[idx].qty = (this.cart[idx].qty || 1) + 1;
            } else {
                this.cart.push({id, name, price, img, qty: 1});
            }
            this.save();
            openDrawer(cartDrawer);
        },

        updateQty: function(index, delta) {
            if(this.cart[index]) {
                const newQty = (this.cart[index].qty || 1) + delta;
                if(newQty > 0) {
                    this.cart[index].qty = newQty;
                    this.save();
                    refreshCartDrawer();
                } else {
                    this.removeItem(index);
                }
            }
        },

        removeItem: function(index) {
            this.cart.splice(index, 1);
            this.save();
            refreshCartDrawer();
        },

        toggleWishlist: function(id, name, price, img) {
            const idx = this.wishlist.findIndex(x => x.id === id);
            const isAdded = idx === -1;
            if(isAdded) this.wishlist.push({id, name, price, img});
            else this.wishlist.splice(idx, 1);
            this.save();
            if(isAdded) openDrawer(wishDrawer);
            return isAdded;
        },

        removeWish: function(index) {
            this.wishlist.splice(index, 1);
            this.save();
            refreshWishlistDrawer();
            // Need to update hearts in grid if present
            document.querySelectorAll(`.cso-wish-btn`).forEach(btn => {
                const wishId = this.wishlist.map(w => w.id);
                btn.classList.toggle('active', wishId.includes(btn.dataset.id));
            });
        },

        updateBadges: function() {
            const counts = {
                'basmaCartBadge': this.cart.length,
                'basmaMobileCartBadge': this.cart.length,
                'basmaWishlistBadge': this.wishlist.length,
                'basmaMobileWishBadge': this.wishlist.length
            };
            for(let id in counts) {
                const el = document.getElementById(id);
                if(el) el.textContent = counts[id];
            }
        }
    };

    function refreshCartDrawer() {
        const containerId = 'basmaCartItems';
        const items = window.BasmaShop ? window.BasmaShop.cart : [];
        const footerId = 'basmaCartFooter';
        const totalId = 'basmaCartTotalAmount';

        const container = document.getElementById(containerId);
        if(!container) return;
        container.innerHTML = '';

        if(items.length === 0) {
            container.innerHTML = `<div class="basma-drawer-empty"><i class="fas fa-shopping-basket"></i><p>Vide</p></div>`;
            if(footerId) document.getElementById(footerId).style.display = 'none';
        } else {
            let total = 0;
            items.forEach((item, idx) => {
                const qty = item.qty || 1;
                total += parseFloat(item.price) * qty;
                container.innerHTML += `
                    <div class="basma-drawer-item">
                        <div class="basma-drawer-item-img"><img src="${item.img}" alt="${item.name}"></div>
                        <div class="basma-drawer-item-info">
                            <h4>${item.name}</h4>
                            <div class="price">${parseFloat(item.price).toFixed(2)} dh</div>
                            <div style="display:flex; align-items:center; gap:10px; margin-top:5px;">
                                <div class="basma-qty-control" style="display:inline-flex; align-items:center; background:#f0f2f5; border-radius:20px; padding:3px;">
                                    <button onclick="window.BasmaShop && window.BasmaShop.updateQty(${idx}, -1)" style="border:none; background:#fff; width:22px; height:22px; border-radius:50%; cursor:pointer; font-weight:800; color:#1e293b; box-shadow:0 2px 5px rgba(0,0,0,0.05); display:flex; align-items:center; justify-content:center; font-size:14px; transition:0.2s;">&minus;</button>
                                    <span style="font-size:13px; font-weight:700; width:30px; text-align:center; color:#1e293b; display:inline-block;">${qty}</span>
                                    <button onclick="window.BasmaShop && window.BasmaShop.updateQty(${idx}, 1)" style="border:none; background:#fff; width:22px; height:22px; border-radius:50%; cursor:pointer; font-weight:800; color:#1e293b; box-shadow:0 2px 5px rgba(0,0,0,0.05); display:flex; align-items:center; justify-content:center; font-size:14px; transition:0.2s;">&plus;</button>
                                </div>
                            </div>
                        </div>
                        <div class="basma-drawer-remove" onclick="window.BasmaShop && window.BasmaShop.removeItem(${idx})"><i class="fas fa-times"></i></div>
                    </div>
                `;
            });
            if(totalId) document.getElementById(totalId).textContent = total.toFixed(2) + ' dh';
            if(footerId) document.getElementById(footerId).style.display = 'block';
        }
    }

    function refreshWishlistDrawer() {
        const containerId = 'basmaWishlistItems';
        const items = window.BasmaShop ? window.BasmaShop.wishlist : [];
        const container = document.getElementById(containerId);
        if(!container) return;
        container.innerHTML = '';

        if(items.length === 0) {
            container.innerHTML = `<div class="basma-drawer-empty"><i class="fas fa-heart"></i><p>Vide</p></div>`;
        } else {
            items.forEach((item, idx) => {
                container.innerHTML += `
                    <div class="basma-drawer-item">
                        <div class="basma-drawer-item-img"><img src="${item.img}" alt="${item.name}"></div>
                        <div class="basma-drawer-item-info">
                            <h4>${item.name}</h4>
                            <div class="price">${parseFloat(item.price).toFixed(2)} dh</div>
                        </div>
                        <div class="basma-drawer-remove" onclick="window.BasmaShop && window.BasmaShop.removeWish(${idx})"><i class="fas fa-times"></i></div>
                    </div>
                `;
            });
        }
    }

    // Smart Checkout Redirection
    const checkoutBtn = document.getElementById('basmaCheckoutBtn');
    if(checkoutBtn) {
        checkoutBtn.addEventListener('click', () => {
            if(!window.BasmaShop || window.BasmaShop.cart.length === 0) return alert('Votre panier est vide!');
            const ids = window.BasmaShop.cart.map(item => item.id).join(',');
            window.location.href = `<?php echo home_url('/checkout/'); ?>?pids=${ids}`;
        });
    }

    if(window.BasmaShop) window.BasmaShop.updateBadges();

    /* --- Sticky Header --- */
    var header = document.getElementById('basmaHeader');
    if (header) {
        var lastScroll = 0;
        window.addEventListener('scroll', function() {
            var currentScroll = window.scrollY;
            header.classList.toggle('scrolled', currentScroll > 50);
            
            // Hide bottom nav on scroll down, show on scroll up (optional but smart)
            var bottomNav = document.querySelector('.basma-bottom-nav');
            if(bottomNav && window.innerWidth < 992) {
                if(currentScroll > lastScroll && currentScroll > 200) {
                    bottomNav.style.transform = 'translateY(100%)';
                } else {
                    bottomNav.style.transform = 'translateY(0)';
                }
            }
            lastScroll = currentScroll;
        });
    }

    /* --- Newsletter Form --- */
    var nlForm = document.getElementById('basmaNewsletterForm');
    if (nlForm) {
        nlForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var emailInput = this.querySelector('input[name="email"]');
            var nonce = this.dataset.nonce;
            var msgEl = document.getElementById('basmaNewsletterMsg');
            var submitBtn = this.querySelector('button');
            var origBtn = submitBtn.textContent;

            if (!emailInput.value || !emailInput.value.includes('@')) {
                if (msgEl) { msgEl.textContent = 'Veuillez entrer une adresse email valide'; msgEl.style.color = '#E74C3C'; }
                return;
            }

            submitBtn.textContent = 'ENVOI...';
            submitBtn.disabled = true;

            var fd = new FormData();
            fd.append('action', 'basma_newsletter');
            fd.append('email', emailInput.value);
            fd.append('nonce', nonce);

            fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                method: 'POST',
                body: fd,
                credentials: 'same-origin'
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (msgEl) {
                    msgEl.textContent = data.data.message;
                    msgEl.style.color = data.success ? '#27AE60' : '#E74C3C';
                }
                if (data.success) { emailInput.value = ''; }
                submitBtn.textContent = origBtn;
                submitBtn.disabled = false;
            })
            .catch(function() {
                if (msgEl) { msgEl.textContent = 'Erreur de connexion'; msgEl.style.color = '#E74C3C'; }
                submitBtn.textContent = origBtn;
                submitBtn.disabled = false;
            });
        });
    }

    /* --- Lazy Load Images --- */
    if ('IntersectionObserver' in window) {
        var lazyImages = document.querySelectorAll('img[loading="lazy"]');
        var imageObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    imageObserver.unobserve(entry.target);
                }
            });
        }, { rootMargin: '50px 0px' });
        lazyImages.forEach(function(img) {
            img.style.opacity = '0';
            img.style.transition = 'opacity 0.4s ease';
            if (img.complete) { img.style.opacity = '1'; }
            else { imageObserver.observe(img); }
        });
    }

    /* --- Smooth Scroll --- */
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            var target = this.getAttribute('href');
            if (target && target.length > 1) {
                var el = document.querySelector(target);
                if (el) {
                    e.preventDefault();
                    var offset = header ? header.offsetHeight : 0;
                    var pos = el.getBoundingClientRect().top + window.pageYOffset - offset;
                    window.scrollTo({ top: pos, behavior: 'smooth' });
                }
            }
        });
    });

    /* --- Global Interactive Logic for Product Cards --- */
    // Use event delegation to handle dynamically added cards (like in search)
    document.body.addEventListener('click', function(e) {
        // Add to Cart Logic
        if(e.target.closest('.cso-add-cart-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.cso-add-cart-btn');
            if(window.BasmaShop) {
                BasmaShop.addItem(btn.dataset.id, btn.dataset.name, btn.dataset.price, btn.dataset.img);
                
                // Button Feedback
                const oldHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> Prêt!';
                setTimeout(() => { btn.innerHTML = oldHtml; }, 1500);
            }
        }

        // Wishlist Logic
        if(e.target.closest('.cso-wish-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.cso-wish-btn');
            if(window.BasmaShop) {
                const isAdded = BasmaShop.toggleWishlist(btn.dataset.id, btn.dataset.name, btn.dataset.price, btn.dataset.img);
                btn.classList.toggle("active", isAdded);
            }
        }
    });

    // Initialize Wishlist Hearts visually
    setTimeout(() => {
        const wishlist = JSON.parse(localStorage.getItem("basma_wishlist")) || [];
        document.querySelectorAll(".cso-wish-btn").forEach(btn => {
            if(wishlist.some(x => x.id == btn.dataset.id)) btn.classList.add("active");
        });
    }, 100);

    /* --- UNIFIED SEARCH MODAL LOGIC --- */
    const searchModal = document.getElementById('basmaSearchModal');
    const modalInput = document.getElementById('modalSearchInput');
    const modalResults = document.getElementById('modalSearchResults');
    const viewAllBox = document.getElementById('modalSearchViewAllContainer');
    const viewAllBtn = document.getElementById('modalSearchViewAll');
    const searchNonce = '<?php echo wp_create_nonce("basma_search_nonce"); ?>';
    let searchDebounce;

    function openSearchModal() {
        if(!searchModal) return;
        searchModal.classList.add('active');
        document.body.style.overflow = 'hidden';
        setTimeout(() => modalInput && modalInput.focus(), 300);
    }

    function closeSearchModal() {
        if(!searchModal) return;
        searchModal.classList.remove('active');
        document.body.style.overflow = '';
        modalInput.value = '';
        modalResults.innerHTML = '';
        viewAllBox.style.display = 'none';
    }

    // Triggers
    const searchOpeners = [
        document.getElementById('basmaSearchOpener'),
        document.getElementById('mobileSearchTrigger'),
        document.getElementById('bottomSearchTrigger')
    ];

    searchOpeners.forEach(opener => {
        if(opener) opener.addEventListener('click', (e) => {
            e.preventDefault();
            openSearchModal();
        });
    });

    const closeBtn = document.getElementById('closeSearchModal');
    if(closeBtn) closeBtn.addEventListener('click', closeSearchModal);

    // Live Search Logic
    if(modalInput) {
        modalInput.addEventListener('input', function() {
            const query = this.value.trim();
            clearTimeout(searchDebounce);

            if(query.length === 0) {
                modalResults.innerHTML = `
                    <div class="basma-search-status">
                        <i class="fas fa-search"></i>
                        <h3>Commencez à taper...</h3>
                        <p>Entrez le nom d'un produit, d'une catégorie ou d'une marque.</p>
                    </div>
                `;
                viewAllBox.style.display = 'none';
                return;
            }

            // Show loading immediately
            modalResults.innerHTML = `
                <div class="basma-search-status">
                    <i class="fas fa-spinner fa-spin" style="color:var(--primary-red)"></i>
                    <h3>Recherche en cours...</h3>
                    <p>Nous parcourons notre catalogue pour vous.</p>
                </div>
            `;
            viewAllBox.style.display = 'none';

            searchDebounce = setTimeout(() => {
                fetch(`<?php echo admin_url('admin-ajax.php'); ?>?action=basma_search_ajax&q=${encodeURIComponent(query)}&nonce=${searchNonce}`)
                    .then(r => r.json())
                    .then(res => {
                        if(res.success) {
                            renderModalResults(res.data.results, query);
                        } else {
                            modalResults.innerHTML = '<div class="basma-search-modal-empty">Erreur de recherche</div>';
                        }
                    })
                    .catch(() => {
                        modalResults.innerHTML = '<div class="basma-search-modal-empty">Problème de connexion</div>';
                    });
            }, 300);
        });
    }

    function renderModalResults(results, query) {
        if(results.length === 0) {
            modalResults.innerHTML = `
                <div class="basma-search-status">
                    <i class="fas fa-frown"></i>
                    <h3>Aucun résultat trouvé</h3>
                    <p>Désolé, nous n'avons trouvé aucun produit pour "${query}".</p>
                </div>
            `;
            viewAllBox.style.display = 'none';
            return;
        }

        let html = '';
        results.forEach(item => {
            html += `
                <a href="${item.url}" class="basma-search-item-card">
                    <img src="${item.img}" class="basma-search-item-img" alt="${item.title}">
                    <div class="basma-search-item-info">
                        <span class="basma-search-item-tag">Produit</span>
                        <div class="basma-search-item-title">${item.title}</div>
                        <div class="basma-search-item-price">${item.price}</div>
                    </div>
                </a>
            `;
        });
        
        modalResults.innerHTML = html;
        viewAllBtn.href = `<?php echo home_url('/recherche'); ?>?q=${encodeURIComponent(query)}`;
        viewAllBox.style.display = 'block';
    }

    // Close on ESC
    document.addEventListener('keydown', (e) => {
        if(e.key === 'Escape' && searchModal && searchModal.classList.contains('active')) {
            closeSearchModal();
        }
    });

})();
</script>
<?php
}
add_action('wp_footer', 'basma_output_footer_js', 99);
/**
 * Shortcode: Unified Search Results
 * Usage: [basma_search_results]
 * Now points to the primary search results logic
 */
add_shortcode('basma_search_results', 'basma_unified_search_sc');
function basma_unified_search_sc() {
    $q = isset($_GET['q']) ? sanitize_text_field($_GET['q']) : (isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '');
    return basma_render_custom_search_results($q);
}
