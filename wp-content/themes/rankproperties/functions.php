<?php
function theme_enqueue_styles() {

    $parent_style = 'parent-style';

    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style )
    );
	
	wp_enqueue_script( 'rankmenu', get_stylesheet_directory_uri() . '/js/script.js', 'jquery', '1.0', true );
	
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );

add_filter( 'wp_nav_menu_items', 'add_login_logout_menu', 20, 5);

    function add_login_logout_menu($items, $args)
    {
		global $wp;

	   $ex_link = explode('/',$wp->request);
	   
	   
		if( is_user_logged_in( ) ){
			
			if($ex_link[0] == 'my-search')
				$link = '<li id="login_logout_menu-link" class="current-menu-item menu-item menu-type-link"><a href="' .esc_url( home_url( '/' )). 'my-search' . '" title="My Search">' . __( 'My Search' ) . '</a></li>';           
			 else
				 $link = '<li id="login_logout_menu-link" class="menu-item menu-type-link"><a href="' .esc_url( home_url( '/' )). 'my-search' . '" title="My Search">' . __( 'My Search' ) . '</a></li>';           
			
				 $link .= '<li id="login_logout_menu-link" class="menu-item menu-type-link"><a href="'.wp_logout_url( esc_url( home_url( '/' )) ).'">Logout</a></li>';
		}
		 else  {
			 if($ex_link[0] == 'signup')
				$link = '<li id="login_logout_menu-link" class="current-menu-item menu-item menu-type-link"><a href="' .esc_url( home_url( '/' )). 'signup' . '" title="Signup">' . __( 'Login / Signup' ) . '</a></li>';
			 else
				 $link = '<li id="login_logout_menu-link" class="menu-item menu-type-link"><a href="' .esc_url( home_url( '/' )). 'signup' . '" title="Signup">' . __( 'Login / Signup' ) . '</a></li>';
		}

        return $items .=  $link;
    }



