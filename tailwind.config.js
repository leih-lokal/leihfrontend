/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./site/**/*.php",
    "./site/**/*.js",
    "./content/**/*.txt",
    "./assets/**/*.js",
    "./assets/**/*.html",
  ],
  theme: {
    extend: {
      "colors": {
        "leihlokal": {
          50: "oklch(95.88% 0.017 3.77 / <alpha-value>)",
          100: "oklch(91.93% 0.036 7.2 / <alpha-value>)",
          200: "oklch(83.68% 0.081 9.38 / <alpha-value>)",
          300: "oklch(76.57% 0.129 13.33 / <alpha-value>)",
          400: "oklch(68.69% 0.191 18.91 / <alpha-value>)",
          500: "oklch(60.62% 0.245 28.83 / <alpha-value>)",
          600: "oklch(51.67% 0.199 22.07 / <alpha-value>)",
          700: "oklch(42.46% 0.159 16.56 / <alpha-value>)",
          800: "oklch(32.54% 0.118 11.76 / <alpha-value>)",
          900: "oklch(23.45% 0.082 7.29 / <alpha-value>)",
          950: "oklch(18.48% 0.061 4.87 / <alpha-value>)"
        }
      },
      fontFamily: {
        sans: ["Univers", "sans-serif"],
        mono: ['"IBM Plex Mono"', "monospace"],
      },
    },
  },
  plugins: [require("@tailwindcss/typography")],
};
