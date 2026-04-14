<?php
session_start();
include('db.php');

$student_count = 0;
$course_count  = 0;

$res1 = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='student'");
if ($res1) $student_count = (int)$res1->fetch_assoc()['total'];

$res2 = $conn->query("SELECT COUNT(*) as total FROM courses");
if ($res2) $course_count = (int)$res2->fetch_assoc()['total'];

// ── CONTACT FORM ─────────────────────────────────────
$contact_success = '';
$contact_error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $name    = trim(strip_tags($_POST['name']    ?? ''));
    $email   = trim(strip_tags($_POST['email']   ?? ''));
    $phone   = trim(strip_tags($_POST['phone']   ?? ''));
    $subject = trim(strip_tags($_POST['subject'] ?? ''));
    $message = trim(strip_tags($_POST['message'] ?? ''));

    if (empty($name) || empty($email) || empty($message) || empty($subject)) {
        $contact_error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $contact_error = 'Please enter a valid email address.';
    } else {
        // ── Option A: save to database (uncomment when table exists)
        // $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, created_at) VALUES (?,?,?,?,?,NOW())");
        // $stmt->bind_param("sssss", $name, $email, $phone, $subject, $message);
        // $stmt->execute();

        // ── Option B: send email via PHP mail()
        $to      = 'info@kiharutechnical.ac.ke';
        $headers = "From: $name <$email>\r\nReply-To: $email\r\nContent-Type: text/plain; charset=UTF-8";
        $body    = "Name: $name\nEmail: $email\nPhone: $phone\nSubject: $subject\n\nMessage:\n$message";
        @mail($to, "Portal Inquiry: $subject", $body, $headers);

        $contact_success = "Thank you, $name! Your message has been received. We'll respond to $email within 24 hours.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kiharu College — Excellence in Education</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
/* ═══════════════════════════════════════════════
   KIHARU COLLEGE PORTAL — Premium Design System
   Font: Playfair Display (headings) + DM Sans (body)
   Theme: Deep Navy × Ivory × Gold
═══════════════════════════════════════════════ */
:root{
  --navy:        #0c1f3d;
  --navy-mid:    #1a3560;
  --navy-light:  #264d8c;
  --gold:        #c9a84c;
  --gold-light:  #e2c278;
  --gold-pale:   #f8f1df;
  --ivory:       #fdfaf4;
  --ivory-dark:  #f0ead8;
  --text:        #1a1a2e;
  --text-mid:    #3d4a5c;
  --text-muted:  #7a8899;
  --white:       #ffffff;
  --border:      rgba(12,31,61,0.1);
  --border-gold: rgba(201,168,76,0.3);
  --card-shadow: 0 2px 24px rgba(12,31,61,0.07);
  --card-shadow-hover: 0 12px 48px rgba(12,31,61,0.14);
}
[data-theme="dark"]{
  --navy:        #0a1628;
  --navy-mid:    #0e2040;
  --navy-light:  #1a3560;
  --ivory:       #0f1a2e;
  --ivory-dark:  #0c1528;
  --text:        #e8edf5;
  --text-mid:    #aab4c4;
  --text-muted:  #6b7a8d;
  --white:       #1a2a40;
  --border:      rgba(255,255,255,0.07);
  --border-gold: rgba(201,168,76,0.2);
  --card-shadow: 0 2px 24px rgba(0,0,0,0.3);
  --card-shadow-hover: 0 12px 48px rgba(0,0,0,0.5);
  --gold-pale:   #1a2535;
}
*,*::before,*::after{ margin:0; padding:0; box-sizing:border-box; }
html{ scroll-behavior:smooth; }
body{
  font-family:'DM Sans',sans-serif;
  background:var(--ivory);
  color:var(--text);
  transition:background .3s,color .3s;
  overflow-x:hidden;
}
::selection{ background:var(--gold); color:var(--navy); }

/* ── TOPBAR ── */
.topbar{
  background:var(--navy);
  padding:9px 7%;
  display:flex; justify-content:space-between; align-items:center;
  font-size:12.5px; color:rgba(255,255,255,0.55);
  border-bottom:1px solid rgba(201,168,76,0.2);
}
.topbar a{ color:var(--gold-light); text-decoration:none; }
.topbar-right{ display:flex; gap:24px; align-items:center; }
.topbar-right a:hover{ color:#fff; }
.topbar-divider{ opacity:0.2; }

/* ── NAV ── */
nav{
  background:rgba(253,250,244,0.97);
  backdrop-filter:blur(20px);
  -webkit-backdrop-filter:blur(20px);
  border-bottom:1px solid var(--border);
  padding:0 7%; height:74px;
  display:flex; justify-content:space-between; align-items:center;
  position:sticky; top:0; z-index:900;
  transition:background .3s;
}
[data-theme="dark"] nav{ background:rgba(10,22,40,0.97); }
.logo-wrap{ display:flex; align-items:center; gap:14px; text-decoration:none; }
.logo-crest{
  width:48px; height:48px; border-radius:50%;
  background:var(--navy); display:flex; align-items:center;
  justify-content:center; font-size:20px; flex-shrink:0;
  border:2px solid var(--gold);
}
.logo-text-top{ font-family:'Playfair Display',serif; font-size:17px; font-weight:800; color:var(--navy); line-height:1; letter-spacing:0.5px; }
.logo-text-sub{ font-size:10px; color:var(--gold); font-weight:600; letter-spacing:2px; text-transform:uppercase; }
[data-theme="dark"] .logo-text-top{ color:var(--gold-light); }
.nav-links{ display:flex; gap:34px; list-style:none; align-items:center; }
.nav-links a{ font-size:14px; font-weight:500; color:var(--text-mid); text-decoration:none; transition:color .2s; position:relative; }
.nav-links a::after{ content:''; position:absolute; bottom:-4px; left:0; width:0; height:2px; background:var(--gold); transition:width .25s; border-radius:2px; }
.nav-links a:hover{ color:var(--navy); }
.nav-links a:hover::after{ width:100%; }
[data-theme="dark"] .nav-links a:hover{ color:var(--gold-light); }
.nav-btn-login{
  padding:9px 22px; border-radius:6px;
  background:var(--navy); color:#fff !important; font-weight:600 !important;
  font-size:13px !important; border:1px solid var(--navy);
  transition:background .2s,transform .15s !important;
}
.nav-btn-login:hover{ background:var(--navy-light) !important; transform:none !important; }
.nav-btn-login::after{ display:none !important; }
.theme-btn{
  width:36px; height:36px; border-radius:8px; cursor:pointer;
  background:var(--ivory-dark); border:1px solid var(--border);
  display:flex; align-items:center; justify-content:center; font-size:16px;
  transition:background .2s; user-select:none;
}
.theme-btn:hover{ background:var(--border-gold); }

/* ── HERO ── */
.hero{
  min-height:92vh;
  background:linear-gradient(160deg,#051429 0%,#0c1f3d 45%,#102848 100%);
  display:flex; align-items:center;
  padding:80px 7% 60px;
  position:relative; overflow:hidden;
}
.hero-pattern{
  position:absolute; inset:0; opacity:0.04;
  background-image:
    repeating-linear-gradient(0deg,transparent,transparent 59px,rgba(201,168,76,1) 60px),
    repeating-linear-gradient(90deg,transparent,transparent 59px,rgba(201,168,76,1) 60px);
}
.hero-glow{
  position:absolute; top:-200px; right:-100px;
  width:700px; height:700px; border-radius:50%;
  background:radial-gradient(circle,rgba(201,168,76,0.07) 0%,transparent 70%);
  pointer-events:none;
}
.hero-glow2{
  position:absolute; bottom:-200px; left:-100px;
  width:500px; height:500px; border-radius:50%;
  background:radial-gradient(circle,rgba(26,53,96,0.6) 0%,transparent 70%);
  pointer-events:none;
}
.hero-content{ position:relative; max-width:640px; }
.hero-eyebrow{
  display:inline-flex; align-items:center; gap:10px;
  background:rgba(201,168,76,0.1); border:1px solid rgba(201,168,76,0.25);
  padding:7px 18px; border-radius:4px; margin-bottom:28px;
  font-size:12px; font-weight:600; color:var(--gold-light); letter-spacing:2px; text-transform:uppercase;
}
.eyebrow-line{ width:20px; height:1px; background:var(--gold); }
.hero h1{
  font-family:'Playfair Display',serif;
  font-size:clamp(42px,5.5vw,76px); font-weight:900; line-height:1.05;
  color:#fff; margin-bottom:24px; letter-spacing:-1px;
}
.hero h1 em{ font-style:normal; color:var(--gold-light); }
.hero p{
  font-size:17px; line-height:1.8; color:rgba(255,255,255,0.55);
  max-width:520px; margin-bottom:44px;
}
.hero-btns{ display:flex; gap:16px; flex-wrap:wrap; }
.btn-gold{
  padding:15px 38px; border-radius:6px;
  background:var(--gold); color:var(--navy);
  font-weight:700; font-size:15px; text-decoration:none;
  transition:background .2s,transform .2s,box-shadow .2s;
  box-shadow:0 4px 20px rgba(201,168,76,0.35);
  border:2px solid var(--gold);
}
.btn-gold:hover{ background:var(--gold-light); transform:translateY(-2px); box-shadow:0 8px 32px rgba(201,168,76,0.45); }
.btn-ghost{
  padding:15px 38px; border-radius:6px;
  border:1px solid rgba(255,255,255,0.2); color:#fff;
  font-weight:600; font-size:15px; text-decoration:none;
  transition:background .2s,border-color .2s;
}
.btn-ghost:hover{ background:rgba(255,255,255,0.07); border-color:rgba(255,255,255,0.4); }
.hero-stats-strip{
  position:absolute; bottom:0; left:0; right:0;
  background:rgba(201,168,76,0.1); backdrop-filter:blur(10px);
  border-top:1px solid rgba(201,168,76,0.15);
  display:flex; justify-content:center; gap:0;
}
.h-stat{
  flex:1; max-width:220px; padding:22px 24px; text-align:center;
  border-right:1px solid rgba(201,168,76,0.12);
}
.h-stat:last-child{ border-right:none; }
.h-stat-num{ font-family:'Playfair Display',serif; font-size:32px; font-weight:800; color:var(--gold-light); line-height:1; }
.h-stat-label{ font-size:11px; color:rgba(255,255,255,0.4); text-transform:uppercase; letter-spacing:1.5px; margin-top:5px; }

/* ── SECTION DEFAULTS ── */
.section{ padding:96px 7%; }
.section-eyebrow{
  font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:3px;
  color:var(--gold); margin-bottom:12px; display:flex; align-items:center; gap:10px;
}
.section-eyebrow::before{ content:''; width:24px; height:1px; background:var(--gold); }
.section-title{
  font-family:'Playfair Display',serif;
  font-size:clamp(30px,4vw,48px); font-weight:800; line-height:1.15;
  letter-spacing:-1px; margin-bottom:16px;
}
.section-subtitle{ color:var(--text-muted); font-size:16px; line-height:1.8; max-width:520px; margin-bottom:56px; }

/* ── ABOUT / FEATURES ── */
.features-bg{ background:var(--ivory); }
.features-grid{ display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:24px; }
.feat-card{
  background:var(--white); border:1px solid var(--border);
  border-radius:12px; padding:34px 28px;
  transition:transform .25s,box-shadow .25s,border-color .25s;
  position:relative; overflow:hidden;
  display: block; text-decoration: none; color: inherit;
}
.feat-card::after{
  content:''; position:absolute; bottom:0; left:0; right:0; height:3px;
  background:linear-gradient(90deg,var(--gold),var(--gold-light));
  transform:scaleX(0); transform-origin:left; transition:transform .3s;
}
.feat-card:hover{ transform:translateY(-6px); box-shadow:var(--card-shadow-hover); }
.feat-card:hover::after{ transform:scaleX(1); }
.feat-icon{
  width:52px; height:52px; border-radius:12px;
  background:var(--gold-pale); display:flex; align-items:center;
  justify-content:center; font-size:24px; margin-bottom:20px;
  border:1px solid var(--border-gold);
}
.feat-card h4{ font-size:17px; font-weight:700; margin-bottom:10px; letter-spacing:-0.3px; }
.feat-card p{ font-size:14px; color:var(--text-muted); line-height:1.75; }

/* ── PORTALS ── */
.portals-bg{ background:var(--navy); }
.portals-bg .section-eyebrow{ color:var(--gold-light); }
.portals-bg .section-eyebrow::before{ background:var(--gold-light); }
.portals-bg .section-title{ color:#fff; }
.portals-bg .section-subtitle{ color:rgba(255,255,255,0.4); }
.portals-grid{ display:grid; grid-template-columns:repeat(auto-fit,minmax(290px,1fr)); gap:24px; }
.portal-card{
  background:rgba(255,255,255,0.04); border:1px solid rgba(201,168,76,0.15);
  border-radius:16px; padding:44px 36px; text-align:center;
  transition:transform .25s,background .25s,border-color .25s;
  position:relative; overflow:hidden;
}
.portal-card:hover{
  transform:translateY(-8px);
  background:rgba(201,168,76,0.07);
  border-color:rgba(201,168,76,0.4);
}
.portal-emoji{ font-size:48px; margin-bottom:20px; display:block; }
.portal-card h3{ font-family:'Playfair Display',serif; font-size:26px; font-weight:800; color:#fff; margin-bottom:14px; }
.portal-card p{ font-size:14px; color:rgba(255,255,255,0.45); line-height:1.8; margin-bottom:30px; }
.btn-portal-gold{
  display:inline-block; padding:13px 32px; border-radius:6px;
  background:var(--gold); color:var(--navy); font-weight:700; font-size:14px;
  text-decoration:none; transition:background .2s,transform .15s;
  border:2px solid var(--gold);
}
.btn-portal-gold:hover{ background:var(--gold-light); transform:scale(1.03); }

/* ── ANNOUNCEMENTS STRIP ── */
.announce-strip{
  background:var(--gold); padding:14px 7%;
  display:flex; align-items:center; gap:16px; overflow:hidden;
}
.announce-tag{
  background:var(--navy); color:var(--gold); padding:4px 12px;
  border-radius:4px; font-size:11px; font-weight:700; letter-spacing:1px;
  text-transform:uppercase; white-space:nowrap; flex-shrink:0;
}
.announce-text{ font-size:13px; font-weight:600; color:var(--navy); white-space:nowrap; }
.announce-scroll{ overflow:hidden; flex:1; }
.announce-inner{
  display:flex; gap:60px; width:max-content;
  animation:scrollText 30s linear infinite;
}
@keyframes scrollText{ 0%{transform:translateX(0)} 100%{transform:translateX(-50%)} }

/* ── CONTACT ── */
.contact-wrapper{
  display:grid; grid-template-columns:1fr 1.35fr; gap:80px; align-items:start;
}
.contact-left{}
.contact-left h2{ font-family:'Playfair Display',serif; font-size:40px; font-weight:800; letter-spacing:-1px; margin-bottom:18px; }
.contact-left p{ color:var(--text-muted); line-height:1.8; margin-bottom:40px; }
.contact-block{
  display:flex; align-items:flex-start; gap:16px; margin-bottom:28px;
  padding-bottom:28px; border-bottom:1px solid var(--border);
}
.contact-block:last-of-type{ border-bottom:none; margin-bottom:0; padding-bottom:0; }
.cb-icon{
  width:44px; height:44px; background:var(--gold-pale); border:1px solid var(--border-gold);
  border-radius:10px; display:flex; align-items:center; justify-content:center;
  font-size:20px; flex-shrink:0;
}
.cb-label{ font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1.5px; color:var(--text-muted); margin-bottom:4px; }
.cb-value{ font-size:15px; font-weight:600; color:var(--text); }
.cb-value a{ color:var(--navy-light); text-decoration:none; }
.cb-value a:hover{ color:var(--gold); }
[data-theme="dark"] .cb-value a{ color:var(--gold-light); }
.social-btns{ display:flex; gap:12px; margin-top:32px; }
.btn-whatsapp{
  display:inline-flex; align-items:center; gap:10px;
  padding:13px 24px; border-radius:8px;
  background:#25D366; color:#fff; text-decoration:none; font-weight:700; font-size:14px;
  transition:background .2s,transform .15s;
  box-shadow:0 4px 16px rgba(37,211,102,0.3);
}
.btn-whatsapp:hover{ background:#1ebe5d; transform:translateY(-2px); }
.btn-gmail{
  display:inline-flex; align-items:center; gap:10px;
  padding:13px 24px; border-radius:8px;
  background:#EA4335; color:#fff; text-decoration:none; font-weight:700; font-size:14px;
  transition:background .2s,transform .15s;
  box-shadow:0 4px 16px rgba(234,67,53,0.3);
}
.btn-gmail:hover{ background:#d33426; transform:translateY(-2px); }

/* CONTACT FORM */
.contact-form-card{
  background:var(--white); border:1px solid var(--border);
  border-radius:16px; padding:44px;
  box-shadow:var(--card-shadow);
}
.form-title{ font-family:'Playfair Display',serif; font-size:24px; font-weight:800; margin-bottom:8px; }
.form-subtitle{ font-size:14px; color:var(--text-muted); margin-bottom:32px; }
.form-row{ display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.form-group{ margin-bottom:18px; }
.form-group label{
  display:block; font-size:12px; font-weight:700; text-transform:uppercase;
  letter-spacing:1px; margin-bottom:8px; color:var(--text-mid);
}
.form-group input, .form-group select, .form-group textarea{
  width:100%; padding:13px 16px; border-radius:8px;
  border:1.5px solid var(--border); background:var(--ivory);
  color:var(--text); font-family:'DM Sans',sans-serif; font-size:14px;
  transition:border-color .2s,box-shadow .2s; outline:none;
}
.form-group select{ cursor:pointer; }
.form-group input:focus, .form-group select:focus, .form-group textarea:focus{
  border-color:var(--gold);
  box-shadow:0 0 0 3px rgba(201,168,76,0.12);
}
.form-group textarea{ resize:vertical; min-height:130px; }
.req{ color:var(--gold); }
.alert{
  padding:13px 16px; border-radius:8px; font-size:14px; margin-bottom:20px;
  display:flex; align-items:flex-start; gap:10px;
}
.alert-error{ background:rgba(239,68,68,0.07); border:1px solid rgba(239,68,68,0.2); color:#b91c1c; }
.alert-success{ background:rgba(22,163,74,0.07); border:1px solid rgba(22,163,74,0.2); color:#166534; }
.btn-send{
  width:100%; padding:15px; border-radius:8px; border:none;
  background:var(--navy); color:#fff; font-family:'DM Sans',sans-serif;
  font-weight:700; font-size:15px; cursor:pointer; letter-spacing:0.3px;
  transition:background .2s,transform .1s;
  display:flex; align-items:center; justify-content:center; gap:10px;
}
.btn-send:hover{ background:var(--navy-light); }
.btn-send:active{ transform:scale(0.99); }
.form-note{ font-size:12px; color:var(--text-muted); text-align:center; margin-top:14px; }

/* ── MAP PLACEHOLDER ── */
.map-section{ background:var(--ivory-dark); }
.map-inner{ display:grid; grid-template-columns:1fr 2fr; gap:48px; align-items:center; }
.map-info h3{ font-family:'Playfair Display',serif; font-size:28px; font-weight:800; margin-bottom:12px; }
.map-info p{ color:var(--text-muted); font-size:14px; line-height:1.8; margin-bottom:24px; }
.map-frame{
  border-radius:16px; overflow:hidden; border:1px solid var(--border);
  height:320px; box-shadow:var(--card-shadow);
}
.map-frame iframe{ width:100%; height:100%; border:none; }

/* ── FOOTER ── */
footer{
  background:var(--navy); color:rgba(255,255,255,0.4);
  padding:60px 7% 28px;
}
.footer-grid{ display:grid; grid-template-columns:2fr 1fr 1fr 1fr; gap:48px; margin-bottom:48px; }
.footer-brand .logo-text-top{ color:var(--gold-light); font-size:20px; }
.footer-brand p{ font-size:13px; color:rgba(255,255,255,0.35); line-height:1.8; margin-top:12px; max-width:280px; }
.footer-col h5{ font-size:12px; font-weight:700; letter-spacing:2px; text-transform:uppercase; color:var(--gold-light); margin-bottom:18px; }
.footer-col ul{ list-style:none; }
.footer-col ul li{ margin-bottom:10px; }
.footer-col ul li a{ color:rgba(255,255,255,0.4); text-decoration:none; font-size:13px; transition:color .2s; }
.footer-col ul li a:hover{ color:var(--gold-light); }
.footer-bottom{
  border-top:1px solid rgba(255,255,255,0.06); padding-top:24px;
  display:flex; justify-content:space-between; align-items:center; font-size:12px;
  flex-wrap:wrap; gap:12px;
}
.footer-accredit{ display:flex; align-items:center; gap:8px; color:rgba(255,255,255,0.25); }
.accredit-badge{
  padding:4px 10px; background:rgba(201,168,76,0.1); border:1px solid rgba(201,168,76,0.2);
  border-radius:4px; font-size:11px; color:var(--gold-light); font-weight:600;
}

/* ── CHAT WIDGET ── */
.chat-widget{ position:fixed; bottom:28px; right:28px; z-index:1100; }
.chat-fab{
  width:60px; height:60px; background:var(--navy); border-radius:16px;
  display:flex; align-items:center; justify-content:center; font-size:26px; cursor:pointer;
  box-shadow:0 8px 32px rgba(12,31,61,0.35); border:2px solid var(--gold);
  transition:transform .2s,box-shadow .2s; user-select:none;
}
.chat-fab:hover{ transform:scale(1.07); box-shadow:0 12px 40px rgba(12,31,61,0.5); }
.chat-notif{
  position:absolute; top:-6px; right:-6px; width:20px; height:20px;
  background:#ef4444; border-radius:50%; border:2px solid var(--ivory);
  display:flex; align-items:center; justify-content:center;
  font-size:10px; color:#fff; font-weight:700;
}
.chat-window{
  position:absolute; bottom:76px; right:0; width:380px;
  background:var(--white); border-radius:20px;
  box-shadow:0 24px 64px rgba(12,31,61,0.2);
  display:none; flex-direction:column; overflow:hidden;
  border:1px solid var(--border); max-height:540px;
  animation:chatPop .2s ease;
}
@keyframes chatPop{ from{opacity:0;transform:scale(0.95) translateY(8px)} to{opacity:1;transform:scale(1) translateY(0)} }
.chat-window.open{ display:flex; }
.chat-header{
  background:var(--navy); padding:18px 20px;
  display:flex; align-items:center; justify-content:space-between;
  border-bottom:1px solid rgba(201,168,76,0.2);
}
.chat-header-left{ display:flex; align-items:center; gap:12px; }
.chat-avatar-wrap{ position:relative; }
.chat-av{
  width:38px; height:38px; border-radius:50%;
  background:rgba(201,168,76,0.2); border:2px solid var(--gold);
  display:flex; align-items:center; justify-content:center; font-size:18px;
}
.chat-online{
  position:absolute; bottom:0; right:0; width:10px; height:10px;
  background:#4ade80; border-radius:50%; border:2px solid var(--navy);
}
.chat-hname{ font-size:15px; font-weight:700; color:#fff; }
.chat-hstatus{ font-size:11px; color:rgba(255,255,255,0.5); margin-top:2px; }
.chat-x{ cursor:pointer; color:rgba(255,255,255,0.5); font-size:18px; transition:color .2s; padding:4px; }
.chat-x:hover{ color:#fff; }
.chat-body{
  flex:1; padding:20px; overflow-y:auto;
  display:flex; flex-direction:column; gap:14px;
  background:var(--ivory);
}
.chat-msg{ padding:12px 16px; border-radius:14px; max-width:84%; font-size:14px; line-height:1.6; word-break:break-word; }
.msg-bot{
  background:var(--white); color:var(--text); align-self:flex-start;
  border:1px solid var(--border); border-bottom-left-radius:4px;
  box-shadow:0 1px 4px rgba(0,0,0,0.05);
}
.msg-user{ background:var(--navy); color:#fff; align-self:flex-end; border-bottom-right-radius:4px; }
.msg-time{ font-size:10px; color:var(--text-muted); margin-top:4px; align-self:flex-end; }
.typing-wrap{ display:flex; align-items:center; gap:5px; padding:10px 14px; }
.t-dot{ width:7px; height:7px; background:var(--text-muted); border-radius:50%; animation:td 1.2s infinite; }
.t-dot:nth-child(2){ animation-delay:.2s; }
.t-dot:nth-child(3){ animation-delay:.4s; }
@keyframes td{ 0%,60%,100%{transform:translateY(0)} 30%{transform:translateY(-7px)} }
.chat-suggestions{
  display:flex; flex-wrap:wrap; gap:8px; padding:0 20px 14px;
  background:var(--ivory);
}
.sug-btn{
  padding:7px 14px; background:var(--white); border:1px solid var(--border);
  border-radius:20px; font-size:12px; font-weight:500; color:var(--text-mid);
  cursor:pointer; transition:all .2s; font-family:'DM Sans',sans-serif;
}
.sug-btn:hover{ background:var(--gold-pale); border-color:var(--gold); color:var(--navy); }
.chat-footer{
  padding:14px 16px; display:flex; gap:10px;
  border-top:1px solid var(--border); background:var(--white);
}
.chat-in{
  flex:1; padding:11px 16px; border-radius:10px;
  border:1.5px solid var(--border); background:var(--ivory);
  color:var(--text); font-size:14px; font-family:'DM Sans',sans-serif;
  outline:none; transition:border-color .2s;
}
.chat-in:focus{ border-color:var(--gold); }
.chat-btn-send{
  padding:11px 20px; border-radius:10px; border:none;
  background:var(--navy); color:#fff; font-weight:700; font-size:13px;
  cursor:pointer; transition:background .2s; font-family:'DM Sans',sans-serif;
  display:flex; align-items:center; gap:6px;
}
.chat-btn-send:hover{ background:var(--navy-light); }
.chat-btn-send:disabled{ opacity:.45; cursor:not-allowed; }

/* ── WHATSAPP FLOAT ── */
.wa-float{
  position:fixed; bottom:100px; right:30px; z-index:1050;
  width:52px; height:52px; background:#25D366; border-radius:50%;
  display:flex; align-items:center; justify-content:center; font-size:26px;
  box-shadow:0 6px 24px rgba(37,211,102,0.45); text-decoration:none;
  transition:transform .2s,box-shadow .2s; animation:waPulse 2s infinite;
}
.wa-float:hover{ transform:scale(1.1); box-shadow:0 10px 32px rgba(37,211,102,0.55); animation:none; }
@keyframes waPulse{
  0%,100%{box-shadow:0 6px 24px rgba(37,211,102,0.45)}
  50%{box-shadow:0 6px 24px rgba(37,211,102,0.45),0 0 0 10px rgba(37,211,102,0.1)}
}

/* ── ANIMATIONS ── */
.reveal{ opacity:0; transform:translateY(28px); transition:opacity .7s ease,transform .7s ease; }
.reveal.show{ opacity:1; transform:translateY(0); }

/* ── RESPONSIVE ── */
@media(max-width:900px){
  .contact-wrapper{ grid-template-columns:1fr; gap:48px; }
  .footer-grid{ grid-template-columns:1fr 1fr; }
  .map-inner{ grid-template-columns:1fr; }
  .topbar{ display:none; }
  .nav-links{ gap:16px; }
  .nav-links li:first-child,
  .nav-links li:nth-child(2){ display:none; }
  .hero-stats-strip .h-stat:nth-child(4){ display:none; }
}
@media(max-width:600px){
  .chat-window{ width:calc(100vw - 40px); right:-8px; }
  .hero h1{ font-size:38px; }
  .form-row{ grid-template-columns:1fr; }
  .footer-grid{ grid-template-columns:1fr; }
  .hero-stats-strip{ display:none; }
  .social-btns{ flex-direction:column; }
}
</style>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
  <span>🏛️ Accredited by the Kenya National Qualifications Authority (KNQA)</span>
  <div class="topbar-right">
    <a href="tel:+254740200024">📞 0740 200 024</a>
    <span class="topbar-divider">|</span>
    <a href="mailto:info@kiharutechnical.ac.ke">✉️ info@kiharutechnical.ac.ke</a>
    <span class="topbar-divider">|</span>
    <span>Mon–Fri 8:00 AM – 5:00 PM</span>
  </div>
</div>

<!-- NAV -->
<nav>
  <a href="index.php" class="logo-wrap">
    <div class="logo-crest">🎓</div>
    <div>
      <div class="logo-text-top">Kiharu College</div>
      <div class="logo-text-sub">Est. 2012</div>
    </div>
  </a>
  <ul class="nav-links">
    <li><a href="#home">Home</a></li>
    <li><a href="#programmes">Programmes</a></li>
    <li><a href="#portals">Portals</a></li>
    <li><a href="#contact">Contact</a></li>
    <li><a href="student/login.php" class="nav-btn-login">Student Login</a></li>
    <li><div class="theme-btn" id="themeBtn" onclick="toggleTheme()">🌙</div></li>
  </ul>
</nav>

<!-- ANNOUNCEMENTS STRIP -->
<div class="announce-strip">
  <div class="announce-tag">📢 Live</div>
  <div class="announce-scroll">
    <div class="announce-inner">
      <span class="announce-text">📅 Semester 2 Registration: April 15 – May 3, 2025</span>
      <span class="announce-text">🎓 Graduation Ceremony: June 28, 2025 — Main Campus</span>
      <span class="announce-text">💳 M-Pesa Fee Payment Now Available — Paybill: 522522</span>
      <span class="announce-text">📋 HELB Applications Open — Visit the Finance Office</span>
      <span class="announce-text">📅 Semester 2 Registration: April 15 – May 3, 2025</span>
      <span class="announce-text">🎓 Graduation Ceremony: June 28, 2025 — Main Campus</span>
      <span class="announce-text">💳 M-Pesa Fee Payment Now Available — Paybill: 522522</span>
      <span class="announce-text">📋 HELB Applications Open — Visit the Finance Office</span>
    </div>
  </div>
</div>

<!-- HERO -->
<section class="hero" id="home">
  <div class="hero-pattern"></div>
  <div class="hero-glow"></div>
  <div class="hero-glow2"></div>
  <div class="hero-content">
    <div class="hero-eyebrow"><span class="eyebrow-line"></span>Academic Year 2024/2025</div>
    <h1>Where <em>Excellence</em><br>Meets Opportunity.</h1>
    <p>Kiharu College offers world-class education in a supportive environment. Manage your academics, finances, and future — all in one portal.</p>
    <div class="hero-btns">
      <a href="#portals" class="btn-gold">Access Your Portal</a>
      <a href="#contact" class="btn-ghost">Enquire Now</a>
    </div>
  </div>
  <div class="hero-stats-strip">
    <div class="h-stat">
      <div class="h-stat-num counter" data-target="<?= $student_count ?>">0</div>
      <div class="h-stat-label">Enrolled Students</div>
    </div>
    <div class="h-stat">
      <div class="h-stat-num counter" data-target="<?= $course_count ?>">0</div>
      <div class="h-stat-label">Active Courses</div>
    </div>
    <div class="h-stat">
      <div class="h-stat-num">98%</div>
      <div class="h-stat-label">Graduate Employment</div>
    </div>
    <div class="h-stat">
      <div class="h-stat-num">12+</div>
      <div class="h-stat-label">Years of Excellence</div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="section features-bg reveal" id="programmes">
  <div class="section-eyebrow">Why Kiharu</div>
  <div class="section-title">Built for Your Success</div>
  <div class="section-subtitle">From digital learning tools to real-time financial management, we've built a campus that works for you.</div>
  <div class="features-grid">
    <a href="#contact" class="feat-card">
      <div class="feat-icon">📱</div>
      <h4>Digital-First Campus</h4>
      <p>Submit assignments, register for units, and access lecture materials from any device, anywhere in the world.</p>
    </a>
    <a href="#contact" class="feat-card">
      <div class="feat-icon">💳</div>
      <h4>Instant M-Pesa Payments</h4>
      <p>Pay fees instantly via M-Pesa STK push. Receipts issued automatically — no queues, no delays.</p>
    </a>
    <a href="#contact" class="feat-card">
      <div class="feat-icon">📊</div>
      <h4>Academic Analytics</h4>
      <p>Track your GPA trajectory with automated visual charts and detailed semester-by-semester breakdowns.</p>
    </a>
    <a href="#contact" class="feat-card">
      <div class="feat-icon">🔔</div>
      <h4>Smart Notifications</h4>
      <p>SMS and portal alerts for results, timetable changes, fee deadlines, and official announcements.</p>
    </a>
    <a href="#contact" class="feat-card">
      <div class="feat-icon">🏆</div>
      <h4>KNQA Accredited</h4>
      <p>All programmes are nationally accredited. Your qualifications are recognised across Kenya and internationally.</p>
    </a>
    <a href="#contact" class="feat-card">
      <div class="feat-icon">🤝</div>
      <h4>HELB Eligible</h4>
      <p>Fully registered with the Higher Education Loans Board. Apply for funding support directly through our office.</p>
    </a>
  </div>
</section>

<!-- PORTALS -->
<section class="section portals-bg reveal" id="portals">
  <div class="section-eyebrow">Access</div>
  <div class="section-title">Choose Your Portal</div>
  <div class="section-subtitle">Dedicated dashboards for every member of the Kiharu community.</div>
  <div class="portals-grid">
    <div class="portal-card">
      <span class="portal-emoji">🎓</span>
      <h3>Student Portal</h3>
      <p>View grades, register for units, track fee balance, download results slips, and manage your academic journey end-to-end.</p>
      <a href="student/login.php" class="btn-portal-gold">Student Login →</a>
    </div>
    <div class="portal-card">
      <span class="portal-emoji">👨‍🏫</span>
      <h3>Lecturer Portal</h3>
      <p>Manage class lists, upload assignments, enter marks, generate unit reports, and communicate with students efficiently.</p>
      <a href="lecturer_login.php" class="btn-portal-gold">Lecturer Login →</a>
    </div>
    <div class="portal-card">
      <span class="portal-emoji">⚙️</span>
      <h3>Admin Portal</h3>
      <p>Oversee institutional operations, generate financial reports, manage users, and access the full analytics dashboard.</p>
      <a href="admin_login.php" class="btn-portal-gold">Admin Login →</a>
    </div>
  </div>
</section>

<!-- MAP -->
<section class="section map-section reveal">
  <div class="map-inner">
    <div class="map-info">
      <div class="section-eyebrow">Location</div>
      <h3>Find Us on Campus</h3>
      <p>Conveniently located in Kiharu, Murang'a County. We're accessible via public transport from Nairobi, Thika, and surrounding towns.</p>
      <div style="display:flex;flex-direction:column;gap:10px;font-size:14px;color:var(--text-mid);">
        <span>📍 Kiharu Town, Murang'a County, Kenya</span>
        <span>🚌 45 minutes from Thika Town CBD</span>
        <span>🕗 Office Hours: Mon–Fri, 8:00 AM – 5:00 PM</span>
      </div>
    </div>
    <div class="map-frame">
      <iframe
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15955.5!2d37.15!3d-0.73!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMC43M1MgMzcuMTVF!5e0!3m2!1sen!2ske!4v1234567890"
        allowfullscreen="" loading="lazy"
        referrerpolicy="no-referrer-when-downgrade">
      </iframe>
    </div>
  </div>
</section>

<!-- CONTACT -->
<section class="section reveal" id="contact">
  <div class="contact-wrapper">

    <!-- LEFT: Info -->
    <div class="contact-left">
      <div class="section-eyebrow">Get In Touch</div>
      <h2>We're Here<br>to Help You.</h2>
      <p>Whether you have questions about admissions, fees, or your portal — our support team responds within 24 hours on business days.</p>

      <div class="contact-block">
        <div class="cb-icon">📍</div>
        <div>
          <div class="cb-label">Address</div>
          <div class="cb-value">Kiharu Town, Murang'a County, Kenya<br>P.O. Box 1234–10200</div>
        </div>
      </div>

      <div class="contact-block">
        <div class="cb-icon">📞</div>
        <div>
          <div class="cb-label">Phone / WhatsApp</div>
          <div class="cb-value">
            <a href="tel:+254740200024">0740 200 024</a><br>
            <a href="tel:+254733000000">+254 733 000 000</a>
          </div>
        </div>
      </div>

      <div class="contact-block">
        <div class="cb-icon">✉️</div>
        <div>
          <div class="cb-label">Email</div>
          <div class="cb-value">
            <a href="mailto:info@kiharutechnical.ac.ke">info@kiharutechnical.ac.ke</a><br>
            <a href="mailto:admissions@kiharutechnical.ac.ke">admissions@kiharutechnical.ac.ke</a>
          </div>
        </div>
      </div>

      <div class="contact-block">
        <div class="cb-icon">🕗</div>
        <div>
          <div class="cb-label">Office Hours</div>
          <div class="cb-value">Monday – Friday: 8:00 AM – 5:00 PM<br>Saturday: 9:00 AM – 1:00 PM</div>
        </div>
      </div>

      <div class="social-btns">
        <a href="https://wa.me/254740200024?text=Hello%20Kiharu%20Technical%2C%20I%20have%20an%20enquiry." target="_blank" class="btn-whatsapp">
          💬 WhatsApp Us
        </a>
        <a href="mailto:info@kiharucollege.ac.ke" class="btn-gmail">
          📧 Email Us
        </a>
      </div>
    </div>

    <!-- RIGHT: Form -->
    <div class="contact-form-card">
      <div class="form-title">Send Us a Message</div>
      <div class="form-subtitle">Fill in the form below and we'll get back to you promptly.</div>

      <?php if ($contact_error): ?>
        <div class="alert alert-error">⚠️ <?= htmlspecialchars($contact_error) ?></div>
      <?php endif; ?>
      <?php if ($contact_success): ?>
        <div class="alert alert-success">✅ <?= htmlspecialchars($contact_success) ?></div>
      <?php endif; ?>

      <form method="POST" action="#contact" id="contactForm" novalidate>
        <div class="form-row">
          <div class="form-group">
            <label for="fname">Full Name <span class="req">*</span></label>
            <input type="text" id="fname" name="name" placeholder="e.g. James Kamau"
                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label for="femail">Email Address <span class="req">*</span></label>
            <input type="email" id="femail" name="email" placeholder="you@example.com"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="fphone">Phone Number</label>
            <input type="tel" id="fphone" name="phone" placeholder="+254 7XX XXX XXX"
                   value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label for="fsubject">Subject <span class="req">*</span></label>
            <select id="fsubject" name="subject">
              <option value="" disabled <?= empty($_POST['subject']) ? 'selected' : '' ?>>Select a topic</option>
              <option value="Admissions Enquiry"       <?= ($_POST['subject'] ?? '') === 'Admissions Enquiry'       ? 'selected' : '' ?>>Admissions Enquiry</option>
              <option value="Fee Payment Support"      <?= ($_POST['subject'] ?? '') === 'Fee Payment Support'      ? 'selected' : '' ?>>Fee Payment Support</option>
              <option value="Course Registration"      <?= ($_POST['subject'] ?? '') === 'Course Registration'      ? 'selected' : '' ?>>Course Registration</option>
              <option value="Portal Technical Support" <?= ($_POST['subject'] ?? '') === 'Portal Technical Support' ? 'selected' : '' ?>>Portal Technical Support</option>
              <option value="HELB / Financial Aid"     <?= ($_POST['subject'] ?? '') === 'HELB / Financial Aid'     ? 'selected' : '' ?>>HELB / Financial Aid</option>
              <option value="General Enquiry"          <?= ($_POST['subject'] ?? '') === 'General Enquiry'          ? 'selected' : '' ?>>General Enquiry</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label for="fmessage">Message <span class="req">*</span></label>
          <textarea id="fmessage" name="message" placeholder="Describe how we can help you..." required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
        </div>
        <button type="submit" name="contact_submit" class="btn-send" id="sendBtn">
          <span>Send Message</span> <span>→</span>
        </button>
        <p class="form-note">🔒 Your information is kept private and never shared with third parties.</p>
      </form>
    </div>

  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="footer-grid">
    <div class="footer-brand">
      <div class="logo-wrap" style="text-decoration:none; display:flex; align-items:center; gap:12px; margin-bottom:12px;">
        <div class="logo-crest" style="width:40px;height:40px;font-size:18px;">🎓</div>
        <div class="logo-text-top">Kiharu College</div>
      </div>
      <p>Providing quality education and empowering students since 2012. KNQA accredited, HELB registered.</p>
    </div>
    <div class="footer-col">
      <h5>Portals</h5>
      <ul>
        <li><a href="student/login.php">Student Portal</a></li>
        <li><a href="lecturer_login.php">Lecturer Portal</a></li>
        <li><a href="admin_login.php">Admin Portal</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h5>Quick Links</h5>
      <ul>
        <li><a href="#home">Home</a></li>
        <li><a href="#programmes">Programmes</a></li>
        <li><a href="#contact">Contact Us</a></li>
        <li><a href="#">Fee Structure</a></li>
        <li><a href="#">Academic Calendar</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h5>Contact</h5>
      <ul>
        <li><a href="tel:+254740200024">0740 200 024</a></li>
        <li><a href="mailto:info@kiharutechnical.ac.ke">info@kiharutechnical.ac.ke</a></li>
        <li><a href="https://wa.me/254740200024" target="_blank">WhatsApp Support</a></li>
        <li><a href="#">Kiharu, Murang'a County</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <span>© <?= date('Y') ?> Kiharu College. All Rights Reserved.</span>
    <div class="footer-accredit">
      <span>Accredited by</span>
      <span class="accredit-badge">KNQA</span>
      <span class="accredit-badge">HELB</span>
      <span class="accredit-badge">ISO 9001</span>
    </div>
  </div>
</footer>

<!-- WHATSAPP FLOAT BUTTON -->
<a href="https://wa.me/254740200024?text=Hello%20Kiharu%20Technical%2C%20I%20have%20an%20enquiry." target="_blank" class="wa-float" title="Chat on WhatsApp">💬</a>

<!-- AI CHAT WIDGET -->
<div class="chat-widget">
  <div class="chat-window" id="chatWindow">
    <div class="chat-header">
      <div class="chat-header-left">
        <div class="chat-avatar-wrap">
          <div class="chat-av">🎓</div>
          <div class="chat-online"></div>
        </div>
        <div>
          <div class="chat-hname">Kiharu AI Assistant</div>
          <div class="chat-hstatus">Online · Powered by AI</div>
        </div>
      </div>
      <span class="chat-x" onclick="toggleChat()">✕</span>
    </div>
    <div class="chat-body" id="chatBody">
      <div class="chat-msg msg-bot">👋 Welcome to Kiharu College! I can help with admissions, portal navigation, fees, courses, and more. How can I assist you today?</div>
    </div>
    <div class="chat-suggestions" id="chatSuggestions">
      <button class="sug-btn" onclick="quickAsk('How do I register for units?')">Unit registration</button>
      <button class="sug-btn" onclick="quickAsk('How do I pay fees via M-Pesa?')">M-Pesa payment</button>
      <button class="sug-btn" onclick="quickAsk('How do I apply for admission?')">Admissions</button>
      <button class="sug-btn" onclick="quickAsk('What courses do you offer?')">Courses offered</button>
    </div>
    <div class="chat-footer">
      <input type="text" id="chatInput" class="chat-in" placeholder="Type your question..."
             onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendChat();}">
      <button class="chat-btn-send" id="chatSend" onclick="sendChat()">Send ↗</button>
    </div>
  </div>
  <div class="chat-fab" onclick="toggleChat()">
    💬
    <div class="chat-notif" id="chatNotif">1</div>
  </div>
</div>

<script>
/* ── THEME ──────────────────────────────── */
function toggleTheme(){
  const dark = document.body.getAttribute('data-theme')==='dark';
  document.body.setAttribute('data-theme', dark ? '' : 'dark');
  document.getElementById('themeBtn').textContent = dark ? '🌙' : '☀️';
  localStorage.setItem('kc_theme', dark ? 'light' : 'dark');
}
(function(){
  if(localStorage.getItem('kc_theme')==='dark'){
    document.body.setAttribute('data-theme','dark');
    document.getElementById('themeBtn').textContent='☀️';
  }
})();

/* ── SCROLL REVEAL ──────────────────────── */
const revealEls = document.querySelectorAll('.reveal');
function doReveal(){
  revealEls.forEach(el=>{
    if(el.getBoundingClientRect().top < window.innerHeight - 60) el.classList.add('show');
  });
}
window.addEventListener('scroll', doReveal, {passive:true}); doReveal();

/* ── COUNTERS ───────────────────────────── */
const statsObs = new IntersectionObserver(entries=>{
  if(!entries[0].isIntersecting) return;
  document.querySelectorAll('.counter').forEach(el=>{
    const target = +el.dataset.target || 0;
    if(target===0){ el.textContent='0'; return; }
    let v=0; const step=Math.max(1,Math.ceil(target/80));
    const t=setInterval(()=>{
      v=Math.min(v+step,target);
      el.textContent=v.toLocaleString();
      if(v>=target) clearInterval(t);
    },14);
  });
  statsObs.disconnect();
},{threshold:0.3});
const heroStats = document.querySelector('.hero-stats-strip');
if(heroStats) statsObs.observe(heroStats);

/* ── CHAT ───────────────────────────────── */
let chatHistory = [];
const SYS = `You are a warm, knowledgeable AI assistant for Kiharu College (Kiharu, Murang'a County, Kenya).
Help students, lecturers, and staff with:
- Admissions: requirements, application process, intake dates
- Courses & programmes offered at the college
- Unit registration: how to register, deadlines, prerequisites  
- Fee payments: M-Pesa Paybill 522522, HELB loans, fee structure
- Portal navigation: student, lecturer, and admin portals
- Academic matters: results, GPA, transcripts, timetables
- College contacts: 0740 200 024, info@kiharutechnical.ac.ke
- WhatsApp: 0740 200 024

Be concise, friendly, and professional. Use short paragraphs.
If asked about something you don't know specifically, say you'll connect them with a human agent.
Do NOT invent specific figures, fees, or dates you're unsure about.`;

function toggleChat(){
  const w = document.getElementById('chatWindow');
  w.classList.toggle('open');
  document.getElementById('chatNotif').style.display='none';
  if(w.classList.contains('open')) document.getElementById('chatInput').focus();
}

function addMsg(text, role){
  const body=document.getElementById('chatBody');
  const d=document.createElement('div');
  d.className='chat-msg '+(role==='user'?'msg-user':'msg-bot');
  d.textContent=text;
  body.appendChild(d);
  body.scrollTop=body.scrollHeight;
}

function showTyping(){
  const body=document.getElementById('chatBody');
  const d=document.createElement('div');
  d.className='chat-msg msg-bot typing-wrap'; d.id='typingDots';
  d.innerHTML='<div class="t-dot"></div><div class="t-dot"></div><div class="t-dot"></div>';
  body.appendChild(d); body.scrollTop=body.scrollHeight;
}
function removeTyping(){
  const t=document.getElementById('typingDots'); if(t) t.remove();
}

async function sendChat(){
  const inp=document.getElementById('chatInput');
  const btn=document.getElementById('chatSend');
  const text=inp.value.trim(); if(!text) return;

  // Hide suggestions after first message
  document.getElementById('chatSuggestions').style.display='none';

  inp.value=''; addMsg(text,'user');
  chatHistory.push({role:'user',content:text});
  btn.disabled=true; inp.disabled=true; showTyping();

  try{
    const res = await fetch('chatbox.php',{
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body:JSON.stringify({ message: text })
    });
    const data=await res.json();
    removeTyping();
    if(data.reply){
      const reply=data.reply;
      addMsg(reply,'bot');
    } else {
      addMsg('Sorry, something went wrong. Please try again or contact us directly.','bot');
    }
  } catch(e){
    removeTyping();
    addMsg('Connection issue. You can reach us at +254 700 000 000 or WhatsApp us!','bot');
  } finally {
    btn.disabled=false; inp.disabled=false; inp.focus();
  }
}

function quickAsk(q){
  document.getElementById('chatInput').value=q;
  sendChat();
}

/* ── CLIENT-SIDE FORM VALIDATION ─────── */
document.getElementById('contactForm')?.addEventListener('submit', function(e){
  const btn = document.getElementById('sendBtn');
  const name = document.getElementById('fname').value.trim();
  const email = document.getElementById('femail').value.trim();
  const subject = document.getElementById('fsubject').value;
  const message = document.getElementById('fmessage').value.trim();
  if(!name||!email||!subject||!message){ return; }
  btn.innerHTML='<span>Sending...</span>';
  btn.style.opacity='0.7';
});
</script>
</body>
</html>