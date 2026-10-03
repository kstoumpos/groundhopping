/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './*.php',
    './template-parts/**/*.php',
  ],
  theme: {
    // extend (not override) as of the homepage redesign: the new design
    // leans on Tailwind's own zinc/purple/black/white, so we stopped
    // replacing the whole palette. Custom keys below are a MIX of the
    // current hazard-tape/concrete tokens (header, footer, homepage) and
    // a couple of leftover tokens still referenced on grounds / campaign
    // detail pages, not yet redesigned.
    extend: {
      colors: {
        'amf-red': '#d90429',
        'hazard-yellow': '#ffd60a',
        'spray-neon': '#00f5d4',
        'wfl-purple': '#a855f7',
        'concrete-dark': '#0a0b0d',
        'card-dark': '#121316',
        'paper-light': '#eaeae2',

        // Unused by any current template; kept for a future status-badge
        // pattern (e.g. a "live now" indicator distinct from amf-red).
        'status-live': '#ff2e7e',
        'status-postponed': '#8a8378',
      },
      fontFamily: {
        // Homepage / header / footer:
        display: ['"Chakra Petch"', 'sans-serif'],
        mono: ['"IBM Plex Mono"', 'monospace'],
        tag: ['"Permanent Marker"', 'cursive'],
        // Still used on grounds / campaign detail pages, not yet redesigned:
        label: ['Oswald', 'Arial Narrow', 'system-ui', 'sans-serif'],
        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
      },
      borderRadius: {
        none: '0px',
        sm: '4px',
        DEFAULT: '4px',
        full: '9999px',
      },
      fontSize: {
        // Original named type scale — still used on grounds / campaign
        // detail pages. The homepage now uses Tailwind's own text-xs …
        // text-6xl scale directly instead (see front-page.html), so
        // nothing new was added here for it.
        caption: ['12px', { lineHeight: '16px', fontWeight: '500' }],
        'body-sm': ['13px', { lineHeight: '18px' }],
        body: ['15px', { lineHeight: '22px' }],
        'body-lg': ['17px', { lineHeight: '26px' }],
        'label-sm': ['12px', { lineHeight: '14px', letterSpacing: '0.6px', fontWeight: '600' }],
        'label-lg': ['15px', { lineHeight: '18px', letterSpacing: '0.5px', fontWeight: '600' }],
        h3: ['20px', { lineHeight: '24px', fontWeight: '600' }],
        h2: ['28px', { lineHeight: '30px' }],
        h1: ['40px', { lineHeight: '40px' }],
        hero: ['56px', { lineHeight: '0.98', letterSpacing: '-0.5px' }],
      },
      keyframes: {
        marquee: {
          '0%': { transform: 'translateX(0%)' },
          '100%': { transform: 'translateX(-50%)' },
        },
      },
      animation: {
        // The ticker's track is rendered TWICE back to back (see
        // parts/header.html) and scrolled exactly -50%, so it loops with
        // no visible seam.
        marquee: 'marquee 28s linear infinite',
      },
    },
  },
  // Tailwind's preflight reset fights with WordPress core block CSS
  // (buttons, lists) in the block editor context; theme.json + this
  // theme's few core-block style overrides already handle that baseline,
  // so we turn preflight off rather than fight two resets.
  corePlugins: {
    preflight: false,
  },
  plugins: [],
};
