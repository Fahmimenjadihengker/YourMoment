#!/usr/bin/env php
<?php
// Bypass psysh issue with direct exec

$output = shell_exec('cd d:\laragon\www\YourMoment && php artisan migrate:fresh 2>&1');
echo $output;

$output2 = shell_exec('cd d:\laragon\www\YourMoment && php artisan db:seed --class=DatabaseSeeder 2>&1');
echo $output2;
