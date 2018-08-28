<?php

/**** ALIO SHORTCODES AREA ****/

// Avito Block Shortcode

//setlocale(LC_ALL, 'ru_RU.UTF-8');

add_shortcode('alio_avito_block', 'alio_avito_block');
function alio_avito_block() {
    $avito_city_option = get_option('aam_avito_city');
    $avito_keys_option = get_option('aam_avito_keys');
    $out = '';

    $out .= '<div class="aam-block"><h2>Мониторинг Авито</h2>';
    $descr_text = ( $avito_city_option && $avito_keys_option ) ? 'Поиск объявлений в г. ' . $avito_city_option . ' по ключевым словам: ' . $avito_keys_option  : 'Город и ключевые слова поиска не заданы!';
    $out .= '<p class="aam-block-description">' . $descr_text . '</p>';
    $out .= '<a href="http://fly-journal/wp-admin/options-general.php?page=alio_ads_monitor_settings">Редактировать запрос</a>';
    if ( $avito_city_option && $avito_keys_option ) {
        $avito_data = get_check_avito( $avito_city_option, $avito_keys_option )
    }


    $url = 'https://www.avito.ru/omsk?s_trg=3&q=опал';
    $file = file_get_contents( $url );
    $avito_codes_arr = array();

        if ( $doc = phpQuery::newDocumentHTML( $file, 'utf-8' ) ) {

            foreach ( $doc->find('div.item.item_table') as $item_table ) {

                $item_table = pq( $item_table );
                $avito_item_id = $item_table->attr('id');
                $avito_codes_arr[$avito_item_id]["last_search_date"] = time(); // get format - date('d/m/Y H:i:s', time())
                $avito_codes_arr[$avito_item_id]["image"] = $item_table->find('a.large-picture')->html();
                $avito_codes_arr[$avito_item_id]["data"] = $item_table->find('div.item_table-header')->html();

                //error_log(print_R('$avito_codes_arr', true));
                //error_log(print_R($avito_codes_arr, true));

                /*$img = $table->find('img.img-rounded')->attr('src'); // ['img']
                $regexp = '/firepic/';
                preg_match( $regexp, $img, $matches, PREG_OFFSET_CAPTURE);
                $img = ( empty( $matches ) ) ? $img : get_stylesheet_directory_uri() . '/img/noimage.gif';

                $price;
                $colour;
                $size;
                $count;
                $comment;
                $author_go_lnk;
                $author_profile_lnk;
                $author_from;
                $sp_topic_lnk;
                $sp_org_lnk;
                foreach ( $table->find('div.table-pristr') as $tab ) {
                    $tab = pq($tab);
                    $price = $tab->find('div:last-child > div.row:eq(4) > div:last-child')->html(); // ['price']

                    $colour = $tab->find('div:last-child > div.row:eq(1) > div:last-child')->html(); // ['colour']
                    $size = $tab->find('div:last-child > div.row:eq(2) > div:last-child')->html(); // ['size']
                    $count = $tab->find('div:last-child > div.row:eq(3) > div:last-child')->html(); // ['count']

                    $comment = $tab->find('div:last-child > div.row:eq(5) > div:last-child')->html();
                    $comment = rtrim( ltrim( trim( str_replace( array( '\n', '  ', 'Комментарий продавцa' ), '', $comment ) ), '<br>' ), '<br>' ); // ['comment']
                    $author_go_lnk = 'http://forum.omskmama.ru/' . $tab->find('div:last-child > div.row:eq(6) > div:last-child > a:last-child')->remove()->attr('href'); // ['author_go_lnk']

                    $author_profile_href = 'http://forum.omskmama.ru/' . $tab->find('div:last-child > div.row:eq(6) > div:last-child > a')->attr('href');
                    $author_profile_lnk = $tab->find('div:last-child > div.row:eq(6) > div:last-child > a')->remove()->attr('href', $author_profile_href); // ['author_profile_lnk']

                    $author_from = trim( str_replace( ', Откуда:', '', $tab->find('div:last-child > div.row:eq(6) > div:last-child')->html() ) ); // ['author_from']

                    $sp_topic_href = 'http://forum.omskmama.ru/' . $tab->find('div:last-child > div.row:eq(7) > div:last-child > a')->attr('href');
                    $sp_topic_lnk = $tab->find('div:last-child > div.row:eq(7) > div:last-child > a')->attr('href', $sp_topic_href); // ['sp_topic_lnk']

                    $sp_org_href = 'http://forum.omskmama.ru/' . $tab->find('div:last-child > div.row:eq(8) > div:last-child > a')->attr('href');
                    $sp_org_lnk = $tab->find('div:last-child > div.row:eq(8) > div:last-child > a')->attr('href', $sp_org_href); // ['sp_org_lnk']

                }

                $thml = '';
                $thml .= '<div class="prstr-item col-sm-3"><div class="item">';
                $thml .= '<a href="#">
                    <div class="thumb-wrapper"><img src="' . $img . '" class="attachment-shop_catalog size-shop_catalog wp-post-image"></div>';
                $thml .= '<h3>' . $text . '</h3>
                        <ul class="short-description">
                            <li>' . $colour . '</li>
                            <li>' . $size . '</li>
                        </ul>
                    <span class="price"><span class="amount">' . $price . '</span></span>
                    </a>';
                $thml .= '</div></div><!-- prstr-item end -->';
                echo $thml;*/
            }
        }

    $out .= '</div>';
    return $out;
}

// DaruDar Block Shortcode

add_shortcode('alio_dd_block', 'alio_dd_block');
function alio_dd_block() {
    $out = '';

    return $out;
}

// OM Block Shortcode

add_shortcode('alio_om_block', 'alio_om_block');
function alio_om_block() {
    $out = '';

    return $out;
}



