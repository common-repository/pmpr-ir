<?php

namespace Pmpr\Plugin\Pmpr\Helper;

use Pmpr\Plugin\Pmpr\Interfaces\Constants;

/**
 * Class HTML
 * @package Pmpr\Plugin\Pmpr\Helper
 */
class HTML extends Common
{
    /**
     * @param string $element
     * @param array $attrs
     * @param string|array $content
     *
     * @return string
     */
    public function createElement(string $element, array $attrs = [], $content = ''): string
    {
        $attributes = $this->generateAttributes($attrs);
        if ($element) {

            $html = "<{$element} {$attributes}";
            if (!in_array($element, ['input', 'img'])) {

                if (is_array($content)) {

                    $content = implode('', $content);
                }
                $html .= ">{$content}</{$element}>";
            } else {

                $html .= "/>";
            }
        } else {

            $html = $content;
        }

        return $html;
    }

    /**
     * @param string $element
     * @param array $attrs
     * @param string|array $content
     */
    public function renderElement(string $element, array $attrs = [], $content = '')
    {
        $this->renderHTML($this->createElement($element, $attrs, $content));
    }

    /**
     * @param       $content
     * @param array $attrs
     *
     * @return string
     */
    public function createDiv($content, array $attrs = []): string
    {
        return $this->createElement('div', $attrs, $content);
    }

    /**
     * @param       $content
     * @param array $attrs
     *
     * @return string
     */
    public function createParagraph($content, array $attrs = []): string
    {
        return $this->createElement('p', $attrs, $content);
    }

    /**
     * @param       $content
     * @param array $attrs
     *
     * @return string
     */
    public function createSpan($content, array $attrs = []): string
    {
        return $this->createElement('span', $attrs, $content);
    }

    /**
     * @param       $content
     * @param array $attrs
     *
     * @return string
     */
    public function createStrong($content, array $attrs = []): string
    {
        return $this->createElement('strong', $attrs, $content);
    }

    /**
     * @param array $attributes
     * @param string $value
     * @param string $key
     *
     * @return array|false|mixed|string|string[]
     */
    public function addAttribute(array $attributes = [], string $value = '', string $key = 'class')
    {
        if (is_array($attributes)) {

            if ($key) {

                $attr = $this->getHelper()->getType()->arrayGetItem($attributes, $key, '');
            } else {

                $attr = $attributes;
            }

            if (is_array($value)) {

                $attr = is_array($attr)
                    ? array_merge($attr, $value)
                    : trim(implode(' ', $value));
            } else if (is_string($value)) {

                $attr = is_array($attr) ? array_merge($attr, explode(' ', $value)) : trim($attr . ' ' . $value);
            }

            if ($key) {

                $attributes[$key] = $attr;
            } else {

                $attributes = $attr;
            }
        } else {

            $attributes = trim($attributes . ' ' . (is_array($value) ? implode(' ', $value) : $value));
        }

        return $attributes;
    }

    /**
     * @param array $attributes
     * @param array $values
     *
     * @return array|false|mixed|string|string[]
     */
    public function addAttributes(array $attributes = [], array $values = [])
    {
        foreach ($values as $key => $value) {

            $attributes = $this->addAttribute($attributes, $value, $key);
        }

        return $attributes;
    }

    /**
     * @param string|array $attributes
     *
     * @return string
     */
    public function generateAttributes($attributes = []): string
    {
        $return = '';
        if ($attributes) {

            if (is_array($attributes)) {

                foreach ($attributes as $key => $value) {
                    if (is_array($value)) {

                        $value = implode(" ", $value);
                    }
                    if ($value) {

                        $attr   = esc_attr($value);
                        $return .= "{$key}=\"{$attr}\" ";
                    } else {

                        $return .= "$key ";
                    }
                }
            } else {

                $return = $attributes;
            }
        }

        return $return;
    }

    /**
     * @param $html
     *
     * @return void
     */
    public function renderHTML($html)
    {
        if (is_string($html)) {

            $allowTags = [
                'style', 'br', 'hr', 'img',
                'a', 'p', 'div', 'span', 'strong', 'em', 'button',
                'tr', 'td', 'th', 'table', 'tbody', 'thead', 'tfoot',
                'nav', 'ul', 'ol', 'li',
                'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
                'input', 'select', 'option', 'label', 'form',
            ];

            $allowAttrs = [
                Constants::ID   => [],
                'data'     => [],
                'style'    => [],
                'class'    => [],
                'disabled' => [],

                'aria-label'   => [],
                'aria-current' => [],
            ];

            $allowedHTML = [];
            foreach ($allowTags as $tag) {

                $custom = [];
                switch ($tag) {
                    case 'img':
                        $custom = [
                            'src'    => [],
                            'width'  => [],
                            'height' => [],
                        ];
                        break;
                    case 'td':
                    case 'th':
                        $custom = [
                            'scope'   => [],
                            'colspan' => [],
                            'rowspan' => [],
                        ];
                        break;
                    case 'input':
                    case 'select':
                        $custom = [
                            'type'        => [],
                            'name'        => [],
                            'value'       => [],
                            'required'    => [],
                            'placeholder' => [],
                        ];
                        if ($tag === 'input') {

                            $custom['checked'] = [];
                        }
                        break;
                    case 'a':
                    case 'button':
                        $custom = [
                            'rol'        => [],
                            'href'        => [],
                            'type'        => [],
                            'title'       => [],
                            'target'      => [],
                            'data-on'     => [],
                            'data-job'    => [],
                            'data-ver'    => [],
                            'data-done'   => [],
                            'data-type'   => [],
                            'data-slug'   => [],
                            'data-name'   => [],
                            'data-nonce'  => [],
                            'data-title'  => [],
                            'data-config' => [],
                            'data-plugin' => [],

                        ];
                        break;
                    case 'option':
                        $custom = [
                            'value'    => [],
                            'selected' => [],
                        ];
                        break;
                    case 'form':
                        $custom = [
                            'action' => [],
                            'method' => [],
                        ];
                        break;
                }
                $allowedHTML[$tag] = array_merge($allowAttrs, $custom);
            }

            echo wp_kses($html, $allowedHTML);
        }
    }

    /**
     * @param       $message
     * @param array $args
     */
    public function renderNotice($message, array $args = [])
    {
        $args['echo'] = true;
        $this->createNotice($message, $args);
    }

    /**
     * @param string $path
     * @param array $args
     * @param bool $echo
     *
     * @return false|string
     */
    public function renderTemplate(string $path, array $args = [], bool $echo = true)
    {
        $html = '';
        if ($fullPath = $this->getHelper()->getFile()->templateExists($path)) {

            if ($args) {

                extract($args);
            }
            if (!$echo) {

                ob_start();
            }

            include $fullPath;

            if (!$echo) {

                $html = ob_get_clean();
            }
        }

        return $html;
    }

    /**
     * @param $count
     *
     * @return string
     */
    public function createBubbleNotification($count): string
    {
        $notification = '';
        if ($count > 0) {

            $notification = $this->createSpan(
                $this->createSpan(number_format_i18n($count), ['class' => 'plugin-count']),
                ['class' => 'update-plugins count-' . $count]
            );
        }

        return $notification;
    }

    /**
     * @param array $args
     *
     * @return string
     */
    public function createAction(array $args = []): string
    {
        $args = $this->getHelper()->getType()->parseArgs($args, [
            Constants::JOB     => '',
            Constants::TYPE    => '',
            Constants::SLUG    => '',
            Constants::NAME    => '',
            Constants::ATTRS   => [],
            Constants::TITLE   => '',
            Constants::LOADING => false,
            Constants::ELEMENT => 'button',
        ]);

        $spinner = '';
        $context = $this->getHelper()->getServer()->getGet(Constants::CONTEXT, Constants::INSTALLED);
        $class   = "pr-component-action pr-{$context}-action";

        if ($args[Constants::LOADING]) {

            $spinner = $this->createSpan('', [
                'class' => 'dashicons dashicons-update spin',
            ]);
            $class .= ' pr-loading-action';
        }

        $attrs = $args[Constants::ATTRS];
        if (isset($attrs['class'])) {

            $attrs['class'] .= " {$class}";
        } else {

            $attrs['class'] = $class;
        }

        $attrs = array_merge($attrs, [
            'data-job'   => $args[Constants::JOB],
            'data-type'  => $args[Constants::TYPE],
            'data-name'  => $args[Constants::NAME],
            'data-slug'  => $args[Constants::SLUG],
            'data-nonce' => wp_create_nonce(PR__PLG__PMPR . '_nonce_action'),
        ]);

        $attrs = array_filter($attrs);

        $content = $spinner . $this->createSpan($args[Constants::TITLE], ['class' => 'action-title']);

        return $this->createElement($args[Constants::ELEMENT], $attrs, $content);
    }

    /**
     * @param       $message
     * @param array $args
     *
     * @return string
     */
    public function createNotice($message, array $args = []): string
    {
        $args = $this->getHelper()->getType()->parseArgs($args, [
            'class'         => '',
            Constants::ID        => '',
            Constants::ECHO      => false,
            Constants::TYPE      => 'warning',
            Constants::PREFIX    => __('Pmpr Plugin', PR__PLG__PMPR),
            Constants::CUSTOM    => false,
            Constants::DISMISS   => true,
            Constants::ELEMENT   => 'p',
            'element_attrs' => [],
        ]);

        $element = $args[Constants::ELEMENT];

        if (is_wp_error($message)) {
            $message = $message->get_error_messages();
        }

        if (is_array($message)) {

            if (count($message) > 1) {

                $element = 'ul';
                $message = '<li>' . implode("</li><li>", $message) . '</li>';
            } else {

                $message = implode('', $message);
            }
        }

        if ($prefix = $args['prefix']) {

            $prefix = $this->createStrong($prefix . ':');
            if ('ul' === $element) {

                $prefix = $this->createElement('li', [], $prefix);
            }
            $message = sprintf('%s %s', $prefix, $message);
        }

        $html  = '';
        $class = "notice";
        if ($args['custom']) {

            $class = 'custom-notice';
        }
        $class .= " notice-{$args['type']}";
        if ($args['dismiss']) {

            $class .= ' is-dismissible';
        }
        if ($args['class']) {

            $class .= " {$args['class']}";
        }
        $attrs     = ['class' => $class];
        $content   = $this->createElement($element, $args['element_attrs'], $message);
        $container = 'div';
        if ($args['echo']) {

            $this->renderElement($container, $attrs, $content);
        } else {

            $html = $this->createElement($container, $attrs, $content);
        }
        return $html;
    }

    /**
     * @param array $args
     * @param string $title
     * @param array $attrs
     */
    public function renderModal(array $args, string $title, array $attrs = [])
    {
        $args[Constants::ECHO] = true;

        $this->createModal($args, $title, $attrs);
    }

    /**
     * @param array $args
     * @param string $title
     * @param array $attrs
     *
     * @return string
     */
    public function createModal(array $args, string $title, array $attrs = []): string
    {
        return apply_filters('foundation_backend_generate_modal_action', '', $args, $title, $attrs);
    }
}