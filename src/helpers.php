<?php

function errorHandle(string $error) : string
{
    throw new InvalidArgumentException("\033[31m" . $error . "\r\n \033[97m");
}

function handleHelp(): string
{
    $help = <<<HELP
Доступные команды
help - вывод данной подсказки
add-post - создать новый пост
read-all-posts - прочитать все посты
read-post id - прочитать пост по айди
search-post searchWorld - найти пост по поисковому слову
delete-post id - удалить пост по айди
clear-posts - удалить все посты
HELP;
    return $help;

}
