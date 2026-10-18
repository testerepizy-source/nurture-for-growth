<?php
// Simple SVG icon helper for inline icons used in partials.
// Adds a function svg_icon($name, $size = 18, $attrs = '') that returns an inline SVG string.
// Uses stroke="currentColor" so icons inherit color from surrounding element.

if (!function_exists('svg_icon')) {
  function svg_icon(string $name, int $size = 18, string $attrs = ''): string {
    $s = (int)$size;
    switch ($name) {
      case 'lock':
        $svg = <<<SVG
<svg width="{$s}" height="{$s}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" {$attrs} aria-hidden="true" focusable="false">
  <path d="M17 8h-1V6a4 4 0 10-8 0v2H7a1 1 0 00-1 1v9a1 1 0 001 1h10a1 1 0 001-1v-9a1 1 0 00-1-1z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
SVG;
        return $svg;

      case 'chart':
        $svg = <<<SVG
<svg width="{$s}" height="{$s}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" {$attrs} aria-hidden="true" focusable="false">
  <path d="M3 3v18h18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M7 13l4-4 4 6 4-8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
SVG;
        return $svg;

      case 'hand-heart':
        $svg = <<<SVG
<svg width="{$s}" height="{$s}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" {$attrs} aria-hidden="true" focusable="false">
  <path d="M12 21s-4-2.2-6-5.2C3.8 12.4 4 10 4 10s1.6-4 8-4 8 4 8 4 .2 2.4-2 5.8C16 18.8 12 21 12 21z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
SVG;
        return $svg;

      case 'shield':
        $svg = <<<SVG
<svg width="{$s}" height="{$s}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" {$attrs} aria-hidden="true" focusable="false">
  <path d="M12 2l7 4v6c0 5-3.7 9.7-7 10-3.3-.3-7-5-7-10V6l7-4z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
SVG;
        return $svg;

      case 'calendar':
        $svg = <<<SVG
<svg width="{$s}" height="{$s}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" {$attrs} aria-hidden="true" focusable="false">
  <rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.4"/>
  <path d="M16 3v4M8 3v4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
</svg>
SVG;
        return $svg;

      case 'user-group':
        $svg = <<<SVG
<svg width="{$s}" height="{$s}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" {$attrs} aria-hidden="true" focusable="false">
  <path d="M17 21v-2a3 3 0 00-3-3H8a3 3 0 00-3 3v2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
  <circle cx="9.5" cy="8.5" r="2.5" stroke="currentColor" stroke-width="1.4"/>
  <path d="M20 8.5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" stroke="currentColor" stroke-width="1.4"/>
</svg>
SVG;
        return $svg;

      case 'user':
        $svg = <<<SVG
<svg width="{$s}" height="{$s}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" {$attrs} aria-hidden="true" focusable="false">
  <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
  <circle cx="12" cy="8" r="3" stroke="currentColor" stroke-width="1.4"/>
</svg>
SVG;
        return $svg;

      case 'eye':
        $svg = <<<SVG
<svg width="{$s}" height="{$s}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" {$attrs} aria-hidden="true" focusable="false">
  <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
  <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.4"/>
</svg>
SVG;
        return $svg;

      case 'book-open':
        $svg = <<<SVG
<svg width="{$s}" height="{$s}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" {$attrs} aria-hidden="true" focusable="false">
  <path d="M2 7.5A2.5 2.5 0 014.5 5H11v14H4.5A2.5 2.5 0 012 16.5v-9zM22 7.5A2.5 2.5 0 0019.5 5H13v14h6.5A2.5 2.5 0 0022 16.5v-9z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
SVG;
        return $svg;

      case 'phone':
        $svg = <<<SVG
<svg width="{$s}" height="{$s}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" {$attrs} aria-hidden="true" focusable="false">
  <path d="M22 16.92v3a2 2 0 01-2.18 2 19.86 19.86 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.86 19.86 0 01-3.07-8.63A2 2 0 014.09 2h3a2 2 0 012 1.72c.12.99.35 1.97.68 2.9a2 2 0 01-.45 2.11L9.91 10.91a16 16 0 006 6l1.18-1.18a2 2 0 012.11-.45c.93.33 1.91.56 2.9.68A2 2 0 0122 16.92z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
SVG;
        return $svg;

      case 'search':
        $svg = <<<SVG
<svg width="{$s}" height="{$s}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" {$attrs} aria-hidden="true" focusable="false">
  <circle cx="11" cy="11" r="6" stroke="currentColor" stroke-width="1.4"/>
  <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
</svg>
SVG;
        return $svg;

      case 'thumbtack':
        $svg = <<<SVG
<svg width="{$s}" height="{$s}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" {$attrs} aria-hidden="true" focusable="false">
  <path d="M4 21l1-4 9-9 3 3-9 9-4 1z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M14 7l3-3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
</svg>
SVG;
        return $svg;

      case 'menu':
        $svg = <<<SVG
<svg width="{$s}" height="{$s}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" {$attrs} aria-hidden="true" focusable="false">
  <path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
SVG;
        return $svg;

      case 'globe':
        $svg = <<<SVG
<svg width="{$s}" height="{$s}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" {$attrs} aria-hidden="true" focusable="false">
  <path d="M2 12a10 10 0 1020 0 10 10 0 00-20 0z" stroke="currentColor" stroke-width="1.2" fill="none"/>
  <path d="M2 12h20M12 2v20" stroke="currentColor" stroke-width="1.0" stroke-linecap="round"/>
</svg>
SVG;
        return $svg;

      case 'check-circle':
        $svg = <<<SVG
<svg width="{$s}" height="{$s}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" {$attrs} aria-hidden="true" focusable="false">
  <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.4"/>
  <path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
SVG;
        return $svg;

      // default placeholder icon (square)
      default:
        return "<svg width=\"{$s}\" height=\"{$s}\" viewBox=\"0 0 24 24\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\" {$attrs} aria-hidden=\"true\" focusable=\"false\"><rect x=\"3\" y=\"3\" width=\"18\" height=\"18\" rx=\"3\" stroke=\"currentColor\" stroke-width=\"1.4\"/></svg>";
    }
  }
}
