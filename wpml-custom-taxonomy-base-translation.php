<?php 
/**
 * WPML-Custom-Taxonomy-base-translation
 * 
 * @version 1.0
 * @copyright 2018 Gleb Makarov 
 * @author Gleb Makarov (email: gleb@blueglass.ee)
 * @link https://www.blueglass.ee
 * 
 * @license GNU General Public LIcense v3.0 - license.txt
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * 
 *
 *

    USAGE:
    Include this file to your function.php

    AND

    Add custom taxonomy as you would usually do, if you dont have one yet.

    function add_post_extra() {
        $labels = array(
            'name'                       => __( 'Extra'),
            'singular_name'              => __( 'Extra' ),
        );

        $args = array(
            'labels'                     => $labels,
            'rewrite'                    => array(
                                                'slug' => 'theme',
                                            ),
            'query_var'                  => true,
        );
        register_taxonomy( 'post_extra', array( 'post' ), $args );
    }
    add_action( 'init', 'add_post_extra' );

 */


$rewrite_bases = get_my_rewrites();
$languages = get_wpml_available_languages();

/* 
 * Put together our BASE rewrites for languages
 * 
 * 'BASE' => array(
 *          'TAXONOMY' => array(
 *              'en' => 'base-slug-en', 
 *              'de' => 'base-slug-de'
 *          )
 *      ),
 * 
 * After update, go to Permalinks and save/update settings, to update rewrite rules.
 * You can add as many base's as you like, just keep the structure of the array.
 */ 
function get_my_rewrites(){
    $rewrite_bases = array(

        'theme' => array(
            'post_theme' => array(
                'en' => 'theme', 
                'fr' => 'thema'
            )
        ),
        
    );

    return $rewrite_bases;
}

/* 
 * Get languages
 */ 
function get_wpml_available_languages(){
    if(function_exists('icl_get_languages')){
        $languages = icl_get_languages('skip_missing=0&orderby=code');
        return $languages;
    }
}

/* 
 * Add rewrite rules
 */ 
add_filter( 'rewrite_rules_array', 'blueglass_rewrite_rules', 99 );
function blueglass_rewrite_rules( $aRules ){
    $rewrite_bases = get_my_rewrites();

    $newRules = array();

    foreach ($rewrite_bases as $base => $bases) {
        foreach ($bases as $taxonomy => $bas) {
            foreach ($bas as $key => $ba) {
                $newRules = $newRules + array( $ba . '/([^/]+)/?$' => 'index.php?' . $taxonomy . '=$matches[1]' );
                $newRules = $newRules + array( $ba . '/([^/]+)/page/([^/]+)/?$' => 'index.php?' . $taxonomy . '=$matches[1]&paged=$matches[2]' );
            }
        }
    }
    $newRules = $newRules + $aRules;

    return $newRules;
}


/* 
 * Replace base in term links
 */ 
add_filter( 'term_link', 'blueglass_term_link_replace', 999, 3 );
function blueglass_term_link_replace( $term_link, $term, $taxonomy ) {
    global $rewrite_bases, $languages;

    if( !empty($rewrite_bases) && !empty($languages) ):
        foreach ($rewrite_bases as $base => $bases) {
            foreach ($bases as $taxon => $bas) {

                if ( $taxonomy == $taxon ) {

                    foreach ($languages as $lang => $language) {
                        if ( icl_object_id( $term->term_id, $taxon, false, $lang ) == $term->term_id ) {
                            $term_link = str_replace( '/'.$base.'/', '/'.$bas[$lang].'/', $term_link );
                        }
                    }
                
                }
            }
        }
    endif;
    return $term_link;
}

?>