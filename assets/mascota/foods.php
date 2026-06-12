<?php
/* ──────────────────────────────────────────────
   MASCOTA — catálogo compartido de alimentos
   ──────────────────────────────────────────────
   Lista única consumida por:
     - assets/mascota/api.php (genera gustos al crear mascota,
       valida `alimento` en actionFeed, devuelve listado en
       actionGetFoods).
     - desktop-base.php (renderiza el grid del picker "Alimentar"
       en la ventana flotante).
     - apps/mascota.php (renderiza la pestaña "Gustos").

   Añadir comidas aquí cuando se quieran nuevas opciones — las
   mascotas EXISTENTES no las generarán hasta que su gusto se
   inicialice (se hace lazy al primer acceso). */

const MASCOTA_FOODS = [
    'hamburguesa' => ['nombre' => 'Hamburguesa', 'emoji' => '🍔'],
    'pizza'       => ['nombre' => 'Pizza',       'emoji' => '🍕'],
    'leche'       => ['nombre' => 'Leche',       'emoji' => '🥛'],
    'sushi'       => ['nombre' => 'Sushi',       'emoji' => '🍣'],
    'tacos'       => ['nombre' => 'Tacos',       'emoji' => '🌮'],
    'helado'      => ['nombre' => 'Helado',      'emoji' => '🍦'],
    'chocolate'   => ['nombre' => 'Chocolate',   'emoji' => '🍫'],
    'manzana'     => ['nombre' => 'Manzana',     'emoji' => '🍎'],
    'pasta'       => ['nombre' => 'Pasta',       'emoji' => '🍝'],
    'donut'       => ['nombre' => 'Donut',       'emoji' => '🍩'],
];
