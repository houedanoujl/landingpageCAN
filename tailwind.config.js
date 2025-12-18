import colors from 'tailwindcss/colors'

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
        // Alias standard colors to enforce theme
        blue: colors.zinc,   // Remap Blue to Gray/Black
        orange: colors.yellow, // Remap Orange to Yellow

        brand: {
          dark: '#000000',
          yellow: '#FFD700',
          green: '#008000',
        },
        soboa: {
          blue: '#000000', // Pure Black
          'blue-dark': '#000000', // Black
          'blue-light': '#18181b', // Zinc-900
          orange: '#FFD700', // Gold/Yellow
          'orange-light': '#FDE047', // Yellow-300
          'orange-dark': '#EAB308', // Yellow-500
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
