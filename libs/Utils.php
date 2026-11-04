<?php
/**
 * Utils.php
 * 
 * 工具类
 * 
 * @author      Geek0ne
 * @version     2019-01-15 0.01
 */

class Utils
{
    /**
     * HTML 属性转义，用于把用户可控内容安全地放入 meta / HTML 属性。
     *
     * ENT_QUOTES 同时转义双引号与单引号，避免属性提前闭合；
     * 第二个参数为 null 时按 UTF-8 处理，避免多字节字符被截断。
     *
     * @param  mixed $value
     * @return string
     */
    public static function esc($value)
    {
        if ($value === null || is_array($value) || is_object($value)) {
            return '';
        }
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * 输出相对首页路由，本方法会自适应伪静态
     * 
     * @return void
     */
    public static function index($path)
    {
        Helper::options()->index($path);
    }

    /**
     * 输出相对首页路径，本方法不处理伪静态，用于静态文件
     * 
     * @return void
     */
    public static function indexHome($path)
    {
        Helper::options()->siteUrl($path);
    }

    /**
     * 输出相对主题目录路径，用于静态文件
     * 
     * @return void
     */
    public static function indexTheme($path)
    {
        Helper::options()->themeUrl($path);
    }

    /**
     * 输出头像链接
     * 
     * @return void
     */
    public static function gravatar($mail, $size = 64, $d = '')
    {
        echo Typecho_Common::gravatarUrl($mail, $size, '', urlencode($d), true);
    }

    /**
     * 判断插件是否可用
     * 
     * @return bool
     */
    public static function isPluginAvailable($name) 
    {
        $plugins = Typecho_Plugin::export();
        $plugins = $plugins['activated'];
        return is_array($plugins) && array_key_exists($name, $plugins);
    }

    /**
     * PJAX判定
     * 
     * @return bool
     */
    public static function isPjax()
    {
        return array_key_exists('HTTP_X_PJAX', $_SERVER) && $_SERVER['HTTP_X_PJAX'];
    }

    /**
     * 使用衬线体判定
     */
    public static function isSerif($setting)
    {
        if(isset($_COOKIE['serif'])) {
            if ($_COOKIE['serif']=='1') return true; 
        } else {
            if ($setting['serifincontent']) return true;
        }
        return false;
    }

    /**
     * 界面大小风格
     * 1: 14px, 2: 16px, 3: 18px, 4: 20px, 5: 22px
     */
    public static function getTextSize($setting) {
        if(isset($_COOKIE['textsize'])) {
            return $_COOKIE['textsize'];
        } else {
            return $setting['defaultFontSize'];
        }
    }

    /**
     * 移动端判定
     * 
     * @return bool
     */
    public static function isMobile()
    { 
        if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])){
            return TRUE;
        }
        
        if (isset ($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array ('mobile','nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap'
                ); 
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))){
                return TRUE;
            }
        }
        if (isset ($_SERVER['HTTP_ACCEPT'])){
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== FALSE) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === FALSE || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))){
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * iOS 判定
     */
    public static function isIosSafari () {
        return strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strpos($_SERVER['HTTP_USER_AGENT'], 'iPad');
    }

    /**
     * 编辑界面添加Button
     * 
     * @return void
     */
    public static function addButton()
    {
        echo '<script src="';
        self::indexTheme('/assets/libs/owo/owo_03.js');
        echo '"></script>';

        echo '<script src="';
        self::indexTheme('/assets/editor.js');
        echo '"></script>';

        echo '<link rel="stylesheet" href="';
        self::indexTheme('/assets/libs/owo/owo.min.css');
        echo '" />';
       
        echo '<style>#custom-field textarea,#custom-field input{width:100%}
        .OwO span{background:none!important;width:unset!important;height:unset!important}
        .OwO .OwO-body .OwO-items{
            -webkit-overflow-scrolling: touch;
            overflow-x: hidden;
        }
        .OwO .OwO-body .OwO-items-image .OwO-item{
            max-width:-moz-calc(20% - 10px);
            max-width:-webkit-calc(20% - 10px);
            max-width:calc(20% - 10px)
        }
        @media screen and (max-width:767px){	
            .comment-info-input{flex-direction:column;}
            .comment-info-input input{max-width:100%;margin-top:5px}
            #comments .comment-author .avatar{
                width: 2.5rem;
                height: 2.5rem;
            }
        }
        @media screen and (max-width:760px){
            .OwO .OwO-body .OwO-items-image .OwO-item{
                max-width:-moz-calc(25% - 10px);
                max-width:-webkit-calc(25% - 10px);
                max-width:calc(25% - 10px)
            }
        }
        .wmd-button-row{height:unset}</style>';
    }

    /**
     * 判定内容是否过时
     * 
     * @return array
     */
    public static function isOutdated($archive)
    {
        $created = round((time()- $archive->created) / 3600 / 24);
        $updated = round((time()- $archive->modified) / 3600 / 24);

        return array("is" => $created > 90,
                    "created" => $created,
                    "updated" => $updated);
    }

    /**
     * 解析建站时间
     *
     * 支持 2019-5-3、2019/5/3、2019.5.3、2019年5月3日、20190503 等写法，
     * 只填年份时按 1 月 1 日处理。日期不合法返回 0。
     *
     * @param string $text 原始文本
     * @return int 时间戳，0 表示无效
     */
    public static function parseBuildTime($text)
    {
        $text = trim((string) $text);
        if ($text === '') {
            return 0;
        }

        // 中文年月日与常见分隔符统一
        $text = str_replace(array('年', '月', '日'), array('-', '-', ''), $text);
        $text = preg_replace('/[^0-9\-.\/]/u', '', $text);
        if ($text === null || $text === '') {
            return 0;
        }

        // 紧凑写法 20190503
        if (preg_match('/^(\d{4})(\d{2})(\d{2})$/', $text, $m)) {
            $parts = array($m[1], $m[2], $m[3]);
        } else {
            $text = str_replace(array('/', '.'), '-', $text);
            $parts = explode('-', $text);
        }

        $parts = array_values(array_filter($parts, 'strlen'));
        if (count($parts) === 1 && preg_match('/^\d{4}$/', $parts[0])) {
            $parts = array($parts[0], '1', '1');
        }
        if (count($parts) < 3) {
            return 0;
        }

        $year  = (int) $parts[0];
        $month = (int) $parts[1];
        $day   = (int) $parts[2];

        if ($year < 1970 || $year > 3000 || !checkdate($month, $day, $year)) {
            return 0;
        }

        return mktime(0, 0, 0, $month, $day, $year);
    }

    /**
     * 后台指定的建站时间
     *
     * @return int 时间戳，0 表示未指定或格式无效
     */
    public static function getCustomBuildTime()
    {
        // 主题设置在 functions.php 中载入全局,这里兜底以防被提前调用
        $setting = isset($GLOBALS['VOIDPlusSetting'])
            ? $GLOBALS['VOIDPlusSetting']
            : self::getVOIDPlusSettings();
        if (empty($setting['siteBuildTime'])) {
            return 0;
        }

        return self::parseBuildTime($setting['siteBuildTime']);
    }

    /**
     * 页脚版权年份
     *
     * 指定了建站时间则显示「起始年-当前年」，否则只显示当前年。
     *
     * @return string
     */
    public static function getCopyrightYears()
    {
        $now = (int) date('Y');
        $start = self::getCustomBuildTime();

        if ($start > 0) {
            $year = (int) date('Y', $start);
            if ($year > 0 && $year < $now) {
                return $year . '-' . $now;
            }
        }

        return (string) $now;
    }

    /**
     * 输出建站时间
     *
     * 后台指定了建站时间则以其为准，否则回退到最早一篇文章的写作时间。
     *
     * @return void
     */
    public static function getBuildTime()
    {
        $custom = self::getCustomBuildTime();
        if ($custom > 0) {
            echo date('Y-m-d\TH:i', $custom);
            return;
        }

        $db = Typecho_Db::get();
        $content = $db->fetchRow($db->select()->from('table.contents')
            ->where('table.contents.status = ?', 'publish')
            ->where('table.contents.password IS NULL')
            ->order('table.contents.created', Typecho_Db::SORT_ASC)
            ->limit(1));
        if (count($content))
            echo date('Y-m-d\TH:i', $content['created']);
        else
            echo date('Y-m-d\TH:i');
    }

    /**
     * 已发布文章数量
     * 
     * @return int
     */
    public static function getPostNum()
    {
        $db = Typecho_Db::get();
        return $db->fetchObject($db->select(array('COUNT(cid)' => 'num'))
                    ->from('table.contents')
                    ->where('table.contents.type = ?', 'post')
                    ->where('table.contents.status = ?', 'publish'))->num;
    }

    /**
     * 分类数量
     * 
     * @return int
     */
    public static function getCatNum()
    {
        $db = Typecho_Db::get();
        return $db->fetchObject($db->select(array('COUNT(mid)' => 'num'))
                    ->from('table.metas')
                    ->where('table.metas.type = ?', 'category'))->num;
    }

    /**
     * 标签数量
     * 
     * @return int
     */
    public static function getTagNum()
    {
        $db = Typecho_Db::get();
        return $db->fetchObject($db->select(array('COUNT(mid)' => 'num'))
                    ->from('table.metas')
                    ->where('table.metas.type = ?', 'tag'))->num;
    }

    /**
     * 存在 VOID-Plus 插件且满足要求
     */
    public static function hasVOIDPlusPlugin($req)
    {
        if(self::isPluginAvailable('VOIDPlus')) {
            $version_have = VOIDPlus_Plugin::$VERSION;
            if($version_have >= $req) return true;
        }

        return false;
    }

    /**
     * 可选的版权许可协议
     *
     * 键为设置项取值，值为协议名称与官方链接。
     * 新增协议只需在此处追加一条，后台下拉框会自动同步。
     *
     * @return array
     */
    public static function getLicenseOptions()
    {
        return array(
            'by' => array(
                'name' => '知识共享署名 4.0 国际',
                'url'  => 'https://creativecommons.org/licenses/by/4.0/'
            ),
            'by-nc' => array(
                'name' => '知识共享署名-非商业性使用 4.0 国际',
                'url'  => 'https://creativecommons.org/licenses/by-nc/4.0/'
            ),
            'by-sa' => array(
                'name' => '知识共享署名-相同方式共享 4.0 国际',
                'url'  => 'https://creativecommons.org/licenses/by-sa/4.0/'
            )
        );
    }

    /**
     * 生成版权说明 HTML
     *
     * 固定句式为「本网站内容均采用 XXX许可协议 进行许可。」，
     * 中间的协议名称与链接取自 getLicenseOptions()。
     *
     * @param string $key 许可协议标识
     * @return string 为空表示不显示版权说明
     */
    public static function getLicenseHtml($key)
    {
        $options = self::getLicenseOptions();
        if (!isset($options[$key])) {
            return '';
        }

        $info = $options[$key];
        return '<p>本网站内容均采用 '
            . '<a href="' . $info['url'] . '" target="_blank" rel="noopener noreferrer nofollow">'
            . '<svg class="cc-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">'
            . '<circle cx="12" cy="12" r="10.2" fill="none" stroke="currentColor" stroke-width="1.7"></circle>'
            . '<text x="12" y="16.1" text-anchor="middle" font-family="Helvetica,Arial,sans-serif" font-size="10.5" font-weight="700" fill="currentColor">cc</text>'
            . '</svg>' . $info['name'] . '许可协议</a> 进行许可。</p>';
    }

    /**
     * 规整备案号文本
     *
     * 去掉全部 Unicode 空白与零宽字符（半角空格、不换行空格、全角空格、
     * 窄空格、零宽空格、BOM 等），避免「粤 ICP 备 XXXXXXXX 号」这类带空格的写法。
     *
     * @param string $text 原始备案号
     * @return string
     */
    public static function formatBeian($text)
    {
        // \p{Z} 覆盖全部 Unicode 分隔符(Zs/Zl/Zp)，\s 覆盖 ASCII 控制空白，
        // 再补零宽空格与 BOM——从网页或 Word 复制来的备案号常含这些字符
        $text = (string) $text;
        $text = preg_replace('/[\s\p{Z}\x{200B}\x{200C}\x{200D}\x{FEFF}]+/u', '', $text);
        return $text === null ? '' : $text;
    }

    /**
     * 超高级设置
     *
     * @return array
     */
    public static function getVOIDPlusSettings()
    {
        $options = Helper::options();

        // 主题设置
        $themeSetting = array(
            'defaultBanner' => '',
            'enableMath' => false,
            'head' => '',
            'footer' => '',
            'serifincontent' => false,
            'pjax' => false,
            'pjaxreload' => '',
            'indexStyle' => 0,
            'lazyload' => false,
            'indexBannerTitle' => '',
            'indexBannerSubtitle' => '',
            'serviceworker' => '',
            'colorScheme' => 0, // 0: 自动，1: 日间，2: 夜间
            'reward' => '',
            'beian' => '0',
            'beianText' => '',
            'license' => 'by',
            'siteBuildTime' => ''
        );

        $keys = array_keys($themeSetting);
        foreach ($keys as $key) {
            if(!empty($options->{$key})){
                $themeSetting[$key] = $options->{$key};
            }
        }

        // 许可协议允许留空(用户可能主动关闭),不能沿用上面的 !empty 判断
        if (isset($options->license)) {
            $themeSetting['license'] = $options->license;
        } elseif (isset($options->copyright)) {
            // 兼容 3.5.7 之前把版权说明存为 HTML 的旧设置:
            // 旧值非空按「知识共享署名 4.0 国际」处理,旧值为空视为用户已主动关闭
            $themeSetting['license'] = empty($options->copyright) ? '' : 'by';
        }

        // 一些类型变换
        $themeSetting['enableMath'] = boolval($themeSetting['enableMath']);
        $themeSetting['lazyload'] = boolval($themeSetting['lazyload']);
        $themeSetting['colorScheme'] = intval($themeSetting['colorScheme']);
        $themeSetting['pjax'] = boolval($themeSetting['pjax']);
        $themeSetting['serifincontent'] = boolval($themeSetting['serifincontent']);
        $themeSetting['indexStyle'] = intval($themeSetting['indexStyle']);

        // 高级设置
        $advanceSetting = array(
            'nav' => '',
            'name' => '',
            'brandFont' => array(
                'src' => '',
                'style' => 'normal',
                'weight' => 'normal'
            ),
            'desktopBannerHeight' => '',
            'mobileBannerHeight' => '',
            'twitterId' => '',
            'weiboId' => '',
            'headerMode' => 1,
            'defaultFontSize' => 3,
            'useFiraCodeFont' => false,
            'followSystemColorScheme' => false,
            'largePhotoSet' => true,
            'macStyleCodeBlock' => true,
            'lineNumbers' => true,
            'parseFigcaption' => true,
            'darkModeTime' => array (
                'start' => 22.0,
                'end' => 7.0
            ),
            'link' => array(),
            'commentFoldThreshold' => array(5, 1.5),
            'commentNotification' => '',
            'bluredLazyload' => false,
            'browserLevelLoadingLazy' => false,
            'CDNType' => array()
        );

        if(!empty($options->advance)){
            $settings = json_decode($options->advance, true);
            // 示例文件带注释，用户直接粘贴会导致解析失败，此时保留默认设置
            if(is_array($settings)){
                foreach ($settings as $key => $value) {
                    $advanceSetting[$key] = $value;
                }
            }
        }

        if(self::isMobile() && array_key_exists('headerModeMobile', $advanceSetting)){
            $advanceSetting['headerMode'] = $advanceSetting['headerModeMobile'];
        }

        $output = array_merge($themeSetting, $advanceSetting);
        $output['VOIDPlusPlugin'] = self::hasVOIDPlusPlugin($GLOBALS['VOIDPlusPluginREQ']);

        return $output;
    }
}