# Alfa 0.1.1

## Nuevas funciones y cambios

- Añadida la función de álbumes, ahora al darle click al título de una canción en el reproductor se mostrará el álbum al que pertenece. También se mostrará el álbum en la ventana de playlists y será clickable. Desde la página del álbum puedes añadirlo a una playlist y reseñarlo. (Se habilitará cuando se nos vaya el baneo de la API de Spotify)
- Añadida la función de importar pinceles a la app de dibujo.
- Subido el cupo para que se considere Melon Must 4.6 o más de nota media y 2 reseñas.
- Añadida función de escalado de texto en la app de temas.
- Añadida una función para evitar que se sigan mandando request a la API de Spotify durante el baneo y por consecuencia se extienda.
- Añadido guardado automático de temas.

## Bugs y mejoras

- Solucionado el bug que hacía que al hacer una actualización se borraran los datos de los perfiles de los usuarios.
- Mejorado el sistema de búsqueda de canciones importadas desde Spotify y otros sitios que no fueran YouTube.
- Solucionado el bug que hacía que las notificaciones se salieran de su ventana al resizearlas.
- Solucionado el bug que hacía que si el nombre de un usuario era muy largo no saliera el icono del chat en el apartado de Social.
- Mejorado el rendimiento de la app de dibujos en lienzos grandes.
- Solucionado un bug que hacía que al activar o guardar un tema se te pusiera de nuevo el tema default.
- Solucionado un bug que hacía que al tener 2 temas con el mismo nombre en interfaces distintas se sobrepusieran al cambiar de interfaz.
- Mejorado el rendimiento de las tabletas gráficas en la app de dibujo.
- Solucionado el bug que hacía que los perfiles de otros usuarios se pudieran mover demasiado arriba de la pantalla haciendo que no pudieras cerrarlos ni volverlos a mover.
- Solucionado el bug que hacía que las reseñas se salieran de su ventana al resizearlas.
- Solucionado el bug que pedía al usuario añadir un webhook de Discord en lugar de loguearse con su perfil en Galería.

---

# Alfa 0.1.2

## Nuevas funciones y cambios

- Añadida barra de volumen general de la Melon Hub.
- Añadida opción para poder deshabilitar las notificaciones de mensajes, reseñas de gente seguida y likes y comentarios. Se puede hacer desde la ventana de notificaciones.
- Añadido modo no molestar que se activa al deshabilitar las notificaciones de mensajes y se marca al usuario con un color rojo en su botón de conectado.
- Ahora la media reseñada que tengan la etiqueta de "Melon Must X" se muestran por delante del resto de reseñas.

## Bugs y mejoras

- Solucionado un bug que no actualizaba la app de perfil al seguir a alguien.
- Solucionado un bug que hacía que, al ser colaborador de una actividad en la app de perfil, solo se mostrara el host en esa actividad y no a los otros colaboradores invitados.
- Arreglado un bug que hacía que las ventanas de reseña se abrieran horizontalmente ocupando toda la pantalla.

---

# Alfa 0.1.3

## Nuevas funciones y cambios

- Las ventanas de los álbumes ahora son resizeables.
- Cambiada la API de la que se buscan los álbumes de la API de Spotify a la de iTunes y Deezer y dejada la de Spotify como último recurso cuando las 2 primeras fallan. Este cambio es para evitar problemas de baneos ya que la API de Spotify banea con una facilidad absurda en cuanto le haces unas pocas consultas.
- Cambiadas las instrucciones de la pantalla de descarga en móvil para hacerlas más fáciles de interpretar.
- Añadido un botón de eliminar cuenta en la ventana de ajustes de móvil.
- Cambiado el tema de la página de inicio de sesión en el móvil que se muestra al cerrar sesión para que tenga el mismo tema que la página de instalación.
- Añadido un buscador de GIFs que busca GIFs en GIPHY. Es accesible desde la pestaña de emojis.
- Añadida la capacidad de pasar y pinchar en links en el chat.
- Añadido que, al pasar el link de una imagen, salga la imagen de la que se ha pasado el link en el chat.
- Ahora al darle al botón de la campana con la ventana de notificaciones abierta, esta se cierra.
