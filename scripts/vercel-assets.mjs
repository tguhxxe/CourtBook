import { cp, mkdir } from 'node:fs/promises';
// Publish only approved static assets, never PHP source or environment files.
await mkdir('vercel-public', { recursive: true });
for (const file of ['build', 'favicon.ico', 'robots.txt']) {
    await cp(`public/${file}`, `vercel-public/${file}`, { recursive: true });
}
