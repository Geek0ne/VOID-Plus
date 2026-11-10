# -*- coding: utf-8 -*-
"""VOID-Plus 一致性校验。

在 CI 中调用，检查三件靠人眼容易漏掉的事：

1. 版本号三处声明必须一致（functions.php / index.php / package.json）
2. README 与 change-log.md 的更新日志必须逐字一致
3. SCSS 源码与编译产物 CSS 必须同步

第 3 项分两层：
   - 零依赖层（始终执行）：SCSS 里出现的每个选择器都必须在 CSS 产物里存在
   - 强校验层（传入 --built-css 时执行）：编译产物的每条声明都必须在 CSS 产物里存在

用法：
    python3 .github/scripts/check_consistency.py [--built-css PATH]

退出码 0 表示全部通过。
"""
import argparse
import json
import os
import re
import sys

ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

SCSS = os.path.join(ROOT, 'assets', 'VOID-Plus.scss')
SCSS_DIR = os.path.join(ROOT, 'assets', 'parts')
CSS = os.path.join(ROOT, 'assets', 'VOID-Plus.css')

FAILURES = []
NOTES = []


def fail(msg):
    FAILURES.append(msg)
    print('::error::%s' % msg)


def note(msg):
    NOTES.append(msg)
    print('::warning::%s' % msg)


def ok(msg):
    print('::notice::%s' % msg)


# ---------------------------------------------------------------- 版本号

def check_version():
    print('\n=== 1. 版本号三处一致 ===')
    fn = open(os.path.join(ROOT, 'functions.php'), encoding='utf-8').read()
    ix = open(os.path.join(ROOT, 'index.php'), encoding='utf-8').read()
    pj = json.load(open(os.path.join(ROOT, 'package.json'), encoding='utf-8'))

    m1 = re.search(r"VOIDPlusVersion'\]\s*=\s*'([^']+)'", fn)
    m2 = re.search(r'@version\s+([0-9][0-9.]*)', ix)
    v1 = m1.group(1) if m1 else None
    v2 = m2.group(1) if m2 else None
    v3 = pj.get('version')

    if not v1:
        fail('functions.php 中找不到 VOIDPlusVersion 声明')
    if not v2:
        fail('index.php 中找不到 @version 声明')
    if not v3:
        fail('package.json 中找不到 version 字段')

    if v1 and v2 and v1 != v2:
        fail('functions.php(%s) 与 index.php(%s) 的版本号不一致' % (v1, v2))
    if v1 and v3 and v1 != v3:
        fail('functions.php(%s) 与 package.json(%s) 的版本号不一致' % (v1, v3))
    if v1 and v2 and v3 and v1 == v2 == v3:
        ok('版本号三处一致：%s' % v1)


# ---------------------------------------------------------------- 更新日志

ENTRY = re.compile(r'^\*\s+\S', re.M)
VERSION_HEAD = re.compile(r'^\*\*\s*.*Version\s+([0-9][0-9.]*)\*\*\s*$', re.M)


def split_sections(text):
    """把文本切成 {版本号: 段落文本}，段落以 '**...Version X.Y.Z**' 行开头。"""
    heads = list(VERSION_HEAD.finditer(text))
    out = {}
    for i, m in enumerate(heads):
        start = m.start()
        end = heads[i + 1].start() if i + 1 < len(heads) else len(text)
        block = text[start:end]
        # 截到最后一个条目行，避免把「更多历史版本请查阅」之类的尾巴算进来
        entries = list(ENTRY.finditer(block))
        if entries:
            block = block[:entries[-1].end()]
        out[m.group(1)] = block.strip()
    return out


def check_changelog():
    print('\n=== 2. README 与 change-log.md 更新日志逐字一致 ===')
    readme = open(os.path.join(ROOT, 'README.md'), encoding='utf-8').read()
    clog = open(os.path.join(ROOT, 'change-log.md'), encoding='utf-8').read()

    if '## 更新日志' not in readme:
        fail('README.md 中找不到「## 更新日志」章节')
        return
    sec = readme[readme.index('## 更新日志'):]
    nxt = sec.find('\n## ', 3)
    if nxt > 0:
        sec = sec[:nxt]

    r_sections = split_sections(sec)
    c_sections = split_sections(clog)

    if not r_sections:
        fail('README.md 的更新日志区块里没有解析到任何版本条目')
        return

    for ver, block in sorted(r_sections.items(), reverse=True):
        if ver not in c_sections:
            fail('README.md 中的 %s 未出现在 change-log.md 中' % ver)
            continue
        if r_sections[ver] != c_sections[ver]:
            fail('README.md 与 change-log.md 的 %s 区块不一致（%d / %d 字符）'
                 % (ver, len(r_sections[ver]), len(c_sections[ver])))
        else:
            ok('%s 区块与 change-log.md 逐字一致' % ver)

    if '更多历史版本请查阅' not in sec:
        fail('README.md 更新日志末尾缺少「更多历史版本请查阅」指引')


# ---------------------------------------------------------------- SCSS / CSS

SKIP_EXT = ('png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'avif', 'woff',
            'woff2', 'ttf', 'eot', 'css', 'scss', 'js', 'json', 'php', 'html',
            'md', 'dat', 'ico', 'map', 'ttc', 'otf')


def scss_selectors():
    """提取 SCSS 源码里出现过的选择器标记（.class / #id）。

    先抹掉属性值（冒号到 ; { } 之间），否则 #fff、url(...#iefix) 这类
    会被误当成 id 选择器。
    """
    found = set()
    files = [SCSS]
    if os.path.isdir(SCSS_DIR):
        for f in sorted(os.listdir(SCSS_DIR)):
            if f.endswith('.scss'):
                files.append(os.path.join(SCSS_DIR, f))
    for path in files:
        text = strip_comments(open(path, encoding='utf-8').read())
        text = re.sub(r':[^;{}]*', ':', text)
        for m in TOKEN.finditer(text):
            name = m.group(2)
            if name.lower() in SKIP_EXT:
                continue
            if re.fullmatch(r'[0-9a-fA-F]{6}|[0-9a-fA-F]{8}', name):
                continue
            found.add(m.group(1) + name)
    return found


TOKEN = re.compile(r'([.#])([A-Za-z_][\w-]*)(?![\w-])')


def strip_comments(text):
    text = re.sub(r'/\*.*?\*/', '', text, flags=re.S)
    text = re.sub(r'(?m)^\s*//.*$', '', text)
    return text


def css_tokens(css):
    """提取 CSS 中出现过的全部 class / id 标记。"""
    css = strip_comments(css)
    tokens = set()
    for m in re.finditer(r'([^{}]+)\{', css):
        sel = m.group(1)
        if sel.strip().startswith('@'):
            continue
        for part in sel.split(','):
            part = part.strip()
            if not part or part.startswith('@'):
                continue
            for t in TOKEN.finditer(part):
                tokens.add(t.group(1) + t.group(2))
    return tokens


def strip_prefix(decl):
    return re.sub(r'^-(webkit|moz|ms|o)-', '', decl.strip().lower())


def parse_css(css):
    """解析 CSS，返回 {选择器: set(声明名)}；@media/@supports 内的规则并入顶层。"""
    rules = {}
    stack = []
    buf = ''
    i = 0
    n = len(css)
    while i < n:
        ch = css[i]
        if ch == '{':
            header = buf.strip()
            buf = ''
            stack.append(('skip' if header.startswith('@') else 'rule', header))
            i += 1
            continue
        if ch == '}':
            body = buf.strip()
            buf = ''
            if stack:
                kind, header = stack.pop()
                if kind == 'rule':
                    decls = set()
                    for d in body.split(';'):
                        if ':' in d:
                            decls.add(strip_prefix(d.split(':')[0]))
                    for s in header.split(','):
                        s = s.strip()
                        if s:
                            rules.setdefault(s, set()).update(decls)
            i += 1
            continue
        buf += ch
        i += 1
    return rules


def check_scss_css(built_path=None):
    print('\n=== 3. SCSS 与 CSS 产物同步 ===')
    if not os.path.exists(SCSS):
        fail('找不到 SCSS 源文件 assets/VOID-Plus.scss')
        return
    if not os.path.exists(CSS):
        fail('找不到 CSS 产物 assets/VOID-Plus.css')
        return

    shipped_css = open(CSS, encoding='utf-8').read()
    bundle = os.path.join(ROOT, 'assets', 'bundle.css')
    bundle_css = ''
    if os.path.exists(bundle):
        bundle_css = open(bundle, encoding='utf-8').read()

    shipped_tokens = css_tokens(shipped_css) | css_tokens(bundle_css)
    selectors = scss_selectors()
    if not selectors:
        fail('未能从 SCSS 中解析出任何选择器')
        return

    missing = sorted(sel for sel in selectors if sel not in shipped_tokens)

    if missing:
        fail('以下选择器在 SCSS 中存在，但 CSS 产物中找不到：\n%s'
             % '  ' + '\n  '.join(missing))
    else:
        ok('%d 个 SCSS 选择器全部存在于 CSS 产物' % len(selectors))

    if not built_path:
        note('未提供编译产物，跳过声明级比对（SCSS 改了属性值但未重新编译时无法发现）')
        return
    if not os.path.exists(built_path):
        note('编译产物 %s 不存在，跳过声明级比对' % built_path)
        return

    built = parse_css(open(built_path, encoding='utf-8').read())
    shipped = parse_css(shipped_css)

    missing_sel = []
    missing_decl = []
    for sel, decls in built.items():
        if sel not in shipped:
            missing_sel.append(sel)
            continue
        gap = decls - shipped[sel]
        if gap:
            missing_decl.append((sel, sorted(gap)))

    if missing_sel:
        fail('CSS 产物缺少以下选择器（SCSS 改了但没重新编译）：\n%s'
             % '  ' + '\n  '.join(sorted(missing_sel)[:20]))
    if missing_decl:
        detail = '\n  '.join('%s -> %s' % (s, ', '.join(d)) for s, d in missing_decl[:20])
        fail('CSS 产物缺少以下声明（SCSS 改了但没重新编译）：\n  ' + detail)
    if not missing_sel and not missing_decl:
        ok('声明级比对通过：编译产物与 CSS 产物一致')


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument('--built-css', default=None,
                    help='SCSS 编译产物的路径，提供后执行声明级比对')
    args = ap.parse_args()

    check_version()
    check_changelog()
    check_scss_css(args.built_css)

    print('\n' + '=' * 60)
    if FAILURES:
        print('RESULT: %d 项检查未通过' % len(FAILURES))
        sys.exit(1)
    print('RESULT: ALL PASS')
    if NOTES:
        print('（%d 条提示）' % len(NOTES))


if __name__ == '__main__':
    main()
