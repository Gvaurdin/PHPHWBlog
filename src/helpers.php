<?php

function errorHandle(string $error) : string
{
    return "\033[31m" . $error . "\r\n \033[97m";
}

function handleHelp(): string
{
    $help = <<<HELP
Доступные команды
help - вывод данной подсказки
add - создать новый пост
HELP;
    return $help;

}
