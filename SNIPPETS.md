# 🔧 Snippets Úteis - Otimizações Mobile

## CSS Snippets

### 1. Responsive Grid Template
```css
.grid-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(min(300px, 100%), 1fr));
    gap: 1.5rem;
}
```

### 2. Fluid Typography
```css
h1 {
    font-size: clamp(1.5rem, 5vw, 2.5rem);
}

p {
    font-size: clamp(0.875rem, 2vw, 1rem);
}
```

### 3. Container Query (Futuro)
```css
@container (min-width: 400px) {
    .card {
        display: grid;
        grid-template-columns: 1fr 2fr;
    }
}
```

### 4. Aspect Ratio Box
```css
.aspect-ratio-box {
    aspect-ratio: 16 / 9;
    width: 100%;
}
```

### 5. Sticky Footer
```css
body {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

main {
    flex: 1;
}
```

### 6. Smooth Scroll with Offset
```css
html {
    scroll-behavior: smooth;
    scroll-padding-top: 80px; /* altura do header */
}
```

### 7. Better Focus Outline
```css
*:focus-visible {
    outline: 3px solid var(--primary-color);
    outline-offset: 2px;
    border-radius: 4px;
}
```

### 8. Truncate Text
```css
.truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.truncate-multiline {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
```

### 9. Card Hover Effect
```css
.card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
}

@media (hover: none) {
    .card:hover {
        transform: none;
    }
}
```

### 10. Loading Skeleton
```css
.skeleton {
    background: linear-gradient(
        90deg,
        #f0f0f0 25%,
        #e0e0e0 50%,
        #f0f0f0 75%
    );
    background-size: 200% 100%;
    animation: loading 1.5s ease-in-out infinite;
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
```

## JavaScript Snippets

### 1. Detect Mobile Device
```javascript
const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

// Ou mais robusto:
const isMobileDevice = () => {
    return (typeof window.orientation !== "undefined") 
        || (navigator.userAgent.indexOf('IEMobile') !== -1);
};
```

### 2. Detect Touch Support
```javascript
const isTouchDevice = () => {
    return (('ontouchstart' in window) ||
        (navigator.maxTouchPoints > 0) ||
        (navigator.msMaxTouchPoints > 0));
};
```

### 3. Viewport Width/Height
```javascript
const getViewportSize = () => {
    return {
        width: Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0),
        height: Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0)
    };
};
```

### 4. Debounce Function
```javascript
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Uso:
window.addEventListener('resize', debounce(() => {
    console.log('Window resized');
}, 250));
```

### 5. Throttle Function
```javascript
function throttle(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Uso:
window.addEventListener('scroll', throttle(() => {
    console.log('Scrolling');
}, 100));
```

### 6. Intersection Observer (Lazy Loading)
```javascript
const observerOptions = {
    root: null,
    rootMargin: '0px',
    threshold: 0.1
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            // Elemento está visível
            entry.target.classList.add('visible');
            observer.unobserve(entry.target);
        }
    });
}, observerOptions);

// Observar elementos
document.querySelectorAll('.lazy-load').forEach(el => {
    observer.observe(el);
});
```

### 7. Smooth Scroll to Element
```javascript
function scrollToElement(elementId, offset = 0) {
    const element = document.getElementById(elementId);
    if (element) {
        const y = element.getBoundingClientRect().top + window.pageYOffset - offset;
        window.scrollTo({ top: y, behavior: 'smooth' });
    }
}
```

### 8. Lock Body Scroll (Modal)
```javascript
function lockScroll() {
    document.body.style.overflow = 'hidden';
    document.body.style.paddingRight = `${window.innerWidth - document.documentElement.clientWidth}px`;
}

function unlockScroll() {
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
}
```

### 9. Copy to Clipboard
```javascript
async function copyToClipboard(text) {
    try {
        await navigator.clipboard.writeText(text);
        console.log('Copied to clipboard');
    } catch (err) {
        console.error('Failed to copy:', err);
    }
}
```

### 10. Detect Network Status
```javascript
function checkNetworkStatus() {
    if (navigator.onLine) {
        console.log('Online');
    } else {
        console.log('Offline');
    }
}

window.addEventListener('online', () => console.log('Back online'));
window.addEventListener('offline', () => console.log('Connection lost'));
```

## HTML Snippets

### 1. Responsive Image
```html
<picture>
    <source media="(min-width: 768px)" srcset="image-large.jpg">
    <source media="(min-width: 480px)" srcset="image-medium.jpg">
    <img src="image-small.jpg" alt="Description" loading="lazy">
</picture>
```

### 2. Responsive Video
```html
<div style="position: relative; padding-bottom: 56.25%; height: 0;">
    <iframe 
        src="video-url" 
        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"
        frameborder="0" 
        allowfullscreen>
    </iframe>
</div>
```

### 3. Touch-Friendly Button
```html
<button 
    type="button"
    style="min-height: 44px; min-width: 44px; padding: 0.75rem 1.5rem;"
    aria-label="Descriptive label">
    Click Me
</button>
```

### 4. Accessible Form Input
```html
<div class="form-group">
    <label for="email">Email</label>
    <input 
        type="email" 
        id="email" 
        name="email"
        autocomplete="email"
        inputmode="email"
        required
        aria-required="true"
        aria-describedby="email-error">
    <span id="email-error" class="error-message" role="alert"></span>
</div>
```

### 5. Loading Spinner
```html
<div class="loading-spinner" role="status" aria-label="Loading">
    <span class="sr-only">Loading...</span>
</div>
```

## Media Query Snippets

### 1. Mobile First Approach
```css
/* Base styles (mobile) */
.element {
    font-size: 14px;
}

/* Tablet and up */
@media (min-width: 768px) {
    .element {
        font-size: 16px;
    }
}

/* Desktop and up */
@media (min-width: 1024px) {
    .element {
        font-size: 18px;
    }
}
```

### 2. Orientation Queries
```css
@media (orientation: portrait) {
    .element {
        /* Portrait styles */
    }
}

@media (orientation: landscape) {
    .element {
        /* Landscape styles */
    }
}
```

### 3. Touch Device Query
```css
@media (hover: none) and (pointer: coarse) {
    /* Touch device styles */
    button {
        min-height: 44px;
    }
}
```

### 4. High DPI Screens
```css
@media (-webkit-min-device-pixel-ratio: 2),
       (min-resolution: 192dpi) {
    /* Retina/High DPI styles */
    .logo {
        background-image: url('logo@2x.png');
    }
}
```

### 5. Dark Mode
```css
@media (prefers-color-scheme: dark) {
    :root {
        --bg-color: #1a1a1a;
        --text-color: #e0e0e0;
    }
}
```

### 6. Reduced Motion
```css
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        transition-duration: 0.01ms !important;
    }
}
```

## Performance Tips

### 1. Optimize Images
```html
<!-- Use WebP with fallback -->
<picture>
    <source type="image/webp" srcset="image.webp">
    <img src="image.jpg" alt="Description" loading="lazy">
</picture>
```

### 2. Preload Critical Resources
```html
<link rel="preload" href="critical.css" as="style">
<link rel="preload" href="font.woff2" as="font" type="font/woff2" crossorigin>
```

### 3. Defer Non-Critical JavaScript
```html
<script src="non-critical.js" defer></script>
```

### 4. Use CSS Containment
```css
.card {
    contain: layout style paint;
}
```

### 5. Will-Change for Animations
```css
.animated-element {
    will-change: transform;
}

/* Remove after animation */
.animated-element.done {
    will-change: auto;
}
```

## Accessibility Snippets

### 1. Skip to Main Content
```html
<a href="#main-content" class="skip-link">Skip to main content</a>

<style>
.skip-link {
    position: absolute;
    top: -40px;
    left: 0;
    background: #000;
    color: #fff;
    padding: 8px;
    z-index: 100;
}

.skip-link:focus {
    top: 0;
}
</style>
```

### 2. Screen Reader Only Text
```css
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border-width: 0;
}
```

### 3. Focus Trap (Modal)
```javascript
function trapFocus(element) {
    const focusableElements = element.querySelectorAll(
        'a[href], button, textarea, input, select, [tabindex]:not([tabindex="-1"])'
    );
    const firstFocusable = focusableElements[0];
    const lastFocusable = focusableElements[focusableElements.length - 1];

    element.addEventListener('keydown', (e) => {
        if (e.key === 'Tab') {
            if (e.shiftKey && document.activeElement === firstFocusable) {
                lastFocusable.focus();
                e.preventDefault();
            } else if (!e.shiftKey && document.activeElement === lastFocusable) {
                firstFocusable.focus();
                e.preventDefault();
            }
        }
    });
}
```

## Testing Snippets

### 1. Console Log Viewport Size
```javascript
console.log(`Viewport: ${window.innerWidth}x${window.innerHeight}`);
```

### 2. Test Touch Events
```javascript
document.addEventListener('touchstart', () => {
    console.log('Touch detected');
}, { passive: true });
```

### 3. Performance Monitoring
```javascript
window.addEventListener('load', () => {
    const perfData = window.performance.timing;
    const pageLoadTime = perfData.loadEventEnd - perfData.navigationStart;
    console.log(`Page load time: ${pageLoadTime}ms`);
});
```

---

**Nota:** Estes snippets são exemplos genéricos. Adapte-os conforme necessário para o projeto sheiqaway.
