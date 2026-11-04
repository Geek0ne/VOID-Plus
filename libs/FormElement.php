<?php
/**
 * 自定义表单项
 *
 * Typecho 的 Text 表单项不输出 placeholder 属性，这里补一个，
 * 并把占位文字渲染成浅色，用于提示用户该按什么格式填写。
 *
 * @author      Geek0ne
 * @version     2026-11-04 1.0
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

class VOIDPlus_Form_Element_DateText extends \Typecho\Widget\Helper\Form\Element\Text
{
    /**
     * 占位提示文字
     *
     * @var string
     */
    public $placeholder = '';

    /**
     * 占位提示颜色
     *
     * @var string
     */
    public $placeholderColor = '#b9b9b9';

    /**
     * 设置占位提示
     *
     * @param string $text 提示文字
     * @param string $color 提示颜色
     * @return $this
     */
    public function setPlaceholder($text, $color = '#b9b9b9')
    {
        $this->placeholder = (string) $text;
        $this->placeholderColor = (string) $color;

        if ($this->input && $this->placeholder !== '') {
            $this->input->setAttribute('placeholder', $this->placeholder);
        }

        return $this;
    }

    /**
     * 渲染表单项
     *
     * @return string
     */
    public function render()
    {
        $html = parent::render();

        $id = $this->input ? $this->input->getAttribute('id') : '';
        if ($id === '' || $this->placeholder === '') {
            return $html;
        }

        return '<style>#' . $id . '::placeholder{color:' . $this->placeholderColor . '}</style>' . $html;
    }
}
