<?php

    $str = 'Link Online English sub - Clicks Vào Ads Ð? Có Phim Xem Nhanh Hon';

    preg_match('/Link Online (?P<name>\w+) (sub)?/', $str, $matches);

    print_r($matches);

?>
