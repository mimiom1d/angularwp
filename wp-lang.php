<?php 
/**
 * Reference: https://code.tutsplus.com/articles/new-wp-config-tweaks-you-probably-dont-know--wp-35396
 */
// start the session 
session_start(); 
 
// if there's a "lang" parameter in the URL...  
if( isset( $_GET[ 'lang' ] ) ) { 
 
    // ...set a session variable named WPLANG based on the URL parameter...     
    $_SESSION[ 'WPLANG' ] = $_GET[ 'lang' ]; 
 
    // ...and define the WPLANG constant with the WPLANG session variable 
    define( 'WPLANG', $_SESSION[ 'WPLANG' ] ); 
 
// if there isn't a "lang" parameter in the URL...  
} else {
 
    // if the WPLANG session variable is already set...
    if( isset( $_SESSION[ 'WPLANG' ] ) ) {
 
        // ...define the WPLANG constant with the WPLANG session variable 
        define( 'WPLANG', $_SESSION[ 'WPLANG' ] );  
 
    // if the WPLANG session variable isn't set...
    } else { 
         
        // set the WPLANG constant to your default language code is (or empty, if you don't need it)        
        define( 'WPLANG', 'en_US' ); // ** en_GB for UK, ja_JP for Japan,  es_ES for Spanish
             
    } 
} 
?>