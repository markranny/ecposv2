const defaultTheme = require('tailwindcss/defaultTheme');
const forms = require('@tailwindcss/forms');
const typography = require('@tailwindcss/typography');

module.exports = {
  darkMode: 'class',
  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './vendor/laravel/jetstream/**/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',
    './resources/js/**/*.vue',
  ],

  theme: {
    extend: {
      fontFamily: {
        sans: ['Figtree', ...defaultTheme.fontFamily.sans],
      },
      colors: {
        navy: '#000080',
        'custom-blue': '#000080', 
        'custom-blue-dark': '#000033',  
      },
      cleannavy: {
          800: '#000080', // Replace with your actual color
          900: '#000080', // Replace with your actual color
      },
    },
  },

  darkMode: 'class', 

  plugins: [forms, typography, require("daisyui")],
};
