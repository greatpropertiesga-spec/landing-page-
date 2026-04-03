<?php
include 'config.php';
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// ── Update status ──
if (isset($_POST['update_status'])) {
    $id     = (int)$_POST['lead_id'];
    $status = $_POST['status'];
    $stmt   = $pdo->prepare("UPDATE leads SET status=? WHERE id=?");
    $stmt->execute([$status, $id]);
    header('Location: admin.php');
    exit;
}

// ── Delete lead ──
if (isset($_POST['delete_lead'])) {
    $id = (int)$_POST['lead_id'];
    $pdo->prepare("DELETE FROM leads WHERE id=?")->execute([$id]);
    header('Location: admin.php');
    exit;
}

// ── Stats ──
$total     = $pdo->query("SELECT COUNT(*) FROM leads")->fetchColumn();
$today     = $pdo->query("SELECT COUNT(*) FROM leads WHERE created_at::date = CURRENT_DATE")->fetchColumn();
$this_week = $pdo->query("SELECT COUNT(*) FROM leads WHERE created_at >= NOW() - INTERVAL '7 days'")->fetchColumn();
$new_leads = $pdo->query("SELECT COUNT(*) FROM leads WHERE status='New'")->fetchColumn();

// ── Search / Filter ──
$search = trim($_GET['q']      ?? '');
$filter = trim($_GET['status'] ?? '');

$sql    = "SELECT * FROM leads WHERE 1=1";
$params = [];

if ($search) {
    $sql    .= " AND (name ILIKE ? OR email ILIKE ? OR phone ILIKE ? OR address ILIKE ?)";
    $like    = "%$search%";
    $params  = array_merge($params, [$like, $like, $like, $like]);
}
if ($filter) {
    $sql    .= " AND status = ?";
    $params[] = $filter;
}
$sql .= " ORDER BY id DESC";

$stmt  = $pdo->prepare($sql);
$stmt->execute($params);
$leads = $stmt->fetchAll();
$count = count($leads);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard | Great Properties GA</title>
  <style>
    *{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:Arial,sans-serif;background:#f0f2f5;color:#222;}

    .sidebar{
      position:fixed;top:0;left:0;bottom:0;width:220px;
      background:#111;color:#fff;display:flex;flex-direction:column;z-index:100;
    }
    .sidebar-logo{padding:24px 20px;font-size:16px;font-weight:bold;border-bottom:1px solid #222;}
    .sidebar-logo span{color:#ffd700;}
    .sidebar-logo small{display:block;color:#666;font-size:11px;font-weight:normal;margin-top:3px;}
    .sidebar nav{flex:1;padding:16px 0;}
    .nav-item{
      display:flex;align-items:center;gap:12px;padding:13px 20px;
      color:#aaa;text-decoration:none;font-size:14px;transition:background .15s,color .15s;
    }
    .nav-item:hover,.nav-item.active{background:#1a1a1a;color:#fff;}
    .nav-item .icon{font-size:18px;width:22px;text-align:center;}
    .sidebar-footer{padding:16px 20px;border-top:1px solid #222;font-size:12px;color:#555;}
    .sidebar-footer a{color:#888;text-decoration:none;}
    .sidebar-footer a:hover{color:#fff;}

    .main{margin-left:220px;min-height:100vh;}
    .topbar{
      background:#fff;padding:16px 28px;
      display:flex;align-items:center;justify-content:space-between;
      border-bottom:1px solid #e8e8e8;position:sticky;top:0;z-index:50;
    }
    .topbar h1{font-size:20px;color:#111;}
    .topbar-right{display:flex;align-items:center;gap:12px;}
    .btn-logout{
      background:#f5f5f5;color:#444;border:none;padding:8px 16px;
      border-radius:6px;font-size:13px;cursor:pointer;
      text-decoration:none;font-weight:bold;
    }
    .btn-logout:hover{background:#eee;}

    .content{padding:28px;}

    .stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:16px;margin-bottom:28px;}
    .stat-card{
      background:#fff;border-radius:12px;padding:22px 20px;
      box-shadow:0 1px 8px rgba(0,0,0,0.06);border-left:4px solid #ddd;
    }
    .stat-card.red{border-color:#cc0000;}
    .stat-card.green{border-color:#22c55e;}
    .stat-card.blue{border-color:#3b82f6;}
    .stat-card.yellow{border-color:#f59e0b;}
    .stat-num{font-size:34px;font-weight:bold;color:#111;line-height:1;}
    .stat-icon{font-size:28px;float:right;opacity:.2;}
    .stat-label{font-size:12px;color:#888;margin-top:6px;}

    .toolbar{
      background:#fff;border-radius:12px;padding:16px 20px;margin-bottom:18px;
      display:flex;flex-wrap:wrap;gap:12px;align-items:center;
      box-shadow:0 1px 8px rgba(0,0,0,0.06);
    }
    .toolbar form{display:flex;flex-wrap:wrap;gap:10px;flex:1;}
    .toolbar input[type=text]{
      flex:1 1 200px;padding:9px 14px;border:1.5px solid #ddd;
      border-radius:7px;font-size:14px;
    }
    .toolbar input[type=text]:focus{outline:none;border-color:#cc0000;}
    .toolbar select{padding:9px 14px;border:1.5px solid #ddd;border-radius:7px;font-size:14px;background:#fff;}
    .btn-search{background:#cc0000;color:#fff;border:none;padding:9px 20px;border-radius:7px;font-size:14px;font-weight:bold;cursor:pointer;}
    .btn-clear{background:#f5f5f5;color:#444;border:none;padding:9px 16px;border-radius:7px;font-size:14px;cursor:pointer;text-decoration:none;display:inline-block;}
    .result-count{font-size:13px;color:#888;white-space:nowrap;}

    .table-wrap{
      background:#fff;border-radius:12px;overflow:hidden;
      box-shadow:0 1px 8px rgba(0,0,0,0.06);overflow-x:auto;
    }
    table{width:100%;border-collapse:collapse;min-width:700px;}
    thead{background:#f8f8f8;}
    thead th{
      padding:13px 16px;text-align:left;font-size:12px;
      text-transform:uppercase;letter-spacing:.5px;
      color:#888;border-bottom:1px solid #eee;white-space:nowrap;
    }
    tbody tr{border-bottom:1px solid #f0f0f0;transition:background .12s;}
    tbody tr:hover{background:#fffbfb;}
    tbody tr:last-child{border-bottom:none;}
    td{padding:13px 16px;font-size:14px;vertical-align:middle;}
    td.address{max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:#555;}
    td.name{font-weight:600;}
    td.phone a{color:#cc0000;text-decoration:none;font-weight:600;}
    td.phone a:hover{text-decoration:underline;}
    td.email{color:#555;font-size:13px;}
    td.date{color:#aaa;font-size:12px;white-space:nowrap;}
    .badge{display:inline-block;padding:4px 10px;border-radius:12px;font-size:11px;font-weight:bold;}
    .badge-new{background:#fef3c7;color:#92400e;}
    .badge-contacted{background:#dbeafe;color:#1e40af;}
    .badge-closed{background:#dcfce7;color:#166534;}
    .badge-lost{background:#f3f4f6;color:#6b7280;}
    .status-form{margin-top:6px;display:flex;align-items:center;}
    .status-form select{padding:5px 8px;border:1.5px solid #ddd;border-radius:6px;font-size:12px;background:#fff;}
    .status-form button{background:#cc0000;color:#fff;border:none;padding:5px 10px;border-radius:6px;font-size:12px;cursor:pointer;margin-left:4px;}
    .btn-del{background:transparent;border:none;color:#ddd;font-size:16px;cursor:pointer;padding:4px 8px;border-radius:6px;transition:color .15s,background .15s;}
    .btn-del:hover{color:#cc0000;background:#fff0f0;}
    .empty{text-align:center;padding:60px 20px;color:#aaa;}
    .empty .icon{font-size:56px;margin-bottom:14px;}
    .empty p{font-size:15px;}

    @media(max-width:768px){
      .sidebar{display:none;}
      .main{margin-left:0;}
      .content{padding:16px;}
      .topbar{padding:12px 16px;}
      .topbar h1{font-size:16px;}
    }
  </style>
</head>
<body>

<div class="sidebar">
  <div class="sidebar-logo">Great <span>Properties</span> GA<small>Admin Dashboard</small></div>
  <nav>
    <a class="nav-item <?= !$filter?'active':'' ?>" href="admin.php"><span class="icon">&#128203;</span> All Leads</a>
    <a class="nav-item <?= $filter==='New'?'active':'' ?>" href="admin.php?status=New"><span class="icon">&#127381;</span> New Leads</a>
    <a class="nav-item <?= $filter==='Contacted'?'active':'' ?>" href="admin.php?status=Contacted"><span class="icon">&#128222;</span> Contacted</a>
    <a class="nav-item <?= $filter==='Closed'?'active':'' ?>" href="admin.php?status=Closed"><span class="icon">&#9989;</span> Closed</a>
  </nav>
  <div class="sidebar-footer">Logged in as <strong style="color:#fff">admin</strong><br><a href="logout.php">&rarr; Log out</a></div>
</div>

<div class="main">
  <div class="topbar">
    <h1>&#128203; Leads Dashboard</h1>
    <div class="topbar-right">
      <span style="font-size:12px;color:#aaa"><?= date('M j, Y') ?></span>
      <a class="btn-logout" href="logout.php">Log out</a>
    </div>
  </div>

  <div class="content">

    <div class="stats">
      <div class="stat-card red"><div class="stat-icon">&#128101;</div><div class="stat-num"><?= $total ?></div><div class="stat-label">Total Leads</div></div>
      <div class="stat-card green"><div class="stat-icon">&#128197;</div><div class="stat-num"><?= $today ?></div><div class="stat-label">Today</div></div>
      <div class="stat-card blue"><div class="stat-icon">&#128198;</div><div class="stat-num"><?= $this_week ?></div><div class="stat-label">This Week</div></div>
      <div class="stat-card yellow"><div class="stat-icon">&#127381;</div><div class="stat-num"><?= $new_leads ?></div><div class="stat-label">New / Unread</div></div>
    </div>

    <div class="toolbar">
      <form method="GET" action="admin.php">
        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name, email, phone, address...">
        <select name="status">
          <option value="">All Statuses</option>
          <option value="New"       <?= $filter==='New'?'selected':'' ?>>New</option>
          <option value="Contacted" <?= $filter==='Contacted'?'selected':'' ?>>Contacted</option>
          <option value="Closed"    <?= $filter==='Closed'?'selected':'' ?>>Closed</option>
          <option value="Lost"      <?= $filter==='Lost'?'selected':'' ?>>Lost</option>
        </select>
        <button type="submit" class="btn-search">Search</button>
        <a href="admin.php" class="btn-clear">Clear</a>
      </form>
      <span class="result-count"><?= $count ?> result<?= $count!==1?'s':'' ?></span>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th><th>Name</th><th>Phone</th><th>Email</th>
            <th>Address</th><th>Status</th><th>Date</th><th></th>
          </tr>
        </thead>
        <tbody>
        <?php if ($count === 0): ?>
          <tr><td colspan="8"><div class="empty"><div class="icon">&#128307;</div><p>No leads found<?= ($search||$filter)?' for this filter':'' ?>.</p></div></td></tr>
        <?php else: ?>
          <?php foreach ($leads as $row): ?>
          <tr>
            <td style="color:#ccc;font-size:12px">#<?= $row['id'] ?></td>
            <td class="name"><?= htmlspecialchars($row['name']) ?></td>
            <td class="phone"><a href="tel:<?= htmlspecialchars($row['phone']) ?>"><?= htmlspecialchars($row['phone']) ?></a></td>
            <td class="email"><?= htmlspecialchars($row['email']) ?></td>
            <td class="address" title="<?= htmlspecialchars($row['address']) ?>"><?= htmlspecialchars($row['address']) ?></td>
            <td>
              <?php
                $s   = $row['status'];
                $cls = match($s) {
                  'New'       => 'badge-new',
                  'Contacted' => 'badge-contacted',
                  'Closed'    => 'badge-closed',
                  default     => 'badge-lost'
                };
              ?>
              <span class="badge <?= $cls ?>"><?= htmlspecialchars($s) ?></span>
              <form method="POST" class="status-form">
                <input type="hidden" name="lead_id" value="<?= $row['id'] ?>">
                <select name="status">
                  <option value="New"       <?= $s==='New'?'selected':'' ?>>New</option>
                  <option value="Contacted" <?= $s==='Contacted'?'selected':'' ?>>Contacted</option>
                  <option value="Closed"    <?= $s==='Closed'?'selected':'' ?>>Closed</option>
                  <option value="Lost"      <?= $s==='Lost'?'selected':'' ?>>Lost</option>
                </select>
                <button type="submit" name="update_status">&#10003;</button>
              </form>
            </td>
            <td class="date"><?= date('M j, Y g:i a', strtotime($row['created_at'])) ?></td>
            <td>
              <form method="POST" onsubmit="return confirm('Delete this lead?')">
                <input type="hidden" name="lead_id" value="<?= $row['id'] ?>">
                <button type="submit" name="delete_lead" class="btn-del" title="Delete">&#128465;</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

</body>
</html>
