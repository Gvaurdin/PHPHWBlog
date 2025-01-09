<?php

function main(): string
{
    $command = parseCommand();
    if(in_array($command, ['readPost', 'searchPost','deletePost']) && isset($_SERVER['argv'][2])){
        $argument = $_SERVER['argv'][2];
        $result = function_exists($command) ? $command($argument) : errorHandle("Нет такой функции");
    } elseif(function_exists($command)) {
        $result = $command();
    } else {
        $result = errorHandle("Нет такой функции");
    }

    return $result;
}

function parseCommand(): string
{
    $functionName = 'handleHelp';
    if(isset($_SERVER['argv'][1])) {
        $functionName = match ($_SERVER['argv'][1]) {
            'add-post' => 'addPost',
            'read-all' => 'readAllPosts',
            'read-post' => 'readPost',
            'search-post' => 'searchPost',
            'delete-post' => 'deletePost',
            'clear-posts' => 'clearPosts',
            default => 'handleHelp',
        };
    }

    return $functionName;
}
