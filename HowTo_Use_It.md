# Instrucciones para la configuración del listado de publicaciones en tu web.



Aigaion te permite no solo tener controlado y gestionar de forma eficiente un listado de publicaciones, si no el poder hacer peticiones configurables a la hora de generar un listado en la web. Por ejemplo las publicacioens de un autor en concreto, o de un topic, etc.

* Llamar en un <iframe> al exportador php de aigaion con los parámetros deseados.

 <p><iframe src="<server_aigaion>/mapir_pub_export.php?mode=byauthor&amp;idparm=71&amp;withlinks=1&amp;css=aigaion_pubs_for_joomlawrapper_images.css&amp;formattype=mapir_formatted_image_list&amp;orderby=type&amp;maxyearsfromnow=2"width="100%" height="1900">
</iframe></p>

* Dicha llamada se descompone en:

* <server_aigaion>/mapir_pub_export.php? --> Página que genera el listado en html (el exportador en si)
* Listado de parámetros, que se separan por (&):

mode=byauthor/bytopic/byid    		-->Indica que publicaciones queremos mostrar: las de un autor, las de un 
                                       topic, o una publicación en concreto.

idparm=XX                           -->El ID (sacado de Aigaion) del autor, del topic, o de la publicación 
									   que queremos listar.

withlinks=0/1                       -->Indica si queremos que aparezca el enlace al BIBTEX en cada 
									   publicación (0=No, 1=Yes)

css=aigaion_pubs_for_joomlawrapper_images.css  -->Nombre de la plantilla de estilo que controla el aspecto 
												visual de la página.
												(reemplaza al anterior aigaion_pubs_for_joomlawrapper.css)

formattype=mapir_formatted_image_list/mapir_formatted_list --> Aquí indicamos si queremos o no que nos 
												muestre una pequeña imagen delante de cada publicación.
												Como veis se puede seguir usando el formato tradicional!

orderby=type/year          -->Permite establecer la forma de ordenar las  publicaciones listadas. "type" 
							 las ordenar primero por "Journal, conference, patents..." y luego por año, 
							 mientras que "year" solo las organiza por año, mezclando todos los tipos de 
							 publicaciones (muy útil para los que empiezan y tienen pocas publicaciones, 
							 aunque al ritmo de este año se va a usar poco! jajaj).

maxyearsfromnow=2     -->Para el caso de listar solo las publicaciones más recientes. Permite definir el Nº 
						máximo de años desde la fecha actual que quieres mostrar. Así, por ejemplo, para el 
						caso de estar en el 2015 y maxyearsfromnow=2, mostrará las publicaciones de 2015, 
						2014 y 2013.
