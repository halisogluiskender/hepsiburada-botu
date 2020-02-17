<?php

/**
 * Class HepsiBurada
 * @author İskender Halisoğlu
 * @blog http://www.iskenderhalisoglu.com
 * @mail halisogluiskender@gmail.com
 * @date 17.2.2020
 */
class HepsiBurada
{

    static $data = array();

    /**
     * Tüm Mağazaları Listelemek İçin Kullanılır
     *
     * @param null $filter
     * @return array
     */



    static function MagazaListe($filter = NULL)
    {
        $filter = $filter ? "?filter=" . $filter : null;
        $hepsiMagaza = self::Curl('https://www.hepsiburada.com/magaza' . $filter);
        $search = array('/\>[^\S ]+/s', '/[^\S ]+\</s', '/(\s)+/s');
        $replace = array('>', '<', '\\1');
        $hepsiMagaza = preg_replace($search, $replace, $hepsiMagaza);

        // Mağazalar
        preg_match_all('@<a class="col lg-1 md-1 sm-1" href="(.*?)"> <span class="brand-name"> <span>(.*?)</span> </span> (.*?) </a>@', $hepsiMagaza, $magazaBox);
        self::$data['MAGAZA'][] = array(
            'URL' => $magazaBox[1],
            'TITLE' => $magazaBox[2]
        );


        return self::$data;
    }


    /**
     * Mağaza Alfabetik Filtreleme 
     *
     * @param null $filter
     * @return array
     */

    static function MagazaAlfabe($filter = NULL)
    {
        $filter = $filter ? "?filter=" . $filter : null;
        $hepsiMagaza = self::Curl('https://www.hepsiburada.com/magaza' . $filter);
        $search = array('/\>[^\S ]+/s', '/[^\S ]+\</s', '/(\s)+/s');
        $replace = array('>', '<', '\\1');
        $hepsiMagaza = preg_replace($search, $replace, $hepsiMagaza);

        // Alfabe Header
        preg_match_all('@<li class=\'(.*?)\'> <a href="(.*?)">(.*?)</a> </li>@', $hepsiMagaza, $alfabe);

        $data['ALFABE'] = array(
            'URL' => $alfabe[2],
            'TITLE' => $alfabe[3]
        );

        return $data;
    }


    /**
     * Mağazaya ait ürüler Listeleniyor
     * @param string $magaza
     * @param string $sayfala
     * @return array
     */

    static function UrunListe($magaza, $sayfa = null)
    {
        $items = array();
        $page = $sayfa ? '&sayfa=' . $sayfa : null;
        $hepsiMagaza = self::Curl("https://www.hepsiburada.com/magaza/" . $magaza . $page);
        // Mağaza Başlığı
        preg_match('@<h1 class="search-results-title">(.*?)</h1>@', $hepsiMagaza, $title);
        $items['magazaTitle'] = trim($title[1]);
        preg_match_all('@<div class="contain-lg-3 contain-md-3 contain-sm-3 fluid with-bottom-border">            (.*?)    </div></div>            @', $hepsiMagaza, $result);
        $UlListe = $result[1][0];
        preg_match_all('@<ul class="product-list results-container do-flex " data-bind=\'css: {grid: isGridSelected, list: isListSelected}\'>                ﻿    (.*?)            </ul>    @', $UlListe, $result1);
        $LiListe = $result1[1][0];
        preg_match_all('@<li(.*?)>(.*?)</li>@', $LiListe, $reulst3);
        $Urunler = $reulst3[2];
        foreach ($Urunler as $value) {
            preg_match_all('@<img src="(.*?)" (.*?)>@', $value, $image);
            preg_match_all('@<img (.*?) srcset="(.*?)"(.*?)>@', $value, $srcset);
            preg_match_all('@<h3 class="product-title title" title="(.*?)">                                        <div>                        <p class="hb-pl-cn">                            <span>(.*?)</span>                        </p>                    </div>                </h3>                                @', $value, $title);
            preg_match_all('@<a href="(.*?)" data-sku="(.*?)" (.*?)>(.*?)</a>@', $value, $url);
            preg_match_all('@<del class="(.*?)">(.*?)</del>@', $value, $old_price);
            preg_match_all('@                                    <span class="price(.*?)"(.*?)>(.*?)</span>                                @', $value, $price);
            $items['URUNLER'][] = array(
                'TITLE' => $title[1][0],
                'PICS' => $image[1][0],
                'SRCSET' => $srcset[2][0],
                'URL' => self::permalink($title[1][0]) . '-p-' . $url[2][0],
                'ID' => str_replace(array('/', '?'), array('', '&'), $url[2][0]),
                'OLD_PRICE' => $old_price[2][0],
                'PRICE' => $price[3][0]
            );
        }
        return $items;
    }


    /**
     * Ürün Sayfalama
     * @param string $magaza
     * @return array
     */
    static function Sayfala($magaza)
    {
        $pageNum = array();
        $hepsiMagaza = self::Curl("https://www.hepsiburada.com/magaza/" . $magaza);
        preg_match_all('@<div class="contain-lg-3 contain-md-3 contain-sm-3 fluid with-bottom-border">            (.*?)    </div></div>            @', $hepsiMagaza, $result);
        $UlListe = $result[1][0];

        preg_match_all('@    <div id="pagination" class="pagination">    <ul>            (.*?)    </ul>    </div>    @', $UlListe, $sayfalaUL);
        $sayfalaLi = $sayfalaUL[1][0];
        preg_match_all('@<li>                            <a href="(.*?)" class="(.*?)">(.*?)</a>            </li>@', $sayfalaLi, $sayfaNo);
        $pageNum['PAGINATION']['NUMBER'] = $sayfaNo[3];
        foreach ($sayfaNo[3] as $sNo) {
            $pageNum['PAGINATION']['URL'][] = '?magaza=' . $magaza . '&sayfa=' . $sNo;
        }

        return $pageNum;
    }

    /**
     * @param string $Link
     * @param string $magaza
     * @return array
     */

    static function Detay($Link, $magaza)
    {
        $Detay = self::Curl("https://www.hepsiburada.com/" . $Link . "?magaza=" . $magaza);

        $data['PRODUCT'] = array();
        preg_match_all('@<span class="variant-name">(.*?)</span>@', $Detay, $variant);
        $data['PRODUCT']['VARIANT'] = $variant[1];
        preg_match_all('@<img itemprop="image"                              class="product-image (.*?)"                              width="200"                              height="200"                              data-img="(.*?)"(.*?)/>@', $Detay, $BigPics);
        $data['PRODUCT']['BIG_PICS'] = $BigPics[2][0];
        preg_match_all('@<img itemprop="image"                              class="product-image (.*?)"                              width="200"                              height="200"                              data-src="(.*?)"(.*?)/>@', $Detay, $pics);
        $data['PRODUCT']['PICS_GAL'] = $pics[2];

        preg_match_all('@<div id="tabProductDesc" class="(.*?)">                    (.*?)                    <div>@', $Detay, $content);
        $data['CONTENT'][] = $content[2][0];
        return $data;
    }

    /**
     * @param $url
     * @param null $proxy
     * @return mixed
     */

    private function Curl($url, $proxy = NULL)
    {
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_ENCODING => "",
            CURLOPT_AUTOREFERER => true,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_SSL_VERIFYPEER => false
        );

        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        $err = curl_errno($ch);
        $errmsg = curl_error($ch);
        $header = curl_getinfo($ch);



        curl_close($ch);

        $header['errno'] = $err;
        $header['errmsg'] = $errmsg;
        $header['content'] = $content;

        return str_replace(array("\n", "\r", "\t"), NULL, $header['content']);
    }


    /**
     * @author Tayfun Erbilen
     * @blog http://www.erbilen.net
     * @mail tayfunerbilen@gmail.com
     * @param $string
     * @return mixed
     */
    /**
     * @param $str
     * @param array $options
     * @return string
     */
    static function permalink($str, $options = array())
    {
        $str = mb_convert_encoding((string) $str, 'UTF-8', mb_list_encodings());
        $defaults = array(
            'delimiter' => '-',
            'limit' => null,
            'lowercase' => true,
            'replacements' => array(),
            'transliterate' => true
        );
        $options = array_merge($defaults, $options);
        $char_map = array(
            // Latin
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O',
            'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH',
            'ß' => 'ss',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o',
            'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th',
            'ÿ' => 'y',
            // Latin symbols
            '©' => '(c)',
            // Greek
            'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8',
            'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',
            'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W',
            'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',
            'Ϋ' => 'Y',
            'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',
            'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',
            'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',
            'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',
            'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',
            // Turkish
            'Ş' => 'S', 'İ' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'Ğ' => 'G',
            'ş' => 's', 'ı' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'ğ' => 'g',
            // Russian
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
            'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
            'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
            'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
            'Я' => 'Ya',
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
            'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
            'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
            'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
            'я' => 'ya',
            // Ukrainian
            'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',
            'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',
            // Czech
            'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ů' => 'U',
            'Ž' => 'Z',
            'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
            'ž' => 'z',
            // Polish
            'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z',
            'Ż' => 'Z',
            'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
            'ż' => 'z',
            // Latvian
            'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i', 'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N',
            'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z',
            'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',
            'š' => 's', 'ū' => 'u', 'ž' => 'z'
        );
        $str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);
        if ($options['transliterate']) {
            $str = str_replace(array_keys($char_map), $char_map, $str);
        }
        $str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);
        $str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);
        $str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');
        $str = trim($str, $options['delimiter']);
        return $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
    }
}
