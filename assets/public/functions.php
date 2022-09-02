<?php

function sanitize($data)
{
    $level1 = trim($data);
    $level2 = strip_tags($level1);
    return $level2;
}
