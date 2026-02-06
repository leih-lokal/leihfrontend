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
          50: "rgb(252 237 240 / <alpha-value>)",
          100: "rgb(251 219 224 / <alpha-value>)",
          200: "rgb(249 180 190 / <alpha-value>)",
          300: "rgb(249 143 155 / <alpha-value>)",
          400: "rgb(250 93 106 / <alpha-value>)",
          500: "rgb(242 12 13 / <alpha-value>)",
          600: "rgb(192 20 46 / <alpha-value>)",
          700: "rgb(145 17 46 / <alpha-value>)",
          800: "rgb(98 12 36 / <alpha-value>)",
          900: "rgb(59 6 23 / <alpha-value>)",
          950: "rgb(39 4 15 / <alpha-value>)"
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
