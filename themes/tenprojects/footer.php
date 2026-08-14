<?php
/**
 * Site Footer — Dynamic brand settings from WP Admin.
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;

// Pull brand settings from wp_options (set in Brand Settings admin page).
$site_name      = get_option( 'tp_brand_name', 'LeadMAAXX' );
$about          = get_option( 'tp_brand_about', '' );
$phone          = get_option( 'tp_brand_phone', '' );
$email          = get_option( 'tp_brand_email', '' );
$address        = get_option( 'tp_brand_address', '' );
$rera_agent     = get_option( 'tp_brand_rera_agent', '' );
$rera_legal     = get_option( 'tp_brand_rera_legal_name', '' );
$logo_light     = get_option( 'tp_brand_logo_light', '' );
$logo           = $logo_light ?: get_template_directory_uri() . '/assets/images/logo.png';

// Social links.
$socials = array(
	'facebook'  => array( 'url' => get_option( 'tp_social_facebook', '' ),  'label' => 'Facebook',  'icon' => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>' ),
	'instagram' => array( 'url' => get_option( 'tp_social_instagram', '' ), 'label' => 'Instagram', 'icon' => '<rect width="20" height="20" x="2" y="2" rx="5" ry="5" fill="none" stroke="currentColor" stroke-width="2"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" fill="none" stroke="currentColor" stroke-width="2"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5" stroke="currentColor" stroke-width="2"/>' ),
	'linkedin'  => array( 'url' => get_option( 'tp_social_linkedin', '' ),  'label' => 'LinkedIn',  'icon' => '<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6z"/><rect width="4" height="12" x="2" y="9"/><circle cx="4" cy="4" r="2"/>' ),
	'youtube'   => array( 'url' => get_option( 'tp_social_youtube', '' ),   'label' => 'YouTube',   'icon' => '<path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19.1c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.43z"/><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02" fill="white"/>' ),
	'twitter'   => array( 'url' => get_option( 'tp_social_twitter', '' ),   'label' => 'X',         'icon' => '<path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>' ),
	'whatsapp'  => array( 'url' => get_option( 'tp_social_whatsapp', '' ),  'label' => 'WhatsApp',  'icon' => '<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>' ),
);
?>

<footer class="tp-footer">
	<div class="tp-footer-grid">
		<!-- Brand column -->
		<div>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="tp-footer-logo" aria-label="<?php echo esc_attr( $site_name ); ?> home">
				<img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $site_name ); ?>" height="40" style="height:40px;width:auto;">
			</a>
			<?php if ( $about ) : ?>
				<p style="font-size:14px;line-height:1.7;margin-top:12px;color:#9CA3AF;">
					<?php echo esc_html( $about ); ?>
				</p>
			<?php endif; ?>

			<?php if ( $phone || $email || $address ) : ?>
				<div style="margin-top:16px;font-size:14px;color:#9CA3AF;display:flex;flex-direction:column;gap:6px;">
					<?php if ( $phone ) : ?>
						<a href="tel:<?php echo esc_attr( $phone ); ?>" style="color:#9CA3AF;text-decoration:none;"><?php echo esc_html( $phone ); ?></a>
					<?php endif; ?>
					<?php if ( $email ) : ?>
						<a href="mailto:<?php echo esc_attr( $email ); ?>" style="color:#9CA3AF;text-decoration:none;"><?php echo esc_html( $email ); ?></a>
					<?php endif; ?>
					<?php if ( $address ) : ?>
						<p style="color:#6B7280;margin:0;"><?php echo esc_html( $address ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php
			// Social icons — only show ones that have a URL.
			$active_socials = array_filter( $socials, function( $s ) { return ! empty( $s['url'] ); } );
			if ( ! empty( $active_socials ) ) : ?>
				<div style="display:flex;align-items:center;gap:8px;margin-top:16px;">
					<?php foreach ( $active_socials as $key => $s ) :
						$href = $s['url'];
						if ( 'whatsapp' === $key && strpos( $href, 'http' ) !== 0 ) {
							$href = 'https://wa.me/' . preg_replace( '/[^0-9]/', '', $href );
						}
					?>
						<a href="<?php echo esc_url( $href ); ?>" target="_blank" rel="noopener noreferrer"
						   aria-label="<?php echo esc_attr( $s['label'] ); ?>"
						   style="display:flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;color:#9CA3AF;transition:all 0.15s;"
						   onmouseover="this.style.color='#fff';this.style.background='rgba(255,255,255,0.1)'"
						   onmouseout="this.style.color='#9CA3AF';this.style.background='transparent'">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><?php echo $s['icon']; ?></svg>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<!-- Quick Links -->
		<div>
			<h4>Quick Links</h4>
			<ul>
				<li><a href="<?php echo esc_url( home_url( '/projects/' ) ); ?>">All Projects</a></li>
				<li><a href="<?php echo esc_url( home_url( '/methodology/' ) ); ?>">Our Methodology</a></li>
				<li><a href="<?php echo esc_url( home_url( '/guides/' ) ); ?>">Buyer Guides</a></li>
				<li><a href="<?php echo esc_url( home_url( '/emi-calculator/' ) ); ?>">EMI Calculator</a></li>
			</ul>
		</div>

		<!-- Locations -->
		<div>
			<h4>Locations</h4>
			<ul>
				<li><a href="<?php echo esc_url( home_url( '/navi-mumbai/kharghar/' ) ); ?>">Kharghar</a></li>
				<li><a href="<?php echo esc_url( home_url( '/navi-mumbai/panvel/' ) ); ?>">Panvel</a></li>
				<li><a href="<?php echo esc_url( home_url( '/navi-mumbai/ulwe/' ) ); ?>">Ulwe</a></li>
				<li><a href="<?php echo esc_url( home_url( '/navi-mumbai/vashi/' ) ); ?>">Vashi</a></li>
			</ul>
		</div>

		<!-- Contact / Company -->
		<div>
			<h4>Company</h4>
			<ul>
				<li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">About Us</a></li>
				<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a></li>
				<li><a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>">Privacy Policy</a></li>
				<li><a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>">Terms of Service</a></li>
				<li><a href="<?php echo esc_url( home_url( '/disclaimer/' ) ); ?>">Disclaimer</a></li>
			</ul>
		</div>
	</div>

	<!-- Bottom bar -->
	<div class="tp-footer-bottom">
		<p>&copy; <?php echo esc_html( date( 'Y' ) ); ?> <?php echo esc_html( $site_name ); ?>. All rights reserved.</p>
		<?php if ( $rera_agent ) : ?>
			<p style="font-size:11px;color:#6B7280;margin-top:4px;">
				RERA Agent: <?php if ( $rera_legal ) echo esc_html( $rera_legal ) . ' | '; ?><?php echo esc_html( $rera_agent ); ?>.
				All project information is sourced from RERA and developer websites. Verify independently before making any decisions.
			</p>
		<?php endif; ?>
	</div>
</footer>

<?php get_template_part( 'template-parts/chatbot' ); ?>
<?php wp_footer(); ?>
</body>
</html>
