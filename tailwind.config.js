/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./app/Views/**/*.php",
    "./public/**/*.js"
  ],
  theme: {
    extend: {
      colors: {
        // Add your custom colors here if needed
      },
    },
  },
  plugins: [],
  darkMode: 'class', // or 'media' or 'class'
}

