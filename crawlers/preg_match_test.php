<?php

    $str = 'Link Online English sub - Clicks V�o Ads �? C� Phim Xem Nhanh Hon';

    preg_match('/Link Online (?P<name>\w+) (sub)?/', $str, $matches);

    print_r($matches);

?>
