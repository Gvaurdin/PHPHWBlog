
<?php

    function getInput(string $prompt, string $errorMessage): string
    {
        echo $prompt;
        $input = trim(readline());

        if(empty($input)) {
            echo errorHandle($errorMessage);
        }

        return $input;
    }
