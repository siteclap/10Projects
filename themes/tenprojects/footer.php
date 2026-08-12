<?php
/**
 * Site Footer
 *
 * @package TenProjects
 */

defined( 'ABSPATH' ) || exit;
?>

<footer class="tp-footer">
	<div class="tp-footer-grid">
		<div>
			<h4>About</h4>
			<p style="font-size:14px;line-height:1.7;">
				LeadMAAXX helps you find the best-fit properties from hundreds of options. Unbiased analysis, transparent scoring.
			</p>
		</div>
		<div>
			<h4>Quick Links</h4>
			<ul>
				<li><a href="<?php echo esc_url( home_url( '/projects/' ) ); ?>">All Projects</a></li>
				<li><a href="<?php echo esc_url( home_url( '/methodology/' ) ); ?>">Our Methodology</a></li>
				<li><a href="<?php echo esc_url( home_url( '/guides/' ) ); ?>">Buyer Guides</a></li>
				<li><a href="<?php echo esc_url( home_url( '/emi-calculator/' ) ); ?>">EMI Calculator</a></li>
			</ul>
		</div>
		<div>
			<h4>Locations</h4>
			<ul>
				<li><a href="<?php echo esc_url( home_url( '/navi-mumbai/kharghar/' ) ); ?>">Kharghar</a></li>
				<li><a href="<?php echo esc_url( home_url( '/navi-mumbai/panvel/' ) ); ?>">Panvel</a></li>
				<li><a href="<?php echo esc_url( home_url( '/navi-mumbai/ulwe/' ) ); ?>">Ulwe</a></li>
				<li><a href="<?php echo esc_url( home_url( '/navi-mumbai/vashi/' ) ); ?>">Vashi</a></li>
			</ul>
		</div>
		<div>
			<h4>Contact</h4>
			<ul>
				<li><a href="mailto:hello@leadmaaxx.com">hello@leadmaaxx.com</a></li>
				<li><a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>">Privacy Policy</a></li>
				<li><a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>">Terms of Service</a></li>
				<li><a href="<?php echo esc_url( home_url( '/disclaimer/' ) ); ?>">Disclaimer</a></li>
			</ul>
		</div>
	</div>
	<div class="tp-footer-bottom">
		&copy; <?php echo date( 'Y' ); ?> LeadMAAXX. All rights reserved.
	</div>
</footer>

<?php get_template_part( 'template-parts/chatbot' ); ?>
<?php wp_footer(); ?>
</body>
</html>
