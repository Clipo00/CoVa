/**
 * Landing Page JavaScript
 *
 * Terminal animation and scroll reveal effects for the CoVa landing page.
 * Uses Alpine.js (already loaded via app.js/Livewire).
 *
 * @see docs/LANDING_PLAN.md
 */

// The Alpine.js components are defined inline in landing.blade.php
// This file serves as the Vite entry point for the landing page.
// If additional non-Alpine JS is needed, add it here.

// Scroll reveal polyfill/helper for elements outside Alpine's scope
document.addEventListener('DOMContentLoaded', () => {
    // Ensure all .reveal elements that missed Alpine's directive still work
    if (!window.Alpine) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.reveal').forEach(el => {
            if (!el.classList.contains('revealed')) {
                observer.observe(el);
            }
        });
    }
});
