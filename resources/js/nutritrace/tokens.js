// Reads design tokens from nutritrace.css at runtime, so the CSS stays the single source of truth for colors.
const styles = () => getComputedStyle(document.documentElement);

export function tokenColor(name, alpha = null) {
    const channels = styles().getPropertyValue(`--nt-${name}`).trim();

    return alpha === null ? `hsl(${channels})` : `hsl(${channels} / ${alpha})`;
}

export function tokenValue(name) {
    return styles().getPropertyValue(`--nt-${name}`).trim();
}

export const prefersReducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;
