"""Frames the raw app screenshots for the App Store and Google Play.

Raw captures live in store/raw/ios (1320 x 2868, iPhone 17 Pro Max simulator)
and store/raw/android (1080 x 2400, Pixel-size emulator). Re-run after taking
new captures:

    python3 store/make_store_images.py      (from the mobile/ folder; needs Pillow)
"""
from pathlib import Path

from PIL import Image, ImageDraw, ImageFilter, ImageFont

HERE = Path(__file__).parent
RED = (229, 57, 53)  # --color-brand-red
DARK_RED = (183, 28, 28)  # --color-brand-dark-red
WHITE = (255, 255, 255)
BOLD = "/System/Library/Fonts/Supplemental/Arial Bold.ttf"
REGULAR = "/System/Library/Fonts/Supplemental/Arial.ttf"

SHOTS = [
    ("01_home", "Groceries,\ndelivered fast", "Fresh food and household essentials, to your door"),
    ("02_categories", "Everything in\none place", "Fruit and veg, drinks, pantry, baby care and more"),
    ("03_products", "Deals on what\nyou buy every week", "Fresh discounts across the store"),
    ("04_product", "Same-day\ndelivery", "Order before 6pm, seven days a week"),
    ("05_cart", "Pay with\nMoMo or card", "On delivery or online"),
]

# folder, raw source, output size
TARGETS = [
    ("ios-6.9", "ios", (1320, 2868)),
    ("ios-6.5", "ios", (1284, 2778)),
    ("android-phone", "android", (1080, 1920)),
]


def gradient(size):
    w, h = size
    top = Image.new("RGB", size, RED)
    bottom = Image.new("RGB", size, DARK_RED)
    mask = Image.linear_gradient("L").resize(size)
    return Image.composite(bottom, top, mask)


def centered(draw, text, font, y, width, fill, spacing):
    box = draw.multiline_textbbox((0, 0), text, font=font, spacing=spacing, align="center")
    draw.multiline_text(((width - (box[2] - box[0])) / 2, y), text, font=font, fill=fill,
                        spacing=spacing, align="center")
    return y + (box[3] - box[1])


def frame(raw: Image.Image, title: str, sub: str, size) -> Image.Image:
    w, h = size
    canvas = gradient(size)
    d = ImageDraw.Draw(canvas)
    y = int(h * 0.055)
    y = centered(d, title, ImageFont.truetype(BOLD, int(w * 0.082)), y, w, WHITE, int(w * 0.012))
    y += int(h * 0.03)
    centered(d, sub, ImageFont.truetype(REGULAR, int(w * 0.036)), y, w, (255, 225, 224), 0)

    # Phone: the screenshot with rounded corners, a dark bezel and a soft shadow.
    phone_h = int(h * 0.70)
    phone_w = int(phone_h * raw.width / raw.height)
    if phone_w > w * 0.84:
        phone_w = int(w * 0.84)
        phone_h = int(phone_w * raw.height / raw.width)
    radius = int(phone_w * 0.11)
    bezel = max(8, int(phone_w * 0.022))
    screen = raw.convert("RGB").resize((phone_w, phone_h), Image.LANCZOS)
    mask = Image.new("L", screen.size, 0)
    ImageDraw.Draw(mask).rounded_rectangle((0, 0, phone_w, phone_h), radius, fill=255)

    px = (w - phone_w) // 2
    py = h - phone_h - int(h * 0.035)
    shadow = Image.new("L", size, 0)
    ImageDraw.Draw(shadow).rounded_rectangle(
        (px - bezel, py - bezel + 20, px + phone_w + bezel, py + phone_h + bezel + 20), radius + bezel, fill=110)
    canvas.paste((60, 0, 0), (0, 0), shadow.filter(ImageFilter.GaussianBlur(40)))
    d = ImageDraw.Draw(canvas)
    d.rounded_rectangle((px - bezel, py - bezel, px + phone_w + bezel, py + phone_h + bezel),
                        radius + bezel, fill=(20, 20, 22))
    canvas.paste(screen, (px, py), mask)
    return canvas


def feature_graphic():
    size = (1024, 500)
    g = gradient(size)
    d = ImageDraw.Draw(g)
    icon = Image.open(HERE.parent / "resources" / "icon.png").convert("RGBA").resize((220, 220), Image.LANCZOS)
    mask = Image.new("L", icon.size, 0)
    ImageDraw.Draw(mask).rounded_rectangle((0, 0, 220, 220), 48, fill=255)
    g.paste(icon, (80, 140), mask)
    d.text((350, 150), "Fenroy", font=ImageFont.truetype(BOLD, 92), fill=WHITE)
    d.text((354, 262), "Groceries delivered to your door", font=ImageFont.truetype(REGULAR, 38),
           fill=(255, 225, 224))
    d.text((354, 318), "Pay with MoMo or card", font=ImageFont.truetype(BOLD, 30), fill=WHITE)
    return g


def main():
    for folder, src, size in TARGETS:
        out = HERE / "screenshots" / folder
        out.mkdir(parents=True, exist_ok=True)
        for name, title, sub in SHOTS:
            raw = Image.open(HERE / "raw" / src / f"{name}.png")
            frame(raw, title, sub, size).save(out / f"{name}.png", optimize=True)
    android = HERE / "android"
    android.mkdir(exist_ok=True)
    feature_graphic().save(android / "feature-graphic.png", optimize=True)
    Image.open(HERE.parent / "resources" / "icon.png").convert("RGB").resize((512, 512), Image.LANCZOS) \
        .save(android / "icon-512.png", optimize=True)


if __name__ == "__main__":
    main()
