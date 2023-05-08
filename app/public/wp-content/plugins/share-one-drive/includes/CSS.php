<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2022, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\ShareoneDrive;

class CSS
{
    public static function generate_inline_css()
    {
        $css = '';

        $custom_css = Core::get_setting('custom_css');
        if (!empty($custom_css)) {
            $css .= $custom_css."\n";
        }

        $loaders = Core::get_setting('loaders');
        if ('custom' === $loaders['style']) {
            $css .= '#ShareoneDrive .loading{  background-image: url('.$loaders['loading'].');}'."\n";
        }

        $css .= '#ShareoneDrive .wpcp-no-results .ajax-filelist { background-image: url('.$loaders['no_results'].');}'."\n";

        $css .= "
    iframe[src*='shareonedrive'] {
        background-image: url({$loaders['iframe']});
        background-repeat: no-repeat;
        background-position: center center;
        background-size: auto 128px;
    }\n";

        $css .= self::get_basic_style_css();

        return \TheLion\ShareoneDrive\Helpers::compress_css($css);
    }

    public static function get_basic_style_css()
    {
        $css = '
        :root {
            --wpcp--present--color--always--white:%white%;
            --wpcp--present--color--always--black:%black%;
            --wpcp--present--border--radius:'.Core::get_setting('layout_border_radius').'px;
            --wpcp--present--gap: '.Core::get_setting('layout_gap').'px;
        }

        @media only screen and (max-width: 480px) {
            :root {
                --wpcp--present--gap: calc('.Core::get_setting('layout_gap').'px * .4);
            }
        }

        @media only screen and (min-width: 480px) and (max-width : 768px) {
            :root {
                --wpcp--present--gap: calc('.Core::get_setting('layout_gap').'px * .6);
            }
        }

        :root .wpcp-theme-light {
        --wpcp--present--color--color-scheme:light;
        --wpcp--present--color--accent:%accent%;
        --wpcp--present--color--background:%background%;
        --wpcp--present--color--background--50:%background_opacity_50%;
        --wpcp--present--color--background--90:%background_opacity_90%;
        --wpcp--present--color--black:%black%;
        --wpcp--present--color--dark1:%dark1%;
        --wpcp--present--color--dark2:%dark2%;
        --wpcp--present--color--white:%white%;
        --wpcp--present--color--light1:%light1%;
        --wpcp--present--color--light2:%light2%;
        }

        :root .wpcp-theme-dark {
        --wpcp--present--color--color-scheme:dark;
        --wpcp--present--color--accent:%accent%;
        --wpcp--present--color--background:%background-dark%;
        --wpcp--present--color--background--50:%background-dark_opacity_50%;
        --wpcp--present--color--background--90:%background-dark_opacity_90%;
        --wpcp--present--color--black:%white%;
        --wpcp--present--color--dark1:%light1%;
        --wpcp--present--color--dark2:%light2%;
        --wpcp--present--color--white:%black%;
        --wpcp--present--color--light1:%dark1%;
        --wpcp--present--color--light2:%dark2%;
    
        }
        ';

        return preg_replace_callback('/%(.*)%/iU', [__CLASS__, 'fill_placeholder_styles'], $css);
    }

    public static function fill_placeholder_styles($matches)
    {
        $colors = Core::get_setting('colors');

        if (isset($colors[$matches[1]])) {
            return $colors[$matches[1]];
        }

        if (false !== strpos($matches[1], 'opacity_')) {
            $original_color_key = str_replace(['_opacity_50', '_opacity_90'], '', $matches[1]);
            if (false !== strpos($colors[$original_color_key], '#')) {
                list($r, $g, $b) = sscanf($colors[$original_color_key], '#%02x%02x%02x');
                $colors[$original_color_key] = "rgb({$r}, {$g}, {$b})";
            }
            $css = str_replace('rgn', 'rgb', $colors[$original_color_key]);

            if (false !== strpos($matches[1], 'opacity_50')) {
                return str_replace(')', ', 0.5)', $css);
            }
            if (false !== strpos($matches[1], 'opacity_90')) {
                return str_replace(')', ', 0.9)', $css);
            }
        }

        return 'initial';
    }
}
