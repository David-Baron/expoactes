<?php
if (file_exists(dirname(__FILE__) . '/GoogleMapV3.php')) {
    include_once(dirname(__FILE__) . '/GoogleMapV3.php');
} else {
    include_once(dirname(__FILE__) . '/GoogleMapV3-2018.inc.php');
}
