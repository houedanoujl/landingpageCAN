import colors from 'tailwindcss/colors'

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    screens: {
      // Mobile-first breakpoints optimized for foldable devices
      'xs': '375px',      // Small phones
      'sm': '640px',      // Large phones / small tablets
      'md': '768px',      // Tablets / Galaxy Fold unfolded (653px)
      'lg': '1024px',     // Small laptops / landscape tablets
      'xl': '1280px',     // Desktops
      '2xl': '1536px',    // Large desktops
      
      // Custom breakpoints for foldable devices
      'fold': '653px',    // Galaxy Z Fold unfolded width
      'fold-sm': '280px', // Galaxy Z Fold folded width
    },
    extend: {
      fontFamily: {
        'montserrat': ['Montserrat', 'sans-serif'],
      },
      colors: {
        // Alias standard colors to enforce theme
        blue: colors.zinc,   // Remap Blue to Gray/Black
        orange: colors.yellow, // Remap Orange to Yellow

        brand: {
          dark: '#003399',
          yellow: '#FFD700',
          green: '#008000',
        },
        soboa: {
          blue: '#0058A3',
          'blue-dark': '#0054A1',
          'blue-light': '#3478B5',
          orange: '#F1862D',
          'orange-secondary': '#F18327',
          'orange-light': '#F4A05B',
          white: '#FFFFFF',
          cream: '#FEF7F1',
          'text-dark': '#0B1F33',
        },
      },
      spacing: {
        'section-sm': '2rem',
        'section-md': '3rem',
        'section-lg': '4rem',
        'section-xl': '6rem',
        'tap': '44px',
      },
      boxShadow: {
        'elev-1': '0 1px 2px rgba(11,31,51,0.06), 0 1px 1px rgba(11,31,51,0.04)',
        'elev-2': '0 4px 12px rgba(11,31,51,0.08), 0 2px 4px rgba(11,31,51,0.04)',
        'elev-3': '0 12px 32px rgba(11,31,51,0.14), 0 4px 8px rgba(11,31,51,0.06)',
        'elev-modal': '0 24px 64px rgba(11,31,51,0.24), 0 8px 16px rgba(11,31,51,0.08)',
        'soboa-orange': '0 0 40px rgba(241,134,45,0.35)',
        'soboa-blue': '0 0 40px rgba(0,88,163,0.35)',
      },
      transitionDuration: {
        'fast': '150ms',
        'base': '300ms',
        'slow': '500ms',
      },
      zIndex: {
        'base': '0',
        'dropdown': '20',
        'sticky': '40',
        'nav': '50',
        'modal-backdrop': '90',
        'modal': '100',
        'toast': '200',
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
