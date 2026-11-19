<?php

namespace YourVendor\LaravelErd\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModelScanner
{
    protected array $modelTables = [];

    public function scan(): array
    {
        $modelsPath = app_path('Models');

        if (!File::isDirectory($modelsPath)) {
            return [];
        }

        $files = [];

        foreach (File::allFiles($modelsPath) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = File::get($file->getPathname());

            $namespace = $this->extractNamespace($content);
            $class = $this->extractClass($content);

            if (!$class) {
                continue;
            }

            $fqcn = $namespace
                ? $namespace . '\\' . $class
                : $class;

            $table = $this->extractTable($content);

            if (!$table) {
                $table = Str::snake(
                    Str::pluralStudly($class)
                );
            }

            $files[] = [
                'content' => $content,
                'path' => $file->getPathname(),
                'namespace' => $namespace,
                'class' => $class,
                'fqcn' => $fqcn,
                'table' => $table,
            ];
        }

        foreach ($files as $file) {
            $this->modelTables[$file['fqcn']] =
                $file['table'];
        }

        $models = [];

        foreach ($files as $file) {
            $model = $this->parseModel(
                $file['content'],
                $file['path'],
                $file['namespace'],
                $file['class'],
                $file['fqcn'],
                $file['table']
            );

            if ($model) {
                $models[] = $model;
            }
        }

        return $models;
    }

    protected function parseModel(
        string $content,
        string $path,
        ?string $namespace,
        string $class,
        string $fqcn,
        string $table
    ): array {
        $fillable = $this->extractArrayProperty(
            $content,
            'fillable'
        );

        $casts = $this->extractCasts(
            $content
        );

        $traits = $this->extractTraits(
            $content
        );

        $uses = $this->extractUses(
            $content
        );

        $relations = $this->extractRelations(
            $content,
            $namespace,
            $class,
            $uses
        );

        $relativePath = Str::after(
            $path,
            base_path() . DIRECTORY_SEPARATOR
        );

        return [
            'name' => $class,
            'class' => $fqcn,
            'file' => str_replace(
                DIRECTORY_SEPARATOR,
                '/',
                $relativePath
            ),
            'table' => $table,
            'traits' => $traits,
            'fillable' => $fillable,
            'casts' => $casts,
            'relations' => $relations,
            'status' => 'active',
        ];
    }

    protected function extractNamespace(
        string $content
    ): ?string {
        if (preg_match(
            '/namespace\s+([^;]+);/',
            $content,
            $matches
        )) {
            return trim($matches[1]);
        }

        return null;
    }

    protected function extractClass(
        string $content
    ): ?string {
        if (preg_match(
            '/\bclass\s+([A-Za-z_][A-Za-z0-9_]*)/',
            $content,
            $matches
        )) {
            return $matches[1];
        }

        return null;
    }

    protected function extractTable(
        string $content
    ): ?string {
        if (preg_match(
            '/(?:protected|private|public)\s+\$table\s*=\s*[\'"]([^\'"]+)[\'"]\s*;/',
            $content,
            $matches
        )) {
            return trim($matches[1]);
        }

        return null;
    }

    protected function extractUses(
        string $content
    ): array {
        preg_match_all(
            '/^\s*use\s+([^;]+);/m',
            $content,
            $matches
        );

        $uses = [];

        foreach ($matches[1] ?? [] as $use) {
            $use = trim($use);

            if (str_contains($use, ',')) {
                foreach (
                    preg_split('/\s*,\s*/', $use)
                    as $part
                ) {
                    $this->registerUse(
                        $uses,
                        $part
                    );
                }

                continue;
            }

            $this->registerUse(
                $uses,
                $use
            );
        }

        return $uses;
    }

    protected function registerUse(
        array &$uses,
        string $use
    ): void {
        $use = trim($use);

        if (!$use) {
            return;
        }

        $alias = null;

        if (preg_match(
            '/^(.+?)\s+as\s+([A-Za-z_][A-Za-z0-9_]*)$/i',
            $use,
            $matches
        )) {
            $use = trim($matches[1]);
            $alias = $matches[2];
        }

        $use = trim(
            $use,
            '\\ '
        );

        $alias ??=
            class_basename($use);

        $uses[$alias] = $use;
    }

    protected function extractArrayProperty(
        string $content,
        string $property
    ): array {
        if (!preg_match(
            '/(?:protected|private|public)\s+\$' .
            preg_quote($property, '/') .
            '\s*=\s*\[(.*?)\];/s',
            $content,
            $matches
        )) {
            return [];
        }

        preg_match_all(
            '/[\'"]([^\'"]+)[\'"]/',
            $matches[1],
            $values
        );

        return array_values(
            array_unique(
                $values[1] ?? []
            )
        );
    }

    protected function extractCasts(
        string $content
    ): array {
        if (!preg_match(
            '/(?:protected|private|public)\s+\$casts\s*=\s*\[(.*?)\];/s',
            $content,
            $matches
        )) {
            return [];
        }

        preg_match_all(
            '/[\'"]([^\'"]+)[\'"]\s*=>\s*[\'"]([^\'"]+)[\'"]/',
            $matches[1],
            $values,
            PREG_SET_ORDER
        );

        $casts = [];

        foreach ($values as $value) {
            $casts[$value[1]] =
                $value[2];
        }

        return $casts;
    }

    protected function extractTraits(
        string $content
    ): array {
        if (!preg_match_all(
            '/\buse\s+([^;]+);/',
            $content,
            $matches
        )) {
            return [];
        }

        $traits = [];

        foreach ($matches[1] as $value) {
            if (
                str_contains(
                    $value,
                    '::'
                )
            ) {
                continue;
            }

            foreach (
                preg_split(
                    '/\s*,\s*/',
                    $value
                ) as $trait
            ) {
                $trait = trim($trait);

                if (!$trait) {
                    continue;
                }

                $trait = preg_replace(
                    '/\s+as\s+.*$/',
                    '',
                    $trait
                );

                $traits[] =
                    trim(
                        $trait,
                        '\\ '
                    );
            }
        }

        return array_values(
            array_unique($traits)
        );
    }

    // protected function extractRelations(
    //     string $content,
    //     ?string $namespace,
    //     string $class,
    //     array $uses
    // ): array {
    //     $methods =
    //         $this->extractMethods(
    //             $content
    //         );

    //     $relationMethods = [
    //         'belongsTo',
    //         'hasOne',
    //         'hasMany',
    //         'belongsToMany',
    //         'hasOneThrough',
    //         'hasManyThrough',
    //         'morphOne',
    //         'morphMany',
    //         'morphTo',
    //         'morphToMany',
    //         'morphedByMany',
    //     ];

    //     $relations = [];

    //     foreach ($methods as $method) {
    //         $body = $method['body'];

    //         if (!preg_match(
    //             '/\$this\s*->\s*(' .
    //             implode(
    //                 '|',
    //                 $relationMethods
    //             ) .
    //             ')\s*\(/',
    //             $body,
    //             $match,
    //             PREG_OFFSET_CAPTURE
    //         )) {
    //             continue;
    //         }

    //         $relationType =
    //             $match[1][0];

    //         $methodOffset =
    //             $match[0][1];

    //         $openPosition =
    //             strpos(
    //                 $body,
    //                 '(',
    //                 $methodOffset
    //             );

    //         if (
    //             $openPosition === false
    //         ) {
    //             continue;
    //         }

    //         $argumentString =
    //             $this->extractBalanced(
    //                 $body,
    //                 $openPosition,
    //                 '(',
    //                 ')'
    //             );

    //         if (
    //             $argumentString === null
    //         ) {
    //             continue;
    //         }

    //         $arguments =
    //             $this->splitArguments(
    //                 $argumentString
    //             );

    //         $relatedClass =
    //             $this->resolveClass(
    //                 $arguments[0] ?? null,
    //                 $namespace,
    //                 $uses,
    //                 $class
    //             );

    //         if (!$relatedClass) {
    //             continue;
    //         }

    //         $relatedTable =
    //             $this->resolveModelTable(
    //                 $relatedClass
    //             );

    //         $relation =
    //             $this->buildRelation(
    //                 $method['name'],
    //                 $relationType,
    //                 $arguments,
    //                 $class,
    //                 $relatedClass,
    //                 $relatedTable
    //             );

    //         if ($relation) {
    //             $relations[] =
    //                 $relation;
    //         }
    //     }

    //     return $relations;
    // }

protected function extractRelations(
    string $content
): array {
    $namespace = $this->extractNamespace($content);
    $class = $this->extractClass($content);
    $uses = $this->extractUses($content);

    if (!$class) {
        return [];
    }

    $methods = $this->extractMethods($content);

    $relationMethods = [
        'hasOne',
        'hasMany',
        'hasOneThrough',
        'hasManyThrough',
        'belongsTo',
        'belongsToMany',
        'morphOne',
        'morphMany',
        'morphTo',
        'morphToMany',
        'morphedByMany',
    ];

    $relations = [];

    foreach ($methods as $method) {
        $body = $method['body'];

        foreach ($relationMethods as $relationMethod) {
            if (!preg_match(
                '/\$this\s*->\s*' .
                preg_quote($relationMethod, '/') .
                '\s*\(/',
                $body,
                $match,
                PREG_OFFSET_CAPTURE
            )) {
                continue;
            }

            $methodOffset = $match[0][1];

            $openPosition = strpos(
                $body,
                '(',
                $methodOffset
            );

            if ($openPosition === false) {
                continue;
            }

            $argumentString = $this->extractBalanced(
                $body,
                $openPosition,
                '(',
                ')'
            );

            if ($argumentString === null) {
                continue;
            }

            $arguments = $this->splitArguments(
                $argumentString
            );

            $relatedClass = $this->resolveClass(
                $arguments[0] ?? null,
                $namespace,
                $uses,
                $class
            );

            if (!$relatedClass) {
                continue;
            }

            $relatedTable = $this->resolveModelTable(
                $relatedClass
            );

            $foreignKey = null;
            $localKey = null;

            if ($relationMethod === 'belongsTo') {
                $foreignKey =
                    $this->argumentString(
                        $arguments[1] ?? null
                    ) ??
                    Str::snake($method['name']) . '_id';

                $localKey =
                    $this->argumentString(
                        $arguments[2] ?? null
                    ) ??
                    'id';
            } elseif (
                in_array(
                    $relationMethod,
                    [
                        'hasOne',
                        'hasMany',
                    ],
                    true
                )
            ) {
                $foreignKey =
                    $this->argumentString(
                        $arguments[1] ?? null
                    ) ??
                    Str::snake($class) . '_id';

                $localKey =
                    $this->argumentString(
                        $arguments[2] ?? null
                    ) ??
                    'id';
            } elseif (
                $relationMethod === 'belongsToMany'
            ) {
                $foreignKey =
                    $this->argumentString(
                        $arguments[2] ?? null
                    ) ??
                    Str::snake($class) . '_id';

                $localKey =
                    $this->argumentString(
                        $arguments[3] ?? null
                    ) ??
                    Str::snake($relatedClass) . '_id';
            }

            $relations[] = [
                'name' => $method['name'],
                'type' => $relationMethod,
                'related_class' => $relatedClass,
                'related_table' => $relatedTable,
                'foreign_key' => $foreignKey,
                'local_key' => $localKey,
            ];

            break;
        }
    }

    return $relations;
}

protected function parseRelationArguments(
    string $arguments
): array {
    $parts = [];
    $current = '';
    $quote = null;
    $depth = 0;

    for ($i = 0; $i < strlen($arguments); $i++) {
        $char = $arguments[$i];

        if (
            ($char === "'" || $char === '"') &&
            (
                $i === 0 ||
                $arguments[$i - 1] !== '\\'
            )
        ) {
            if ($quote === null) {
                $quote = $char;
            } elseif ($quote === $char) {
                $quote = null;
            }
        }

        if ($quote === null) {
            if ($char === '(') {
                $depth++;
            }

            if ($char === ')') {
                $depth--;
            }

            if ($char === ',' && $depth === 0) {
                $parts[] = trim($current);
                $current = '';

                continue;
            }
        }

        $current .= $char;
    }

    if (trim($current) !== '') {
        $parts[] = trim($current);
    }

    return array_map(
        function ($value) {
            $value = trim($value);

            $value = trim(
                $value,
                "'\" "
            );

            $value = preg_replace(
                '/::class$/',
                '',
                $value
            );

            return trim($value, '\\ ');
        },
        $parts
    );
}

protected function resolveRelatedClass(
    string $content,
    string $class
): string {
    $class = trim($class, '\\');

    if (str_contains($class, '\\')) {
        return $class;
    }

    preg_match_all(
        '/^use\s+([^;]+);/m',
        $content,
        $uses
    );

    foreach ($uses[1] ?? [] as $use) {
        $use = trim($use);

        $alias = null;

        if (preg_match(
            '/^(.+)\s+as\s+([A-Za-z_][A-Za-z0-9_]*)$/',
            $use,
            $match
        )) {
            $use = trim($match[1]);
            $alias = $match[2];
        }

        $importedClass =
            class_basename($use);

        if (
            $class === $importedClass ||
            $class === $alias
        ) {
            return trim($use, '\\');
        }
    }

    $namespace =
        $this->extractNamespace(
            $content
        );

    return $namespace
        ? $namespace . '\\' . $class
        : $class;
}

    protected function buildRelation(
        string $method,
        string $type,
        array $arguments,
        string $modelClass,
        string $relatedClass,
        string $relatedTable
    ): ?array {
        $type = strtolower($type);

        $modelTable =
            Str::snake(
                Str::pluralStudly(
                    $modelClass
                )
            );

        if (
            isset(
                $this->modelTables[
                    $modelClass
                ]
            )
        ) {
            $modelTable =
                $this->modelTables[
                    $modelClass
                ];
        }

        if ($type === 'belongsto') {
            return [
                'name' => $method,
                'type' => 'belongsTo',
                'related' => $relatedClass,
                'related_table' => $relatedTable,
                'foreign_key' =>
                    $this->argumentString(
                        $arguments[1] ??
                        null
                    ) ??
                    Str::snake($method) . '_id',
                'owner_key' =>
                    $this->argumentString(
                        $arguments[2] ??
                        null
                    ) ??
                    'id',
            ];
        }

        if (
            $type === 'hasmany' ||
            $type === 'hasone'
        ) {
            return [
                'name' => $method,
                'type' => $type,
                'related' => $relatedClass,
                'related_table' => $relatedTable,
                'foreign_key' =>
                    $this->argumentString(
                        $arguments[1] ??
                        null
                    ) ??
                    Str::snake(
                        $modelClass
                    ) . '_id',
                'owner_key' =>
                    $this->argumentString(
                        $arguments[2] ??
                        null
                    ) ??
                    'id',
            ];
        }

        if ($type === 'belongstomany') {
            $pivot =
                $this->argumentString(
                    $arguments[1] ??
                    null
                );

            return [
                'name' => $method,
                'type' => 'belongsToMany',
                'related' => $relatedClass,
                'related_table' => $relatedTable,
                'pivot_table' =>
                    $pivot ??
                    Str::snake(
                        Str::pluralStudly(
                            $modelClass
                        )
                    ),
                'foreign_key' =>
                    $this->argumentString(
                        $arguments[2] ??
                        null
                    ) ??
                    Str::snake(
                        $modelClass
                    ) . '_id',
                'related_key' =>
                    $this->argumentString(
                        $arguments[3] ??
                        null
                    ) ??
                    Str::snake(
                        $relatedClass
                    ) . '_id',
                'owner_key' => 'id',
            ];
        }

        return [
            'name' => $method,
            'type' => $type,
            'related' => $relatedClass,
            'related_table' => $relatedTable,
            'foreign_key' =>
                $this->argumentString(
                    $arguments[1] ??
                    null
                ),
            'owner_key' =>
                $this->argumentString(
                    $arguments[2] ??
                    null
                ) ?? 'id',
        ];
    }

    protected function extractMethods(
        string $content
    ): array {
        $tokens =
            token_get_all(
                $content
            );

        $methods = [];
        $count = count($tokens);

        for (
            $i = 0;
            $i < $count;
            $i++
        ) {
            if (
                !is_array($tokens[$i]) ||
                $tokens[$i][0] !== T_FUNCTION
            ) {
                continue;
            }

            $name = null;
            $openBrace = null;

            for (
                $j = $i + 1;
                $j < $count;
                $j++
            ) {
                $token = $tokens[$j];

                if (
                    is_array($token) &&
                    $token[0] === T_STRING
                ) {
                    $name =
                        $token[1];
                }

                if (
                    $token === '{'
                ) {
                    $openBrace =
                        $j;

                    break;
                }

                if (
                    $token === ';'
                ) {
                    break;
                }
            }

            if (
                !$name ||
                $openBrace === null
            ) {
                continue;
            }

            $depth = 1;
            $body = '';

            for (
                $j = $openBrace + 1;
                $j < $count;
                $j++
            ) {
                $token = $tokens[$j];

                if ($token === '{') {
                    $depth++;
                }

                if ($token === '}') {
                    $depth--;

                    if ($depth === 0) {
                        break;
                    }
                }

                $body .=
                    is_array($token)
                        ? $token[1]
                        : $token;
            }

            $methods[] = [
                'name' => $name,
                'body' => $body,
            ];
        }

        return $methods;
    }

    protected function resolveClass(
        ?string $expression,
        ?string $namespace,
        array $uses,
        string $currentClass
    ): ?string {
        if (!$expression) {
            return null;
        }

        $expression =
            trim($expression);

        if (
            !preg_match(
                '/^(.+?)::class$/',
                $expression,
                $matches
            )
        ) {
            return null;
        }

        $class =
            trim(
                $matches[1]
            );

        if ($class === 'self') {
            return $namespace
                ? $namespace . '\\' . $currentClass
                : $currentClass;
        }

        if ($class === 'static') {
            return $namespace
                ? $namespace . '\\' . $currentClass
                : $currentClass;
        }

        if (str_starts_with(
            $class,
            '\\'
        )) {
            return trim(
                $class,
                '\\'
            );
        }

        if (
            isset(
                $uses[$class]
            )
        ) {
            return $uses[$class];
        }

        if (
            str_contains(
                $class,
                '\\'
            )
        ) {
            return $class;
        }

        return $namespace
            ? $namespace . '\\' . $class
            : $class;
    }

    protected function resolveModelTable(
        string $class
    ): string {
        if (
            isset(
                $this->modelTables[$class]
            )
        ) {
            return $this->modelTables[$class];
        }

        return Str::snake(
            Str::pluralStudly(
                class_basename($class)
            )
        );
    }

    protected function argumentString(
        ?string $argument
    ): ?string {
        if (!$argument) {
            return null;
        }

        $argument =
            trim($argument);

        if (
            preg_match(
                '/^[\'"]([^\'"]+)[\'"]$/',
                $argument,
                $matches
            )
        ) {
            return $matches[1];
        }

        return null;
    }

    protected function extractBalanced(
        string $text,
        int $start,
        string $open,
        string $close
    ): ?string {
        $length =
            strlen($text);

        $depth = 0;
        $quote = null;

        for (
            $i = $start;
            $i < $length;
            $i++
        ) {
            $char =
                $text[$i];

            if (
                $quote !== null
            ) {
                if (
                    $char === '\\'
                ) {
                    $i++;

                    continue;
                }

                if (
                    $char === $quote
                ) {
                    $quote = null;
                }

                continue;
            }

            if (
                $char === "'" ||
                $char === '"'
            ) {
                $quote = $char;

                continue;
            }

            if (
                $char === $open
            ) {
                $depth++;
                continue;
            }

            if (
                $char === $close
            ) {
                $depth--;

                if ($depth === 0) {
                    return substr(
                        $text,
                        $start + 1,
                        $i - $start - 1
                    );
                }
            }
        }

        return null;
    }

    protected function splitArguments(
        string $arguments
    ): array {
        $result = [];
        $current = '';
        $depth = 0;
        $quote = null;
        $length =
            strlen($arguments);

        for (
            $i = 0;
            $i < $length;
            $i++
        ) {
            $char =
                $arguments[$i];

            if (
                $quote !== null
            ) {
                $current .= $char;

                if (
                    $char === '\\' &&
                    $i + 1 < $length
                ) {
                    $current .=
                        $arguments[++$i];

                    continue;
                }

                if (
                    $char === $quote
                ) {
                    $quote = null;
                }

                continue;
            }

            if (
                $char === "'" ||
                $char === '"'
            ) {
                $quote = $char;
                $current .= $char;

                continue;
            }

            if (
                $char === '(' ||
                $char === '[' ||
                $char === '{'
            ) {
                $depth++;
                $current .= $char;

                continue;
            }

            if (
                $char === ')' ||
                $char === ']' ||
                $char === '}'
            ) {
                $depth--;
                $current .= $char;

                continue;
            }

            if (
                $char === ',' &&
                $depth === 0
            ) {
                $result[] =
                    trim($current);

                $current = '';

                continue;
            }

            $current .= $char;
        }

        if (
            trim($current) !== ''
        ) {
            $result[] =
                trim($current);
        }

        return $result;
    }
}