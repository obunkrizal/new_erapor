module.exports = {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
    './app/Filament/Resources/**/*.php',
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#1D4ED8', // Blue-700
          light: '#3B82F6',   // Blue-500
          dark: '#1E40AF',    // Blue-800
        },
        secondary: {
          DEFAULT: '#F59E0B', // Amber-500
          light: '#FBBF24',   // Amber-400
          dark: '#B45309',    // Amber-700
        },
        accent: {
          DEFAULT: '#10B981', // Emerald-500
          light: '#34D399',   // Emerald-400
          dark: '#047857',    // Emerald-700
        },
        neutral: {
          DEFAULT: '#374151', // Gray-700
          light: '#6B7280',   // Gray-500
          dark: '#111827',    // Gray-900
        },
      },
      fontFamily: {
        sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
    },
  },
  plugins: [],
};
