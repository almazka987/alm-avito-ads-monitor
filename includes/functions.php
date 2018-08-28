<?php

// Clear spaces, lowercase helper
function aam_clear( $str ) {
    return trim( str_replace( ', ', ',', mb_strtolower( $str, 'UTF-8' ) ) );
}

// Transliterate cyrillic
function transliterate( $string ) {
    $roman = array("Sch","sch",'Yo','Zh','Kh','Ts','Ch','Sh','Yu','ya','yo','zh','kh','ts','ch','sh','yu','ya','A','B','V','G','D','E','Z','I','Y','K','L','M','N','O','P','R','S','T','U','F','','Y','','E','a','b','v','g','d','e','z','i','y','k','l','m','n','o','p','r','s','t','u','f','','y','','e');
    $cyrillic = array("Щ","щ",'Ё','Ж','Х','Ц','Ч','Ш','Ю','я','ё','ж','х','ц','ч','ш','ю','я','А','Б','В','Г','Д','Е','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Ь','Ы','Ъ','Э','а','б','в','г','д','е','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','ь','ы','ъ','э');
    return str_replace($cyrillic, $roman, $string);
}

//Get keys aray from the string option
function get_keys_array( $str ) {
    $k_array = explode( ',', aam_clear( $str ) );
    return $k_array;
}

// Check Avito
function get_check_avito( $city, $keys ) {

}



