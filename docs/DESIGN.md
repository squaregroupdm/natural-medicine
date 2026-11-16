# Design notes

The idea in one line: **from nature's roots to everyday wellness.** The page
travels the way the theme line does. It opens on line drawings, moves into a
forest on film, circles the plants one by one, and then arrives in ordinary
life: a plain, light section that asks what you want to support and shows what
fits. Film is for the story. Plain and fast is for finding a product.

Keep to these rules and later changes will look like they belong.

## Colour

All colours are variables at the top of `assets/css/site.css`.

| Name | Value | Used for |
|---|---|---|
| `--forest` | `#0B2526` | Dark sections |
| `--forest-2` | `#0F3233` | Health areas section |
| `--mist` | `#EDF2EE` | Light sections. Cool, never cream |
| `--white` | `#FFFFFF` | Product photograph cards only |
| `--teal` | `#45989B` | Brand teal. Fills and thin lines, not text |
| `--teal-deep` | `#1F6468` | Export section background |
| `--teal-ink` | `#1A585C` | Teal that is readable as text on light |
| `--gold` | `#D9A441` | The one accent. Buttons, labels on dark, the progress line |

One accent only. If something new needs emphasis, use gold or use size. Do not
add a second accent colour.

## Type

| Role | Font | Notes |
|---|---|---|
| Headlines, plant and ingredient names, figures | Tiro Bangla | Serif, one weight. Covers Latin and Bangla. Italic for botanical names |
| Body, labels, buttons, prices | Anek Bangla (variable) | Weights 400 to 700. Labels use a narrower width, capitals and wide tracking |

Bangla text never gets capitals, letter-spacing or italics, and gets more line
height. Those overrides sit together near the end of the stylesheet under
`html[data-lang="bn"]`.

## Layout

- Content width tops out at 1360 px, with a side margin of 20 to 72 px.
- Sections are deliberately off-centre: headline left, supporting text or picture right, figures on an uneven grid.
- The story is dark throughout. The product list is light, joined to it by the sweeping curve from the booklet cover.
- Each section sets its own background. The top bar reads `data-tone` from the section beneath it.

## Line drawings

- Used in two places only: the opening, where three plants draw themselves, and as the small icons on the Wellness Finder buttons.
- Thin, single weight, no fill to speak of. Teal on light, gold for the one that is selected.

## Film

- Every clip is one slow, continuous camera move with no cuts, 4 to 6 seconds, silent.
- The subject sits on the right. The left third stays dark and calm, because the words sit there. On phones the picture is cropped to the right-hand part and the words move to the bottom.
- One look across all clips: dawn light from the upper right, thin mist, deep teal-green shadows, shallow depth of field.
- Product cards over film are frosted glass, so the photograph on its white tile is the brightest thing on screen.

## Motion

- The clip follows the scroll, forwards and back. Nothing in the story plays on a timer.
- Words arrive one line at a time as the visitor scrolls, and leave before the next picture dissolves in.
- On a computer the mouse wheel glides, the picture shifts slightly with the pointer, and cards lean towards it.
- Only `transform` and `opacity` are animated.
- Visitors who ask their device for reduced motion get a still page with everything visible.

## Accessibility

- Text contrast meets WCAG AA on every background used.
- Every control is reachable by keyboard and shows a gold focus ring. The first Tab stop is a skip link.
- The product window is a native `<dialog>`, so focus is trapped and Escape closes it.
- Clips are hidden from screen readers. Each still carries the plant's name as its description.
