/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./src/Frontend/views/**/*.php",
    "./Public/**/*.html",
    "./Public/**/*.js"
  ],
  theme: {
    extend: {
      colors: {
        'primary-green': '#10b981',
        'primary-green-light': '#34d399',
        'primary-green-dark': '#059669',
        'primary-blue': '#3b82f6',
        'primary-blue-light': '#60a5fa',
        'primary-blue-dark': '#1d4ed8',
        'accent-violet': '#8b5cf6',
        'accent-yellow': '#f59e0b',
        'accent-red': '#ef4444'
      },
      fontFamily: {
        'inter': ['Inter', 'Segoe UI', 'Tahoma', 'Geneva', 'Verdana', 'sans-serif']
      },
      animation: {
        'slide-in': 'slideIn 0.4s ease-out',
        'fade-in': 'fadeIn 0.2s ease-out',
        'shake': 'shake 0.5s ease-in-out',
        'bounce-light': 'bounceLight 0.6s ease-out'
      },
      keyframes: {
        slideIn: {
          'from': { opacity: '0', transform: 'translateX(20px)' },
          'to': { opacity: '1', transform: 'translateX(0)' }
        },
        fadeIn: {
          'from': { opacity: '0' },
          'to': { opacity: '1' }
        },
        shake: {
          '0%, 100%': { transform: 'translateX(0)' },
          '10%, 30%, 50%, 70%, 90%': { transform: 'translateX(-5px)' },
          '20%, 40%, 60%, 80%': { transform: 'translateX(5px)' }
        },
        bounceLight: {
          '0%, 20%, 50%, 80%, 100%': { transform: 'translateY(0)' },
          '40%': { transform: 'translateY(-10px)' },
          '60%': { transform: 'translateY(-5px)' }
        }
      }
    },
  },
  plugins: [
    require('daisyui')
  ],
  daisyui: {
    themes: [
      {
        academictheme: {
          "primary": "#3b82f6",
          "secondary": "#10b981",
          "accent": "#8b5cf6",
          "neutral": "#374151",
          "base-100": "#ffffff",
          "info": "#3b82f6",
          "success": "#10b981",
          "warning": "#f59e0b",
          "error": "#ef4444",
        }
      }
    ],
    base: true,
    styled: true,
    utils: true,
    logs: false,
  }
}