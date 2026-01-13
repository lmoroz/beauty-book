<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/commands',
        __DIR__ . '/components',
        __DIR__ . '/config',
        __DIR__ . '/controllers',
        __DIR__ . '/migrations',
        __DIR__ . '/models',
        __DIR__ . '/modules',
    ])
    ->name('*.php');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(false)
    ->setRules([
        '@PSR12' => true,

        // Imports
        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'single_import_per_statement' => true,

        // Whitespace & formatting
        'array_syntax' => ['syntax' => 'short'],
        'no_trailing_whitespace' => true,
        'no_whitespace_in_blank_line' => true,
        'single_blank_line_at_eof' => true,
        'blank_line_after_opening_tag' => true,
        'no_blank_lines_after_class_opening' => true,
        'no_extra_blank_lines' => [
            'tokens' => [
                'extra',
                'use',
                'curly_brace_block',
                'parenthesis_brace_block',
            ],
        ],

        // Casting & types
        'cast_spaces' => ['space' => 'single'],
        'lowercase_cast' => true,
        'short_scalar_cast' => true,

        // Operators & spacing
        'binary_operator_spaces' => ['default' => 'single_space'],
        'concat_space' => ['spacing' => 'one'],
        'unary_operator_spaces' => true,
        'ternary_operator_spaces' => true,

        // Semicolons & commas
        'no_singleline_whitespace_before_semicolons' => true,
        'trailing_comma_in_multiline' => ['elements' => ['arrays']],

        // Strings
        'single_quote' => true,

        // Class & methods
        'class_attributes_separation' => [
            'elements' => ['method' => 'one'],
        ],
        'no_blank_lines_after_phpdoc' => true,
        'visibility_required' => [
            'elements' => ['property', 'method', 'const'],
        ],

        // Control structures
        'no_spaces_around_offset' => true,
        'trim_array_spaces' => true,

        // PHPDoc (light touch — keep @property for models)
        'phpdoc_trim' => true,
        'phpdoc_no_empty_return' => true,
        'phpdoc_single_line_var_spacing' => true,
    ])
    ->setFinder($finder);
