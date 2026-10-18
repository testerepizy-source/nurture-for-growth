<?php

echo "OpenSSL: ";
var_dump(extension_loaded('openssl'));

echo "<br>allow_url_fopen: ";
var_dump(ini_get('allow_url_fopen'));