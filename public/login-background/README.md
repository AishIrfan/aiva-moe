# Login-page video background — asset directory

This directory holds the looping institutional/education-themed video used as the background of `/login`. See `LOGIN_VIDEO_BACKGROUND_CHECKLIST.md` at the project root for the full spec.

The implementation in `resources/views/layouts/guest.blade.php` already references these filenames. **Source + encode the assets following the recipes below, drop them in here, and they go live on the next deploy.** No code change required after the asset drop.

---

## Files this directory should contain

| File | Format | Resolution | Bitrate | Target size | Served when |
|------|--------|-----------|---------|-------------|-------------|
| `bg-desktop.webm` | WebM (VP9 or AV1) | 1920×1080 | ~1.5 Mbps | ≤ 5 MB | Desktop (≥ 768 px) — preferred by browsers that support WebM |
| `bg-desktop.mp4` | MP4 (H.264) | 1920×1080 | ~1.5–2 Mbps | ≤ 5 MB | Desktop (≥ 768 px) — fallback when WebM not supported |
| `bg-mobile.mp4` | MP4 (H.264) | 1280×720 | ~800 Kbps | ≤ 2 MB | Mobile (< 768 px) |
| `bg-poster.jpg` | JPG | 1920×1080 | q=80 | ≤ 250 KB | Loading state, autoplay-blocked fallback, `prefers-reduced-motion: reduce` |
| `LICENSE.md` | Markdown | — | — | — | License + provenance documentation |

Until the assets are in place, the login page shows the dark `bg-zinc-950` body colour with the gradient overlay on top of nothing — visually degraded but not broken. The card and form remain fully functional.

---

## Sourcing — preferred stock libraries

In order of preference (all permit commercial use without attribution):
1. https://www.pexels.com/videos/ — Pexels License
2. https://pixabay.com/videos/ — Pixabay License
3. https://coverr.co/ — Coverr License

**Do NOT use** YouTube/Vimeo clips, search-engine image results, or anything without a clear commercial-use licence.

Selection criteria (per checklist §1.2):
- **Theme**: education / classroom / books / writing / library — institutional, not abstract
- **Motion**: low / slow / calm — no fast cuts, no rapid camera moves
- **Subjects**: avoid identifiable faces; hands-writing / page-turning / room-from-behind / light-through-windows
- **Palette**: warm naturals (wood, paper, sunlight) or cool institutional (chalkboard, books) — nothing oversaturated
- **Aspect**: 16:9 or wider, 1920×1080 minimum source resolution
- **Length**: 10–25 seconds; must loop gracefully (or short enough to crossfade-mask the seam)

Per checklist §5, evaluate **3–5 candidates** before picking — asset quality has outsized impact on the final feel. Don't take the first plausible result.

---

## Encoding — ffmpeg recipes

After downloading the chosen source clip (say `source.mp4`):

```bash
# bg-desktop.mp4 — H.264, 1920x1080, ~1.5 Mbps, faststart for streaming
ffmpeg -i source.mp4 \
  -vf scale=1920:1080 \
  -c:v libx264 -profile:v high -crf 24 -preset slow \
  -an -movflags +faststart \
  bg-desktop.mp4

# bg-desktop.webm — VP9, same dimensions
ffmpeg -i source.mp4 \
  -vf scale=1920:1080 \
  -c:v libvpx-vp9 -b:v 1.5M -crf 32 \
  -an \
  bg-desktop.webm

# bg-mobile.mp4 — H.264, 1280x720, ~800 Kbps
ffmpeg -i source.mp4 \
  -vf scale=1280:720 \
  -c:v libx264 -profile:v high -crf 26 -preset slow \
  -an -movflags +faststart \
  bg-mobile.mp4

# bg-poster.jpg — single still frame, picked from a visually-settled moment
# (not frame 0). Adjust -ss to the timestamp you want, e.g. 5s in:
ffmpeg -ss 00:00:05 -i source.mp4 -vframes 1 -q:v 4 -vf scale=1920:1080 bg-poster.jpg
```

Flag notes:
- `-an` strips audio. Required: autoplay needs `muted`, and a login page should never play sound regardless.
- `-movflags +faststart` for MP4 moves the moov atom to the front so the browser can begin playback before the full file downloads.
- `-crf` is quality-targeted constant rate factor — lower = better quality, larger file. The numbers above hit the size targets at acceptable quality for the typical institutional footage.

If `ffmpeg` isn't available locally, use a hosted converter that supports the same parameters — e.g. CloudConvert (paid for batch), or stand up a one-off Docker container: `docker run --rm -v $(pwd):/work jrottenberg/ffmpeg -i /work/source.mp4 ...`.

After encoding, verify each file is at or below its target size. If `bg-desktop.mp4` exceeds 5 MB, raise `-crf` by 2 and re-encode.

---

## After the assets land

Quick sanity check on a deployed environment:

```bash
# These should all 200 once the files are in place:
curl -I https://moe-laravel.weststar-dev.com/login-background/bg-desktop.mp4
curl -I https://moe-laravel.weststar-dev.com/login-background/bg-desktop.webm
curl -I https://moe-laravel.weststar-dev.com/login-background/bg-mobile.mp4
curl -I https://moe-laravel.weststar-dev.com/login-background/bg-poster.jpg
```

Then visit `/login` and verify against the §4 acceptance criteria in the checklist (loops without seam, card stays crisp, wordmark legible, video pauses when tab is hidden, reduced-motion shows poster only, etc.).
