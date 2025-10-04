// tailwind.config.js (atau tailwind.config.cjs)
import defaultTheme from 'tailwindcss/defaultTheme'
import forms from '@tailwindcss/forms'

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',

    // Tambahkan ini ↓ supaya kelas di JS/TS/Vue ikut discan
    './resources/js/**/*.js',
    './resources/js/**/*.ts',
    './resources/**/*.vue',
    './resources/**/*.jsx',
    './resources/**/*.tsx',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Poppins', ...defaultTheme.fontFamily.sans],
      },
    },
  },
  plugins: [forms],
}
