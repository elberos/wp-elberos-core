<?php

/*!
 *  Elberos Framework
 *
 *  (c) Copyright 2019-2021 "Ildar Bikmamatov" <support@elberos.org>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      https://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace Elberos;

// Переопределение SEO код плагина Rank Math на код из шаблона
add_action
(
	'plugins_loaded',
	function ()
	{
		if (class_exists(RankMath::class))
		{
			// Remove RankMath SEO Frontend Block
			$rank_math = RankMath::get();
			remove_action( 'plugins_loaded', [ $rank_math, 'init_frontend' ], 15 );
		}
	},
	0
);


// Сайт
class Site
{
	/**
	 * Context
	 */
	public $wp_query = null;
	public $site_name = "";
	public $site_url = "";
	public $robots;
	public $categories = null;
	public $language;
	public $language_code;
	public $locale_prefix;
	public $routes;
	public $route_info = null;
	public $request = null;
	public $f_inc = "";
	public $search_text = "";
	public $page_vars = [];
	public $page = 0;
	public $pages = 0;
	public $breadcrumbs = null;
	public $title = "";
	public $title_suffix = " | ";
	public $full_title = "";
	public $description = "";
	public $og_type = "";
	public $og_image = "";
	public $open_graph = [];
	public $article_publisher = "";
	public $article_tags = [];
	public $article_section = "";
	public $article_published_time = "";
	public $article_modified_time = "";
	public $canonical_url = "";
	public $canonical_url_un_paged = "";
	public $prev_url = "";
	public $next_url = "";
	public $term = null;
	public $post = null;
	public $post_id = "";
	public $post_category = null;
	public $current_category = null;
	public $initialized = false;
	public $current_user = null;
	public $jwt = null;
	public $index_twig = 'pages/index.twig';
	public $context = [];
	public $twig = null;
	public $twig_loader = null;
	public $twig_cache = true;
	public $twig_templates = ["templates"];
	public $posts = null;
	
	
	/** Constructor **/
	
	public function __construct() 
	{
		// Register hooks
		$this->register_hooks();
		
		// Register routes
		do_action('elberos_register_routes', $this);
		$this->register_routes();
	}
	
	
	
	/** Theme settings **/
	
	public function action_widgets_init()
	{
	}
	
	
	
	public function action_theme_supports() 
	{
	}
	
	
	
	/** This is where you can register custom post types. */
	public function action_register_post_types()
	{
		
	}
	
	
	
	/** This is where you can register custom taxonomies. */
	public function action_register_taxonomies()
	{

	}
	
	
	
	/**
	 * Extend context
	 */
	function extend_context($context)
	{
		return $context;
	}
	
	
	
	/**
	 * Assets increment
	 */
	function get_f_inc()
	{
		return 1;
	}
	
	
	
	/**
	 * Custom routes
	 */
	function register_routes()
	{
	}
	
	
	
	/**
	 * After setup
	 */
	function setup_after()
	{
		
	}
	
	
	
	/**
	 * Render custom route
	 */
	function render_page($template, $context = null)
	{
		if ($context == null) $context = $this->context;
		if (gettype($template) == 'array')
		{
			foreach ($template as $t)
			{
				try
				{
					$res = $this->twig->render($t, $context);
					return $res;
				}
				catch (\Twig\Error\LoaderError $err)
				{
				}
			}
		}
		else
		{
			return $this->twig->render($template, $context);
		}
		return "";
	}
	
	
	
	/**
	 * Render
	 */
	function render()
	{
		$context = $this->context;
		$template = $this->index_twig;
		if ($this->route_info != null)
		{
			$template = $this->route_info['template'];
		}
		if (isset($this->route_info['params']['context']))
		{
			$context = $this->route_info['params']['context']($this, $context);
		}
		return $this->render_page($template, $context);
	}
	
	
	
	/** Setup **/
	
	public function setup()
	{
		global $wp_query;
		
		/* Init */
		$this->setup_init();
		
		/* Setup base variables */
		$this->wp_query = $wp_query;
		$this->site_url = get_site_url();
		$this->site_name = $this->get_site_name();
		$this->theme_link = get_template_directory_uri();
		$this->f_inc = $this->get_f_inc();
		$this->search_text = isset($_GET['s']) ? $_GET['s'] : "";
		$post = get_queried_object();
		if ($post != null && $post instanceof \WP_POST)
		{
			$this->post = $post;
			$this->post_id = $this->post->ID;
			$this->post_category = get_the_category($this->post_id);
			$this->current_category = isset($this->post_category[0]) ? $this->post_category[0] : null;
		}
		if ($post != null && $post instanceof \WP_Term)
		{
			$this->term = $post;
			$this->current_category = get_category($this->term->cat_ID);
		}
		$this->page_vars =
		[
			"wp_show" => THEME_WP_SHOW,
			"is_admin" => current_user_can('administrator'),
			"is_archive" => is_archive(),
			"is_category" => is_category(),
			"is_page" => is_page(),
			"is_home" => is_home() && $this->route_info == null,
			"is_front_page" => is_front_page(),
			"is_single" => is_single(),
			"is_singular" => is_singular(),
			"is_search" => is_search(),
			"is_post" => $this->post instanceof \WP_POST,
			"is_404" => is_404(),
			"have_posts" => have_posts(),
		];
		$this->language = get_locale();
		$this->language_code = $this->get_current_locale_code();
		$this->locale_prefix = "";
		$langs = \Elberos\wp_langs();
		if ($langs != null && count($langs) > 0)
		{
			$this->locale_prefix = "/" . $this->language_code;
		}
		$this->title = $this->get_page_title();
		$this->full_title = $this->get_page_full_title($this->title);
		$this->description = $this->get_page_description();
		$this->robots = $this->get_page_robots();
		$this->page = max( 1, (int) get_query_var( 'paged' ) );
		$this->max_pages = $this->wp_query->max_num_pages;
		$this->canonical_url = $this->get_canonical_url();
		$this->canonical_url_un_paged = $this->get_canonical_url(true);
		
		/* Setup canonical, prev and next url */
		$this->setup_links();
		
		/* Setup article tags */
		$this->setup_article_tags();
		
		/* Setup breadcrumbs */
		$this->setup_breadcrumbs();
		
		/* Create twig */
		$this->create_twig();
		
		/* Create context */
		$this->create_context();
		
		/* Set initialized */
		$this->initialized = true;
		
		/* Call action */
		do_action('elberos_setup_after', $this);
		
		/* After setup */
		$this->setup_after();
	}
	
	public function setup_init()
	{
	}
	
	public function setup_breadcrumbs()
	{
		$category_base = get_option("category_base", "");
		
		$this->breadcrumbs = [];
		$this->add_breadcrumbs("Главная", "/");
		
		if ($this->route_info != null)
		{
			if (isset($this->route_info['params']))
			{
				$title = isset($this->route_info['params']['title']) ?
					$this->route_info['params']['title'] : "";
				$this->add_breadcrumbs($title, $this->request['uri']);
			}
		}
		
		if ($this->page_vars["is_category"] or $this->page_vars["is_single"])
		{
			$url = "";
			if ($category_base != "" && $category_base != ".")
			{
				$url .= "/articles";
				$this->add_breadcrumbs("Статьи", $url);
			}
			
			if ($this->current_category != null)
			{
				$arr = [];
				$cat_id = $this->current_category->term_id;
				while ($cat_id != 0)
				{
					$cat = $this->get_category_by_id($cat_id);
					$arr[] = $cat; 
					$cat_id = $cat->parent;
				}
				$arr = array_reverse($arr);
				foreach ($arr as $cat)
				{
					$url .= "/" . $cat->slug;
					$this->add_breadcrumbs($cat->name, $url);
				}
			}
		}
		
		if ($this->page_vars["is_page"] && $this->post instanceof \WP_POST)
		{
			$this->add_breadcrumbs($this->post->post_title, $this->remove_site_url(get_the_permalink($this->post)) );
		}
		else if ($this->page_vars["is_single"] && $this->post instanceof \WP_POST)
		{
			$this->add_breadcrumbs($this->post->post_title, $this->remove_site_url(get_the_permalink($this->post)) );
		}
		
		if ($this->page > 1)
		{
			/* $this->add_breadcrumbs("Страница " . $this->page, urlGetAdd($this->request)); */
		}
		
		//var_dump($this->breadcrumbs);
	}
	
	public function setup_links()
	{
		if (!is_singular())
		{
			$canonical_url_get = "";
			$canonical_url = $this->canonical_url_un_paged;
			$paged = max( 1, (int) get_query_var( 'paged' ) );
			$max_pages = $this->max_pages;
			
			$is_get = strpos($canonical_url, "?");
			if ($is_get)
			{
				$canonical_url_get = substr($canonical_url, $is_get);
				$canonical_url = substr($canonical_url, 0, $is_get);
			}
			
			$prev_url = ($paged <= 2) ? $canonical_url : $this->url_concat($canonical_url, "/page/" . ($paged - 1));
			$next_url = $this->url_concat($canonical_url, "/page/" . ($paged + 1));
			
			//var_dump($canonical_url);
			//var_dump($canonical_url_get);
			
			if ($is_get)
			{
				$prev_url .= $canonical_url_get;
				$next_url .= $canonical_url_get;
			}
			if ($paged >= 2 && $paged < $max_pages)
			{
				$this->prev_url = $prev_url;
			}
			if ($paged < $max_pages)
			{
				$this->next_url = $next_url;
			}
		}
	}
	
	public function setup_article_tags()
	{
		if ($this->post instanceof \WP_POST)
		{
			$this->og_type = "article";
			
			$dt = new \DateTime($this->post->post_date_gmt, new \DateTimezone("UTC"));
			$dt->setTimezone( new \DateTimezone(date_default_timezone_get()) );
			$this->article_published_time = $dt->format("c");
			
			$dt = new \DateTime($this->post->post_modified_gmt, new \DateTimezone("UTC"));
			$dt->setTimezone( new \DateTimezone(date_default_timezone_get()) );
			$this->article_modified_time = $dt->format("c");
			
			/* Setup article section */
			if ($this->post_category and count($this->post_category) > 0)
			{
				$this->article_section = $this->post_category[0]->name;
			}
			
			/* Setup article tags */
			$tags = wp_get_post_tags($this->post_id);
			$this->article_tags = array_map( function ($item) { return $item->name; }, $tags );
			
			/* Setup publisher */
			$this->article_publisher = $this->get_site_name();
			
			/* Setup og image */
			$media_id = get_post_thumbnail_id($this->post->ID);
			if ($media_id)
			{
				$image = wp_get_attachment_image_src($media_id, "medium_large");
				$image_href = isset($image[0]) ? $image[0] : null;
				if ($image_href)
				{
					$image_time = strtotime($this->post->post_modified);
					$this->og_image = $image_href . '?_' . $image_time;
				}
			}
		}
	}
	
	
	/**
	 * Create context
	 */
	public function create_context()
	{
		/* Setup context */
		$context = [];
		$context['site'] = $this;
		$context = $this->extend_context($context);
		$context = apply_filters( 'elberos_context', $context );
		$this->context = $context;
	}
	
	
	/**
	 * Create twig
	 */
	public function create_twig()
	{
		$twig_opt = array
		(
			'autoescape'=>true,
			'charset'=>'utf-8',
			'optimizations'=>-1,
		);

		/* Enable cache */
		if ($this->twig_cache)
		{
			$twig_opt['cache'] = ABSPATH.'wp-content/cache/twig';
			$twig_opt['auto_reload'] = true;
		}
		
		/* Create twig loader */
		$this->twig_loader = new \Twig\Loader\FilesystemLoader();
		foreach ($this->twig_templates as $template)
		{
			$this->twig_loader->addPath(get_template_directory() . '/' . $template);
		}
		do_action('elberos_twig_loader', [$this->twig_loader]);
		
		/* Create twig instance */
		$this->twig = new \Twig\Environment
		(
			$this->twig_loader,
			$twig_opt
		);
		
		/* Set strategy */
		$this->twig->getExtension(\Twig\Extension\EscaperExtension::class)->setDefaultStrategy('html');
		
		/* Do action */
		do_action('elberos_twig', [$this->twig]);
	}
	
	
	
	/** Functions **/
	
	/**
	 * Add breadcrumbs
	 */
	public function add_breadcrumbs($title, $url)
	{
		$this->breadcrumbs[] = [
			'title' => $title,
			'url' => $url,
		];
	}
	
	/**
	 * Add route
	 */
	public function add_route($route_name, $match, $template = null, $params=[])
	{
		$this->routes[$route_name] = 
		[
			'route_name' => $route_name,
			'template' => $template,
			'params' => $params,
			'match' => $match,
		];
	}
	
	function get_route_params()
	{
		if ($this->route_info == null) return null;
		if (!isset($this->route_info['params'])) return null;
		return $this->route_info['params'];
	}
	
	function get_category_by_id($cat_id)
	{
		return get_category($cat_id);
	}
	
	function remove_site_url($url)
	{
		$site_url = $this->site_url;
		if (strpos($url, $site_url) === 0) $url = substr($url, strlen($site_url));
		return $url;
	}
	
	function get_canonical_url($un_paged = false)
	{
		global $wp;
		
		$is_langs = \Elberos\is_langs();
		$site_url = $this->site_url;
		$paged = max( 1, (int) get_query_var( 'paged' ) );
		
		if ($is_langs)
		{
			$locale_code = $this->get_current_locale_code();
			$uri = $this->request['uri'];
			
			if (strpos($uri, "/" . $locale_code) === 0)
			{
				$uri = substr($uri, strlen($locale_code) + 1);
			}
			
			if ($un_paged)
			{
				$str = "page/" . $paged;
				$pos = strpos($uri, $str);
				if ($pos !== false)
				{
					$uri = substr($uri, 0, $pos) . substr($uri, $pos + strlen($str));
				}
			}
			
			$url = $this->url_concat($site_url . "/" . $locale_code, $uri);
		}
		else
		{
			$uri = $this->request['uri'];
			$url = $this->url_concat($site_url, $uri);
		}
		
		if (substr($url, -1) == "/") $url = substr($url, 0, -1);
		if ((is_home() && $this->route_info == null && $paged == 1) || $uri == false) $url .= "/";
		
		return $url;
	}
	
	function get_canonical_url_page($page)
	{
		$canonical_url = $this->canonical_url_un_paged;
		
		$is_get = strpos($canonical_url, "?");
		if ($is_get)
		{
			$canonical_url_get = substr($canonical_url, $is_get);
			$canonical_url = substr($canonical_url, 0, $is_get);
		}
		
		$url = ($page <= 1) ? $canonical_url : $this->url_concat($canonical_url, "/page/" . $page);
		
		if ($is_get)
		{
			$url .= $canonical_url_get;
		}
		
		return $url;
	}
	
	public function get_site_name()
	{
		return get_bloginfo("name");
	}
	
	public function get_site_description()
	{
		return get_bloginfo("description");
	}
	
	public function get_term_title()
	{
		$vars = $this->page_vars;
		$title = "";
		if ($this->term != null && $this->term->taxonomy == 'category')
		{
			$title = $this->term->name;
		}
		else if ($this->term != null && $this->term->taxonomy == 'post_tag')
		{
			$title = $this->term->name;
		}
		else if (is_archive())
		{
			$title = "Архив за " . $this->get_the_archive_title();
		}
		else if ($this->search_text != null)
		{
			$title = "Результаты поиска для " . $this->search_text;
		}
		else
		{
			$title = get_the_title();
		}
		return $title;
	}
	
	public function get_current_title()
	{
		$route_params = $this->get_route_params();
		if ($route_params != null && isset($route_params['title']))
		{
			return $route_params['title'];
		}
		if ($route_params != null && isset($route_params['full_title']))
		{
			return $route_params['full_title'];
		}
		
		if (is_home())
		{
			$title = $this->get_site_name();
		}
		else
		{
			$title = $this->get_term_title();
		}
		
		return $title;
	}
	
	public function get_page_title()
	{
		$title = $this->get_current_title();
		
		$page = max( 1, (int) get_query_var( 'paged' ) );
		if ($page > 1)
		{
			$title .= " страница " . $page;
		}
		
		return $title;
	}
	
	public function get_page_full_title($title)
	{
		if (is_home() && $this->route_info == null)
		{
			return $title;
		}
		if ($this->route_info != null && isset($this->route_info['params']['full_title']))
		{
			return $this->route_info['params']['full_title'];
		}
		return $title . $this->title_suffix . $this->site_name;
	}
	
	public function get_page_description()
	{
		$str = "";
		$route_params = $this->get_route_params();
		if ($route_params != null)
		{
			return isset($route_params['description']) ? $route_params['description'] : get_bloginfo("description");
		}
		
		if (class_exists(\RankMath\Paper\Paper::class))
		{
			$str = \RankMath\Paper\Paper::get()->get_description();
			if ($str == "")
			{
				$str = get_bloginfo("description");
			}
		}
		
		return $str;
	}
	
	public function get_page_robots()
	{
		if ( class_exists(\RankMath\Paper\Paper::class) )
		{
			$robots = \RankMath\Paper\Paper::get()->get_robots();
			if (!isset($robots['index'])) $robots['index'] = 'index';
			if (!isset($robots['follow'])) $robots['follow'] = 'follow';
			if (isset($robots['max-snippet'])) unset($robots['max-snippet']);
			if (isset($robots['max-video-preview'])) unset($robots['max-video-preview']);
			if (isset($robots['max-image-preview'])) unset($robots['max-image-preview']);
			return implode( ",", array_values($robots) );
		}
		return "";
	}
	
	function get_current_locale_code()
	{
		$locale = get_locale();
		if ($locale == "ru_RU") return "ru";
		else if ($locale == "en_US") return "en";
		return "";
	}
	
	public function get_the_archive_title() 
	{
		$title = "";
		
		if ( is_category() ) {
			/* translators: Category archive title. %s: Category name. */
			$title = sprintf( __( '%s' ), single_cat_title( '', false ) );
		} elseif ( is_tag() ) {
			/* translators: Tag archive title. %s: Tag name. */
			$title = sprintf( __( '%s' ), single_tag_title( '', false ) );
		} elseif ( is_author() ) {
			/* translators: Author archive title. %s: Author name. */
			$title = sprintf( __( '%s' ), '<span class="vcard">' . get_the_author() . '</span>' );
		} elseif ( is_year() ) {
			/* translators: Yearly archive title. %s: Year. */
			$title = sprintf( __( '%s' ), get_the_date( _x( 'Y', 'yearly archives date format' ) ) );
		} elseif ( is_month() ) {
			/* translators: Monthly archive title. %s: Month name and year. */
			$title = sprintf( __( '%s' ), get_the_date( _x( 'F Y', 'monthly archives date format' ) ) );
		} elseif ( is_day() ) {
			/* translators: Daily archive title. %s: Date. */
			$title = sprintf( __( '%s' ), get_the_date( _x( 'F j, Y', 'daily archives date format' ) ) );
		}
	 
		/**
		 * Filters the archive title.
		 */
		return apply_filters( 'get_the_archive_title', $title );
	}
	
	
	/** Actions & Filters **/
	
	function register_hooks()
	{
		add_action( 'do_parse_request', array( $this, 'action_do_parse_request' ) );
		add_action( 'after_setup_theme', array( $this, 'action_theme_supports' ) );
		add_action( 'elberos_twig', array( $this, 'action_add_to_twig' ) );
		add_action( 'init', array( $this, 'action_register_post_types' ) );
		add_action( 'init', array( $this, 'action_register_taxonomies' ) );
		add_action( 'init', array( $this, 'action_setup_route' ) );
		add_action( 'widgets_init', array( $this, 'action_widgets_init' ) );
		add_action( 'wp', array( $this, 'setup' ), 99999 );
		add_filter( 'term_link', function($url){ return str_replace('/./', '/', $url); }, 10, 1 );
		
		// Title
		add_filter( 'wp_title', [$this, 'filter_page_title'], 99999, 1 );
		add_filter( 'thematic_doctitle', [$this, 'filter_page_title'], 99999, 1 );
		add_filter( 'pre_get_document_title', [$this, 'filter_page_title'], 99999, 1 );
		add_filter( 'redirect_canonical', [$this, 'filter_redirect_canonical'], 99999, 1 );
		add_filter( 'template_include', [$this, 'filter_template_include'], 99999, 1 );
	}
	
	function filter_redirect_canonical($url)
	{
		if ($this->route_info != null) return false;
		return $url;
	}
	
	function filter_page_title($orig_title)
	{
		if (is_home())
		{
			return $this->title;
		}		
		return $this->title . $this->title_suffix . $this->site_name;
	}
	
	function filter_template_include($template)
	{
		if ($this->route_info != null)
		{
			$t = locate_template("index.php");
			if ($t) return $t;
		}
		return $template;
	}
	
	
	/**
	 * Setup route
	 */
	function action_setup_route()
	{
		$current_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : "/";
		$arr = parse_url($current_uri);
		
		$this->request = [
			"method" => isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : "GET",
			"uri" => $current_uri,
			"path" => isset($arr['path']) ? $arr['path'] : "/",
			"query" => isset($arr['query']) ? $arr['query'] : "",
		];
		$request_uri = $this->request["path"];
		
		if (gettype($this->routes) == 'array')
		{
			$langs = \Elberos\wp_langs();
			if ($langs != null && count($langs) > 0)
			{
				$langs = array_map(function($item){ return $item['slug']; }, $langs);
			}
			foreach ($this->routes as $route)
			{
				$matches = [];
				$match = $route['match'];
				if ($langs != null && count($langs) > 0)
				{
					$match = "/(" . implode("|", $langs) . ")" . $match;
				}
				$match = str_replace("/", "\\/", $match);
				$match = "/^" . $match . "$/i";				
				$flag = preg_match_all($match, $request_uri, $matches);
				if ($flag)
				{
					$this->route_info = $route;
					$this->route_info['matches'] = $matches;
					break;
				}
			}
		}
	}
	
	function action_do_parse_request()
	{
		global $wp;
		
		if ($this->route_info == null)
		{
			return true;
		}
		
		$wp->query_vars = [];
		return false;
	}
	
	
	
	/** Twig functions **/
	
	/**
	 * This is where you can add your own functions to twig.
	 *
	 * @param string $twig get extension.
	 */
	public function action_add_to_twig( $twig )
	{
		// Extensions https://twig.symfony.com/doc/3.x/api.html
		$twig->addExtension( new \Twig\Extension\StringLoaderExtension() );	
		
		// Undefined functions
		$twig->registerUndefinedFunctionCallback(function ($name) {
			if (!function_exists($name))
			{
				return false;
			}
			return new \Twig\TwigFunction($name, $name);
		});
		$twig->registerUndefinedFilterCallback(function ($name) {
			if (!function_exists($name))
			{
				return false;
			}
			return new \Twig\TwigFunction($name, $name);
		});
		
		// Default functions
		$twig->addFunction( new \Twig\TwigFunction( 'count', array( $this, 'get_count' ) ) );
		$twig->addFunction( new \Twig\TwigFunction( 'dump', array( $this, 'var_dump' ) ) );
		$twig->addFunction( new \Twig\TwigFunction( 'url', array( $this, 'url_new' ) ) );
		$twig->addFunction( new \Twig\TwigFunction( 'function', function($name)
		{
			$args = func_get_args();
			array_shift($args);
			return call_user_func_array($name, $args);
		} ) );
	}
	
	function get_sub_categories($categories, $parent_id)
	{
		if (gettype($categories) != 'array') return [];
		return array_filter
		(
			$categories,
			function ($item) use ($parent_id)
			{
				if ($item->parent == $parent_id) return true;
				return false;
			}
		);
	}
	
	function url_concat($url, $add)
	{
		if (strlen($add) == 0) return $url;
		if ($add[0] == "/") $add = substr($add, 1);
		if (strlen($add) == 0) return $url;
		if (substr($url, -1) != "/") return $url . "/" . $add;
		return $url . $add;
	}
	
	function url_new($name, $params=null)
	{
		return isset($this->routes[$name]) ? $this->routes[$name]['match'] : '';
	}

	function isRouteNameBegins()
	{
		return false;
	}

	function isUrlsEquals()
	{
		return false;
	}
	
	function post_preview($content, $count = 150)
	{
		$allowed_tags = "";
		$f = preg_match('/<!--\s?more(.*?)?-->/', $content, $readmore_matches);
		if ($readmore_matches != null and isset($readmore_matches[0]))
		{
			$pieces = explode($readmore_matches[0], $content);
			if ($f)
			{
				$text = $pieces[0];
			}
			else
			{
				$content = preg_replace('/<!--\s?more(.*?)?-->/', '', $content);
				$content = strip_tags($content, $allowed_tags);
				$content = trim(preg_replace("/[\n\r\t ]+/", ' ', $content), ' ');
				$text = mb_substr($content, 0, $count) . "...";
			}
		}
		else
		{
			$content = preg_replace('/<!--\s?more(.*?)?-->/', '', $content);
			$content = strip_tags($content, $allowed_tags);
			$content = trim(preg_replace("/[\n\r\t ]+/", ' ', $content), ' ');
			$text = mb_substr($content, 0, $count) . "...";
		}
		$preview = $text;
		//$preview = str_replace("<p>", "", $preview);
		//$preview = str_replace("</p>", "<br/>", $preview);
		//$preview = \Timber\TextHelper::trim_words($preview, $count, "", "a span b i br blockquote p");
		
		return $preview;
	}
	
	function to_money($value, $decimals = 2)
	{
		return number_format($value, $decimals, ".", " ");
	}
	
	function get_count($x)
	{
		return count($x);
	}
	
	function var_dump($v)
	{
		echo "<pre>";
		var_dump($v);
		echo "</pre>";
		return "";
	}
	
	function load_template_css($css_name, $flag = false)
	{
		$s = "";
		if ($flag)
		{
			$s = '<link rel="stylesheet" href="'.$this->theme->link."/".$css_name.'?_'.$this->f_inc.'" '.
				'type="text/css" media="screen" />';
		}
		else
		{
			$uri = get_template_directory_uri();
			$path = get_template_directory() . "/" . $css_name;
			if (file_exists($path))
			{
				$s = file_get_contents($path);
				$s = str_replace("site.css.map", $uri . "/static/site.css.map", $s);
			}
			$s = "<style>" . $s . "</style>";
		}
		return $s;
	}
	
	
	/**
	 * Create new instance
	 */
	public function newInstance($class_name, $params = [])
	{
		$reflectionClass = new \ReflectionClass($class_name);
		$obj = $reflectionClass->newInstanceArgs($params);
		return $obj;
	}
	
	
	/**
	 * Get image from item
	 */
	function get_image_from_item($data, $image_name)
	{
		$field = @json_decode($data, true);
		if ($field == null) return "";
		
		$image = isset($field[$image_name]) ? $field[$image_name] : null;
		if ($image == null) return "";
		
		$path = isset($image['path']) ? $image['path'] : "";
		if ($path == "") return "";
		
		$inc = isset($image['inc']) ? $image['inc'] : "1";
		return $path . "?_=" . $inc;
	}
	
	
	
	/**
	 * Returns wordpress posts
	 */
	function get_posts_from_query($wp_query)
	{
		return $wp_query->get_posts();
	}
	
	
	
	/**
	 * Returns wordpress posts
	 */
	function require_current_posts()
	{
		if ($this->posts == null)
		{
			$this->posts = $this->get_posts_from_query($this->wp_query);
		}
		return $this->posts;
	}
	
	
	/**
	 * Returns wordpress categories
	 */
	function require_current_categories()
	{
		if ($this->categories == null)
		{
			$this->categories = get_categories();
		}
		return $this->categories;
	}
}
