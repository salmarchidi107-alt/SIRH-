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
        'sans': ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
      colors: {
        primary: {
          50: '#e6f4f7',
          100: '#b3dde5',
          500: '#0f6b7c',
          600: '#0d5a68',
          700: '#094f5c',
        },
      },
      boxShadow: {
        'pro': '0 25px 50px -12px rgba(0, 0, 0, 0.25)',
        'pro-xl': '0 35px 60px -12px rgba(0, 0, 0, 0.25)',
      },
      animation: {
        'fade-in': 'fadeIn 0.5s ease-out',
        'slide-up': 'slideUp 0.3s ease-out',
      },
      keyframes: {
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        slideUp: {
          '0%': { transform: 'translateY(10px)', opacity: '0' },
          '100%': { transform: 'translateY(0)', opacity: '1' },
        },
      },
      borderRadius: {
        'pro': '20px',
        'pro-xl': '24px',
      }
    },
  },
  darkMode: 'class',
  plugins: [],
}

