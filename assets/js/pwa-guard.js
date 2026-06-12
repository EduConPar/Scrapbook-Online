/* ──────────────────────────────────────────────────────────────────────
   PWA GUARD — Enforcer client-side de "Melon Hub solo desde icono"
   ──────────────────────────────────────────────────────────────────────
   Si la página se está cargando en un dispositivo MÓVIL pero NO en
   modo standalone (es decir, dentro del navegador con barra de URL),
   redirigimos a la landing. Esto plug el agujero del cookie-sharing
   en Android, donde session.is_pwa server-side puede haberse colado
   al navegador normal.

   Desktop / tablet → return temprano, no toca nada.
   PWA standalone   → return temprano, deja seguir.
   Móvil + navegador → redirect a mobile-landing.php.

   Inline en <head> antes de cualquier otra cosa para evitar flash y
   evitar carrera con scripts de la app.
   ────────────────────────────────────────────────────────────────────── */
(function(){
    var ua = navigator.userAgent;
    /* Solo aplicamos a móviles. Tablets (iPad, Android no-Mobile) e
       PC pasan sin tocar nada. Mismo regex que isMobileDevice() server-side. */
    if (!/Mobi|Android.*Mobile|iPhone|iPod|BlackBerry|IEMobile|Opera Mini/.test(ua)) return;
    if (/iPad|Tablet/i.test(ua)) return;

    var standalone = (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches)
                  || window.navigator.standalone === true;
    if (standalone) return;

    /* Calculamos la URL de la landing relativa a /scrapbookOnline/ para
       que funcione llamado desde cualquier profundidad (apps/X.php o
       /mobile.php). */
    var path = window.location.pathname;
    var marker = '/scrapbookOnline/';
    var idx = path.indexOf(marker);
    var base = idx >= 0 ? path.slice(0, idx + marker.length) : marker;
    window.location.replace(base + 'mobile-landing.php');
})();
