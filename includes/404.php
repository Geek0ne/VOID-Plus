<?php
/**
 * 404.php
 *
 * @author      Geek0ne
 * @version     3.6.0 2026-11-11
 *
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$setting = $GLOBALS['VOIDPlusSetting'];
$banner = $setting['defaultBanner'];

// 搜索地址：Utils::index() 是 echo 型且输出绝对 URL，先捕获再转成 JS 字符串
ob_start();
Utils::index('/search/');
$searchBase = ob_get_clean();
?>

<main id="pjax-container" class="void-404">
    <title hidden>
        <?php Contents::title($this); ?>
    </title>

    <div class="void-404-bg">
        <?php if(!empty($banner)): ?>
            <img src="<?php echo Utils::esc($banner); ?>" alt="" />
        <?php endif; ?>
        <div class="void-404-mask"></div>
        <div class="void-404-grid"></div>
        <div class="void-404-watermark" aria-hidden="true">404</div>
    </div>

    <div class="void-404-card">
        <div class="void-404-code">404</div>
        <h1 class="void-404-title">页面走丢了</h1>
        <p class="void-404-desc">这个地址可能已被删除或改名，也可能从来就不存在。<br>不如搜一下站内的内容？</p>

        <form class="void-404-search" onsubmit="return false;">
            <input type="text" id="void-404-keyword" placeholder="输入关键词，回车搜索" autocomplete="off" aria-label="站内搜索" />
            <button type="submit">搜索</button>
        </form>

        <div class="void-404-actions">
            <a class="void-404-btn is-primary" href="<?php Utils::index('/'); ?>">返回首页</a>
            <button class="void-404-btn" type="button" onclick="history.back();">返回上一页</button>
        </div>
    </div>
</main>

<style>
.void-404 {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    padding: 2rem 1.25rem;
    color: rgba(0, 0, 0, .86);
    font-family: 'Open Sans', 'PingFang SC', 'Hiragino Sans GB', 'Microsoft Yahei', Helvetica, Arial, sans-serif;
}
.void-404-bg {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 0;
    overflow: hidden;
    background:
        radial-gradient(1100px 520px at 12% 6%, rgba(189, 98, 98, .20), transparent 62%),
        radial-gradient(900px 480px at 88% 84%, rgba(74, 144, 226, .16), transparent 62%),
        radial-gradient(760px 420px at 72% 8%, rgba(255, 214, 102, .14), transparent 60%),
        linear-gradient(165deg, #f8f8fb 0%, #edeef4 55%, #e4e6ef 100%);
}
.void-404-bg img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.void-404-grid {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    background-image:
        linear-gradient(rgba(0, 0, 0, .035) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0, 0, 0, .035) 1px, transparent 1px);
    background-size: 46px 46px;
}
.void-404-watermark {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-family: Monoton, 'Open Sans', sans-serif;
    font-size: min(42vw, 460px);
    line-height: 1;
    letter-spacing: .02em;
    color: rgba(0, 0, 0, .045);
    user-select: none;
    pointer-events: none;
}
.void-404-mask {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    background: linear-gradient(180deg, rgba(0, 0, 0, .32), rgba(0, 0, 0, .58));
}
.void-404-card {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 460px;
    padding: 2.5rem 2rem 2rem;
    text-align: center;
    border-radius: 18px;
    background: rgba(255, 255, 255, .93);
    box-shadow: 0 20px 60px rgba(0, 0, 0, .2);
    -webkit-backdrop-filter: blur(8px);
    backdrop-filter: blur(8px);
    animation: void-404-in .45s cubic-bezier(.25, .46, .45, .94) both;
}
@keyframes void-404-in {
    from { opacity: 0; transform: translateY(16px); }
    to { opacity: 1; transform: translateY(0); }
}
.void-404-code {
    font-family: Monoton, 'Open Sans', sans-serif;
    font-size: 4.5rem;
    line-height: 1;
    letter-spacing: .04em;
    color: #bd6262;
    text-shadow: 0 6px 24px rgba(189, 98, 98, .28);
}
.void-404-title {
    margin: .75rem 0 .5rem;
    font-size: 1.5rem;
    font-weight: 600;
}
.void-404-desc {
    margin: 0 0 1.5rem;
    font-size: .95rem;
    line-height: 1.8;
    color: rgba(0, 0, 0, .6);
}
.void-404-search {
    display: flex;
    margin-bottom: 1.25rem;
    border: 1px solid rgba(0, 0, 0, .1);
    border-radius: 10px;
    overflow: hidden;
    background: #fff;
}
.void-404-search input {
    flex: 1;
    min-width: 0;
    padding: .7rem .9rem;
    border: none;
    outline: none;
    font-size: .95rem;
    color: rgba(0, 0, 0, .86);
    background: transparent;
}
.void-404-search button {
    flex: none;
    padding: 0 1.1rem;
    border: none;
    cursor: pointer;
    font-size: .95rem;
    color: #fff;
    background: #bd6262;
    transition: background .2s ease;
}
.void-404-search button:hover {
    background: #a85555;
}
.void-404-actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: .6rem;
}
.void-404-btn {
    display: inline-block;
    padding: .55rem 1.35rem;
    border: 1px solid rgba(0, 0, 0, .12);
    border-radius: 8px;
    cursor: pointer;
    font-size: .95rem;
    line-height: 1.6;
    color: rgba(0, 0, 0, .7);
    background: transparent;
    text-decoration: none;
    transition: all .2s ease;
}
.void-404-btn:hover {
    border-color: #bd6262;
    color: #bd6262;
}
.void-404-btn.is-primary {
    border-color: #bd6262;
    color: #fff;
    background: #bd6262;
}
.void-404-btn.is-primary:hover {
    color: #fff;
    background: #a85555;
}
body.theme-dark .void-404 {
    color: #cececf;
}
body.theme-dark .void-404-bg {
    background:
        radial-gradient(1100px 520px at 12% 6%, rgba(189, 98, 98, .26), transparent 62%),
        radial-gradient(900px 480px at 88% 84%, rgba(74, 144, 226, .20), transparent 62%),
        radial-gradient(760px 420px at 72% 8%, rgba(255, 214, 102, .10), transparent 60%),
        linear-gradient(165deg, #191a1e 0%, #121216 55%, #0c0c0f 100%);
}
body.theme-dark .void-404-grid {
    background-image:
        linear-gradient(rgba(255, 255, 255, .04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, .04) 1px, transparent 1px);
}
body.theme-dark .void-404-watermark {
    color: rgba(255, 255, 255, .05);
}
body.theme-dark .void-404-card {
    background: rgba(26, 26, 26, .88);
    box-shadow: 0 20px 60px rgba(0, 0, 0, .5);
}
body.theme-dark .void-404-desc {
    color: #aaa;
}
body.theme-dark .void-404-search {
    border-color: rgba(255, 255, 255, .12);
    background: #222;
}
body.theme-dark .void-404-search input {
    color: #cececf;
}
body.theme-dark .void-404-btn {
    border-color: rgba(255, 255, 255, .16);
    color: #b0b0b0;
}
body.theme-dark .void-404-btn:hover {
    border-color: #bd6262;
    color: #bd6262;
}
@media (max-width: 480px) {
    .void-404-card {
        padding: 2rem 1.25rem 1.75rem;
    }
    .void-404-code {
        font-size: 3.5rem;
    }
}
</style>

<script>
(function () {
    var form = document.querySelector('.void-404-search');
    var input = document.getElementById('void-404-keyword');
    if (!form || !input) return;
    form.addEventListener('submit', function () {
        var keyword = input.value.replace(/^\s+|\s+$/g, '');
        if (!keyword) {
            input.focus();
            return;
        }
        window.location.href = <?php echo json_encode($searchBase); ?> + encodeURIComponent(keyword);
    });
})();
</script>
