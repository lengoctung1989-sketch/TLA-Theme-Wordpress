// P6.3 lint — cấu hình ESLint flat cho asset JS.
import js from '@eslint/js';
import globals from 'globals';
import prettier from 'eslint-config-prettier';

export default [
    { ignores: ['assets/dist/**', 'vendor/**', 'node_modules/**'] },
    js.configs.recommended,
    prettier,
    {
        files: ['assets/src/**/*.js', '*.js'],
        languageOptions: {
            ecmaVersion: 2022,
            sourceType: 'module',
            globals: { ...globals.browser, ...globals.node },
        },
        rules: {
            'no-unused-vars': ['error', { argsIgnorePattern: '^_' }],
        },
    },
];
