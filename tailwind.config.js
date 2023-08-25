import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

export default {
    content: [
        "./resources/**/*.js",
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/**/*.blade.php",
        "./resources/**/*.vue",
    ],

    theme: {
        screens: {
            xs: "375px",
            ...defaultTheme.screens,
        },
        extend: {
            fontFamily: {
                sans: ["Nunito", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                "veryummy-primary": "#43A680",
                "veryummy-secondary": "#489CC1",
                "veryummy-ternary": "#F67E7D",
            },
        },
    },

    plugins: [forms],
};
