<?php
/* ──────────────────────────────────────────────────────────────────────
   DETECCIÓN DE DISPOSITIVO MÓVIL
   ──────────────────────────────────────────────────────────────────────
   Centralizado para que tanto index.php (al loguear) como
   desktop-base.php (al cargar el escritorio) puedan redirigir a
   mobile.php de forma coherente.

   Override:
     - ?desktop=1 en la URL                            → fuerza escritorio
     - cookie force_desktop=1                          → fuerza escritorio
   Las tablets NO entran en el flujo móvil — usan el escritorio.
   ────────────────────────────────────────────────────────────────────── */

if (!function_exists('isMobileDevice')) {
    function isMobileDevice(): bool {
        if (isset($_GET['desktop']))                                              return false;
        if (!empty($_COOKIE['force_desktop']) && $_COOKIE['force_desktop'] === '1') return false;
        $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
        if ($ua === '') return false;
        /* iPad / "Tablet" → escritorio (Apple no incluye "Mobi" en iPad desde
           iOS 13, pero algunos navegadores sí ponen "iPad"). */
        if (preg_match('/iPad|Tablet/i', $ua)) return false;
        return (bool)preg_match('/Mobi|Android.*Mobile|iPhone|iPod|BlackBerry|IEMobile|Opera Mini/i', $ua);
    }
}

if (!function_exists('isTabletDevice')) {
    /* Detección de tablet (iPad, Android tablet, Kindle, Surface no-touch…).
       Las tablets entran al MISMO escritorio que el PC pero se les sirve un
       viewport responsive + tablet.css. NO redirige a mobile.
       Override:
         - ?tablet=1 / ?tablet=0 en la URL    → fuerza on/off
         - cookie force_tablet=1              → fuerza on
       Útil para depurar en PC. */
    function isTabletDevice(): bool {
        if (isset($_GET['tablet'])) return $_GET['tablet'] === '1';
        if (!empty($_COOKIE['force_tablet']) && $_COOKIE['force_tablet'] === '1') return true;
        $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
        if ($ua === '') return false;
        /* iPad/Android tablet/Kindle/Silk/PlayBook. "Android" sin "Mobile" en
           el UA es la convención que Google indica para tablets. */
        if (preg_match('/iPad|Tablet|Kindle|Silk|PlayBook/i', $ua)) return true;
        if (preg_match('/Android/i', $ua) && !preg_match('/Mobile/i', $ua)) return true;
        return false;
    }
}

if (!function_exists('setLongSessionCookie')) {
    /* Llamar SIEMPRE antes de session_start(). Marca la cookie de sesión
       como persistente 30 días para que, una vez logueado en PWA o
       navegador, el usuario no tenga que volver a meter la contraseña. */
    function setLongSessionCookie(): void {
        if (session_status() !== PHP_SESSION_NONE) return;
        session_set_cookie_params([
            'lifetime' => 60 * 60 * 24 * 30,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        @ini_set('session.gc_maxlifetime', (string)(60 * 60 * 24 * 30));
    }
}
