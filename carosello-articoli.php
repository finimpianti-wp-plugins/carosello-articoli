<?php
/**
 * Plugin Name:       Carosello Articoli
 * Description:       Carosello di articoli filtrati per tag, da inserire nelle pagine con lo shortcode [carosello_articoli]. Impostazioni in Bacheca → Carosello Articoli.
 * Version:           1.1.1
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Update URI:        false
 * Text Domain:       carosello-articoli
 * License:           GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

define( 'CDA_VERSION', '1.1.1' );
define( 'CDA_URL', plugin_dir_url( __FILE__ ) );
define( 'CDA_OPTION', 'cda_impostazioni' );
define( 'CDA_SHORTCODE', 'carosello_articoli' );

/* -------------------------------------------------------------------------
 * Impostazioni: definizione campi, valori predefiniti e sanificazione
 * ---------------------------------------------------------------------- */

/**
 * Elenco dei campi, raggruppati per sezione.
 * Ogni chiave è anche il nome dell'attributo utilizzabile nello shortcode.
 */
function cda_fields() {
	return array(
		'contenuto'   => array(
			'title'  => 'Contenuto',
			'fields' => array(
				'tag'          => array( 'type' => 'tag', 'label' => 'Tag degli articoli', 'default' => '', 'desc' => 'Vengono mostrati solo gli articoli con questo tag. Vuoto = tutti. Nello shortcode puoi indicare più tag separati da virgola.' ),
				'post_type'    => array( 'type' => 'post_type', 'label' => 'Tipo di contenuto', 'default' => 'post' ),
				'numero'       => array( 'type' => 'int', 'label' => 'Numero massimo di articoli', 'default' => 10, 'min' => 1, 'max' => 50 ),
				'righe'        => array( 'type' => 'int', 'label' => 'Righe massime della descrizione', 'default' => 3, 'min' => 1, 'max' => 6 ),
				'nuova_scheda' => array( 'type' => 'bool', 'label' => 'Apri gli articoli in una nuova scheda', 'default' => 1 ),
			),
		),
		'layout'      => array(
			'title'  => 'Dimensioni e layout',
			'fields' => array(
				'altezza'         => array( 'type' => 'int', 'label' => 'Altezza del carosello', 'default' => 500, 'min' => 200, 'max' => 1200, 'unit' => 'px' ),
				'img_altezza'     => array( 'type' => 'int', 'label' => 'Altezza immagine nella card', 'default' => 55, 'min' => 25, 'max' => 80, 'unit' => '%', 'desc' => 'Percentuale dell\'altezza della card occupata dall\'immagine.' ),
				'img_adattamento' => array( 'type' => 'select', 'label' => 'Adattamento immagine', 'default' => 'cover', 'options' => array( 'cover' => 'Riempi la card (ritaglia i bordi)', 'contain' => 'Mostra l\'immagine intera' ) ),
				'desktop'         => array( 'type' => 'int', 'label' => 'Card visibili su desktop', 'default' => 3, 'min' => 1, 'max' => 6 ),
				'tablet'          => array( 'type' => 'int', 'label' => 'Card visibili su tablet', 'default' => 2, 'min' => 1, 'max' => 6 ),
				'mobile'          => array( 'type' => 'int', 'label' => 'Card visibili su mobile', 'default' => 1, 'min' => 1, 'max' => 6 ),
				'bp_tablet'       => array( 'type' => 'int', 'label' => 'Larghezza massima tablet', 'default' => 1024, 'min' => 600, 'max' => 1920, 'unit' => 'px', 'desc' => 'Sotto questa larghezza dello schermo si usa il numero di card "tablet" (1024 = come Elementor).' ),
				'bp_mobile'       => array( 'type' => 'int', 'label' => 'Larghezza massima mobile', 'default' => 767, 'min' => 320, 'max' => 1200, 'unit' => 'px', 'desc' => 'Sotto questa larghezza si usa il numero di card "mobile" (767 = come Elementor).' ),
				'gap'             => array( 'type' => 'int', 'label' => 'Spazio tra le card', 'default' => 24, 'min' => 0, 'max' => 80, 'unit' => 'px' ),
				'anteprima'       => array( 'type' => 'int', 'label' => 'Anteprima card laterali', 'default' => 60, 'min' => 0, 'max' => 200, 'unit' => 'px', 'desc' => 'Porzione visibile delle card precedente e successiva ai lati (ridotta automaticamente su schermi piccoli).' ),
				'sfumatura'       => array( 'type' => 'bool', 'label' => 'Sfuma le card laterali', 'default' => 1 ),
			),
		),
		'stile'       => array(
			'title'  => 'Stile card',
			'fields' => array(
				'stile_card' => array( 'type' => 'select', 'label' => 'Stile delle card', 'default' => 'base', 'options' => array(
					'base'        => 'Base – card bianca, diventa verde al passaggio del mouse',
					'cornice'     => 'Cornice – immagine incorniciata e pulsante "Scopri di più"',
					'scura'       => 'Scura – card verde scuro con testo chiaro',
					'sovrapposta' => 'Sovrapposta – testo sopra l\'immagine',
					'minimal'     => 'Minimal – senza card, immagine arrotondata e testo libero',
				) ),
				'testo_link' => array( 'type' => 'text', 'label' => 'Testo del link', 'default' => 'Scopri di più', 'desc' => 'Usato negli stili Cornice, Scura e Minimal.' ),
			),
		),
		'navigazione' => array(
			'title'  => 'Navigazione',
			'fields' => array(
				'frecce'           => array( 'type' => 'bool', 'label' => 'Mostra le frecce', 'default' => 1 ),
				'frecce_posizione' => array( 'type' => 'select', 'label' => 'Posizione frecce', 'default' => 'laterali', 'options' => array( 'laterali' => 'Ai lati del carosello', 'sotto' => 'Sotto, a destra' ) ),
				'pallini'          => array( 'type' => 'bool', 'label' => 'Mostra i pallini', 'default' => 1 ),
				'trascinamento'    => array( 'type' => 'bool', 'label' => 'Scorrimento con trascinamento (mouse e touch)', 'default' => 1 ),
				'loop'             => array( 'type' => 'bool', 'label' => 'Scorrimento infinito', 'default' => 1 ),
			),
		),
		'autoplay'    => array(
			'title'  => 'Scorrimento automatico',
			'fields' => array(
				'autoplay'    => array( 'type' => 'bool', 'label' => 'Attiva lo scorrimento automatico', 'default' => 1 ),
				'intervallo'  => array( 'type' => 'int', 'label' => 'Intervallo tra uno scorrimento e l\'altro', 'default' => 4000, 'min' => 1000, 'max' => 30000, 'unit' => 'ms', 'desc' => '1000 ms = 1 secondo.' ),
				'velocita'    => array( 'type' => 'int', 'label' => 'Durata dell\'animazione', 'default' => 500, 'min' => 100, 'max' => 3000, 'unit' => 'ms' ),
				'pausa_hover' => array( 'type' => 'bool', 'label' => 'Metti in pausa al passaggio del mouse', 'default' => 1 ),
			),
		),
		'colori'      => array(
			'title'  => 'Colori',
			'fields' => array(
				'colore_primario' => array( 'type' => 'color', 'label' => 'Primario (hover card, pallino attivo)', 'default' => '#06A34A' ),
				'colore_scuro'    => array( 'type' => 'color', 'label' => 'Scuro (testi, frecce)', 'default' => '#012611' ),
				'colore_medio'    => array( 'type' => 'color', 'label' => 'Medio (hover pallini)', 'default' => '#6BBF89' ),
				'colore_chiaro'   => array( 'type' => 'color', 'label' => 'Chiaro (pallini, sfondo immagini)', 'default' => '#ADD9C0' ),
				'colore_card'     => array( 'type' => 'color', 'label' => 'Sfondo card', 'default' => '#FFFFFF' ),
				'colore_sfondo'   => array( 'type' => 'color', 'label' => 'Sfondo del carosello', 'default' => '#E5E5E5' ),
			),
		),
	);
}

/** Campi in forma piatta: chiave => definizione. */
function cda_flat_fields() {
	$flat = array();
	foreach ( cda_fields() as $section ) {
		$flat += $section['fields'];
	}
	return $flat;
}

function cda_defaults() {
	return wp_list_pluck( cda_flat_fields(), 'default' );
}

function cda_get_options() {
	$saved = get_option( CDA_OPTION, array() );
	return wp_parse_args( is_array( $saved ) ? $saved : array(), cda_defaults() );
}

/** Sanifica un singolo valore (usato sia dal form che dagli attributi dello shortcode). */
function cda_sanitize_value( $field, $value ) {
	switch ( $field['type'] ) {
		case 'bool':
			return in_array( strtolower( trim( (string) $value ) ), array( '1', 'si', 'sì', 'true', 'yes', 'on' ), true ) ? 1 : 0;
		case 'int':
			return max( $field['min'], min( $field['max'], (int) $value ) );
		case 'select':
			return array_key_exists( $value, $field['options'] ) ? $value : $field['default'];
		case 'color':
			$color = sanitize_hex_color( $value );
			return $color ? $color : $field['default'];
		case 'tag':
			return implode( ',', array_filter( array_map( 'sanitize_title', explode( ',', (string) $value ) ) ) );
		case 'text':
			return sanitize_text_field( $value );
		case 'post_type':
			$value = sanitize_key( $value );
			return post_type_exists( $value ) ? $value : $field['default'];
	}
	return $field['default'];
}

function cda_sanitize( $input ) {
	if ( isset( $_POST['cda_reset'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- verificato da options.php
		add_settings_error( CDA_OPTION, 'cda_reset', 'Impostazioni ripristinate ai valori predefiniti.', 'updated' );
		return cda_defaults();
	}
	$input = is_array( $input ) ? $input : array();
	$clean = array();
	foreach ( cda_flat_fields() as $key => $field ) {
		if ( 'bool' === $field['type'] ) {
			$clean[ $key ] = empty( $input[ $key ] ) ? 0 : 1;
		} else {
			$clean[ $key ] = cda_sanitize_value( $field, isset( $input[ $key ] ) ? $input[ $key ] : $field['default'] );
		}
	}
	return $clean;
}

/* -------------------------------------------------------------------------
 * Pagina di amministrazione
 * ---------------------------------------------------------------------- */

add_action( 'admin_menu', function () {
	add_menu_page( 'Carosello Articoli', 'Carosello Articoli', 'manage_options', 'carosello-articoli', 'cda_render_admin_page', 'dashicons-slides', 58 );
} );

add_action( 'admin_init', function () {
	register_setting( 'cda_group', CDA_OPTION, array(
		'type'              => 'array',
		'sanitize_callback' => 'cda_sanitize',
		'default'           => cda_defaults(),
	) );

	foreach ( cda_fields() as $section_id => $section ) {
		add_settings_section( 'cda_' . $section_id, $section['title'], '__return_false', 'carosello-articoli' );
		foreach ( $section['fields'] as $key => $field ) {
			add_settings_field( 'cda_' . $key, $field['label'], 'cda_render_field', 'carosello-articoli', 'cda_' . $section_id, array(
				'key'       => $key,
				'field'     => $field,
				'label_for' => 'cda_' . $key,
			) );
		}
	}
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( 'toplevel_page_carosello-articoli' !== $hook ) {
		return;
	}
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );
	wp_add_inline_script( 'wp-color-picker', 'jQuery(function($){$(".cda-color").wpColorPicker();});' );
} );

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function ( $links ) {
	array_unshift( $links, '<a href="' . esc_url( admin_url( 'admin.php?page=carosello-articoli' ) ) . '">Impostazioni</a>' );
	return $links;
} );

function cda_render_field( $args ) {
	$key   = $args['key'];
	$field = $args['field'];
	$value = cda_get_options()[ $key ];
	$id    = 'cda_' . $key;
	$name  = CDA_OPTION . '[' . $key . ']';

	switch ( $field['type'] ) {
		case 'int':
			printf( '<input type="number" id="%s" name="%s" value="%d" min="%d" max="%d" class="small-text"> %s',
				esc_attr( $id ), esc_attr( $name ), (int) $value, (int) $field['min'], (int) $field['max'], esc_html( isset( $field['unit'] ) ? $field['unit'] : '' ) );
			break;

		case 'text':
			printf( '<input type="text" id="%s" name="%s" value="%s" class="regular-text">', esc_attr( $id ), esc_attr( $name ), esc_attr( $value ) );
			break;

		case 'bool':
			printf( '<input type="checkbox" id="%s" name="%s" value="1" %s>', esc_attr( $id ), esc_attr( $name ), checked( $value, 1, false ) );
			break;

		case 'select':
			printf( '<select id="%s" name="%s">', esc_attr( $id ), esc_attr( $name ) );
			foreach ( $field['options'] as $opt => $label ) {
				printf( '<option value="%s" %s>%s</option>', esc_attr( $opt ), selected( $value, $opt, false ), esc_html( $label ) );
			}
			echo '</select>';
			break;

		case 'color':
			printf( '<input type="text" id="%s" name="%s" value="%s" class="cda-color" data-default-color="%s">',
				esc_attr( $id ), esc_attr( $name ), esc_attr( $value ), esc_attr( $field['default'] ) );
			break;

		case 'tag':
			$tags = get_terms( array( 'taxonomy' => 'post_tag', 'hide_empty' => false ) );
			printf( '<select id="%s" name="%s">', esc_attr( $id ), esc_attr( $name ) );
			echo '<option value="">— Nessun filtro (tutti gli articoli) —</option>';
			if ( ! is_wp_error( $tags ) ) {
				foreach ( $tags as $tag ) {
					printf( '<option value="%s" %s>%s (%d)</option>', esc_attr( $tag->slug ), selected( $value, $tag->slug, false ), esc_html( $tag->name ), (int) $tag->count );
				}
			}
			echo '</select>';
			break;

		case 'post_type':
			printf( '<select id="%s" name="%s">', esc_attr( $id ), esc_attr( $name ) );
			foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $pt ) {
				if ( 'attachment' === $pt->name ) {
					continue;
				}
				printf( '<option value="%s" %s>%s</option>', esc_attr( $pt->name ), selected( $value, $pt->name, false ), esc_html( $pt->labels->name ) );
			}
			echo '</select>';
			break;
	}

	if ( ! empty( $field['desc'] ) ) {
		printf( '<p class="description">%s</p>', esc_html( $field['desc'] ) );
	}
}

function cda_render_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1>Carosello Articoli</h1>

		<div class="card" style="max-width:860px">
			<h2>Come si usa</h2>
			<p>Inserisci nella pagina (in Elementor con il widget <strong>Shortcode</strong>) il codice:</p>
			<p><code>[<?php echo esc_html( CDA_SHORTCODE ); ?>]</code></p>
			<p>Il carosello usa le impostazioni qui sotto. Per avere più caroselli diversi puoi sovrascrivere qualsiasi impostazione direttamente nello shortcode, ad esempio:</p>
			<p><code>[<?php echo esc_html( CDA_SHORTCODE ); ?> tag="fiere" altezza="450" desktop="4" autoplay="no"]</code></p>
			<details>
				<summary style="cursor:pointer"><strong>Elenco di tutti gli attributi dello shortcode</strong></summary>
				<table class="widefat striped" style="margin-top:10px">
					<thead><tr><th>Attributo</th><th>Impostazione</th><th>Valori</th></tr></thead>
					<tbody>
					<?php foreach ( cda_flat_fields() as $key => $field ) : ?>
						<tr>
							<td><code><?php echo esc_html( $key ); ?></code></td>
							<td><?php echo esc_html( $field['label'] ); ?></td>
							<td>
								<?php
								switch ( $field['type'] ) {
									case 'bool':
										echo 'si / no';
										break;
									case 'int':
										echo esc_html( $field['min'] . '–' . $field['max'] . ( isset( $field['unit'] ) ? ' ' . $field['unit'] : '' ) );
										break;
									case 'select':
										echo esc_html( implode( ' / ', array_keys( $field['options'] ) ) );
										break;
									case 'color':
										echo 'colore esadecimale, es. #06A34A';
										break;
									case 'text':
										echo 'testo libero';
										break;
									case 'tag':
										echo 'slug del tag, anche più di uno separati da virgola';
										break;
									case 'post_type':
										echo 'post, page, …';
										break;
								}
								?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</details>
		</div>

		<form method="post" action="options.php">
			<?php
			settings_fields( 'cda_group' );
			do_settings_sections( 'carosello-articoli' );
			?>
			<p class="submit">
				<?php submit_button( 'Salva impostazioni', 'primary', 'submit', false ); ?>
				&nbsp;
				<?php submit_button( 'Ripristina predefiniti', 'secondary', 'cda_reset', false, array( 'onclick' => "return confirm('Ripristinare tutte le impostazioni ai valori predefiniti?');" ) ); ?>
			</p>
		</form>
	</div>
	<?php
}

/* -------------------------------------------------------------------------
 * Frontend: risorse e shortcode
 * ---------------------------------------------------------------------- */

/** True se la pagina corrente contiene lo shortcode (contenuto classico o dati Elementor). */
function cda_page_has_shortcode() {
	if ( ! is_singular() ) {
		return false;
	}
	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		return false;
	}
	if ( has_shortcode( $post->post_content, CDA_SHORTCODE ) ) {
		return true;
	}
	$data = get_post_meta( $post->ID, '_elementor_data', true );
	return is_string( $data ) && false !== strpos( $data, CDA_SHORTCODE );
}

add_action( 'wp_enqueue_scripts', function () {
	wp_register_style( 'cda-carosello', CDA_URL . 'assets/carosello.css', array(), CDA_VERSION );
	wp_register_script( 'cda-carosello', CDA_URL . 'assets/carosello.js', array(), CDA_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );

	$elementor_preview = isset( $_GET['elementor-preview'] ); // phpcs:ignore WordPress.Security.NonceVerification
	// Caricando il CSS nell'<head> si evita che il carosello appaia "spoglio" per un istante.
	// Se lo shortcode è altrove (es. template Elementor) il CSS viene caricato dallo shortcode stesso.
	if ( $elementor_preview || cda_page_has_shortcode() ) {
		wp_enqueue_style( 'cda-carosello' );
	}
	if ( $elementor_preview ) {
		wp_enqueue_script( 'cda-carosello' );
	}
} );

add_shortcode( CDA_SHORTCODE, 'cda_shortcode' );

function cda_shortcode( $atts ) {
	$o      = cda_get_options();
	$fields = cda_flat_fields();

	// Gli attributi dello shortcode sovrascrivono le impostazioni globali.
	foreach ( (array) $atts as $key => $value ) {
		$key = 'tipo' === $key ? 'post_type' : $key;
		if ( isset( $fields[ $key ] ) ) {
			$o[ $key ] = cda_sanitize_value( $fields[ $key ], $value );
		}
	}

	$query = array(
		'post_type'           => $o['post_type'],
		'post_status'         => 'publish',
		'posts_per_page'      => $o['numero'],
		'orderby'             => 'date',
		'order'               => 'DESC',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);
	if ( '' !== $o['tag'] ) {
		$query['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
			array(
				'taxonomy' => 'post_tag',
				'field'    => 'slug',
				'terms'    => explode( ',', $o['tag'] ),
			),
		);
	}
	$posts = get_posts( $query );

	if ( ! $posts ) {
		return current_user_can( 'edit_posts' )
			? '<p class="cda-vuoto">Carosello Articoli: nessun articolo trovato' . ( $o['tag'] ? ' con il tag "' . esc_html( $o['tag'] ) . '"' : '' ) . '. (Messaggio visibile solo agli amministratori.)</p>'
			: '';
	}

	wp_enqueue_style( 'cda-carosello' );
	wp_enqueue_script( 'cda-carosello' );

	$config = array(
		'desktop'    => $o['desktop'],
		'tablet'     => $o['tablet'],
		'mobile'     => $o['mobile'],
		'bpTablet'   => $o['bp_tablet'],
		'bpMobile'   => $o['bp_mobile'],
		'gap'        => $o['gap'],
		'peek'       => $o['anteprima'],
		'loop'       => (bool) $o['loop'],
		'drag'       => (bool) $o['trascinamento'],
		'autoplay'   => (bool) $o['autoplay'],
		'interval'   => $o['intervallo'],
		'speed'      => $o['velocita'],
		'pauseHover' => (bool) $o['pausa_hover'],
	);

	$style = sprintf(
		'--cda-h:%dpx;--cda-gap:%dpx;--cda-img:%d%%;--cda-lines:%d;--cda-fit:%s;--cda-primary:%s;--cda-dark:%s;--cda-medium:%s;--cda-light:%s;--cda-card:%s;--cda-bg:%s;',
		$o['altezza'], $o['gap'], $o['img_altezza'], $o['righe'], $o['img_adattamento'],
		$o['colore_primario'], $o['colore_scuro'], $o['colore_medio'], $o['colore_chiaro'], $o['colore_card'], $o['colore_sfondo']
	);

	$classes = array( 'cda', 'cda--card-' . $o['stile_card'] );
	if ( $o['sfumatura'] ) {
		$classes[] = 'cda--fade';
	}
	if ( $o['trascinamento'] ) {
		$classes[] = 'cda--draggable';
	}
	$arrows_below = $o['frecce'] && 'sotto' === $o['frecce_posizione'];
	$classes[]    = $arrows_below ? 'cda--arrows-below' : 'cda--arrows-side';

	$target = $o['nuova_scheda'] ? ' target="_blank" rel="noopener"' : '';
	$sizes  = sprintf( '(max-width: %dpx) %dvw, (max-width: %dpx) %dvw, %dvw',
		$o['bp_mobile'], ceil( 100 / $o['mobile'] ), $o['bp_tablet'], ceil( 100 / $o['tablet'] ), ceil( 100 / $o['desktop'] ) );

	$more_icon = '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path d="M5 12h14M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>';

	$arrows = '';
	if ( $o['frecce'] ) {
		$chevron = '<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path d="M15 5l-7 7 7 7" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
		$arrows  = '<div class="cda__arrows">'
			. '<button type="button" class="cda__arrow cda__arrow--prev" aria-label="Articolo precedente">' . $chevron . '</button>'
			. '<button type="button" class="cda__arrow cda__arrow--next" aria-label="Articolo successivo">' . $chevron . '</button>'
			. '</div>';
	}

	ob_start();
	?>
	<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" style="<?php echo esc_attr( $style ); ?>" data-config="<?php echo esc_attr( wp_json_encode( $config ) ); ?>" role="region" aria-roledescription="carosello" aria-label="Articoli">
		<div class="cda__stage">
			<div class="cda__viewport">
				<div class="cda__track">
					<?php foreach ( $posts as $p ) : ?>
						<?php
						$excerpt = has_excerpt( $p ) ? $p->post_excerpt : strip_shortcodes( $p->post_content );
						$excerpt = wp_trim_words( wp_strip_all_tags( $excerpt ), 35, '…' );
						?>
						<div class="cda__slide">
							<a class="cda__card" href="<?php echo esc_url( get_permalink( $p ) ); ?>"<?php echo $target; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
								<div class="cda__media">
									<?php
									if ( has_post_thumbnail( $p ) ) {
										echo get_the_post_thumbnail( $p, 'large', array(
											'class'     => 'cda__img',
											'loading'   => 'lazy',
											'decoding'  => 'async',
											'draggable' => 'false',
											'sizes'     => $sizes,
											'alt'       => '',
										) );
									}
									?>
								</div>
								<div class="cda__body">
									<h3 class="cda__title"><?php echo esc_html( get_the_title( $p ) ); ?></h3>
									<?php if ( $excerpt ) : ?>
										<p class="cda__excerpt"><?php echo esc_html( $excerpt ); ?></p>
									<?php endif; ?>
									<?php if ( '' !== $o['testo_link'] ) : ?>
										<span class="cda__more"><span class="cda__more-text"><?php echo esc_html( $o['testo_link'] ); ?></span><span class="cda__more-icon"><?php echo $more_icon; // phpcs:ignore WordPress.Security.EscapeOutput ?></span></span>
									<?php endif; ?>
								</div>
							</a>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			<?php echo $arrows_below ? '' : $arrows; // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
		<?php if ( $o['pallini'] || $arrows_below ) : ?>
			<div class="cda__nav">
				<?php if ( $o['pallini'] ) : ?>
					<div class="cda__dots"></div>
				<?php endif; ?>
				<?php echo $arrows_below ? $arrows : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}
