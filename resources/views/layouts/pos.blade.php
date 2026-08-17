<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>POS Terminal — Fenroy</title>
@vite(['resources/css/app.css', 'resources/js/app.js'])
@livewireStyles
<style>
/* ─── Fenroy Brand Tokens ─── */
:root {
    --fen-red:    #E53935;
    --fen-dark:   #B71C1C;
    --fen-light:  #FFEBEE;
    --fen-ok:     #2E7D32;
    --fen-warn:   #F59E0B;
    --fen-err:    #D32F2F;
    --fen-text:   #171717;
    --fen-muted:  #666666;
    --fen-bg:     #F7F7F7;
    --fen-white:  #FFFFFF;
    --fen-border: #E5E5E5;
    --fen-side:    #B71C1C;
    --fen-sidebar: #B71C1C;  /* alias — keep both */
    --fen-danger:  #D32F2F;
    --fen-success: #2E7D32;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif; background: var(--fen-bg); }

/* ─── 3-column POS grid (terminal) ─── */
.pos-grid {
    display: grid;
    grid-template-columns: 188px minmax(0, 1fr) 420px;
    grid-template-rows: 88px 1fr;
    height: 100vh;
    overflow: hidden;
}
/* ─── 2-column POS grid (sub-pages) ─── */
.pos-grid-2 {
    display: grid;
    grid-template-columns: 188px minmax(0, 1fr);
    grid-template-rows: 88px 1fr;
    height: 100vh;
    overflow: hidden;
}
/* Header always spans all columns */
.pos-header { grid-column: 1 / -1; background: var(--fen-white); border-bottom: 1px solid var(--fen-border); display: flex; align-items: center; overflow: hidden; }

/* ─── Sidebar ─── */
.pos-sidebar { background: var(--fen-side); display: flex; flex-direction: column; overflow: hidden; padding: 16px 12px; min-height: 0; }

/* ─── Nav items — white text, stands out ─── */
.nav-item {
    display: flex; align-items: center; gap: 10px;
    min-height: 44px; padding: 0 14px; border-radius: 8px;
    text-decoration: none; color: rgba(255,255,255,.88);
    font-size: 13px; font-weight: 600; margin-bottom: 2px;
    transition: background .12s;
    cursor: pointer; border: none; background: transparent; width: 100%;
    white-space: nowrap;
}
.nav-item:hover  { background: rgba(0,0,0,.2); color: #fff; }
.nav-active      { background: rgba(0,0,0,.3) !important; color: #fff !important; font-weight: 700 !important; }
.nav-disabled    { opacity: .3; cursor: not-allowed; pointer-events: none; }

/* ─── Main areas ─── */
.pos-main { background: var(--fen-bg); display: flex; flex-direction: column; overflow: hidden; min-height: 0; }
.pos-cart { background: var(--fen-white); border-left: 1px solid var(--fen-border); display: flex; flex-direction: column; overflow: hidden; min-height: 0; }

/* ─── Category pills ─── */
.cat-btn { height: 38px; padding: 0 16px; border-radius: 10px; border: 1.5px solid var(--fen-border); background: var(--fen-white); color: var(--fen-text); font-size: 13px; font-weight: 600; cursor: pointer; white-space: nowrap; flex-shrink: 0; transition: all .12s; }
.cat-btn:hover { border-color: var(--fen-red); color: var(--fen-red); }
.cat-active    { background: var(--fen-red) !important; color: #fff !important; border-color: var(--fen-red) !important; }

/* ─── Product grid ─── */
.product-grid { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 12px; }
.product-card { background: var(--fen-white); border: 1px solid var(--fen-border); border-radius: 10px; overflow: hidden; cursor: pointer; min-width: 0; padding: 0; text-align: left; width: 100%; transition: border-color .12s, box-shadow .12s; }
.product-card:hover:not(:disabled) { border-color: var(--fen-red); box-shadow: 0 2px 12px rgba(229,57,53,.13); }
.product-card:disabled { opacity: .4; cursor: not-allowed; }

/* ─── Pagination ─── */
.page-btn { width: 40px; height: 40px; border-radius: 8px; border: 1.5px solid var(--fen-border); background: var(--fen-white); color: var(--fen-text); font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background .12s; }
.page-btn:hover:not(:disabled) { background: var(--fen-bg); }
.page-active { background: var(--fen-red) !important; color: #fff !important; border-color: var(--fen-red) !important; }
.page-btn:disabled { opacity: .35; cursor: not-allowed; }

/* ─── Cart ─── */
.cart-item { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-bottom: 1px solid #fafafa; min-height: 72px; }
.qty-btn { width: 28px; height: 28px; border-radius: 6px; border: 1.5px solid var(--fen-border); background: var(--fen-bg); font-size: 16px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--fen-text); flex-shrink: 0; transition: background .1s; }
.qty-btn:hover { background: var(--fen-border); }

/* ─── Payment ─── */
.pay-method { height: 48px; flex: 1; border-radius: 8px; border: 1.5px solid var(--fen-border); background: var(--fen-white); color: var(--fen-text); font-size: 13px; font-weight: 600; cursor: pointer; transition: all .12s; }
.pay-method:hover:not(.pay-on) { border-color: var(--fen-red); color: var(--fen-red); }
.pay-on { background: var(--fen-red) !important; color: #fff !important; border-color: var(--fen-red) !important; }

/* ─── Big pay button ─── */
.pay-btn { width: 100%; height: 56px; border-radius: 10px; background: var(--fen-red); color: #fff; font-size: 18px; font-weight: 700; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background .15s; }
.pay-btn:hover { background: var(--fen-dark); }
.pay-btn:disabled { opacity: .55; cursor: not-allowed; background: var(--fen-red); }

/* ─── Generic inputs ─── */
.pos-input { width: 100%; height: 40px; border: 1.5px solid var(--fen-border); border-radius: 8px; padding: 0 12px; font-size: 13px; color: var(--fen-text); outline: none; transition: border-color .15s; background: var(--fen-white); }
.pos-input:focus { border-color: var(--fen-red); }

/* ─── Search ─── */
.pos-search { width: 100%; height: 48px; border: 2px solid var(--fen-border); border-radius: 10px; padding: 0 48px; font-size: 14px; color: var(--fen-text); background: var(--fen-bg); outline: none; transition: border-color .15s, background .15s; }
.pos-search:focus { border-color: var(--fen-red); background: var(--fen-white); }

/* ─── Header logo zone ─── */
.hdr-logo { width: 188px; min-width: 188px; height: 100%; display: flex; align-items: center; gap: 10px; padding: 0 20px; background: var(--fen-side); border-right: 1px solid rgba(0,0,0,.12); flex-shrink: 0; }

/* ─── Responsive ─── */
@media (max-width: 1400px) {
    .pos-grid   { grid-template-columns: 176px minmax(0, 1fr) 400px; }
    .pos-grid-2 { grid-template-columns: 176px minmax(0, 1fr); }
    .hdr-logo   { width: 176px; min-width: 176px; }
    .product-grid { grid-template-columns: repeat(5, minmax(0, 1fr)); }
}
@media (max-width: 1200px) {
    .pos-grid   { grid-template-columns: 160px minmax(0, 1fr); }
    .pos-grid-2 { grid-template-columns: 160px minmax(0, 1fr); }
    .hdr-logo   { width: 160px; min-width: 160px; }
    .pos-cart   { position: fixed; right: 0; top: 88px; width: 400px; height: calc(100vh - 88px); z-index: 50; box-shadow: -4px 0 20px rgba(0,0,0,.1); }
    .product-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
}

@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
</head>
<body>
{{ $slot }}
@livewireScripts
</body>
</html>
