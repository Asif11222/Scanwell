<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="description" content="ScanWell Admin Control Center for managing products, health intelligence, app content, and sponsored campaigns." />
  <title>@yield('title', 'ScanWell Admin')</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --green-900:#075f34;
      --green-800:#08763c;
      --green-700:#079447;
      --green-600:#15a65a;
      --green-500:#22b86a;
      --green-100:#e8f7ee;
      --green-50:#f1faf5;
      --ink-950:#111820;
      --ink-900:#1d252b;
      --ink-800:#2f3842;
      --ink-700:#4b535b;
      --ink-600:#68707c;
      --ink-500:#7b828d;
      --ink-400:#a8adb4;
      --line:#e5e8ea;
      --line-soft:#edf0f1;
      --surface:#ffffff;
      --surface-subtle:#fafcfb;
      --surface-muted:#f5f7f6;
      --canvas:#f5f8f6;
      --danger:#e53946;
      --danger-strong:#c83243;
      --danger-bg:#fff2f3;
      --danger-border:#ffcdd3;
      --warning:#e9a413;
      --warning-strong:#a97910;
      --warning-bg:#fffaf0;
      --warning-border:#ffe8b4;
      --success:#2f9f52;
      --success-bg:#f0faea;
      --success-border:#cdeedb;
      --blue:#2397cf;
      --blue-bg:#f3f9fd;
      --purple:#7c46b9;
      --purple-bg:#f8f3fc;
      --ad:#5c5bd6;
      --ad-bg:#f4f3ff;
      --ad-border:#dcd9ff;
      --shadow-sm:0 1px 2px rgba(17,24,32,.05),0 1px 1px rgba(17,24,32,.03);
      --shadow-md:0 10px 30px rgba(17,24,32,.08),0 2px 8px rgba(17,24,32,.05);
      --shadow-lg:0 24px 70px rgba(17,24,32,.18);
      --radius-sm:8px;
      --radius-md:12px;
      --radius-lg:16px;
      --radius-xl:22px;
      --sidebar:260px;
      --topbar:72px;
      --font:'Inter',ui-sans-serif,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;
    }

    *{box-sizing:border-box}
    html{background:var(--canvas)}
    body{margin:0;font-family:var(--font);background:var(--canvas);color:var(--ink-950);font-size:14px;line-height:1.45;-webkit-font-smoothing:antialiased}
    button,input,select,textarea{font:inherit}
    button{cursor:pointer}
    a{color:inherit;text-decoration:none}
    svg{display:block}
    [hidden]{display:none!important}
    :focus-visible{outline:3px solid rgba(7,148,71,.22);outline-offset:2px}

    .app-shell{min-height:100vh;display:flex}
    .sidebar{position:fixed;inset:0 auto 0 0;width:var(--sidebar);background:var(--surface);border-right:1px solid var(--line);display:flex;flex-direction:column;z-index:40;transition:transform .22s ease,width .22s ease}
    .brand{height:var(--topbar);display:flex;align-items:center;gap:11px;padding:0 22px;border-bottom:1px solid var(--line-soft)}
    .brand-mark{width:36px;height:36px;border-radius:11px;background:linear-gradient(145deg,var(--green-700),var(--green-500));display:grid;place-items:center;color:#fff;box-shadow:0 8px 18px rgba(7,148,71,.22)}
    .brand-copy strong{font-size:17px;letter-spacing:-.25px;display:block;line-height:1.08;color:var(--ink-950)}
    .brand-copy span{font-size:10px;color:var(--ink-500);letter-spacing:.15px}
    .nav-wrap{padding:14px 12px 20px;overflow-y:auto;flex:1}
    .nav-label{font-size:10px;font-weight:800;letter-spacing:.9px;text-transform:uppercase;color:var(--ink-400);padding:11px 11px 6px}
    .nav-item{width:100%;border:0;background:transparent;color:var(--ink-700);height:42px;border-radius:10px;padding:0 11px;display:flex;align-items:center;gap:11px;text-align:left;font-weight:650;font-size:13px;transition:background .15s,color .15s;text-decoration:none}
    .nav-item:hover{background:var(--surface-muted);color:var(--ink-950)}
    .nav-item.active{background:var(--green-50);color:var(--green-800)}
    .nav-item.active .nav-icon{background:rgba(7,148,71,.12);color:var(--green-800)}
    .nav-item .nav-icon{width:29px;height:29px;border-radius:9px;background:var(--surface-muted);display:grid;place-items:center;flex:0 0 auto;color:var(--ink-600)}
    .nav-item .nav-text{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .nav-count{margin-left:auto;min-width:20px;height:20px;padding:0 6px;border-radius:999px;background:var(--surface-muted);display:grid;place-items:center;font-size:10px;font-weight:800;color:var(--ink-600)}
    .nav-item.active .nav-count{background:var(--green-100);color:var(--green-800)}

    /* Collapsible Nav Group & Tree Submenu (Master Data) */
    .nav-group{position:relative;margin-bottom:2px}
    .nav-item-parent{cursor:pointer;position:relative;user-select:none;border:none}
    .nav-chevron{margin-left:auto;display:flex;align-items:center;justify-content:center;color:var(--ink-400);transition:transform .22s cubic-bezier(.4,0,.2,1);flex-shrink:0}
    .nav-group.open .nav-chevron{transform:rotate(180deg)}
    .nav-group.active .nav-chevron{color:var(--green-800)}
    .nav-submenu{display:none;padding:2px 0 6px 0}
    .nav-group.open .nav-submenu{display:block}
    .submenu-tree{list-style:none;margin:0;padding:0 0 0 25px;position:relative}
    .submenu-tree::before{content:"";position:absolute;left:25px;top:2px;bottom:14px;width:1.5px;background:#cfd6dc}
    .submenu-tree-item{position:relative;list-style:none;margin:2px 0}
    .submenu-tree-item::before{content:"";position:absolute;left:0;top:15px;width:13px;height:1.5px;background:#cfd6dc}
    .submenu-link{display:flex;align-items:center;gap:7px;padding:6px 10px 6px 17px;border-radius:8px;font-size:12px;font-weight:550;color:var(--ink-700);text-decoration:none;transition:background .15s,color .15s,border-color .15s;border:1px solid transparent;line-height:1.35}
    .submenu-link:hover{background:var(--surface-muted);color:var(--ink-950)}
    .tree-bullet{font-size:16px;line-height:0;color:var(--ink-400);flex-shrink:0;margin-top:-1px}
    .submenu-link.active{background:var(--green-50);border-color:var(--green-600);color:var(--green-900);font-weight:700;box-shadow:0 1px 3px rgba(7,148,71,.08)}
    .submenu-link.active .tree-bullet{color:var(--green-700)}
    .sidebar-footer{padding:14px;border-top:1px solid var(--line-soft)}
    .admin-chip{background:var(--surface-subtle);border:1px solid var(--line);border-radius:12px;padding:10px;display:flex;align-items:center;gap:10px}
    .avatar{width:34px;height:34px;border-radius:50%;display:grid;place-items:center;font-weight:800;font-size:12px;background:var(--green-100);color:var(--green-800);flex:0 0 auto}
    .admin-chip .meta{min-width:0;flex:1}
    .admin-chip .meta strong{display:block;font-size:12.5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .admin-chip .meta span{font-size:10.5px;color:var(--ink-500)}

    .workspace{margin-left:var(--sidebar);min-width:0;width:calc(100% - var(--sidebar));transition:margin .22s,width .22s}
    .topbar{height:var(--topbar);position:sticky;top:0;z-index:30;background:rgba(255,255,255,.94);backdrop-filter:blur(16px);border-bottom:1px solid var(--line);display:flex;align-items:center;padding:0 24px;gap:18px}
    .mobile-menu{display:none;width:38px;height:38px;border:1px solid var(--line);border-radius:10px;background:#fff;color:var(--ink-700);align-items:center;justify-content:center}
    .top-title{min-width:190px}
    .breadcrumbs{font-size:10.5px;color:var(--ink-500);margin-bottom:2px}
    .top-title h1{font-size:17px;margin:0;font-weight:760;letter-spacing:-.25px}
    .global-search{position:relative;max-width:480px;flex:1;margin-left:auto}
    .global-search input{width:100%;height:40px;border:1px solid var(--line);border-radius:11px;background:var(--surface-muted);padding:0 42px 0 38px;color:var(--ink-900);outline:none;transition:border .15s,background .15s,box-shadow .15s}
    .global-search input:focus{background:#fff;border-color:#b8d8c4;box-shadow:0 0 0 3px rgba(7,148,71,.08)}
    .global-search .search-icon{position:absolute;left:12px;top:11px;color:var(--ink-500)}
    .shortcut{position:absolute;right:10px;top:9px;height:22px;min-width:26px;border:1px solid var(--line);border-radius:6px;background:#fff;color:var(--ink-400);display:grid;place-items:center;font-size:10px;padding:0 5px}
    .search-results{position:absolute;left:0;right:0;top:46px;background:#fff;border:1px solid var(--line);box-shadow:var(--shadow-md);border-radius:14px;padding:8px;max-height:420px;overflow:auto;z-index:100}
    .search-group-title{font-size:10px;color:var(--ink-400);font-weight:800;text-transform:uppercase;letter-spacing:.7px;padding:8px 10px 4px}
    .search-hit{width:100%;border:0;background:#fff;padding:9px 10px;border-radius:9px;display:flex;align-items:center;gap:10px;text-align:left;text-decoration:none;color:inherit}
    .search-hit:hover{background:var(--surface-muted)}
    .search-hit strong{font-size:12.5px;display:block}
    .search-hit span{font-size:10.5px;color:var(--ink-500)}
    .top-actions{display:flex;gap:8px;align-items:center}
    .icon-button{width:38px;height:38px;border:1px solid var(--line);border-radius:10px;background:#fff;color:var(--ink-700);display:grid;place-items:center;position:relative}
    .icon-button:hover{background:var(--surface-muted)}
    .notification-dot{width:7px;height:7px;background:var(--danger);border:2px solid #fff;border-radius:50%;position:absolute;right:7px;top:7px;box-sizing:content-box}
    .sidebar-overlay{position:fixed;inset:0;background:rgba(17,24,32,.4);z-index:35}

    .main-content{padding:24px;max-width:1640px;margin:0 auto}
    .page-head{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:20px}
    .page-head h2{margin:0;font-size:22px;line-height:1.25;letter-spacing:-.45px}
    .page-head p{margin:4px 0 0;color:var(--ink-500);max-width:620px;font-size:12.5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .page-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap;justify-content:flex-end}
    .btn{height:38px;padding:0 14px;border-radius:9px;border:1px solid transparent;display:inline-flex;align-items:center;justify-content:center;gap:7px;font-weight:700;font-size:12.5px;white-space:nowrap;text-decoration:none}
    .btn-primary{background:var(--green-700);color:#fff;border-color:var(--green-700);box-shadow:0 5px 12px rgba(7,148,71,.15)}
    .btn-primary:hover{background:var(--green-800)}
    .btn-secondary{background:#fff;color:var(--ink-800);border-color:var(--line)}
    .btn-secondary:hover{background:var(--surface-muted)}
    .btn-ghost{background:transparent;color:var(--ink-700);border-color:transparent}
    .btn-danger{background:var(--danger);color:#fff}
    .btn-sm{height:32px;padding:0 10px;font-size:11.5px;border-radius:8px}
    .btn:disabled,.btn.disabled{opacity:.45!important;cursor:not-allowed!important;pointer-events:none!important;background:var(--surface-muted)!important;color:var(--ink-400)!important;border-color:var(--line-soft)!important;box-shadow:none!important}

    .grid{display:grid;gap:16px}
    .grid-4{grid-template-columns:repeat(4,minmax(0,1fr))}
    .grid-3{grid-template-columns:repeat(3,minmax(0,1fr))}
    .grid-2{grid-template-columns:repeat(2,minmax(0,1fr))}
    .visual-kpi-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:16px}
    .visual-kpi{position:relative;overflow:hidden;background:var(--surface);border:1px solid var(--line);border-radius:18px;padding:16px;min-height:136px;box-shadow:var(--shadow-sm)}
    .visual-kpi:after{content:"";position:absolute;right:-30px;bottom:-42px;width:118px;height:118px;border-radius:50%;background:var(--green-50)}
    .visual-kpi.blue:after{background:var(--blue-bg)} .visual-kpi.purple:after{background:var(--purple-bg)} .visual-kpi.amber:after{background:var(--warning-bg)}
    .visual-kpi-top{display:flex;align-items:center;justify-content:space-between;gap:10px;position:relative;z-index:1}
    .visual-kpi-icon{width:42px;height:42px;border-radius:13px;display:grid;place-items:center;background:var(--green-100);color:var(--green-800)}
    .visual-kpi.blue .visual-kpi-icon{background:var(--blue-bg);color:var(--blue)} .visual-kpi.purple .visual-kpi-icon{background:var(--purple-bg);color:var(--purple)} .visual-kpi.amber .visual-kpi-icon{background:var(--warning-bg);color:var(--warning-strong)}
    .visual-kpi-value{font-size:27px;line-height:1;font-weight:850;letter-spacing:-.03em;margin-top:16px;position:relative;z-index:1}
    .visual-kpi-label{font-size:12px;font-weight:700;color:var(--ink-600);margin-top:6px;position:relative;z-index:1}
    .visual-kpi-trend{font-size:10px;font-weight:800;padding:4px 7px;border-radius:999px;background:var(--green-50);color:var(--green-800)}

    .graphical-overview{display:grid;grid-template-columns:1.35fr .8fr .9fr;gap:14px;margin-bottom:16px}
    .ring-card{display:flex;align-items:center;gap:16px;min-height:180px}
    .ring-progress{--value:95;--ring:var(--green-700);width:116px;height:116px;border-radius:50%;background:conic-gradient(var(--ring) calc(var(--value)*1%),var(--line-soft) 0);display:grid;place-items:center;position:relative;flex:none}
    .ring-progress:before{content:"";position:absolute;inset:11px;border-radius:50%;background:var(--surface)}
    .ring-progress strong{position:relative;font-size:23px;letter-spacing:-.03em}
    .ring-progress span{position:absolute;top:67px;font-size:10px;color:var(--ink-500);font-weight:700}
    .ring-meta{display:grid;gap:9px;min-width:0}.ring-meta h3{margin:0;font-size:15px}.ring-meta p{margin:0;font-size:11px;color:var(--ink-500)}
    .mini-metric{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:8px 0;border-bottom:1px solid var(--line-soft);font-size:11px}.mini-metric:last-child{border-bottom:0}.mini-metric strong{font-size:13px}

    .health-signal-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-top:12px}
    .health-signal{border-radius:14px;padding:12px 8px;text-align:center;border:1px solid var(--line);background:var(--surface)}
    .health-signal i{display:block;width:13px;height:13px;border-radius:50%;margin:0 auto 7px}.health-signal strong{display:block;font-size:18px;line-height:1}.health-signal span{display:block;font-size:10px;margin-top:5px;color:var(--ink-500);font-weight:700}
    .health-signal.red i{background:var(--danger)}.health-signal.yellow i{background:var(--warning)}.health-signal.green i{background:var(--success)}

    .icon-tile-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
    .icon-tile{border:1px solid var(--line);background:var(--surface);border-radius:14px;padding:13px;min-height:94px;display:flex;flex-direction:column;align-items:flex-start;justify-content:space-between;cursor:pointer;transition:.18s ease;text-align:left;color:var(--ink-900);text-decoration:none}
    .icon-tile:hover{transform:translateY(-2px);box-shadow:var(--shadow-md);border-color:#d5ddd8}.icon-tile .tile-icon{width:36px;height:36px;border-radius:11px;background:var(--green-50);color:var(--green-800);display:grid;place-items:center}.icon-tile strong{font-size:11px;line-height:1.15;margin-top:9px}.icon-tile .tile-count{font-size:10px;color:var(--ink-500)}

    .card{background:#fff;border:1px solid var(--line);border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);min-width:0}
    .card-header{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:16px 17px;border-bottom:1px solid var(--line-soft)}
    .card-title h3{margin:0;font-size:14px;letter-spacing:-.15px}
    .card-title p{margin:3px 0 0;color:var(--ink-500);font-size:10.8px}
    .card-body{padding:16px 17px}
    .chart-wrap{height:245px;display:flex;flex-direction:column}
    .bar-chart{flex:1;display:flex;align-items:flex-end;gap:12px;padding:18px 4px 0;border-bottom:1px solid var(--line-soft);position:relative}
    .bar-chart:before,.bar-chart:after{content:"";position:absolute;left:0;right:0;border-top:1px dashed var(--line-soft)}
    .bar-chart:before{top:34%}.bar-chart:after{top:67%}
    .bar-col{flex:1;min-width:0;height:100%;display:flex;flex-direction:column;justify-content:flex-end;align-items:center;gap:7px;z-index:1}
    .bar{width:min(32px,70%);background:linear-gradient(180deg,var(--green-500),var(--green-700));border-radius:8px 8px 3px 3px;min-height:8px;box-shadow:0 4px 10px rgba(7,148,71,.12)}
    .bar-label{font-size:9.5px;color:var(--ink-500);white-space:nowrap}
    .chart-legend{display:flex;gap:16px;margin-top:12px;color:var(--ink-500);font-size:10.5px}
    .legend-dot{width:7px;height:7px;border-radius:50%;background:var(--green-700);display:inline-block;margin-right:5px}

    .table-wrap{overflow:auto}
    table{width:100%;border-collapse:collapse;min-width:760px}
    th{text-align:left;font-size:10px;text-transform:uppercase;letter-spacing:.55px;color:var(--ink-500);font-weight:800;padding:10px 14px;background:var(--surface-subtle);border-bottom:1px solid var(--line)}
    td{padding:12px 14px;border-bottom:1px solid var(--line-soft);font-size:11.5px;color:var(--ink-800);vertical-align:middle}
    tbody tr:hover{background:#fcfdfc}
    tbody tr:last-child td{border-bottom:0}
    .product-cell{display:flex;align-items:center;gap:10px;min-width:210px}
    .thumb{width:38px;height:38px;border-radius:10px;background:linear-gradient(145deg,#f1faf5,#dcefe3);border:1px solid #d7eadf;display:grid;place-items:center;color:var(--green-800);font-size:10px;font-weight:850;flex:0 0 auto;overflow:hidden;position:relative}
    .thumb img{width:100%;height:100%;object-fit:cover;display:block;border-radius:inherit}
    .image-dropzone{border:2px dashed var(--line);border-radius:12px;padding:14px;text-align:center;background:var(--surface-subtle);cursor:pointer;transition:all .2s ease;display:flex;flex-direction:column;align-items:center;gap:6px;position:relative}
    .image-dropzone:hover,.image-dropzone.dragover{border-color:var(--green-600);background:var(--green-50)}
    .image-dropzone-preview{display:flex;align-items:center;gap:12px;padding:8px 12px;background:#fff;border:1px solid var(--line);border-radius:10px;width:100%}
    .image-dropzone-preview img{width:46px;height:46px;object-fit:cover;border-radius:8px;border:1px solid var(--line-soft);flex-shrink:0}
    .cell-main{font-weight:730;color:var(--ink-900);font-size:11.7px;max-width:250px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .cell-sub{font-size:9.8px;color:var(--ink-500);margin-top:2px;max-width:240px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .badge{display:inline-flex;align-items:center;gap:5px;height:24px;padding:0 8px;border-radius:999px;font-size:9.8px;font-weight:760;border:1px solid var(--line);background:#fff;color:var(--ink-600);white-space:nowrap}
    .badge::before{content:"";width:6px;height:6px;border-radius:50%;background:currentColor;opacity:.75}
    .badge.active,.badge.verified,.badge.published,.badge.approved{color:var(--green-800);background:var(--green-50);border-color:#cdeedb}
    .badge.draft,.badge.pending,.badge.review,.badge.scheduled{color:var(--warning-strong);background:var(--warning-bg);border-color:var(--warning-border)}
    .badge.rejected,.badge.archived,.badge.suspended{color:var(--danger-strong);background:var(--danger-bg);border-color:var(--danger-border)}
    .badge.paused{color:var(--ink-600);background:var(--surface-muted)}
    .badge.ad{color:var(--ad);background:var(--ad-bg);border-color:var(--ad-border)}
    .severity{display:inline-flex;align-items:center;gap:6px;font-weight:760;font-size:10.5px}
    .severity i{width:9px;height:9px;border-radius:3px;display:inline-block}
    .severity.red{color:var(--danger-strong)}.severity.red i{background:var(--danger)}
    .severity.yellow{color:var(--warning-strong)}.severity.yellow i{background:var(--warning)}
    .actions{display:flex;gap:8px;align-items:center;justify-content:flex-end}
    .actions .badge{min-width:84px;width:84px;justify-content:center;text-align:center;box-sizing:border-box}
    .actions .btn-sm{min-width:64px;justify-content:center;text-align:center}
    .table-wrap td .badge.approved,.table-wrap td .badge.review,.table-wrap td .badge.rejected,.table-wrap td .badge.pending,.table-wrap td .badge.published,.table-wrap td .badge.draft,.table-wrap td .badge.active,.table-wrap td .badge.paused{min-width:84px;width:84px;justify-content:center;text-align:center;box-sizing:border-box}
    .mini-btn{width:30px;height:30px;border:1px solid var(--line);border-radius:8px;background:#fff;display:grid;place-items:center;color:var(--ink-600);cursor:pointer}
    .mini-btn:hover{background:var(--surface-muted);color:var(--ink-900)}
    .mini-btn.delete-btn:hover{background:var(--danger-bg);color:var(--danger-strong);border-color:var(--danger-border)}

    .toolbar{display:flex;align-items:center;gap:9px;flex-wrap:wrap;padding:13px 14px;border-bottom:1px solid var(--line-soft)}
    .filter-search{height:36px;min-width:220px;flex:1;max-width:360px;border:1px solid var(--line);border-radius:9px;padding:0 12px;background:#fff;color:var(--ink-900);outline:none}
    .filter-select{height:36px;border:1px solid var(--line);border-radius:9px;padding:0 30px 0 10px;background:#fff;color:var(--ink-800);font-size:11.5px}
    .filter-search:focus,.filter-select:focus{border-color:#b8d8c4;box-shadow:0 0 0 3px rgba(7,148,71,.07)}
    .toolbar-spacer{flex:1}
    .result-count{font-size:10.5px;color:var(--ink-500)}

    .info-strip{border:1px solid #d6e9de;background:var(--green-50);border-radius:13px;padding:12px 14px;display:flex;align-items:flex-start;gap:10px;color:var(--green-900);margin-bottom:15px}
    .info-strip.warning{background:var(--warning-bg);border-color:var(--warning-border);color:#b45309}
    .info-strip.warning .copy strong,.info-strip.warning .copy span{color:#b45309}
    .info-strip.danger{background:var(--danger-bg);border-color:var(--danger-border);color:#dc2626}
    .info-strip.danger .copy strong,.info-strip.danger .copy span{color:#dc2626}
    .info-strip.ad-info{background:var(--ad-bg);border-color:var(--ad-border);color:#4d4cab}
    .info-strip .copy strong{display:block;font-size:11.8px}.info-strip .copy span{display:block;font-size:10.5px;opacity:.82;margin-top:2px}

    .health-safety{display:grid;grid-template-columns:1fr 1fr 1fr;gap:9px;margin-bottom:16px}
    .safety-card{border-radius:12px;border:1px solid;padding:11px 12px}
    .safety-card.red{background:var(--danger-bg);border-color:var(--danger-border)}
    .safety-card.yellow{background:var(--warning-bg);border-color:var(--warning-border)}
    .safety-card.green{background:var(--success-bg);border-color:var(--success-border)}
    .safety-card strong{font-size:11px;display:block}.safety-card span{font-size:9.7px;color:var(--ink-600);display:block;margin-top:2px}

    .workflow-strip{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:9px;margin-bottom:14px}
    .workflow-step{background:var(--surface);border:1px solid var(--line);border-radius:14px;padding:12px;text-align:center}
    .workflow-step .step-icon{width:34px;height:34px;border-radius:11px;background:var(--surface-muted);display:grid;place-items:center;margin:0 auto 7px;color:var(--ink-700)}
    .workflow-step.active .step-icon{background:var(--green-100);color:var(--green-800)}
    .workflow-step strong{display:block;font-size:11px}.workflow-step span{display:block;font-size:18px;font-weight:850;margin-top:3px}

    .product-visual-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
    .product-visual-card{background:var(--surface);border:1px solid var(--line);border-radius:17px;overflow:hidden;box-shadow:var(--shadow-sm);transition:.18s ease}
    .product-visual-card:hover{transform:translateY(-2px);box-shadow:var(--shadow-md)}
    .product-visual-hero{height:105px;background:linear-gradient(145deg,var(--green-50),#dcefe4);display:grid;place-items:center;position:relative;color:var(--green-800);overflow:hidden}
    .product-visual-hero img{width:100%;height:100%;object-fit:cover;position:absolute;inset:0}
    .product-visual-hero .product-glyph{width:54px;height:54px;border-radius:18px;background:rgba(255,255,255,.8);display:grid;place-items:center;box-shadow:var(--shadow-sm);position:relative;z-index:1}
    .product-visual-hero .corner-badge{position:absolute;top:10px;right:10px;z-index:2}
    .product-visual-body{padding:13px}.product-visual-body h4{font-size:13px;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.product-visual-body p{font-size:10.5px;margin:4px 0 0;color:var(--ink-500);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .product-visual-meta{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-top:12px}.product-visual-actions{display:flex;gap:6px}

    .compact-list{display:grid;gap:9px}
    .compact-row{display:grid;grid-template-columns:auto minmax(0,1fr) auto auto;align-items:center;gap:11px;padding:10px 12px;border:1px solid var(--line);border-radius:13px;background:var(--surface)}
    .compact-row-icon{width:34px;height:34px;border-radius:11px;background:var(--surface-muted);display:grid;place-items:center;color:var(--ink-700)}
    .compact-row-main{min-width:0}.compact-row-main strong{display:block;font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.compact-row-main span{display:block;font-size:10px;color:var(--ink-500);margin-top:2px}
    .compact-score{font-size:11px;font-weight:800;color:var(--ink-600)}

    .split-panel{display:grid;grid-template-columns:minmax(0,1.45fr) minmax(300px,.55fr);gap:16px;align-items:start}
    .phone-shell{width:280px;max-width:100%;background:#101415;border-radius:34px;padding:8px;margin:0 auto;box-shadow:0 18px 40px rgba(17,24,32,.18)}
    .phone-screen{background:#fff;min-height:540px;border-radius:27px;overflow:hidden;position:relative}
    .phone-status{height:24px;padding:7px 14px 0;display:flex;justify-content:space-between;font-size:8px;font-weight:800;color:#111}
    .phone-header{height:48px;border-bottom:1px solid var(--line-soft);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:760;color:var(--green-800);position:relative}
    .phone-body{padding:12px;background:#fbfcfb;min-height:458px}
    .phone-product{display:flex;gap:9px;align-items:center;margin-bottom:10px}.phone-product .thumb{width:42px;height:50px;border-radius:7px}.phone-product strong{display:block;font-size:10px}.phone-product span{font-size:8px;color:var(--ink-500)}
    .health-preview{border-radius:11px;padding:10px;border:1px solid;margin:8px 0}.health-preview.red{background:var(--danger-bg);border-color:var(--danger-border)}.health-preview.yellow{background:var(--warning-bg);border-color:var(--warning-border)}.health-preview.green{background:var(--success-bg);border-color:var(--success-border)}
    .health-preview .prelabel{font-size:7px;font-weight:850;text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px}.health-preview strong{display:block;font-size:9px}.health-preview p{margin:3px 0 0;font-size:7.7px;line-height:1.35;color:var(--ink-600)}
    .ad-preview{border:1px solid var(--ad-border);background:linear-gradient(145deg,#faf9ff,var(--ad-bg));border-radius:12px;padding:10px;margin:9px 0;position:relative}.ad-label{font-size:6.5px;text-transform:uppercase;font-weight:900;letter-spacing:.65px;color:var(--ad);margin-bottom:5px;display:flex;align-items:center;gap:4px}.ad-label:before{content:"";width:5px;height:5px;border-radius:50%;background:var(--ad)}.ad-preview strong{font-size:9px;display:block;color:#33326f}.ad-preview p{font-size:7.6px;color:#5c5b7f;margin:3px 0 7px}.ad-cta{height:24px;border:0;border-radius:7px;padding:0 9px;background:var(--ad);color:#fff;font-size:7.5px;font-weight:800}.phone-placement{font-size:7px;color:var(--ink-400);margin-top:4px;text-align:center}

    .form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.field{min-width:0}.field.full{grid-column:1/-1}.field label{display:block;font-size:10px;font-weight:760;color:var(--ink-700);margin-bottom:5px}.field .hint{font-size:9.5px;color:var(--ink-500);margin-top:4px}.input,.select,.textarea{width:100%;border:1px solid var(--line);border-radius:9px;background:#fff;color:var(--ink-900);outline:none}.input,.select{height:38px;padding:0 10px}.textarea{min-height:84px;padding:9px 10px;resize:vertical}.input:focus,.select:focus,.textarea:focus{border-color:#afd5bd;box-shadow:0 0 0 3px rgba(7,148,71,.07)}
    .checkbox-row{display:flex;gap:9px;align-items:flex-start}.checkbox-row input{margin-top:2px;accent-color:var(--green-700)}.checkbox-row label{font-size:10.5px;color:var(--ink-700)}
    .section-label{font-size:10px;font-weight:850;text-transform:uppercase;letter-spacing:.6px;color:var(--ink-400);margin:18px 0 8px}

    .toggle{position:relative;width:38px;height:22px;border-radius:999px;border:0;background:#cfd4d1;padding:0;transition:background .15s}.toggle::after{content:"";position:absolute;width:16px;height:16px;left:3px;top:3px;background:#fff;border-radius:50%;box-shadow:0 1px 3px rgba(0,0,0,.18);transition:transform .16s}.toggle.on{background:var(--green-700)}.toggle.on::after{transform:translateX(16px)}.toggle.ad-on{background:var(--ad)}
    .control-list{display:flex;flex-direction:column}.control-row{display:flex;align-items:center;gap:14px;padding:14px 0;border-bottom:1px solid var(--line-soft)}.control-row:last-child{border-bottom:0}.control-row .ci{width:36px;height:36px;border-radius:10px;background:var(--surface-muted);display:grid;place-items:center;color:var(--ink-600);flex:0 0 auto}.control-copy{flex:1;min-width:0}.control-copy strong{font-size:11.7px;display:block}.control-copy p{font-size:9.8px;color:var(--ink-500);margin:2px 0 0}.control-value{display:flex;align-items:center;gap:8px}

    .modal-backdrop{position:fixed;inset:0;background:rgba(17,24,32,.45);z-index:100;display:flex;align-items:center;justify-content:center;padding:22px;animation:fadeIn .16s ease}
    .modal{width:min(760px,100%);max-height:min(88vh,900px);background:#fff;border-radius:18px;box-shadow:var(--shadow-lg);display:flex;flex-direction:column;overflow:hidden;animation:modalUp .18s ease}
    .modal.wide{width:min(1040px,100%)}.modal.narrow{width:min(560px,100%)}
    .modal-header{padding:16px 18px;border-bottom:1px solid var(--line-soft);display:flex;align-items:flex-start;gap:12px;justify-content:space-between}.modal-header h3{margin:0;font-size:16px;letter-spacing:-.2px}.modal-header p{margin:3px 0 0;color:var(--ink-500);font-size:10.5px}.modal-close{width:32px;height:32px;border:1px solid var(--line);background:#fff;border-radius:9px;display:grid;place-items:center;color:var(--ink-600);flex:0 0 auto}
    .modal-body{padding:17px 18px;overflow:auto}.modal-footer{padding:13px 18px;border-top:1px solid var(--line-soft);display:flex;align-items:center;justify-content:flex-end;gap:8px;background:#fdfefd}
    .modal-split{display:grid;grid-template-columns:minmax(0,1fr) 330px;gap:18px;align-items:start}
    @keyframes fadeIn{from{opacity:0}to{opacity:1}}@keyframes modalUp{from{opacity:.7;transform:translateY(8px) scale(.99)}to{opacity:1;transform:none}}

    .toast-stack{position:fixed;right:20px;bottom:20px;z-index:130;display:flex;flex-direction:column;gap:8px;pointer-events:none}.toast{min-width:280px;max-width:380px;background:#1d252b;color:#fff;border-radius:11px;padding:11px 13px;box-shadow:var(--shadow-md);display:flex;align-items:flex-start;gap:9px;animation:toastIn .2s ease;pointer-events:auto}.toast.success{background:#155e36}.toast.warning{background:#7e5b0c}.toast.danger{background:#8d2630}.toast .toast-copy strong{font-size:11.5px;display:block}.toast .toast-copy span{font-size:9.8px;opacity:.84;display:block;margin-top:1px}@keyframes toastIn{from{transform:translateY(10px);opacity:0}to{transform:none;opacity:1}}
    .tag-row{display:flex;flex-wrap:wrap;gap:5px;margin-top:12px}.tag{height:22px;display:inline-flex;align-items:center;padding:0 7px;background:var(--surface-muted);border:1px solid var(--line-soft);border-radius:7px;font-size:9.5px;color:var(--ink-600)}
    .tabs{display:flex;gap:5px;border-bottom:1px solid var(--line);padding:0 14px;overflow:auto;background:#fff}.tab{height:43px;border:0;background:transparent;color:var(--ink-500);font-size:11px;font-weight:700;padding:0 11px;border-bottom:2px solid transparent;white-space:nowrap;text-decoration:none;display:flex;align-items:center}.tab.active{color:var(--green-800);border-bottom-color:var(--green-700)}
    .master-tabs{display:flex;gap:7px;flex-wrap:wrap;margin-bottom:14px}.master-tab{height:32px;padding:0 10px;border:1px solid var(--line);background:#fff;border-radius:8px;color:var(--ink-600);font-size:10.5px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center}.master-tab.active{background:var(--green-50);color:var(--green-800);border-color:#cdeedb}

    .notice{font-size:9.7px;color:var(--ink-500);display:flex;align-items:flex-start;gap:7px;padding:9px 10px;border:1px dashed var(--line);border-radius:9px;background:var(--surface-subtle)}
    .metric-row{display:flex;align-items:center;gap:10px}.confidence{width:72px;height:7px;border-radius:999px;background:var(--surface-muted);overflow:hidden;border:1px solid var(--line-soft)}.confidence>span{display:block;height:100%;border-radius:inherit;background:var(--green-600)}
    .confidence.low>span{background:var(--warning)}
    .confidence-value{font-size:10px;font-weight:750;color:var(--ink-700);min-width:32px}

    /* Nutrient Box & Dynamic Grid System */
    .nutrient-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:14px}
    .nutrient-box{background:#fff;border:1px solid var(--line);border-radius:12px;padding:8px 10px 10px;position:relative;transition:border-color .18s ease,box-shadow .18s ease,transform .15s ease;display:flex;flex-direction:column;justify-content:space-between}
    .nutrient-box:hover{border-color:#cbd5e1;box-shadow:0 3px 8px rgba(17,24,32,.04)}
    .nutrient-box:focus-within{border-color:var(--green-600);box-shadow:0 0 0 3px rgba(7,148,71,.08)}
    .nutrient-box-header{display:flex;align-items:center;justify-content:space-between;gap:4px;margin-bottom:6px}
    .nutrient-name-input{font-size:11px;font-weight:750;color:var(--ink-800);background:transparent;border:1px solid transparent;border-radius:6px;padding:3px 5px;width:100%;min-width:0;box-sizing:border-box;transition:all .15s ease;font-family:inherit}
    .nutrient-name-input:hover{background:var(--surface-subtle);border-color:var(--line-soft)}
    .nutrient-name-input:focus{background:#fff;border-color:var(--green-700);outline:none;box-shadow:0 0 0 2px rgba(7,148,71,.12)}
    .nutrient-remove-btn{width:22px;height:22px;border-radius:6px;border:1px solid transparent;background:transparent;color:var(--ink-400);cursor:pointer;display:inline-flex;align-items:center;justify-content:center;font-size:15px;font-weight:700;line-height:1;padding:0;flex-shrink:0;transition:all .15s cubic-bezier(.4,0,.2,1)}
    .nutrient-remove-btn:hover{background:#fee2e2;border-color:#fca5a5;color:#b91c1c;transform:scale(1.1)}
    .btn-add-nutrient{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;padding:4px 10px;border-radius:999px;background:var(--surface-subtle);color:var(--ink-700);border:1px solid var(--line);cursor:pointer;transition:all .15s ease}
    .btn-add-nutrient:hover{background:var(--green-50);color:var(--green-800);border-color:#a7f3d0;transform:translateY(-1px)}
    .nutrient-name-error{color:#dc2626;font-size:10px;font-weight:650;margin:2px 0 5px;line-height:1.2;display:none}
    @media (max-width:900px){.nutrient-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media (max-width:540px){.nutrient-grid{grid-template-columns:1fr}}

    @media (max-width:1200px){.visual-kpi-grid{grid-template-columns:repeat(2,1fr)}.graphical-overview{grid-template-columns:1fr 1fr}.graphical-overview>.card:first-child{grid-column:1/-1}.product-visual-grid{grid-template-columns:repeat(2,1fr)}}
    @media (max-width:900px){:root{--sidebar:260px}.sidebar{transform:translateX(-100%);box-shadow:var(--shadow-lg)}body.sidebar-open .sidebar{transform:translateX(0)}.workspace{margin-left:0;width:100%}.mobile-menu{display:flex}.topbar{padding:0 16px}.top-title{min-width:0}.global-search{max-width:none}.main-content{padding:18px}}
    @media (max-width:640px){.topbar{height:64px}.brand{height:64px}.top-title{display:none}.visual-kpi-grid,.graphical-overview,.product-visual-grid{grid-template-columns:1fr}}
  </style>
</head>
<body>
  @php
    $navCounts = [
      'products' => \App\Models\Product::count(),
      'corrections' => \App\Models\ProductCorrection::count(),
      'pending_corrections' => \App\Models\ProductCorrection::where('status', 'Pending')->count(),
      'submissions' => \App\Models\ProductSubmission::whereIn('status', ['Pending', 'Review'])->count(),
      'duplicates' => \App\Models\ProductDuplicate::where('status', '!=', 'Resolved')->count(),
      'verification' => \App\Models\Product::where('verified', false)->where('status', '!=', 'Archived')->count(),
    ];
  @endphp

  <div class="app-shell">
    <!-- Sidebar Navigation -->
    <aside id="sidebar" class="sidebar">
      <div class="brand">
        <div class="brand-mark">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12h4l2.3-5 4.4 10 2.2-5H21"/></svg>
        </div>
        <div class="brand-copy">
          <strong>ScanWell</strong>
          <span>Admin Control Center</span>
        </div>
      </div>

      @php
        $currentAdmin = auth('admin')->user();
        $adminInitials = 'SW';
        if ($currentAdmin && !empty($currentAdmin->name)) {
            $words = explode(' ', trim($currentAdmin->name));
            $adminInitials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
        }
      @endphp

      <nav class="nav-wrap">
        <div class="nav-label">Overview</div>
        <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
          <span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg></span>
          <span class="nav-text">Dashboard</span>
        </a>

        @if($currentAdmin && $currentAdmin->canAccessModule('products'))
        <div class="nav-label">Product Management</div>
        <a href="{{ route('admin.products.index') }}" class="nav-item {{ request()->routeIs('admin.products.index') ? 'active' : '' }}">
          <span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 8l-9 5-9-5 9-5 9 5z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/></svg></span>
          <span class="nav-text">All Products</span>
          <span class="nav-count">{{ $navCounts['products'] }}</span>
        </a>
        <a href="{{ route('admin.products.details') }}" class="nav-item {{ request()->routeIs('admin.products.details') ? 'active' : '' }}">
          <span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1.5 12s4-7 10.5-7 10.5 7 10.5 7-4 7-10.5 7S1.5 12 1.5 12z"/><circle cx="12" cy="12" r="3"/></svg></span>
          <span class="nav-text">Product Details</span>
        </a>
        @if($currentAdmin->canAccessModule('corrections'))
        <a href="{{ route('admin.corrections.index') }}" class="nav-item {{ request()->routeIs('admin.corrections.index') ? 'active' : '' }}">
          <span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4z"/></svg></span>
          <span class="nav-text">Product Corrections</span>
          <span class="nav-count">{{ $navCounts['corrections'] }}</span>
        </a>
        <a href="{{ route('admin.corrections.pending') }}" class="nav-item {{ request()->routeIs('admin.corrections.pending') ? 'active' : '' }}">
          <span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
          <span class="nav-text">Pending Corrections</span>
          @if($navCounts['pending_corrections'] > 0)
            <span class="nav-count" style="background:var(--warning-bg);color:var(--warning-strong)">{{ $navCounts['pending_corrections'] }}</span>
          @endif
        </a>
        @endif
        @if($currentAdmin->canAccessModule('submissions'))
        <a href="{{ route('admin.submissions.index') }}" class="nav-item {{ request()->routeIs('admin.submissions.index') ? 'active' : '' }}">
          <span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7V5a1 1 0 0 1 1-1h2M17 4h2a1 1 0 0 1 1 1v2M20 17v2a1 1 0 0 1-1 1h-2M7 20H5a1 1 0 0 1-1-1v-2"/><path d="M7 12h10M8 9v6M11 9v6M14 9v6M17 9v6"/></svg></span>
          <span class="nav-text">OCR / Product Review</span>
          <span class="nav-count">{{ $navCounts['submissions'] }}</span>
        </a>
        @endif
        @if($currentAdmin->canAccessModule('duplicates'))
        <a href="{{ route('admin.duplicates.index') }}" class="nav-item {{ request()->routeIs('admin.duplicates.index') ? 'active' : '' }}">
          <span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M15 9V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h4"/></svg></span>
          <span class="nav-text">Duplicate Products</span>
          <span class="nav-count">{{ $navCounts['duplicates'] }}</span>
        </a>
        @endif
        @if($currentAdmin->hasPermission('products.manage'))
        <a href="{{ route('admin.products.verification') }}" class="nav-item {{ request()->routeIs('admin.products.verification') ? 'active' : '' }}">
          <span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg></span>
          <span class="nav-text">Verification Queue</span>
          <span class="nav-count">{{ $navCounts['verification'] }}</span>
        </a>
        @endif
        @endif

        @if($currentAdmin && ($currentAdmin->canAccessModule('health') || $currentAdmin->canAccessModule('marketing')))
        <div class="nav-label">Health & Content</div>
        @if($currentAdmin->canAccessModule('health'))
        <a href="{{ route('admin.health.rules') }}" class="nav-item {{ request()->routeIs('admin.health.rules') ? 'active' : '' }}">
          <span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 12h4l2-5 4 10 2-5h6"/></svg></span>
          <span class="nav-text">Health Intelligence</span>
        </a>
        <a href="{{ route('admin.health.concerns') }}" class="nav-item {{ request()->routeIs('admin.health.concerns') ? 'active' : '' }}">
          <span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.8 4.6a5.4 5.4 0 0 0-7.6 0L12 5.8l-1.2-1.2a5.4 5.4 0 0 0-7.6 7.6L12 21l8.8-8.8a5.4 5.4 0 0 0 0-7.6z"/></svg></span>
          <span class="nav-text">Health Concerns</span>
        </a>
        <a href="{{ route('admin.health.alerts') }}" class="nav-item {{ request()->routeIs('admin.health.alerts') ? 'active' : '' }}">
          <span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg></span>
          <span class="nav-text">Personalized Alerts</span>
        </a>
        @endif
        @if($currentAdmin->canAccessModule('marketing'))
        <a href="{{ route('admin.ads.index') }}" class="nav-item {{ request()->routeIs('admin.ads.index') ? 'active' : '' }}">
          <span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 11v2a2 2 0 0 0 2 2h2l4 4V5L7 9H5a2 2 0 0 0-2 2zM11 8l8-3v14l-8-3M7 15v4"/></svg></span>
          <span class="nav-text">Ads & Promotions</span>
        </a>
        <a href="{{ route('admin.content.index') }}" class="nav-item {{ request()->routeIs('admin.content.index') ? 'active' : '' }}">
          <span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2h8l4 4v16H6z"/><path d="M14 2v5h5M9 13h6M9 17h6M9 9h2"/></svg></span>
          <span class="nav-text">App Content</span>
        </a>
        <a href="{{ route('admin.notifications.index') }}" class="nav-item {{ request()->routeIs('admin.notifications.index') ? 'active' : '' }}">
          <span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/></svg></span>
          <span class="nav-text">Notifications</span>
        </a>
        @endif
        @endif

        @if($currentAdmin && ($currentAdmin->canAccessModule('users') || $currentAdmin->canAccessModule('master_data') || $currentAdmin->canAccessModule('app_control') || $currentAdmin->canAccessModule('security')))
        <div class="nav-label">Operations</div>
        @if($currentAdmin->canAccessModule('users'))
        <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
          <span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
          <span class="nav-text">Users & Contributors</span>
        </a>
        @endif
        @if($currentAdmin->canAccessModule('master_data'))
        @php
          $masterTaxonomies = [
            'Categories',
            'Brands',
            'Countries',
            'Nutrients',
            'Units',
            'Ingredients',
            'Additives',
            'Allergens',
            'Health Concerns',
          ];
          $isMasterDataRoute = request()->routeIs('admin.masterData.*');
          $activeMasterTab = request('tab', 'Categories');
        @endphp
        <div class="nav-group {{ $isMasterDataRoute ? 'open active' : '' }}" id="masterDataNavGroup">
          <button type="button" class="nav-item nav-item-parent {{ $isMasterDataRoute ? 'active' : '' }}" onclick="toggleNavGroup('masterDataNavGroup')" aria-expanded="{{ $isMasterDataRoute ? 'true' : 'false' }}">
            <span class="nav-icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <rect x="3" y="3" width="18" height="18" rx="2"/>
                <path d="M7 8h10M7 12h10M7 16h10"/>
              </svg>
            </span>
            <span class="nav-text">Master Data</span>
            <span class="nav-chevron">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
            </span>
          </button>
          <div class="nav-submenu">
            <ul class="submenu-tree">
              @foreach($masterTaxonomies as $tax)
                @php
                  $isTaxActive = $isMasterDataRoute && ($activeMasterTab === $tax);
                @endphp
                <li class="submenu-tree-item">
                  <a href="{{ route('admin.masterData.index', ['tab' => $tax]) }}" class="submenu-link {{ $isTaxActive ? 'active' : '' }}">
                    <span class="tree-bullet">·</span>
                    <span class="submenu-text">{{ $tax }}</span>
                  </a>
                </li>
              @endforeach
            </ul>
          </div>
        </div>
        @endif
        @if($currentAdmin->canAccessModule('app_control'))
        <a href="{{ route('admin.appControl.index') }}" class="nav-item {{ request()->routeIs('admin.appControl.index') ? 'active' : '' }}">
          <span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3"/><path d="M1 14h6M9 8h6M17 16h6"/></svg></span>
          <span class="nav-text">App Control</span>
        </a>
        @endif
        @if($currentAdmin->canAccessModule('security'))
        <a href="{{ route('admin.security.index') }}" class="nav-item {{ request()->routeIs('admin.security.index') ? 'active' : '' }}">
          <span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg></span>
          <span class="nav-text">Admin & Security</span>
        </a>
        @endif
        @endif
      </nav>

      <div class="sidebar-footer">
        <div class="admin-chip">
          <div class="avatar">{{ $adminInitials }}</div>
          <div class="meta">
            <strong>{{ $currentAdmin->name ?? 'Admin Staff' }}</strong>
            <span>{{ $currentAdmin->role ?? 'Security Reviewer' }}</span>
          </div>
          <form action="{{ route('admin.logout') }}" method="POST" style="margin:0;display:inline;">
            @csrf
            <button type="submit" class="icon-button" style="width:30px;height:30px;border-radius:8px;border:none;background:var(--surface-muted);" title="Sign out terminal" onclick="return confirm('Sign out of ScanWell Admin Portal?')">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
            </button>
          </form>
        </div>
      </div>
    </aside>

    <div id="sidebarOverlay" class="sidebar-overlay" hidden onclick="closeSidebar()"></div>

    <!-- Main Workspace -->
    <section class="workspace">
      <header class="topbar">
        <button class="mobile-menu" type="button" onclick="openSidebar()" aria-label="Open menu">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>

        <div class="top-title">
          <div class="breadcrumbs">ScanWell Admin / <span>@yield('breadcrumb', 'Dashboard')</span></div>
          <h1>@yield('page_title', 'Dashboard')</h1>
        </div>

        <!-- Global Search ⌘K -->
        <div class="global-search">
          <span class="search-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg>
          </span>
          <input id="globalSearch" type="search" placeholder="Search products, campaigns, rules, users…" autocomplete="off" aria-label="Global search" />
          <span class="shortcut">⌘K</span>
          <div id="searchResults" class="search-results" hidden></div>
        </div>

        <div class="top-actions">
          <button class="icon-button" type="button" title="Documentation" onclick="showToast('ScanWell Admin is connected to MySQL database scanwell.', 'info', 'System Notice')">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.7 2.7 0 1 1 4.8 1.7c-.9.9-2.3 1.3-2.3 3.3M12 18h.01"/></svg>
          </button>
          <button class="icon-button" type="button" title="Alerts" onclick="showToast('{{ $navCounts['pending_corrections'] + $navCounts['submissions'] }} items waiting for admin review.', 'warning', 'Review Queue')">
            <span class="notification-dot"></span>
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg>
          </button>
          <form action="{{ route('admin.logout') }}" method="POST" style="margin:0;display:inline;">
            @csrf
            <button type="submit" class="icon-button" style="color:var(--danger);" title="Sign out ({{ $currentAdmin->name ?? 'Admin' }})" onclick="return confirm('Sign out of ScanWell Admin Portal?')">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
            </button>
          </form>
        </div>
      </header>

      <main class="main-content">
        @if(session('status_type') === 'rejected' || session('rejected'))
          <div class="info-strip danger" style="margin-bottom:18px;background:var(--danger-bg);border:1px solid var(--danger-border);color:#dc2626">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" style="flex-shrink:0"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            <div class="copy" style="color:#dc2626">
              <strong style="color:#dc2626;font-size:11.8px;display:block">{{ session('status_title') ?? 'Rejected' }}</strong>
              <span style="color:#dc2626;font-size:10.5px;display:block;margin-top:2px">{{ session('status_message') ?? session('rejected') }}</span>
            </div>
          </div>
        @elseif(session('status_type') === 'review' || session('review'))
          <div class="info-strip warning" style="margin-bottom:18px;background:var(--warning-bg);border:1px solid var(--warning-border);color:#b45309">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#b45309" stroke-width="2" style="flex-shrink:0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <div class="copy" style="color:#b45309">
              <strong style="color:#b45309;font-size:11.8px;display:block">{{ session('status_title') ?? 'Under Review' }}</strong>
              <span style="color:#b45309;font-size:10.5px;display:block;margin-top:2px">{{ session('status_message') ?? session('review') }}</span>
            </div>
          </div>
        @elseif(session('success'))
          <div class="info-strip" style="margin-bottom:18px">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12l4 4L19 6"/></svg>
            <div class="copy">
              <strong style="font-size:11.8px;display:block">{{ session('status_title') ?? 'Success' }}</strong>
              <span style="font-size:10.5px;display:block;margin-top:2px">{{ session('status_message') ?? session('success') }}</span>
            </div>
          </div>
        @elseif(session('error'))
          <div class="info-strip danger" style="margin-bottom:18px;background:var(--danger-bg);border:1px solid var(--danger-border);color:#dc2626">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" style="flex-shrink:0"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            <div class="copy" style="color:#dc2626">
              <strong style="color:#dc2626;font-size:11.8px;display:block">Error</strong>
              <span style="color:#dc2626;font-size:10.5px;display:block;margin-top:2px">{{ session('error') }}</span>
            </div>
          </div>
        @elseif(session('warning'))
          <div class="info-strip warning" style="margin-bottom:18px;background:var(--warning-bg);border:1px solid var(--warning-border);color:#b45309">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#b45309" stroke-width="2" style="flex-shrink:0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <div class="copy" style="color:#b45309">
              <strong style="color:#b45309;font-size:11.8px;display:block">Warning</strong>
              <span style="color:#b45309;font-size:10.5px;display:block;margin-top:2px">{{ session('warning') }}</span>
            </div>
          </div>
        @elseif(session('info'))
          <div class="info-strip ad-info" style="margin-bottom:18px">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            <div class="copy">
              <strong style="font-size:11.8px;display:block">Information</strong>
              <span style="font-size:10.5px;display:block;margin-top:2px">{{ session('info') }}</span>
            </div>
          </div>
        @endif

        @if($errors->any())
          <div id="pageValidationAlert" class="info-strip warning" style="margin-bottom:18px;background:var(--danger-bg);border-color:var(--danger-border);color:var(--danger-strong)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3L2 20h20L12 3z"/><path d="M12 9v4M12 17h.01"/></svg>
            <div class="copy">
              <strong>Validation Feedback</strong>
              <ul style="margin:4px 0 0;padding-left:16px">
                @foreach($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          </div>
        @endif

        @yield('content')
      </main>
    </section>
  </div>

  <!-- Dynamic Modal Root -->
  <div id="modalRoot"></div>

  <!-- Toast Stack -->
  <div id="toastRoot" class="toast-stack" aria-live="polite"></div>

  <script>
    function openSidebar(){document.body.classList.add('sidebar-open');document.getElementById('sidebarOverlay').hidden=false;}
    function closeSidebar(){document.body.classList.remove('sidebar-open');document.getElementById('sidebarOverlay').hidden=true;}

    function toggleNavGroup(id){
      const group = document.getElementById(id);
      if(group){
        const isOpen = group.classList.toggle('open');
        const btn = group.querySelector('.nav-item-parent');
        if(btn) btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      }
    }

    // Dynamic Modal System
    function openModal({title, subtitle='', body='', footer='', size=''}){
      document.getElementById('modalRoot').innerHTML = `
        <div class="modal-backdrop" onclick="if(event.target===this)closeModal()">
          <section class="modal ${size}" role="dialog" aria-modal="true">
            <div class="modal-header">
              <div>
                <h3>${title}</h3>
                ${subtitle ? `<p>${subtitle}</p>` : ''}
              </div>
              <button class="modal-close" type="button" onclick="closeModal()" aria-label="Close dialog">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg>
              </button>
            </div>
            <div class="modal-body">${body}</div>
            ${footer ? `<div class="modal-footer">${footer}</div>` : ''}
          </section>
        </div>
      `;

      const form = document.querySelector('#modalRoot form');
      if (form) {
        attachNutritionCounter(form);
        attachProductNameValidation(form);
      }
    }
    function closeModal(){document.getElementById('modalRoot').innerHTML = '';}

    // Dynamic Live Nutrition Box & Field Counter
    function updateNutritionBadge(form) {
      if (!form) return;
      const badge = form.querySelector('.nutrition-counter-badge');
      if (!badge) return;

      const boxes = form.querySelectorAll('.nutrient-box');
      if (boxes.length > 0) {
        const total = boxes.length;
        let filled = 0;
        boxes.forEach(box => {
          const nameInp = box.querySelector('.nutrient-name-input');
          const valInp = box.querySelector('.nutrient-value-input');
          if (nameInp && valInp) {
            const nv = nameInp.value.trim();
            const vv = valInp.value.trim();
            if (nv !== '' && vv !== '' && !isNaN(vv) && Number(vv) >= 0) {
              filled++;
            }
          }
        });

        if (filled >= 3) {
          const oldNutrientErr = form.querySelector('.nutrient-form-error');
          if (oldNutrientErr) oldNutrientErr.remove();
          badge.style.background = '#dcfce7';
          badge.style.color = '#15803d';
          badge.style.border = '1px solid #86efac';
          badge.innerHTML = `✓ ${filled} OF ${total} FILLED (READY)`;
        } else if (filled > 0) {
          badge.style.background = '#fef3c7';
          badge.style.color = '#b45309';
          badge.style.border = '1px solid #fde68a';
          badge.innerHTML = `⚠️ ${filled} OF ${total} FILLED (${3 - filled} MORE REQUIRED)`;
        } else {
          badge.style.background = '#f1f5f9';
          badge.style.color = '#64748b';
          badge.style.border = '1px solid #cbd5e1';
          badge.innerHTML = `Any 3 nutrient boxes required`;
        }
        return;
      }

      // Legacy static fields fallback
      const nutrientFields = ['sodium', 'sugar', 'added_sugar', 'saturated_fat', 'trans_fat', 'calories', 'fat', 'protein', 'fiber', 'carbohydrate'];
      const inputs = nutrientFields.map(name => form.querySelector(`input[name="${name}"]`)).filter(Boolean);
      if (inputs.length === 0) return;

      const filled = inputs.filter(i => {
        const v = i.value.trim();
        return v !== '' && !isNaN(v) && Number(v) >= 0;
      }).length;

      if (filled >= 3) {
        badge.style.background = '#dcfce7';
        badge.style.color = '#15803d';
        badge.style.border = '1px solid #86efac';
        badge.innerHTML = `✓ ${filled} OF ${inputs.length} FILLED (READY)`;
      } else if (filled > 0) {
        badge.style.background = '#fef3c7';
        badge.style.color = '#b45309';
        badge.style.border = '1px solid #fde68a';
        badge.innerHTML = `⚠️ ${filled} OF ${inputs.length} FILLED (${3 - filled} MORE REQUIRED)`;
      } else {
        badge.style.background = '#f1f5f9';
        badge.style.color = '#64748b';
        badge.style.border = '1px solid #cbd5e1';
        badge.innerHTML = `Any 3 fields required`;
      }
    }

    function normalizeNutrientKey(name) {
      if (!name) return '';
      const base = name.toLowerCase().replace(/\s*\([^)]*\)/g, '').trim();
      const clean = base.replace(/[\s_\-]/g, '');
      return clean || name.toLowerCase().trim().replace(/[\s_\-]/g, '');
    }

    function validateNutrientNames(form) {
      if (!form) return false;
      const boxes = form.querySelectorAll('.nutrient-box');
      if (boxes.length === 0) return false;

      const nameMap = {};
      boxes.forEach(box => {
        const nameInp = box.querySelector('.nutrient-name-input');
        if (nameInp) {
          const raw = nameInp.value.trim();
          if (raw !== '') {
            const key = normalizeNutrientKey(raw);
            if (!nameMap[key]) nameMap[key] = [];
            nameMap[key].push({ box, nameInp, raw });
          }
        }
      });

      let hasDuplicates = false;

      boxes.forEach(box => {
        const nameInp = box.querySelector('.nutrient-name-input');
        let errEl = box.querySelector('.nutrient-name-error');
        if (!errEl) {
          errEl = document.createElement('div');
          errEl.className = 'nutrient-name-error';
          errEl.style.color = '#dc2626';
          errEl.style.fontSize = '10px';
          errEl.style.fontWeight = '600';
          errEl.style.margin = '2px 0 5px';
          errEl.style.display = 'none';
          const header = box.querySelector('.nutrient-box-header');
          if (header) {
            header.insertAdjacentElement('afterend', errEl);
          } else {
            box.prepend(errEl);
          }
        }

        if (nameInp) {
          const raw = nameInp.value.trim();
          const key = normalizeNutrientKey(raw);
          if (raw !== '' && nameMap[key] && nameMap[key].length > 1) {
            errEl.textContent = 'The nutrient already exists';
            errEl.style.display = 'block';
            nameInp.style.borderColor = '#dc2626';
            box.style.borderColor = '#fca5a5';
            hasDuplicates = true;
          } else {
            errEl.textContent = '';
            errEl.style.display = 'none';
            nameInp.style.borderColor = '';
            box.style.borderColor = '';
          }
        }
      });

      return hasDuplicates;
    }

    function attachNutritionCounter(form) {
      if (!form) return;
      updateNutritionBadge(form);
      validateNutrientNames(form);

      form.addEventListener('input', function(e) {
        if (e.target.matches('.nutrient-name-input')) {
          validateNutrientNames(form);
          updateNutritionBadge(form);
        } else if (e.target.matches('.nutrient-value-input, input[name="sodium"], input[name="sugar"], input[name="calories"], input[name="added_sugar"], input[name="fat"], input[name="saturated_fat"], input[name="trans_fat"], input[name="protein"]')) {
          updateNutritionBadge(form);
        }
      });
    }

    function addNutrientBox(triggerBtn, initialName = '', initialVal = '', placeholder = 'e.g. 0') {
      const modal = triggerBtn.closest('.modal') || document.querySelector('#modalRoot .modal');
      const form = triggerBtn.closest('form') || modal?.querySelector('form');
      if (!form) return;
      const grid = form.querySelector('.nutrient-grid');
      if (!grid) return;

      const box = document.createElement('div');
      box.className = 'field nutrient-box';
      box.innerHTML = `
        <div class="nutrient-box-header">
          <input type="text" name="nutrient_names[]" class="nutrient-name-input" value="${initialName}" placeholder="Nutrient Name (unit)" title="Click to edit nutrient name">
          <button type="button" class="nutrient-remove-btn" title="Remove nutrient box" onclick="removeNutrientBox(this)">&times;</button>
        </div>
        <div class="nutrient-name-error" style="color:#dc2626;font-size:10px;font-weight:600;margin:2px 0 5px;display:none;"></div>
        <input type="number" step="any" min="0" name="nutrient_values[]" class="input nutrient-value-input" value="${initialVal}" placeholder="${placeholder}">
      `;
      grid.appendChild(box);

      validateNutrientNames(form);
      updateNutritionBadge(form);

      const nameInp = box.querySelector('.nutrient-name-input');
      if (!initialName) {
        nameInp.focus();
      }
    }

    function removeNutrientBox(btn) {
      const box = btn.closest('.nutrient-box');
      if (!box) return;
      const form = box.closest('form');
      box.remove();
      if (form) {
        validateNutrientNames(form);
        updateNutritionBadge(form);
      }
    }

    // Dynamic Product Name Validation (Inline Uniqueness & Required Check)
    function attachProductNameValidation(form) {
      if (!form) return;
      const nameInput = form.querySelector('input[name="name"]');
      if (!nameInput) return;

      form.setAttribute('novalidate', 'true');

      let errorEl = nameInput.parentNode.querySelector('.product-name-error');
      if (!errorEl) {
        errorEl = document.createElement('div');
        errorEl.className = 'product-name-error';
        errorEl.style.display = 'none';
        errorEl.style.color = '#dc2626';
        errorEl.style.fontSize = '11px';
        errorEl.style.fontWeight = '600';
        errorEl.style.marginTop = '4px';
        nameInput.parentNode.appendChild(errorEl);
      }

      function showError(msg) {
        errorEl.textContent = msg;
        errorEl.style.display = 'block';
        errorEl.dataset.hasError = 'true';
        nameInput.style.borderColor = '#dc2626';
      }

      function clearError() {
        errorEl.textContent = '';
        errorEl.style.display = 'none';
        errorEl.dataset.hasError = 'false';
        nameInput.style.borderColor = '';
      }

      let checkTimer = null;

      async function checkUniqueness() {
        const val = nameInput.value.trim();
        if (!val) {
          showError('Product name must!');
          return;
        }

        const productIdMatch = form.action.match(/\/products\/(\d+)/);
        const ignoreId = productIdMatch ? productIdMatch[1] : '';

        try {
          const res = await fetch(`{{ url('admin/products/check-name') }}?name=${encodeURIComponent(val)}&ignore_id=${ignoreId}`, {
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json'
            }
          });
          if (!res.ok) return;
          const data = await res.json();
          if (data.exists) {
            showError('This product name already exists in the catalog.');
          } else {
            clearError();
          }
        } catch (e) {
          console.error(e);
        }
      }

      nameInput.addEventListener('input', () => {
        clearTimeout(checkTimer);
        const val = nameInput.value.trim();
        if (!val) {
          showError('Product name must!');
          return;
        }
        clearError();
        checkTimer = setTimeout(checkUniqueness, 250);
      });

      nameInput.addEventListener('blur', () => {
        clearTimeout(checkTimer);
        const val = nameInput.value.trim();
        if (!val) {
          showError('Product name must!');
        } else {
          checkUniqueness();
        }
      });
    }

    // AJAX Form Submitter with Inline Error Display & Live Health Engine Re-evaluation
    async function submitProductForm(formId, submitBtn) {
      const form = document.getElementById(formId);
      if (!form) return;

      // Validate Product Name first to display custom inline error under text box
      const nameInput = form.querySelector('input[name="name"]');
      if (nameInput) {
        const nameVal = nameInput.value.trim();
        let errorEl = nameInput.parentNode.querySelector('.product-name-error');
        if (!errorEl) {
          errorEl = document.createElement('div');
          errorEl.className = 'product-name-error';
          errorEl.style.color = '#dc2626';
          errorEl.style.fontSize = '11px';
          errorEl.style.fontWeight = '600';
          errorEl.style.marginTop = '4px';
          nameInput.parentNode.appendChild(errorEl);
        }

        if (!nameVal) {
          errorEl.textContent = 'Product name must!';
          errorEl.style.display = 'block';
          errorEl.dataset.hasError = 'true';
          nameInput.style.borderColor = '#dc2626';
          nameInput.focus();
          return;
        }

        // Live check if name already exists
        const productIdMatch = form.action.match(/\/products\/(\d+)/);
        const ignoreId = productIdMatch ? productIdMatch[1] : '';
        try {
          const res = await fetch(`{{ url('admin/products/check-name') }}?name=${encodeURIComponent(nameVal)}&ignore_id=${ignoreId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
          });
          if (res.ok) {
            const data = await res.json();
            if (data.exists) {
              errorEl.textContent = 'This product name already exists in the catalog.';
              errorEl.style.display = 'block';
              errorEl.dataset.hasError = 'true';
              nameInput.style.borderColor = '#dc2626';
              nameInput.focus();
              return;
            }
          }
        } catch (e) {
          console.error(e);
        }

        if (errorEl.dataset.hasError === 'true') {
          nameInput.focus();
          return;
        }
      }

      if (!form.reportValidity()) {
        return;
      }

      // Check duplicate nutrient names: same name cannot be added
      if (validateNutrientNames(form)) {
        const oldErr = form.querySelector('.modal-form-errors');
        if (oldErr) oldErr.remove();

        const errDiv = document.createElement('div');
        errDiv.className = 'notice modal-form-errors';
        errDiv.style.borderColor = 'var(--danger)';
        errDiv.style.background = 'var(--danger-bg)';
        errDiv.style.color = '#b91c1c';
        errDiv.style.marginBottom = '14px';
        errDiv.innerHTML = `<div><strong>Validation Error:</strong> The nutrient already exists. Each nutrient box must have a unique nutrient name.</div>`;
        form.prepend(errDiv);
        form.scrollTop = 0;

        const firstDup = form.querySelector('.nutrient-box .nutrient-name-error:not([style*="display: none"])');
        if (firstDup) {
          const inp = firstDup.closest('.nutrient-box')?.querySelector('.nutrient-name-input');
          if (inp) inp.focus();
        }
        return;
      }

      // Check nutritional declaration requirements: at least 3 boxes must have valid data
      const nutrientBoxes = form.querySelectorAll('.nutrient-box');
      let filledNutrientsCount = 0;

      if (nutrientBoxes.length > 0) {
        nutrientBoxes.forEach(box => {
          const nInp = box.querySelector('.nutrient-name-input');
          const vInp = box.querySelector('.nutrient-value-input');
          if (nInp && vInp) {
            const nv = nInp.value.trim();
            const vv = vInp.value.trim();
            if (nv !== '' && vv !== '' && !isNaN(vv) && Number(vv) >= 0) {
              filledNutrientsCount++;
            }
          }
        });
      } else {
        const nutrientFields = ['sodium', 'sugar', 'added_sugar', 'saturated_fat', 'trans_fat', 'calories', 'fat', 'protein', 'fiber', 'carbohydrate'];
        const presentNutrientInputs = nutrientFields
          .map(name => form.querySelector(`input[name="${name}"]`))
          .filter(Boolean);

        filledNutrientsCount = presentNutrientInputs.filter(input => {
          const v = input.value.trim();
          return v !== '' && !isNaN(v) && Number(v) >= 0;
        }).length;
      }

      if (filledNutrientsCount < 3) {
        const oldErr = form.querySelector('.nutrient-form-error, .modal-form-errors');
        if (oldErr) oldErr.remove();

        const errDiv = document.createElement('div');
        errDiv.className = 'notice modal-form-errors nutrient-form-error';
        errDiv.style.borderColor = 'var(--danger)';
        errDiv.style.background = 'var(--danger-bg)';
        errDiv.style.color = '#b91c1c';
        errDiv.style.marginBottom = '14px';
        errDiv.innerHTML = `<div><strong>Validation Error:</strong> At least 3 nutritional declaration boxes must be filled with valid numbers (currently ${filledNutrientsCount} filled). Emptied or removed boxes are excluded.</div>`;

        const placeholder = form.querySelector('.nutrient-error-placeholder');
        const targetGrid = form.querySelector('.nutrient-grid');

        if (placeholder) {
          placeholder.appendChild(errDiv);
        } else if (targetGrid) {
          targetGrid.parentNode.insertBefore(errDiv, targetGrid);
        } else {
          form.prepend(errDiv);
        }

        errDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
      }

      const originalText = submitBtn.innerHTML;
      submitBtn.innerHTML = '<span style="display:inline-block;width:14px;height:14px;border:2px solid #fff;border-top-color:transparent;border-radius:50%;animation:spin 0.6s linear infinite;margin-right:6px"></span> Re-evaluating...';
      submitBtn.disabled = true;

      const oldErr = form.querySelector('.nutrient-form-error, .modal-form-errors');
      if (oldErr) oldErr.remove();

      try {
        const formData = new FormData(form);
        const res = await fetch(form.action, {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          },
          body: formData
        });

        const data = await res.json();

        if (!res.ok) {
          submitBtn.innerHTML = originalText;
          submitBtn.disabled = false;
          let msg = data.message || 'Validation error. Please review the highlighted fields.';
          if (data.errors) {
            const list = Object.values(data.errors).flat().map(e => `<li>${e}</li>`).join('');
            msg = `<ul style="margin:4px 0 0 16px;padding:0">${list}</ul>`;
          }
          if (data.errors && data.errors.name && nameInput) {
            let errorEl = nameInput.parentNode.querySelector('.product-name-error');
            if (errorEl) {
              errorEl.textContent = data.errors.name[0];
              errorEl.style.display = 'block';
              errorEl.dataset.hasError = 'true';
              nameInput.style.borderColor = '#dc2626';
              nameInput.focus();
            }
          }

          const isNutrientError = (data.errors && (data.errors.nutrition || data.errors.nutrients)) ||
                                  (data.message && /nutrient|nutritional|nutrition/i.test(data.message));

          const oldErr = form.querySelector('.nutrient-form-error, .modal-form-errors');
          if (oldErr) oldErr.remove();

          const errDiv = document.createElement('div');
          errDiv.className = 'notice modal-form-errors' + (isNutrientError ? ' nutrient-form-error' : '');
          errDiv.style.borderColor = 'var(--danger)';
          errDiv.style.background = 'var(--danger-bg)';
          errDiv.style.color = '#b91c1c';
          errDiv.style.marginBottom = '14px';
          errDiv.innerHTML = `<div><strong>Validation Error:</strong> ${msg}</div>`;

          if (isNutrientError) {
            const placeholder = form.querySelector('.nutrient-error-placeholder');
            const targetGrid = form.querySelector('.nutrient-grid');
            if (placeholder) {
              placeholder.appendChild(errDiv);
            } else if (targetGrid) {
              targetGrid.parentNode.insertBefore(errDiv, targetGrid);
            } else {
              form.prepend(errDiv);
            }
            errDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
          } else {
            form.prepend(errDiv);
            form.scrollTop = 0;
          }
          return;
        }

        closeModal();
        showToast(data.message || 'Product updated and health flags re-evaluated.', 'success', 'Health Rules Evaluated');
        setTimeout(() => window.location.reload(), 600);
      } catch (err) {
        console.error(err);
        form.submit();
      }
    }

    // Toast Notifications
    function showToast(message, tone='success', title='Notification'){
      const id = 't' + Date.now();
      const root = document.getElementById('toastRoot');
      const icons = {
        success: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12l4 4L19 6"/></svg>',
        warning: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7h.01"/></svg>',
        danger: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3L2 20h20L12 3z"/><path d="M12 9v4M12 17h.01"/></svg>',
        info: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.7 2.7 0 1 1 4.8 1.7c-.9.9-2.3 1.3-2.3 3.3M12 18h.01"/></svg>'
      };
      root.insertAdjacentHTML('beforeend', `
        <div class="toast ${tone}" id="${id}">
          ${icons[tone] || icons.info}
          <div class="toast-copy">
            <strong>${title}</strong>
            <span>${message}</span>
          </div>
        </div>
      `);
      setTimeout(() => document.getElementById(id)?.remove(), 3500);
    }

    // Global Search ⌘K
    const searchInput = document.getElementById('globalSearch');
    const searchResults = document.getElementById('searchResults');

    document.addEventListener('keydown', e => {
      if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        searchInput.focus();
      }
      if (e.key === 'Escape') {
        searchResults.hidden = true;
        closeModal();
      }
    });

    let debounceTimer;
    searchInput?.addEventListener('input', e => {
      clearTimeout(debounceTimer);
      const query = e.target.value.trim();
      if (query.length < 2) {
        searchResults.hidden = true;
        searchResults.innerHTML = '';
        return;
      }

      debounceTimer = setTimeout(() => {
        fetch(`{{ route('admin.search') }}?q=${encodeURIComponent(query)}`)
          .then(res => res.json())
          .then(data => {
            if (!data.groups || !data.groups.length) {
              searchResults.innerHTML = '<div style="padding:16px;text-align:center;color:var(--ink-500);font-size:11px">No matching records found.</div>';
              searchResults.hidden = false;
              return;
            }

            searchResults.innerHTML = data.groups.map(group => `
              <div class="search-group-title">${group.label}</div>
              ${group.items.map(item => `
                <a href="${item.url}" class="search-hit">
                  <div style="width:28px;height:28px;border-radius:8px;background:var(--green-50);color:var(--green-800);display:grid;place-items:center;flex:none">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 8l-9 5-9-5 9-5 9 5z"/><path d="M3 8v8l9 5 9-5V8"/></svg>
                  </div>
                  <div>
                    <strong>${item.title}</strong>
                    <span>${item.sub}</span>
                  </div>
                </a>
              `).join('')}
            `).join('');
            searchResults.hidden = false;
          })
          .catch(() => {
            searchResults.hidden = true;
          });
      }, 250);
    });

    document.addEventListener('click', e => {
      if (!e.target.closest('.global-search')) {
        searchResults.hidden = true;
      }
    });
  </script>
  @stack('scripts')
</body>
</html>
