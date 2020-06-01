# Uložistě obrázků pro Nette Framework

Nastavení v **config.neon**
```neon
extensions:
    images: NAttreid\ImageStorage\DI\ImageStorageExtension
    
images:
    assetsPath: '%wwwDir%/../assets'
    noImage: 'default/default.png'
    publicDir: 'assets'
    quality: 85
    defaultFlag: 'fit'
    domain: '//domena/' # zobrazi url obrazku na jine domene
    timeout: 10
```

## Použití v presenteru

Do hlavního presenteru vložit
```php
class BasePresenter {
    use \NAttreid\ImageStorage\TraitImagePresenter;
}
```

Ukládání obrázků
```php
/* @var $fileUpload \Nette\Http\FileUpload */
$resource = $storage->createUploadedResource($fileUpload);

// nebo z cesty
/* @var $location string */
$resource = $storage->createResource($location);

// pridame namespace
$resource->setNamespace('namespace');

// ulozime
$storage->save($resource);

// zobrazime url adresu
echo $storage->link($result);

// $id pro ulozeni
$id = $resource->getIdentifier();
```

Získání obrázku
```php
$resource = $storage->createResource($id);
```

Přesouvání obrázků
```php
$resource = $storage->createResource($id);
$resource->setNamespace('jine/namespace');
$storage->save($resource);
```

Odstranění obrázku
```php
$storage->delete($id);
//nebo vice najednou
$ids=[....];
$storage->delete($ids);
```

## Šablony
Zobrazení obrázku
```latte
{img 'image.jpg'}
<img n:img="'image.jpg', '100x100', 'fill', 80">
```