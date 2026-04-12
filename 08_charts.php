<?php
require_once __DIR__ . '/config.php';
if (empty($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit; }

$res  = sb('GET', 'leads', null, 'order=id.desc&select=*');
$all  = is_array($res['data']) ? $res['data'] : [];
$total = count($all);

$cnt = ['New'=>0,'Contacted'=>0,'Closed'=>0,'Lost'=>0];
foreach ($all as $r) { $s=$r['status']??'New'; if(isset($cnt[$s])) $cnt[$s]++; }

// Last 30 days daily
$daily30 = [];
for ($i=29;$i>=0;$i--) $daily30[date('M j',strtotime("-{$i} days"))]=0;
foreach ($all as $r) {
    $lbl=date('M j',strtotime(substr($r['created_at'],0,10)));
    if(isset($daily30[$lbl])) $daily30[$lbl]++;
}

// Last 7 days
$daily7=[];
for($i=6;$i>=0;$i--) $daily7[date('M j',strtotime("-{$i} days"))]=0;
foreach($all as $r){
    $lbl=date('M j',strtotime(substr($r['created_at'],0,10)));
    if(isset($daily7[$lbl])) $daily7[$lbl]++;
}

// Monthly last 6 months
$monthly=[];
for($i=5;$i>=0;$i--){
    $monthly[date('M Y',strtotime("-{$i} months"))]=0;
}
foreach($all as $r){
    $lbl=date('M Y',strtotime(substr($r['created_at'],0,10)));
    if(isset($monthly[$lbl])) $monthly[$lbl]++;
}

// Conversion rate
$closedPct = $total > 0 ? round($cnt['Closed']/$total*100,1) : 0;
$contactedPct = $total > 0 ? round($cnt['Contacted']/$total*100,1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Charts | Great Properties GA</title>
  <link rel="manifest" href="/manifest.json">
  <meta name="theme-color" content="#cc0000">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-title" content="GP Admin">
  <link rel="apple-touch-icon" href="/icon-512.svg">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:Arial,sans-serif;background:#f0f2f5;color:#222}

    /* SIDEBAR */
    .sidebar{position:fixed;top:0;left:0;bottom:0;width:230px;background:#111;display:flex;flex-direction:column;z-index:100;overflow-y:auto}
    .s-logo{padding:22px 20px;font-size:15px;font-weight:bold;border-bottom:1px solid #1e1e1e;color:#fff}
    .s-logo span{color:#ffd700}
    .s-logo small{display:block;color:#555;font-size:11px;font-weight:normal;margin-top:2px}
    .sidebar nav{flex:1;padding:12px 0}
    .nav-a{display:flex;align-items:center;padding:12px 20px;color:#777;text-decoration:none;font-size:14px;border-left:3px solid transparent;transition:all .15s;gap:8px}
    .nav-a:hover,.nav-a.on{background:#1a1a1a;color:#fff;border-left-color:#cc0000}
    .nav-label{flex:1}
    .nav-badge{display:inline-flex;align-items:center;justify-content:center;min-width:22px;height:22px;padding:0 6px;border-radius:11px;font-size:11px;font-weight:bold;background:#2a2a2a;color:#888}
    .nav-a:hover .nav-badge,.nav-a.on .nav-badge,.nav-badge.hot{background:#cc0000;color:#fff}
    .install-wrap{padding:12px 16px;border-top:1px solid #1e1e1e}
    .btn-pwa{width:100%;background:linear-gradient(135deg,#cc0000,#990000);color:#fff;border:none;padding:11px;border-radius:8px;font-size:13px;font-weight:bold;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px}
    .s-foot{padding:14px 20px;border-top:1px solid #1e1e1e;font-size:12px;color:#555}
    .s-foot a{color:#777;text-decoration:none}.s-foot a:hover{color:#fff}

    /* MAIN */
    .main{margin-left:230px;min-height:100vh}
    .topbar{background:#fff;padding:15px 26px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #eee;position:sticky;top:0;z-index:50;box-shadow:0 1px 4px rgba(0,0,0,.06)}
    .topbar h1{font-size:19px}
    .btn-out{background:#f5f5f5;color:#444;border:none;padding:8px 15px;border-radius:6px;font-size:13px;cursor:pointer;text-decoration:none;font-weight:bold}
    .content{padding:28px}

    /* STAT CARDS */
    .kpi-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:28px}
    .kpi{
      background:#fff;border-radius:14px;padding:22px 20px;
      box-shadow:0 2px 12px rgba(0,0,0,.07);
      border-top:4px solid #ddd;text-align:center;
    }
    .kpi.red{border-color:#cc0000}.kpi.green{border-color:#22c55e}
    .kpi.blue{border-color:#3b82f6}.kpi.yellow{border-color:#f59e0b}.kpi.gray{border-color:#6b7280}
    .kpi-num{font-size:40px;font-weight:bold;color:#111;line-height:1}
    .kpi-pct{font-size:18px;font-weight:bold;color:#22c55e}
    .kpi-label{font-size:13px;color:#888;margin-top:6px}

    /* CHART CARDS */
    .chart-grid{display:grid;gap:20px;grid-template-columns:1fr 1fr;margin-bottom:20px}
    .chart-card{
      background:#fff;border-radius:14px;
      padding:24px;box-shadow:0 2px 12px rgba(0,0,0,.07);
    }
    .chart-card.full{grid-column:1/-1}
    .chart-card h2{
      font-size:16px;font-weight:bold;color:#111;
      margin-bottom:20px;display:flex;align-items:center;gap:8px;
    }
    .chart-card h2 span{font-size:20px}
    .chart-wrap{position:relative}
    .donut-wrap-big{position:relative;max-width:280px;margin:0 auto}
    .donut-center-big{
      position:absolute;top:50%;left:50%;
      transform:translate(-50%,-50%);
      text-align:center;pointer-events:none;
    }
    .donut-center-big .dc-num{font-size:44px;font-weight:bold;color:#111;line-height:1}
    .donut-center-big .dc-lbl{font-size:12px;color:#888;margin-top:4px;text-transform:uppercase;letter-spacing:.5px}

    /* Legend */
    .legend-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:20px}
    .leg{
      display:flex;align-items:center;gap:10px;
      background:#f8f8f8;border-radius:8px;padding:10px 14px;
    }
    .leg-dot{width:14px;height:14px;border-radius:50%;flex-shrink:0}
    .leg-info{flex:1}
    .leg-name{font-size:13px;color:#555;font-weight:500}
    .leg-count{font-size:22px;font-weight:bold;color:#111}
    .leg-pct{font-size:11px;color:#aaa}

    /* Mobile */
    @media(max-width:900px){.chart-grid{grid-template-columns:1fr}}
    @media(max-width:768px){
      .sidebar{display:none}.main{margin-left:0}.content{padding:16px}
      .kpi-num{font-size:30px}
    }
  </style>
</head>
<body>

<div class="sidebar">
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
    <button class="btn-pwa" id="btn-pwa" style="display:none">&#11015; Install App</button>
  </div>
  <div class="s-foot">Logged in as <strong style="color:#fff">admin</strong><br><a href="logout.php">&#8594; Log out</a></div>
</div>

<div class="main">
  <div class="topbar">
    <h1>&#128200; Charts &amp; Analytics</h1>
    <div style="display:flex;align-items:center;gap:12px">
      <span style="font-size:12px;color:#aaa"><?=date('M j, Y')?></span>
      <a class="btn-out" href="logout.php">Log out</a>
    </div>
  </div>
  <div class="content">

    <!-- KPI CARDS -->
    <div class="kpi-grid">
      <div class="kpi red">
        <div class="kpi-num"><?=$total?></div>
        <div class="kpi-label">Total Leads</div>
      </div>
      <div class="kpi yellow">
        <div class="kpi-num"><?=$cnt['New']?></div>
        <div class="kpi-label">New / Pending</div>
      </div>
      <div class="kpi blue">
        <div class="kpi-num"><?=$cnt['Contacted']?></div>
        <div class="kpi-label">Contacted</div>
      </div>
      <div class="kpi green">
        <div class="kpi-num"><?=$cnt['Closed']?></div>
        <div class="kpi-label">Closed / Won</div>
      </div>
      <div class="kpi gray">
        <div class="kpi-num"><?=$cnt['Lost']?></div>
        <div class="kpi-label">Lost</div>
      </div>
      <div class="kpi green">
        <div class="kpi-pct"><?=$closedPct?>%</div>
        <div class="kpi-label">Close Rate</div>
      </div>
    </div>

    <!-- CHARTS ROW 1 -->
    <div class="chart-grid">

      <!-- Donut -->
      <div class="chart-card">
        <h2><span>&#127383;</span> Lead Status Breakdown</h2>
        <div class="donut-wrap-big">
          <canvas id="donutBig"></canvas>
          <div class="donut-center-big">
            <div class="dc-num"><?=$total?></div>
            <div class="dc-lbl">Total</div>
          </div>
        </div>
        <div class="legend-grid">
          <div class="leg"><div class="leg-dot" style="background:#f59e0b"></div><div class="leg-info"><div class="leg-name">New</div><div class="leg-count"><?=$cnt['New']?></div><div class="leg-pct"><?=$total>0?round($cnt['New']/$total*100).'%':'0%'?> of total</div></div></div>
          <div class="leg"><div class="leg-dot" style="background:#3b82f6"></div><div class="leg-info"><div class="leg-name">Contacted</div><div class="leg-count"><?=$cnt['Contacted']?></div><div class="leg-pct"><?=$total>0?round($cnt['Contacted']/$total*100).'%':'0%'?> of total</div></div></div>
          <div class="leg"><div class="leg-dot" style="background:#22c55e"></div><div class="leg-info"><div class="leg-name">Closed</div><div class="leg-count"><?=$cnt['Closed']?></div><div class="leg-pct"><?=$total>0?round($cnt['Closed']/$total*100).'%':'0%'?> of total</div></div></div>
          <div class="leg"><div class="leg-dot" style="background:#6b7280"></div><div class="leg-info"><div class="leg-name">Lost</div><div class="leg-count"><?=$cnt['Lost']?></div><div class="leg-pct"><?=$total>0?round($cnt['Lost']/$total*100).'%':'0%'?> of total</div></div></div>
        </div>
      </div>

      <!-- Bar last 7 days -->
      <div class="chart-card">
        <h2><span>&#128337;</span> Leads — Last 7 Days</h2>
        <div class="chart-wrap"><canvas id="bar7"></canvas></div>
      </div>

    </div>

    <!-- CHARTS ROW 2 -->
    <div class="chart-grid">

      <!-- Line last 30 days -->
      <div class="chart-card">
        <h2><span>&#128200;</span> Leads — Last 30 Days</h2>
        <div class="chart-wrap"><canvas id="line30"></canvas></div>
      </div>

      <!-- Monthly bar -->
      <div class="chart-card">
        <h2><span>&#128197;</span> Monthly Overview (6 months)</h2>
        <div class="chart-wrap"><canvas id="barMonthly"></canvas></div>
      </div>

    </div>

    <!-- Horizontal bar by status -->
    <div class="chart-card full" style="margin-bottom:20px">
      <h2><span>&#127942;</span> Status Comparison</h2>
      <div class="chart-wrap"><canvas id="hbar" height="80"></canvas></div>
    </div>

  </div>
</div>

<script>
if('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js').catch(()=>{});

// PWA
let dp;
const pb=document.getElementById('btn-pwa');
window.addEventListener('beforeinstallprompt',e=>{e.preventDefault();dp=e;pb.style.display='flex';});
pb.addEventListener('click',async()=>{if(!dp)return;dp.prompt();const{outcome}=await dp.userChoice;dp=null;if(outcome==='accepted'){pb.textContent='\u2705 Installed!';pb.style.display='flex';}else pb.style.display='none';});
const isIOS=/iphone|ipad|ipod/i.test(navigator.userAgent);
const isSafari=/safari/i.test(navigator.userAgent)&&!/chrome/i.test(navigator.userAgent);
const isStandalone=window.matchMedia('(display-mode: standalone)').matches;
if(isIOS&&isSafari&&!isStandalone){pb.style.display='flex';pb.textContent='\ud83d\udcf1 Install on iPhone';pb.addEventListener('click',()=>alert('1. Tap \u2191 Share\n2. Add to Home Screen\n3. Tap Add'),{once:true});}

const COLORS=['#f59e0b','#3b82f6','#22c55e','#6b7280'];
const STATUS=['New','Contacted','Closed','Lost'];
const VALS=[<?=$cnt['New']?>,<?=$cnt['Contacted']?>,<?=$cnt['Closed']?>,<?=$cnt['Lost']?>];

// Donut big
new Chart(document.getElementById('donutBig'),{
  type:'doughnut',
  data:{labels:STATUS,datasets:[{data:VALS,backgroundColor:COLORS,borderWidth:0,hoverOffset:8}]},
  options:{cutout:'65%',plugins:{legend:{display:false},tooltip:{callbacks:{label:ctx=>` ${ctx.label}: ${ctx.parsed} (${<?=$total?>>0?'Math.round(ctx.parsed/'+<?=$total?>+'*100)':0}+'%)'}}},animation:{duration:900}}
});

// Bar 7 days
const b7data=<?=json_encode(array_values($daily7))?>;
new Chart(document.getElementById('bar7'),{
  type:'bar',
  data:{labels:<?=json_encode(array_keys($daily7))?>,datasets:[{data:b7data,backgroundColor:b7data.map(v=>v>0?'#cc0000':'#e5e7eb'),borderRadius:6,borderSkipped:false}]},
  options:{plugins:{legend:{display:false},tooltip:{callbacks:{label:ctx=>` ${ctx.parsed.y} lead${ctx.parsed.y!==1?'s':''}`}}},scales:{x:{grid:{display:false},ticks:{color:'#888'}},y:{beginAtZero:true,ticks:{stepSize:1,color:'#888',precision:0},grid:{color:'#f5f5f5'}}},animation:{duration:700}}
});

// Line 30 days
const l30data=<?=json_encode(array_values($daily30))?>;
new Chart(document.getElementById('line30'),{
  type:'line',
  data:{labels:<?=json_encode(array_keys($daily30))?>,datasets:[{data:l30data,borderColor:'#cc0000',backgroundColor:'rgba(204,0,0,.08)',tension:.4,fill:true,pointRadius:l30data.map(v=>v>0?4:0),pointBackgroundColor:'#cc0000'}]},
  options:{plugins:{legend:{display:false},tooltip:{callbacks:{label:ctx=>` ${ctx.parsed.y} lead${ctx.parsed.y!==1?'s':''}`}}},scales:{x:{grid:{display:false},ticks:{color:'#888',maxTicksLimit:8}},y:{beginAtZero:true,ticks:{stepSize:1,color:'#888',precision:0},grid:{color:'#f5f5f5'}}},animation:{duration:800}}
});

// Monthly bar
const mData=<?=json_encode(array_values($monthly))?>;
new Chart(document.getElementById('barMonthly'),{
  type:'bar',
  data:{labels:<?=json_encode(array_keys($monthly))?>,datasets:[{data:mData,backgroundColor:mData.map((v,i)=>i===mData.length-1?'#cc0000':'#3b82f6'),borderRadius:6,borderSkipped:false}]},
  options:{plugins:{legend:{display:false},tooltip:{callbacks:{label:ctx=>` ${ctx.parsed.y} leads`}}},scales:{x:{grid:{display:false},ticks:{color:'#888'}},y:{beginAtZero:true,ticks:{stepSize:1,color:'#888',precision:0},grid:{color:'#f5f5f5'}}},animation:{duration:700}}
});

// Horizontal status bar
new Chart(document.getElementById('hbar'),{
  type:'bar',
  data:{
    labels:STATUS,
    datasets:[{data:VALS,backgroundColor:COLORS,borderRadius:6,borderSkipped:false}]
  },
  options:{
    indexAxis:'y',
    plugins:{legend:{display:false},tooltip:{callbacks:{label:ctx=>` ${ctx.parsed.x} leads`}}},
    scales:{
      x:{beginAtZero:true,ticks:{stepSize:1,color:'#888',precision:0},grid:{color:'#f5f5f5'}},
      y:{grid:{display:false},ticks:{color:'#444',font:{size:14,weight:'bold'}}}
    },
    animation:{duration:800}
  }
});
</script>
</body>
</html>
