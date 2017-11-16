<?php

function fileGetText($className)
{
    if ($className == 'File_Gettext_MO') {
        include_once __DIR__ . '/File/Gettext/MO.php';
    } else {
        if ($className == 'File_Gettext_PO') {
            include_once __DIR__ . '/File/Gettext/PO.php';
        } else {
            if ($className == 'File_Gettext') {
                include_once __DIR__ . '/File/Gettext.php';
            }
        }
    }
}

spl_autoload_register('fileGetText');