<?php
/**
 * Plugin Iframe: Inserts an iframe element to include the specified url
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
 */
 // must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_iframe extends DokuWiki_Syntax_Plugin {

    function getType() { return 'substition'; }
    function getSort() { return 305; }
    function connectTo($mode) { $this->Lexer->addSpecialPattern('{{iframe>.*?}}',$mode,'plugin_iframe'); }

    function handle($match, $state, $pos, Doku_Handler $handler){
        $match = substr($match, 9, -2);
        list($url, $alt)   = explode('|',$match,2);
        list($url, $opts) = explode(' ',$url,2);
        $params = explode(' ', $opts);

        // javascript pseudo uris allowed?
        if (!$this->getConf('js_ok') && substr($url,0,11) == 'javascript:'){
            $url = false;
        }

        // set defaults
        $attrs = array(
                    'src'         => $url,
                    'width'       => '100%',
                    'height'      => '400px',
                    'title'       => trim($alt),
                    'frameborder' => 0,
                );

        $align = '';
        foreach ($params as $idx => $param) {
            if ($param === 'noscroll') {
                $attrs['scrolling'] = 'no';
                continue;
            }
            if (in_array($param,['center', 'left', 'right'])) {
                $align = $param; 
                continue;
            }
            // Unknown attributes
            list($key, $value) = explode('=',$param);
            if (!$key && !$value) continue;
            $attrs[$key] = $value;
        }
        // Merge the align class
        if (!empty($align)) {
            $attrs['class'] .= " plugin_iframe_$align";
        }
        // Create style attr
        $attrs['style'] = 'width:'.$attrs['width'].';height:'.$attrs['height'];
        unset($attrs['width'], $attrs['height']);

        return $attrs;
    }

    function render($mode, Doku_Renderer $R, $data) {
        if($mode != 'xhtml') return false;

        if(!$data['src']){
            $R->doc .= '<div class="iframe">'.hsc($data['title']).'</div>';
            return true;
        }

        $params = buildAttributes($data);
        $R->doc .= "<iframe $params>".hsc($data['title']).'</iframe>';

        return true;
    }
}
