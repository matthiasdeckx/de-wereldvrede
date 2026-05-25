<?php

date_default_timezone_set('Europe/Brussels');

require __DIR__ . '/kirby/bootstrap.php';

echo (new Kirby)->render();
