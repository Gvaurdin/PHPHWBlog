<?php

function main(): string
{
    $command = parseCommand();
    $argument = $_SERVER['argv'][2] ?? null;

    // проверяем, есть ли у фукнции аргументы
    if(empty($argument)) {
        $result = $command();
    } else {
        $result = $command($argument); // передаем аргументы как параметры
    }
    return $result;
}

function parseCommand(): string
{

    return match ($_SERVER['argv'][1] ?? null) {
        'add-post' => 'addPost',
        'read-all' => 'readAllPosts',
        'read-post' => 'readPost',
        'search-post' => 'searchPost',
        'delete-post' => 'deletePost',
        'clear-posts' => 'clearPosts',
        default => 'handleHelp'
    };

}
