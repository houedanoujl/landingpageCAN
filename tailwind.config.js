/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      fontFamily: {
        'montserrat': ['Montserrat', 'sans-serif'],
      },
      colors: {
        brand: {
          dark: '#1a1a1a',
          yellow: '#FFD700',
          green: '#008000',
        },
        soboa: {
          blue: '#121212', // Dark/Black background
          'blue-dark': '#000000', // Pure black
          'blue-light': '#2C2C2C', // Dark gray
          orange: '#FFD700', // Gazelle Yellow (Gold)
          'orange-light': '#FFE44D', // Lighter Yellow
          'orange-dark': '#CCAC00', // Darker Yellow
        },
      },
      animation: {
        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
        'float': 'float 6s ease-in-out infinite',
        'bounce-slow': 'bounce 2s infinite',
      },
      keyframes: {
        float: {
          '0%, 100%': { transform: 'translateY(0px)' },
          '50%': { transform: 'translateY(-20px)' },
        }
      }
    },
  },
  plugins: [],
}
