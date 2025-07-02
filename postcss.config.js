module.exports = {
  plugins: {
    'postcss-import': {}, // Doit être le premier plugin
    tailwindcss: {
      config: './tailwind.config.js' // S'assurer que le chemin de config est correct
    },
    autoprefixer: {},
    // Ajouter cssnano pour la minification en production
    ...(process.env.NODE_ENV === 'production' ? { cssnano: {} } : {})
  },
}