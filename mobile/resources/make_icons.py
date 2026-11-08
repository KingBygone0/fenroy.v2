"""Builds the app icons from the website's header logo (public/images/fenroy-logo.png), so
the app and fenroy.shop show the same mark: the tilted dark-red tile with a white cart.

    python3 resources/make_icons.py      (from mobile/; needs Pillow)

Writes resources/icon.png, the iOS AppIcon and the Android launcher icons.
"""
from pathlib import Path

from PIL import Image, ImageDraw, ImageFilter

MOBILE = Path(__file__).resolve().parent.parent
LOGO = MOBILE.parent / "public" / "images" / "fenroy-logo.png"
RES = MOBILE / "android" / "app" / "src" / "main" / "res"
WHITE = (255, 255, 255, 255)

# Android launcher sizes per density: legacy icon (48dp) and adaptive foreground (108dp).
DENSITIES = {"mdpi": (48, 108), "hdpi": (72, 162), "xhdpi": (96, 216), "xxhdpi": (144, 324), "xxxhdpi": (192, 432)}


def logo_mark() -> Image.Image:
    """The tile on the left of the logo, without the "Fenroy" wordmark."""
    logo = Image.open(LOGO).convert("RGBA")
    alpha = logo.getchannel("A")
    cols = [alpha.crop((x, 0, x + 1, logo.height)).getextrema()[1] > 0 for x in range(logo.width)]
    start = cols.index(True)
    end = start + cols[start:].index(False)
    mark = logo.crop((start, 0, end, logo.height))
    return mark.crop(mark.getbbox())


def render_mark(size: int = 2048) -> Image.Image:
    """The mark redrawn larger with crisp edges: the logo is two flat colours, so each
    shape is upscaled, smoothed and re-thresholded, then scaled down for anti-aliasing."""
    src = logo_mark()
    opaque = [px for px in (src.get_flattened_data() if hasattr(src, "get_flattened_data") else src.getdata()) if px[3] > 250 and px[0] > 120 and px[1] < 60]
    red = tuple(sorted(c[i] for c in opaque)[len(opaque) // 2] for i in range(3)) + (255,)

    big = size * 2
    scale = big / max(src.size)
    up = src.resize((round(src.width * scale), round(src.height * scale)), Image.BICUBIC)

    def crisp(mask):
        return mask.filter(ImageFilter.GaussianBlur(scale * 1.0)).point(lambda v: 255 if v >= 128 else 0)

    shape = crisp(up.getchannel("A"))
    white = crisp(Image.eval(up.convert("L"), lambda v: 255 if v > 200 else 0))
    white = Image.composite(white, Image.new("L", up.size, 0), shape)

    out = Image.new("RGBA", up.size, (0, 0, 0, 0))
    out.paste(Image.new("RGBA", up.size, red), (0, 0), shape)
    out.paste(Image.new("RGBA", up.size, WHITE), (0, 0), white)
    out = out.resize((src.width * size // max(src.size), src.height * size // max(src.size)), Image.LANCZOS)
    return out.crop(out.getbbox())


def on_canvas(mark: Image.Image, size: int, fraction: float, bg=WHITE) -> Image.Image:
    """The mark centred on a size × size canvas, its longer side `fraction` of the canvas."""
    canvas = Image.new("RGBA", (size, size), bg)
    scale = size * fraction / max(mark.size)
    m = mark.resize((round(mark.width * scale), round(mark.height * scale)), Image.LANCZOS)
    canvas.alpha_composite(m, ((size - m.width) // 2, (size - m.height) // 2))
    return canvas


def main():
    mark = render_mark()

    # iOS and the source icon: opaque white square (App Store icons may not be transparent).
    icon = on_canvas(mark, 1024, 0.80).convert("RGB")
    icon.save(MOBILE / "resources" / "icon.png", optimize=True)
    icon.save(MOBILE / "ios" / "App" / "App" / "Assets.xcassets" / "AppIcon.appiconset" / "AppIcon-512@2x.png",
              optimize=True)

    for density, (legacy, fg) in DENSITIES.items():
        folder = RES / f"mipmap-{density}"
        # Adaptive foreground: keep the mark inside the 66dp safe zone of the 108dp canvas.
        on_canvas(mark, fg, 0.58, bg=(0, 0, 0, 0)).save(folder / "ic_launcher_foreground.png", optimize=True)
        on_canvas(mark, legacy, 0.80).save(folder / "ic_launcher.png", optimize=True)
        circle = Image.new("L", (legacy * 4, legacy * 4), 0)
        ImageDraw.Draw(circle).ellipse((0, 0, legacy * 4 - 1, legacy * 4 - 1), fill=255)
        round_icon = on_canvas(mark, legacy, 0.70)
        round_icon.putalpha(circle.resize((legacy, legacy), Image.LANCZOS))
        round_icon.save(folder / "ic_launcher_round.png", optimize=True)

    (RES / "values" / "ic_launcher_background.xml").write_text(
        '<?xml version="1.0" encoding="utf-8"?>\n<resources>\n'
        '    <color name="ic_launcher_background">#FFFFFF</color>\n</resources>\n')


if __name__ == "__main__":
    main()
