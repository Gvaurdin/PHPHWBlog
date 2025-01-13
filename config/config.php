<?php
/**
 * Возвращает значение из конфигурации.
 *
 * @param string $section Секция конфигурации.
 * @param string $key Ключ в секции.
 * @param string|null $default Значение по умолчанию, если ключ или секция отсутствуют.
 * @return string Значение из конфигурации или значение по умолчанию.
 * @errorHandle Если конфигурация недоступна.
 */

function getConfig(string $section, string $key, string $default = null) : string
{
    static $config = null;

    //загружаем конфигурацию только один раз
    if (is_null($config)) {
        $configPath = __DIR__ . '/config.ini';
        if (!file_exists($configPath)) {
            errorHandle("Файл конфигурации не найден: {$configPath}");
        }

        // INI_SCANNER_TYPED - режим типизированного сканирования файла .ini
        $config = parse_ini_file($configPath,true,INI_SCANNER_TYPED);
        if(!$config) {
            errorHandle("Не удалось загрузить файл конфигурации: {$configPath}");
        }
    }

    //проверяем наличие секции конф-ии
    if(!isset($config[$section])) {
        if($default !== null) {
            return $default;
        }
        errorHandle("Секция {$section} не найдена в конфигурации");
    }

    //проверяем наличие ключа
    if(!isset($config[$section][$key])) {
        if($default !== null) {
            return $default;
        }
        errorHandle("Ключ {$key} не найден в секции {$section}");
    }

    return $config[$section][$key];
}

/**
 * Формирует путь к файлу поста.
 *
 * @param string $postId ID поста.
 * @return string Полный путь к файлу поста.
 */
function getPostFilePath(string $postId): string
{
    /*
* получаем конфигурационные параметры из ini-файла:
* - 'posts_dir' (путь к директории, где хранятся файлы постов) из paths.
* - 'post_prefix' (префикс имени файла для постов) из file_naming.
* данные параметры позволяют гибко настроить расположение и именование файлов постов,
* не изменяя код программы.
*
* если конфигурационные параметры не будут найдены или произойдет ошибка при
* их чтении, будет выброшено исключение, которое мы обрабатываем в блоке try-catch.
*/
    $postsDir = getConfig('paths', 'posts_dir');
    $postPrefix = getConfig('file_naming', 'post_prefix');
    $postExtension = getConfig('file_naming', 'post_extension', 'json');

    if(!is_dir($postsDir)) {
        mkdir($postsDir, 0777, true);
    }


    /* Формируем полный путь к файлу для сохранения поста:
$postsDir — это директория, в которой будут храниться файлы постов (получено из конфигурации). DIRECTORY_SEPARATOR — это константа,
которая содержит разделитель пути, подходящий для текущей операционной системы. На Windows это будет '\\',
 на Unix-подобных системах (например, Linux или macOS) — '/'.
$postPrefix — это префикс для имени файла, полученный из конфигурации, который добавляется к имени каждого файла поста для удобства
(например, "post_" или "blog_").
*/
    return $postsDir . DIRECTORY_SEPARATOR . $postPrefix . $postId . '.' . $postExtension;
}

/**
 * Формирует путь к индексному файлу.
 *
 * @return string Полный путь к индексному файлу.
 */

function getIndexFilePath(): string
{
    $indexesDir = getConfig('paths', 'indexes_dir');
    $indexFile = getConfig('file_naming', 'index_file', 'indexes.csv');

    if(!is_dir($indexesDir)) {
        mkdir($indexesDir, 0777, true);
    }

    return $indexesDir . DIRECTORY_SEPARATOR  . $indexFile;
}

function getDirectoryPosts(): string
{
    return getConfig('paths', 'posts_dir');
}

function getDirectoryIndexes(): string
{
    return getConfig('paths', 'indexes_dir');
}
