/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./src/Frontend/views/**/*.php",
    "./Public/**/*.js",
  ],
  theme: {
    extend: {
      fontFamily: {
        poppins: ['Poppins', 'sans-serif'],
        montserrat: ['Montserrat', 'sans-serif'],
        mulish: ['mulish-variable', 'sans-serif'],
      },
      colors: { // Définition des couleurs personnalisées pour Tailwind
        // Ces noms peuvent être utilisés directement comme bg-primary-custom, text-text-primary-custom
        // Ils pointent vers les variables CSS que vous avez définies dans input.css
        'primary-custom': 'var(--color-primary)',
        'secondary-custom': 'var(--color-secondary)',
        'background-primary-custom': 'var(--color-background-primary)',
        'background-secondary-custom': 'var(--color-background-secondary)',
        'background-input-custom': 'var(--color-background-input)',
        'text-primary-custom': 'var(--color-text-primary)',
        'text-secondary-custom': 'var(--color-text-secondary)',
        'text-disabled-custom': 'var(--color-text-disabled)',
        'button-primary-custom': 'var(--color-button-primary)',
        'button-primary-hover-custom': 'var(--color-button-primary-hover)',
        'button-secondary-custom': 'var(--color-button-secondary)',
        'button-secondary-hover-custom': 'var(--color-button-secondary-hover)',
        'button-disabled-custom': 'var(--color-button-disabled)',
        'success-custom': 'var(--color-success)',
        'warning-custom': 'var(--color-warning)',
        'error-custom': 'var(--color-error)',
        'info-custom': 'var(--color-info)',
        'border-light-custom': 'var(--color-border-light)',
        'border-medium-custom': 'var(--color-border-medium)',
        'border-dark-custom': 'var(--color-border-dark)',
        'gradient-hover-custom': 'var(--color-gradient-hover)',
        'overlay-custom': 'var(--color-overlay)',
        'shadow-custom': 'var(--color-shadow)',
        'shadow-sm-custom': 'var(--color-shadow-sm)',
        'shadow-md-custom': 'var(--color-shadow-md)',
        'shadow-lg-custom': 'var(--color-shadow-lg)',
        'input-border-custom': 'var(--color-input-border)',
        'input-focus-custom': 'var(--color-input-focus)',
        'link-custom': 'var(--color-link)',
        'link-hover-custom': 'var(--color-link-hover)',
      },
      keyframes: {
        'fade-in': {
          '0%': { opacity: '0', transform: 'translateY(-20px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        'fade-in-up': {
          '0%': { opacity: '0', transform: 'translateY(20px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        'fade-in-down': {
          '0%': { opacity: '0', transform: 'translateY(-20px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        'fade-in-right': {
          '0%': { opacity: '0', transform: 'translateX(20px)' },
          '100%': { opacity: '1', transform: 'translateX(0)' },
        },
        'pulse-slow': {
          '0%, 100%': { opacity: '1' },
          '50%': { opacity: '0.7' },
        }
      },
      animation: {
        'fade-in': 'fade-in 0.5s ease-out forwards',
        'fade-in-up': 'fade-in-up 0.5s ease-out forwards',
        'fade-in-down': 'fade-in-down 0.5s ease-out forwards',
        'fade-in-right': 'fade-in-right 0.5s ease-out forwards',
        'pulse-slow': 'pulse-slow 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
      }
    },
  },
  plugins: [
    require('daisyui'),
  ],
  daisyui: {
    themes: [
      {
        mytheme: {
          // Définition des couleurs de base de DaisyUI en utilisant des valeurs HSL directes
          // qui correspondent à vos variables CSS.
          // C'est la méthode la plus fiable pour DaisyUI.
          // Vous pouvez trouver les valeurs HSL de vos couleurs ici :
          // #1A5E63 -> hsl(183, 58%, 25%)
          // #FFC857 -> hsl(40, 100%, 67%)
          // #F9FAFA -> hsl(0, 0%, 98%)
          // #F7F9FA -> hsl(0, 0%, 97%)
          // #ECF0F1 -> hsl(200, 10%, 93%)
          // #050E10 -> hsl(200, 10%, 4%)
          // #66BB6A -> hsl(123, 45%, 56%)
          // #FFC107 -> hsl(43, 100%, 51%)
          // #EF5350 -> hsl(1, 80%, 63%)
          // #64B5F6 -> hsl(208, 100%, 68%)

          "primary": "hsl(183, 58%, 25%)",
          "primary-focus": "hsl(183, 65%, 20%)",
          "primary-content": "white", // Texte sur la couleur primaire

          "secondary": "hsl(40, 100%, 67%)",
          "secondary-focus": "hsl(40, 100%, 60%)",
          "secondary-content": "black", // Texte sur la couleur secondaire

          "accent": "hsl(51, 100%, 50%)", // Or pur, si vous voulez qu'il soit différent de secondary
          "accent-focus": "hsl(51, 100%, 40%)",
          "accent-content": "black",

          "neutral": "hsl(200, 10%, 10%)", // Gris très foncé
          "neutral-focus": "hsl(200, 10%, 5%)",
          "neutral-content": "white",

          "base-100": "hsl(0, 0%, 98%)", // Fond clair principal
          "base-200": "hsl(0, 0%, 97%)", // Fond légèrement plus foncé
          "base-300": "hsl(200, 10%, 93%)", // Fond encore plus foncé (input)
          "base-content": "hsl(200, 10%, 4%)", // Texte sombre sur fond clair

          "info": "hsl(208, 100%, 68%)",
          "success": "hsl(123, 45%, 56%)",
          "warning": "hsl(43, 100%, 51%)",
          "error": "hsl(1, 80%, 63%)",
          "error-content": "white",
        },
      },
    ],
    darkTheme: "mytheme", // DaisyUI gérera le mode sombre via les variables CSS
    styled: true,
    utils: true,
    prefix: "",
    logs: true,
    themeRoot: ":root",
  },
};