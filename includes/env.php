<?php
    // Loads KEY=value pairs from config/.env into getenv()/$_ENV. Existing environment variables win.
    function loadEnv($path = __DIR__ . '/../config/.env')
    {
        if (!is_readable($path)) {
            throw new RuntimeException("Env file not found: $path");
        }

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
                continue;
            }
            [$name, $value] = array_map('trim', explode('=', $line, 2));
            if (getenv($name) === false) {
                putenv("$name=$value");
                $_ENV[$name] = $value;
            }
        }
    }

    function env($name, $default = null)
    {
        $value = getenv($name);
        return ($value === false || $value === '') ? $default : $value;
    }

    loadEnv();
?>
