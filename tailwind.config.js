/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./src/Frontend/views/**/*.php", // Scanne tous vos fichiers de vue PHP
    "./Public/**/*.js", // Si vous avez du JS qui manipule des classes Tailwind
    // Ajoutez d'autres chemins si vous utilisez Tailwind dans d'autres types de fichiers
  ],
  theme: {
    extend: {
      fontFamily: {
        poppins: ['Poppins', 'sans-serif'],
        montserrat: ['Montserrat', 'sans-serif'],
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
          "primary": "#4A90E2", // Bleu vif
          "primary-focus": "#357ABD", // Bleu plus foncé au focus
          "primary-content": "#ffffff", // Texte blanc sur primaire
          "secondary": "#50C878", // Vert émeraude
          "secondary-focus": "#3BA05F",
          "secondary-content": "#ffffff",
          "accent": "#FFD700", // Or
          "accent-focus": "#E6C200",
          "accent-content": "#333333",
          "neutral": "#3D4451", // Gris foncé
          "neutral-focus": "#2A2E37",
          "neutral-content": "#ffffff",
          "base-100": "#ffffff", // Fond clair principal
          "base-200": "#F8F9FA", // Fond légèrement plus foncé
          "base-300": "#E9ECEF", // Fond encore plus foncé
          "base-content": "#333333", // Texte sombre sur fond clair
          "info": "#2094f3", // Bleu info
          "success": "#009485", // Vert succès
          "warning": "#ff9900", // Orange avertissement
          "error": "#ff5724", // Rouge erreur
          "error-content": "#ffffff", // Texte blanc sur erreur
        },
      },
      // Ajoutez d'autres thèmes si nécessaire
    ],
    darkTheme: "mytheme", // Thème par défaut pour le mode sombre
    styled: true,
    utils: true,
    prefix: "",
    logs: true,
    themeRoot: ":root",
  },
};