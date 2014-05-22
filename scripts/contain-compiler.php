<?php
if (file_exists(__DIR__ . '/../../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../../vendor/autoload.php';
} else {
    require_once __DIR__ . '/../vendor/autoload.php';
}

if (php_sapi_name() != 'cli') {
    fprintf(STDERR, "This script should be executed through the CLI.%s", PHP_EOL);
    exit(1);
}

if (empty($argv[1])) {
    fprintf(STDERR, "Syntax: %s <file>%s",
        $argv[0],
        PHP_EOL
    );
    exit(1);
}

for ($index = 1; $index < $argc; $index++) {
    $file = $argv[$index];

    if (!file_exists($file)) {
        fprintf(STDERR, "File '%s' does not exist.%s",
            $file,
            PHP_EOL
        );
        exit(1);
    }

    $result = false;

    if (is_file($file)) {
        if (compile_file($file)) {
            $result = true;
        }
    }

    if (is_dir($file)) {
        $iterator = new DirectoryIterator($file);

        foreach ($iterator as $item) {
            if (!$item->isFile() || $item->getExtension() != 'php') {
                continue;
            }

            if (compile_file($item->getPathname())) {
                $result = true;
            }
        }
    }
}

if (!$result) {
    printf("No classes found which extend Contain\Entity\Definition\AbstractDefinition -- nothing to do.%s%s",
        PHP_EOL,
        PHP_EOL
    );
    exit(0);
}

printf("%sAll done.%s%s", PHP_EOL, PHP_EOL, PHP_EOL);
exit(0);

function compile_file($file)
{
    require_once($file);

    if (!$definitions = get_definitions($file)) {
        return false;
    }

    $compiler = new Contain\Entity\Compiler\Compiler;

    try {
        foreach ($definitions as $definition) {
            printf("%-70s ... ", sprintf('Compiling %s', $definition));
            $compiler->compile($definition);
            printf("[ Ok ]\n");
        }
    } catch (Exception $e) {
        fprintf(STDERR, "[ Failed ]\nException: %s\n--\n%s\n\n", $e->getMessage(), $e->getTraceAsString());
        exit(1);
    }

    return true;
}

function get_definitions($file) 
{
    $php_code = file_get_contents($file);
    $classes  = array();
    $tokens   = token_get_all($php_code);
    $count    = count($tokens);
    $dlm      = false;

    for ($i = 2; $i < $count; $i++) {
        if ((isset($tokens[$i - 2][1]) && ($tokens[$i - 2][1] == "phpnamespace" || $tokens[$i - 2][1] == "namespace")) || 
            ($dlm && $tokens[$i - 1][0] == T_NS_SEPARATOR && $tokens[$i][0] == T_STRING)) { 

            if (!$dlm) {
                $namespace = 0; 
            }

            if (isset($tokens[$i][1])) {
                $namespace = $namespace ? $namespace . "\\" . $tokens[$i][1] : $tokens[$i][1];
                $dlm = true; 
            }   
        } elseif ($dlm && ($tokens[$i][0] != T_NS_SEPARATOR) && ($tokens[$i][0] != T_STRING)) {
            $dlm = false; 
        } 

        if (($tokens[$i - 2][0] == T_CLASS || (isset($tokens[$i - 2][1]) && $tokens[$i - 2][1] == "phpclass")) 
                && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING) {
            $class_name = $tokens[$i][1];
            if ($namespace) {
                $class_name = $namespace . '\\' . $class_name;
            }

            if (is_subclass_of($class_name, 'Contain\Entity\Definition\AbstractDefinition')) {
                $classes[]  = $class_name;
            }
        }
    }

    return $classes;
}
