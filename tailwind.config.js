/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './site/**/*.php',
    './site/**/*.js',
    './content/**/*.txt',
    './assets/**/*.js',
    './assets/**/*.html',
  ],
  theme: {
    extend: {
      colors: {
        'leihlokal': {
          50: '#fef2f2',
          100: '#fee2e2',
          200: '#fecaca',
          300: '#fca5a5',
          400: '#f87171',
          500: '#f04d53',
          600: '#dc2626',
          700: '#b91c1c',
          800: '#991b1b',
          900: '#7f1d1d',
          950: '#450a0a',
        },
      },
      fontFamily: {
        sans: ['Univers', 'sans-serif'],
        mono: ['"IBM Plex Mono"', 'monospace'],
      },
    },
  },
  plugins: [
    require('@tailwindcss/typography'),
  ],
}

