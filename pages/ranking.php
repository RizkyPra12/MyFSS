<?php
$tab = $_GET['tab'] ?? 'military';
$rankings = [];

if($tab === 'military') {
    $rankings = Military::getRankings(50);
}
?>

<a href="?page=dashboard" class="back-btn"><?=icon('back')?> Back</a>

<div class="card">
    <div class="card-header">🏆 Rankings</div>
    <div style="color:var(--text-secondary)">Global leaderboards and statistics</div>
</div>

<div class="tabs">
    <a href="?page=ranking&tab=military" class="tab <?=$tab==='military'?'active':''?>">
        Military Power
    </a>
    <div class="tab disabled" style="opacity:0.4;cursor:not-allowed">
        Economy (Coming Soon)
    </div>
    <div class="tab disabled" style="opacity:0.4;cursor:not-allowed">
        Population (Coming Soon)
    </div>
</div>

<?php if($tab === 'military'): ?>
<div class="card">
    <div class="card-header">⚔️ Military Power Rankings</div>
    
    <?php if(empty($rankings)): ?>
    <div style="text-align:center;padding:3rem 1rem;color:var(--text-muted)">
        <div style="font-size:2rem;margin-bottom:1rem;opacity:0.3">⚔️</div>
        <div>No military data available yet</div>
        <div style="font-size:0.85rem;margin-top:0.5rem">Update your military statistics in Settings to appear here</div>
    </div>
    <?php else: ?>
    <div style="overflow-x:auto">
        <table class="ranking-table">
            <thead>
                <tr>
                    <th style="width:60px">Rank</th>
                    <th>Country</th>
                    <th style="text-align:right">Total Active</th>
                    <th style="text-align:right">Reserves</th>
                    <th style="text-align:right">Equipment</th>
                    <th style="text-align:center">Index</th>
                    <th style="text-align:right">Military Index</th>
                    <th style="text-align:right">Daily Cost</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($rankings as $i => $r): 
                    $rank = $i + 1;
                    $medalClass = $rank === 1 ? 'gold' : ($rank === 2 ? 'silver' : ($rank === 3 ? 'bronze' : ''));
                ?>
                <tr class="rank-row">
                    <td>
                        <div class="rank-badge <?=$medalClass?>">
                            <?php if($rank <= 3): ?>
                                <?=$rank===1?'🥇':($rank===2?'🥈':'🥉')?>
                            <?php else: ?>
                                #<?=$rank?>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <div class="country-cell">
                            <?php if($r['flag_url']): ?>
                            <img src="<?=h($r['flag_url'])?>" class="rank-flag" alt="Flag">
                            <?php endif; ?>
                            <div>
                                <div class="country-name"><?=h($r['country_name'])?></div>
                                <div class="country-user">@<?=h($r['username'])?></div>
                            </div>
                        </div>
                    </td>
                    <td style="text-align:right;font-family:monospace"><?=number_format($r['total_active'])?></td>
                    <td style="text-align:right;font-family:monospace"><?=number_format($r['reserve_personnel'])?></td>
                    <td style="text-align:right;font-family:monospace"><?=number_format($r['defense_equipment'])?></td>
                    <td style="text-align:center">
                        <span class="index-badge"><?=number_format($r['index_factor'], 2)?></span>
                    </td>
                    <td style="text-align:right">
                        <span class="military-index"><?=number_format($r['military_index'], 3)?></span>
                    </td>
                    <td style="text-align:right">
                        <span class="spending-badge"><?=number_format($r['military_spending'], 0)?> FCD</span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<style>
.tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
    border-bottom: 2px solid var(--border);
    overflow-x: auto;
}

.tab {
    padding: 0.8rem 1.5rem;
    text-decoration: none;
    color: var(--text-secondary);
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    transition: all 0.2s;
    white-space: nowrap;
}

.tab:hover:not(.disabled) {
    color: var(--blue);
    background: rgba(119,184,240,0.05);
}

.tab.active {
    color: var(--blue);
    border-bottom-color: var(--blue);
    font-weight: 700;
}

.ranking-table {
    width: 100%;
    border-collapse: collapse;
}

.ranking-table thead {
    background: var(--bg-input);
}

.ranking-table th {
    padding: 1rem;
    font-weight: 700;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-secondary);
}

.ranking-table td {
    padding: 1rem;
    border-bottom: 1px solid var(--border);
}

.rank-row {
    transition: all 0.2s;
}

.rank-row:hover {
    background: var(--bg-hover);
}

.rank-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    border-radius: 8px;
    font-weight: 900;
    font-size: 1.1rem;
}

.rank-badge.gold {
    background: linear-gradient(135deg, #FFD700, #FFA500);
    color: #000;
}

.rank-badge.silver {
    background: linear-gradient(135deg, #C0C0C0, #A0A0A0);
    color: #000;
}

.rank-badge.bronze {
    background: linear-gradient(135deg, #CD7F32, #8B4513);
    color: #fff;
}

.country-cell {
    display: flex;
    align-items: center;
    gap: 0.8rem;
}

.rank-flag {
    width: 40px;
    height: 30px;
    object-fit: cover;
    border-radius: 4px;
    border: 1px solid var(--border);
}

.country-name {
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 0.2rem;
}

.country-user {
    font-size: 0.8rem;
    color: var(--text-secondary);
}

.index-badge {
    display: inline-block;
    padding: 0.3rem 0.6rem;
    background: rgba(119,184,240,0.1);
    border: 1px solid var(--blue);
    border-radius: 4px;
    font-weight: 700;
    color: var(--blue);
    font-family: monospace;
}

.military-index {
    font-size: 1.1rem;
    font-weight: 900;
    color: var(--success);
    font-family: monospace;
}

.spending-badge {
    font-weight: 600;
    color: var(--error);
    font-family: monospace;
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .ranking-table {
        font-size: 0.85rem;
    }
    
    .ranking-table th,
    .ranking-table td {
        padding: 0.6rem 0.4rem;
    }
    
    .rank-flag {
        width: 30px;
        height: 22px;
    }
}
</style>
