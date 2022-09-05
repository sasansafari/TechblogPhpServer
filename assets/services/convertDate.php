<?php
require_once __DIR__ . "/../public/jdf.php";

function convertDateToJalali($date)
{
    $explode = explode(" ", $date);
    $mod = "/";
    $date = $explode[0];
    $time = $explode[1];
    @$explode = explode('-', $date);
    return jdate($time . " / " . @gregorian_to_jalali("$explode[0]", "$explode[1]", "$explode[2]", $mod));
}
function convertDateToJalali_full($date)
{
    $explode = explode(" ", $date);
    $mod = "/";
    $date = $explode[0];
    $time = $explode[1];
    @$explode = explode('-', $date);
    return jdate($time . " / " . @gregorian_to_jalali("$explode[0]", "$explode[1]", "$explode[2]", $mod));
}

function convertDateToJalali_date($date)
{
    $explode = explode(" ", $date);
    @$exploded_date = explode('-', $explode[0]);

    $mod = "/";
    return jdate(@gregorian_to_jalali("$exploded_date[0]", "$exploded_date[1]", "$exploded_date[2]", $mod));
}
