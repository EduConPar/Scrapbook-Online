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
$history_test = hd_fetch("$base/war/history/64", $ctx); // planeta Kerth Secundus index=64
$war_raw      = hd_fetch("https://api.helldivers2.dev/raw/api/WarFeed", $ctx);



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
$accuracy   = $statsSum['bulletsFired'] > 0 ? round($statsSum['bulletsHit']/$statsSum['bulletsFired']*100,1) : 0;
$days       = floor($statsSum['missionTime']/86400);
$hours      = floor(($statsSum['missionTime']%86400)/3600);
$totalKills = $statsSum['terminidKills']+$statsSum['automatonKills']+$statsSum['illuminateKills'];
$kd         = $statsSum['deaths'] > 0 ? round($totalKills/$statsSum['deaths'],1) : '—';

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

// Returns [accentColor, bgColor, label, svgIcon, cssClass]
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
    if($planetId===null&&isset($values[2])) $planetId=(int)$values[2];
    $planetName=($planetId!==null&&isset($planetaMap[$planetId]))?$planetaMap[$planetId]['name']:($planetId!==null?"Planeta #$planetId":null);
    $count=null;
    foreach($vTypes as $i=>$vt){if($vt==3){$count=$values[$i]??null;break;}}
    return match($type){
        11=>$planetName?"Liberar $planetName":"Liberar planeta",
        13=>$planetName?"Defender $planetName":"Defender Super Earth",
        3=>$count?"Completar ".number_format($count)." misiones":"Completar misiones",
        2=>$count?"Eliminar ".number_format($count)." enemigos":"Eliminar enemigos",
        12=>$planetName?"Controlar $planetName":"Controlar planeta",
        default=>$planetName?"Objetivo: $planetName":"Objetivo",
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
// Mapa galáctico
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

:root {
    --gold:   #e8c84a;
    --gold2:  #f5d870;
    --cyan:   #00e5ff;
    --magenta:#ff00cc;
    --orange: #ff9500;
    --red:    #ff3322;
    --purple: #cc44ff;
    --green:  #49e07d;
    --bg:     #000000;
    --panel:  #060a0f;
    --border: #2a3a4a;
    --text:   #ccdde8;
    --muted:  #5a7080;
}

body {
    background: var(--bg);
    color: var(--text);
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 14px;
    overflow: hidden;
    height: 100vh;
    display: flex;
    flex-direction: column;
}

/* ══════════════════════════════════════
   HEADER — replica del banner oficial
══════════════════════════════════════ */
.hd-header {
    background: #000;
    border-bottom: 2px solid var(--gold);
    flex-shrink: 0;
    position: relative;
}
/* línea brillante superior dorada */
.hd-header::before {
    content:'';
    position:absolute;top:0;left:0;right:0;height:1px;
    background: linear-gradient(90deg, transparent, var(--gold2), transparent);
}

.hd-title-row {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 8px 16px 0;
}

/* Diamante con logo */
.hd-emblem {
    width: 36px; height: 36px; flex-shrink: 0;
    position: relative; display: flex; align-items: center; justify-content: center;
}
.hd-emblem::before {
    content:'';
    position:absolute;inset:0;
    background: var(--gold);
    clip-path: polygon(50% 0%,100% 50%,50% 100%,0% 50%);
}
.hd-emblem-icon {
    position: relative; z-index:1;
    font-size: 14px; line-height:1;
}

.hd-brand {
    display: flex; flex-direction: column; line-height: 1.1;
}
.hd-brand-main {
    font-size: 20px; font-weight: 900; letter-spacing: 0.14em;
    color: var(--gold); text-transform: uppercase;
}
.hd-brand-sub {
    font-family: 'Share Tech Mono', monospace;
    font-size: 9px; color: #557; letter-spacing: 0.25em; text-transform: uppercase;
}

.hd-live {
    display: flex; align-items: center; gap: 5px;
    font-family: 'Share Tech Mono', monospace;
    font-size: 10px; color: var(--muted); letter-spacing: 0.1em;
}
.live-dot {
    width: 7px; height: 7px; border-radius: 50%;
    background: var(--red); box-shadow: 0 0 6px var(--red);
    animation: blink 1.1s infinite;
}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.1}}

.btn-refresh {
    margin-left: auto;
    background: transparent;
    border: 1px solid var(--gold);
    color: var(--gold);
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 11px; font-weight: 700; letter-spacing: 0.14em;
    padding: 5px 16px; cursor: pointer; text-transform: uppercase;
    display: flex; align-items: center; gap: 6px;
    clip-path: polygon(6px 0%,100% 0%,calc(100% - 6px) 100%,0% 100%);
    transition: background .15s, color .15s;
}
.btn-refresh:hover { background: var(--gold); color: #000; }
.btn-refresh svg { transition: transform .6s; }
.btn-refresh:hover svg { transform: rotate(360deg); }

/* ── STATS BAR ── */
.hd-stats-bar {
    display: flex;
    border-top: 1px solid #1a2530;
    margin-top: 8px;
}
.hd-stat {
    flex: 1; text-align: center; padding: 6px 4px;
    border-right: 1px solid #1a2530; position: relative;
}
.hd-stat:last-child { border-right: none; }
/* divisores con forma de diamante */
.hd-stat+.hd-stat::before {
    content:'◆';
    position:absolute;left:-1px;top:50%;transform:translate(-50%,-50%);
    font-size:6px;color:#1a2530;
}
.hd-stat-val {
    font-size: 22px; font-weight: 900; display: block;
    line-height: 1; letter-spacing: 0.04em;
   
}
.hd-stat-lbl {
    font-family: 'Share Tech Mono', monospace;
    font-size: 8px; text-transform: uppercase;
    letter-spacing: 0.14em; color: var(--muted); display: block;
    margin-top: 1px;
}

/* ══════════════════════════════════════
   TABS — estilo botones del juego
══════════════════════════════════════ */
.hd-tabs {
    display: flex;
    background: #000;
    border-bottom: 1px solid #1a2530;
    padding: 0 16px;
    flex-shrink: 0;
    gap: 2px;
    overflow-x: auto;
}
.hd-tabs::-webkit-scrollbar{display:none}

.hd-tab {
    font-family: 'Barlow Condensed', sans-serif;
    font-weight: 700; font-size: 12px; letter-spacing: 0.16em;
    text-transform: uppercase; padding: 10px 18px 8px;
    cursor: pointer; color: var(--muted);
    border-bottom: 2px solid transparent; margin-bottom: -1px;
    white-space: nowrap; transition: color .15s;
    display: flex; align-items: center; gap: 6px;
    user-select: none;
}
.hd-tab:hover { color: var(--text); }
.hd-tab.active {
    color: var(--gold);
    border-bottom-color: var(--gold);
    text-shadow: 0 0 8px rgba(232,200,74,.4);
}

/* ══════════════════════════════════════
   SCROLL
══════════════════════════════════════ */
.hd-scroll { flex:1; overflow-y:auto; overflow-x:hidden; }
.hd-scroll::-webkit-scrollbar{width:3px}
.hd-scroll::-webkit-scrollbar-track{background:#000}
.hd-scroll::-webkit-scrollbar-thumb{background:#1a2530}
.hd-content{display:none; padding:14px 16px}
.hd-content.active{display:block}

/* ══════════════════════════════════════
   SECTION HEADER
══════════════════════════════════════ */
.sec-title {
    font-size: 9px; font-weight: 700; letter-spacing: 0.35em;
    text-transform: uppercase; color: var(--gold);
    margin-bottom: 12px; padding-bottom: 6px;
    border-bottom: 1px solid #1a2530;
    display: flex; align-items: center; gap: 8px;
}
.sec-title::before { content:'◆'; font-size: 7px; }

/* ══════════════════════════════════════
   PLANET CARDS — calco del companion oficial
══════════════════════════════════════ */
.planet-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 10px;
}

.planet-card {
    background: #000;
    border: 1px solid var(--gold);
    position: relative;
    cursor: pointer;
    transition: box-shadow .2s, border-color .2s;
    overflow: hidden;
}
/* brillo dorado al hover */
.planet-card:hover {
    box-shadow: 0 0 16px rgba(232,200,74,.25), inset 0 0 16px rgba(232,200,74,.05);
    border-color: var(--gold2);
}
/* esquinas cortadas estilo UI del juego */
.planet-card::before,.planet-card::after {
    content:''; position:absolute; width:10px;height:10px;
    border-color: var(--gold); border-style:solid; z-index:5;
}
.planet-card::before { top:-1px;right:-1px; border-width:2px 2px 0 0; }
.planet-card::after  { bottom:-1px;left:-1px; border-width:0 0 2px 2px; }

/* ── Card Header (tipo + nombre + sector) ── */
.pc-header {
    padding: 7px 10px 6px;
    border-bottom: 1px solid #1a2530;
    background: rgba(0,0,0,.92);
    display: flex; align-items: center; gap: 8px;
    position: relative; z-index:2;
}
.pc-type-icon {
    width: 18px; height: 18px; flex-shrink:0;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px;
    clip-path: polygon(50% 0%,100% 25%,100% 75%,50% 100%,0% 75%,0% 25%);
}
.f-terminid  .pc-type-icon { background: var(--orange); }
.f-automaton .pc-type-icon { background: var(--red); }
.f-illuminate .pc-type-icon{ background: var(--purple); }
.f-human     .pc-type-icon { background: var(--cyan); }

.pc-names { flex:1; min-width:0; }
.pc-planet-name {
    font-size: 16px; font-weight: 900; letter-spacing: 0.06em;
    text-transform: uppercase; line-height: 1;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.f-terminid  .pc-planet-name { color: var(--orange); }
.f-automaton .pc-planet-name { color: var(--red); }
.f-illuminate .pc-planet-name{ color: var(--purple); }
.f-human     .pc-planet-name { color: var(--cyan); }

.pc-sector {
    font-family: 'Share Tech Mono', monospace;
    font-size: 9px; letter-spacing: 0.12em; text-transform: uppercase;
    color: var(--muted); margin-top: 1px;
}

.pc-victory {
    font-family: 'Share Tech Mono', monospace;
    font-size: 9px; color: var(--gold); letter-spacing: 0.06em;
    white-space: nowrap; flex-shrink:0;
    text-shadow: 0 0 8px rgba(232,200,74,.5);
}

/* ── Imagen del planeta ── */
.pc-img {
    position: relative; height: 130px; overflow: hidden;
}
.pc-img-bg {
    position: absolute; inset:0;
    background-size: cover; background-position: center;
    filter: brightness(.7) saturate(.9);
    transition: filter .3s, transform .4s;
}
.planet-card:hover .pc-img-bg { filter: brightness(.9) saturate(1.1); transform: scale(1.05); }

/* Gradient fade bottom */
.pc-img::after {
    content:'';
    position:absolute;inset:0;
    background: linear-gradient(180deg, transparent 40%, rgba(0,0,0,.95) 100%);
}

/* Iconos de hazards en la imagen */
.pc-hazard-row {
    position:absolute; bottom:6px; left:8px; right:8px;
    display:flex; align-items:center; gap:5px; z-index:3;
}
.pc-hazard-icon {
    width: 20px; height: 20px; border-radius: 3px;
    background: rgba(0,0,0,.7); border: 1px solid rgba(255,255,255,.15);
    display: flex; align-items:center; justify-content:center;
    font-size: 10px;
}
.pc-players-badge {
    margin-left:auto; display:flex; align-items:center; gap:4px;
    font-family:'Share Tech Mono',monospace; font-size:9px; color:#aaa;
}
.pc-players-dot {
    width:5px;height:5px;border-radius:50%;background:var(--green);
    box-shadow:0 0 5px var(--green); animation:blink 2s infinite;
}

/* ── Liberation bar — estilo oficial con dientes ── */
.pc-lib-section {
    padding: 8px 10px 0;
    background: #000;
}

.pc-lib-label {
    font-family: 'Share Tech Mono', monospace;
    font-size: 9px; letter-spacing: 0.2em; text-transform: uppercase;
    color: var(--muted); margin-bottom: 4px;
    display: flex; align-items:center; gap:6px;
}
.pc-lib-label-pct {
    margin-left: auto;
    font-size: 11px; font-weight: 700; letter-spacing: 0.06em;
}

/* La barra con el efecto SVG de "dientes" */
.pc-lib-bar-wrap {
    height: 18px; position: relative; margin-bottom: 5px;
    background: #0a0e14;
    border: 1px solid #1a2530;
    overflow: hidden;
}
/* fill cian animado */
.pc-lib-bar-fill {
    position: absolute; top:0; bottom:0; left:0;
    background: repeating-linear-gradient(
        90deg,
        var(--cyan) 0px, var(--cyan) 14px,
        rgba(0,180,220,.7) 14px, rgba(0,180,220,.7) 16px
    );
    box-shadow: 2px 0 8px var(--cyan);
    transition: width .6s ease;
}
/* fill enemy regen (naranja, rellena desde la derecha si aplica) */
.pc-lib-bar-enemy {
    position: absolute; top:0; bottom:0; right:0;
    background: var(--orange);
    opacity: .7;
    transition: width .6s ease;
}
/* Terminids: magenta */
.f-terminid  .pc-lib-bar-fill { background: repeating-linear-gradient(90deg,var(--magenta) 0px,var(--magenta) 14px,#cc0099 14px,#cc0099 16px); box-shadow:2px 0 8px var(--magenta);}
/* Automatons: rojo pero bar sigue siendo cian (es la liberación nuestra) */
/* Illuminate: purple */
.f-illuminate .pc-lib-bar-fill { background: repeating-linear-gradient(90deg,var(--purple) 0px,var(--purple) 14px,#9900cc 14px,#9900cc 16px); box-shadow:2px 0 8px var(--purple);}

/* "pico" decorativo en el extremo de la barra */
.pc-lib-bar-tip {
    position:absolute; top:0;bottom:0; width:8px;
    background: inherit;
    clip-path: polygon(0 0, 60% 0, 100% 50%, 60% 100%, 0 100%);
}

/* ── Status text (HOLDING / LIBERATION) ── */
.pc-status {
    font-family: 'Share Tech Mono', monospace;
    font-size: 9px; letter-spacing: 0.14em; text-transform: uppercase;
    color: var(--muted); margin-bottom: 6px;
    display: flex; align-items:center; gap:5px;
}
.pc-status-icon { font-size: 10px; }

/* ── Footer stats row ── */
.pc-footer {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    border-top: 1px solid #1a2530;
    background: #000;
}
.pc-foot-cell {
    padding: 5px 6px; text-align:center;
    border-right: 1px solid #1a2530;
    font-family: 'Share Tech Mono', monospace; font-size: 9px;
}
.pc-foot-cell:last-child{border-right:none}
.pc-foot-label { color: var(--muted); font-size:8px; letter-spacing:.06em; }
.pc-foot-val   { color: var(--text); font-size:11px; font-weight:700; margin-top:1px; }
.pc-foot-val.cy { color: var(--gold); }
.pc-foot-val.cc { color: var(--cyan); }
.pc-foot-val.co { color: var(--orange); }
.pc-foot-val.cr { color: var(--red); }
.pc-foot-val.cg { color: var(--green); }

/* ══════════════════════════════════════
   MODAL DE PLANETA
══════════════════════════════════════ */
.modal-backdrop {
    position:fixed;inset:0;background:rgba(0,0,0,.88);
    z-index:200;display:none;align-items:center;justify-content:center;padding:20px;
    backdrop-filter:blur(6px);
}
.modal-backdrop.open{display:flex}
.modal {
    background:#000;border:1px solid var(--gold);
    max-width:600px;width:100%;
    animation:modal-in .2s ease;
    position:relative;
}
/* esquinas del modal */
.modal::before,.modal::after{
    content:'';position:absolute;width:14px;height:14px;
    border-color:var(--gold2);border-style:solid;z-index:5;
}
.modal::before{top:-1px;right:-1px;border-width:2px 2px 0 0}
.modal::after{bottom:-1px;left:-1px;border-width:0 0 2px 2px}
@keyframes modal-in{from{opacity:0;transform:scale(.97)}to{opacity:1;transform:scale(1)}}
.modal-hero{height:200px;background-size:cover;background-position:center;position:relative;}
.modal-hero-fade{position:absolute;inset:0;background:linear-gradient(180deg,transparent 30%,#000 100%);}
.modal-hero-header{
    position:absolute;top:0;left:0;right:0;
    padding:10px 14px;
    background:linear-gradient(180deg,rgba(0,0,0,.85),transparent);
    display:flex;align-items:center;gap:8px;
}
.modal-hero-faction {
    font-family:'Share Tech Mono',monospace;
    font-size:9px;letter-spacing:.2em;text-transform:uppercase;
    padding:3px 10px;border:1px solid currentColor;
}
.modal-close {
    position:absolute;top:10px;right:10px;
    background:rgba(0,0,0,.7);border:1px solid #1a2530;
    color:var(--text);width:28px;height:28px;font-size:16px;
    cursor:pointer;display:flex;align-items:center;justify-content:center;
    transition:background .15s;z-index:10;
}
.modal-close:hover{background:var(--red);color:#fff;border-color:var(--red)}
.modal-body{padding:14px 18px 20px}
.modal-name{
    font-size:32px;font-weight:900;color:#fff;
    text-transform:uppercase;letter-spacing:.1em;line-height:1;margin-bottom:2px;
}
.modal-sector{
    font-family:'Share Tech Mono',monospace;
    font-size:10px;color:var(--muted);letter-spacing:.18em;
    text-transform:uppercase;margin-bottom:14px;
}
.modal-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:12px}
.modal-stat{background:#060a0f;border:1px solid #1a2530;padding:10px 12px}
.modal-stat-lbl{
    font-family:'Share Tech Mono',monospace;font-size:8px;
    letter-spacing:.2em;text-transform:uppercase;color:var(--muted);margin-bottom:3px;
}
.modal-stat-val{
    font-size:18px;font-weight:800;color:#fff;
}
.modal-biome-desc{
    font-size:12px;color:var(--muted);line-height:1.65;font-style:italic;
    border-left:2px solid #1a2530;padding-left:10px;
}

/* ══════════════════════════════════════
   ORDERS
══════════════════════════════════════ */
.order-card {
    background:#000;border:1px solid var(--gold);
    border-left:3px solid var(--gold);
    padding:14px 16px;margin-bottom:10px;
    position:relative;
}
.order-card::before{
    content:'';position:absolute;top:0;left:0;right:0;height:1px;
    background:linear-gradient(90deg,var(--gold),transparent);
}
.order-header{
    display:flex;align-items:flex-start;justify-content:space-between;
    gap:12px;margin-bottom:8px;
}
.order-title{
    font-size:20px;font-weight:900;color:var(--gold);
    text-transform:uppercase;letter-spacing:.08em;
}
.order-reward{
    font-family:'Share Tech Mono',monospace;font-size:10px;
    color:#000;background:var(--gold);
    padding:3px 10px;white-space:nowrap;flex-shrink:0;
    font-weight:700;letter-spacing:.06em;
}
.order-briefing{
    font-size:13px;color:var(--muted);line-height:1.65;margin-bottom:12px;
    border-left:2px solid #1a2530;padding-left:10px;
}
.order-expiry{
    font-family:'Share Tech Mono',monospace;font-size:10px;
    color:var(--muted);margin-bottom:12px;letter-spacing:.05em;
}
.order-expiry span{color:var(--gold)}
.task-row{margin-bottom:7px}
.task-label-row{display:flex;align-items:center;gap:8px;margin-bottom:3px}
.task-check{
    width:13px;height:13px;border:1px solid #1a2530;
    display:inline-flex;align-items:center;justify-content:center;
    flex-shrink:0;font-size:8px;font-family:'Share Tech Mono',monospace;
}
.task-check.done{border-color:var(--green);color:var(--green)}
.task-text{font-size:13px}
.task-bar-bg{height:2px;background:#1a2530}
.task-bar-fill{height:100%;background:var(--green);transition:width .5s}

/* ══════════════════════════════════════
   DISPATCHES
══════════════════════════════════════ */
.dispatch-card {
    background:#000;border:1px solid #1a2530;
    border-left:3px solid var(--cyan);
    padding:14px 16px;margin-bottom:10px;
}
.dispatch-date{
    font-family:'Share Tech Mono',monospace;font-size:9px;
    color:var(--muted);letter-spacing:.12em;text-transform:uppercase;margin-bottom:7px;
}
.dispatch-title{
    font-size:16px;font-weight:900;color:var(--gold);
    text-transform:uppercase;letter-spacing:.06em;margin-bottom:7px;line-height:1.2;
}
.dispatch-body{
    font-size:13px;color:var(--muted);line-height:1.7;white-space:pre-wrap;
}

/* ══════════════════════════════════════
   STATS
══════════════════════════════════════ */
.stats-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.stats-card{background:#000;border:1px solid #1a2530;padding:14px 16px}
.stats-card-title{
    font-size:11px;font-weight:700;text-transform:uppercase;
    letter-spacing:.2em;color:var(--gold);margin-bottom:10px;
    display:flex;align-items:center;gap:6px;padding-bottom:6px;
    border-bottom:1px solid #1a2530;
}
.stat-row{
    display:flex;justify-content:space-between;align-items:center;
    padding:4px 0;border-bottom:1px solid #0a0e14;
    font-size:13px;
}
.stat-row:last-child{border-bottom:none}
.stat-val{font-size:16px;font-weight:800;font-family:'Barlow Condensed',sans-serif}

/* ══════════════════════════════════════
   MAPA
══════════════════════════════════════ */
#tab-mapa{padding:0;height:100%}
.mapa-wrap{position:relative;width:100%;height:100%;background:#000;overflow:hidden}
#mapa-canvas{display:block;width:100%;height:100%;cursor:grab}
#mapa-canvas:active{cursor:grabbing}
.mapa-hint{
    position:absolute;bottom:12px;left:50%;transform:translateX(-50%);
    font-family:'Share Tech Mono',monospace;font-size:10px;
    color:#1a2530;letter-spacing:.1em;pointer-events:none;
}
.mapa-tooltip{
    position:absolute;background:#000;border:1px solid var(--gold);
    padding:10px 14px;pointer-events:none;display:none;z-index:10;min-width:200px;
}
.mapa-tooltip::before{
    content:'';position:absolute;top:0;left:0;right:0;height:1px;
    background:linear-gradient(90deg,var(--gold),transparent);
}
.mapa-tt-name{
    font-size:16px;font-weight:800;color:#fff;
    text-transform:uppercase;letter-spacing:.06em;margin-bottom:5px;
}
.mapa-tt-row{
    font-family:'Share Tech Mono',monospace;font-size:10px;
    color:var(--muted);margin-top:2px;letter-spacing:.05em;
}
.mapa-tt-row span{color:var(--text)}

/* ══════════════════════════════════════
   UTILITY COLORS
══════════════════════════════════════ */
.cy{color:var(--gold)}
.co{color:var(--orange)}
.cr{color:var(--red)}
.cp{color:var(--purple)}
.cg{color:var(--green)}
.cc{color:var(--cyan)}
</style>
<style>
body { visibility: hidden; }
</style>
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.body.style.visibility = 'visible';
});
</script>

</head>
<body>

<!-- ══ HEADER ══ -->
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
        <div class="hd-stat">
            <span class="hd-stat-val cy"><?= number_format($hdActivos) ?></span>
            <span class="hd-stat-lbl">Helldivers activos</span>
        </div>
        <div class="hd-stat">
            <span class="hd-stat-val cy"><?= $frentesActivos ?></span>
            <span class="hd-stat-lbl">Frentes activos</span>
        </div>
        <div class="hd-stat">
            <span class="hd-stat-val co"><?= number_format($vsTerminids) ?></span>
            <span class="hd-stat-lbl">vs Terminids</span>
        </div>
        <div class="hd-stat">
            <span class="hd-stat-val cr"><?= number_format($vsAutomatons) ?></span>
            <span class="hd-stat-lbl">vs Automatons</span>
        </div>
        <div class="hd-stat">
            <span class="hd-stat-val cp"><?= number_format($vsIlluminate) ?></span>
            <span class="hd-stat-lbl">vs Illuminate</span>
        </div>
    </div>
</div>

<!-- ══ TABS ══ -->
<div class="hd-tabs">
    <div class="hd-tab active" onclick="showTab('frentes',this)">⚔ Frentes</div>
    <div class="hd-tab" onclick="showTab('overview',this)">🌐 Overview</div>
    <div class="hd-tab"        onclick="showTab('estadisticas',this)">📊 Estadísticas</div>
    <div class="hd-tab"        onclick="showTab('despachos',this)">📡 Despachos</div>
    <div class="hd-tab"        onclick="showTab('mapa',this)">🌌 Mapa</div>
</div>

<!-- ══ CONTENIDO ══ -->
<div class="hd-scroll" id="scroll-area">

    <!-- ── FRENTES ── -->
    <div id="tab-frentes" class="hd-content active">
        <div class="sec-title">Frentes de campaña activos</div>
        <div class="planet-grid">
        <?php foreach ($campanas as $c):
            $p        = $c['planet'];
            $bioma    = $p['biome']['name'] ?? '';
            $img      = getBiomaImg($bioma, $biomaImgs);
            $health   = $p['health']    ?? 0;
            $maxH     = $p['maxHealth'] ?: 1;
            $pct      = max(0, min(100, round((1-$health/$maxH)*100)));
            $jugadores= $p['statistics']['playerCount'] ?? 0;
            $peligros = array_column($p['hazards']??[],'name');
            $regen    = round(($p['regenPerSecond']??0)*3600/$maxH*100, 3);
            [$fColor,$fBg,$fLabel,$fIcon,$fClass] = factionStyle($p['currentOwner']??'');

            // ¿Hay tiempo de victoria estimado? (si la campaña lo trae)
            $victoryIn = '';
            if (isset($c['count']) && isset($c['planet']['statistics']['playerCount'])) {
                // API no siempre da tiempo de victoria; placeholder si el companion lo calcula
            }

            $md = htmlspecialchars(json_encode([
                'name'      => strtoupper($p['name']),
                'sector'    => $p['sector'],
                'owner'     => $p['currentOwner']??'',
                'faction'   => $fLabel,
                'fcolor'    => $fColor,
                'fclass'    => $fClass,
                'lib'       => $pct,
                'players'   => $jugadores,
                'biome'     => $bioma,
                'biomeDesc' => $p['biome']['description']??'',
                'img'       => $img,
                'hazards'   => implode(', ',$peligros),
                'regen'     => $regen,
            ]), ENT_QUOTES);

            // Hazard icons mapping
            $hazardIcons=['Cold'=>'❄','Extreme Cold'=>'🧊','Fire Tornadoes'=>'🌪','Blizzards'=>'🌨',
                'Meteor Showers'=>'☄','Ion Storms'=>'⚡','Tremors'=>'🌍','Rainstorms'=>'🌧',
                'Toxic Rain'=>'☠','Volcanic Activity'=>'🌋','Supercolony'=>'🪲','default'=>'⚠'];
        ?>
            <div class="planet-card <?= $fClass ?>" onclick='openModal(<?= $md ?>)'>

                <!-- Header: icono facción + nombre + sector -->
                <div class="pc-header">
                    <div class="pc-type-icon">
                        <?php if($fClass==='f-terminid') echo '🦟';
                              elseif($fClass==='f-automaton') echo '🤖';
                              elseif($fClass==='f-illuminate') echo '👁';
                              else echo '🌍'; ?>
                    </div>
                    <div class="pc-names">
                        <div class="pc-planet-name"><?= htmlspecialchars($p['name']) ?></div>
                        <div class="pc-sector"><?= htmlspecialchars($p['sector']) ?></div>
                    </div>
                    <?php if($victoryIn): ?>
                    <div class="pc-victory">VICTORY IN: <?= $victoryIn ?></div>
                    <?php endif ?>
                </div>

                <!-- Imagen -->
                <div class="pc-img">
                    <div class="pc-img-bg" style="background-image:url('<?= $img ?>')"></div>
                    <div class="pc-hazard-row">
                        <?php foreach(array_slice($peligros,0,4) as $hz):
                            $ico = $hazardIcons[$hz] ?? $hazardIcons['default']; ?>
                        <div class="pc-hazard-icon" title="<?= htmlspecialchars($hz) ?>"><?= $ico ?></div>
                        <?php endforeach ?>
                        <?php if($jugadores > 0): ?>
                        <div class="pc-players-badge">
                            <div class="pc-players-dot"></div>
                            <?= number_format($jugadores) ?>
                        </div>
                        <?php endif ?>
                    </div>
                </div>

                <!-- Barra de liberación -->
                <div class="pc-lib-section">
                    <div class="pc-lib-label">
                        <span>Liberation</span>
                        <span class="pc-lib-label-pct" style="color:<?= $fColor ?>"><?= number_format($pct,4) ?>%</span>
                    </div>
                    <div class="pc-lib-bar-wrap">
                        <div class="pc-lib-bar-fill" style="width:<?= $pct ?>%">
                            <div class="pc-lib-bar-tip" style="left:<?= $pct ?>%;transform:translateX(-100%)"></div>
                        </div>
                        <?php if($regen > 0): ?>
                        <div class="pc-lib-bar-enemy" style="width:<?= min(30,$regen*10) ?>%"></div>
                        <?php endif ?>
                    </div>
                    <div class="pc-status">
                        <span class="pc-status-icon">
                            <?= $jugadores > 1000 ? '⚔' : '🔃' ?>
                        </span>
                        <?= $jugadores > 500 ? 'LIBERATION IN PROGRESS' : 'HOLDING FOR REINFORCEMENT' ?>
                    </div>
                </div>

                <!-- Footer stats -->
                <div class="pc-footer">
                    <div class="pc-foot-cell">
                        <div class="pc-foot-label">REINF%</div>
                        <div class="pc-foot-val cc"><?= $pct ?>%</div>
                    </div>
                    <div class="pc-foot-cell">
                        <div class="pc-foot-label">PLAYERS</div>
                        <div class="pc-foot-val cg"><?= number_format($jugadores) ?></div>
                    </div>
                    <div class="pc-foot-cell">
                        <div class="pc-foot-label">LIB/H</div>
                        <div class="pc-foot-val cy">+<?= $regen ?>%</div>
                    </div>
                    <div class="pc-foot-cell">
                        <div class="pc-foot-label">REGEN/H</div>
                        <div class="pc-foot-val co"><?= $regen ?>%</div>
                    </div>
                </div>

            </div>
        <?php endforeach ?>
        </div>
    </div>

    <!-- ── ÓRDENES ── -->
   <!-- ── ÓRDENES ── -->
<div id="tab-overview" class="hd-content" style="padding:0;height:100%">
    <div style="display:grid;grid-template-columns:340px 1fr 320px;height:100%;overflow:hidden;">

        <!-- ═══ COLUMNA IZQUIERDA ═══ -->
        <div style="border-right:1px solid #1a2530;overflow-y:auto;overflow-x:hidden;">
            <div style="overflow-y:auto;overflow-x:hidden;height:100%">
            <div style="overflow-y:auto;overflow-x:hidden;height:100%;">

            <!-- Distribution of Democracy header -->
            <div style="text-align:center;padding:10px 12px 8px;border-bottom:1px solid #1a2530;background:#000;">
                <div style="font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:.3em;color:var(--gold);text-transform:uppercase;margin-bottom:6px;">
                    Distribution of Democracy
                </div>
                <div style="font-family:'Share Tech Mono',monospace;font-size:9px;color:var(--muted);display:flex;align-items:center;justify-content:center;gap:8px;margin-bottom:6px;">
                    <span>SEST <?= date('H:i:s') ?></span>
                    <span>📅 <?= date('d/m/Y') ?></span>
                    <span style="background:var(--gold);color:#000;font-weight:900;font-size:9px;padding:2px 8px;letter-spacing:.1em;">
                        DAY <?= floor((time() - strtotime('2024-02-08')) / 86400) ?>
                    </span>
                </div>
                <div style="font-size:28px;font-weight:900;color:var(--gold);letter-spacing:.04em;display:flex;align-items:center;justify-content:center;gap:8px;">
                    <span>🪙</span><span><?= number_format($hdActivos) ?></span>
                </div>
            </div>

            <!-- Pie chart -->
            <div style="padding:10px 12px;border-bottom:1px solid #1a2530;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <canvas id="ov-pie" width="130" height="130" style="flex-shrink:0"></canvas>
                    <div id="ov-pie-legend" style="flex:1;min-width:0;"></div>
                </div>
            </div>

            <!-- Faction bars -->
            <div style="padding:8px 12px;border-bottom:1px solid #1a2530;">
                <?php
                $factions = [
                    ['label'=>'Illuminate','players'=>$vsIlluminate,'color'=>'var(--purple)','icon'=>'👁'],
                    ['label'=>'Terminids', 'players'=>$vsTerminids, 'color'=>'var(--orange)','icon'=>'🦟'],
                    ['label'=>'Automatons','players'=>$vsAutomatons,'color'=>'var(--red)',   'icon'=>'🤖'],
                ];
                foreach($factions as $f):
                    $pct  = $hdActivos > 0 ? round($f['players']/$hdActivos*100,3) : 0;
                    $barW = $hdActivos > 0 ? round($f['players']/$hdActivos*100,1) : 0;
                ?>
                <div style="display:flex;align-items:center;border:1px solid <?= $f['color'] ?>33;margin-bottom:5px;background:#06090d;position:relative;overflow:hidden;">
                    <div style="width:22px;height:24px;flex-shrink:0;border-right:1px solid #2a3a4a;display:flex;align-items:center;justify-content:center;font-size:9px;background:rgba(73,224,125,.1);color:var(--green);">✓</div>
                    <div style="width:22px;height:24px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:12px;"><?= $f['icon'] ?></div>
                    <div style="font-size:17px;font-weight:900;padding:0 6px;color:<?= $f['color'] ?>;min-width:65px;"><?= number_format($f['players']) ?></div>
                    <div style="font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--muted);flex:1;">○<?= $pct ?>%</div>
                    <div style="position:absolute;top:0;left:0;bottom:0;width:<?= $barW ?>%;background:<?= $f['color'] ?>;opacity:.08;pointer-events:none;"></div>
                </div>
                <?php endforeach ?>
            </div>

            <!-- War stats -->
            <div style="font-size:9px;font-weight:700;letter-spacing:.3em;text-transform:uppercase;color:var(--gold);padding:8px 12px 6px;border-bottom:1px solid #1a2530;display:flex;align-items:center;gap:6px;">
                <span style="font-size:6px;">◆</span> War Statistics
            </div>
            <div style="padding:6px 12px;">
                <?php
                $missionTotal = $statsSum['missionsWon'] + $statsSum['missionsLost'];
                $missionRate  = $missionTotal > 0 ? round($statsSum['missionsWon']/$missionTotal*100,1) : 0;
                $rows = [
                    ['Missions Completed', number_format($statsSum['missionsWon']).' ('.$missionRate.'%)'],
                    ['Missions Failed',    number_format($statsSum['missionsLost']).' ('.round(100-$missionRate,1).'%)'],
                    ['Mission Dive Time',  $days.'d '.$hours.'h'],
                    ['Shots Fired',        number_format($statsSum['bulletsFired'])],
                    ['Projectile Hits',    number_format($statsSum['bulletsHit'])],
                    ['Hits to Shot Ratio', $accuracy.'%'],
                    ['Kill to Death Ratio',$kd.' : 1'],
                    ['Dead Terminids',     number_format($statsSum['terminidKills'])],
                    ['Dead Automatons',    number_format($statsSum['automatonKills'])],
                    ['Dead Illuminate',    number_format($statsSum['illuminateKills'])],
                    ['Helldivers KIA',     number_format($statsSum['deaths'])],
                    ['Accidentals',        number_format($statsSum['friendlies'])],
                ];
                foreach($rows as $r): ?>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:3px 0;border-bottom:1px solid #0a0e14;font-size:12px;">
                    <span style="color:var(--gold)"><?= $r[0] ?></span>
                    <span style="font-family:'Share Tech Mono',monospace;font-size:11px;"><?= $r[1] ?></span>
                </div>
                <?php endforeach ?>
            </div>

            </div>
        </div>

        <!-- ═══ COLUMNA CENTRAL ═══ -->
        <div style="border-right:1px solid #1a2530;overflow-y:auto;overflow-x:hidden;display:flex;flex-direction:column;">

            <!-- Theater of War -->
            <div style="font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:.3em;color:var(--muted);text-transform:uppercase;text-align:center;padding:8px;border-bottom:1px solid #1a2530;background:#000;flex-shrink:0;">
                Theater of War
            </div>
            <div style="position:relative;height:300px;flex-shrink:0;background:radial-gradient(ellipse at center,#0a0d14,#000);">
                <canvas id="ov-theater" style="display:block;width:100%;height:100%;cursor:grab;"></canvas>
                <div style="position:absolute;bottom:6px;left:50%;transform:translateX(-50%);font-family:'Share Tech Mono',monospace;font-size:9px;color:#1a2530;letter-spacing:.1em;pointer-events:none;">
                    Scroll: zoom · Drag: pan · Hover: info
                </div>
                <div id="ov-tt" style="position:absolute;background:#000;border:1px solid var(--gold);padding:8px 12px;pointer-events:none;display:none;z-index:10;min-width:180px;">
                    <div id="ov-tt-name" style="font-size:14px;font-weight:800;color:#fff;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;"></div>
                    <div style="font-family:'Share Tech Mono',monospace;font-size:9px;color:var(--muted);margin-top:2px;">Sector: <span id="ov-tt-sector" style="color:var(--text)"></span></div>
                    <div style="font-family:'Share Tech Mono',monospace;font-size:9px;color:var(--muted);margin-top:2px;">Faction: <span id="ov-tt-faction" style="color:var(--text)"></span></div>
                    <div style="font-family:'Share Tech Mono',monospace;font-size:9px;color:var(--muted);margin-top:2px;">Liberation: <span id="ov-tt-pct" style="color:var(--text)"></span>%</div>
                    <div style="font-family:'Share Tech Mono',monospace;font-size:9px;color:var(--muted);margin-top:2px;">Players: <span id="ov-tt-players" style="color:var(--text)"></span></div>
                </div>
            </div>

            <!-- Galactic Impact Mod -->
            <div style="flex-shrink:0;border-top:1px solid #1a2530;">
                <div style="display:flex;align-items:center;justify-content:center;gap:8px;padding:7px 12px;border-bottom:1px solid #1a2530;font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--muted);letter-spacing:.1em;">
                    Galactic Impact Mod ×
                    <span id="ov-gim-val" style="color:var(--green);font-weight:700;font-size:13px;">—</span>
                </div>
                <div style="padding:6px 12px 4px;">
                    <canvas id="ov-gim" style="display:block;width:100%;height:140px;"></canvas>
                </div>
            </div>

            <!-- Dispatches -->
            <div style="font-size:9px;font-weight:700;letter-spacing:.3em;text-transform:uppercase;color:var(--gold);padding:8px 12px 6px;border-bottom:1px solid #1a2530;border-top:1px solid #1a2530;display:flex;align-items:center;gap:6px;flex-shrink:0;">
                <span style="font-size:6px;">◆</span> Transmissions
            </div>
            <div style="flex:1;overflow-y:auto;">
                <?php foreach(array_slice($despachos,0,6) as $d):
                    $raw   = $d['message'] ?? '';
                    $parts = preg_split('/\n\n|\r\n\r\n/',$raw,2);
                    $titulo= trim(strip_tags($parts[0]));
                    $cuerpo= isset($parts[1])?trim(strip_tags($parts[1])):'';
                    $fecha = isset($d['published'])?date('d M Y · H:i',strtotime($d['published'])).' UTC':'—';
                ?>
                <div style="border-left:2px solid var(--cyan);padding:10px 12px;border-bottom:1px solid #1a2530;position:relative;">
                    <div style="position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,var(--cyan),transparent);opacity:.4;"></div>
                    <div style="font-family:'Share Tech Mono',monospace;font-size:8px;color:var(--muted);letter-spacing:.15em;text-transform:uppercase;margin-bottom:5px;">📡 <?= $fecha ?></div>
                    <?php if($titulo): ?><div style="font-size:13px;font-weight:900;color:#fff;text-transform:uppercase;letter-spacing:.06em;margin-bottom:5px;"><?= htmlspecialchars($titulo) ?></div><?php endif ?>
                    <?php if($cuerpo): ?><div style="font-size:12px;color:var(--muted);line-height:1.65;white-space:pre-wrap;"><?= htmlspecialchars($cuerpo) ?></div><?php endif ?>
                </div>
                <?php endforeach ?>
            </div>

        </div>

        <!-- ═══ COLUMNA DERECHA — MAJOR ORDER ═══ -->
        <div style="overflow-y:auto;overflow-x:hidden;">
            <?php if(!empty($ordenes)): foreach($ordenes as $orden):
                $tasks    = $orden['tasks']    ?? [];
                $progress = $orden['progress'] ?? [];
                $expiry   = formatExpiry($orden['expiration'] ?? '');
                $title    = $orden['title']    ?? 'MAJOR ORDER';
                $briefing = $orden['briefing'] ?? $orden['description'] ?? '';
                $rewardData = $orden['reward'] ?? ($orden['rewards'][0] ?? null);
                $rewardType = $rewardData['type']   ?? 1;
                $rewardAmt  = $rewardData['amount'] ?? '—';
                $rewardTypes = [1=>['🏅','MEDALS'],4=>['💠','SUPER CREDITS'],6=>['🎽','CAPE']];
                [$rIcon,$rLabel] = $rewardTypes[$rewardType] ?? ['🎖','REWARD'];
            ?>

            <?php if(!empty($despachos)): ?>
            <div style="background:rgba(0,229,255,.07);border:1px solid var(--cyan);padding:6px 10px;margin:10px 14px 0;font-family:'Share Tech Mono',monospace;font-size:9px;color:var(--cyan);letter-spacing:.1em;text-transform:uppercase;display:flex;align-items:center;gap:6px;">
                📡 A NEW MESSAGE has been received from Super Earth
            </div>
            <?php endif ?>

            <!-- Hero -->
            <div style="position:relative;height:90px;overflow:hidden;background:linear-gradient(135deg,#0a0510,#050010);border-bottom:1px solid #1a2530;margin-top:10px;">
                <div style="position:absolute;inset:0;background:url('../assets/img/companion/Black_Hole_Landscape.webp') center/cover;opacity:.2;filter:saturate(.5);"></div>
                <div style="position:relative;z-index:2;padding:12px 14px;display:flex;align-items:flex-start;gap:10px;">
                    <div style="width:42px;height:42px;flex-shrink:0;background:#0a0a0a;border:1px solid #2a3a4a;display:flex;align-items:center;justify-content:center;font-size:22px;">💀</div>
                    <div>
                        <div style="font-family:'Share Tech Mono',monospace;font-size:8px;letter-spacing:.25em;color:var(--muted);text-transform:uppercase;margin-bottom:3px;">Major Order</div>
                        <div style="font-size:20px;font-weight:900;color:var(--gold);text-transform:uppercase;letter-spacing:.1em;line-height:1;"><?= htmlspecialchars(strtoupper($title)) ?></div>
                    </div>
                </div>
            </div>

            <div style="padding:12px 14px;">
                <?php if($briefing): ?>
                <div style="font-size:12px;color:#8aabbb;line-height:1.65;margin-bottom:12px;"><?= nl2br(htmlspecialchars($briefing)) ?></div>
                <?php endif ?>

                <div style="display:flex;align-items:center;gap:7px;font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:.25em;color:var(--muted);text-transform:uppercase;margin-bottom:10px;">
                    🎯 Order Overview
                </div>

                <?php foreach($tasks as $i => $task):
                    $rawProg = $progress[$i] ?? 0;
                    $isDone  = ($rawProg > 0);
                    $values  = $task['values']     ?? [];
                    $vTypes  = $task['valueTypes']  ?? [];
                    $planetId = null;
                    foreach($vTypes as $vi=>$vt){ if($vt==12){$planetId=$values[$vi]??null;break;} }
                    $libPct = null;
                    if($planetId!==null && isset($planetaMap[$planetId])){
                        $pp=$planetaMap[$planetId];
                        $libPct=round((1-($pp['health']??0)/($pp['maxHealth']?:1))*100,1);
                    }
                    $label  = taskLabel($task,$planetaMap);
                    $pctBar = $isDone ? 100 : ($libPct ?? 0);
                ?>
                <div style="margin-bottom:12px;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                        <div style="width:15px;height:15px;flex-shrink:0;border:2px solid <?= $isDone?'var(--green)':'#2a3a4a' ?>;background:<?= $isDone?'rgba(73,224,125,.12)':'transparent' ?>;display:flex;align-items:center;justify-content:center;font-size:9px;color:var(--green);"><?= $isDone?'✓':'' ?></div>
                        <span style="font-size:13px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:<?= $isDone?'var(--green)':'#ccdde8' ?>;text-decoration:<?= $isDone?'line-through':'none' ?>;">
                            <?php
                            if($planetId!==null && isset($planetaMap[$planetId])){
                                $pname=htmlspecialchars($planetaMap[$planetId]['name']);
                                $prefix=htmlspecialchars(str_replace($planetaMap[$planetId]['name'],'',$label));
                                echo $prefix.'<span style="color:var(--purple)">'.$pname.'</span>';
                            } else { echo htmlspecialchars($label); }
                            ?>
                        </span>
                    </div>
                    <div style="height:6px;background:#0a0e14;border:1px solid #1a2530;position:relative;overflow:hidden;">
                        <div style="position:absolute;top:0;left:0;bottom:0;width:<?= $pctBar ?>%;background:<?= $isDone?'var(--green)':'var(--magenta)' ?>;transition:width .6s;"></div>
                    </div>
                    <?php if(!$isDone && $libPct!==null): ?>
                    <div style="font-family:'Share Tech Mono',monospace;font-size:9px;color:var(--cyan);margin-top:2px;"><?= $libPct ?>%</div>
                    <?php endif ?>
                </div>
                <?php endforeach ?>

                <div style="border-top:1px solid #1a2530;padding-top:9px;margin-top:4px;font-family:'Share Tech Mono',monospace;font-size:9px;color:var(--muted);letter-spacing:.08em;display:flex;align-items:center;gap:5px;">
                    ⏱ Complete in: <span style="color:var(--gold);font-weight:700;"><?= $expiry ?></span>
                </div>

                <!-- Reward -->
                <div style="border:1px solid #2a3a4a;margin-top:14px;background:#060a0f;overflow:hidden;">
                    <div style="font-family:'Share Tech Mono',monospace;font-size:8px;letter-spacing:.3em;text-transform:uppercase;color:var(--muted);padding:6px 10px;border-bottom:1px solid #1a2530;">Reward</div>
                    <div style="display:flex;align-items:center;gap:10px;padding:10px;">
                        <div style="width:50px;height:50px;background:#0a0a0a;border:1px solid #2a3a4a;display:flex;align-items:center;justify-content:center;font-size:26px;flex-shrink:0;"><?= $rIcon ?></div>
                        <div>
                            <div style="font-size:16px;font-weight:900;color:#fff;text-transform:uppercase;letter-spacing:.06em;"><?= $rLabel ?></div>
                            <div style="font-family:'Share Tech Mono',monospace;font-size:10px;color:var(--gold);margin-top:2px;">× <?= number_format((int)$rewardAmt) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <?php endforeach; else: ?>
            <div style="padding:40px 14px;text-align:center;font-family:'Share Tech Mono',monospace;font-size:11px;color:var(--muted);">No active major orders.</div>
            <?php endif ?>
        </div>

    </div>
</div>

    <!-- ── ESTADÍSTICAS ── -->
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
                <div class="stat-row"><span>Tiempo en misión</span><span class="stat-val"><?= "{$days}d {$hours}h" ?></span></div>
                <div class="stat-row"><span>Helldivers activos</span><span class="stat-val cg"><?= number_format($hdActivos) ?></span></div>
                <div class="stat-row"><span>Frentes activos</span><span class="stat-val cy"><?= $frentesActivos ?></span></div>
            </div>
        </div>
    </div>

    <!-- ── DESPACHOS ── -->
    <!-- ── DESPACHOS ── -->
     
<div id="tab-despachos" class="hd-content">
    <div class="sec-title">Transmisiones de Alto Mando</div>
    <?php if(empty($despachos)): ?>
        <p style="color:var(--muted);font-family:'Share Tech Mono',monospace;font-size:12px;text-align:center;padding:40px 0">
            Sin transmisiones disponibles.
        </p>
    <?php else: foreach(array_slice($despachos,0,15) as $d):
        $raw   = $d['message'] ?? '';
        $parts = preg_split('/\n\n|\r\n\r\n/', $raw, 2);
        $titulo= trim(strip_tags($parts[0]));
        $cuerpo= isset($parts[1]) ? trim(strip_tags($parts[1])) : '';
        $fecha = isset($d['published'])
            ? date('d M Y · H:i', strtotime($d['published'])).' UTC'
            : '—';
    ?>
        <div style="
            background:#000;
            border:1px solid #1a2530;
            border-left:2px solid var(--cyan);
            padding:14px 16px;
            margin-bottom:8px;
            position:relative;
        ">
            <!-- Línea superior sutil -->
            <div style="position:absolute;top:0;left:0;right:0;height:1px;
                background:linear-gradient(90deg,var(--cyan),transparent)"></div>

            <!-- Cabecera: icono antena + fecha -->
            <div style="
                display:flex;align-items:center;gap:7px;
                font-family:'Share Tech Mono',monospace;
                font-size:9px;letter-spacing:.18em;text-transform:uppercase;
                color:var(--muted);margin-bottom:8px;
            ">
                <span style="color:var(--cyan)">📡</span>
                TRANSMISSION · <?= $fecha ?>
            </div>

            <!-- Título del despacho -->
            <?php if($titulo): ?>
            <div style="
                font-size:15px;font-weight:900;
                color:#fff;
                text-transform:uppercase;letter-spacing:.08em;
                line-height:1.2;margin-bottom:8px;
            "><?= htmlspecialchars($titulo) ?></div>
            <?php endif ?>

            <!-- Cuerpo -->
            <?php if($cuerpo): ?>
            <div style="
                font-size:13px;color:#7a9aaa;
                line-height:1.75;
                white-space:pre-wrap;
                border-left:2px solid #1a2530;
                padding-left:10px;
            "><?= htmlspecialchars($cuerpo) ?></div>
            <?php endif ?>
        </div>
    <?php endforeach; endif ?>
</div>

    <!-- ── MAPA ── -->
    <div id="tab-mapa" class="hd-content" style="padding:0;height:100%">
        <div class="mapa-wrap" id="mapa-wrap">
            <canvas id="mapa-canvas"></canvas>
            <div class="mapa-hint">Rueda: zoom · Arrastrar: mover · Hover: detalles</div>
            <div class="mapa-tooltip" id="mapa-tooltip">
                <div class="mapa-tt-name" id="tt-name"></div>
                <div class="mapa-tt-row">Sector: <span id="tt-sector"></span></div>
                <div class="mapa-tt-row">Facción: <span id="tt-faction"></span></div>
                <div class="mapa-tt-row">Liberación: <span id="tt-pct"></span>%</div>
                <div class="mapa-tt-row">Jugadores: <span id="tt-players"></span></div>
                <div class="mapa-tt-row" id="tt-haz-row" style="display:none">
                    Peligros: <span id="tt-haz"></span>
                </div>
            </div>
        </div>
    </div>

</div><!-- /scroll -->

<!-- ══ MODAL PLANETA ══ -->
<div class="modal-backdrop" id="planetModal" onclick="if(event.target===this)closeModal()">
    <div class="modal" id="modalEl">
        <div class="modal-hero" id="modalHero">
            <div class="modal-hero-fade"></div>
            <div class="modal-hero-header">
                <div class="modal-hero-faction" id="modalFactionBadge"></div>
            </div>
            <button class="modal-close" onclick="closeModal()">✕</button>
        </div>
        <div class="modal-body">
            <div class="modal-name" id="modalName"></div>
            <div class="modal-sector" id="modalSector"></div>
            <div class="modal-grid">
                <div class="modal-stat">
                    <div class="modal-stat-lbl">Facción</div>
                    <div class="modal-stat-val" id="modalFaction" style="font-size:14px"></div>
                </div>
                <div class="modal-stat">
                    <div class="modal-stat-lbl">Liberación</div>
                    <div class="modal-stat-val" id="modalLib"></div>
                </div>
                <div class="modal-stat">
                    <div class="modal-stat-lbl">Helldivers</div>
                    <div class="modal-stat-val" id="modalPlayers"></div>
                </div>
                <div class="modal-stat">
                    <div class="modal-stat-lbl">Bioma</div>
                    <div class="modal-stat-val" id="modalBiome" style="font-size:13px"></div>
                </div>
            </div>
            <div class="modal-biome-desc" id="modalBiomeDesc"></div>
        </div>
    </div>
</div>

<script>
    
// ── Tabs ──
function showTab(name, el) {
    // quitar active a todo
    document.querySelectorAll('.hd-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.hd-tab').forEach(t => t.classList.remove('active'));

    // activar correcto
    const target = document.getElementById('tab-' + name);
    if (target) target.classList.add('active');
    if (el) el.classList.add('active');

    const scroll = document.getElementById('scroll-area');

    // reset default
    scroll.style.overflow = 'auto';

    // lógica por tab
    if (name === 'mapa') {
        scroll.style.overflow = 'hidden';
        if (typeof initMapa === 'function') initMapa();
    }

    if (name === 'overview') {
        scroll.style.overflow = 'hidden';
        if (typeof initOverview === 'function') initOverview();
    }
}

// ── Modal ──
function openModal(d) {
    document.getElementById('modalHero').style.backgroundImage=`url('${d.img}')`;
    document.getElementById('modalName').textContent=d.name;
    document.getElementById('modalSector').textContent='SECTOR: '+d.sector;
    const fb=document.getElementById('modalFactionBadge');
    fb.textContent=d.faction; fb.style.color=d.fcolor; fb.style.borderColor=d.fcolor;
    document.getElementById('modalFaction').textContent=d.faction;
    document.getElementById('modalFaction').style.color=d.fcolor;
    document.getElementById('modalLib').textContent=d.lib+'%';
    document.getElementById('modalLib').style.color=d.fcolor;
    document.getElementById('modalPlayers').textContent=d.players>0?d.players.toLocaleString():'—';
    document.getElementById('modalBiome').textContent=d.biome||'—';
    document.getElementById('modalBiomeDesc').textContent=d.biomeDesc||'';
    // Apply faction border color to modal
    document.getElementById('modalEl').style.borderColor=d.fcolor;
    document.getElementById('planetModal').classList.add('open');
}
function closeModal(){
    document.getElementById('planetModal').classList.remove('open');
}

// ── Mapa ──
const PLANETAS=<?= $mapaJson ?>;
const WAYPOINTS=<?= $wpJson ?>;
let mapaReady=false;

function initMapa(){
    if(mapaReady)return; mapaReady=true;
    const wrap=document.getElementById('mapa-wrap');
    const canvas=document.getElementById('mapa-canvas');
    const tt=document.getElementById('mapa-tooltip');
    const ctx=canvas.getContext('2d');
    let W,H,scale,zoom=1;
    let drag=false,dragStart={x:0,y:0},viewOff={x:0,y:0},viewStart={x:0,y:0};

    function resize(){
        W=canvas.width=wrap.clientWidth;
        H=canvas.height=wrap.clientHeight;
        scale=Math.min(W,H)*0.42; draw();
    }
    function toC(x,y){return{cx:W/2+viewOff.x+x*scale*zoom,cy:H/2+viewOff.y-y*scale*zoom};}

    function draw(){
        ctx.clearRect(0,0,W,H);
        ctx.fillStyle='#000'; ctx.fillRect(0,0,W,H);
        // Estrellas
        let sr=42;
        const rnd=()=>{sr=(sr*1664525+1013904223)&0xffffffff;return(sr>>>0)/0xffffffff;};
        for(let i=0;i<400;i++){
            const b=0.05+rnd()*0.3;
            ctx.fillStyle=`rgba(255,255,255,${b})`;
            ctx.beginPath();ctx.arc(rnd()*W,rnd()*H,rnd()*1.3,0,Math.PI*2);ctx.fill();
        }
        // Círculos decorativos
        ctx.strokeStyle='rgba(232,200,74,0.05)';ctx.lineWidth=1;
        [0.25,0.5,0.75,1].forEach(r=>{
            ctx.beginPath();ctx.arc(W/2+viewOff.x,H/2+viewOff.y,r*scale*zoom,0,Math.PI*2);ctx.stroke();
        });
        // Waypoints
        ctx.lineWidth=0.6;
        WAYPOINTS.forEach(wp=>{
            const a=toC(wp.x1,wp.y1),b=toC(wp.x2,wp.y2);
            ctx.strokeStyle='rgba(232,200,74,0.12)';
            ctx.beginPath();ctx.moveTo(a.cx,a.cy);ctx.lineTo(b.cx,b.cy);ctx.stroke();
        });
        // Planetas
        PLANETAS.forEach(p=>{
            const{cx,cy}=toC(p.x,p.y);
            const r=p.name==='Super Earth'?8:5;
            const grd=ctx.createRadialGradient(cx,cy,0,cx,cy,r*3.5);
            grd.addColorStop(0,p.color+'55');grd.addColorStop(1,'transparent');
            ctx.fillStyle=grd;ctx.beginPath();ctx.arc(cx,cy,r*3.5,0,Math.PI*2);ctx.fill();
            ctx.fillStyle=p.color;ctx.beginPath();ctx.arc(cx,cy,r,0,Math.PI*2);ctx.fill();
            // Nombre con fondo negro
            ctx.font=`${p.name==='Super Earth'?9:8}px "Share Tech Mono",monospace`;
            ctx.textAlign='center';
            const tw=ctx.measureText(p.name).width;
            ctx.fillStyle='rgba(0,0,0,.6)';
            ctx.fillRect(cx-tw/2-2,cy-r-12,tw+4,10);
            ctx.fillStyle='rgba(255,255,255,0.7)';
            ctx.fillText(p.name,cx,cy-r-4);
        });
    }

    canvas.addEventListener('mousemove',e=>{
        const rect=canvas.getBoundingClientRect();
        const mx=e.clientX-rect.left,my=e.clientY-rect.top;
        let found=null;
        PLANETAS.forEach(p=>{const{cx,cy}=toC(p.x,p.y);if(Math.hypot(mx-cx,my-cy)<14)found=p;});
        if(found){
            document.getElementById('tt-name').textContent=found.name;
            document.getElementById('tt-sector').textContent=found.sector;
            document.getElementById('tt-faction').textContent=found.faction;
            document.getElementById('tt-faction').style.color=found.color;
            document.getElementById('tt-pct').textContent=found.pct;
            document.getElementById('tt-players').textContent=found.players.toLocaleString();
            const hr=document.getElementById('tt-haz-row');
            if(found.hazards){document.getElementById('tt-haz').textContent=found.hazards;hr.style.display='block';}
            else hr.style.display='none';
            const wr=wrap.getBoundingClientRect();
            let tx=e.clientX-wr.left+16,ty=e.clientY-wr.top+16;
            if(tx+210>W)tx-=225;
            tt.style.borderColor=found.color;
            tt.style.left=tx+'px';tt.style.top=ty+'px';tt.style.display='block';
        } else tt.style.display='none';
        if(drag){viewOff.x=viewStart.x+(e.clientX-dragStart.x);viewOff.y=viewStart.y+(e.clientY-dragStart.y);draw();}
    });
    canvas.addEventListener('mousedown',e=>{drag=true;dragStart={x:e.clientX,y:e.clientY};viewStart={x:viewOff.x,y:viewOff.y};});
    canvas.addEventListener('mouseup',()=>drag=false);
    canvas.addEventListener('mouseleave',()=>{drag=false;tt.style.display='none';});
    canvas.addEventListener('wheel',e=>{
        e.preventDefault();zoom*=e.deltaY<0?1.1:0.9;zoom=Math.max(0.3,Math.min(6,zoom));draw();
    },{passive:false});
    window.addEventListener('resize',resize);
    resize();
}

</script>
</body>
</html>