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


## Importar listado desde Python

        import urllib2
        url = self.arguments[0].strip()
        opener = urllib2.build_opener(urllib2.HTTPCookieProcessor())
        output_html = opener.open(url).read()
        # TODO: strip invalid chars!


