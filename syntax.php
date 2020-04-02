<?php
/**
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Go Namhyeon <gnh1201@gmail.com>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_msaviewer extends DokuWiki_Syntax_Plugin {

    /**
     * What kind of syntax are we?
     */
    function getType(){
        return 'substition';
    }

    function getPType(){
        return 'block';
    }

    /**
     * Where to sort in?
     */
    function getSort(){
        return 160;
    }

    /**
     * Connect pattern to lexer
     */
    function connectTo($mode) {
      $this->Lexer->addSpecialPattern('{{msaviewer.*?>*?}}',$mode,'plugin_msaviewer');
    }

    /**
     * Handle the match
     */
    function handle($match, $state, $pos, Doku_Handler $handler) {
        list($params, $uri) = explode('>', trim($match,'{}'), 2);

        return [
            "id" => sprintf("%x", crc32($uri)),
            "uri" => $uri
        ];
    }

    /**
     * Create output
     */
    function render($mode, Doku_Renderer $R, $data) {
        $viewer_id = $data['id'];
        $viewer_uri = $data['uri'];
        
        $R->doc .= <<<EOF
        <a href="javascript:msaviewer_{$viewer_id}('{$viewer_uri}')">[view]</a>

        <script src="https://catswords.info/lib/scripts/msa.custom.js"></script>
        <script>
        function msaviewer_{$viewer_id}(uri) {
            var onload = function() {
                var el = document.createElement("div");
                var opts = {
                    el: el,
                    vis: {
                        conserv: false,
                        overviewbox: false
                    },
                    // smaller menu for JSBin
                    menu: "small",
                    bootstrapMenu: true
                };
                var m = new msa.msa(opts);
                m.u.file.importURL(uri, function() {
                    m.render();
                });

                var w = jQuery(window).width() * 0.8;
                var h = jQuery(window).height() * 0.8;
                jQuery(el).dialog({
                    title: "Sequence viewer",
                    height: h,
                    width: w
                });
            };

            if(typeof msa == 'undefined') {
                var script = document.createElement('script');
                script.src = "https://catswords.info/lib/scripts/msa.custom.js";
                document.head.appendChild(script);
                script.onload = onload;
            }
            
            onload();
        }
        </script>
EOF;

        return true;
    }
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
