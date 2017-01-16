# aigaion2-mapir-arm
Aigaion2 publication PHP site - mods for MAPIR / ARM groups

## Partes importantes

Exportadores:
* [mapir_formatted_list](https://github.com/jlblancoc/aigaion2-mapir-arm/blob/master/aigaionengine/views/export/mapir_formatted_list.php)
* [mapir_formatted_image_list](https://github.com/jlblancoc/aigaion2-mapir-arm/blob/master/aigaionengine/views/export/mapir_formatted_image_list.php)

Generadores de listados:
* [byauthor](https://github.com/jlblancoc/aigaion2-mapir-arm/blob/f5bddb5b252b9c7f9f8d3a61b9b54049d800f291/aigaionengine/controllers/export.php#L234)
* [bytopic](https://github.com/jlblancoc/aigaion2-mapir-arm/blob/f5bddb5b252b9c7f9f8d3a61b9b54049d800f291/aigaionengine/controllers/export.php#L161)

URL call: 
* [formato de la URL](https://github.com/jlblancoc/aigaion2-mapir-arm/blob/master/mapir_pub_export.php#L87)
* [How to call it](https://github.com/jlblancoc/aigaion2-mapir-arm/blob/master/HowTo_Use_It.md)

## Videotutoriales

* Parte 1: Introducción y uso básico

[![Aigaion2 videotutorial](http://img.youtube.com/vi/fh6b9UBsrI8/0.jpg)](http://www.youtube.com/watch?v=fh6b9UBsrI8 "Tutorial: Aigaion II (gestor listados de publicaciones online) - (Parte 1) ")

* Parte 2: Uso de topics para agrupar publicaciones

[![Aigaion2 videotutorial](http://img.youtube.com/vi/vblxmbGiP4A/0.jpg)](http://www.youtube.com/watch?v=vblxmbGiP4A "Tutorial: Aigaion II (gestor listados de publicaciones online) - (Parte 2) ")

* Parte 3: Estadísticas sobre cuartiles y terciles

(Write me!)



## Importar listado desde Python

        import urllib2
        url = self.arguments[0].strip()
        opener = urllib2.build_opener(urllib2.HTTPCookieProcessor())
        output_html = opener.open(url).read()
        # TODO: strip invalid chars!


