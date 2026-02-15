<?php
require_once 'config.php';
require_once 'includes/Backend.php';
require_once 'lang.php';
session_name(SESSION_NAME);
session_start();

if(!Auth::check()||!Auth::isAdmin()) {
    header('Location: index.php');
    exit;
}

$pg = $_GET['page'] ?? 'dashboard';
$U = User::get();

// POST Handlers
if($_SERVER['REQUEST_METHOD']==='POST'){
    $act = $_POST['action'] ?? '';
    
    if($act==='topup_fcd'){
        $username = H::s($_POST['username']??'');
        $amount = (float)($_POST['amount']??0);
        if($amount<1){$_SESSION['error']='Invalid amount';header('Location: ?page=find');exit;}
        $target = DB::q("SELECT uuid FROM users WHERE username=?",[$username])->fetch();
        if(!$target){$_SESSION['error']='User not found';header('Location: ?page=find');exit;}
        try{
            DB::begin();
            DB::q("UPDATE users SET fcd_balance=fcd_balance+? WHERE uuid=?",[$amount,$target['uuid']]);
            $txid='TX'.strtoupper(bin2hex(random_bytes(6)));
            DB::q("INSERT INTO wallet_transactions (txn_id,to_uuid,type,amount,memo) VALUES (?,?,?,?,?)",
                [$txid,$target['uuid'],'topup',$amount,'Admin top-up by @'.$U['username']]);
            DB::commit();
            $_SESSION['success']="Added {$amount} FCD to @{$username}";
        }catch(Exception $e){
            DB::rollback();
            $_SESSION['error']='Top-up failed';
        }
        header('Location: ?page=find');exit;
    }
    
    if($act==='create_event'){
        $name=H::s($_POST['event_name']??'');
        $loc=H::s($_POST['location']??'');
        $desc=H::s($_POST['description']??'');
        $start=$_POST['date_started']??'';
        $end=$_POST['date_ended']??'';
        if(empty($name)||empty($start)||empty($end)){$_SESSION['error']='Fill all required fields';header('Location: ?page=events');exit;}
        if(strtotime($end)<strtotime($start)){$_SESSION['error']='End date must be after start date';header('Location: ?page=events');exit;}
        try{
            Events::create(['event_name'=>$name,'location'=>$loc,'description'=>$desc,'date_started'=>$start,'date_ended'=>$end]);
            $_SESSION['success']='Event created';
        }catch(Exception $e){
            $_SESSION['error']='Failed to create event';
        }
        header('Location: ?page=events');exit;
    }
    
    if($act==='delete_event'){
        $eid=H::s($_POST['event_id']??'');
        try{
            DB::q("DELETE FROM events WHERE event_id=?",[$eid]);
            DB::q("DELETE FROM event_participants WHERE event_id=?",[$eid]);
            $_SESSION['success']='Event deleted';
        }catch(Exception $e){
            $_SESSION['error']='Failed to delete';
        }
        header('Location: ?page=events');exit;
    }
    
    if($act==='create_vote'){
        $title=H::s($_POST['vote_title']??'');
        $desc=H::s($_POST['vote_description']??'');
        $opts=$_POST['options']??'';
        $start=$_POST['start_date']??'';
        $end=$_POST['end_date']??'';
        if(empty($title)||empty($opts)||empty($start)||empty($end)){$_SESSION['error']='Fill all fields';header('Location: ?page=votes');exit;}
        if(strtotime($end)<strtotime($start)){$_SESSION['error']='End must be after start';header('Location: ?page=votes');exit;}
        $options=array_filter(array_map('trim',explode("\n",$opts)));
        if(count($options)<2){$_SESSION['error']='Need at least 2 options';header('Location: ?page=votes');exit;}
        try{
            Voting::create(['vote_title'=>$title,'vote_description'=>$desc,'options'=>json_encode($options),'start_date'=>$start,'end_date'=>$end]);
            $_SESSION['success']='Vote created';
        }catch(Exception $e){
            $_SESSION['error']='Failed';
        }
        header('Location: ?page=votes');exit;
    }
    
    if($act==='delete_vote'){
        $vid=H::s($_POST['vote_id']??'');
        try{
            DB::q("DELETE FROM votes WHERE vote_id=?",[$vid]);
            DB::q("DELETE FROM vote_casts WHERE vote_id=?",[$vid]);
            $_SESSION['success']='Vote deleted';
        }catch(Exception $e){
            $_SESSION['error']='Failed';
        }
        header('Location: ?page=votes');exit;
    }
}

function detectLogo(){
    foreach(['logo','icon'] as $n){
        foreach(['png','jpg','jpeg','svg','webp','gif'] as $f){
            if(file_exists(__DIR__."/{$n}.{$f}")) return "{$n}.{$f}";
        }
    }
    return null;
}
$logo=detectLogo();

function pgDashboard(){
    global $logo;
    $stats=[
        'users'=>DB::q("SELECT COUNT(*) c FROM users")->fetch()['c']??0,
        'events'=>DB::q("SELECT COUNT(*) c FROM events")->fetch()['c']??0,
        'votes'=>DB::q("SELECT COUNT(*) c FROM votes")->fetch()['c']??0,
        'uploads'=>count(FileUpload::listAll()),
        'total_fcd'=>DB::q("SELECT SUM(fcd_balance) s FROM users")->fetch()['s']??0,
        'transactions'=>DB::q("SELECT COUNT(*) c FROM wallet_transactions")->fetch()['c']??0
    ];
    ?>
    <div class="card">
      <div class="card-header">🔧 Admin Dashboard</div>
      <p style="color:var(--text-secondary)">Platform management and statistics</p>
    </div>
    
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(clamp(140px,30vw,200px),1fr));gap:clamp(0.8rem,2vw,1rem);margin-bottom:1.5rem">
      <?php foreach([
        ['Users','👥',$stats['users']],
        ['Events','📅',$stats['events']],
        ['Votes','🗳️',$stats['votes']],
        ['Uploads','☁️',$stats['uploads']],
        ['FCD Supply','💰',number_format($stats['total_fcd'],0)],
        ['Transactions','📊',$stats['transactions']]
      ] as [$label,$icon,$val]):?>
      <div class="stat-card">
        <div class="stat-icon"><?=$icon?></div>
        <div class="stat-value"><?=$val?></div>
        <div class="stat-label"><?=$label?></div>
      </div>
      <?php endforeach;?>
    </div>
    
    <div class="admin-grid">
      <a href="?page=members" class="admin-btn">
        <div class="admin-btn-icon">👥</div>
        <div class="admin-btn-label">Members</div>
      </a>
      <a href="?page=events" class="admin-btn">
        <div class="admin-btn-icon">📅</div>
        <div class="admin-btn-label">Events</div>
      </a>
      <a href="?page=votes" class="admin-btn">
        <div class="admin-btn-icon">🗳️</div>
        <div class="admin-btn-label">Votes</div>
      </a>
      <a href="?page=find" class="admin-btn">
        <div class="admin-btn-icon">🔍</div>
        <div class="admin-btn-label">Find User</div>
      </a>
      <a href="index.php" class="admin-btn admin-btn-wide">
        <div class="admin-btn-icon">🏠</div>
        <div class="admin-btn-label">Back to Site</div>
      </a>
    </div>
    <?php
}

function pgMembers(){
    $users=DB::q("SELECT uuid,username,country_name,tier,fcd_balance,created_at FROM users ORDER BY created_at DESC LIMIT 100")->fetchAll();
    ?>
    <a href="?page=dashboard" class="back-btn">← Back to Admin</a>
    <div class="card">
      <div class="card-header">👥 Members (<?=count($users)?>)</div>
      <div style="overflow-x:auto">
        <table class="admin-table">
          <thead>
            <tr><th>Username</th><th>Country</th><th>Tier</th><th>FCD</th><th>Joined</th></tr>
          </thead>
          <tbody>
            <?php foreach($users as $u):?>
            <tr>
              <td><strong>@<?=H::s($u['username'])?></strong></td>
              <td><?=H::s($u['country_name'])?></td>
              <td><span class="tier-badge <?=H::s($u['tier'])?>"><?=H::s($u['tier'])?></span></td>
              <td><?=number_format($u['fcd_balance'],0)?></td>
              <td><?=H::date($u['created_at'])?></td>
            </tr>
            <?php endforeach;?>
          </tbody>
        </table>
      </div>
    </div>
    <?php
}

function pgFind(){
    $user=null;
    if(isset($_GET['u'])){
        $user=DB::q("SELECT * FROM users WHERE username=?",[H::s($_GET['u'])])->fetch();
    }
    ?>
    <a href="?page=dashboard" class="back-btn">← Back to Admin</a>
    <div class="card">
      <div class="card-header">🔍 Find User</div>
      <form method="GET">
        <input type="hidden" name="page" value="find">
        <div class="fg"><label>Username</label><input type="text" name="u" value="<?=H::s($_GET['u']??'')?>" placeholder="@username"></div>
        <button type="submit" class="btn btn-block">Search</button>
      </form>
    </div>
    
    <?php if(isset($_GET['u'])&&!$user):?>
      <div class="alert alert-error">User not found</div>
    <?php elseif($user):?>
      <div class="card">
        <div class="card-header">User Details</div>
        <div style="display:grid;gap:0.5rem">
          <div style="display:flex;justify-content:space-between;padding:0.5rem 0;border-bottom:1px solid var(--border)">
            <span style="color:var(--text-secondary)">Username</span><strong>@<?=H::s($user['username'])?></strong>
          </div>
          <div style="display:flex;justify-content:space-between;padding:0.5rem 0;border-bottom:1px solid var(--border)">
            <span style="color:var(--text-secondary)">Country</span><strong><?=H::s($user['country_name'])?></strong>
          </div>
          <div style="display:flex;justify-content:space-between;padding:0.5rem 0;border-bottom:1px solid var(--border)">
            <span style="color:var(--text-secondary)">FCD Balance</span><strong><?=number_format($user['fcd_balance'],2)?></strong>
          </div>
          <div style="display:flex;justify-content:space-between;padding:0.5rem 0;border-bottom:1px solid var(--border)">
            <span style="color:var(--text-secondary)">Tier</span><span class="tier-badge <?=H::s($user['tier'])?>"><?=H::s($user['tier'])?></span>
          </div>
          <div style="display:flex;justify-content:space-between;padding:0.5rem 0">
            <span style="color:var(--text-secondary)">Joined</span><strong><?=H::date($user['created_at'])?></strong>
          </div>
        </div>
      </div>
      
      <div class="card">
        <div class="card-header">💰 FCD Top-Up</div>
        <form method="POST">
          <input type="hidden" name="action" value="topup_fcd">
          <input type="hidden" name="username" value="<?=H::s($user['username'])?>">
          <div class="fg"><label>Amount to Add</label><input type="number" name="amount" required min="1" step="0.01"></div>
          <button type="submit" class="btn btn-block">Top-Up FCD</button>
        </form>
      </div>
    <?php endif;?>
    <?php
}

function pgEvents(){
    $events=Events::all();
    ?>
    <a href="?page=dashboard" class="back-btn">← Back to Admin</a>
    <div class="card">
      <div class="card-header">📅 Create Event</div>
      <form method="POST">
        <input type="hidden" name="action" value="create_event">
        <div class="fg"><label>Event Name *</label><input type="text" name="event_name" required></div>
        <div class="fg"><label>Location *</label><input type="text" name="location" required></div>
        <div class="fg"><label>Description</label><textarea name="description" rows="3"></textarea></div>
        <div class="fg"><label>Start Date *</label><input type="datetime-local" name="date_started" required></div>
        <div class="fg"><label>End Date *</label><input type="datetime-local" name="date_ended" required></div>
        <button type="submit" class="btn btn-block">Create Event</button>
      </form>
    </div>
    
    <div class="card">
      <div class="card-header">All Events (<?=count($events)?>)</div>
      <?php if(!$events):?><p style="text-align:center;color:var(--text-muted);padding:2rem">No events</p><?php else:?>
      <?php foreach($events as $e):
        $now=time();$st=strtotime($e['date_started']);$en=strtotime($e['date_ended']);
        $stat=$now>$en?'ended':($now>=$st?'active':'upcoming');
        $parts=DB::q("SELECT COUNT(*) c FROM event_participants WHERE event_id=?",[$e['event_id']])->fetch()['c']??0;
      ?>
      <div style="border-bottom:1px solid var(--border);padding:1rem 0">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem">
          <div style="font-weight:700"><?=H::s($e['event_name'])?></div>
          <span class="event-status <?=$stat?>"><?=$stat?></span>
        </div>
        <div style="font-size:clamp(0.8rem,2.5vw,0.9rem);color:var(--text-secondary)">
          📍 <?=H::s($e['location'])?> • <?=$parts?> participants
        </div>
        <div style="margin-top:0.5rem">
          <form method="POST" style="display:inline" onsubmit="return confirm('Delete this event?')">
            <input type="hidden" name="action" value="delete_event">
            <input type="hidden" name="event_id" value="<?=H::s($e['event_id'])?>">
            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
          </form>
        </div>
      </div>
      <?php endforeach;?>
      <?php endif;?>
    </div>
    <?php
}

function pgVotes(){
    $votes=Voting::all();
    ?>
    <a href="?page=dashboard" class="back-btn">← Back to Admin</a>
    <div class="card">
      <div class="card-header">🗳️ Create Vote</div>
      <form method="POST">
        <input type="hidden" name="action" value="create_vote">
        <div class="fg"><label>Title *</label><input type="text" name="vote_title" required></div>
        <div class="fg"><label>Description</label><textarea name="vote_description" rows="2"></textarea></div>
        <div class="fg"><label>Options (one per line) *</label><textarea name="options" rows="4" required placeholder="Option 1&#10;Option 2&#10;Option 3"></textarea></div>
        <div class="fg"><label>Start Date *</label><input type="datetime-local" name="start_date" required></div>
        <div class="fg"><label>End Date *</label><input type="datetime-local" name="end_date" required></div>
        <button type="submit" class="btn btn-block">Create Vote</button>
      </form>
    </div>
    
    <div class="card">
      <div class="card-header">All Votes (<?=count($votes)?>)</div>
      <?php if(!$votes):?><p style="text-align:center;color:var(--text-muted);padding:2rem">No votes</p><?php else:?>
      <?php foreach($votes as $v):
        $active=Voting::isActive($v);
        $results=Voting::getResults($v['vote_id']);
        $total=array_sum(array_column($results,'count'));
      ?>
      <div style="border-bottom:1px solid var(--border);padding:1rem 0">
        <div style="font-weight:700"><?=H::s($v['vote_title'])?></div>
        <div style="font-size:clamp(0.8rem,2.5vw,0.9rem);color:var(--text-secondary);margin:0.5rem 0">
          <?=H::date($v['start_date'])?> - <?=H::date($v['end_date'])?>
        </div>
        <div style="margin-bottom:0.5rem">
          <?php foreach($results as $r):$pct=$total>0?round($r['count']/$total*100):0;?>
          <div style="font-size:clamp(0.75rem,2vw,0.85rem);margin:0.25rem 0">
            <?=H::s($r['option'])?>: <strong><?=$r['count']?></strong> (<?=$pct?>%)
          </div>
          <?php endforeach;?>
        </div>
        <div style="font-size:clamp(0.75rem,2vw,0.85rem);color:var(--text-secondary)">Total votes: <?=$total?></div>
        <form method="POST" style="display:inline;margin-top:0.5rem" onsubmit="return confirm('Delete?')">
          <input type="hidden" name="action" value="delete_vote">
          <input type="hidden" name="vote_id" value="<?=H::s($v['vote_id'])?>">
          <button type="submit" class="btn btn-danger btn-sm">Delete</button>
        </form>
      </div>
      <?php endforeach;?>
      <?php endif;?>
    </div>
    <?php
}

$_pages=['dashboard'=>'pgDashboard','members'=>'pgMembers','find'=>'pgFind','events'=>'pgEvents','votes'=>'pgVotes'];
$fn=$_pages[$pg]??'pgDashboard';

header('Content-Type: text/html; charset=UTF-8');
?><!DOCTYPE html>
<html lang="<?=getCurrentLanguage()?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=5">
<meta name="theme-color" content="#0a0a0f">
<title>Admin Panel - <?=SITE_NAME?></title>
<link rel="stylesheet" href="assets/css/main.css">
<style>
.stat-card{background:linear-gradient(135deg,var(--bg-card),#1a1a2a);border:1px solid var(--border);border-radius:clamp(12px,3vw,16px);padding:clamp(1rem,3vw,1.5rem);text-align:center;transition:all .3s;box-shadow:var(--shadow);animation:scaleIn .4s ease}
.stat-card:hover{transform:translateY(-4px);box-shadow:var(--shadow-lg)}
.stat-icon{font-size:clamp(2rem,6vw,3rem);margin-bottom:0.5rem}
.stat-value{font-size:clamp(1.5rem,4vw,2rem);font-weight:900;color:var(--primary);line-height:1}
.stat-label{font-size:clamp(0.75rem,2vw,0.85rem);color:var(--text-secondary);margin-top:0.25rem;text-transform:uppercase;letter-spacing:0.5px}
.admin-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(clamp(120px,30vw,160px),1fr));gap:clamp(0.8rem,2vw,1rem)}
.admin-btn{background:var(--bg-card);border:1px solid var(--border);border-radius:clamp(12px,3vw,16px);padding:clamp(1.5rem,4vw,2rem) clamp(1rem,3vw,1.5rem);text-align:center;text-decoration:none;color:var(--text-primary);transition:all .3s;cursor:pointer;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:clamp(0.5rem,2vw,0.8rem);box-shadow:var(--shadow);animation:scaleIn .4s ease}
.admin-btn:hover{transform:scale(1.05) translateY(-4px);border-color:var(--primary);box-shadow:0 8px 24px rgba(0,170,19,0.2)}
.admin-btn-icon{font-size:clamp(2rem,6vw,3rem)}
.admin-btn-label{font-size:clamp(0.85rem,2.5vw,1rem);font-weight:600;text-transform:uppercase;letter-spacing:0.5px}
.admin-btn-wide{grid-column:span 2}
.admin-table{width:100%;border-collapse:collapse;font-size:clamp(0.85rem,2.5vw,0.95rem)}
.admin-table th{background:var(--bg-input);padding:clamp(0.6rem,2vw,0.8rem);text-align:left;font-weight:700;border-bottom:1px solid var(--border);position:sticky;top:0;z-index:1}
.admin-table td{padding:clamp(0.6rem,2vw,0.8rem);border-bottom:1px solid var(--border)}
.admin-table tr:hover{background:var(--bg-hover)}
.tier-badge{display:inline-block;padding:clamp(0.2rem,1vw,0.3rem) clamp(0.4rem,1.5vw,0.6rem);border-radius:999px;font-size:clamp(0.7rem,2vw,0.75rem);font-weight:600;text-transform:uppercase}
.tier-badge.free{background:rgba(153,153,153,0.1);color:var(--text-muted)}
.tier-badge.tier1,.tier-badge.tier2,.tier-badge.tier3{background:rgba(0,170,19,0.1);color:var(--primary)}
.tier-badge.special{background:rgba(0,170,255,0.1);color:var(--info)}
@keyframes scaleIn{from{transform:scale(0.95);opacity:0}to{transform:scale(1);opacity:1}}
@media(max-width:768px){.admin-btn-wide{grid-column:span 1}}
</style>
</head>
<body>
<div id="app">
<main class="content">
<?php
if(isset($_SESSION['success'])){echo'<div class="alert alert-success">'.H::s($_SESSION['success']).'</div>';unset($_SESSION['success']);}
if(isset($_SESSION['error'])){echo'<div class="alert alert-error">'.H::s($_SESSION['error']).'</div>';unset($_SESSION['error']);}
if($logo):?><img src="<?=H::s($logo)?>" class="logo" alt="Logo"><?php endif;
$fn();
?>
</main>
</div>
</body>
</html>
