# Hakkında

Hepsiburada.com için php ile bot hazırladım. Dileyenler alıp kendileri de geliştirmeye devam edebilirler.

Şuan güzel bir şekilde mağazaları, ürünleri listelemektedir.

Kullanımı da oldukça basit, aşağıdan bakabilirsiniz.

# Kullanımı

```php
<?php

header('Content-type: text/html; charset=utf8');
require 'class.hepsimagaza.php';

// Mağazalar
print_r( HepsiBurada::MagazaListe() );

// Mağazalar Filtreleme
print_r( HepsiBurada::MagazaAlfabe() );

// Mağaza Ürünler
print_r( HepsiBurada::UrunListe('220V',2) );
// HepsiBurada::UrunListe('220V',2); // 220V mağazası 2. sayfa

// Mağaza Ürünler
print_r( HepsiBurada::Sayfala('220V') );
// HepsiBurada::Sayfala('220V'); // 220V Mağaza ürünleri sayfalama yapıyor

// Ürün Detay
print_r( HepsiBurada::Detay('sony-ps4-dualshock-kablosuz-kumanda-mavi-v2-sony-eurasia-p-HBV0000033DWA', '220V'));
```