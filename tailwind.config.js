/** ডেমোর ডিজাইন টোকেন হুবহু সংরক্ষিত (legacy-demo/index.html) */
module.exports = {
  darkMode: 'class',
  content: [
    './resources/views/**/*.blade.php',
    './app/**/*.php',
    './public/js/**/*.js',
  ],
  theme: {
    extend: {
      colors: {
        brand: {
          red: '#D50E18',
          ink: '#0B0B0B',
          leaf: '#C5E7C8',
          leafdeep: '#1F7A3D',
          paper: '#EAF7EC',
          dark: '#111827',
          light: '#F3F4F6',
        },
      },
      fontFamily: {
        sans: ['Inter', 'Noto Sans Bengali', 'sans-serif'],
        serif: ['Noto Serif Bengali', 'serif'],
      },
    },
  },
  plugins: [],
};
