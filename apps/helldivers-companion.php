<?php
session_start();
require_once __DIR__ . '/../assets/config.php';
if (!isset($_SESSION['user'])) { header('Location: ../index.php'); exit; }

$headers = "X-Super-Client: MelonOS-Companion\r\nX-Super-Contact: melonos@prueba.com\r\n";
$ctx = stream_context_create(['http' => ['header' => $headers, 'timeout' => 10]]);

function hd_fetch($url, $ctx) {
    $r = @file_get_contents($url, false, $ctx);
    return $r ? json_decode($r, true) : null;
}

$base           = 'https://api.helldivers2.dev/api/v1';
$campanas       = hd_fetch("$base/campaigns",   $ctx) ?? [];
$ordenes        = hd_fetch("$base/assignments", $ctx) ?? [];
$despachos      = hd_fetch("$base/dispatches",  $ctx) ?? [];
$guerra         = hd_fetch("$base/war",         $ctx) ?? [];
$todos_planetas = hd_fetch("$base/planets",     $ctx) ?? [];

$planetaMap = [];
foreach ($todos_planetas as $p) $planetaMap[$p['index']] = $p;

usort($campanas, fn($a,$b) =>
    ($b['planet']['statistics']['playerCount'] ?? 0) -
    ($a['planet']['statistics']['playerCount'] ?? 0)
);

$hdActivos      = array_reduce($campanas, fn($c,$x) => $c + ($x['planet']['statistics']['playerCount'] ?? 0), 0);
$frentesActivos = count($campanas);
$vsTerminids = $vsAutomatons = $vsIlluminate = 0;
foreach ($campanas as $c) {
    $d = strtolower($c['planet']['currentOwner'] ?? '');
    $j = $c['planet']['statistics']['playerCount'] ?? 0;
    if (str_contains($d,'terminid'))   $vsTerminids  += $j;
    if (str_contains($d,'automaton'))  $vsAutomatons += $j;
    if (str_contains($d,'illuminate')) $vsIlluminate += $j;
}

$statsSum = ['terminidKills'=>0,'automatonKills'=>0,'illuminateKills'=>0,
             'deaths'=>0,'friendlies'=>0,'missionsWon'=>0,'missionsLost'=>0,
             'bulletsFired'=>0,'bulletsHit'=>0,'missionTime'=>0];
foreach ($todos_planetas as $p) {
    $s = $p['statistics'] ?? [];
    foreach ($statsSum as $k => $_) $statsSum[$k] += $s[$k] ?? 0;
}
$accuracy   = $statsSum['bulletsFired'] > 0 ? round($statsSum['bulletsHit']/$statsSum['bulletsFired']*100,2) : 0;
$days       = floor($statsSum['missionTime']/86400);
$hours      = floor(($statsSum['missionTime']%86400)/3600);
$mins       = floor(($statsSum['missionTime']%3600)/60);
$years      = floor($days/365); $remDays = $days % 365;
$months     = floor($remDays/30); $remDays2 = $remDays % 30;
$weeks      = floor($remDays2/7); $remDays3 = $remDays2 % 7;
$missionTimeStr = ($years>0?$years.'Y ':'').($months>0?$months.'M ':'').($weeks>0?$weeks.'W ':'').$remDays3.'d '.$hours.'h '.$mins.'m';
$totalKills = $statsSum['terminidKills']+$statsSum['automatonKills']+$statsSum['illuminateKills'];
$kd         = $statsSum['deaths'] > 0 ? round($totalKills/$statsSum['deaths'],2) : '—';
$missionTotal = $statsSum['missionsWon'] + $statsSum['missionsLost'];
$missionRate  = $missionTotal > 0 ? round($statsSum['missionsWon']/$missionTotal*100,1) : 0;
$htsRatio     = $statsSum['bulletsFired'] > 0 ? round($statsSum['bulletsHit']/$statsSum['bulletsFired'],11) : 0;

$imgBase = '../assets/img/companion/';
$biomaImgs = [
    'acidic'=>$imgBase.'Acidic_Desert_Landscape.webp','arctic'=>$imgBase.'Arctic_Landscape.webp',
    'frozen'=>$imgBase.'Arctic_Landscape.webp','ice'=>$imgBase.'Tundra_Landscape.webp',
    'tundra'=>$imgBase.'Tundra_Landscape.webp','polar'=>$imgBase.'PolarBulbs_many.webp',
    'icemoss'=>$imgBase.'PolarBulbs_many.webp','glaciers'=>$imgBase.'Tundra_Landscape.webp',
    'scorched'=>$imgBase.'Scorched_Wasteland_Landscape.webp','moor'=>$imgBase.'Scorched_Wasteland_Landscape.webp',
    'crimson'=>$imgBase.'Scorched_Wasteland_Landscape.webp','wasteland'=>$imgBase.'Scorched_Wasteland_Landscape.webp',
    'ashland'=>$imgBase.'Ashland_Landscape.webp','moon'=>$imgBase.'Barren_Moon_Landscape.webp',
    'moonscape'=>$imgBase.'Barren_Moon_Landscape.webp','barren'=>$imgBase.'Barren_Moon_Landscape.webp',
    'superearth'=>$imgBase.'Super_Earth_Landscape.webp','super earth'=>$imgBase.'Super_Earth_Landscape.webp',
    'ethereal'=>$imgBase.'Ethereal_Jungle_Landscape.webp','jungle'=>$imgBase.'Sparse_Jungle_Landscape.webp',
    'shadowed'=>$imgBase.'Shadowed_Jungle_Landscape.webp','rainforest'=>$imgBase.'Shadowed_Jungle_Landscape.webp',
    'temperate'=>$imgBase.'TemperateForest_Landscape.webp','forest'=>$imgBase.'TemperateForest_Landscape.webp',
    'swamp'=>$imgBase.'Swamp_Landscape.webp','bog'=>$imgBase.'Swamp_Landscape.webp',
    'foggy'=>$imgBase.'FoggySwamp2.webp','haunted'=>$imgBase.'FoggySwamp2.webp',
    'boneyard'=>$imgBase.'Frozen_Boneyard_Landscape.webp','hive'=>$imgBase.'Hive_World_Landscape.webp',
    'supercolony'=>$imgBase.'Supercolony_Landscape.webp','ionic'=>$imgBase.'Ionized_Grassland_Landscape.webp',
    'ionized'=>$imgBase.'Ionized_Grassland_Landscape.webp','grassland'=>$imgBase.'Grassland_Landscape.webp',
    'plains'=>$imgBase.'Grassland_Landscape.webp','highlands'=>$imgBase.'Tien_Kwan_Landscape.webp',
    'cliffs'=>$imgBase.'Tien_Kwan_Landscape.webp','volcanic'=>$imgBase.'Magma_base.webp',
    'magma'=>$imgBase.'Magma_base.webp','lava'=>$imgBase.'Magma_base.webp',
    'desert'=>$imgBase.'Copper_Desert_Landscape.webp','mesa'=>$imgBase.'Sandy_Mesa_Landscape.webp',
    'sandy'=>$imgBase.'Sandy_Mesa_Landscape.webp','oasis'=>$imgBase.'OasisDesertLandscape.webp',
    'canyon'=>$imgBase.'Quake_Desert_Landscape.webp','rocky'=>$imgBase.'Quake_Desert_Landscape.webp',
    'quake'=>$imgBase.'Quake_Desert_Landscape.webp','colonies'=>$imgBase.'CyberstanMap3.webp',
    'colony'=>$imgBase.'CyberstanMap3.webp','metropolis'=>$imgBase.'CyberstanMap3.webp',
    'cyberstan'=>$imgBase.'CyberstanMap3.webp','deadlands'=>$imgBase.'Acidic_Desert_Landscape.webp',
    'default'=>$imgBase.'Black_Hole_Landscape.webp',
];

function getBiomaImg($bioma, $imgs) {
    $b = strtolower(trim($bioma ?? ''));
    if (isset($imgs[$b])) return $imgs[$b];
    foreach ($imgs as $key => $url) { if ($key!=='default'&&str_contains($b,$key)) return $url; }
    foreach ($imgs as $key => $url) { if ($key!=='default'&&$b!==''&&str_contains($key,$b)) return $url; }
    return $imgs['default'];
}

function factionStyle($owner) {
    $d = strtolower($owner ?? '');
    if (str_contains($d,'terminid'))   return ['#ff9500','#3a1a00','Terminids',  'terminid', 'f-terminid'];
    if (str_contains($d,'automaton'))  return ['#ff3322','#3a0000','Automatons', 'automaton','f-automaton'];
    if (str_contains($d,'illuminate')) return ['#cc44ff','#25005a','Illuminate', 'illuminate','f-illuminate'];
    return ['#00e5ff','#002233','Humans','human','f-human'];
}

function taskLabel($task, $planetaMap) {
    $type=$task['type']??0; $values=$task['values']??[]; $vTypes=$task['valueTypes']??[];
    $planetId=null;
    foreach ($vTypes as $i=>$vt){if($vt==12){$planetId=$values[$i]??null;break;}}
    if($planetId===null) foreach($vTypes as $i=>$vt){if($vt==11){$planetId=$values[$i]??null;break;}}
    $planetName=($planetId!==null&&isset($planetaMap[$planetId]))?$planetaMap[$planetId]['name']:($planetId!==null?"Planet #$planetId":null);
    return match($type){
        11=>$planetName?"Liberate $planetName":"Liberate planet",
        13=>$planetName?"Defend $planetName":"Defend Super Earth",
        3 =>"Complete missions",
        2 =>"Eliminate enemies",
        12=>$planetName?"Control $planetName":"Control planet",
        default=>$planetName?"Objective: $planetName":"Objective",
    };
}

function formatExpiry($iso) {
    try {
        $dt=new DateTime($iso);$now=new DateTime();$diff=$now->diff($dt);
        if($diff->invert) return 'Expirada';
        $p=[];if($diff->d)$p[]=$diff->d.'d';if($diff->h)$p[]=$diff->h.'h';if($diff->i)$p[]=$diff->i.'m';
        return implode(' ',$p)?:'Inminente';
    }catch(Exception $e){return '—';}
}

// Build map data
$mapaData=[];
foreach($campanas as $c){
    $p=$c['planet'];
    [$fColor,,$fLabel,,]= factionStyle($p['currentOwner']??'');
    $health=$p['health']??0;$maxH=$p['maxHealth']?:1;
    $mapaData[]=['name'=>$p['name'],'sector'=>$p['sector'],
        'x'=>$p['position']['x']??0,'y'=>$p['position']['y']??0,
        'color'=>$fColor,'faction'=>$fLabel,
        'pct'=>round((1-$health/$maxH)*100),
        'players'=>$p['statistics']['playerCount']??0,
        'biome'=>$p['biome']['name']??'',
        'hazards'=>implode(', ',array_column($p['hazards']??[],'name')),
    ];
}
$mapaData[]=['name'=>'Super Earth','sector'=>'Sol','x'=>0,'y'=>0,
    'color'=>'#00e5ff','faction'=>'Humans','pct'=>100,'players'=>0,'biome'=>'Super Earth','hazards'=>''];
$wpLines=[];
foreach($campanas as $c){
    $p=$c['planet'];
    foreach(($p['waypoints']??[]) as $wi){
        if(isset($planetaMap[$wi])){$w=$planetaMap[$wi];
            $wpLines[]=['x1'=>$p['position']['x']??0,'y1'=>$p['position']['y']??0,
                'x2'=>$w['position']['x']??0,'y2'=>$w['position']['y']??0];}
    }
}
$mapaJson=json_encode($mapaData,JSON_UNESCAPED_UNICODE);
$wpJson=json_encode($wpLines,JSON_UNESCAPED_UNICODE);

// Faction data for overview
$factionBars = [
    ['label'=>'Illuminate','players'=>$vsIlluminate,'color'=>'#cc44ff','icon'=>'👁','check'=>true],
    ['label'=>'Terminids', 'players'=>$vsTerminids, 'color'=>'#ff9500','icon'=>'🦟','check'=>true],
    ['label'=>'Automatons','players'=>$vsAutomatons,'color'=>'#ff3322','icon'=>'🤖','check'=>true],
];
$impactMod = round($hdActivos / 2700000, 6);

// Pie data
$pieData = array_map(fn($c) => [
    'name'   => $c['planet']['name'],
    'players'=> $c['planet']['statistics']['playerCount'] ?? 0,
    'owner'  => $c['planet']['currentOwner'] ?? '',
], array_slice($campanas, 0, 9));
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="../assets/css/98.css">
<link rel="stylesheet" href="../assets/css/base.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,400;0,600;0,700;0,900;1,700&family=Share+Tech+Mono&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
    --gold:#e8c84a;--gold2:#f5d870;--cyan:#00e5ff;--magenta:#ff00cc;
    --orange:#ff9500;--red:#ff3322;--purple:#cc44ff;--green:#49e07d;
    --blue:#00aaff;--bg:#000;--panel:#06090d;--border:#1a2530;
    --border2:#2a3a4a;--text:#ccdde8;--muted:#4a6070;
}
body{background:var(--bg);color:var(--text);font-family:'Barlow Condensed',sans-serif;
    font-size:14px;overflow:hidden;height:100vh;display:flex;flex-direction:column;}

/* HEADER */
.hd-header{background:#000;border-bottom:2px solid var(--gold);flex-shrink:0;position:relative;}
.hd-header::before{content:'';position:absolute;top:0;left:0;right:0;height:1px;
    background:linear-gradient(90deg,transparent,var(--gold2),transparent);}
.hd-title-row{display:flex;align-items:center;gap:14px;padding:8px 16px 0;}
.hd-emblem{width:36px;height:36px;flex-shrink:0;position:relative;display:flex;align-items:center;justify-content:center;}
.hd-emblem::before{content:'';position:absolute;inset:0;background:var(--gold);clip-path:polygon(50% 0%,100% 50%,50% 100%,0% 50%);}
.hd-emblem-icon{position:relative;z-index:1;font-size:14px;line-height:1;}
.hd-brand{display:flex;flex-direction:column;line-height:1.1;}
.hd-brand-main{font-size:20px;font-weight:900;letter-spacing:0.14em;color:var(--gold);text-transform:uppercase;}
.hd-brand-sub{font-family:'Share Tech Mono',monospace;font-size:9px;color:#557;letter-spacing:0.25em;text-transform:uppercase;}
.hd-live{display:flex;align-items:center;gap:5px;font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--muted);letter-spacing:0.1em;}
.live-dot{width:7px;height:7px;border-radius:50%;background:var(--red);box-shadow:0 0 6px var(--red);animation:blink 1.1s infinite;}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.1}}
.btn-refresh{margin-left:auto;background:transparent;border:1px solid var(--gold);color:var(--gold);
    font-family:'Barlow Condensed',sans-serif;font-size:11px;font-weight:700;letter-spacing:0.14em;
    padding:5px 16px;cursor:pointer;text-transform:uppercase;display:flex;align-items:center;gap:6px;
    clip-path:polygon(6px 0%,100% 0%,calc(100% - 6px) 100%,0% 100%);transition:background .15s,color .15s;}
.btn-refresh:hover{background:var(--gold);color:#000;}
.btn-refresh svg{transition:transform .6s;}
.btn-refresh:hover svg{transform:rotate(360deg);}
.hd-stats-bar{display:flex;border-top:1px solid #1a2530;margin-top:8px;}
.hd-stat{flex:1;text-align:center;padding:6px 4px;border-right:1px solid #1a2530;position:relative;}
.hd-stat:last-child{border-right:none;}
.hd-stat+.hd-stat::before{content:'◆';position:absolute;left:-1px;top:50%;transform:translate(-50%,-50%);font-size:6px;color:#1a2530;}
.hd-stat-val{font-size:22px;font-weight:900;display:block;line-height:1;letter-spacing:0.04em;}
.hd-stat-lbl{font-family:'Share Tech Mono',monospace;font-size:8px;text-transform:uppercase;letter-spacing:0.14em;color:var(--muted);display:block;margin-top:1px;}

/* TABS */
.hd-tabs{display:flex;background:#000;border-bottom:1px solid #1a2530;padding:0 16px;flex-shrink:0;gap:2px;overflow-x:auto;}
.hd-tabs::-webkit-scrollbar{display:none}
.hd-tab{font-family:'Barlow Condensed',sans-serif;font-weight:700;font-size:12px;letter-spacing:0.16em;
    text-transform:uppercase;padding:10px 18px 8px;cursor:pointer;color:var(--muted);
    border-bottom:2px solid transparent;margin-bottom:-1px;white-space:nowrap;transition:color .15s;
    display:flex;align-items:center;gap:6px;user-select:none;}
.hd-tab:hover{color:var(--text);}
.hd-tab.active{color:var(--gold);border-bottom-color:var(--gold);}

/* SCROLL + CONTENT */
.hd-scroll{flex:1;overflow-y:auto;overflow-x:hidden;}
.hd-scroll::-webkit-scrollbar{width:3px}
.hd-scroll::-webkit-scrollbar-track{background:#000}
.hd-scroll::-webkit-scrollbar-thumb{background:#1a2530}
.hd-content{display:none;padding:14px 16px;}
.hd-content.active{display:block;}
#tab-overview.active,#tab-mapa.active{display:flex !important;padding:0 !important;height:100% !important;}

/* SECTION TITLE */
.sec-title{font-size:9px;font-weight:700;letter-spacing:0.35em;text-transform:uppercase;color:var(--gold);
    margin-bottom:12px;padding-bottom:6px;border-bottom:1px solid #1a2530;display:flex;align-items:center;gap:8px;}
.sec-title::before{content:'◆';font-size:7px;}

/* PLANET CARDS */
.planet-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:10px;}
.planet-card{background:#000;border:1px solid var(--gold);position:relative;cursor:pointer;
    transition:box-shadow .2s,border-color .2s;overflow:hidden;}
.planet-card:hover{box-shadow:0 0 16px rgba(232,200,74,.25),inset 0 0 16px rgba(232,200,74,.05);border-color:var(--gold2);}
.planet-card::before,.planet-card::after{content:'';position:absolute;width:10px;height:10px;border-color:var(--gold);border-style:solid;z-index:5;}
.planet-card::before{top:-1px;right:-1px;border-width:2px 2px 0 0;}
.planet-card::after{bottom:-1px;left:-1px;border-width:0 0 2px 2px;}
.pc-header{padding:7px 10px 6px;border-bottom:1px solid #1a2530;background:rgba(0,0,0,.92);display:flex;align-items:center;gap:8px;position:relative;z-index:2;}
.pc-type-icon{width:18px;height:18px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:11px;clip-path:polygon(50% 0%,100% 25%,100% 75%,50% 100%,0% 75%,0% 25%);}
.f-terminid .pc-type-icon{background:var(--orange);}
.f-automaton .pc-type-icon{background:var(--red);}
.f-illuminate .pc-type-icon{background:var(--purple);}
.f-human .pc-type-icon{background:var(--cyan);}
.pc-names{flex:1;min-width:0;}
.pc-planet-name{font-size:16px;font-weight:900;letter-spacing:0.06em;text-transform:uppercase;line-height:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.f-terminid .pc-planet-name{color:var(--orange);}
.f-automaton .pc-planet-name{color:var(--red);}
.f-illuminate .pc-planet-name{color:var(--purple);}
.f-human .pc-planet-name{color:var(--cyan);}
.pc-sector{font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.12em;text-transform:uppercase;color:var(--muted);margin-top:1px;}
.pc-img{position:relative;height:130px;overflow:hidden;}
.pc-img-bg{position:absolute;inset:0;background-size:cover;background-position:center;filter:brightness(.7) saturate(.9);transition:filter .3s,transform .4s;}
.planet-card:hover .pc-img-bg{filter:brightness(.9) saturate(1.1);transform:scale(1.05);}
.pc-img::after{content:'';position:absolute;inset:0;background:linear-gradient(180deg,transparent 40%,rgba(0,0,0,.95) 100%);}
.pc-hazard-row{position:absolute;bottom:6px;left:8px;right:8px;display:flex;align-items:center;gap:5px;z-index:3;}
.pc-hazard-icon{width:20px;height:20px;border-radius:3px;background:rgba(0,0,0,.7);border:1px solid rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;font-size:10px;}
.pc-players-badge{margin-left:auto;display:flex;align-items:center;gap:4px;font-family:'Share Tech Mono',monospace;font-size:9px;color:#aaa;}
.pc-players-dot{width:5px;height:5px;border-radius:50%;background:var(--green);box-shadow:0 0 5px var(--green);animation:blink 2s infinite;}
.pc-lib-section{padding:8px 10px 0;background:#000;}
.pc-lib-label{font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.2em;text-transform:uppercase;color:var(--muted);margin-bottom:4px;display:flex;align-items:center;gap:6px;}
.pc-lib-label-pct{margin-left:auto;font-size:11px;font-weight:700;letter-spacing:0.06em;}
.pc-lib-bar-wrap{height:18px;position:relative;margin-bottom:5px;background:#0a0e14;border:1px solid #1a2530;overflow:hidden;}
.pc-lib-bar-fill{position:absolute;top:0;bottom:0;left:0;background:repeating-linear-gradient(90deg,var(--cyan) 0px,var(--cyan) 14px,rgba(0,180,220,.7) 14px,rgba(0,180,220,.7) 16px);box-shadow:2px 0 8px var(--cyan);transition:width .6s ease;}
.pc-lib-bar-enemy{position:absolute;top:0;bottom:0;right:0;background:var(--orange);opacity:.7;transition:width .6s ease;}
.f-terminid .pc-lib-bar-fill{background:repeating-linear-gradient(90deg,var(--magenta) 0px,var(--magenta) 14px,#cc0099 14px,#cc0099 16px);box-shadow:2px 0 8px var(--magenta);}
.f-illuminate .pc-lib-bar-fill{background:repeating-linear-gradient(90deg,var(--purple) 0px,var(--purple) 14px,#9900cc 14px,#9900cc 16px);box-shadow:2px 0 8px var(--purple);}
.pc-lib-bar-tip{position:absolute;top:0;bottom:0;width:8px;background:inherit;clip-path:polygon(0 0,60% 0,100% 50%,60% 100%,0 100%);}
.pc-status{font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.14em;text-transform:uppercase;color:var(--muted);margin-bottom:6px;display:flex;align-items:center;gap:5px;}
.pc-footer{display:grid;grid-template-columns:repeat(4,1fr);border-top:1px solid #1a2530;background:#000;}
.pc-foot-cell{padding:5px 6px;text-align:center;border-right:1px solid #1a2530;font-family:'Share Tech Mono',monospace;font-size:9px;}
.pc-foot-cell:last-child{border-right:none;}
.pc-foot-label{color:var(--muted);font-size:8px;letter-spacing:.06em;}
.pc-foot-val{color:var(--text);font-size:11px;font-weight:700;margin-top:1px;}
.pc-foot-val.cy{color:var(--gold);}
.pc-foot-val.cc{color:var(--cyan);}
.pc-foot-val.co{color:var(--orange);}
.pc-foot-val.cr{color:var(--red);}
.pc-foot-val.cg{color:var(--green);}

/* MODAL */
.modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.88);z-index:200;display:none;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(6px);}
.modal-backdrop.open{display:flex;}
.modal{background:#000;border:1px solid var(--gold);max-width:600px;width:100%;animation:modal-in .2s ease;position:relative;}
.modal::before,.modal::after{content:'';position:absolute;width:14px;height:14px;border-color:var(--gold2);border-style:solid;z-index:5;}
.modal::before{top:-1px;right:-1px;border-width:2px 2px 0 0;}
.modal::after{bottom:-1px;left:-1px;border-width:0 0 2px 2px;}
@keyframes modal-in{from{opacity:0;transform:scale(.97)}to{opacity:1;transform:scale(1)}}
.modal-hero{height:200px;background-size:cover;background-position:center;position:relative;}
.modal-hero-fade{position:absolute;inset:0;background:linear-gradient(180deg,transparent 30%,#000 100%);}
.modal-hero-header{position:absolute;top:0;left:0;right:0;padding:10px 14px;background:linear-gradient(180deg,rgba(0,0,0,.85),transparent);display:flex;align-items:center;gap:8px;}
.modal-hero-faction{font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:.2em;text-transform:uppercase;padding:3px 10px;border:1px solid currentColor;}
.modal-close{position:absolute;top:10px;right:10px;background:rgba(0,0,0,.7);border:1px solid #1a2530;color:var(--text);width:28px;height:28px;font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background .15s;z-index:10;}
.modal-close:hover{background:var(--red);color:#fff;border-color:var(--red);}
.modal-body{padding:14px 18px 20px;}
.modal-name{font-size:32px;font-weight:900;color:#fff;text-transform:uppercase;letter-spacing:.1em;line-height:1;margin-bottom:2px;}
.modal-sector{font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--muted);letter-spacing:.18em;text-transform:uppercase;margin-bottom:14px;}
.modal-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:12px;}
.modal-stat{background:#060a0f;border:1px solid #1a2530;padding:10px 12px;}
.modal-stat-lbl{font-family:'Share Tech Mono',monospace;font-size:8px;letter-spacing:.2em;text-transform:uppercase;color:var(--muted);margin-bottom:3px;}
.modal-stat-val{font-size:18px;font-weight:800;color:#fff;}
.modal-biome-desc{font-size:12px;color:var(--muted);line-height:1.65;font-style:italic;border-left:2px solid #1a2530;padding-left:10px;}

/* STATS */
.stats-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
.stats-card{background:#000;border:1px solid #1a2530;padding:14px 16px;}
.stats-card-title{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.2em;color:var(--gold);margin-bottom:10px;display:flex;align-items:center;gap:6px;padding-bottom:6px;border-bottom:1px solid #1a2530;}
.stat-row{display:flex;justify-content:space-between;align-items:center;padding:4px 0;border-bottom:1px solid #0a0e14;font-size:13px;}
.stat-row:last-child{border-bottom:none;}
.stat-val{font-size:16px;font-weight:800;font-family:'Barlow Condensed',sans-serif;}

/* OVERVIEW LAYOUT */
#tab-overview{flex-direction:row;}
.ov-col{height:100%;overflow-y:auto;overflow-x:hidden;}
.ov-col::-webkit-scrollbar{width:2px;}
.ov-col::-webkit-scrollbar-thumb{background:#1a2530;}
.ov-left{width:310px;flex-shrink:0;border-right:1px solid var(--border);}
.ov-center{flex:1;min-width:0;border-right:1px solid var(--border);display:flex;flex-direction:column;overflow:hidden;}
.ov-right{width:300px;flex-shrink:0;}

/* OV section headers */
.ov-sec{font-family:'Share Tech Mono',monospace;font-size:9px;font-weight:700;letter-spacing:.3em;
    text-transform:uppercase;color:var(--gold);padding:7px 12px 6px;
    border-bottom:1px solid var(--border);background:#000;
    display:flex;align-items:center;gap:6px;flex-shrink:0;}
.ov-sec::before{content:'◆';font-size:6px;}

/* DoD header */
.dod-header{text-align:center;padding:10px 12px 8px;border-bottom:1px solid var(--border);background:#000;}
.dod-title{font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:.3em;color:var(--gold);text-transform:uppercase;margin-bottom:5px;}
.dod-meta{font-family:'Share Tech Mono',monospace;font-size:9px;color:var(--muted);display:flex;align-items:center;justify-content:center;gap:8px;margin-bottom:6px;}
.dod-day{background:var(--gold);color:#000;font-weight:900;font-size:9px;padding:2px 8px;letter-spacing:.1em;}
.dod-count{font-size:26px;font-weight:900;color:var(--gold);letter-spacing:.04em;display:flex;align-items:center;justify-content:center;gap:8px;}

/* Faction rows */
.faction-row{display:flex;align-items:center;position:relative;overflow:hidden;margin-bottom:4px;border:1px solid;}
.faction-check{width:22px;height:26px;flex-shrink:0;border-right:1px solid var(--border2);display:flex;align-items:center;justify-content:center;font-size:9px;background:rgba(73,224,125,.1);color:var(--green);}
.faction-icon{width:22px;height:26px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:13px;}
.faction-players{font-size:18px;font-weight:900;padding:0 6px;min-width:70px;}
.faction-pct{font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--muted);flex:1;}
.faction-fill{position:absolute;top:0;left:0;bottom:0;opacity:.1;pointer-events:none;}

/* War stat rows */
.ws-row{display:flex;justify-content:space-between;align-items:center;padding:3px 0;border-bottom:1px solid #0a0e14;font-size:12px;}
.ws-row:last-child{border-bottom:none;}
.ws-key{color:var(--gold);}
.ws-val{font-family:'Share Tech Mono',monospace;font-size:11px;}

/* Theater of War — 3D ring */
.theater-wrap{position:relative;flex-shrink:0;background:#000;border-bottom:1px solid var(--border);}
.theater-title-bar{font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:.3em;color:var(--muted);text-transform:uppercase;text-align:center;padding:7px;border-bottom:1px solid var(--border);background:#000;}
#theater3d{display:block;width:100%;}
.theater-tt{position:absolute;background:rgba(0,0,0,.95);border:1px solid var(--gold);padding:8px 12px;pointer-events:none;display:none;z-index:10;min-width:160px;font-family:'Share Tech Mono',monospace;font-size:9px;}
.theater-tt-name{font-family:'Barlow Condensed',sans-serif;font-size:14px;font-weight:800;color:#fff;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;}
.theater-tt-row{color:var(--muted);margin-top:2px;}
.theater-tt-row span{color:var(--text);}

/* GIM chart */
.gim-header{display:flex;align-items:center;justify-content:center;gap:8px;padding:7px 12px;border-bottom:1px solid var(--border);font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--muted);letter-spacing:.1em;flex-shrink:0;}
.gim-val{color:var(--green);font-weight:700;font-size:13px;}
#gimCanvas{display:block;width:100%;}

/* MO right column */
.mo-new-msg{background:rgba(0,229,255,.06);border:1px solid var(--cyan);padding:6px 10px;
    font-family:'Share Tech Mono',monospace;font-size:9px;color:var(--cyan);letter-spacing:.1em;
    text-transform:uppercase;display:flex;align-items:center;gap:6px;margin:10px 12px 0;}
.mo-hero{position:relative;height:88px;overflow:hidden;background:linear-gradient(135deg,#0a0510,#050010);border-bottom:1px solid var(--border);margin-top:8px;}
.mo-hero-bg{position:absolute;inset:0;background:url('../assets/img/companion/Black_Hole_Landscape.webp') center/cover;opacity:.22;filter:saturate(.4);}
.mo-hero-content{position:relative;z-index:2;padding:10px 12px;display:flex;align-items:flex-start;gap:10px;}
.mo-skull{width:40px;height:40px;flex-shrink:0;background:#0a0a0a;border:1px solid var(--border2);display:flex;align-items:center;justify-content:center;font-size:20px;}
.mo-label{font-family:'Share Tech Mono',monospace;font-size:8px;letter-spacing:.25em;color:var(--muted);text-transform:uppercase;margin-bottom:2px;}
.mo-title{font-size:19px;font-weight:900;color:var(--gold);text-transform:uppercase;letter-spacing:.1em;line-height:1;}
.mo-body{padding:10px 12px;}
.mo-briefing{font-size:12px;color:#8aabbb;line-height:1.6;margin-bottom:10px;}
.mo-overview-lbl{display:flex;align-items:center;gap:7px;font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:.25em;color:var(--muted);text-transform:uppercase;margin-bottom:10px;}
.mo-task{margin-bottom:11px;}
.mo-task-row{display:flex;align-items:center;gap:8px;margin-bottom:4px;}
.mo-check{width:14px;height:14px;flex-shrink:0;border:2px solid #2a3a4a;background:transparent;display:flex;align-items:center;justify-content:center;font-size:9px;color:var(--green);}
.mo-check.done{border-color:var(--green);background:rgba(73,224,125,.1);}
.mo-task-name{font-size:12px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#ccdde8;}
.mo-task-name.done{color:var(--green);text-decoration:line-through;}
.mo-bar{height:6px;background:#0a0e14;border:1px solid #1a2530;position:relative;overflow:hidden;}
.mo-bar-fill{position:absolute;top:0;left:0;bottom:0;background:var(--magenta);transition:width .6s;}
.mo-bar-fill.done{background:var(--green);}
.mo-lib-sub{font-family:'Share Tech Mono',monospace;font-size:9px;color:var(--muted);margin-top:2px;display:flex;justify-content:space-between;}
.mo-expiry{border-top:1px solid var(--border);padding-top:8px;margin-top:4px;font-family:'Share Tech Mono',monospace;font-size:9px;color:var(--muted);letter-spacing:.08em;display:flex;align-items:center;gap:5px;}
.mo-expiry-val{color:var(--gold);font-weight:700;}
.mo-reward-block{border:1px solid var(--border2);margin-top:12px;background:#060a0f;overflow:hidden;}
.mo-reward-lbl{font-family:'Share Tech Mono',monospace;font-size:8px;letter-spacing:.3em;text-transform:uppercase;color:var(--muted);padding:5px 10px;border-bottom:1px solid var(--border);}
.mo-reward-body{display:flex;align-items:center;gap:10px;padding:8px 10px;}
.mo-reward-icon{width:44px;height:44px;background:#0a0a0a;border:1px solid var(--border2);display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;}
.mo-reward-name{font-size:15px;font-weight:900;color:#fff;text-transform:uppercase;letter-spacing:.06em;}
.mo-reward-amt{font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--gold);margin-top:2px;}

/* MAPA tab */
#tab-mapa{flex-direction:column;}
.mapa-full{position:relative;flex:1;background:#000;overflow:hidden;}
#mapa-canvas{display:block;width:100%;height:100%;cursor:grab;}
#mapa-canvas:active{cursor:grabbing;}
.mapa-hint{position:absolute;bottom:12px;left:50%;transform:translateX(-50%);font-family:'Share Tech Mono',monospace;font-size:10px;color:#1a2530;letter-spacing:.1em;pointer-events:none;}
.mapa-tooltip{position:absolute;background:#000;border:1px solid var(--gold);padding:10px 14px;pointer-events:none;display:none;z-index:10;min-width:200px;}
.mapa-tooltip::before{content:'';position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,var(--gold),transparent);}
.mapa-tt-name{font-size:16px;font-weight:800;color:#fff;text-transform:uppercase;letter-spacing:.06em;margin-bottom:5px;}
.mapa-tt-row{font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--muted);margin-top:2px;letter-spacing:.05em;}
.mapa-tt-row span{color:var(--text);}

/* DISPATCHES */
.dispatch-item{background:#000;border:1px solid var(--border);border-left:2px solid var(--cyan);padding:12px 14px;margin-bottom:8px;position:relative;}
.dispatch-item::before{content:'';position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,var(--cyan),transparent);opacity:.4;}
.dispatch-date{font-family:'Share Tech Mono',monospace;font-size:8px;color:var(--muted);letter-spacing:.15em;text-transform:uppercase;margin-bottom:6px;}
.dispatch-title{font-size:14px;font-weight:900;color:#fff;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;line-height:1.2;}
.dispatch-body{font-size:12px;color:#7a9aaa;line-height:1.7;white-space:pre-wrap;}

/* UTILS */
.cy{color:var(--gold)}.co{color:var(--orange)}.cr{color:var(--red)}.cp{color:var(--purple)}.cg{color:var(--green)}.cc{color:var(--cyan)}
</style>
</head>
<body>

<!-- HEADER -->
<div class="hd-header">
    <div class="hd-title-row">
        <div class="hd-emblem"><span class="hd-emblem-icon">◈</span></div>
        <div class="hd-brand">
            <div class="hd-brand-main">Helldivers 2</div>
            <div class="hd-brand-sub">Companion Táctico · MelonOS</div>
        </div>
        <div class="hd-live"><span class="live-dot"></span>EN VIVO</div>
        <button class="btn-refresh" onclick="location.reload()">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/>
                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
            </svg>
            Actualizar
        </button>
    </div>
    <div class="hd-stats-bar">
        <div class="hd-stat"><span class="hd-stat-val cy"><?= number_format($hdActivos) ?></span><span class="hd-stat-lbl">Helldivers activos</span></div>
        <div class="hd-stat"><span class="hd-stat-val cy"><?= $frentesActivos ?></span><span class="hd-stat-lbl">Frentes activos</span></div>
        <div class="hd-stat"><span class="hd-stat-val co"><?= number_format($vsTerminids) ?></span><span class="hd-stat-lbl">vs Terminids</span></div>
        <div class="hd-stat"><span class="hd-stat-val cr"><?= number_format($vsAutomatons) ?></span><span class="hd-stat-lbl">vs Automatons</span></div>
        <div class="hd-stat"><span class="hd-stat-val cp"><?= number_format($vsIlluminate) ?></span><span class="hd-stat-lbl">vs Illuminate</span></div>
    </div>
</div>

<!-- TABS -->
<div class="hd-tabs">
    <div class="hd-tab active" onclick="showTab('frentes',this)">⚔ Frentes</div>
    <div class="hd-tab" onclick="showTab('overview',this)">🌐 Overview</div>
    <div class="hd-tab" onclick="showTab('estadisticas',this)">📊 Estadísticas</div>
    <div class="hd-tab" onclick="showTab('despachos',this)">📡 Despachos</div>
    <div class="hd-tab" onclick="showTab('mapa',this)">🌌 Mapa</div>
</div>

<!-- SCROLL AREA -->
<div class="hd-scroll" id="scroll-area">

<!-- ══ FRENTES ══ -->
<div id="tab-frentes" class="hd-content active">
    <div class="sec-title">Frentes de campaña activos</div>
    <div class="planet-grid">
    <?php foreach ($campanas as $c):
        $p=$c['planet'];
        $bioma=$p['biome']['name']??'';
        $img=getBiomaImg($bioma,$biomaImgs);
        $health=$p['health']??0;$maxH=$p['maxHealth']?:1;
        $pct=max(0,min(100,round((1-$health/$maxH)*100)));
        $jugadores=$p['statistics']['playerCount']??0;
        $peligros=array_column($p['hazards']??[],'name');
        $regen=round(($p['regenPerSecond']??0)*3600/$maxH*100,3);
        [$fColor,$fBg,$fLabel,$fIcon,$fClass]=factionStyle($p['currentOwner']??'');
        $md=htmlspecialchars(json_encode(['name'=>strtoupper($p['name']),'sector'=>$p['sector'],'owner'=>$p['currentOwner']??'','faction'=>$fLabel,'fcolor'=>$fColor,'fclass'=>$fClass,'lib'=>$pct,'players'=>$jugadores,'biome'=>$bioma,'biomeDesc'=>$p['biome']['description']??'','img'=>$img,'hazards'=>implode(', ',$peligros),'regen'=>$regen]),ENT_QUOTES);
        $hazardIcons=['Cold'=>'❄','Extreme Cold'=>'🧊','Fire Tornadoes'=>'🌪','Blizzards'=>'🌨','Meteor Showers'=>'☄','Ion Storms'=>'⚡','Tremors'=>'🌍','Rainstorms'=>'🌧','Toxic Rain'=>'☠','Volcanic Activity'=>'🌋','Supercolony'=>'🪲','default'=>'⚠'];
    ?>
        <div class="planet-card <?= $fClass ?>" onclick='openModal(<?= $md ?>)'>
            <div class="pc-header">
                <div class="pc-type-icon"><?php if($fClass==='f-terminid')echo'🦟';elseif($fClass==='f-automaton')echo'🤖';elseif($fClass==='f-illuminate')echo'👁';else echo'🌍';?></div>
                <div class="pc-names">
                    <div class="pc-planet-name"><?= htmlspecialchars($p['name']) ?></div>
                    <div class="pc-sector"><?= htmlspecialchars($p['sector']) ?></div>
                </div>
            </div>
            <div class="pc-img">
                <div class="pc-img-bg" style="background-image:url('<?= $img ?>')"></div>
                <div class="pc-hazard-row">
                    <?php foreach(array_slice($peligros,0,4) as $hz): $ico=$hazardIcons[$hz]??$hazardIcons['default']; ?>
                    <div class="pc-hazard-icon" title="<?= htmlspecialchars($hz) ?>"><?= $ico ?></div>
                    <?php endforeach ?>
                    <?php if($jugadores>0): ?><div class="pc-players-badge"><div class="pc-players-dot"></div><?= number_format($jugadores) ?></div><?php endif ?>
                </div>
            </div>
            <div class="pc-lib-section">
                <div class="pc-lib-label"><span>Liberation</span><span class="pc-lib-label-pct" style="color:<?= $fColor ?>"><?= number_format($pct,4) ?>%</span></div>
                <div class="pc-lib-bar-wrap">
                    <div class="pc-lib-bar-fill" style="width:<?= $pct ?>%"><div class="pc-lib-bar-tip" style="left:<?= $pct ?>%;transform:translateX(-100%)"></div></div>
                    <?php if($regen>0): ?><div class="pc-lib-bar-enemy" style="width:<?= min(30,$regen*10) ?>%"></div><?php endif ?>
                </div>
                <div class="pc-status"><span class="pc-status-icon"><?= $jugadores>1000?'⚔':'🔃' ?></span><?= $jugadores>500?'LIBERATION IN PROGRESS':'HOLDING FOR REINFORCEMENT' ?></div>
            </div>
            <div class="pc-footer">
                <div class="pc-foot-cell"><div class="pc-foot-label">REINF%</div><div class="pc-foot-val cc"><?= $pct ?>%</div></div>
                <div class="pc-foot-cell"><div class="pc-foot-label">PLAYERS</div><div class="pc-foot-val cg"><?= number_format($jugadores) ?></div></div>
                <div class="pc-foot-cell"><div class="pc-foot-label">LIB/H</div><div class="pc-foot-val cy">+<?= $regen ?>%</div></div>
                <div class="pc-foot-cell"><div class="pc-foot-label">REGEN/H</div><div class="pc-foot-val co"><?= $regen ?>%</div></div>
            </div>
        </div>
    <?php endforeach ?>
    </div>
</div>

<!-- ══ OVERVIEW ══ -->
<div id="tab-overview" class="hd-content">

    <!-- LEFT -->
    <div class="ov-col ov-left">
        <div class="dod-header">
            <div class="dod-title">Distribution of Democracy</div>
            <div class="dod-meta">
                <span>SEST <?= date('H:i:s') ?></span>
                <span>📅 <?= date('d/m/Y') ?></span>
                <span class="dod-day">DAY <?= floor((time()-strtotime('2024-02-08'))/86400) ?></span>
            </div>
            <div class="dod-count"><span>🪙</span><span><?= number_format($hdActivos) ?></span></div>
        </div>

        <!-- Pie -->
        <div style="padding:10px 12px;border-bottom:1px solid var(--border);">
            <div style="display:flex;align-items:center;gap:10px;">
                <canvas id="ov-pie" width="130" height="130" style="flex-shrink:0;"></canvas>
                <div id="ov-pie-legend" style="flex:1;min-width:0;"></div>
            </div>
        </div>

        <!-- Faction bars -->
        <div style="padding:8px 12px;border-bottom:1px solid var(--border);">
            <?php foreach($factionBars as $f):
                $pctF=$hdActivos>0?round($f['players']/$hdActivos*100,3):0;
                $barW=$hdActivos>0?round($f['players']/$hdActivos*100,1):0;
            ?>
            <div class="faction-row" style="border-color:<?= $f['color'] ?>55;">
                <div class="faction-check">✓</div>
                <div class="faction-icon"><?= $f['icon'] ?></div>
                <div class="faction-players" style="color:<?= $f['color'] ?>"><?= number_format($f['players']) ?></div>
                <div class="faction-pct">○<?= $pctF ?>%</div>
                <div class="faction-fill" style="width:<?= $barW ?>%;background:<?= $f['color'] ?>;"></div>
            </div>
            <?php endforeach ?>
        </div>

        <!-- War stats -->
        <div class="ov-sec">War Statistics</div>
        <div style="padding:5px 12px 12px;">
            <?php
            $wsRows=[
                ['Missions Completed',number_format($statsSum['missionsWon']).' ('.$missionRate.'%)'],
                ['Missions Failed',   number_format($statsSum['missionsLost']).' ('.round(100-$missionRate,1).'%)'],
                ['Mission Dive Time', $missionTimeStr],
                ['Shots Fired',       number_format($statsSum['bulletsFired'])],
                ['Projectile Hits',   number_format($statsSum['bulletsHit'])],
                ['Hits to Shot Ratio',$htsRatio.' : 1'],
                ['Kill to Death Ratio',$kd.' : 1'],
                ['Dead Terminids',    number_format($statsSum['terminidKills'])],
                ['Dead Automatons',   number_format($statsSum['automatonKills'])],
                ['Dead Illuminate',   number_format($statsSum['illuminateKills'])],
                ['Helldivers KIA',    number_format($statsSum['deaths'])],
                ['Accidentals',       number_format($statsSum['friendlies']).' ('.($statsSum['deaths']>0?round($statsSum['friendlies']/$statsSum['deaths']*100,1):0).'%)'],
            ];
            foreach($wsRows as $r): ?>
            <div class="ws-row"><span class="ws-key"><?= $r[0] ?></span><span class="ws-val"><?= $r[1] ?></span></div>
            <?php endforeach ?>
        </div>
    </div>

    <!-- CENTER -->
    <div class="ov-col ov-center">
        <!-- Theater of War 3D -->
        <div class="theater-wrap">
            <div class="theater-title-bar">Theater of War</div>
            <canvas id="theater3d" height="280"></canvas>
            <div class="theater-tt" id="theater-tt">
                <div class="theater-tt-name" id="ttt-name"></div>
                <div class="theater-tt-row">Sector: <span id="ttt-sector"></span></div>
                <div class="theater-tt-row">Faction: <span id="ttt-faction"></span></div>
                <div class="theater-tt-row">Liberation: <span id="ttt-pct"></span>%</div>
                <div class="theater-tt-row">Players: <span id="ttt-players"></span></div>
            </div>
        </div>

        <!-- GIM Chart -->
        <div class="gim-header">
            Galactic Impact Mod ×
            <span class="gim-val" id="gim-val">—</span>
        </div>
        <div style="padding:6px 10px 4px;flex-shrink:0;">
            <canvas id="gimCanvas" height="150"></canvas>
        </div>

        <!-- Transmissions preview -->
        <div class="ov-sec" style="margin-top:auto;">Transmissions</div>
        <div style="flex:1;overflow-y:auto;">
            <?php foreach(array_slice($despachos,0,4) as $d):
                $raw=$d['message']??'';
                $parts=preg_split('/\n\n|\r\n\r\n/',$raw,2);
                $titulo=trim(strip_tags($parts[0]));
                $cuerpo=isset($parts[1])?trim(strip_tags($parts[1])):'';
                $fecha=isset($d['published'])?date('d M Y · H:i',strtotime($d['published'])).' UTC':'—';
            ?>
            <div style="border-left:2px solid var(--cyan);padding:8px 12px;border-bottom:1px solid var(--border);position:relative;">
                <div style="position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,var(--cyan),transparent);opacity:.4;"></div>
                <div style="font-family:'Share Tech Mono',monospace;font-size:8px;color:var(--muted);letter-spacing:.12em;text-transform:uppercase;margin-bottom:4px;">📡 <?= $fecha ?></div>
                <?php if($titulo): ?><div style="font-size:12px;font-weight:900;color:#fff;text-transform:uppercase;letter-spacing:.05em;"><?= htmlspecialchars($titulo) ?></div><?php endif ?>
            </div>
            <?php endforeach ?>
        </div>
    </div>

    <!-- RIGHT — Major Order -->
    <div class="ov-col ov-right">
        <?php if(!empty($despachos)): ?>
        <div class="mo-new-msg">📡 A NEW MESSAGE from Super Earth</div>
        <?php endif ?>

        <?php if(!empty($ordenes)): foreach($ordenes as $orden):
            $tasks=$orden['tasks']??[];
            $progress=$orden['progress']??[];
            $expiry=formatExpiry($orden['expiration']??'');
            $title=$orden['title']??'MAJOR ORDER';
            $briefing=$orden['briefing']??$orden['description']??'';
            $rewardData=$orden['reward']??($orden['rewards'][0]??null);
            $rewardType=$rewardData['type']??1;
            $rewardAmt=$rewardData['amount']??'—';
            $rewardTypes=[1=>['🏅','MEDALS'],4=>['💠','SUPER CREDITS'],6=>['🎽','CAPE']];
            [$rIcon,$rLabel]=$rewardTypes[$rewardType]??['🎖','REWARD'];
        ?>
        <div class="mo-hero">
            <div class="mo-hero-bg"></div>
            <div class="mo-hero-content">
                <div class="mo-skull">💀</div>
                <div><div class="mo-label">Major Order</div><div class="mo-title"><?= htmlspecialchars(strtoupper($title)) ?></div></div>
            </div>
        </div>
        <div class="mo-body">
            <?php if($briefing): ?><div class="mo-briefing"><?= nl2br(htmlspecialchars($briefing)) ?></div><?php endif ?>
            <div class="mo-overview-lbl">🎯 Order Overview</div>
            <?php foreach($tasks as $i=>$task):
                $rawProg=$progress[$i]??0;
                $isDone=($rawProg>0);
                $values=$task['values']??[];
                $vTypes=$task['valueTypes']??[];
                $planetId=null;
                foreach($vTypes as $vi=>$vt){if($vt==12){$planetId=$values[$vi]??null;break;}}
                $libPct=null;
                if($planetId!==null&&isset($planetaMap[$planetId])){
                    $pp=$planetaMap[$planetId];
                    $libPct=round((1-($pp['health']??0)/($pp['maxHealth']?:1))*100,1);
                }
                $label=taskLabel($task,$planetaMap);
                $pctBar=$isDone?100:($libPct??0);
            ?>
            <div class="mo-task">
                <div class="mo-task-row">
                    <div class="mo-check <?= $isDone?'done':'' ?>"><?= $isDone?'✓':'' ?></div>
                    <span class="mo-task-name <?= $isDone?'done':'' ?>">
                        <?php if($planetId!==null&&isset($planetaMap[$planetId])){
                            $pname=htmlspecialchars($planetaMap[$planetId]['name']);
                            $prefix=htmlspecialchars(str_replace($planetaMap[$planetId]['name'],'',$label));
                            echo $prefix.'<span style="color:var(--purple)">'.$pname.'</span>';
                        }else{echo htmlspecialchars($label);} ?>
                    </span>
                </div>
                <div class="mo-bar"><div class="mo-bar-fill <?= $isDone?'done':'' ?>" style="width:<?= $pctBar ?>%"></div></div>
                <?php if(!$isDone&&$libPct!==null): ?>
                <div class="mo-lib-sub">
                    <span style="color:var(--cyan)"><?= $libPct ?>%</span>
                </div>
                <?php endif ?>
            </div>
            <?php endforeach ?>
            <div class="mo-expiry">⏱ Complete in: <span class="mo-expiry-val"><?= $expiry ?></span></div>
            <div class="mo-reward-block">
                <div class="mo-reward-lbl">Reward</div>
                <div class="mo-reward-body">
                    <div class="mo-reward-icon"><?= $rIcon ?></div>
                    <div><div class="mo-reward-name"><?= $rLabel ?></div><div class="mo-reward-amt">× <?= number_format((int)$rewardAmt) ?></div></div>
                </div>
            </div>
        </div>
        <?php endforeach; else: ?>
        <div style="padding:40px 12px;text-align:center;font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--muted);">No active major orders.</div>
        <?php endif ?>
    </div>

</div><!-- /overview -->

<!-- ══ ESTADÍSTICAS ══ -->
<div id="tab-estadisticas" class="hd-content">
    <div class="sec-title">Estadísticas de Guerra</div>
    <div class="stats-grid">
        <div class="stats-card">
            <div class="stats-card-title">💀 Bajas enemigas</div>
            <div class="stat-row"><span>Terminids</span><span class="stat-val co"><?= number_format($statsSum['terminidKills']) ?></span></div>
            <div class="stat-row"><span>Automatons</span><span class="stat-val cr"><?= number_format($statsSum['automatonKills']) ?></span></div>
            <div class="stat-row"><span>Illuminate</span><span class="stat-val cp"><?= number_format($statsSum['illuminateKills']) ?></span></div>
            <div class="stat-row"><span>Total</span><span class="stat-val cy"><?= number_format($totalKills) ?></span></div>
        </div>
        <div class="stats-card">
            <div class="stats-card-title">🪖 Helldivers</div>
            <div class="stat-row"><span>KIA</span><span class="stat-val cr"><?= number_format($statsSum['deaths']) ?></span></div>
            <div class="stat-row"><span>Fuego amigo</span><span class="stat-val"><?= number_format($statsSum['friendlies']) ?></span></div>
            <div class="stat-row"><span>Misiones completadas</span><span class="stat-val cg"><?= number_format($statsSum['missionsWon']) ?></span></div>
            <div class="stat-row"><span>Misiones fallidas</span><span class="stat-val cr"><?= number_format($statsSum['missionsLost']) ?></span></div>
        </div>
        <div class="stats-card">
            <div class="stats-card-title">🔫 Armamento</div>
            <div class="stat-row"><span>Disparos</span><span class="stat-val"><?= number_format($statsSum['bulletsFired']) ?></span></div>
            <div class="stat-row"><span>Impactos</span><span class="stat-val"><?= number_format($statsSum['bulletsHit']) ?></span></div>
            <div class="stat-row"><span>Precisión</span><span class="stat-val cy"><?= $accuracy ?>%</span></div>
            <div class="stat-row"><span>Ratio K/D</span><span class="stat-val cy"><?= $kd ?></span></div>
        </div>
        <div class="stats-card">
            <div class="stats-card-title">⏱ Operaciones</div>
            <div class="stat-row"><span>Tiempo en misión</span><span class="stat-val"><?= $missionTimeStr ?></span></div>
            <div class="stat-row"><span>Helldivers activos</span><span class="stat-val cg"><?= number_format($hdActivos) ?></span></div>
            <div class="stat-row"><span>Frentes activos</span><span class="stat-val cy"><?= $frentesActivos ?></span></div>
        </div>
    </div>
</div>

<!-- ══ DESPACHOS ══ -->
<div id="tab-despachos" class="hd-content">
    <div class="sec-title">Transmisiones de Alto Mando</div>
    <?php if(empty($despachos)): ?>
        <p style="color:var(--muted);font-family:'Share Tech Mono',monospace;font-size:12px;text-align:center;padding:40px 0;">Sin transmisiones.</p>
    <?php else: foreach(array_slice($despachos,0,15) as $d):
        $raw=$d['message']??'';
        $parts=preg_split('/\n\n|\r\n\r\n/',$raw,2);
        $titulo=trim(strip_tags($parts[0]));
        $cuerpo=isset($parts[1])?trim(strip_tags($parts[1])):'';
        $fecha=isset($d['published'])?date('d M Y · H:i',strtotime($d['published'])).' UTC':'—';
    ?>
    <div class="dispatch-item">
        <div class="dispatch-date">📡 TRANSMISSION · <?= $fecha ?></div>
        <?php if($titulo): ?><div class="dispatch-title"><?= htmlspecialchars($titulo) ?></div><?php endif ?>
        <?php if($cuerpo): ?><div class="dispatch-body"><?= htmlspecialchars($cuerpo) ?></div><?php endif ?>
    </div>
    <?php endforeach; endif ?>
</div>

<!-- ══ MAPA ══ -->
<div id="tab-mapa" class="hd-content">
    <div style="font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:.3em;color:var(--muted);text-transform:uppercase;text-align:center;padding:7px;border-bottom:1px solid var(--border);background:#000;flex-shrink:0;">
        Theater of War
    </div>
    <div class="mapa-full">
        <canvas id="mapa-canvas"></canvas>
        <div class="mapa-hint">Rueda: zoom · Arrastrar: mover · Hover: detalles</div>
        <div class="mapa-tooltip" id="mapa-tooltip">
            <div class="mapa-tt-name" id="tt-name"></div>
            <div class="mapa-tt-row">Sector: <span id="tt-sector"></span></div>
            <div class="mapa-tt-row">Facción: <span id="tt-faction"></span></div>
            <div class="mapa-tt-row">Liberación: <span id="tt-pct"></span>%</div>
            <div class="mapa-tt-row">Jugadores: <span id="tt-players"></span></div>
            <div class="mapa-tt-row" id="tt-haz-row" style="display:none">Peligros: <span id="tt-haz"></span></div>
        </div>
    </div>
</div>

</div><!-- /scroll-area -->

<!-- MODAL -->
<div class="modal-backdrop" id="planetModal" onclick="if(event.target===this)closeModal()">
    <div class="modal" id="modalEl">
        <div class="modal-hero" id="modalHero">
            <div class="modal-hero-fade"></div>
            <div class="modal-hero-header"><div class="modal-hero-faction" id="modalFactionBadge"></div></div>
            <button class="modal-close" onclick="closeModal()">✕</button>
        </div>
        <div class="modal-body">
            <div class="modal-name" id="modalName"></div>
            <div class="modal-sector" id="modalSector"></div>
            <div class="modal-grid">
                <div class="modal-stat"><div class="modal-stat-lbl">Facción</div><div class="modal-stat-val" id="modalFaction" style="font-size:14px"></div></div>
                <div class="modal-stat"><div class="modal-stat-lbl">Liberación</div><div class="modal-stat-val" id="modalLib"></div></div>
                <div class="modal-stat"><div class="modal-stat-lbl">Helldivers</div><div class="modal-stat-val" id="modalPlayers"></div></div>
                <div class="modal-stat"><div class="modal-stat-lbl">Bioma</div><div class="modal-stat-val" id="modalBiome" style="font-size:13px"></div></div>
            </div>
            <div class="modal-biome-desc" id="modalBiomeDesc"></div>
        </div>
    </div>
</div>

<script>
// ══════════════════════════════════════════════════
// DATA FROM PHP
// ══════════════════════════════════════════════════
const PLANETAS  = <?= $mapaJson ?>;
const WAYPOINTS = <?= $wpJson ?>;
const PIE_DATA  = <?= json_encode($pieData, JSON_UNESCAPED_UNICODE) ?>;
const HD_TOTAL  = <?= $hdActivos ?: 1 ?>;
const IMPACT_MOD= <?= $impactMod ?>;

// ══════════════════════════════════════════════════
// TABS
// ══════════════════════════════════════════════════
function showTab(name, el) {
    document.querySelectorAll('.hd-content').forEach(t => { t.classList.remove('active'); });
    document.querySelectorAll('.hd-tab').forEach(t => t.classList.remove('active'));
    const target = document.getElementById('tab-' + name);
    if (target) target.classList.add('active');
    if (el) el.classList.add('active');
    const scroll = document.getElementById('scroll-area');
    if (name === 'mapa') {
        scroll.style.overflow = 'hidden';
        initMapa();
    } else if (name === 'overview') {
        scroll.style.overflow = 'hidden';
        initOverview();
    } else {
        scroll.style.overflow = 'auto';
    }
}

// ══════════════════════════════════════════════════
// MODAL
// ══════════════════════════════════════════════════
function openModal(d) {
    document.getElementById('modalHero').style.backgroundImage=`url('${d.img}')`;
    document.getElementById('modalName').textContent=d.name;
    document.getElementById('modalSector').textContent='SECTOR: '+d.sector;
    const fb=document.getElementById('modalFactionBadge');
    fb.textContent=d.faction;fb.style.color=d.fcolor;fb.style.borderColor=d.fcolor;
    document.getElementById('modalFaction').textContent=d.faction;
    document.getElementById('modalFaction').style.color=d.fcolor;
    document.getElementById('modalLib').textContent=d.lib+'%';
    document.getElementById('modalLib').style.color=d.fcolor;
    document.getElementById('modalPlayers').textContent=d.players>0?d.players.toLocaleString():'—';
    document.getElementById('modalBiome').textContent=d.biome||'—';
    document.getElementById('modalBiomeDesc').textContent=d.biomeDesc||'';
    document.getElementById('modalEl').style.borderColor=d.fcolor;
    document.getElementById('planetModal').classList.add('open');
}
function closeModal(){document.getElementById('planetModal').classList.remove('open');}

// ══════════════════════════════════════════════════
// MAPA FULL (tab Mapa)
// ══════════════════════════════════════════════════
let mapaReady = false;
function initMapa() {
    if (mapaReady) return; mapaReady = true;
    const wrap   = document.querySelector('.mapa-full');
    const canvas = document.getElementById('mapa-canvas');
    const tt     = document.getElementById('mapa-tooltip');
    const ctx    = canvas.getContext('2d');
    let W,H,zoom=1,drag=false,dragStart={x:0,y:0},viewOff={x:0,y:0},viewStart={x:0,y:0};

    function resize(){ W=canvas.width=wrap.clientWidth; H=canvas.height=wrap.clientHeight; draw(); }
    function sc(){ return Math.min(W,H)*0.42; }
    function toC(x,y){ return{ cx:W/2+viewOff.x+x*sc()*zoom, cy:H/2+viewOff.y-y*sc()*zoom }; }

    function draw(){
        ctx.clearRect(0,0,W,H); ctx.fillStyle='#000'; ctx.fillRect(0,0,W,H);
        let sr=42; const rnd=()=>{sr=(sr*1664525+1013904223)&0xffffffff;return(sr>>>0)/0xffffffff;};
        for(let i=0;i<400;i++){const b=0.05+rnd()*0.3;ctx.fillStyle=`rgba(255,255,255,${b})`;ctx.beginPath();ctx.arc(rnd()*W,rnd()*H,rnd()*1.3,0,Math.PI*2);ctx.fill();}
        ctx.strokeStyle='rgba(232,200,74,0.05)'; ctx.lineWidth=1;
        [0.25,0.5,0.75,1].forEach(r=>{ctx.beginPath();ctx.arc(W/2+viewOff.x,H/2+viewOff.y,r*sc()*zoom,0,Math.PI*2);ctx.stroke();});
        WAYPOINTS.forEach(wp=>{const a=toC(wp.x1,wp.y1),b=toC(wp.x2,wp.y2);ctx.strokeStyle='rgba(232,200,74,0.12)';ctx.lineWidth=0.6;ctx.beginPath();ctx.moveTo(a.cx,a.cy);ctx.lineTo(b.cx,b.cy);ctx.stroke();});
        PLANETAS.forEach(p=>{
            const{cx,cy}=toC(p.x,p.y); const r=p.name==='Super Earth'?8:5;
            const g=ctx.createRadialGradient(cx,cy,0,cx,cy,r*3.5);g.addColorStop(0,p.color+'55');g.addColorStop(1,'transparent');
            ctx.fillStyle=g;ctx.beginPath();ctx.arc(cx,cy,r*3.5,0,Math.PI*2);ctx.fill();
            ctx.fillStyle=p.color;ctx.beginPath();ctx.arc(cx,cy,r,0,Math.PI*2);ctx.fill();
            ctx.font=`${p.name==='Super Earth'?9:8}px "Share Tech Mono",monospace`;ctx.textAlign='center';
            const tw=ctx.measureText(p.name).width;
            ctx.fillStyle='rgba(0,0,0,.6)';ctx.fillRect(cx-tw/2-2,cy-r-12,tw+4,10);
            ctx.fillStyle='rgba(255,255,255,0.7)';ctx.fillText(p.name,cx,cy-r-4);
        });
    }
    canvas.addEventListener('mousemove',e=>{
        const rect=canvas.getBoundingClientRect();const mx=e.clientX-rect.left,my=e.clientY-rect.top;
        let found=null;PLANETAS.forEach(p=>{const{cx,cy}=toC(p.x,p.y);if(Math.hypot(mx-cx,my-cy)<14)found=p;});
        if(found){
            document.getElementById('tt-name').textContent=found.name;
            document.getElementById('tt-sector').textContent=found.sector;
            document.getElementById('tt-faction').textContent=found.faction;
            document.getElementById('tt-faction').style.color=found.color;
            document.getElementById('tt-pct').textContent=found.pct;
            document.getElementById('tt-players').textContent=found.players.toLocaleString();
            const hr=document.getElementById('tt-haz-row');
            if(found.hazards){document.getElementById('tt-haz').textContent=found.hazards;hr.style.display='block';}else hr.style.display='none';
            const wr=wrap.getBoundingClientRect();let tx=e.clientX-wr.left+16,ty=e.clientY-wr.top+16;
            if(tx+210>W)tx-=225;tt.style.borderColor=found.color;tt.style.left=tx+'px';tt.style.top=ty+'px';tt.style.display='block';
        }else tt.style.display='none';
        if(drag){viewOff.x=viewStart.x+(e.clientX-dragStart.x);viewOff.y=viewStart.y+(e.clientY-dragStart.y);draw();}
    });
    canvas.addEventListener('mousedown',e=>{drag=true;dragStart={x:e.clientX,y:e.clientY};viewStart={x:viewOff.x,y:viewOff.y};});
    canvas.addEventListener('mouseup',()=>drag=false);
    canvas.addEventListener('mouseleave',()=>{drag=false;tt.style.display='none';});
    canvas.addEventListener('wheel',e=>{e.preventDefault();zoom*=e.deltaY<0?1.1:0.9;zoom=Math.max(0.3,Math.min(6,zoom));draw();},{passive:false});
    window.addEventListener('resize',resize);
    resize();
}

// ══════════════════════════════════════════════════
// OVERVIEW INIT
// ══════════════════════════════════════════════════
let ovReady = false;
function initOverview() {
    if (ovReady) return; ovReady = true;
    initOvPie();
    initTheater3D();
    initGim();
}

// ── PIE CHART ──
function initOvPie() {
    const canvas = document.getElementById('ov-pie');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const W=130,H=130,cx=65,cy=65,R=58,Ri=28;
    function col(o){const d=(o||'').toLowerCase();if(d.includes('terminid'))return'#ff9500';if(d.includes('automaton'))return'#ff3322';if(d.includes('illuminate'))return'#cc44ff';return'#3399ff';}
    const segs=[...PIE_DATA];
    const known=segs.reduce((s,x)=>s+x.players,0);
    if(HD_TOTAL-known>0) segs.push({name:'Others',players:HD_TOTAL-known,owner:''});
    let angle=-Math.PI/2;
    segs.forEach(s=>{
        const slice=(s.players/HD_TOTAL)*Math.PI*2;
        ctx.beginPath();ctx.moveTo(cx,cy);ctx.arc(cx,cy,R,angle,angle+slice);ctx.closePath();
        ctx.fillStyle=col(s.owner);ctx.fill();ctx.strokeStyle='#000';ctx.lineWidth=1.5;ctx.stroke();
        angle+=slice;
    });
    ctx.beginPath();ctx.arc(cx,cy,Ri,0,Math.PI*2);ctx.fillStyle='#000';ctx.fill();
    const legend=document.getElementById('ov-pie-legend');
    if(legend){
        legend.innerHTML='';
        segs.filter(s=>s.players>0).forEach(s=>{
            const pct=HD_TOTAL>0?(s.players/HD_TOTAL*100).toFixed(1):0;
            const el=document.createElement('div');
            el.style.cssText='display:flex;align-items:center;gap:5px;padding:2px 0;border-bottom:1px solid #0a0e14;';
            el.innerHTML=`<div style="width:7px;height:7px;flex-shrink:0;background:${col(s.owner)};"></div>`
                +`<div style="flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-weight:700;color:${col(s.owner)};font-size:10px;">${s.name}</div>`
                +`<div style="font-family:'Share Tech Mono',monospace;font-size:8px;color:#4a6070;white-space:nowrap;">${(s.players/1000).toFixed(1)}k (${pct}%)</div>`;
            legend.appendChild(el);
        });
    }
}

// ── THEATER 3D RING ──
function initTheater3D() {
    const canvas = document.getElementById('theater3d');
    if (!canvas) return;
    const wrap = canvas.parentElement;
    const ctx  = canvas.getContext('2d');
    const tt   = document.getElementById('theater-tt');

    // Count planets per faction for ring segments
    const factionCounts = {};
    const factionColors = {'Terminids':'#ff9500','Automatons':'#ff3322','Illuminate':'#cc44ff','Humans':'#00aaff'};
    PLANETAS.forEach(p => {
        if (!factionCounts[p.faction]) factionCounts[p.faction] = {count:0,players:0,color:factionColors[p.faction]||'#888'};
        factionCounts[p.faction].count++;
        factionCounts[p.faction].players += p.players;
    });
    const totalPlanets = PLANETAS.length || 1;

    let animFrame = 0;
    let hoverSeg = null;

    function resize() {
        canvas.width  = wrap.clientWidth;
        canvas.height = 280;
        render();
    }

    function render() {
        animFrame++;
        const W = canvas.width, H = canvas.height;
        ctx.clearRect(0,0,W,H);

        // Dark bg with subtle gradient
        const bgGrd = ctx.createRadialGradient(W/2,H/2,0,W/2,H/2,Math.max(W,H)*0.7);
        bgGrd.addColorStop(0,'#0a0d14');bgGrd.addColorStop(1,'#000');
        ctx.fillStyle=bgGrd; ctx.fillRect(0,0,W,H);

        // Stars
        let sr=99;const rnd=()=>{sr=(sr*1664525+1013904223)&0xffffffff;return(sr>>>0)/0xffffffff;};
        for(let i=0;i<200;i++){const b=0.04+rnd()*0.2;ctx.fillStyle=`rgba(255,255,255,${b})`;ctx.beginPath();ctx.arc(rnd()*W,rnd()*H,rnd()*0.9,0,Math.PI*2);ctx.fill();}

        const cx = W/2, cy = H*0.52;
        const outerRx = W*0.44, outerRy = outerRx*0.32; // ellipse radii for 3D effect
        const numRings = 5;
        const fKeys    = Object.keys(factionCounts);
        const totalSegs= fKeys.length;

        // Draw rings from outside in (bigger radius = outer ring)
        for (let ring = numRings; ring >= 1; ring--) {
            const rx = outerRx * (ring/numRings);
            const ry = outerRy * (ring/numRings);
            const innerRx = rx * 0.75;
            const innerRy = ry * 0.75;

            let startAngle = -Math.PI * 0.1; // slight rotation for 3D feel

            fKeys.forEach((fKey, fi) => {
                const fc   = factionCounts[fKey];
                const slice = (fc.count / totalPlanets) * Math.PI * 2;
                const endAngle = startAngle + slice * 0.92; // gap between segments

                // Only draw visible (front-facing) portion
                const midA = startAngle + slice*0.46;
                const isFront = Math.cos(midA) > -0.15; // show most of ring

                if (isFront) {
                    // Base color with ring depth variation
                    const depth = ring / numRings;
                    const alpha = 0.4 + depth * 0.55;
                    const hover = hoverSeg === fKey;

                    // Draw segment as filled arc path (isometric)
                    ctx.save();
                    ctx.beginPath();
                    // outer arc
                    for (let a = startAngle; a <= endAngle; a += 0.04) {
                        const x = cx + rx * Math.cos(a);
                        const y = cy + ry * Math.sin(a) * 0.38 - (ring-1)*4; // lift rings slightly
                        if (a === startAngle) ctx.moveTo(x,y); else ctx.lineTo(x,y);
                    }
                    // inner arc reversed
                    for (let a = endAngle; a >= startAngle; a -= 0.04) {
                        const x = cx + innerRx * Math.cos(a);
                        const y = cy + innerRy * Math.sin(a) * 0.38 - (ring-1)*4;
                        ctx.lineTo(x,y);
                    }
                    ctx.closePath();

                    // Color
                    const baseColor = fc.color;
                    const liftBoost = hover ? 0.3 : 0;
                    ctx.fillStyle = hexAlpha(baseColor, alpha + liftBoost);
                    ctx.fill();

                    // Top edge highlight
                    ctx.beginPath();
                    for (let a = startAngle; a <= endAngle; a += 0.04) {
                        const x = cx + rx * Math.cos(a);
                        const y = cy + ry * Math.sin(a) * 0.38 - (ring-1)*4;
                        if (a === startAngle) ctx.moveTo(x,y); else ctx.lineTo(x,y);
                    }
                    ctx.strokeStyle = hexAlpha(baseColor, 0.6 + liftBoost);
                    ctx.lineWidth   = hover ? 2 : 1;
                    ctx.stroke();

                    // Inner glow lines (the grid lines inside segments)
                    for (let sub = 1; sub <= 3; sub++) {
                        const sa = startAngle + (slice * 0.92 / 4) * sub;
                        const ox = cx + rx * Math.cos(sa);
                        const oy = cy + ry * Math.sin(sa) * 0.38 - (ring-1)*4;
                        const ix = cx + innerRx * Math.cos(sa);
                        const iy = cy + innerRy * Math.sin(sa) * 0.38 - (ring-1)*4;
                        ctx.beginPath();ctx.moveTo(ox,oy);ctx.lineTo(ix,iy);
                        ctx.strokeStyle = hexAlpha(baseColor, 0.2);
                        ctx.lineWidth   = 0.5;
                        ctx.stroke();
                    }
                    ctx.restore();
                }
                startAngle += slice;
            });
        }

        // Center sphere (Super Earth)
        const sphereR = Math.min(W,H)*0.055;
        const sGrd = ctx.createRadialGradient(cx-sphereR*0.3, cy-sphereR*0.3, sphereR*0.1, cx, cy, sphereR);
        sGrd.addColorStop(0,'#5af');sGrd.addColorStop(0.5,'#07f');sGrd.addColorStop(1,'#003');
        ctx.beginPath();ctx.arc(cx,cy,sphereR,0,Math.PI*2);ctx.fillStyle=sGrd;ctx.fill();
        ctx.beginPath();ctx.arc(cx,cy,sphereR,0,Math.PI*2);ctx.strokeStyle='rgba(0,229,255,.4)';ctx.lineWidth=1.5;ctx.stroke();
        // glow ring around sphere
        const sRingGrd = ctx.createRadialGradient(cx,cy,sphereR,cx,cy,sphereR*2.2);
        sRingGrd.addColorStop(0,'rgba(0,180,255,.15)');sRingGrd.addColorStop(1,'transparent');
        ctx.beginPath();ctx.arc(cx,cy,sphereR*2.2,0,Math.PI*2);ctx.fillStyle=sRingGrd;ctx.fill();

        // Legend bottom
        let legX = W*0.08;
        fKeys.forEach(fk => {
            const fc = factionCounts[fk];
            ctx.fillStyle = fc.color;
            ctx.fillRect(legX, H-18, 8, 8);
            ctx.fillStyle = 'rgba(200,220,230,.6)';
            ctx.font = '9px Share Tech Mono';
            ctx.textAlign = 'left';
            ctx.fillText(fk.toUpperCase(), legX+11, H-11);
            legX += fk.length*6.5 + 26;
        });
    }

    // Mouse hover detection on ring segments
    canvas.addEventListener('mousemove', e => {
        const rect  = canvas.getBoundingClientRect();
        const mx    = e.clientX - rect.left;
        const my    = e.clientY - rect.top;
        const W = canvas.width, H = canvas.height;
        const cx = W/2, cy = H*0.52;
        // Rough hit test: find angle from center
        const dx = mx-cx, dy = (my-cy)/0.38;
        const dist = Math.sqrt(dx*dx+dy*dy);
        const outerRx = W*0.44;
        if (dist > outerRx*0.2 && dist < outerRx*1.05) {
            let ang = Math.atan2(dy, dx);
            if (ang < -Math.PI*0.1) ang += Math.PI*2;
            let start = -Math.PI*0.1;
            let found = null;
            const totalPlanets2 = PLANETAS.length||1;
            Object.entries(factionCounts).forEach(([fk,fc]) => {
                const slice = (fc.count/totalPlanets2)*Math.PI*2;
                if (ang >= start && ang < start+slice*0.92) found = fk;
                start += slice;
            });
            hoverSeg = found;
            if (found) {
                const fc = factionCounts[found];
                document.getElementById('ttt-name').textContent = found;
                document.getElementById('ttt-name').style.color = fc.color;
                document.getElementById('ttt-sector').textContent = fc.count+' planets';
                document.getElementById('ttt-faction').textContent = found;
                document.getElementById('ttt-faction').style.color = fc.color;
                document.getElementById('ttt-pct').textContent = Math.round(fc.count/totalPlanets*100);
                document.getElementById('ttt-players').textContent = fc.players.toLocaleString();
                const wr = canvas.getBoundingClientRect();
                let tx = e.clientX-wr.left+14, ty = e.clientY-wr.top+14;
                if(tx+180>W)tx-=190;
                tt.style.left=tx+'px';tt.style.top=ty+'px';tt.style.display='block';
            } else { tt.style.display='none'; }
        } else { hoverSeg=null; tt.style.display='none'; }
        render();
    });
    canvas.addEventListener('mouseleave',()=>{hoverSeg=null;tt.style.display='none';render();});

    function hexAlpha(hex, alpha) {
        const r=parseInt(hex.slice(1,3),16),g=parseInt(hex.slice(3,5),16),b=parseInt(hex.slice(5,7),16);
        return `rgba(${r},${g},${b},${alpha})`;
    }

    window.addEventListener('resize', resize);
    resize();
}

// ── GALACTIC IMPACT MOD ──
function initGim() {
    const canvas = document.getElementById('gimCanvas');
    if (!canvas) return;
    const ctx  = canvas.getContext('2d');
    const wrap = canvas.parentElement;

    document.getElementById('gim-val').textContent = IMPACT_MOD.toFixed(6);

    let history = [];
    try { history = JSON.parse(sessionStorage.getItem('hd2_gim2')||'[]'); } catch(e) { history=[]; }
    history.push({t: Date.now(), p: HD_TOTAL, i: IMPACT_MOD});
    if (history.length > 60) history = history.slice(-60);
    sessionStorage.setItem('hd2_gim2', JSON.stringify(history));

    function draw() {
        const W = canvas.width = wrap.clientWidth;
        const H = canvas.height = 150;
        ctx.clearRect(0,0,W,H);

        // Dark bg
        ctx.fillStyle = 'rgba(0,0,0,.5)'; ctx.fillRect(0,0,W,H);

        if (history.length < 2) {
            ctx.fillStyle='#1a2530'; ctx.font='10px Share Tech Mono';
            ctx.textAlign='center'; ctx.fillText('Accumulating data... reload to build history',W/2,H/2);
            return;
        }

        const maxP = Math.max(...history.map(h=>h.p))*1.12||1;
        const minP = Math.min(...history.map(h=>h.p))*0.9;
        const maxI = Math.max(...history.map(h=>h.i))*1.12||1;
        const minI = Math.min(...history.map(h=>h.i))*0.9;
        const pad  = {l:42,r:42,t:18,b:22};
        const cW   = W-pad.l-pad.r, cH = H-pad.t-pad.b;
        const n    = history.length;
        const px   = i => pad.l + (i/(n-1))*cW;
        const pyP  = v => pad.t+cH - ((v-minP)/(maxP-minP||1))*cH;
        const pyI  = v => pad.t+cH - ((v-minI)/(maxI-minI||1))*cH;

        // Subtle grid
        ctx.strokeStyle='rgba(26,37,48,.5)'; ctx.lineWidth=0.5;
        for(let i=0;i<=4;i++){const y=pad.t+(i/4)*cH;ctx.beginPath();ctx.moveTo(pad.l,y);ctx.lineTo(W-pad.r,y);ctx.stroke();}

        // Players filled area (gold/amber)
        ctx.beginPath();
        ctx.moveTo(px(0), pad.t+cH);
        history.forEach((h,i) => ctx.lineTo(px(i), pyP(h.p)));
        ctx.lineTo(px(n-1), pad.t+cH);
        ctx.closePath();
        const gP=ctx.createLinearGradient(0,pad.t,0,pad.t+cH);
        gP.addColorStop(0,'rgba(180,140,20,.45)');gP.addColorStop(0.6,'rgba(100,80,10,.2)');gP.addColorStop(1,'rgba(50,40,0,.05)');
        ctx.fillStyle=gP; ctx.fill();

        // Purple/illuminate fill (secondary)
        ctx.beginPath();
        ctx.moveTo(px(0), pad.t+cH);
        history.forEach((h,i) => ctx.lineTo(px(i), pyP(h.p*0.65)));
        ctx.lineTo(px(n-1), pad.t+cH); ctx.closePath();
        const gPu=ctx.createLinearGradient(0,pad.t,0,pad.t+cH);
        gPu.addColorStop(0,'rgba(100,20,100,.3)');gPu.addColorStop(1,'rgba(50,10,50,.05)');
        ctx.fillStyle=gPu; ctx.fill();

        // Players line (gold)
        ctx.beginPath();
        history.forEach((h,i) => i===0?ctx.moveTo(px(i),pyP(h.p)):ctx.lineTo(px(i),pyP(h.p)));
        ctx.strokeStyle='#e8c84a'; ctx.lineWidth=2; ctx.stroke();

        // Impact line (cyan)
        ctx.beginPath();
        history.forEach((h,i) => i===0?ctx.moveTo(px(i),pyI(h.i)):ctx.lineTo(px(i),pyI(h.i)));
        ctx.strokeStyle='#00e5ff'; ctx.lineWidth=2; ctx.stroke();

        // Axis labels
        ctx.font='9px Share Tech Mono'; ctx.textAlign='right'; ctx.fillStyle='#e8c84a';
        [0,.5,1].forEach(v=>{const val=Math.round(minP+v*(maxP-minP));const y=pad.t+cH-v*cH;ctx.fillText(val>999?(val/1000).toFixed(0)+'k':val,W-2,y+3);});
        ctx.textAlign='left'; ctx.fillStyle='#00e5ff';
        [0,.5,1].forEach(v=>{const val=(minI+v*(maxI-minI)).toFixed(4);const y=pad.t+cH-v*cH;ctx.fillText(val,2,y+3);});

        // Current value dots + annotations
        const last=history[history.length-1];
        // gold dot
        ctx.beginPath();ctx.arc(px(n-1),pyP(last.p),3,0,Math.PI*2);ctx.fillStyle='#e8c84a';ctx.fill();
        // cyan dot
        ctx.beginPath();ctx.arc(px(n-1),pyI(last.i),3,0,Math.PI*2);ctx.fillStyle='#00e5ff';ctx.fill();

        // Peak annotation
        const maxIdx=history.reduce((mi,h,i)=>h.p>history[mi].p?i:mi,0);
        ctx.fillStyle='#e8c84a';ctx.textAlign='center';ctx.font='8px Share Tech Mono';
        const peakVal=history[maxIdx].p;
        ctx.fillText((peakVal/1000).toFixed(0)+'k',px(maxIdx),pyP(peakVal)-6);
        // current value
        const curVal=last.p;
        ctx.fillText((curVal/1000).toFixed(0)+'k',px(n-1)-4,pyP(curVal)-6);
    }

    draw();
    window.addEventListener('resize', draw);
}
</script>
</body>
</html>