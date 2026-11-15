# Design notes

The idea in one line: **herbarium plates over living foliage.** Plants are shown
as drawn specimens on paper cards. Products are shown as real photographs on
white cards. Behind the dark sections, painted leaves at three depths give the
page a sense of space without needing photography or video.

Keep to these rules and later changes will look like they belong.

## Colour

All colours are variables at the top of `assets/css/site.css`.

| Name | Value | Used for |
|---|---|---|
| `--forest` | `#0B2526` | Dark sections |
| `--forest-2` | `#0F3233` | Health areas section |
| `--mist` | `#EDF2EE` | Light sections. Cool, never cream |
| `--paper` | `#F6F7F0` | Plant cards |
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
- Light and dark sections alternate, joined by the sweeping curve from the booklet cover.
- Each section sets its own background. The top bar reads `data-tone` from the section beneath it.

## Motion

- Things appear once, the first time they are seen: a short rise and fade. Headlines rise word by word. Plant drawings draw line by line.
- Three things follow the scroll continuously: the hero leaves, the sideways plant row, and the leaf-to-bottle link.
- Only `transform` and `opacity` are animated.
- Visitors who ask their device for reduced motion get a still page with everything visible.

## Accessibility

- Text contrast meets WCAG AA on every background used.
- Every control is reachable by keyboard and shows a gold focus ring. The first Tab stop is a skip link.
- The product window is a native `<dialog>`, so focus is trapped and Escape closes it.
- Decorative drawings and the painted leaves are hidden from screen readers.
