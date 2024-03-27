<?php
class ScrapeLib {
    function getContent( $scrapeCommand, $htmlDomElement ) {
        switch( $scrapeCommand ) {
            case 'text':
                return $htmlDomElement->innertext();
            break;
            case (preg_match('/attribute_.*/', $scrapeCommand) ? true : false) :
                 list( , $name ) = explode("_", $scrapeCommand);
                return $htmlDomElement->getAttribute( $name );
            break;
        }
    }
}
?>