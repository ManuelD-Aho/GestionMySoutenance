module.exports = {
  plugins: {
    'postcss-import': {}, // Doit être le premier plugin pour gérer les @import dans input.css
    tailwindcss: {},
    autoprefixer: {},
    // Ajouter cssnano pour la minification en production
    ...(process.env.NODE_ENV === 'production' ? { cssnano: {} } : {})
  },
}