<?php

function addPost() : string
{
    //считываем заголовок
    echo "Введите заголовок поста: ";
    $title = trim(readline());

    //проверка ввода заголовка
    if(empty($title)) {
        return errorHandle("Заголовок не может быть пуст");
    }

    //читаем тело поста
    echo "Введите текст поста:\n";
    $body = trim(readline());

    if(empty($body)) {
        return errorHandle("Текст поста не может быть пуст");
    }

    //формируем структура поста
    $post = [
      'id' => uniqid(),
      'title' => $title,
      'body' => $body,
      'date' => date('Y-m-d H:i:s')
    ];

    //сохраняем пост в файл
    $dbFile = __DIR__ . '/../db.txt';
    $dbData = file_exists($dbFile) ? json_decode(file_get_contents($dbFile), true) : [];
    $dbData[] = $post;

    //пишем обратно в файл
    file_put_contents($dbFile, json_encode($dbData, JSON_PRETTY_PRINT));

    return "Пост успешно добавлен";
}

function readAllPosts() : string
{
    $dbFile = __DIR__ . '/../db.txt';
    if(!file_exists($dbFile)) {
        return errorHandle("Базы постов не существует");
    }

    $dbData = json_decode(file_get_contents($dbFile), true);
    if(empty($dbData)) {
        return errorHandle("Нет доступных постов");
    }

    $output = "Список постов:\n";
    foreach($dbData as $post) {
        $output .= "_ " . $post['title'] . " (ID: " . $post['id'] . ")\n";
    }
    return $output;
}

function readPost($id) : string
{
    $dbFile = __DIR__ . '/../db.txt';
    if(!file_exists($dbFile)) {
        return errorHandle("Базы постов не существует");
    }

    $dbData = json_decode(file_get_contents($dbFile), true);
    foreach($dbData as $post) {
        if($post['id'] == $id) {
            return "Заголовок: " . $post['title'] . "\n" .
                "Дата: " . $post['date'] . "\n" .
                "Текст: " . $post['body'];
        }
    }

    return errorHandle("Пост с ID {$id} не найден");
}

function searchPost(string $search) : string
{
    $dbFile = __DIR__ . '/../db.txt';
    if(!file_exists($dbFile)) {
        return errorHandle("Базы постов не существует");
    }

    $dbData = json_decode(file_get_contents($dbFile), true);
    $results = [];

    foreach($dbData as $post) {
        if(stripos($post['title'], $search) !== false ||
            stripos($post['body'], $search) !== false)
        {
            $results[] = $post;
        }
    }

    if(empty($results)) {
        return errorHandle("Ничего не найдено по запросу: {$search}");
    }

    $output = "Результаты поиска:\n";
    foreach($results as $post) {
        $output = "_ " . $post['title'] . " (ID: " . $post['id'] . ")\n";
    }

    return $output;
}

function deletePost($id) : string
{
    $dbFile = __DIR__ . '/../db.txt';
    if(!file_exists($dbFile)) {
        return errorHandle("Базы постов не существует");
    }

    $dbData = json_decode(file_get_contents($dbFile), true);
    if(empty($dbData)) {
        return errorHandle("Нет доступных постов");
    }

    $dbData = json_decode(file_get_contents($dbFile), true);
    $postFound = false;
    foreach($dbData as $key => $post) {
        if($post['id'] === $id) {
            unset($dbData[$key]);
            $postFound = true;
            break;
        }
    }
    if(!$postFound) {
        return errorHandle("Пост с указанным id не найден");
    }
        file_put_contents($dbFile, json_encode($dbData, JSON_PRETTY_PRINT));
        return "Пост успешно удален";
}

function clearPosts() : string
{
    $dbFile = __DIR__ . '/../db.txt';
    if(file_exists($dbFile)) {
        file_put_contents($dbFile, json_encode([], JSON_PRETTY_PRINT));
        return "Все посты успешно удалены";
    }

    return errorHandle("База постов уже пустая");
}