<?php
// app/config/parameters.php

$container->setParameter('database_name', $_SERVER['SYMFONY__DATABASE__NAME']);
$container->setParameter('database_host', $_SERVER['SYMFONY__DATABASE__HOST']);
$container->setParameter('database_user', $_SERVER['SYMFONY__DATABASE__USER']);
$container->setParameter('database_password', $_SERVER['SYMFONY__DATABASE__PASSWORD']);
