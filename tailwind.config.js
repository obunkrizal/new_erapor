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
        // Modern gradient-based primary colors
        primary: {
          50: '#EFF6FF',
          100: '#DBEAFE',
          200: '#BFDBFE',
          300: '#93C5FD',
          400: '#60A5FA',
          500: '#3B82F6',   // Primary blue
          600: '#2563EB',
          700: '#1D4ED8',   // Primary dark
          800: '#1E40AF',
          900: '#1E3A8A',
          DEFAULT: '#2563EB', // Modern blue-600
          light: '#60A5FA',   // Blue-400
          dark: '#1E40AF',    // Blue-800
        },
        // Modern secondary colors (soft coral/peach)
        secondary: {
          50: '#FFF7ED',
          100: '#FFEDD5',
          200: '#FED7AA',
          300: '#FDBA74',
          400: '#FB923C',
          500: '#F97316',   // Modern orange
          600: '#EA580C',
          700: '#C2410C',
          800: '#9A3412',
          900: '#7C2D12',
          DEFAULT: '#FB923C', // Soft orange-400
          light: '#FDBA74',   // Orange-300
          dark: '#C2410C',    // Orange-700
        },
        // Modern accent colors (mint green/teal)
        accent: {
          50: '#F0FDF4',
          100: '#DCFCE7',
          200: '#BBF7D0',
          300: '#86EFAC',
          400: '#4ADE80',
          500: '#22C55E',   // Modern green
          600: '#16A34A',
          700: '#15803D',
          800: '#166534',
          900: '#14532D',
          DEFAULT: '#10B981', // Keep emerald for compatibility
          light: '#34D399',
          dark: '#047857',
        },
        // Modern neutral colors (soft grays)
        neutral: {
          50: '#F9FAFB',
          100: '#F3F4F6',
          200: '#E5E7EB',
          300: '#D1D5DB',
          400: '#9CA3AF',
          500: '#6B7280',
          600: '#4B5563',
          700: '#374151',
          800: '#1F2937',
          900: '#111827',
          DEFAULT: '#4B5563', // Gray-600 for better contrast
          light: '#9CA3AF',   // Gray-400
          dark: '#1F2937',    // Gray-800
        },
        // Additional modern colors
        success: {
          DEFAULT: '#10B981',
          light: '#34D399',
          dark: '#047857',
        },
        warning: {
          DEFAULT: '#F59E0B',
          light: '#FBBF24',
          dark: '#B45309',
        },
        danger: {
          DEFAULT: '#EF4444',
          light: '#F87171',
          dark: '#B91C1C',
        },
        info: {
          DEFAULT: '#3B82F6',
          light: '#60A5FA',
          dark: '#1D4ED8',
        },
      },
      fontFamily: {
        sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
      // Add modern gradient utilities
      backgroundImage: {
        'gradient-primary': 'linear-gradient(135deg, #3B82F6 0%, #1D4ED8 100%)',
        'gradient-secondary': 'linear-gradient(135deg, #FB923C 0%, #EA580C 100%)',
        'gradient-accent': 'linear-gradient(135deg, #10B981 0%, #047857 100%)',
      },
      // Add modern shadow effects
      boxShadow: {
        'soft': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
        'medium': '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
      },
    },
  },
  plugins: [],
};
