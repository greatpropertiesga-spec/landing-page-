<?php
require_once __DIR__ . '/config.php';
if (empty($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit; }

$res   = sb('GET','leads',null,'order=id.desc&select=*');
$all   = is_array($res['data']) ? $res['data'] : [];
$total = count($all);

$cnt = ['New'=>0,'Contacted'=>0,'Closed'=>0,'Lost'=>0];
foreach ($all as $r) { $s=$r['status']??'New'; if(isset($cnt[$s])) $cnt[$s]++; }

$daily7=[];
for($i=6;$i>=0;$i--) $daily7[date('M j',strtotime("-{$i} days"))]=0;
foreach($all as $r){ $l=date('M j',strtotime(substr($r['created_at'],0,10))); if(isset($daily7[$l])) $daily7[$l]++; }

$daily30=[];
for($i=29;$i>=0;$i--) $daily30[date('M j',strtotime("-{$i} days"))]=0;
foreach($all as $r){ $l=date('M j',strtotime(substr($r['created_at'],0,10))); if(isset($daily30[$l])) $daily30[$l]++; }

$monthly=[];
for($i=5;$i>=0;$i--) $monthly[date('M Y',strtotime("-{$i} months"))]=0;
foreach($all as $r){ $l=date('M Y',strtotime(substr($r['created_at'],0,10))); if(isset($monthly[$l])) $monthly[$l]++; }

$closedPct = $total>0 ? round($cnt['Closed']/$total*100,1) : 0;
$totalJS   = (int)$total;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Charts | Great Properties GA</title>
  <link rel="manifest" href="/manifest.json">
  <meta name="theme-color" content="#cc0000">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="GP Admin">
  <link rel="apple-touch-icon" href="/icon-512.svg">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    html,body{height:100%}
    body{font-family:Arial,sans-serif;background:#f0f2f5;color:#222;overflow-x:hidden}

    /* SIDEBAR */
    .sidebar{position:fixed;top:0;left:0;bottom:0;width:230px;background:#111;display:flex;flex-direction:column;z-index:300;overflow-y:auto;transition:transform .28s ease}
    .s-logo{padding:22px 20px;font-size:15px;font-weight:bold;border-bottom:1px solid #1e1e1e;color:#fff}
    .s-logo span{color:#ffd700}
    .s-logo small{display:block;color:#555;font-size:11px;font-weight:normal;margin-top:2px}
    .sidebar nav{flex:1;padding:12px 0}
    .nav-a{display:flex;align-items:center;padding:13px 20px;color:#777;text-decoration:none;font-size:14px;border-left:3px solid transparent;transition:all .15s;gap:8px}
    .nav-a:hover,.nav-a.on{background:#1a1a1a;color:#fff;border-left-color:#cc0000}
    .nav-label{flex:1}
    .nav-badge{display:inline-flex;align-items:center;justify-content:center;min-width:22px;height:22px;padding:0 6px;border-radius:11px;font-size:11px;font-weight:bold;background:#2a2a2a;color:#888}
    .nav-a:hover .nav-badge,.nav-a.on .nav-badge,.nav-badge.hot{background:#cc0000;color:#fff}
    .install-wrap{padding:12px 16px;border-top:1px solid #1e1e1e}
    .btn-pwa{width:100%;background:linear-gradient(135deg,#cc0000,#990000);color:#fff;border:none;padding:11px;border-radius:8px;font-size:13px;font-weight:bold;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px}
    .s-foot{padding:14px 20px;border-top:1px solid #1e1e1e;font-size:12px;color:#555}
    .s-foot a{color:#777;text-decoration:none}.s-foot a:hover{color:#fff}

    /* OVERLAY */
    .overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:299}
    .overlay.open{display:block}

    /* MAIN */
    .main{margin-left:230px;min-height:100vh}
    .topbar{background:#fff;padding:14px 20px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #eee;position:sticky;top:0;z-index:50;box-shadow:0 1px 4px rgba(0,0,0,.06)}
    .topbar-left{display:flex;align-items:center;gap:12px}
    .hamburger{display:none;background:none;border:none;cursor:pointer;padding:6px;border-radius:6px;flex-direction:column;gap:5px}
    .hamburger span{display:block;width:22px;height:2px;background:#333;border-radius:2px}
    .topbar h1{font-size:18px;color:#111}
    .topbar-right{display:flex;align-items:center;gap:10px}
    .topbar-date{font-size:12px;color:#aaa}
    .btn-out{background:#f5f5f5;color:#444;border:none;padding:8px 14px;border-radius:6px;font-size:13px;cursor:pointer;text-decoration:none;font-weight:bold}
    .btn-out:hover{background:#eee}
    .content{padding:22px}

    /* KPI */
    .kpi-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:12px;margin-bottom:20px}
    .kpi{background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 10px rgba(0,0,0,.06);border-top:4px solid #ddd;text-align:center}
    .kpi.red{border-color:#cc0000}.kpi.green{border-color:#22c55e}.kpi.blue{border-color:#3b82f6}.kpi.yellow{border-color:#f59e0b}.kpi.gray{border-color:#6b7280}
    .kpi-num{font-size:34px;font-weight:bold;color:#111;line-height:1}
    .kpi-pct{font-size:26px;font-weight:bold;color:#22c55e}
    .kpi-label{font-size:11px;color:#888;margin-top:5px}

    /* CHART ROWS */
    .chart-row{display:grid;gap:16px;grid-template-columns:1fr 1fr;margin-bottom:16px}
    .chart-card{background:#fff;border-radius:14px;padding:20px;box-shadow:0 2px 10px rgba(0,0,0,.06)}
    .chart-card.full{grid-column:1/-1}
    .chart-card h2{font-size:14px;font-weight:bold;color:#111;margin-bottom:14px;display:flex;align-items:center;gap:7px}
    .chart-card h2 em{font-size:18px;font-style:normal}

    /* Fixed height canvas containers */
    .canvas-box{position:relative;width:100%;height:240px}
    .canvas-box.tall{height:280px}
    .canvas-box.short{height:110px}
    .canvas-box canvas{position:absolute;top:0;left:0;width:100%!important;height:100%!important}

    /* Donut layout */
    .donut-layout{display:flex;flex-wrap:wrap;gap:16px;align-items:center}
    .donut-box{position:relative;flex:0 0 200px;height:200px}
    .donut-box canvas{position:absolute;top:0;left:0;width:100%!important;height:100%!important}
    .donut-center{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;pointer-events:none}
    .dc-n{font-size:36px;font-weight:bold;color:#111;line-height:1}
    .dc-l{font-size:10px;color:#999;text-transform:uppercase;letter-spacing:.5px;margin-top:3px}
    .legend-list{flex:1;display:flex;flex-direction:column;gap:8px;min-width:120px}
    .leg{display:flex;align-items:center;gap:8px;background:#f8f8f8;border-radius:8px;padding:9px 12px}
    .leg-dot{width:12px;height:12px;border-radius:50%;flex-shrink:0}
    .leg-name{font-size:12px;color:#555;flex:1}
    .leg-val{font-size:16px;font-weight:bold;color:#111}
    .leg-pct{font-size:10px;color:#aaa;margin-left:3px}

    /* Mobile */
    @media(max-width:960px){.chart-row{grid-template-columns:1fr}}
    @media(max-width:768px){
      .sidebar{transform:translateX(-100%)}
      .sidebar.open{transform:translateX(0)}
      .main{margin-left:0}
      .hamburger{display:flex}
      .topbar-date{display:none}
      .content{padding:14px}
      .kpi-grid{grid-template-columns:1fr 1fr}
      .kpi-num{font-size:26px}
      .donut-box{flex:0 0 160px;height:160px}
      .canvas-box{height:200px}
      .canvas-box.tall{height:220px}
    }
  </style>
</head>
<body>

<div class="overlay" id="overlay" onclick="closeSidebar()"></div>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
  <div class="s-logo">Great <span>Properties</span> GA<small>Admin Dashboard</small></div>
  <nav>
    <a class="nav-a" href="admin.php">
      <span>&#128203;</span><span class="nav-label">All Leads</span>
      <span class="nav-badge <?=$total>0?'hot':''?>"><?=$total?></span>
    </a>
    <a class="nav-a" href="admin.php?status=New">
      <span>&#127381;</span><span class="nav-label">New</span>
      <span class="nav-badge <?=$cnt['New']>0?'hot':''?>"><?=$cnt['New']?></span>
    </a>
    <a class="nav-a" href="admin.php?status=Contacted">
      <span>&#128222;</span><span class="nav-label">Contacted</span>
      <span class="nav-badge <?=$cnt['Contacted']>0?'hot':''?>"><?=$cnt['Contacted']?></span>
    </a>
    <a class="nav-a" href="admin.php?status=Closed">
      <span>&#9989;</span><span class="nav-label">Closed</span>
      <span class="nav-badge <?=$cnt['Closed']>0?'hot':''?>"><?=$cnt['Closed']?></span>
    </a>
    <a class="nav-a" href="admin.php?status=Lost">
      <span>&#128683;</span><span class="nav-label">Lost</span>
      <span class="nav-badge <?=$cnt['Lost']>0?'hot':''?>"><?=$cnt['Lost']?></span>
    </a>
    <a class="nav-a on" href="charts.php">
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
      <h1>&#128200; Charts</h1>
    </div>
    <div class="topbar-right">
      <span class="topbar-date"><?=date('M j, Y')?></span>
      <a class="btn-out" href="logout.php">Log out</a>
    </div>
  </div>
  <div class="content">

    <!-- KPI -->
    <div class="kpi-grid">
      <div class="kpi red"><div class="kpi-num"><?=$total?></div><div class="kpi-label">Total Leads</div></div>
      <div class="kpi yellow"><div class="kpi-num"><?=$cnt['New']?></div><div class="kpi-label">New</div></div>
      <div class="kpi blue"><div class="kpi-num"><?=$cnt['Contacted']?></div><div class="kpi-label">Contacted</div></div>
      <div class="kpi green"><div class="kpi-num"><?=$cnt['Closed']?></div><div class="kpi-label">Closed</div></div>
      <div class="kpi gray"><div class="kpi-num"><?=$cnt['Lost']?></div><div class="kpi-label">Lost</div></div>
      <div class="kpi green"><div class="kpi-pct"><?=$closedPct?>%</div><div class="kpi-label">Close Rate</div></div>
    </div>

    <!-- ROW 1 -->
    <div class="chart-row">
      <div class="chart-card">
        <h2><em>&#127383;</em> Status Breakdown</h2>
        <div class="donut-layout">
          <div class="donut-box">
            <canvas id="donutBig"></canvas>
            <div class="donut-center"><div class="dc-n"><?=$total?></div><div class="dc-l">Total</div></div>
          </div>
          <div class="legend-list">
            <?php $ld=[['New','#f59e0b'],['Contacted','#3b82f6'],['Closed','#22c55e'],['Lost','#6b7280']];
            foreach($ld as [$n,$c]){ $p=$total>0?round($cnt[$n]/$total*100):0; ?>
            <div class="leg"><div class="leg-dot" style="background:<?=$c?>"></div><span class="leg-name"><?=$n?></span><span class="leg-val"><?=$cnt[$n]?></span><span class="leg-pct"><?=$p?>%</span></div>
            <?php } ?>
          </div>
        </div>
      </div>
      <div class="chart-card">
        <h2><em>&#128337;</em> Last 7 Days</h2>
        <div class="canvas-box tall"><canvas id="bar7"></canvas></div>
      </div>
    </div>

    <!-- ROW 2 -->
    <div class="chart-row">
      <div class="chart-card">
        <h2><em>&#128200;</em> Last 30 Days</h2>
        <div class="canvas-box"><canvas id="line30"></canvas></div>
      </div>
      <div class="chart-card">
        <h2><em>&#128197;</em> Monthly (6 months)</h2>
        <div class="canvas-box"><canvas id="barMonthly"></canvas></div>
      </div>
    </div>

    <!-- ROW 3 -->
    <div class="chart-card full" style="margin-bottom:20px">
      <h2><em>&#127942;</em> Status Comparison</h2>
      <div class="canvas-box short"><canvas id="hbar"></canvas></div>
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
const isIOS=/iphone|ipad|ipod/i.test(navigator.userAgent),isSafari=/safari/i.test(navigator.userAgent)&&!/chrome/i.test(navigator.userAgent),isSA=window.matchMedia('(display-mode:standalone)').matches;
if(isIOS&&isSafari&&!isSA){pb.style.display='flex';pb.textContent='\ud83d\udcf1 Install on iPhone';pb.addEventListener('click',()=>alert('1. Tap \u2191 Share\n2. Add to Home Screen\n3. Tap Add'),{once:true});}

const OPTS={maintainAspectRatio:false,responsive:true};
const COLORS=['#f59e0b','#3b82f6','#22c55e','#6b7280'];
const STATUS=['New','Contacted','Closed','Lost'];
const VALS=[<?=$cnt['New']?>,<?=$cnt['Contacted']?>,<?=$cnt['Closed']?>,<?=$cnt['Lost']?>];
const TOTAL=<?=$totalJS?>;

new Chart(document.getElementById('donutBig'),{
  type:'doughnut',
  data:{labels:STATUS,datasets:[{data:VALS,backgroundColor:COLORS,borderWidth:2,borderColor:'#fff',hoverOffset:8}]},
  options:{...OPTS,cutout:'62%',plugins:{legend:{display:false},tooltip:{callbacks:{label:ctx=>{const p=TOTAL>0?Math.round(ctx.parsed/TOTAL*100):0;return ` ${ctx.label}: ${ctx.parsed} (${p}%)`;}}}},animation:{duration:900}}
});

const b7d=<?=json_encode(array_values($daily7))?>;
new Chart(document.getElementById('bar7'),{
  type:'bar',
  data:{labels:<?=json_encode(array_keys($daily7))?>,datasets:[{data:b7d,backgroundColor:b7d.map(v=>v>0?'#cc0000':'#e5e7eb'),borderRadius:5,borderSkipped:false}]},
  options:{...OPTS,plugins:{legend:{display:false},tooltip:{callbacks:{label:ctx=>` ${ctx.parsed.y} lead${ctx.parsed.y!==1?'s':''}`}}},scales:{x:{grid:{display:false},ticks:{color:'#888',font:{size:10}}},y:{beginAtZero:true,ticks:{stepSize:1,precision:0,color:'#888'},grid:{color:'#f0f0f0'}}}}
});

const l30=<?=json_encode(array_values($daily30))?>;
new Chart(document.getElementById('line30'),{
  type:'line',
  data:{labels:<?=json_encode(array_keys($daily30))?>,datasets:[{data:l30,borderColor:'#cc0000',backgroundColor:'rgba(204,0,0,0.08)',tension:.4,fill:true,pointRadius:l30.map(v=>v>0?4:0),pointBackgroundColor:'#cc0000',pointBorderColor:'#fff',pointBorderWidth:2}]},
  options:{...OPTS,plugins:{legend:{display:false},tooltip:{callbacks:{label:ctx=>` ${ctx.parsed.y} lead${ctx.parsed.y!==1?'s':''}`}}},scales:{x:{grid:{display:false},ticks:{color:'#888',maxTicksLimit:7,font:{size:10}}},y:{beginAtZero:true,ticks:{stepSize:1,precision:0,color:'#888'},grid:{color:'#f0f0f0'}}}}
});

const mD=<?=json_encode(array_values($monthly))?>;
new Chart(document.getElementById('barMonthly'),{
  type:'bar',
  data:{labels:<?=json_encode(array_keys($monthly))?>,datasets:[{data:mD,backgroundColor:mD.map((v,i)=>i===mD.length-1?'#cc0000':'#93c5fd'),borderRadius:5,borderSkipped:false}]},
  options:{...OPTS,plugins:{legend:{display:false},tooltip:{callbacks:{label:ctx=>` ${ctx.parsed.y} leads`}}},scales:{x:{grid:{display:false},ticks:{color:'#888',font:{size:10}}},y:{beginAtZero:true,ticks:{stepSize:1,precision:0,color:'#888'},grid:{color:'#f0f0f0'}}}}
});

new Chart(document.getElementById('hbar'),{
  type:'bar',
  data:{labels:STATUS,datasets:[{data:VALS,backgroundColor:COLORS,borderRadius:5,borderSkipped:false}]},
  options:{...OPTS,indexAxis:'y',plugins:{legend:{display:false},tooltip:{callbacks:{label:ctx=>` ${ctx.parsed.x} leads`}}},scales:{x:{beginAtZero:true,ticks:{stepSize:1,precision:0,color:'#888'},grid:{color:'#f0f0f0'}},y:{grid:{display:false},ticks:{color:'#333',font:{size:13,weight:'bold'}}}}}
});
</script>
</body>
</html>
