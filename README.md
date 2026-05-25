# De Wereldvrede

Kirby CMS website for De Wereldvrede production company.

## Setup

1. Install PHP dependencies: `composer install`
2. Install Node dependencies: `npm install`
3. Add ABC Diatype font files to `src/assets/fonts/`:
   - `ABCDiatypeCondensed-Bold.woff2`
   - `ABCDiatype-Regular.woff2`
   - `ABCDiatypeMono-Regular.woff2`
4. Build assets: `npm run production`
5. Run locally via DDEV or `composer start`

## Pages

- `/` — Snap-scroll homepage with hero video and feature slides
- `/work` — Project index with filters
- `/work/{slug}` — Project detail
- `/creators` — Creator list with overlay details
- `/about` — About page
- `/news` — News grid
- `/news/{slug}` — Article detail
- Contact opens as overlay from navigation
