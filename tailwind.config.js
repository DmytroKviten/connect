/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  theme: {
    extend: {},
  },
  plugins: [],
  
  safelist: [
    'bg-glass',
    'bg-accent',
    'text-accent',
    'hover:text-accent',
    'bg-glass/70',
    'bg-glass/40',
    'backdrop-blur',
    'backdrop-blur-md',
    'backdrop-blur-lg',
    // якщо треба ще якісь кастомні
  ],
  theme: {
    extend: {
      colors: {
        base:      '#0e0f11',
        surface:   '#1b1d22',
        glass:     'rgba(24,26,30,.4)',
        accent:    '#20e3b2',
        accentDark:'#129b80',
        muted:     '#9aa0ac',
      },
    },
  },
  plugins: [],
}
