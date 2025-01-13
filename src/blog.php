<?php

function addPost() : string
{
    try {
        //считываем заголовок
        $title = getInput("Введите заголовок поста:", "Заголовок не может быть пуст");
        $body = getInput("Введите текст поста:", "Пост не может быть пустым");

        //формируем структура поста
        $post = [
            'id' => uniqid(),
            'title' => $title,
            'body' => $body,
            'date' => date('Y-m-d H:i:s')
        ];

        //получаем путь к файлу поста
        $filePath = getPostFilePath($post['id']);
        echo $filePath . PHP_EOL;

        //пытаемся сохранить пост в файл, если метод записи возрвращает false выбрасываем исключение

        if (file_put_contents($filePath, json_encode($post, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
            errorHandle("Сбой при записи поста в файл. Не удалось сохранить пост в файл");
        }


        //после успешной записи файла начинаем работу с индексным файлом


        $fileIndexPath = getIndexFilePath();
        echo $fileIndexPath . PHP_EOL;

        if (!file_exists($fileIndexPath) || filesize($fileIndexPath) == 0) {
            // если файл не существует, создаем
            $fileIndex = fopen($fileIndexPath, 'w+'); // 'w' - открытие для записи
            if ($fileIndex === false) {
                errorHandle("Не удалось открыть индексный файл для записи.");
            }

            // Записываем заголовок
            fputcsv($fileIndex, ['id', 'title', 'file']);
            fclose($fileIndex);
        }

        // открываем csv файл для добавления новых записей

        $fileIndex = fopen($fileIndexPath, 'a');
        if(!$fileIndex) {
            errorHandle("Не удалось открыть индексный файл записи");
        }

        $postIndex = [
            'id' => $post['id'],
            'title' => $post['title'],
            'file' => $filePath
        ];

        //записываем данные в csv
        fputcsv($fileIndex, $postIndex);
        fclose($fileIndex);

        return "Пост успешно записан в файл" . PHP_EOL;

        //если файл индекса существует
    } catch (Exception $ex) {
        echo "Ошибка: " . $ex->getMessage() . PHP_EOL;
        return "не удалось сохранить пост в файл";
    }
}



function readAllPosts(): string
{
    try {
        //получаем путь к файлу индексов
        $fileIndexPath = getIndexFilePath();

        //проверяем, что индексный файл существует
        if(!file_exists($fileIndexPath) || filesize($fileIndexPath) == 0) {
            errorHandle("Индексный файл отсутствует или пустой: {$fileIndexPath}");
        }

        // открываем чтение файла индекса
        $fileIndex = fopen($fileIndexPath, 'r');
        if(!$fileIndex) {
            errorHandle("Не удалось открыть индексный файл: {$fileIndexPath}");
        }

        $posts =[];
        $header = true;

        //читаем файл построчно
        while (($row = fgetcsv($fileIndex)) !== false) {
            if($header) { // заголовок пропускаем
                $header = false;
                continue;
            }

            //формируем массив постов
            $posts[] = [
              'id' => $row[0],
              'title' => $row[1]
            ];
        }

        fclose($fileIndex);
        $output = "Список постов:" . PHP_EOL .
        "ID | Title" . PHP_EOL;

        foreach ($posts as $post) {
            $output .= $post['id'] . " | " . $post['title'] . PHP_EOL;
        }

        return $output;
    }catch (Exception $ex) {
        echo "Ошибка: " . $ex->getMessage() . PHP_EOL;
        return "Не удалось прочитать посты из индексного файла";
    }
}

function readPost($id) : string
{
    try {
        //получаем путь к индексному файлу
        $fileIndexPath = getIndexFilePath();
        if(!file_exists($fileIndexPath) || filesize($fileIndexPath) == 0) {
            errorHandle("Индексный файл отсутствует или пустой: {$fileIndexPath}");
        }

        $fileIndex = fopen($fileIndexPath, 'r');
        if(!$fileIndex) {
            errorHandle("Не удалось открыть индексный файл: {$fileIndexPath}");
        }

        $header = true;
        $post =[];
        $flag = false;
        while (($row = fgetcsv($fileIndex)) !== false) {
            if($header) { // заголовок пропускаем
                $header = false;
                continue;
            }
            if($id == $row[0]) {
                //получаем путь к файлу ин индексного файла
                $pathFile = $row[2];
                //проверяем файл поста
                if(!file_exists($pathFile)) {
                    errorHandle("Пост с ID {$id} не найден");
                }
                //читаем содержимое файла поста
                $post = json_decode(file_get_contents($pathFile), true);
                $flag = true;
                break;
            }
        }
        fclose($fileIndex);
        if($flag) {
            return "Найденный пост по ID {$id}:" . PHP_EOL .
                "ID : {$post['id']} | Title : {$post['title']}" . PHP_EOL .
                "Text : {$post['body']}" . PHP_EOL .
                "Date : {$post['date']}" . PHP_EOL;
        }

        return "не удалось найти пост по заданному ID {$id}" . PHP_EOL;

    }catch (Exception $ex) {
        echo $ex->getMessage() . PHP_EOL;
        return "не удалось прочитать пост по заданному ID";
    }
}

function searchPost(string $search) : string
{
    try {
        //получаем путь к директории с постами
        $dirPosts = getDirectoryPosts();

        if(!is_dir($dirPosts)) {
            errorHandle("Директория с постами не найдена: {$dirPosts}");
        }

        //получаем все файла с директории постов
        $files = glob($dirPosts . '/*.json');
        $results = [];
        //проходим по каждому файлу  ищем соотвествия с поисковым словом
        foreach ($files as $file) {
            //читаем содержимое файла
            $post = json_decode(file_get_contents($file), true);

            if(stripos($post['title'], $search) !== false ||
            stripos($post['body'], $search) !== false)  {
                $results[] = $post; // добавляем найденный пост в результаты
            }
        }

        if(empty($results)) {
            return errorHandle("Ничего не найдено по запросу: {$search}");
        }

        $output = "Результаты поиска:\n";
        foreach($results as $post) {
            $output .= "_ " . $post['title'] . " (ID: " . $post['id'] . ")\n";
        }
    }catch (Exception $ex) {
        echo $ex->getMessage() . PHP_EOL;
        return "Не удалось прочитать файл по поисковому слову";
    }
    return $output;
}

function deletePost($id) : string
{
    try {
        //получаем путь к индексному файлу
        $fileIndexPath = getIndexFilePath();
        if(!file_exists($fileIndexPath) || filesize($fileIndexPath) == 0) {
            return errorHandle("Индексный файл отсутствует или пустой: {$fileIndexPath}");
        }

        //открываем индексный файл для чтения
        $fileIndex = fopen($fileIndexPath, 'r');
        if(!$fileIndex) {
            return errorHandle("Не удалось открыть индексный файл: {$fileIndexPath}");
        }

        $header = true;
        $updatedIndex = []; // новый массив для хранения данных индекса
        $postFilePath = '';
        $postFound = false;

        //проходим по строкам индексного файла
        while (($row = fgetcsv($fileIndex)) !== false) {
            if($header) {
                $header = false;
                $updatedIndex[] = $row;
                continue;
            }

            if($id == $row[0]) {
                $postFilePath = $row[2];

                //проверка файла поста
                if(file_exists($postFilePath)) {
                    // удаляем пост из файла
                    unlink($postFilePath);
                } else {
                    return errorHandle("Пост с ID {$id} не найден в файле: {$postFilePath}");
                }

                $postFound = true;
            }else {
                $updatedIndex[] = $row;
            }

        }

        fclose($fileIndex);

        if(!$postFound) {
            return "Пост с ID {$id} не найден в индексном файле" . PHP_EOL;
        }

        // Записываем обновленный индексный файл
        $fileIndex = fopen($fileIndexPath, 'w');
        if (!$fileIndex) {
            return errorHandle("Не удалось открыть индексный файл для записи: {$fileIndexPath}");
        }

        foreach ($updatedIndex as $line) {
            fputcsv($fileIndex, $line); // записываем обновленные данные
        }

        fclose($fileIndex);

        return "Пост успешно удален";
    }catch (Exception $ex) {
        echo $ex->getMessage() . PHP_EOL;
        return "не удалось удалить пост";
    }
}

function clearPosts() : string
{
    try {
        $dirPosts = getDirectoryPosts();
        if(!is_dir($dirPosts)) {
            return errorHandle("Директория с постами не найдена: {$dirPosts}");
        }
        $files = glob($dirPosts . '/*.json');

        if(empty($files)) {
            return "Не было найдено постов для удаления" . PHP_EOL;
        }

        //счетчик удаленных постов
        $deletedPostsCount = 0;

        //чистим каждый пост(файл)
        foreach ($files as $file) {
            if(unlink($file)) {
                $deletedPostsCount++;
            }
        }

        $fileIndexPath = getIndexFilePath();
        if (file_put_contents($fileIndexPath, '') === false) {
            return errorHandle("Не удалось очистить индексный файл.");
        }

        return "Все посты были успешно удалены. Всего удалено постов : {$deletedPostsCount}" . PHP_EOL;
    }catch (Exception $ex) {
        echo $ex->getMessage() . PHP_EOL;
        return "Не удалось очистить данные";
    }
}