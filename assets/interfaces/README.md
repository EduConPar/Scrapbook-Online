# Sistema de Interfaces

Las **interfaces** son packs de CSS completos que cambian el look de toda la
app. El default es `win98` (estilo Windows 98 con biseles, fuente pixelada
y paleta gris). Cualquier carpeta dentro de `assets/interfaces/` se detecta
automáticamente como una interfaz disponible.

## Cómo crear una interfaz nueva

```
assets/interfaces/<nombre>/
├── style.css       (obligatorio)
├── meta.json       (obligatorio)
└── preview.png     (opcional — icono en Temas)
```

### 1. `style.css` — los overrides CSS

Este archivo se carga **después** de `assets/css/98.css`, `themes.css` y
`mobile-theme.css`, así que tus reglas ganan por orden de cascada. Para
sobreescribir estilos heredados de `98.css` puede que necesites `!important`
en algunas reglas.

Cubre típicamente:
- `body, button, input, textarea, select` → fuente base
- `.window` → forma de las ventanas (bordes, sombra, fondo)
- `.title-bar`, `.title-bar-text`, `.title-bar-controls button` → cabeceras
- `button, .button, input[type="submit"]` → botones
- `input[type="text|email|search|...]`, `textarea`, `select` → campos
- `::-webkit-scrollbar*` → scrollbars
- `.sunken-panel`, `.field-border` → paneles hundidos

Las CSS variables `--win-bg`, `--accent`, `--titlebar-start`, etc. están
disponibles globalmente (definidas en `assets/css/tokens.css`). Tus tokens
en `meta.json` SIRVEN como **defaults** y pueden ser personalizados por
el usuario desde la app de Temas (si esa parte está implementada).

### 2. `meta.json` — manifiesto

```json
{
  "name": "Win7",
  "label": "Windows 7 Aero",
  "description": "Estilo moderno con bordes redondeados, gradientes Aero.",
  "author": "tu-nombre",
  "isDefault": false,
  "tokens": {
    "winBg":         { "label": "Fondo de ventana",      "default": "#ece9d8" },
    "accent":        { "label": "Acento (selección)",    "default": "#3a9dde" },
    "titlebarStart": { "label": "Titlebar inicio",       "default": "#a4c2e3" },
    "titlebarEnd":   { "label": "Titlebar fin",          "default": "#7494bf" }
    // ... define los tokens de color que tu CSS use
  }
}
```

- `name`: identificador interno (sin espacios). Debe coincidir con el
  nombre de la carpeta.
- `label`: nombre amigable que se muestra en la app de Temas.
- `description`: texto del tooltip cuando el usuario pasa por encima.
- `isDefault`: si es `true`, esta interfaz se considera el "look base".
  Solo una interfaz debería tener `isDefault: true` (Win98).
- `tokens`: lista de variables de color que tu CSS usa. Cada token tiene
  `label` (qué nombre mostrar al usuario) y `default` (color por defecto).

### 3. `preview.png` (opcional)

Imagen que se muestra como miniatura en la app de Temas. Tamaño
recomendado: 64×64 px. Si no la incluyes, se usa un icono genérico.

## Cómo funciona técnicamente

1. **Detección**: `assets/php/active-interface.php` escanea esta carpeta
   con `scandir()` cada vez que se renderiza la app de Temas.

2. **Selección**: cuando el usuario hace click en una interfaz en Temas,
   `assets/js/interface-loader.js` guarda el nombre en una cookie
   (`activeInterface`) y recarga `window.top`.

3. **Carga**: en cada página (`desktop-base.php`, `mobile.php`, todas las
   apps standalone) se llama a `emitInterfaceCss($relPrefix)` que emite
   un `<link id="active-interface-link">` apuntando al `style.css` de la
   interfaz activa.

4. **Default**: si la cookie no existe o apunta a una carpeta que no
   existe, se usa `win98` como fallback.

## Limitaciones actuales

- Los **tokens de color** del `meta.json` son informativos por ahora.
  En el futuro se conectarán al editor de colores de Temas para que el
  usuario pueda customizar la paleta de cada interfaz.
- El sistema **no toca** los CSS específicos de apps (`perfil.css`,
  `reproductor.css`, etc.) — solo el chrome global (ventanas, botones,
  inputs, scrollbars). Si quieres modificar una vista de app, tu CSS
  tendrá que sobreescribir esos selectores específicos también.

## Ejemplos incluidos

- `win98/` — **MelonOS 98**, la interfaz por defecto. Look retro
  inspirado en Windows 98 con biseles 3D, fuente pixelada y paleta
  gris clásica. Sirve como template para crear las tuyas.
