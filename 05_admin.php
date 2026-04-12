<?php
require_once __DIR__ . '/config.php';
if (empty($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit; }

if (isset($_POST['update_status'])) {
    sb('PATCH','leads',['status'=>$_POST['status']],'id=eq.'.(int)$_POST['lead_id']);
    header('Location: admin.php'.($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:'')); exit;
}
if (isset($_POST['delete_lead'])) {
    sb('DELETE','leads',null,'id=eq.'.(int)$_POST['lead_id']);
    header('Location: admin.php'); exit;
}

$res    = sb('GET','leads',null,'order=id.desc&select=*');
$all    = is_array($res['data']) ? $res['data'] : [];
$sb_ok  = $res['code'] >= 200 && $res['code'] < 300;
$sb_err = $res['raw'];
$total     = count($all);
$today     = count(array_filter($all, fn($r) => substr($r['created_at'],0,10)===date('Y-m-d')));
$this_week = count(array_filter($all, fn($r) => strtotime($r['created_at'])>=strtotime('-7 days')));
$cnt = ['New'=>0,'Contacted'=>0,'Closed'=>0,'Lost'=>0];
foreach ($all as $r) { $s=$r['status']??'New'; if(isset($cnt[$s])) $cnt[$s]++; }
$new_count = $cnt['New'];
$search = trim($_GET['q']??''); $filter = trim($_GET['status']??'');
$leads = array_filter($all, function($r) use ($search,$filter) {
    if ($filter && ($r['status']??'')!==$filter) return false;
    if ($search) {
        $s=strtolower($search);
        return str_contains(strtolower($r['name']??''),$s)
            || str_contains(strtolower($r['email']??''),$s)
            || str_contains(strtolower($r['phone']??''),$s)
            || str_contains(strtolower($r['address']??''),$s);
    }
    return true;
});
$count = count($leads);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Leads Dashboard | Great Properties GA</title>
  <link rel="manifest" href="/manifest.json">
  <meta name="theme-color" content="#cc0000">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="GP Admin">
  <link rel="apple-touch-icon" href="/icon-512.svg">
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:Arial,sans-serif;background:#f0f2f5;color:#222;overflow-x:hidden}

    /* ── SIDEBAR ── */
    .sidebar{
      position:fixed;top:0;left:0;bottom:0;width:230px;
      background:#111;display:flex;flex-direction:column;
      z-index:300;overflow-y:auto;
      transition:transform .28s ease;
    }
    .s-logo{padding:22px 20px;font-size:15px;font-weight:bold;border-bottom:1px solid #1e1e1e;color:#fff}
    .s-logo span{color:#ffd700}
    .s-logo small{display:block;color:#555;font-size:11px;font-weight:normal;margin-top:2px}
    .sidebar nav{flex:1;padding:12px 0}
    .nav-a{display:flex;align-items:center;padding:13px 20px;color:#777;text-decoration:none;font-size:14px;border-left:3px solid transparent;transition:all .15s;gap:8px}
    .nav-a:hover,.nav-a.on{background:#1a1a1a;color:#fff;border-left-color:#cc0000}
    .nav-label{flex:1}
    .nav-badge{display:inline-flex;align-items:center;justify-content:center;min-width:22px;height:22px;padding:0 6px;border-radius:11px;font-size:11px;font-weight:bold;background:#2a2a2a;color:#888;transition:all .15s}
    .nav-a:hover .nav-badge,.nav-a.on .nav-badge,.nav-badge.hot{background:#cc0000;color:#fff}
    .install-wrap{padding:12px 16px;border-top:1px solid #1e1e1e}
    .btn-pwa{width:100%;background:linear-gradient(135deg,#cc0000,#990000);color:#fff;border:none;padding:11px;border-radius:8px;font-size:13px;font-weight:bold;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px}
    .s-foot{padding:14px 20px;border-top:1px solid #1e1e1e;font-size:12px;color:#555}
    .s-foot a{color:#777;text-decoration:none}.s-foot a:hover{color:#fff}

    /* ── OVERLAY (mobile) ── */
    .overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:299;}
    .overlay.open{display:block;}

    /* ── MAIN ── */
    .main{margin-left:230px;min-height:100vh}
    .topbar{
      background:#fff;padding:14px 20px;
      display:flex;align-items:center;justify-content:space-between;
      border-bottom:1px solid #eee;position:sticky;top:0;z-index:50;
      box-shadow:0 1px 4px rgba(0,0,0,.06);
    }
    .topbar-left{display:flex;align-items:center;gap:12px}
    .hamburger{
      display:none;background:none;border:none;
      cursor:pointer;padding:6px;border-radius:6px;
      flex-direction:column;gap:5px;
    }
    .hamburger span{
      display:block;width:22px;height:2px;
      background:#333;border-radius:2px;
      transition:all .2s;
    }
    .topbar h1{font-size:18px;color:#111}
    .topbar-right{display:flex;align-items:center;gap:10px}
    .topbar-date{font-size:12px;color:#aaa}
    .btn-out{background:#f5f5f5;color:#444;border:none;padding:8px 14px;border-radius:6px;font-size:13px;cursor:pointer;text-decoration:none;font-weight:bold}
    .btn-out:hover{background:#eee}

    /* ── CONTENT ── */
    .content{padding:22px}
    .alert{background:#fff0f0;border:1px solid #fca5a5;color:#b00;padding:14px 18px;border-radius:8px;margin-bottom:18px;font-size:13px}
    .alert code{display:block;background:#ffe;padding:6px 10px;border-radius:4px;margin-top:8px;font-size:11px;word-break:break-all;color:#555}

    /* ── STATS ── */
    .stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:12px;margin-bottom:18px}
    .sc{background:#fff;border-radius:12px;padding:16px;box-shadow:0 1px 6px rgba(0,0,0,.06);border-left:4px solid #ddd}
    .sc.r{border-color:#cc0000}.sc.g{border-color:#22c55e}.sc.b{border-color:#3b82f6}.sc.y{border-color:#f59e0b}
    .sn{font-size:28px;font-weight:bold;color:#111}.si{font-size:20px;float:right;opacity:.18}.sl{font-size:11px;color:#888;margin-top:4px}

    /* ── TOOLBAR ── */
    .toolbar{background:#fff;border-radius:12px;padding:12px 14px;margin-bottom:12px;display:flex;flex-wrap:wrap;gap:8px;align-items:center;box-shadow:0 1px 6px rgba(0,0,0,.06)}
    .toolbar form{display:flex;flex-wrap:wrap;gap:8px;flex:1}
    .ti{flex:1 1 160px;padding:9px 12px;border:1.5px solid #ddd;border-radius:7px;font-size:14px;min-width:0}
    .ti:focus{outline:none;border-color:#cc0000}
    .tsel{padding:9px 10px;border:1.5px solid #ddd;border-radius:7px;font-size:14px;background:#fff}
    .btn-s{background:#cc0000;color:#fff;border:none;padding:9px 16px;border-radius:7px;font-size:14px;font-weight:bold;cursor:pointer}
    .btn-c{background:#f0f0f0;color:#555;border:none;padding:9px 12px;border-radius:7px;font-size:14px;cursor:pointer;text-decoration:none;display:inline-block}
    .rc{font-size:13px;color:#999;white-space:nowrap;align-self:center}

    /* ── TABLE ── */
    .tw{background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 1px 6px rgba(0,0,0,.06);overflow-x:auto;-webkit-overflow-scrolling:touch}
    table{width:100%;border-collapse:collapse;min-width:640px}
    thead{background:#fafafa}
    th{padding:11px 14px;text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:#999;border-bottom:1px solid #eee;white-space:nowrap}
    tbody tr{border-bottom:1px solid #f5f5f5;transition:background .1s}
    tbody tr:hover{background:#fffafa}
    tbody tr:last-child{border-bottom:none}
    td{padding:11px 14px;font-size:14px;vertical-align:middle}
    .ta{max-width:150px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:#666;font-size:13px}
    .tn{font-weight:600}
    .tp a{color:#cc0000;text-decoration:none;font-weight:600}
    .td2{color:#bbb;font-size:12px;white-space:nowrap}
    .badge{display:inline-block;padding:3px 9px;border-radius:10px;font-size:11px;font-weight:bold;margin-bottom:4px}
    .bn{background:#fef3c7;color:#92400e}.bc{background:#dbeafe;color:#1e40af}.bx{background:#dcfce7;color:#166534}.bl{background:#f3f4f6;color:#6b7280}
    .sf{display:flex;align-items:center;gap:4px;margin-top:4px}
    .sf select{padding:4px 6px;border:1.5px solid #ddd;border-radius:5px;font-size:11px;background:#fff}
    .sfb{background:#cc0000;color:#fff;border:none;padding:4px 8px;border-radius:5px;font-size:11px;cursor:pointer}
    .db{background:none;border:none;color:#ddd;font-size:15px;cursor:pointer;padding:3px 6px;border-radius:5px}
    .db:hover{color:#cc0000;background:#fff0f0}
    .empty{text-align:center;padding:40px;color:#ccc}
    .empty div{font-size:44px;margin-bottom:12px}

    /* ── MOBILE ── */
    @media(max-width:768px){
      .sidebar{transform:translateX(-100%)}
      .sidebar.open{transform:translateX(0)}
      .main{margin-left:0}
      .hamburger{display:flex}
      .topbar-date{display:none}
      .content{padding:14px}
      .stats{grid-template-columns:1fr 1fr}
      .sn{font-size:24px}
    }
  </style>
</head>
<body>

<!-- Mobile overlay -->
<div class="overlay" id="overlay" onclick="closeSidebar()"></div>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
  <div class="s-logo">Great <span>Properties</span> GA<small>Admin Dashboard</small></div>
  <nav>
    <a class="nav-a <?=!$filter&&!$search?'on':''?>" href="admin.php">
      <span>&#128203;</span><span class="nav-label">All Leads</span>
      <span class="nav-badge <?=$total>0?'hot':''?>"><?=$total?></span>
    </a>
    <a class="nav-a <?=$filter==='New'?'on':''?>" href="admin.php?status=New">
      <span>&#127381;</span><span class="nav-label">New</span>
      <span class="nav-badge <?=$cnt['New']>0?'hot':''?>"><?=$cnt['New']?></span>
    </a>
    <a class="nav-a <?=$filter==='Contacted'?'on':''?>" href="admin.php?status=Contacted">
      <span>&#128222;</span><span class="nav-label">Contacted</span>
      <span class="nav-badge <?=$cnt['Contacted']>0?'hot':''?>"><?=$cnt['Contacted']?></span>
    </a>
    <a class="nav-a <?=$filter==='Closed'?'on':''?>" href="admin.php?status=Closed">
      <span>&#9989;</span><span class="nav-label">Closed</span>
      <span class="nav-badge <?=$cnt['Closed']>0?'hot':''?>"><?=$cnt['Closed']?></span>
    </a>
    <a class="nav-a <?=$filter==='Lost'?'on':''?>" href="admin.php?status=Lost">
      <span>&#128683;</span><span class="nav-label">Lost</span>
      <span class="nav-badge <?=$cnt['Lost']>0?'hot':''?>"><?=$cnt['Lost']?></span>
    </a>
    <a class="nav-a" href="charts.php">
      <span>&#128200;</span><span class="nav-label">Charts</span>
    </a>
  </nav>
  <div class="install-wrap">
    <button class="btn-pwa" id="btn-pwa" style="display:none">&#11015; Install App on Phone</button>
  </div>
  <div class="s-foot">Logged in as <strong style="color:#fff">admin</strong><br><a href="logout.php">&#8594; Log out</a></div>
</div>

<!-- MAIN -->
<div class="main">
  <div class="topbar">
    <div class="topbar-left">
      <button class="hamburger" id="hamburger" onclick="openSidebar()" aria-label="Menu">
        <span></span><span></span><span></span>
      </button>
      <h1>&#128203; Leads</h1>
    </div>
    <div class="topbar-right">
      <span class="topbar-date"><?=date('M j, Y')?></span>
      <a class="btn-out" href="logout.php">Log out</a>
    </div>
  </div>

  <div class="content">
    <?php if (!$sb_ok): ?>
    <div class="alert">&#9888; Error Supabase (<?=$res['code']?>)<code><?=htmlspecialchars(substr($sb_err,0,300))?></code></div>
    <?php endif; ?>

    <div class="stats">
      <div class="sc r"><div class="si">&#128101;</div><div class="sn"><?=$total?></div><div class="sl">Total Leads</div></div>
      <div class="sc g"><div class="si">&#128197;</div><div class="sn"><?=$today?></div><div class="sl">Today</div></div>
      <div class="sc b"><div class="si">&#128198;</div><div class="sn"><?=$this_week?></div><div class="sl">This Week</div></div>
      <div class="sc y"><div class="si">&#127381;</div><div class="sn"><?=$new_count?></div><div class="sl">New / Unread</div></div>
    </div>

    <div class="toolbar">
      <form method="GET" action="admin.php">
        <input class="ti" type="text" name="q" value="<?=htmlspecialchars($search)?>" placeholder="&#128269; Search...">
        <select class="tsel" name="status">
          <option value="">All</option>
          <option value="New" <?=$filter==='New'?'selected':''?>>New</option>
          <option value="Contacted" <?=$filter==='Contacted'?'selected':''?>>Contacted</option>
          <option value="Closed" <?=$filter==='Closed'?'selected':''?>>Closed</option>
          <option value="Lost" <?=$filter==='Lost'?'selected':''?>>Lost</option>
        </select>
        <button class="btn-s" type="submit">Search</button>
        <a class="btn-c" href="admin.php">Clear</a>
      </form>
      <span class="rc"><?=$count?> result<?=$count!==1?'s':''?></span>
    </div>

    <div class="tw">
      <table>
        <thead><tr><th>#</th><th>Name</th><th>Phone</th><th>Email</th><th>Address</th><th>Status</th><th>Date</th><th></th></tr></thead>
        <tbody>
        <?php if($count===0):?>
          <tr><td colspan="8"><div class="empty"><div>&#128307;</div>No leads found<?=($search||$filter)?' for this filter':''?>.</div></td></tr>
        <?php else: foreach($leads as $row):
          $s=$row['status']??'New';
          $b=match($s){'Contacted'=>'bc','Closed'=>'bx','Lost'=>'bl',default=>'bn'};
        ?>
          <tr>
            <td style="color:#ccc;font-size:12px">#<?=(int)$row['id']?></td>
            <td class="tn"><?=htmlspecialchars($row['name']??'')?></td>
            <td class="tp"><a href="tel:<?=htmlspecialchars($row['phone']??'')?>">&#128222; <?=htmlspecialchars($row['phone']??'')?></a></td>
            <td><?=htmlspecialchars($row['email']??'')?></td>
            <td class="ta" title="<?=htmlspecialchars($row['address']??'')?>"><?=htmlspecialchars($row['address']??'')?></td>
            <td>
              <span class="badge <?=$b?>"><?=htmlspecialchars($s)?></span>
              <form method="POST" action="admin.php" class="sf">
                <input type="hidden" name="lead_id" value="<?=(int)$row['id']?>">
                <select name="status">
                  <option <?=$s==='New'?'selected':''?>>New</option>
                  <option <?=$s==='Contacted'?'selected':''?>>Contacted</option>
                  <option <?=$s==='Closed'?'selected':''?>>Closed</option>
                  <option <?=$s==='Lost'?'selected':''?>>Lost</option>
                </select>
                <button class="sfb" type="submit" name="update_status">&#10003;</button>
              </form>
            </td>
            <td class="td2"><?=date('M j g:i a',strtotime($row['created_at']??'now'))?></td>
            <td>
              <form method="POST" action="admin.php" onsubmit="return confirm('Delete?')">
                <input type="hidden" name="lead_id" value="<?=(int)$row['id']?>">
                <button class="db" type="submit" name="delete_lead">&#128465;</button>
              </form>
            </td>
          </tr>
        <?php endforeach; endif;?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
function openSidebar(){
  document.getElementById('sidebar').classList.add('open');
  document.getElementById('overlay').classList.add('open');
  document.body.style.overflow='hidden';
}
function closeSidebar(){
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('overlay').classList.remove('open');
  document.body.style.overflow='';
}

if('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js').catch(()=>{});
let dp; const pb=document.getElementById('btn-pwa');
window.addEventListener('beforeinstallprompt',e=>{e.preventDefault();dp=e;pb.style.display='flex';});
pb.addEventListener('click',async()=>{if(!dp)return;dp.prompt();const{outcome}=await dp.userChoice;dp=null;outcome==='accepted'?(pb.textContent='\u2705 Installed!',pb.style.display='flex'):pb.style.display='none';});
window.addEventListener('appinstalled',()=>{pb.textContent='\u2705 App Installed!';pb.style.display='flex';});
const isIOS=/iphone|ipad|ipod/i.test(navigator.userAgent),isSafari=/safari/i.test(navigator.userAgent)&&!/chrome/i.test(navigator.userAgent),isSA=window.matchMedia('(display-mode:standalone)').matches;
if(isIOS&&isSafari&&!isSA){pb.style.display='flex';pb.textContent='\ud83d\udcf1 Install on iPhone';pb.addEventListener('click',()=>alert('1. Tap \u2191 Share\n2. Add to Home Screen\n3. Tap Add'),{once:true});}
</script>
</body>
</html>
