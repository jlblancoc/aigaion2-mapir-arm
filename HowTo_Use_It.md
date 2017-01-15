# Instrucciones para la configuración del listado de publicaciones en tu web.



Aigaion te permite no solo tener controlado y gestionar de forma eficiente un listado de publicaciones, si no el poder hacer peticiones configurables a la hora de generar un listado en la web. Por ejemplo las publicacioens de un autor en concreto, o de un topic, filtrar por años, etc. En este tutorial te explicamos las posibilidades que actualmente están implementadas.

Lo primero y fundamental es llamar dentro de un `<iframe>` al exportador php de aigaion con los parámetros deseados.
```html
 <p>
   <iframe src="<server_aigaion>/mapir_pub_export.php?mode=VALUE&idparm=VALUE&withlinks=VALUE&css=VALUE&formattype=VALUE&orderby=VALUE&maxyearsfromnow=VALUE" width="100%" height="1900" ></iframe>
</p>
```

Dicha llamada se descompone en:

1. `<server_aigaion>/mapir_pub_export.php?` --> Página que genera el listado en html (el exportador en si proporcionado en este repo).
2. Listado de parámetros, que se separan por (&):

* **mode** = byauthor/bytopic/byid	-->indica que tipo de listado queremos generar: las de un autor, las de un topic, o una publicación en concreto.

* **idparm** = XX -->el *ID* (sacado de Aigaion) del autor, del topic, o de la publicación que queremos listar.

* **withlinks** = 0/1--> indica si queremos que aparezca un enlace al *bibtex* en cada publicación (0=No, 1=Yes).

* **css** = aigaion_pubs_for_joomlawrapper_images.css --> Nombre de la plantilla de estilo que controla el aspecto visual de la página. Existen diversos formatos o puedes crear el tuyo personal.

* **formattype** = mapir_formatted_image_list/mapir_formatted_list --> Aquí indicamos si queremos o no que nos  muestre una pequeña imagen delante de cada publicación. Como veis se puede seguir usando el formato tradicional.

* **orderby** = type/year --> Permite establecer la forma de ordenar las  publicaciones listadas. *"type"* las ordenar primero por tipo de paper (Journal, conference, patents..)" y luego por año, mientras que "year" solo las organiza por año, mezclando todos los tipos de  publicaciones (muy útil para los que empiezan y tienen pocas publicaciones, aunque al ritmo de estos años se va a usar poco! jajaja).

* **maxyearsfromnow** = XX  --> Para el caso de tener muchas publicaciones, se puede listar solo las publicaciones más recientes. Este param permite definir el número máximo de años desde la fecha actual que quieres mostrar. Así, por ejemplo, para el caso de estar en el 2015 y maxyearsfromnow=2, mostrará las publicaciones de 2015, 2014 y 2013.
