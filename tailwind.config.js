import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [
        function({ addComponents, theme }) {
            addComponents({
                '.form-input': {
                    marginTop: theme('spacing.2'),
                    width: '100%',
                    padding: `${theme('spacing.2')} ${theme('spacing.3')}`,
                    color: theme('colors.gray.700'),
                    borderWidth: '1px',
                    borderRadius: theme('borderRadius.md'),
                    '&:focus': {
                        outline: 'none',
                        '--tw-ring-color': theme('colors.indigo.500'),
                        '--tw-ring-offset-shadow': `var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color)`,
                        '--tw-ring-shadow': `var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color)`,
                        boxShadow: `var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000)`,
                        borderColor: 'transparent',
                    },
                },
            })
        },
    ],
};

