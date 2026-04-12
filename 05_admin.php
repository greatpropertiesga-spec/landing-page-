<?php
require_once __DIR__ . '/config.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php'); exit;
}

if (isset($_POST['update_status'])) {
    sb('PATCH', 'leads', ['status' => $_POST['status']], 'id=eq.'.(int)$_POST['lead_id']);
    header('Location: admin.php'.($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''));
    exit;
}
if (isset($_POST['delete_lead'])) {
    sb('DELETE', 'leads', null, 'id=eq.'.(int)$_POST['lead_id']);
    header('Location: admin.php'); exit;
}

$res    = sb('GET', 'leads', null, 'order=id.desc&select=*');
$all    = is_array($res['data']) ? $res['data'] : [];
$sb_ok  = $res['code'] >= 200 && $res['code'] < 300;
$sb_err = $res['raw'];

$total     = count($all);
$today     = count(array_filter($all, fn($r) => substr($r['created_at'],0,10)===date('Y-m-d')));
$this_week = count(array_filter($all, fn($r) => strtotime($r['created_at'])>=strtotime('-7 days')));

$cnt = ['New'=>0,'Contacted'=>0,'Closed'=>0,'Lost'=>0];
foreach ($all as $r) {
    $s = $r['status'] ?? 'New';
    if (isset($cnt[$s])) $cnt[$s]++;
}
$new_count = $cnt['New'];

// Leads per day last 7 days for bar chart
$daily = [];
for ($i=6; $i>=0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $label = date('M j', strtotime("-$i days"));
    $daily[$label] = 0;
}
foreach ($all as $r) {
    $d = substr($r['created_at'],0,10);
    $label = date('M j', strtotime($d));
    if (isset($daily[$label])) $daily[$label]++;
}

$search = trim($_GET['q']??''); $filter = trim($_GET['status']??'');
$leads  = array_filter($all, function($r) use ($search,$filter) {
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
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:Arial,sans-serif;background:#f0f2f5;color:#222}

    /* ── SIDEBAR ── */
    .sidebar{
      position:fixed;top:0;left:0;bottom:0;width:240px;
      background:#111;display:flex;flex-direction:column;z-index:100;
      overflow-y:auto;
    }
    .s-logo{padding:22px 20px;font-size:15px;font-weight:bold;border-bottom:1px solid #1e1e1e;color:#fff;flex-shrink:0}
    .s-logo span{color:#ffd700}
    .s-logo small{display:block;color:#555;font-size:11px;font-weight:normal;margin-top:2px}
    .sidebar nav{padding:12px 0;flex-shrink:0}
    .nav-a{display:flex;align-items:center;padding:12px 20px;color:#777;text-decoration:none;font-size:14px;border-left:3px solid transparent;transition:all .15s;gap:8px}
    .nav-a:hover,.nav-a.on{background:#1a1a1a;color:#fff;border-left-color:#cc0000}
    .nav-label{flex:1}
    .nav-badge{display:inline-flex;align-items:center;justify-content:center;min-width:22px;height:22px;padding:0 6px;border-radius:11px;font-size:11px;font-weight:bold;background:#2a2a2a;color:#888;transition:all .15s}
    .nav-a:hover .nav-badge,.nav-a.on .nav-badge{background:#cc0000;color:#fff}
    .nav-badge.hot{background:#cc0000;color:#fff}

    /* ── CHART SECTION ── */
    .chart-section{
      flex-shrink:0;
      border-top:1px solid #1e1e1e;
      padding:20px 16px 16px;
      background:#0d0d0d;
    }
    .chart-title{
      font-size:11px;text-transform:uppercase;letter-spacing:1px;
      color:#555;margin-bottom:14px;text-align:center;font-weight:bold;
    }
    .donut-wrap{position:relative;width:140px;margin:0 auto 14px}
    .donut-center{
      position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);
      text-align:center;pointer-events:none;
    }
    .donut-center .dc-num{font-size:22px;font-weight:bold;color:#fff;line-height:1}
    .donut-center .dc-lbl{font-size:9px;color:#666;margin-top:2px;text-transform:uppercase;letter-spacing:.5px}
    .chart-legend{display:grid;grid-template-columns:1fr 1fr;gap:6px 10px;margin-bottom:16px}
    .leg-item{display:flex;align-items:center;gap:6px;font-size:11px;color:#888}
    .leg-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0}
    .leg-val{margin-left:auto;color:#fff;font-weight:bold;font-size:12px}

    /* bar chart */
    .bar-title{font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#555;margin-bottom:10px;text-align:center;font-weight:bold}
    .bar-wrap{width:100%}

    /* ── FOOTER ── */
    .s-foot{padding:14px 20px;border-top:1px solid #1e1e1e;font-size:12px;color:#555;flex-shrink:0}
    .s-foot a{color:#777;text-decoration:none}.s-foot a:hover{color:#fff}

    /* ── MAIN ── */
    .main{margin-left:240px;min-height:100vh}
    .topbar{background:#fff;padding:15px 26px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #eee;position:sticky;top:0;z-index:50;box-shadow:0 1px 4px rgba(0,0,0,.06)}
    .topbar h1{font-size:19px}
    .btn-out{background:#f5f5f5;color:#444;border:none;padding:8px 15px;border-radius:6px;font-size:13px;cursor:pointer;text-decoration:none;font-weight:bold}
    .btn-out:hover{background:#eee}
    .content{padding:24px}
    .alert{background:#fff0f0;border:1px solid #fca5a5;color:#b00;padding:14px 18px;border-radius:8px;margin-bottom:18px;font-size:13px;line-height:1.7}
    .alert code{display:block;background:#ffe;padding:6px 10px;border-radius:4px;margin-top:8px;font-size:11px;word-break:break-all;color:#555}
    .stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;margin-bottom:20px}
    .sc{background:#fff;border-radius:12px;padding:18px;box-shadow:0 1px 6px rgba(0,0,0,.06);border-left:4px solid #ddd}
    .sc.r{border-color:#cc0000}.sc.g{border-color:#22c55e}.sc.b{border-color:#3b82f6}.sc.y{border-color:#f59e0b}
    .sn{font-size:32px;font-weight:bold;color:#111}.si{font-size:22px;float:right;opacity:.18}.sl{font-size:12px;color:#888;margin-top:4px}
    .toolbar{background:#fff;border-radius:12px;padding:14px 16px;margin-bottom:14px;display:flex;flex-wrap:wrap;gap:10px;align-items:center;box-shadow:0 1px 6px rgba(0,0,0,.06)}
    .toolbar form{display:flex;flex-wrap:wrap;gap:8px;flex:1}
    .ti{flex:1 1 180px;padding:9px 12px;border:1.5px solid #ddd;border-radius:7px;font-size:14px}
    .ti:focus{outline:none;border-color:#cc0000}
    .tsel{padding:9px 12px;border:1.5px solid #ddd;border-radius:7px;font-size:14px;background:#fff}
    .btn-s{background:#cc0000;color:#fff;border:none;padding:9px 18px;border-radius:7px;font-size:14px;font-weight:bold;cursor:pointer}
    .btn-c{background:#f0f0f0;color:#555;border:none;padding:9px 14px;border-radius:7px;font-size:14px;cursor:pointer;text-decoration:none;display:inline-block}
    .rc{font-size:13px;color:#999;white-space:nowrap}
    .tw{background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 1px 6px rgba(0,0,0,.06);overflow-x:auto}
    table{width:100%;border-collapse:collapse;min-width:680px}
    thead{background:#fafafa}
    th{padding:11px 14px;text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:#999;border-bottom:1px solid #eee;white-space:nowrap}
    tbody tr{border-bottom:1px solid #f5f5f5;transition:background .1s}
    tbody tr:hover{background:#fffafa}
    tbody tr:last-child{border-bottom:none}
    td{padding:11px 14px;font-size:14px;vertical-align:middle}
    .ta{max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:#666;font-size:13px}
    .tn{font-weight:600}.tp a{color:#cc0000;text-decoration:none;font-weight:600}.td2{color:#bbb;font-size:12px;white-space:nowrap}
    .badge{display:inline-block;padding:3px 9px;border-radius:10px;font-size:11px;font-weight:bold;margin-bottom:4px}
    .bn{background:#fef3c7;color:#92400e}.bc{background:#dbeafe;color:#1e40af}.bx{background:#dcfce7;color:#166534}.bl{background:#f3f4f6;color:#6b7280}
    .sf{display:flex;align-items:center;gap:4px;margin-top:4px}
    .sf select{padding:4px 6px;border:1.5px solid #ddd;border-radius:5px;font-size:11px;background:#fff}
    .sfb{background:#cc0000;color:#fff;border:none;padding:4px 8px;border-radius:5px;font-size:11px;cursor:pointer}
    .db{background:none;border:none;color:#ddd;font-size:15px;cursor:pointer;padding:3px 6px;border-radius:5px}
    .db:hover{color:#cc0000;background:#fff0f0}
    .empty{text-align:center;padding:50px;color:#ccc}
    .empty div{font-size:48px;margin-bottom:12px}
    @media(max-width:768px){.sidebar{display:none}.main{margin-left:0}.content{padding:14px}}
  </style>
</head>
<body>

<!-- ══ SIDEBAR ══ -->
<div class="sidebar">
  <div class="s-logo">Great <span>Properties</span> GA<small>Admin Dashboard</small></div>
  <nav>
    <a class="nav-a <?=!$filter&&!$search?'on':''?>" href="admin.php">
      <span>&#128203;</span>
      <span class="nav-label">All Leads</span>
      <span class="nav-badge <?=$total>0?'hot':''?>"><?=$total?></span>
    </a>
    <a class="nav-a <?=$filter==='New'?'on':''?>" href="admin.php?status=New">
      <span>&#127381;</span>
      <span class="nav-label">New</span>
      <span class="nav-badge <?=$cnt['New']>0?'hot':''?>"><?=$cnt['New']?></span>
    </a>
    <a class="nav-a <?=$filter==='Contacted'?'on':''?>" href="admin.php?status=Contacted">
      <span>&#128222;</span>
      <span class="nav-label">Contacted</span>
      <span class="nav-badge <?=$cnt['Contacted']>0?'hot':''?>"><?=$cnt['Contacted']?></span>
    </a>
    <a class="nav-a <?=$filter==='Closed'?'on':''?>" href="admin.php?status=Closed">
      <span>&#9989;</span>
      <span class="nav-label">Closed</span>
      <span class="nav-badge <?=$cnt['Closed']>0?'hot':''?>"><?=$cnt['Closed']?></span>
    </a>
    <a class="nav-a <?=$filter==='Lost'?'on':''?>" href="admin.php?status=Lost">
      <span>&#128683;</span>
      <span class="nav-label">Lost</span>
      <span class="nav-badge <?=$cnt['Lost']>0?'hot':''?>"><?=$cnt['Lost']?></span>
    </a>
  </nav>

  <!-- ══ CHART SECTION ══ -->
  <div class="chart-section">

    <!-- Donut chart -->
    <div class="chart-title">&#128200; Lead Overview</div>
    <div class="donut-wrap">
      <canvas id="donutChart" width="140" height="140"></canvas>
      <div class="donut-center">
        <div class="dc-num"><?=$total?></div>
        <div class="dc-lbl">Total</div>
      </div>
    </div>
    <div class="chart-legend">
      <div class="leg-item"><div class="leg-dot" style="background:#f59e0b"></div>New<span class="leg-val"><?=$cnt['New']?></span></div>
      <div class="leg-item"><div class="leg-dot" style="background:#3b82f6"></div>Contacted<span class="leg-val"><?=$cnt['Contacted']?></span></div>
      <div class="leg-item"><div class="leg-dot" style="background:#22c55e"></div>Closed<span class="leg-val"><?=$cnt['Closed']?></span></div>
      <div class="leg-item"><div class="leg-dot" style="background:#6b7280"></div>Lost<span class="leg-val"><?=$cnt['Lost']?></span></div>
    </div>

    <!-- Bar chart last 7 days -->
    <div class="bar-title">&#128337; Last 7 Days</div>
    <div class="bar-wrap">
      <canvas id="barChart" height="110"></canvas>
    </div>

  </div>

  <div class="s-foot">
    Logged in as <strong style="color:#fff">admin</strong><br>
    <a href="logout.php">&#8594; Log out</a>
  </div>
</div>

<!-- ══ MAIN ══ -->
<div class="main">
  <div class="topbar">
    <h1>&#128203; Leads Dashboard</h1>
    <div style="display:flex;align-items:center;gap:12px">
      <span style="font-size:12px;color:#aaa"><?=date('M j, Y')?></span>
      <a class="btn-out" href="logout.php">Log out</a>
    </div>
  </div>
  <div class="content">

    <?php if (!$sb_ok): ?>
    <div class="alert">
      &#9888; Error conectando con Supabase (HTTP <?=$res['code']?>)
      <code><?=htmlspecialchars(substr($sb_err,0,400))?></code>
    </div>
    <?php endif; ?>

    <div class="stats">
      <div class="sc r"><div class="si">&#128101;</div><div class="sn"><?=$total?></div><div class="sl">Total Leads</div></div>
      <div class="sc g"><div class="si">&#128197;</div><div class="sn"><?=$today?></div><div class="sl">Today</div></div>
      <div class="sc b"><div class="si">&#128198;</div><div class="sn"><?=$this_week?></div><div class="sl">This Week</div></div>
      <div class="sc y"><div class="si">&#127381;</div><div class="sn"><?=$new_count?></div><div class="sl">New / Unread</div></div>
    </div>

    <div class="toolbar">
      <form method="GET" action="admin.php">
        <input class="ti" type="text" name="q" value="<?=htmlspecialchars($search)?>" placeholder="&#128269; Search name, phone, email, address...">
        <select class="tsel" name="status">
          <option value="">All Statuses</option>
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
            <td class="tp"><a href="tel:<?=htmlspecialchars($row['phone']??'')?>"><?=htmlspecialchars($row['phone']??'')?></a></td>
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
            <td class="td2"><?=date('M j, Y g:i a',strtotime($row['created_at']??'now'))?></td>
            <td>
              <form method="POST" action="admin.php" onsubmit="return confirm('Delete this lead?')">
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
// ── DONUT CHART ──
const donutData = {
  labels: ['New','Contacted','Closed','Lost'],
  datasets:[{
    data: [<?=$cnt['New']?>,<?=$cnt['Contacted']?>,<?=$cnt['Closed']?>,<?=$cnt['Lost']?>],
    backgroundColor: ['#f59e0b','#3b82f6','#22c55e','#6b7280'],
    borderWidth: 0,
    hoverOffset: 6,
  }]
};
new Chart(document.getElementById('donutChart'), {
  type: 'doughnut',
  data: donutData,
  options: {
    cutout: '68%',
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: ctx => ` ${ctx.label}: ${ctx.parsed} leads`
        }
      }
    },
    animation: { animateRotate: true, duration: 800 }
  }
});

// ── BAR CHART last 7 days ──
const barLabels = <?=json_encode(array_keys($daily))?>;
const barData   = <?=json_encode(array_values($daily))?>;
new Chart(document.getElementById('barChart'), {
  type: 'bar',
  data: {
    labels: barLabels,
    datasets:[{
      data: barData,
      backgroundColor: barData.map(v => v > 0 ? '#cc0000' : '#2a2a2a'),
      borderRadius: 4,
      borderSkipped: false,
    }]
  },
  options: {
    plugins: { legend: { display: false }, tooltip: {
      callbacks: { label: ctx => ` ${ctx.parsed.y} lead${ctx.parsed.y!==1?'s':''}` }
    }},
    scales: {
      x: {
        ticks: { color:'#555', font:{ size:9 } },
        grid: { display: false },
        border: { display: false }
      },
      y: {
        ticks: { color:'#555', font:{ size:9 }, stepSize:1, precision:0 },
        grid: { color:'#1a1a1a' },
        border: { display: false },
        beginAtZero: true
      }
    },
    animation: { duration: 600 }
  }
});
</script>
</body>
</html>
