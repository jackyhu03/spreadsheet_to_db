<?php
    $values = parse_url($_REQUEST["url"]);

    $path = explode('/', $values["path"]);

    $key = $path[3];

    echo $key;

?>