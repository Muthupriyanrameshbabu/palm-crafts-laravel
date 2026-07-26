/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
  ],
  theme: {
    extend: {
      colors: {
        // Named design tokens — see README-DESIGN.md for the rationale.
        'palm-cream': '#F1E9D8',      // background — sun-bleached palm fiber
        'palmyra-brown': '#3B2A1F',   // dark mode background / deep wood tone
        'kolam-red': '#A8432D',       // oxide-red rangoli pigment — sparing accent
        'brass': '#B08D3F',           // brass hardware — CTAs, dividers, hover states
        'woven-olive': '#6B7A4F',     // natural leaf-dye green — secondary accent
        'ink': '#211A14',             // primary text
      },
      fontFamily: {
        display: ['"Fraunces"', 'serif'],
        body: ['"Work Sans"', 'sans-serif'],
        mono: ['"IBM Plex Mono"', 'monospace'],
      },
    },
  },
  plugins: [],
};
