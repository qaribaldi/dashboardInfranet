/** @type {import('tailwindcss').Config} */
export default {
  content: [
    '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    '../../storage/framework/views/*.php',
    '../**/*.blade.php',
    '../**/*.js',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
