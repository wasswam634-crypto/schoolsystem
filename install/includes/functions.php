<?php

function redirect($page)
{
    header("Location: ".$page);
    exit;
}

function installed()
{
    return file_exists("../install/install.lock");
}

function create_lock()
{
    file_put_contents(
        "../install/install.lock",
        date("Y-m-d H:i:s")
    );
}

function progress($step)
{
    $steps=[
        1=>16,
        2=>32,
        3=>48,
        4=>64,
        5=>80,
        6=>100
    ];

    return $steps[$step];
}

function check_extension($name)
{
    return extension_loaded($name);
}

function writable($folder)
{
    return is_writable($folder);
}